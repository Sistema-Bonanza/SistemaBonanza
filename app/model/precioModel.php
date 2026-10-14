<?php
/**
 * app/model/precioModel.php
 *
 * Modelo del módulo de Control de Precios.
 *
 * Reglas que respeta (vienen de tu esquema SQL):
 *  - historial_precios tiene un CHECK: solo se puede insertar una fila si
 *    cambió el precio de compra o el de venta. Por eso nunca se inserta
 *    cuando no hubo cambio real.
 *  - Todo cambio de precio = UPDATE en productos + INSERT en historial_precios,
 *    dentro de UNA transacción: o se guardan los dos o ninguno.
 *  - El modelo NO imprime ni hace die(): devuelve códigos de resultado y el
 *    controlador decide qué mensaje mostrar.
 *
 * Nota técnica: la conexión usa ATTR_EMULATE_PREPARES = false (consultas
 * preparadas reales). Con eso un mismo parámetro con nombre NO se puede
 * repetir en una consulta (por eso en la búsqueda hay :q1 y :q2), y los
 * valores de LIMIT/OFFSET deben enlazarse como enteros (bindValue).
 */
class PrecioModel
{
    /** Por debajo de este margen (%) un producto se marca como "margen bajo". */
    const MARGEN_MINIMO = 10.0;

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // =====================================================================
    // LECTURA: CATÁLOGO DE PRECIOS
    // =====================================================================

