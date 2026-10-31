<?php
/**
 * app/model/reporteModel.php
 *
 * Modelo del módulo de Reportes. SOLO LEE: nunca inserta, actualiza ni borra.
 *
 * Reportes que arma:
 *   1. cierreDeCaja()          cierre de caja de UN día (ventas, formas de pago, anulaciones)
 *   2. inventario()            existencias valoradas, con situación de stock
 *   3. gananciaPorProducto()   ingresos - costo por producto, en un rango de fechas
 *   4. variacionDeVentas()     compara las ventas de dos períodos, producto por producto
 *
 * Tablas que consulta: ventas, venta_pagos, detalle_ventas, productos, categorias,
 * clientes, usuarios, compras, detalle_compras.
 * Requiere las columnas/tablas de migracion_pagos_factura.sql (venta_pagos, tipo_pago,
 * numero_factura); sin esa migración tampoco se pueden registrar ventas.
 *
 * Reglas de negocio (iguales a las del resto del sistema):
 *  - Solo cuentan las ventas REGISTRADAS (estado = 1). Las anuladas se muestran aparte
 *    y nunca suman en ingresos, costos ni cantidades.
 *  - Los ingresos y la ganancia se calculan SIN IVA (detalle_ventas.subtotal_linea): el IVA
 *    es un impuesto que se cobra para el fisco, no es ingreso del negocio. El cierre de caja
 *    sí muestra el total con IVA, porque es lo que entra a la caja.
 *  - El costo de cada línea vendida es detalle_ventas.costo_unitario, que VentaModel guardó
 *    en el momento de la venta. Si ese costo no se conocía (NULL) la línea NO entra al cálculo
 *    de ganancia (no se inventa un costo) y se avisa en pantalla.
 *  - Los montos se suman en CENTAVOS ENTEROS (aCentavos) para que filas y totales cuadren
 *    exactamente. Los métodos devuelven los montos ya como decimales con 2 cifras (float).
 *
 * Nota técnica: la conexión usa ATTR_EMULATE_PREPARES = false (consultas preparadas reales):
 * un mismo parámetro con nombre NO se puede repetir dentro de una consulta, por eso hay
 * nombres como :a_ini1, :a_ini2, :a_ini3. Las consultas usan SQL estándar (sin funciones
 * propias de MySQL) y las fechas llegan como parámetros ya calculados en PHP.
 *
 * Igual que PrecioModel y VentaModel, este modelo no imprime ni hace die().
 */

require_once __DIR__ . '/../helpers/formato.php';

class ReporteModel
{
    /** Forma de pago que NO entra a la caja: es dinero por cobrar. */
    private const METODO_CREDITO = 'credito';

