<?php
/**
 * app/model/ventaModel.php
 *
 * Modelo del módulo de Ventas (notas de entrega).
 *
 * Tablas que toca (ya creadas con correcciones.txt):
 *   ventas           cabecera: una fila por venta/factura (numero_factura, tipo_pago)
 *   venta_pagos      cómo se pagó: una fila por forma de pago (varias en pago mixto)
 *   detalle_ventas   líneas: una fila por producto dentro de la venta
 *   movimientos      kardex: cada venta genera una 'salida' por producto (columna id_venta)
 *   productos        se descuenta stock_actual (CHECK stock_actual >= 0)
 *
 * Reglas que respeta (vienen del esquema SQL):
 *  - Una venta = UNA transacción: cabecera + líneas + kardex + descuento de stock.
 *    Si algo falla, no se guarda nada.
 *  - Las filas de productos se bloquean con FOR UPDATE antes de descontar, siempre en
 *    orden de id_producto. Así dos ventas simultáneas no pueden vender el mismo stock
 *    dos veces, y tampoco se bloquean entre sí (deadlock).
 *  - Las ventas NO se borran: se anulan. Anular devuelve el stock con una 'entrada'
 *    en el kardex ligada a la misma venta.
 *  - Igual que PrecioModel, este modelo NO imprime ni hace die(): devuelve códigos de
 *    resultado y el controlador decide qué mensaje mostrar.
 *
 * Nota técnica: la conexión usa ATTR_EMULATE_PREPARES = false (consultas preparadas
 * reales). Con eso un mismo parámetro con nombre NO se puede repetir en una consulta
 * (por eso la búsqueda tiene :q1, :q2 y :q3), y LIMIT/OFFSET se enlazan como enteros.
 */

require_once __DIR__ . '/../helpers/formato.php';

class VentaModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // =====================================================================
    // LECTURA: LISTADO
    // =====================================================================

    /**
     * Arma el WHERE del listado y llena $params.
     * $filtros: q (texto), desde (Y-m-d), hasta (Y-m-d), estado ('1' registrada | '0' anulada).
     */
    private function condiciones(array $filtros, array &$params): string
    {
        $where = [];

        $q = isset($filtros['q']) ? trim((string) $filtros['q']) : '';
        if ($q !== '') {
            // Se escapan % y _ para que el usuario no los use como comodines.
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $cond = '(c.nombre_razon_social LIKE :q1 OR c.num_documento LIKE :q2';
            $params[':q1'] = $like;
            $params[':q2'] = $like;

            // "VEN-000015", "FAC-000015", "ven15" o "15" también encuentran la venta por su número.
            if (preg_match('/^(?:VEN|FAC)?-?0*(\d{1,10})\z/i', $q, $m)) {
                $cond .= ' OR v.id_venta = :q3';
                $params[':q3'] = (int) $m[1];
            }
            $where[] = $cond . ')';
        }

        if (!empty($filtros['desde'])) {
            $where[] = 'v.fecha_venta >= :desde';
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            // "hasta" incluye todo ese día: se compara contra el día siguiente a las 00:00.
            $siguiente = (new DateTime($filtros['hasta']))->modify('+1 day')->format('Y-m-d');
            $where[] = 'v.fecha_venta < :hasta_exclusivo';
            $params[':hasta_exclusivo'] = $siguiente;
        }

        if (isset($filtros['estado']) && ($filtros['estado'] === '0' || $filtros['estado'] === '1')) {
            $where[] = 'v.estado = :estado';
            $params[':estado'] = (int) $filtros['estado'];
        }

        return $where ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    /** Enlaza los parámetros: enteros como INT, el resto como texto. */
    private function enlazar(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $nombre => $valor) {
            $stmt->bindValue($nombre, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    /** Filas del listado, más recientes primero. */
    public function obtenerVentas(array $filtros, int $limite, int $desplazamiento): array
    {
        $params = [];
        $where  = $this->condiciones($filtros, $params);

        $sql = "SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.total,
                    v.estado,
                    v.observaciones,
                    c.tipo_documento,
                    c.num_documento,
                    c.nombre_razon_social AS nombre_cliente,
                    u.username AS nombre_usuario
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                INNER JOIN usuarios u ON u.id = v.id_usuario
                $where
                ORDER BY v.fecha_venta DESC, v.id_venta DESC
                LIMIT :limite OFFSET :desplazamiento";

        $stmt = $this->pdo->prepare($sql);
        $this->enlazar($stmt, $params);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':desplazamiento', $desplazamiento, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Total de filas del listado con esos filtros (para paginar). */
    public function contarVentas(array $filtros): int
    {
        $params = [];
        $where  = $this->condiciones($filtros, $params);

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
               FROM ventas v
              INNER JOIN clientes c ON c.id_cliente = v.id_cliente
              $where"
        );
        $this->enlazar($stmt, $params);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Indicadores de las tarjetas del listado. Solo cuentan ventas registradas
     * (las anuladas no suman). "Mes" = desde el día 1 del mes en curso.
     */
    public function resumen(): array
    {
        $desdeMes = "DATE_FORMAT(CURDATE(), '%Y-%m-01')";

        $sql = "SELECT
                    (SELECT COUNT(*) FROM ventas
                      WHERE estado = 1 AND fecha_venta >= CURDATE()) AS ventas_hoy,
                    (SELECT COALESCE(SUM(total), 0) FROM ventas
                      WHERE estado = 1 AND fecha_venta >= CURDATE()) AS total_hoy,
                    (SELECT COUNT(*) FROM ventas
                      WHERE estado = 1 AND fecha_venta >= $desdeMes) AS ventas_mes,
                    (SELECT COALESCE(SUM(total), 0) FROM ventas
                      WHERE estado = 1 AND fecha_venta >= $desdeMes) AS total_mes,
                    (SELECT COALESCE(SUM(d.cantidad), 0)
                       FROM detalle_ventas d
                      INNER JOIN ventas v2 ON v2.id_venta = d.id_venta
                      WHERE v2.estado = 1 AND v2.fecha_venta >= $desdeMes) AS unidades_mes";

        $fila = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

        return [
            'ventas_hoy'   => (int) $fila['ventas_hoy'],
            'total_hoy'    => (float) $fila['total_hoy'],
            'ventas_mes'   => (int) $fila['ventas_mes'],
            'total_mes'    => (float) $fila['total_mes'],
            'unidades_mes' => (int) $fila['unidades_mes'],
        ];
    }

    // =====================================================================
    // LECTURA: UNA VENTA (nota de entrega)
    // =====================================================================

    /** Cabecera de una venta con datos del cliente y de los usuarios, o null si no existe. */
    public function obtenerVenta(int $idVenta): ?array
    {
        $sql = "SELECT
                    v.id_venta, v.numero_factura, v.id_cliente, v.fecha_venta,
                    v.subtotal, v.tasa_iva, v.monto_iva, v.total, v.tipo_pago,
                    v.observaciones, v.estado, v.id_usuario,
                    v.fecha_anulacion, v.id_usuario_anula, v.motivo_anulacion,
                    c.tipo_documento, c.num_documento,
                    c.nombre_razon_social AS nombre_cliente,
                    c.telefono AS telefono_cliente,
                    c.email AS email_cliente,
                    c.direccion AS direccion_cliente,
                    u.username  AS nombre_usuario,
                    ua.username AS nombre_usuario_anula
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                INNER JOIN usuarios u ON u.id = v.id_usuario
                LEFT JOIN  usuarios ua ON ua.id = v.id_usuario_anula
                WHERE v.id_venta = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $fila : null;
    }

    /** Líneas de una venta, en el orden en que se registraron. */
    public function obtenerDetalle(int $idVenta): array
    {
        $sql = "SELECT
                    d.id_detalle, d.id_producto, d.cantidad, d.precio_unitario,
                    d.aplica_iva, d.subtotal_linea,
                    p.codigo, p.nombre
                FROM detalle_ventas d
                INNER JOIN productos p ON p.id_producto = d.id_producto
                WHERE d.id_venta = :id
                ORDER BY d.id_detalle ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idVenta, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cómo se pagó la venta: una fila por forma de pago (varias si fue pago mixto). */
    public function obtenerPagos(int $idVenta): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT metodo, monto FROM venta_pagos WHERE id_venta = :id ORDER BY id_pago ASC"
        );
        $stmt->bindValue(':id', $idVenta, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================================================
    // ESCRITURA: REGISTRAR UNA VENTA
    // =====================================================================

    /**
     * Guarda la venta completa y descuenta el stock.
     *
     * $cabecera: id_cliente, subtotal, tasa_iva, monto_iva, total, tipo_pago, observaciones (o null), id_usuario
     * $detalles: lista de líneas, cada una con
     *            id_producto, cantidad (en unidades), precio_unitario, aplica_iva, subtotal_linea
     * $pagos:    lista de ['metodo' => 'efectivo', 'monto' => '12.50']. Una fila si se pagó con una
     *            sola forma de pago; varias si fue pago mixto. Deben sumar EXACTAMENTE el total.
     *
     * La factura se genera aquí mismo: número FAC-000015 (el mismo correlativo de la venta), que
     * se guarda en ventas.numero_factura y queda también en el motivo de cada salida del kardex.
     *
     * El controlador ya calculó los montos con los precios que el usuario VIO en pantalla. Aquí,
     * con las filas de productos bloqueadas, se comprueba que nada cambió mientras tanto:
     *  - el stock sigue alcanzando,
     *  - el precio y el IVA del producto son los mismos que se usaron para el cálculo.
     * Si el precio cambió se rechaza (como PrecioModel::actualizarPrecio con 'conflicto'),
     * para no cobrar un monto distinto al que se mostró.
     *
     * Devuelve:
     *   ['ok' => true,  'id' => <id_venta>, 'numero_factura' => 'FAC-000015']
     *   ['ok' => false, 'motivo' => 'stock_insuficiente', 'producto' => <nombre>, 'disponible' => <int>]
     *   ['ok' => false, 'motivo' => 'conflicto',          'producto' => <nombre>]
     *   ['ok' => false, 'motivo' => 'producto_invalido']
     *   ['ok' => false, 'motivo' => 'pagos_invalidos']   (los pagos no suman el total)
     *   ['ok' => false, 'motivo' => 'ocupado']   (otra operación bloqueaba las mismas filas: reintentar)
     *   ['ok' => false, 'motivo' => 'error']
     */
    public function registrarVentaCompleta(array $cabecera, array $detalles, array $pagos): array
    {
        // Los pagos deben cuadrar con el total al centavo. El controlador ya lo valida; se repite
        // aquí, ANTES de abrir la transacción, porque este es el último punto antes de la BD.
        $sumaPagos = 0;
        foreach ($pagos as $pago) {
            $sumaPagos += aCentavos($pago['monto']);
        }
        if (empty($pagos) || $sumaPagos !== aCentavos($cabecera['total'])) {
            error_log('VentaModel::registrarVentaCompleta -> los pagos no suman el total');
            return ['ok' => false, 'motivo' => 'pagos_invalidos'];
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Bloquear los productos, SIEMPRE en orden de id (evita deadlocks entre ventas).
            $stmtBloqueo = $this->pdo->prepare(
                "SELECT id_producto, nombre, precio_compra, precio_venta, aplica_iva, stock_actual, estado
                   FROM productos
                  WHERE id_producto = ?
                    FOR UPDATE"
            );

            $ids = array_map(function ($d) {
                return (int) $d['id_producto'];
            }, $detalles);
            sort($ids);

            $productos = [];
            foreach ($ids as $idProducto) {
                $stmtBloqueo->execute([$idProducto]);
                $fila = $stmtBloqueo->fetch(PDO::FETCH_ASSOC);

                if (!$fila || (int) $fila['estado'] !== 1) {
                    $this->pdo->rollBack();
                    return ['ok' => false, 'motivo' => 'producto_invalido'];
                }
                $productos[$idProducto] = $fila;
            }

            // 2. Verificar cada línea contra la fila bloqueada.
            foreach ($detalles as $det) {
                $prod = $productos[(int) $det['id_producto']];

                $precioActual = number_format((float) $prod['precio_venta'], 2, '.', '');
                if ($precioActual !== (string) $det['precio_unitario']
                    || (int) $prod['aplica_iva'] !== (int) $det['aplica_iva']) {
                    $this->pdo->rollBack();
                    return ['ok' => false, 'motivo' => 'conflicto', 'producto' => $prod['nombre']];
                }

                if ((int) $prod['stock_actual'] < (int) $det['cantidad']) {
                    $this->pdo->rollBack();
                    return [
                        'ok'         => false,
                        'motivo'     => 'stock_insuficiente',
                        'producto'   => $prod['nombre'],
                        'disponible' => (int) $prod['stock_actual'],
                    ];
                }
            }

            // 3. Cabecera.
            $stmtVenta = $this->pdo->prepare(
                "INSERT INTO ventas
                    (id_cliente, subtotal, tasa_iva, monto_iva, total, tipo_pago, observaciones, id_usuario, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $stmtVenta->execute([
                $cabecera['id_cliente'],
                $cabecera['subtotal'],
                $cabecera['tasa_iva'],
                $cabecera['monto_iva'],
                $cabecera['total'],
                $cabecera['tipo_pago'],
                $cabecera['observaciones'],
                $cabecera['id_usuario'],
            ]);
            $idVenta = (int) $this->pdo->lastInsertId();

            // Factura: su número se deriva del id de la venta (FAC-000015 <-> VEN-000015).
            $numeroFactura = numeroOperacion('FAC', $idVenta);
            $this->pdo->prepare("UPDATE ventas SET numero_factura = ? WHERE id_venta = ?")
                      ->execute([$numeroFactura, $idVenta]);

            // Pagos: una fila por forma de pago.
            $stmtPago = $this->pdo->prepare(
                "INSERT INTO venta_pagos (id_venta, metodo, monto) VALUES (?, ?, ?)"
            );
            foreach ($pagos as $pago) {
                $stmtPago->execute([$idVenta, $pago['metodo'], $pago['monto']]);
            }

            // 4. Líneas + kardex + descuento de stock.
            $stmtDetalle = $this->pdo->prepare(
                "INSERT INTO detalle_ventas
                    (id_venta, id_producto, cantidad, precio_unitario, aplica_iva, subtotal_linea, costo_unitario)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmtKardex = $this->pdo->prepare(
                "INSERT INTO movimientos
                    (id_producto, tipo, cantidad, stock_antes, stock_despues, motivo, id_venta, id_usuario)
                 VALUES (?, 'salida', ?, ?, ?, ?, ?, ?)"
            );
            $stmtStock = $this->pdo->prepare(
                "UPDATE productos SET stock_actual = ? WHERE id_producto = ?"
            );

            $motivo = 'Factura ' . $numeroFactura;   // así el kardex muestra a qué factura pertenece cada salida

            foreach ($detalles as $det) {
                $idProducto = (int) $det['id_producto'];
                $cantidad   = (int) $det['cantidad'];
                $prod       = $productos[$idProducto];

                $stmtDetalle->execute([
                    $idVenta,
                    $idProducto,
                    $cantidad,
                    $det['precio_unitario'],
                    (int) $det['aplica_iva'],
                    $det['subtotal_linea'],
                    $this->costoReferencia($idProducto, $prod['precio_compra']),
                ]);

                $stockAntes   = (int) $prod['stock_actual'];
                $stockDespues = $stockAntes - $cantidad;

                $stmtKardex->execute([
                    $idProducto,
                    $cantidad,
                    $stockAntes,
                    $stockDespues,
                    $motivo,
                    $idVenta,
                    $cabecera['id_usuario'],
                ]);
                $stmtStock->execute([$stockDespues, $idProducto]);

                // Si el mismo producto apareciera dos veces, la segunda línea parte del stock ya descontado.
                $productos[$idProducto]['stock_actual'] = $stockDespues;
            }

            $this->pdo->commit();
            return ['ok' => true, 'id' => $idVenta, 'numero_factura' => $numeroFactura];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('VentaModel::registrarVentaCompleta -> ' . $e->getMessage());
            return ['ok' => false, 'motivo' => $this->motivoDeError($e)];
        }
    }

    /**
     * Costo unitario que se guarda en la línea (sirve a Reportes para calcular ganancias).
     * Misma regla que Control de Precios (PrecioModel::enriquecer): el precio_compra registrado
     * si es mayor que 0; si no, el costo de la última compra NO anulada; si tampoco hay, NULL.
     * Se guarda con 4 decimales (la columna es DECIMAL(12,4)).
     */
    private function costoReferencia(int $idProducto, $precioCompra): ?string
    {
        if ((float) $precioCompra > 0) {
            return number_format((float) $precioCompra, 4, '.', '');
        }

        $stmt = $this->pdo->prepare(
            "SELECT dc.costo_unitario
               FROM detalle_compras dc
              INNER JOIN compras co ON co.id_compra = dc.id_compra
              WHERE dc.id_producto = ?
                AND co.estado = 1
              ORDER BY co.fecha_factura DESC, co.id_compra DESC, dc.id_detalle DESC
              LIMIT 1"
        );
        $stmt->execute([$idProducto]);
        $ultimo = $stmt->fetchColumn();

        if ($ultimo !== false && $ultimo !== null && (float) $ultimo > 0) {
            return number_format((float) $ultimo, 4, '.', '');
        }
        return null;
    }

    // =====================================================================
    // ESCRITURA: ANULAR UNA VENTA
    // =====================================================================

    /**
     * Anula una venta registrada y devuelve el stock al inventario.
     * Por cada línea se crea una 'entrada' en el kardex ligada a la venta, así queda el rastro
     * completo: salió con la venta y volvió con la anulación.
     *
     * Devuelve: ['ok' => true] | ['ok' => false, 'motivo' => 'no_existe' | 'ya_anulada' | 'ocupado' | 'error']
     */
    public function anularVenta(int $idVenta, int $idUsuario, string $motivo): array
    {
        try {
            $this->pdo->beginTransaction();

            // Se bloquea la cabecera: dos anulaciones simultáneas no pueden devolver el stock dos veces.
            $stmt = $this->pdo->prepare("SELECT estado, numero_factura FROM ventas WHERE id_venta = ? FOR UPDATE");
            $stmt->execute([$idVenta]);
            $venta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                $this->pdo->rollBack();
                return ['ok' => false, 'motivo' => 'no_existe'];
            }
            if ((int) $venta['estado'] !== 1) {
                $this->pdo->rollBack();
                return ['ok' => false, 'motivo' => 'ya_anulada'];
            }

            // Líneas ordenadas por producto: mismo orden de bloqueo que al vender.
            $stmtLineas = $this->pdo->prepare(
                "SELECT id_producto, cantidad FROM detalle_ventas WHERE id_venta = ? ORDER BY id_producto ASC"
            );
            $stmtLineas->execute([$idVenta]);
            $lineas = $stmtLineas->fetchAll(PDO::FETCH_ASSOC);

            $stmtStockActual = $this->pdo->prepare(
                "SELECT stock_actual FROM productos WHERE id_producto = ? FOR UPDATE"
            );
            $stmtKardex = $this->pdo->prepare(
                "INSERT INTO movimientos
                    (id_producto, tipo, cantidad, stock_antes, stock_despues, motivo, id_venta, id_usuario)
                 VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?)"
            );
            $stmtStock = $this->pdo->prepare(
                "UPDATE productos SET stock_actual = ? WHERE id_producto = ?"
            );

            // Ventas anteriores a las facturas no tienen número guardado: se deriva igual que al crearlas.
            $factura      = !empty($venta['numero_factura']) ? $venta['numero_factura'] : numeroOperacion('FAC', $idVenta);
            $motivoKardex = 'Anulación factura ' . $factura;

            foreach ($lineas as $linea) {
                $idProducto = (int) $linea['id_producto'];
                $cantidad   = (int) $linea['cantidad'];

                $stmtStockActual->execute([$idProducto]);
                $stockAntes   = (int) $stmtStockActual->fetchColumn();
                $stockDespues = $stockAntes + $cantidad;

                $stmtKardex->execute([
                    $idProducto,
                    $cantidad,
                    $stockAntes,
                    $stockDespues,
                    $motivoKardex,
                    $idVenta,
                    $idUsuario,
                ]);
                $stmtStock->execute([$stockDespues, $idProducto]);
            }

            $stmtAnular = $this->pdo->prepare(
                "UPDATE ventas
                    SET estado = 0,
                        fecha_anulacion = NOW(),
                        id_usuario_anula = ?,
                        motivo_anulacion = ?
                  WHERE id_venta = ?"
            );
            $stmtAnular->execute([$idUsuario, $motivo, $idVenta]);

            $this->pdo->commit();
            return ['ok' => true];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('VentaModel::anularVenta -> ' . $e->getMessage());
            return ['ok' => false, 'motivo' => $this->motivoDeError($e)];
        }
    }

    // =====================================================================
    // UTILIDAD
    // =====================================================================

    /**
     * 1213 = deadlock, 1205 = tiempo de espera de bloqueo agotado: no es un fallo del sistema,
     * solo otra operación usaba las mismas filas. Se avisa distinto para que el usuario reintente.
     */
    private function motivoDeError(PDOException $e): string
    {
        $codigo = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
        return ($codigo === 1213 || $codigo === 1205) ? 'ocupado' : 'error';
    }
}
