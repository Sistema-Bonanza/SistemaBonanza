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
                (id_proveedor, numero_factura, fecha_factura, subtotal, tasa_iva, monto_iva, total, id_usuario, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmtCompra = $this->pdo->prepare($sqlCompra);
            $stmtCompra->execute([
                $cabecera['id_proveedor'],
                $cabecera['numero_factura'],
                $cabecera['fecha_factura'],
                $cabecera['subtotal'],
                $cabecera['tasa_iva'],
                $cabecera['monto_iva'],
                $cabecera['total'],
                $cabecera['id_usuario']
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

    // ==================================================================================
    // OBTENER MOVIMIENTOS CON FILTROS Y PAGINACIÓN (SEGURO CONTRA SQL INJECTION)
    // ==================================================================================
    public function obtenerMovimientosPaginados($busqueda, $filtro, $offset, $limite) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];

            // 1. Validación de Búsqueda Segura
            if (!empty($busqueda)) {
                // Buscamos coincidencias en factura, proveedor (nombre o RIF) y usuario
                $whereClause .= " AND (c.numero_factura LIKE :busqueda 
                                   OR p.razon_social LIKE :busqueda 
                                   OR p.rif LIKE :busqueda 
                                   OR u.username LIKE :busqueda)";
                // Preparamos el comodín % desde PHP, NO desde SQL
                $params[':busqueda'] = "%{$busqueda}%";
            }

            // 2. Validación de Filtros (Entradas / Salidas)
            if ($filtro === 'salidas') {
                // Como las ventas dicen "Pronto", forzamos 0 resultados de forma segura
                $whereClause .= " AND 1=0"; 
            } 
            // Si es 'entradas' o 'todas', consulta normal a compras. (A futuro aquí añadirás UNION con la tabla ventas).

            $sql = "SELECT 
                        c.id_compra, c.numero_factura, c.fecha_factura, 
                        c.fecha_registro, c.total, c.estado,
                        p.razon_social AS nombre_proveedor, p.rif AS rif_proveedor, 
                        u.username AS nombre_usuario
                    FROM compras c
                    INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                    INNER JOIN usuarios u ON c.id_usuario = u.id
                    $whereClause
                    ORDER BY c.fecha_registro DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);

            // 3. Bind Dinámico de Textos (Buscador)
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }

            // 4. Bind Estricto de Enteros (Paginación)
            // Esto evita que un atacante envíe "?pagina=1; DROP TABLE usuarios"
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            // Guardamos el error en el log interno, pero no le mostramos la estructura SQL al usuario
            error_log("Error en obtenerMovimientosPaginados: " . $e->getMessage());
            return [];
        }
    }

    // ==================================================================================
    // CONTAR TOTAL DE REGISTROS PARA DIBUJAR LA PAGINACIÓN EXACTA
    // ==================================================================================
    public function contarMovimientosPaginados($busqueda, $filtro) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];

            if (!empty($busqueda)) {
                $whereClause .= " AND (c.numero_factura LIKE :busqueda 
                                   OR p.razon_social LIKE :busqueda 
                                   OR p.rif LIKE :busqueda 
                                   OR u.username LIKE :busqueda)";
                $params[':busqueda'] = "%{$busqueda}%";
            }

            if ($filtro === 'salidas') {
                $whereClause .= " AND 1=0";
            }

            // Usamos COUNT(*) para no sobrecargar la memoria trayendo datos que no usaremos
            $sql = "SELECT COUNT(*) 
                    FROM compras c
                    INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                    INNER JOIN usuarios u ON c.id_usuario = u.id
                    $whereClause";

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            // fetchColumn() extrae directamente el número del COUNT
            return (int) $stmt->fetchColumn();

        } catch(PDOException $e) {
            error_log("Error en contarMovimientosPaginados: " . $e->getMessage());
            return 0;
        }
    }
}   