    /**
     * Subconsulta: costo unitario de la última compra NO anulada del producto.
     * (compras.estado = 1 son las vigentes; 0 son las anuladas.)
     */
    private function sqlUltimoCosto(): string
    {
        return "(SELECT dc.costo_unitario
                   FROM detalle_compras dc
                  INNER JOIN compras co ON co.id_compra = dc.id_compra
                  WHERE dc.id_producto = p.id_producto
                    AND co.estado = 1
                  ORDER BY co.fecha_factura DESC, co.id_compra DESC, dc.id_detalle DESC
                  LIMIT 1)";
    }

    /** SELECT base compartido por obtenerCatalogo() y obtenerProducto(). */
    private function sqlCatalogoBase(): string
    {
        return "SELECT
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    p.id_categoria,
                    p.aplica_iva,
                    p.precio_compra,
                    p.precio_venta,
                    c.nombre AS nombre_categoria,
                    ROUND(" . $this->sqlUltimoCosto() . ", 2) AS ultimo_costo_compra,
                    (SELECT MAX(h.fecha)
                       FROM historial_precios h
                      WHERE h.id_producto = p.id_producto) AS ultimo_cambio
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria";
    }

    /**
     * Todos los productos ACTIVOS con su situación de precio calculada.
     * Es una sola consulta; la búsqueda y los filtros se aplican después con
     * filtrar() (el catálogo de una distribuidora es pequeño, así que es más
     * simple y no pesa).
     */
    public function obtenerCatalogo(): array
    {
        $sql = $this->sqlCatalogoBase() . " WHERE p.estado = 1 ORDER BY p.nombre ASC";
        $filas = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'enriquecer'], $filas);
    }

    /** Un producto activo por id (con la misma información que el catálogo), o null. */
    public function obtenerProducto(int $idProducto): ?array
    {
        $sql = $this->sqlCatalogoBase() . " WHERE p.id_producto = :id AND p.estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idProducto, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->enriquecer($fila) : null;
    }

    /**
     * Agrega a cada fila: costo_ref, origen_costo, margen y situacion.
     *
     *  costo_ref    = precio_compra si es mayor que 0; si no, el último costo
     *                 de compra (hoy la compra NO actualiza precio_compra).
     *  origen_costo = 'registrado' | 'compra' | 'ninguno'
     *  situacion    = sin_precio | sin_costo | bajo_costo | margen_bajo | ok
     */
    private function enriquecer(array $fila): array
    {
        $compra = (float) $fila['precio_compra'];
        $venta  = (float) $fila['precio_venta'];
        $ultimo = $fila['ultimo_costo_compra'] !== null ? (float) $fila['ultimo_costo_compra'] : 0.0;

        if ($compra > 0) {
            $costo  = $compra;
            $origen = 'registrado';
        } elseif ($ultimo > 0) {
            $costo  = $ultimo;
            $origen = 'compra';
        } else {
            $costo  = 0.0;
            $origen = 'ninguno';
        }

        $margen = calcularMargen($costo, $venta);

        if ($venta <= 0) {
            $situacion = 'sin_precio';
        } elseif ($costo <= 0) {
            $situacion = 'sin_costo';
        } elseif ($venta < $costo) {
            $situacion = 'bajo_costo';
        } elseif ($margen !== null && $margen < self::MARGEN_MINIMO) {
            $situacion = 'margen_bajo';
        } else {
            $situacion = 'ok';
        }

        $fila['costo_ref']    = $costo;
        $fila['origen_costo'] = $origen;
        $fila['margen']       = $margen;
        $fila['situacion']    = $situacion;

        return $fila;
    }

    /**
     * Aplica los filtros sobre el catálogo ya cargado.
     * $filtros: q (texto), id_categoria (int), estado (situacion o 'todos').
     */
    public function filtrar(array $catalogo, array $filtros): array
    {
        $q      = isset($filtros['q']) ? mb_strtolower((string) $filtros['q'], 'UTF-8') : '';
        $cat    = !empty($filtros['id_categoria']) ? (int) $filtros['id_categoria'] : 0;
        $estado = $filtros['estado'] ?? 'todos';

        $resultado = [];
        foreach ($catalogo as $p) {
            if ($cat > 0 && (int) $p['id_categoria'] !== $cat) {
                continue;
            }
            if ($estado !== 'todos' && $p['situacion'] !== $estado) {
                continue;
            }
            if ($q !== '') {
                $nombre = mb_strtolower($p['nombre'], 'UTF-8');
                $codigo = mb_strtolower($p['codigo'], 'UTF-8');
                // strpos en lugar de str_contains para funcionar también en PHP 7.
                if (strpos($nombre, $q) === false && strpos($codigo, $q) === false) {
                    continue;
                }
            }
            $resultado[] = $p;
        }
        return $resultado;
    }

    /** Conteo por situación para las tarjetas de resumen. */
    public function resumir(array $catalogo): array
    {
        $resumen = [
            'total'       => count($catalogo),
            'sin_precio'  => 0,
            'sin_costo'   => 0,
            'bajo_costo'  => 0,
            'margen_bajo' => 0,
            'ok'          => 0,
        ];
        foreach ($catalogo as $p) {
            $resumen[$p['situacion']]++;
        }
        return $resumen;
    }

    // =====================================================================
    // ESCRITURA: CAMBIO DE PRECIO DE UN PRODUCTO
    // =====================================================================

    /**
     * Cambia costo y precio de venta de UN producto y deja el rastro en el
     * historial. Todo en una transacción.
     *
     * $compraEsperado / $ventaEsperado son los precios que el usuario VIO al
     * abrir el formulario. Si en la BD ya son otros, alguien más los cambió
     * mientras tanto: se rechaza para no pisar ese cambio en silencio.
     *
     * Devuelve: 'ok' | 'sin_cambios' | 'conflicto' | 'no_existe' | 'error'
     */
    public function actualizarPrecio(
        int $idProducto,
        string $compraNuevo,
        string $ventaNuevo,
        string $compraEsperado,
        string $ventaEsperado,
        int $idUsuario
    ): string {
        try {
            $this->pdo->beginTransaction();

            // FOR UPDATE bloquea la fila hasta el commit: nadie más puede
            // cambiarla entre que la leemos y que la actualizamos.
            $stmt = $this->pdo->prepare(
                "SELECT precio_compra, precio_venta
                   FROM productos
                  WHERE id_producto = :id AND estado = 1
                    FOR UPDATE"
            );
            $stmt->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $stmt->execute();
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$actual) {
                $this->pdo->rollBack();
                return 'no_existe';
            }

            $compraActual = $this->dosDecimales($actual['precio_compra']);
            $ventaActual  = $this->dosDecimales($actual['precio_venta']);

            if ($compraActual !== $this->dosDecimales($compraEsperado)
                || $ventaActual !== $this->dosDecimales($ventaEsperado)) {
                $this->pdo->rollBack();
                return 'conflicto';
            }

            if ($compraActual === $this->dosDecimales($compraNuevo)
                && $ventaActual === $this->dosDecimales($ventaNuevo)) {
                $this->pdo->rollBack();
                return 'sin_cambios';
            }

            $upd = $this->pdo->prepare(
                "UPDATE productos
                    SET precio_compra = :compra, precio_venta = :venta
                  WHERE id_producto = :id"
            );
            $upd->bindValue(':compra', $compraNuevo);
            $upd->bindValue(':venta', $ventaNuevo);
            $upd->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $upd->execute();

            $this->insertarHistorial(
                $idProducto,
                $compraActual,
                $compraNuevo,
                $ventaActual,
                $ventaNuevo,
                $idUsuario
            );

            $this->pdo->commit();
            return 'ok';

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('PrecioModel::actualizarPrecio -> ' . $e->getMessage());
            return 'error';
        }
    }

    /** INSERT en historial_precios. id_compra va NULL: es un cambio manual. */
    private function insertarHistorial(
        int $idProducto,
        string $compraAnterior,
        string $compraNuevo,
        string $ventaAnterior,
        string $ventaNuevo,
        int $idUsuario
    ): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO historial_precios
                (id_producto, precio_compra_anterior, precio_compra_nuevo,
                 precio_venta_anterior, precio_venta_nuevo, id_compra, id_usuario)
             VALUES
                (:id_producto, :compra_ant, :compra_nue,
                 :venta_ant, :venta_nue, NULL, :id_usuario)"
        );
        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindValue(':compra_ant', $compraAnterior);
        $stmt->bindValue(':compra_nue', $compraNuevo);
        $stmt->bindValue(':venta_ant', $ventaAnterior);
        $stmt->bindValue(':venta_nue', $ventaNuevo);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
    }

    // =====================================================================
    // AJUSTE MASIVO POR PORCENTAJE (solo precio de venta)
    // =====================================================================

    /**
     * Calcula el precio de venta nuevo, o null si ese producto se omite:
     *  - precio actual en 0 (no hay base para aplicar un porcentaje),
     *  - resultado <= 0 o que no cabe en DECIMAL(10,2),
     *  - resultado igual al actual (el CHECK del historial no lo permitiría).
     */
    public function calcularNuevoPrecio($actual, string $tipo, float $porcentaje): ?string
    {
        $actual = (float) $actual;
        if ($actual <= 0) {
            return null;
        }

        $factor = ($tipo === 'aumento') ? (1 + $porcentaje / 100) : (1 - $porcentaje / 100);
        $nuevo  = round($actual * $factor, 2);

        if ($nuevo <= 0 || $nuevo > 99999999.99) {
            return null;
        }
        if ($this->dosDecimales($nuevo) === $this->dosDecimales($actual)) {
            return null;
        }
        return $this->dosDecimales($nuevo);
    }

    /**
     * Vista previa: NO escribe nada. Recibe filas del catálogo.
     * Devuelve ['cambios' => [...], 'omitidos' => n].
     */
    public function simularAjusteMasivo(array $filas, string $tipo, float $porcentaje): array
    {
        $cambios  = [];
        $omitidos = 0;

        foreach ($filas as $p) {
            $nuevo = $this->calcularNuevoPrecio($p['precio_venta'], $tipo, $porcentaje);
            if ($nuevo === null) {
                $omitidos++;
                continue;
            }
            $cambios[] = [
                'id_producto'      => $p['id_producto'],
                'codigo'           => $p['codigo'],
                'nombre'           => $p['nombre'],
                'nombre_categoria' => $p['nombre_categoria'],
                'precio_venta'     => $p['precio_venta'],
                'nuevo'            => $nuevo,
            ];
        }
        return ['cambios' => $cambios, 'omitidos' => $omitidos];
    }

    /**
     * Aplica el ajuste. Recalcula con los precios que hay en la BD en este
     * momento (los bloquea con FOR UPDATE), así que si alguien cambió algo
     * entre la vista previa y la confirmación, se usa el precio vigente.
     * Todo o nada: si algo falla, no se cambia ningún producto.
     *
     * Devuelve ['estado' => 'ok'|'error', 'actualizados' => n, 'omitidos' => n]
     */
    public function aplicarAjusteMasivo(?int $idCategoria, string $tipo, float $porcentaje, int $idUsuario): array
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "SELECT id_producto, precio_compra, precio_venta
                      FROM productos
                     WHERE estado = 1";
            if ($idCategoria !== null) {
                $sql .= " AND id_categoria = :cat";
            }
            $sql .= " FOR UPDATE";

            $stmt = $this->pdo->prepare($sql);
            if ($idCategoria !== null) {
                $stmt->bindValue(':cat', $idCategoria, PDO::PARAM_INT);
            }
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $upd = $this->pdo->prepare(
                "UPDATE productos SET precio_venta = :venta WHERE id_producto = :id"
            );

            $actualizados = 0;
            $omitidos     = 0;

            foreach ($productos as $p) {
                $nuevo = $this->calcularNuevoPrecio($p['precio_venta'], $tipo, $porcentaje);
                if ($nuevo === null) {
                    $omitidos++;
                    continue;
                }

                $upd->bindValue(':venta', $nuevo);
                $upd->bindValue(':id', (int) $p['id_producto'], PDO::PARAM_INT);
                $upd->execute();

                // El costo no cambia: anterior = nuevo. El CHECK se cumple porque
                // el precio de venta sí cambió.
                $compra = $this->dosDecimales($p['precio_compra']);
                $this->insertarHistorial(
                    (int) $p['id_producto'],
                    $compra,
                    $compra,
                    $this->dosDecimales($p['precio_venta']),
                    $nuevo,
                    $idUsuario
                );
                $actualizados++;
            }

            $this->pdo->commit();
            return ['estado' => 'ok', 'actualizados' => $actualizados, 'omitidos' => $omitidos];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('PrecioModel::aplicarAjusteMasivo -> ' . $e->getMessage());
            return ['estado' => 'error', 'actualizados' => 0, 'omitidos' => 0];
        }
    }

    // =====================================================================
    // LECTURA: HISTORIAL
    // =====================================================================

    /**
     * Arma el WHERE del historial y llena $params.
     * $filtros: id_producto (int), desde (Y-m-d), hasta (Y-m-d).
     */
    private function condicionesHistorial(array $filtros, array &$params): string
    {
        $where = [];

        if (!empty($filtros['id_producto'])) {
            $where[] = 'h.id_producto = :id_producto';
            $params[':id_producto'] = (int) $filtros['id_producto'];
        }
        if (!empty($filtros['desde'])) {
            $where[] = 'h.fecha >= :desde';
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            // "hasta" incluye todo ese día: se compara contra el día siguiente a las 00:00.
            $siguiente = (new DateTime($filtros['hasta']))->modify('+1 day')->format('Y-m-d');
            $where[] = 'h.fecha < :hasta_exclusivo';
            $params[':hasta_exclusivo'] = $siguiente;
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

    /** Filas del historial, más recientes primero. */
    public function historial(array $filtros, int $limite, int $desplazamiento): array
    {
        $params = [];
        $where  = $this->condicionesHistorial($filtros, $params);

        $sql = "SELECT
                    h.id_historial,
                    h.id_producto,
                    h.precio_compra_anterior,
                    h.precio_compra_nuevo,
                    h.precio_venta_anterior,
                    h.precio_venta_nuevo,
                    h.id_compra,
                    h.fecha,
                    p.codigo,
                    p.nombre AS nombre_producto,
                    u.username AS nombre_usuario,
                    co.numero_factura
                FROM historial_precios h
                INNER JOIN productos p ON p.id_producto = h.id_producto
                INNER JOIN usuarios  u ON u.id = h.id_usuario
                LEFT JOIN  compras  co ON co.id_compra = h.id_compra
                $where
                ORDER BY h.fecha DESC, h.id_historial DESC
                LIMIT :limite OFFSET :desplazamiento";

        $stmt = $this->pdo->prepare($sql);
        $this->enlazar($stmt, $params);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':desplazamiento', $desplazamiento, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Total de filas del historial con esos filtros (para paginar). */
    public function contarHistorial(array $filtros): int
    {
        $params = [];
        $where  = $this->condicionesHistorial($filtros, $params);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM historial_precios h $where");
        $this->enlazar($stmt, $params);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // =====================================================================
    // UTILIDAD
    // =====================================================================

    /** Normaliza a texto con 2 decimales ("5" => "5.00") para comparar sin errores de float. */
    private function dosDecimales($valor): string
    {
        return number_format((float) $valor, 2, '.', '');
    }
}
