<?php 

Class movimientoModel {
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

//==================================================================================
        //OBTENER TODOS LOS PRODUCTOS
    public function obtenerCompras(){
    try{
        $sql = "SELECT
                    c.id_compra, 
                    c.numero_factura, 
                    c.fecha_factura, 
                    c.fecha_registro, 
                    c.total, 
                    c.estado,
                    p.razon_social AS nombre_proveedor, 
                    p.rif AS rif_proveedor, 
                    u.username AS nombre_usuario
                FROM compras c
                INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                INNER JOIN usuarios u ON c.id_usuario = u.id
                ORDER BY c.fecha_registro DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        // En producción (cuando el sistema esté listo), esto debe ser solo un return [];
        // Por ahora lo dejamos con die() para cazar errores de MySQL.
        die("Error en obtenerCompras: " . $e->getMessage());
    }
}
    //==================================================================================

    public function registrarCompraCompleta($cabecera, $detalles) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar Cabecera
            $sqlCompra = "INSERT INTO compras 
                (id_proveedor, numero_factura, fecha_factura, subtotal, tasa_iva, monto_iva, total, id_usuario, tasa_cambio, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmtCompra = $this->pdo->prepare($sqlCompra);
            $stmtCompra->execute([
                $cabecera['id_proveedor'],
                $cabecera['numero_factura'],
                $cabecera['fecha_factura'],
                $cabecera['subtotal'],
                $cabecera['tasa_iva'],
                $cabecera['monto_iva'],
                $cabecera['total'],
                $cabecera['id_usuario'],
                $cabecera['tasa_cambio'] ?? null
            ]);
            
            $id_compra = $this->pdo->lastInsertId();

            // 2. Preparar consultas para el ciclo
            // Agregamos id_presentacion después de id_producto y sumamos un "?" al final
            $sqlDetalle = "INSERT INTO detalle_compras 
                (id_compra, id_producto, id_presentacion, tipo_empaque, cantidad_empaques, unidades_por_empaque, cantidad_unidades, costo_por_empaque, costo_unitario, aplica_iva, subtotal_linea) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDetalle = $this->pdo->prepare($sqlDetalle);

            $sqlStockActual = "SELECT stock_actual FROM productos WHERE id_producto = ? FOR UPDATE";
            $stmtStockActual = $this->pdo->prepare($sqlStockActual);

            $sqlKardex = "INSERT INTO movimientos 
                (id_producto, tipo, cantidad, stock_antes, stock_despues, motivo, id_compra, id_usuario) 
                VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?)";
            $stmtKardex = $this->pdo->prepare($sqlKardex);

            $sqlActualizarStock = "UPDATE productos SET stock_actual = ? WHERE id_producto = ?";
            $stmtActualizarStock = $this->pdo->prepare($sqlActualizarStock);

            usort($detalles, function($a, $b) {
                return $a['id_producto'] <=> $b['id_producto'];
            });

            // 3. Ejecutar ciclo por cada producto válido
            foreach ($detalles as $det) {
                
                $stmtDetalle->execute([
                    $id_compra,
                    $det['id_producto'],
                    $det['id_presentacion'], // <-- AÑADIMOS EL NUEVO ENLACE AQUÍ
                    $det['tipo_empaque'],
                    $det['cantidad_empaques'],
                    $det['unidades_por_empaque'],
                    $det['cantidad_unidades'],
                    $det['costo_por_empaque'],
                    $det['costo_unitario'],
                    $det['aplica_iva'],
                    $det['subtotal_linea']
                ]);

                $stmtStockActual->execute([$det['id_producto']]);
                $filaProd = $stmtStockActual->fetch(PDO::FETCH_ASSOC);
                $stock_antes = $filaProd['stock_actual'] ?? 0;
                $stock_despues = $stock_antes + $det['cantidad_unidades'];

                $motivo = "Compra Factura #" . $cabecera['numero_factura'];
                $stmtKardex->execute([
                    $det['id_producto'],
                    $det['cantidad_unidades'],
                    $stock_antes,
                    $stock_despues,
                    $motivo,
                    $id_compra,
                    $cabecera['id_usuario']
                ]);

                $stmtActualizarStock->execute([$stock_despues, $det['id_producto']]);
            }

            $this->pdo->commit();
            
            // =========================================================================
            // A8: NUEVO CONTRATO DE RESPUESTA (ÉXITO)
            // En vez de retornar true, devolvemos un arreglo con el estado y el ID
            // =========================================================================
            return ['ok' => true, 'id' => $id_compra];

        } catch (PDOException $e) {
            // =========================================================================
            // A9: REVERTIR TRANSACCIÓN SEGURA
            // Solo hacemos rollBack si realmente se inició una transacción. 
            // Evita un error fatal doble si el servidor de BD se cayó antes del beginTransaction.
            // =========================================================================
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            // A8: Se eliminó el die() para no mostrar el error SQL puro al usuario[cite: 15].

            // =========================================================================
            // A9: DETECCIÓN DE FACTURA DUPLICADA (ERROR 1062)
            // MySQL arroja error 1062 cuando violamos un constreñimiento de llave única (UNIQUE).
            // =========================================================================
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                return ['ok' => false, 'motivo' => 'factura_duplicada'];
            }
            
            // =========================================================================
            // A9: ERRORES INESPERADOS
            // Guardamos el error real en los logs del servidor para ti (el programador)
            // y devolvemos un error genérico para la pantalla del usuario.
            // =========================================================================
            error_log("Fallo en registrarCompraCompleta: " . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error'];
        }
    }

    // ==========================================================
    // DETALLE DE UNA COMPRA (nota de recepción)
    // ==========================================================

    /** Cabecera de una compra con proveedor y usuario, o null si no existe. */
    public function obtenerCompra($idCompra) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT c.id_compra, c.numero_factura, c.fecha_factura, c.fecha_registro,
                        c.subtotal, c.tasa_iva, c.monto_iva, c.total, c.tasa_cambio,
                        c.observaciones, c.estado, c.fecha_anulacion, c.motivo_anulacion,
                        p.razon_social, p.rif, p.telefono AS telefono_proveedor, p.direccion AS direccion_proveedor,
                        u.username AS nombre_usuario
                   FROM compras c
                  INNER JOIN proveedores p ON p.id_proveedor = c.id_proveedor
                  INNER JOIN usuarios u ON u.id = c.id_usuario
                  WHERE c.id_compra = :id"
            );
            $stmt->bindValue(':id', (int) $idCompra, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila : null;
        } catch (PDOException $e) {
            error_log('MovimientoModel::obtenerCompra -> ' . $e->getMessage());
            return null;
        }
    }

    /** Líneas de una compra en el orden en que se registraron. */
    public function obtenerDetalleCompra($idCompra) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT d.id_detalle, d.tipo_empaque, d.cantidad_empaques, d.unidades_por_empaque,
                        d.cantidad_unidades, d.costo_por_empaque, d.costo_unitario, d.aplica_iva, d.subtotal_linea,
                        p.codigo, p.nombre
                   FROM detalle_compras d
                  INNER JOIN productos p ON p.id_producto = d.id_producto
                  WHERE d.id_compra = :id
                  ORDER BY d.id_detalle ASC"
            );
            $stmt->bindValue(':id', (int) $idCompra, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MovimientoModel::obtenerDetalleCompra -> ' . $e->getMessage());
            return [];
        }
    }

    // ==========================================================
    // FUNCIONES PARA LA BÚSQUEDA Y PAGINACIÓN AJAX
    // ----------------------------------------------------------
    // El listado principal junta DOS orígenes en una sola lista:
    //   - COMPRAS (entradas al inventario)  -> tipo 'compra'
    //   - VENTAS  (salidas del inventario)  -> tipo 'venta'
    // Cada fila trae las mismas columnas sin importar el origen:
    //   tipo, id, documento, fecha, total, estado, entidad, identificacion, nombre_usuario
    // Nota: la conexión usa consultas preparadas reales (sin emulación), por eso un mismo
    // parámetro con nombre NO se repite: cada rama del UNION tiene los suyos (:c_..., :v_...).
    // ==========================================================

    /**
     * Arma el UNION (compras + ventas) según búsqueda y filtro.
     * Devuelve [sql_union, parametros]. El filtro 'entradas' deja solo compras y 'salidas' solo ventas.
     */
    private function sqlUnionMovimientos($busqueda, $filtro) {
        $params = [];
        $ramas  = [];

        $busqueda = trim((string) $busqueda);
        $like = '%' . addcslashes($busqueda, '%_\\') . '%';

        // Un texto como "VEN-000015", "FAC-15", "COMP-3" o "15" también encuentra por id.
        $idBuscado = null;
        if ($busqueda !== '' && preg_match('/^(?:VEN|FAC|COMP)?-?0*(\d{1,10})\z/i', $busqueda, $m)) {
            $idBuscado = (int) $m[1];
        }

        // ---------------- RAMA 1: COMPRAS (entradas) ----------------
        if ($filtro !== 'salidas') {
            $where = '1=1';
            if ($busqueda !== '') {
                $where .= " AND (c.numero_factura LIKE :c_q1
                             OR p.razon_social LIKE :c_q2
                             OR p.rif LIKE :c_q3
                             OR u.username LIKE :c_q4";
                $params[':c_q1'] = $like;
                $params[':c_q2'] = $like;
                $params[':c_q3'] = $like;
                $params[':c_q4'] = $like;
                if ($idBuscado !== null) {
                    $where .= " OR c.id_compra = :c_id";
                    $params[':c_id'] = $idBuscado;
                }
                $where .= ")";
            }

            $ramas[] = "SELECT 'compra' AS tipo,
                               c.id_compra AS id,
                               CONVERT(c.numero_factura USING utf8mb4) COLLATE utf8mb4_general_ci AS documento,
                               c.fecha_registro AS fecha,
                               c.total AS total,
                               c.estado AS estado,
                               c.tasa_cambio AS tasa_cambio,
                               CONVERT(p.razon_social USING utf8mb4) COLLATE utf8mb4_general_ci AS entidad,
                               CONVERT(p.rif USING utf8mb4) COLLATE utf8mb4_general_ci AS identificacion,
                               CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci AS nombre_usuario
                          FROM compras c
                         INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                         INNER JOIN usuarios u ON c.id_usuario = u.id
                         WHERE $where";
        }

        // ---------------- RAMA 2: VENTAS (salidas) ----------------
        if ($filtro !== 'entradas') {
            $where = '1=1';
            if ($busqueda !== '') {
                $where .= " AND (v.numero_factura LIKE :v_q1
                             OR cl.nombre_razon_social LIKE :v_q2
                             OR cl.num_documento LIKE :v_q3
                             OR u2.username LIKE :v_q4";
                $params[':v_q1'] = $like;
                $params[':v_q2'] = $like;
                $params[':v_q3'] = $like;
                $params[':v_q4'] = $like;
                if ($idBuscado !== null) {
                    $where .= " OR v.id_venta = :v_id";
                    $params[':v_id'] = $idBuscado;
                }
                $where .= ")";
            }

            // Ventas viejas pudieron no guardar numero_factura: se deriva FAC-000015 igual que en Ventas.
            $ramas[] = "SELECT 'venta' AS tipo,
                               v.id_venta AS id,
                               CONVERT(COALESCE(v.numero_factura, CONCAT('FAC-', LPAD(v.id_venta, 6, '0'))) USING utf8mb4) COLLATE utf8mb4_general_ci AS documento,
                               v.fecha_venta AS fecha,
                               v.total AS total,
                               v.estado AS estado,
                               v.tasa_cambio AS tasa_cambio,
                               CONVERT(cl.nombre_razon_social USING utf8mb4) COLLATE utf8mb4_general_ci AS entidad,
                               CONVERT(CONCAT(cl.tipo_documento, '-', cl.num_documento) USING utf8mb4) COLLATE utf8mb4_general_ci AS identificacion,
                               CONVERT(u2.username USING utf8mb4) COLLATE utf8mb4_general_ci AS nombre_usuario
                          FROM ventas v
                         INNER JOIN clientes cl ON v.id_cliente = cl.id_cliente
                         INNER JOIN usuarios u2 ON v.id_usuario = u2.id
                         WHERE $where";
        }

        if (empty($ramas)) {
            // Filtro desconocido: no se muestra nada.
            $ramas[] = "SELECT 'compra' AS tipo, 0 AS id, '' AS documento, NOW() AS fecha, 0 AS total,
                               1 AS estado, NULL AS tasa_cambio, '' AS entidad, '' AS identificacion, '' AS nombre_usuario
                         FROM DUAL WHERE 1=0";
        }

        return [implode("\n UNION ALL \n", $ramas), $params];
    }

    /** Enlaza parámetros: enteros como INT, el resto como texto. */
    private function enlazarParametros(PDOStatement $stmt, array $params) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    public function obtenerMovimientosPaginados($busqueda, $filtro, $offset, $limite) {
        try {
            [$union, $params] = $this->sqlUnionMovimientos($busqueda, $filtro);

            $sql = "SELECT * FROM ( $union ) AS mov
                    ORDER BY mov.fecha DESC, mov.id DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $this->enlazarParametros($stmt, $params);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            error_log("Error BD Paginacion: " . $e->getMessage());
            return [];
        }
    }

    public function contarMovimientosPaginados($busqueda, $filtro) {
        try {
            [$union, $params] = $this->sqlUnionMovimientos($busqueda, $filtro);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ( $union ) AS mov");
            $this->enlazarParametros($stmt, $params);
            $stmt->execute();
            return (int) $stmt->fetchColumn();

        } catch(PDOException $e) {
            error_log("Error BD Conteo movimientos: " . $e->getMessage());
            return 0;
        }
    }


    public function obtenerMetricasMovimientos() {
        try {
            // 1. Entradas del mes: unidades que ingresaron por COMPRAS (las reversiones por anulación de
            //    ventas también son 'entrada' en el kardex, pero no son compras: se excluyen con id_compra).
            //    El valor es lo que costó la mercancía (subtotal de las compras vigentes), no el precio de venta.
            $sqlEntradas = "SELECT COALESCE(SUM(m.cantidad), 0) AS total_unds
                            FROM movimientos m
                            INNER JOIN compras c ON c.id_compra = m.id_compra AND c.estado = 1
                            WHERE m.tipo = 'entrada'
                            AND MONTH(m.fecha) = MONTH(CURRENT_DATE())
                            AND YEAR(m.fecha) = YEAR(CURRENT_DATE())";
            $entradas = $this->pdo->query($sqlEntradas)->fetch(PDO::FETCH_ASSOC);
            $sqlValor = "SELECT COALESCE(SUM(subtotal), 0.00) AS total_valor
                         FROM compras
                         WHERE estado = 1
                         AND MONTH(fecha_registro) = MONTH(CURRENT_DATE())
                         AND YEAR(fecha_registro) = YEAR(CURRENT_DATE())";
            $entradas['total_valor'] = $this->pdo->query($sqlValor)->fetchColumn();

            // 2. Salidas del mes: unidades vendidas en facturas vigentes (las anuladas ya volvieron al inventario).
            $sqlSalidas = "SELECT COALESCE(SUM(m.cantidad), 0) AS total_unds
                           FROM movimientos m
                           INNER JOIN ventas v ON v.id_venta = m.id_venta AND v.estado = 1
                           WHERE m.tipo = 'salida'
                           AND MONTH(m.fecha) = MONTH(CURRENT_DATE())
                           AND YEAR(m.fecha) = YEAR(CURRENT_DATE())";
            $salidas = $this->pdo->query($sqlSalidas)->fetch(PDO::FETCH_ASSOC);

            // 3. Stock crítico: productos activos por debajo del mínimo que fijó el administrador
            //    (mínimo 0 = sin alerta; antes contaba como crítico todo producto con stock 0).
            $sqlCriticos = "SELECT COUNT(*) AS total_criticos 
                            FROM productos 
                            WHERE stock_minimo > 0
                            AND stock_actual < stock_minimo 
                            AND estado = 1";
            $stmtC = $this->pdo->query($sqlCriticos);
            $criticos = $stmtC->fetch(PDO::FETCH_ASSOC);

            return [
                'entradas_unds'  => (int) ($entradas['total_unds'] ?? 0),
                'entradas_valor' => (float) ($entradas['total_valor'] ?? 0.00),
                'salidas_unds'   => (int) ($salidas['total_unds'] ?? 0),
                'stock_critico'  => (int) ($criticos['total_criticos'] ?? 0)
            ];

        } catch (PDOException $e) {
            error_log("Error al obtener métricas de movimientos: " . $e->getMessage());
            return [
                'entradas_unds'  => 0,
                'entradas_valor' => 0.00,
                'salidas_unds'   => 0,
                'stock_critico'  => 0
            ];
        }
    }
}