    /**
     * Subconsulta: último costo unitario de compra del producto (compras NO anuladas).
     * Es la MISMA regla de PrecioModel::sqlUltimoCosto() y de VentaModel::costoReferencia().
     * Se usa dentro de un SELECT cuyo alias de productos es "p".
     */
    private const SQL_ULTIMO_COSTO = "(SELECT dc.costo_unitario
                   FROM detalle_compras dc
                  INNER JOIN compras co ON co.id_compra = dc.id_compra
                  WHERE dc.id_producto = p.id_producto
                    AND co.estado = 1
                  ORDER BY co.fecha_factura DESC, co.id_compra DESC, dc.id_detalle DESC
                  LIMIT 1)";

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // =====================================================================
    // UTILIDADES PRIVADAS
    // =====================================================================

    /** Ejecuta una consulta preparada y devuelve todas las filas. */
    private function consultar(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $nombre => $valor) {
            $stmt->bindValue($nombre, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Primera fila de una consulta (o arreglo vacío). */
    private function fila(string $sql, array $params = []): array
    {
        $filas = $this->consultar($sql, $params);
        return $filas ? $filas[0] : [];
    }

    /** Primera columna de la primera fila. */
    private function valor(string $sql, array $params = [])
    {
        $fila = $this->fila($sql, $params);
        return $fila ? reset($fila) : null;
    }

    /** "2026-10-30" => "2026-10-31". Los rangos son [desde, siguiente día de hasta): incluyen todo el último día. */
    private function siguienteDia(string $dia): string
    {
        return (new DateTime($dia))->modify('+1 day')->format('Y-m-d');
    }

    /** Centavos enteros => decimal con 2 cifras. */
    private function dec(int $centavos): float
    {
        return $centavos / 100;
    }

    // =====================================================================
    // LISTAS PARA LOS FILTROS
    // =====================================================================

    /** Todas las categorías, por nombre (para el selector de los filtros). */
    public function categorias(): array
    {
        return $this->consultar("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
    }

    // =====================================================================
    // 1. CIERRE DE CAJA DIARIO
    // =====================================================================

    /**
     * Cierre de caja de un día ('Y-m-d').
     *
     * Devuelve:
     *   dia, facturas, unidades
     *   subtotal, iva, total             ventas registradas (total = lo vendido, con IVA)
     *   pagos[metodo] = [facturas, monto]  desglose por forma de pago (solo las usadas)
     *   cobrado                          lo que entró de verdad: todo menos el crédito
     *   credito                          vendido a crédito: queda POR COBRAR
     *   diferencia_pagos                 total - suma de pagos; debe ser 0 (si no, hay ventas sin desglose)
     *   anuladas[cantidad, total]        ventas de ese día que se anularon (no suman)
     *   anulaciones_previas[cantidad, total]  ventas de días ANTERIORES anuladas ese día
     *   por_usuario[]                    quién vendió cuánto
     *   ventas[]                         todas las ventas del día (registradas y anuladas)
     */
    public function cierreDeCaja(string $dia): array
    {
        $p     = [':ini' => $dia, ':fin' => $this->siguienteDia($dia)];
        $rango = 'v.fecha_venta >= :ini AND v.fecha_venta < :fin';

        // Totales de las ventas registradas del día.
        $t = $this->fila(
            "SELECT COUNT(*)                     AS facturas,
                    COALESCE(SUM(v.subtotal), 0)  AS subtotal,
                    COALESCE(SUM(v.monto_iva), 0) AS iva,
                    COALESCE(SUM(v.total), 0)     AS total
               FROM ventas v
              WHERE v.estado = 1 AND $rango",
            $p
        );

        $unidades = (int) $this->valor(
            "SELECT COALESCE(SUM(d.cantidad), 0)
               FROM detalle_ventas d
              INNER JOIN ventas v ON v.id_venta = d.id_venta
              WHERE v.estado = 1 AND $rango",
            $p
        );

        // Desglose por forma de pago (un pago mixto aporta una fila a cada forma usada).
        $filasPago = $this->consultar(
            "SELECT pg.metodo,
                    COUNT(DISTINCT pg.id_venta)   AS facturas,
                    COALESCE(SUM(pg.monto), 0)    AS monto
               FROM venta_pagos pg
              INNER JOIN ventas v ON v.id_venta = pg.id_venta
              WHERE v.estado = 1 AND $rango
              GROUP BY pg.metodo",
            $p
        );

        $pagos = [];
        $centavosCobrado = 0;
        $centavosCredito = 0;
        foreach ($filasPago as $f) {
            $c = aCentavos($f['monto']);
            $pagos[$f['metodo']] = ['facturas' => (int) $f['facturas'], 'monto' => $this->dec($c)];
            if ($f['metodo'] === self::METODO_CREDITO) {
                $centavosCredito += $c;
            } else {
                $centavosCobrado += $c;
            }
        }
        $centavosTotal = aCentavos($t['total']);

        // Ventas anuladas que se hicieron ese día (informativo: no suman).
        $anuladas = $this->fila(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(v.total), 0) AS total
               FROM ventas v
              WHERE v.estado = 0 AND $rango",
            $p
        );

        // Ventas de días ANTERIORES que se anularon ese día: no están en este cierre, pero
        // si ya se había cobrado, el dinero pudo haber salido de la caja de hoy.
        $previas = $this->fila(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(v.total), 0) AS total
               FROM ventas v
              WHERE v.estado = 0
                AND v.fecha_anulacion >= :ini
                AND v.fecha_anulacion < :fin
                AND v.fecha_venta < :antes",
            [':ini' => $dia, ':fin' => $this->siguienteDia($dia), ':antes' => $dia]
        );

        $porUsuario = $this->consultar(
            "SELECT u.username,
                    COUNT(*)                   AS facturas,
                    COALESCE(SUM(v.total), 0)  AS total_vendido
               FROM ventas v
              INNER JOIN usuarios u ON u.id = v.id_usuario
              WHERE v.estado = 1 AND $rango
              GROUP BY u.id, u.username
              ORDER BY total_vendido DESC, u.username ASC",
            $p
        );

        $ventas = $this->consultar(
            "SELECT v.id_venta, v.numero_factura, v.fecha_venta, v.total, v.tipo_pago, v.estado,
                    c.nombre_razon_social AS cliente,
                    u.username            AS usuario
               FROM ventas v
              INNER JOIN clientes c ON c.id_cliente = v.id_cliente
              INNER JOIN usuarios u ON u.id = v.id_usuario
              WHERE $rango
              ORDER BY v.fecha_venta ASC, v.id_venta ASC",
            $p
        );
        // Ventas anteriores a las facturas no guardaron número: se deriva igual que en Ventas.
        foreach ($ventas as &$venta) {
            if (empty($venta['numero_factura'])) {
                $venta['numero_factura'] = numeroOperacion('FAC', $venta['id_venta']);
            }
        }
        unset($venta);

        foreach ($porUsuario as &$u) {
            $u['facturas']      = (int) $u['facturas'];
            $u['total_vendido'] = $this->dec(aCentavos($u['total_vendido']));
        }
        unset($u);

        return [
            'dia'                 => $dia,
            'facturas'            => (int) $t['facturas'],
            'unidades'            => $unidades,
            'subtotal'            => $this->dec(aCentavos($t['subtotal'])),
            'iva'                 => $this->dec(aCentavos($t['iva'])),
            'total'               => $this->dec($centavosTotal),
            'pagos'               => $pagos,
            'cobrado'             => $this->dec($centavosCobrado),
            'credito'             => $this->dec($centavosCredito),
            'diferencia_pagos'    => $this->dec($centavosTotal - $centavosCobrado - $centavosCredito),
            'anuladas'            => [
                'cantidad' => (int) $anuladas['cantidad'],
                'total'    => $this->dec(aCentavos($anuladas['total'])),
            ],
            'anulaciones_previas' => [
                'cantidad' => (int) $previas['cantidad'],
                'total'    => $this->dec(aCentavos($previas['total'])),
            ],
            'por_usuario'         => $porUsuario,
            'ventas'              => $ventas,
        ];
    }

    // =====================================================================
    // 2. INVENTARIO
    // =====================================================================

    /**
     * Todos los productos con su stock y su valor. Por defecto solo los ACTIVOS.
     *
     * A cada fila le agrega:
     *   stock, minimo        enteros
     *   costo_unitario       precio_compra si es > 0; si no, el último costo de compra; si no, null
     *   origen_costo         'registrado' | 'compra' | 'ninguno'
     *   valor_costo          stock x costo      (null si no hay costo)
     *   valor_venta          stock x precio_venta (null si no hay precio)
     *   situacion            'agotado' | 'bajo' | 'ok'
     *
     * Los valores van SIN IVA. El costo usa los 4 decimales de la compra (0,5833), sin redondear
     * antes de multiplicar, para que 120 unidades den el costo real de la factura.
     */
    public function inventario(bool $incluirInactivos = false): array
    {
        $sql = "SELECT p.id_producto, p.codigo, p.nombre, p.id_categoria, p.estado,
                       p.stock_actual, p.stock_minimo, p.aplica_iva,
                       p.precio_compra, p.precio_venta,
                       c.nombre AS nombre_categoria,
                       " . self::SQL_ULTIMO_COSTO . " AS ultimo_costo_compra
                  FROM productos p
                 INNER JOIN categorias c ON c.id_categoria = p.id_categoria"
             . ($incluirInactivos ? '' : ' WHERE p.estado = 1')
             . ' ORDER BY c.nombre ASC, p.nombre ASC';

        $filas = $this->consultar($sql);

        foreach ($filas as &$f) {
            $stock  = (int) $f['stock_actual'];
            $minimo = (int) $f['stock_minimo'];
            $compra = (float) $f['precio_compra'];
            $venta  = (float) $f['precio_venta'];
            $ultimo = $f['ultimo_costo_compra'] !== null ? (float) $f['ultimo_costo_compra'] : 0.0;

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

            if ($stock <= 0) {
                $situacion = 'agotado';
            } elseif ($minimo > 0 && $stock <= $minimo) {
                $situacion = 'bajo';
            } else {
                $situacion = 'ok';
            }

            $f['stock']          = $stock;
            $f['minimo']         = $minimo;
            $f['precio_venta']   = $venta;
            $f['costo_unitario'] = $costo > 0 ? $costo : null;
            $f['origen_costo']   = $origen;
            $f['valor_costo']    = $costo > 0 ? $this->dec((int) round($stock * $costo * 100)) : null;
            $f['valor_venta']    = $venta > 0 ? $this->dec((int) round($stock * $venta * 100)) : null;
            $f['situacion']      = $situacion;
        }
        unset($f);

        return $filas;
    }

    /**
     * Filtra el inventario ya cargado (el catálogo es pequeño: más simple que otra consulta).
     * $q busca en código y nombre; $idCategoria y $situacion pueden ser null (= todos).
     */
    public function filtrarInventario(array $filas, string $q, ?int $idCategoria, ?string $situacion): array
    {
        $q = mb_strtolower(trim($q), 'UTF-8');

        return array_values(array_filter($filas, function ($f) use ($q, $idCategoria, $situacion) {
            if ($idCategoria !== null && (int) $f['id_categoria'] !== $idCategoria) {
                return false;
            }
            if ($situacion !== null && $f['situacion'] !== $situacion) {
                return false;
            }
            if ($q !== '') {
                $texto = mb_strtolower($f['codigo'] . ' ' . $f['nombre'], 'UTF-8');
                if (mb_strpos($texto, $q, 0, 'UTF-8') === false) {
                    return false;
                }
            }
            return true;
        }));
    }

    /** Totales de las filas que se están mostrando. */
    public function totalesInventario(array $filas): array
    {
        $unidades = 0;
        $centCosto = 0;
        $centVenta = 0;
        $agotados = 0;
        $bajos = 0;
        $sinCosto = 0;
        $sinPrecio = 0;

        foreach ($filas as $f) {
            $unidades += $f['stock'];
            if ($f['situacion'] === 'agotado') {
                $agotados++;
            } elseif ($f['situacion'] === 'bajo') {
                $bajos++;
            }
            if ($f['valor_costo'] !== null) {
                $centCosto += aCentavos($f['valor_costo']);
            } elseif ($f['stock'] > 0) {
                $sinCosto++;      // tiene existencias pero no se sabe cuánto costaron
            }
            if ($f['valor_venta'] !== null) {
                $centVenta += aCentavos($f['valor_venta']);
            } elseif ($f['stock'] > 0) {
                $sinPrecio++;
            }
        }

        return [
            'productos'   => count($filas),
            'unidades'    => $unidades,
            'valor_costo' => $this->dec($centCosto),
            'valor_venta' => $this->dec($centVenta),
            'agotados'    => $agotados,
            'bajos'       => $bajos,
            'sin_costo'   => $sinCosto,
            'sin_precio'  => $sinPrecio,
        ];
    }

    // =====================================================================
    // 3. GANANCIA POR PRODUCTO
    // =====================================================================

    /**
     * Ingresos, costo y ganancia de cada producto vendido entre $desde y $hasta (ambos incluidos).
     *
     * $orden: 'ganancia' | 'ingresos' | 'unidades' | 'margen' | 'nombre'.
     *
     * Cada fila trae:
     *   unidades, ingresos           todo lo vendido (sin IVA)
     *   costo, ganancia, margen      SOLO de las líneas con costo conocido
     *                                (null si el producto no tiene ninguna línea con costo)
     *   ingresos_sin_costo           lo vendido sin costo registrado: no entra en la ganancia
     *   pct_ganancia                 parte que aporta a la ganancia total
     * Más 'totales'. Todo en un arreglo: ['filas' => [...], 'totales' => [...]].
     */
    public function gananciaPorProducto(string $desde, string $hasta, ?int $idCategoria, string $orden = 'ganancia'): array
    {
        $params = [':ini' => $desde, ':fin' => $this->siguienteDia($hasta)];
        $filtroCategoria = '';
        if ($idCategoria !== null) {
            $filtroCategoria = ' AND p.id_categoria = :cat';
            $params[':cat']  = $idCategoria;
        }

        $filas = $this->consultar(
            "SELECT p.id_producto, p.codigo, p.nombre,
                    c.nombre AS nombre_categoria,
                    SUM(d.cantidad)         AS unidades,
                    SUM(d.subtotal_linea)   AS ingresos,
                    SUM(CASE WHEN d.costo_unitario IS NULL THEN 0 ELSE d.cantidad END)                  AS unidades_con_costo,
                    SUM(CASE WHEN d.costo_unitario IS NULL THEN 0 ELSE d.subtotal_linea END)            AS ingresos_con_costo,
                    SUM(CASE WHEN d.costo_unitario IS NULL THEN 0 ELSE d.cantidad * d.costo_unitario END) AS costo,
                    SUM(CASE WHEN d.costo_unitario IS NULL THEN d.subtotal_linea ELSE 0 END)            AS ingresos_sin_costo
               FROM detalle_ventas d
              INNER JOIN ventas v     ON v.id_venta = d.id_venta
              INNER JOIN productos p  ON p.id_producto = d.id_producto
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
              WHERE v.estado = 1
                AND v.fecha_venta >= :ini
                AND v.fecha_venta < :fin
                $filtroCategoria
              GROUP BY p.id_producto, p.codigo, p.nombre, c.nombre",
            $params
        );

        $totIngresos = 0;        // todo lo vendido
        $totConCosto = 0;        // lo vendido con costo conocido (base del margen)
        $totCosto    = 0;
        $totSinCosto = 0;        // lo vendido sin costo
        $totUnidades = 0;
        $unidadesSinCosto = 0;
        $productosSinCosto = 0;

        foreach ($filas as &$f) {
            $ingresos   = aCentavos($f['ingresos']);
            $conCosto   = aCentavos($f['ingresos_con_costo']);
            $costo      = aCentavos($f['costo']);
            $sinCosto   = aCentavos($f['ingresos_sin_costo']);
            $unidades   = (int) $f['unidades'];
            $uConCosto  = (int) $f['unidades_con_costo'];

            $tieneCosto = $uConCosto > 0;
            $ganancia   = $tieneCosto ? $conCosto - $costo : null;

            $f['unidades']           = $unidades;
            $f['unidades_sin_costo'] = $unidades - $uConCosto;
            $f['_ganancia_c']        = $ganancia;
            $f['ingresos']           = $this->dec($ingresos);
            $f['ingresos_sin_costo'] = $this->dec($sinCosto);
            $f['costo']              = $tieneCosto ? $this->dec($costo) : null;
            $f['ganancia']           = $ganancia !== null ? $this->dec($ganancia) : null;
            $f['margen']             = ($tieneCosto && $conCosto > 0) ? $ganancia / $conCosto * 100 : null;
            unset($f['ingresos_con_costo'], $f['unidades_con_costo']);

            $totIngresos += $ingresos;
            $totSinCosto += $sinCosto;
            $totUnidades += $unidades;
            if ($tieneCosto) {
                $totConCosto += $conCosto;
                $totCosto    += $costo;
            }
            if ($f['unidades_sin_costo'] > 0) {
                $unidadesSinCosto += $f['unidades_sin_costo'];
                $productosSinCosto++;
            }
        }
        unset($f);

        $totGanancia = $totConCosto - $totCosto;

        // Participación de cada producto en la ganancia total (solo tiene sentido si ésta es positiva).
        foreach ($filas as &$f) {
            $f['pct_ganancia'] = ($f['_ganancia_c'] !== null && $totGanancia > 0)
                ? $f['_ganancia_c'] / $totGanancia * 100
                : null;
            unset($f['_ganancia_c']);
        }
        unset($f);

        $this->ordenarGanancias($filas, $orden);

        return [
            'filas'    => $filas,
            'totales'  => [
                'productos'           => count($filas),
                'unidades'            => $totUnidades,
                'ingresos'            => $this->dec($totIngresos),
                'ingresos_con_costo'  => $this->dec($totConCosto),
                'costo'               => $this->dec($totCosto),
                'ganancia'            => $this->dec($totGanancia),
                'margen'              => $totConCosto > 0 ? $totGanancia / $totConCosto * 100 : null,
                'ingresos_sin_costo'  => $this->dec($totSinCosto),
                'unidades_sin_costo'  => $unidadesSinCosto,
                'productos_sin_costo' => $productosSinCosto,
            ],
        ];
    }

    /** Ordena las filas de ganancias. Los valores null (sin costo) van siempre al final. */
    private function ordenarGanancias(array &$filas, string $orden): void
    {
        $campos = [
            'ganancia' => 'ganancia',
            'ingresos' => 'ingresos',
            'unidades' => 'unidades',
            'margen'   => 'margen',
        ];

        usort($filas, function ($a, $b) use ($orden, $campos) {
            if (isset($campos[$orden])) {
                $campo = $campos[$orden];
                $va = $a[$campo];
                $vb = $b[$campo];
                if ($va === null && $vb !== null) {
                    return 1;
                }
                if ($va !== null && $vb === null) {
                    return -1;
                }
                if ($va !== null && $va != $vb) {
                    return $vb <=> $va;           // mayor primero
                }
            }
            return strcasecmp($a['nombre'], $b['nombre']);
        });
    }

    // =====================================================================
    // 4. AUMENTO Y DISMINUCIÓN DE LAS VENTAS
    // =====================================================================

    /**
     * Compara las ventas de cada producto entre dos períodos.
     *
     * $actual y $base: ['desde' => 'Y-m-d', 'hasta' => 'Y-m-d'] (ambos días incluidos).
     * $metrica   'unidades' | 'monto': con cuál se decide si un producto subió o bajó.
     * $tendencia null (todos) | 'aumento' | 'disminucion' | 'igual': filtro de las filas.
     *
     * Cada fila trae las unidades y el monto (sin IVA) de cada período, la diferencia y el
     * porcentaje (null si el período base no tenía ventas: no hay base para comparar), y:
     *   tendencia   'aumento' | 'disminucion' | 'igual' | 'nuevo' | 'sin_ventas'
     *               nuevo      = no se vendió en el período base y sí en el actual
     *               sin_ventas = se vendió en el período base y NO en el actual (-100 %)
     * Los productos sin ventas en ninguno de los dos períodos no aparecen.
     *
     * Devuelve ['filas' => [...], 'totales' => [...], 'conteo' => [...]]. El conteo se hace
     * ANTES de aplicar el filtro de tendencia, para que las tarjetas no cambien al filtrar.
     */
    public function variacionDeVentas(array $actual, array $base, ?int $idCategoria, string $metrica = 'unidades', ?string $tendencia = null): array
    {
        $finActual = $this->siguienteDia($actual['hasta']);
        $finBase   = $this->siguienteDia($base['hasta']);

        // Cada parámetro con nombre solo puede usarse una vez: se numeran (1, 2, 3).
        $params = [];
        for ($i = 1; $i <= 3; $i++) {
            $params[":a_ini$i"] = $actual['desde'];
            $params[":a_fin$i"] = $finActual;
            $params[":b_ini$i"] = $base['desde'];
            $params[":b_fin$i"] = $finBase;
        }
        $filtroCategoria = '';
        if ($idCategoria !== null) {
            $filtroCategoria = ' AND p.id_categoria = :cat';
            $params[':cat']  = $idCategoria;
        }

        $filas = $this->consultar(
            "SELECT p.id_producto, p.codigo, p.nombre,
                    c.nombre AS nombre_categoria,
                    SUM(CASE WHEN v.fecha_venta >= :a_ini1 AND v.fecha_venta < :a_fin1 THEN d.cantidad ELSE 0 END)       AS unidades_actual,
                    SUM(CASE WHEN v.fecha_venta >= :b_ini1 AND v.fecha_venta < :b_fin1 THEN d.cantidad ELSE 0 END)       AS unidades_base,
                    SUM(CASE WHEN v.fecha_venta >= :a_ini2 AND v.fecha_venta < :a_fin2 THEN d.subtotal_linea ELSE 0 END) AS monto_actual,
                    SUM(CASE WHEN v.fecha_venta >= :b_ini2 AND v.fecha_venta < :b_fin2 THEN d.subtotal_linea ELSE 0 END) AS monto_base
               FROM detalle_ventas d
              INNER JOIN ventas v     ON v.id_venta = d.id_venta
              INNER JOIN productos p  ON p.id_producto = d.id_producto
              INNER JOIN categorias c ON c.id_categoria = p.id_categoria
              WHERE v.estado = 1
                AND ((v.fecha_venta >= :a_ini3 AND v.fecha_venta < :a_fin3)
                  OR (v.fecha_venta >= :b_ini3 AND v.fecha_venta < :b_fin3))
                $filtroCategoria
              GROUP BY p.id_producto, p.codigo, p.nombre, c.nombre",
            $params
        );

        $conteo = ['aumento' => 0, 'disminucion' => 0, 'igual' => 0, 'nuevo' => 0, 'sin_ventas' => 0];
        $tot = ['ua' => 0, 'ub' => 0, 'ma' => 0, 'mb' => 0];
        $resultado = [];

        foreach ($filas as $f) {
            $ua = (int) $f['unidades_actual'];
            $ub = (int) $f['unidades_base'];
            $ma = aCentavos($f['monto_actual']);
            $mb = aCentavos($f['monto_base']);

            // La métrica elegida decide la tendencia; ambas se muestran siempre.
            $a = ($metrica === 'monto') ? $ma : $ua;
            $b = ($metrica === 'monto') ? $mb : $ub;
            if ($b === 0 && $a > 0) {
                $t = 'nuevo';
            } elseif ($a === 0 && $b > 0) {
                $t = 'sin_ventas';
            } elseif ($a > $b) {
                $t = 'aumento';
            } elseif ($a < $b) {
                $t = 'disminucion';
            } else {
                $t = 'igual';
            }
            $conteo[$t]++;

            $tot['ua'] += $ua;
            $tot['ub'] += $ub;
            $tot['ma'] += $ma;
            $tot['mb'] += $mb;

            $resultado[] = [
                'id_producto'      => (int) $f['id_producto'],
                'codigo'           => $f['codigo'],
                'nombre'           => $f['nombre'],
                'nombre_categoria' => $f['nombre_categoria'],
                'unidades_actual'  => $ua,
                'unidades_base'    => $ub,
                'dif_unidades'     => $ua - $ub,
                'pct_unidades'     => $ub > 0 ? ($ua - $ub) / $ub * 100 : null,
                'monto_actual'     => $this->dec($ma),
                'monto_base'       => $this->dec($mb),
                'dif_monto'        => $this->dec($ma - $mb),
                'pct_monto'        => $mb > 0 ? ($ma - $mb) / $mb * 100 : null,
                'tendencia'        => $t,
                '_orden'           => ($metrica === 'monto') ? ($ma - $mb) : ($ua - $ub),
            ];
        }

        // Los que más subieron arriba, los que más bajaron abajo; empate: por nombre.
        usort($resultado, function ($x, $y) {
            if ($x['_orden'] !== $y['_orden']) {
                return $y['_orden'] <=> $x['_orden'];
            }
            return strcasecmp($x['nombre'], $y['nombre']);
        });

        if ($tendencia !== null) {
            $resultado = array_values(array_filter($resultado, function ($r) use ($tendencia) {
                if ($tendencia === 'aumento') {
                    return $r['tendencia'] === 'aumento' || $r['tendencia'] === 'nuevo';
                }
                if ($tendencia === 'disminucion') {
                    return $r['tendencia'] === 'disminucion' || $r['tendencia'] === 'sin_ventas';
                }
                return $r['tendencia'] === 'igual';
            }));
        }
        foreach ($resultado as &$r) {
            unset($r['_orden']);
        }
        unset($r);

        return [
            'filas'   => $resultado,
            'totales' => [
                'unidades_actual' => $tot['ua'],
                'unidades_base'   => $tot['ub'],
                'dif_unidades'    => $tot['ua'] - $tot['ub'],
                'pct_unidades'    => $tot['ub'] > 0 ? ($tot['ua'] - $tot['ub']) / $tot['ub'] * 100 : null,
                'monto_actual'    => $this->dec($tot['ma']),
                'monto_base'      => $this->dec($tot['mb']),
                'dif_monto'       => $this->dec($tot['ma'] - $tot['mb']),
                'pct_monto'       => $tot['mb'] > 0 ? ($tot['ma'] - $tot['mb']) / $tot['mb'] * 100 : null,
            ],
            'conteo'  => [
                // "Aumentaron" incluye a los productos nuevos; "Disminuyeron", a los que dejaron de venderse.
                'aumentaron'   => $conteo['aumento'] + $conteo['nuevo'],
                'disminuyeron' => $conteo['disminucion'] + $conteo['sin_ventas'],
                'iguales'      => $conteo['igual'],
                'nuevos'       => $conteo['nuevo'],
                'sin_ventas'   => $conteo['sin_ventas'],
            ],
        ];
    }
}
