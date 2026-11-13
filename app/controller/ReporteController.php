<?php
/**
 * app/controller/ReporteController.php
 *
 * Controlador del módulo de Reportes.
 * URL base: index.php?controller=reporte&action=...
 *
 * Acciones públicas (las que el router puede llamar):
 *   index        menú con los reportes disponibles para el rol       (cualquier usuario con sesión)
 *   historialCierres  listado de cierres de caja anteriores, con buscador (admin y operador)
 *   cierreCaja   cierre de caja de un día (abierto: se puede cerrar; cerrado: solo lectura e impresión)
 *   registrarCierre  (POST) guarda el cierre, cierra la sesión y vuelve al login   (admin y operador)
 *   inventario   existencias y valor del inventario                  (admin y operador; costos: solo admin)
 *   ganancias    ganancia por producto en un rango de fechas         (solo admin)
 *   variacion    aumento / disminución de las ventas por producto    (solo admin)
 *
 * Todos son de SOLO LECTURA (GET). Cada reporte se puede imprimir (botón de la vista) y
 * exportar a CSV agregando &exportar=csv a la URL (la vista trae el botón).
 *
 * El nombre del archivo lleva la R mayúscula a propósito: igual que VentaController, así
 * funciona también en servidores Linux (que distinguen mayúsculas en los nombres de archivo).
 *
 * Reglas de oro (las mismas del resto del sistema):
 *  - Toda entrada (fechas, categoría, orden...) se valida con una lista blanca; un valor
 *    inválido se ignora y se avisa, nunca se usa.
 *  - Los permisos se repiten aquí (exigirRol) y en la tabla de rutas de public/index.php.
 *  - Los costos y las ganancias solo los ve el administrador.
 */

require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../helpers/formato.php';
require_once __DIR__ . '/../helpers/reportes.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';
require_once __DIR__ . '/../model/reporteModel.php';
require_once __DIR__ . '/../model/cierreCajaModel.php';

class ReporteController
{
    /**
     * Roles (en MINÚSCULAS; el front controller normaliza el rol de la sesión).
     * Para abrir un reporte a otro rol, cámbialo aquí Y en la tabla de rutas de public/index.php.
     *
     *   Caja e inventario: el operador (almacén y ventas) los necesita en su trabajo diario.
     *   Ganancias y variación: información de gestión y de costos, solo para administración.
     */
    private const ROLES_CAJA       = ['admin', 'operador'];
    private const ROLES_INVENTARIO = ['admin', 'operador'];
    private const ROLES_GERENCIA   = ['admin'];
    private const ROLES_COSTOS     = ['admin'];

    private $modelo;
    private $cierres;   // cierres de caja guardados (único modelo de Reportes que escribe)

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->modelo  = new ReporteModel($pdo);
        $this->cierres = new CierreCajaModel($pdo);
    }

    // =====================================================================
    // MENÚ
    // =====================================================================

    public function index(): void
    {
        exigirSesion();
        evitarCache();

        $catalogo = [
            [
                'accion'      => 'historialCierres',   // primero el historial; desde ahí se abre o se hace un cierre
                'titulo'      => 'Cierre de caja diario',
                'descripcion' => 'Ventas del día por forma de pago, lo cobrado y las anulaciones. Con espacio para el conteo y las firmas.',
                'icono'       => 'fa-cash-register',
                'color'       => 'success',
                'roles'       => self::ROLES_CAJA,
            ],
            [
                'accion'      => 'inventario',
                'titulo'      => 'Reporte de inventario',
                'descripcion' => 'Existencias de cada producto, stock mínimo, productos agotados y valor del inventario.',
                'icono'       => 'fa-boxes-stacked',
                'color'       => 'info',
                'roles'       => self::ROLES_INVENTARIO,
            ],
            [
                'accion'      => 'ganancias',
                'titulo'      => 'Ganancia por producto',
                'descripcion' => 'Ingresos, costo y ganancia de cada producto vendido en el período que elijas, con su margen.',
                'icono'       => 'fa-coins',
                'color'       => 'warning',
                'roles'       => self::ROLES_GERENCIA,
            ],
            [
                'accion'      => 'variacion',
                'titulo'      => 'Aumento y disminución de ventas',
                'descripcion' => 'Compara las ventas de cada producto entre dos períodos para ver cuáles suben y cuáles bajan.',
                'icono'       => 'fa-chart-line',
                'color'       => 'danger',
                'roles'       => self::ROLES_GERENCIA,
            ],
        ];

        // Cada usuario ve solo los reportes que su rol puede abrir.
        $reportes = array_values(array_filter($catalogo, function ($r) {
            return tieneRol($r['roles']);
        }));

        require __DIR__ . '/../views/reportes/index.php';
    }

    // =====================================================================
    // 0. HISTORIAL DE CIERRES DE CAJA (vista previa del cierre diario)
    // =====================================================================

    private const CIERRES_POR_PAGINA = 15;

    public function historialCierres(): void
    {
        exigirRol(self::ROLES_CAJA);
        evitarCache();

        $hoy = date('Y-m-d');
        $q   = validarTexto($_GET['q'] ?? null, 1, 60) ?? '';

        $desde = $this->leerFecha('desde', null);
        $hasta = $this->leerFecha('hasta', null);
        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $pagina = validarEntero($_GET['pagina'] ?? null, 1, 100000) ?? 1;

        $datos = $this->generar(function () use ($desde, $hasta, $q, $pagina) {
            $total        = $this->modelo->contarCierres($desde, $hasta, $q);
            $totalPaginas = max(1, (int) ceil($total / self::CIERRES_POR_PAGINA));
            $pagina       = min($pagina, $totalPaginas);
            $filas        = $this->modelo->historialCierres(
                $desde, $hasta, $q, self::CIERRES_POR_PAGINA, ($pagina - 1) * self::CIERRES_POR_PAGINA
            );
            return ['total' => $total, 'totalPaginas' => $totalPaginas, 'pagina' => $pagina, 'filas' => $filas];
        });

        $totalCierres = $datos['total'];
        $totalPaginas = $datos['totalPaginas'];
        $pagina       = $datos['pagina'];
        $cierres      = $datos['filas'];

        // Marca cuáles de esos días ya tienen su cierre efectuado (para mostrarlos como "Cerrado").
        if ($cierres) {
            $efectuados = $this->generar(function () use ($cierres) {
                return $this->cierres->porRango(end($cierres)['dia'], reset($cierres)['dia']);
            });
            foreach ($cierres as &$fila) {
                $fila['cierre'] = $efectuados[$fila['dia']] ?? null;
            }
            unset($fila);
        }
        $hayFiltros   = ($q !== '' || $desde !== null || $hasta !== null);

        // Filtros que se arrastran al paginar.
        $filtrosUrl = $this->parametrosUrl(['q' => $q, 'desde' => $desde, 'hasta' => $hasta]);

        require __DIR__ . '/../views/reportes/historialCierres.php';
    }

    // =====================================================================
    // 1. CIERRE DE CAJA
    // =====================================================================

    public function cierreCaja(): void
    {
        exigirRol(self::ROLES_CAJA);
        evitarCache();

        $hoy = date('Y-m-d');
        $dia = $this->leerFecha('fecha', $hoy);

        $cierre = $this->generar(function () use ($dia) {
            return $this->modelo->cierreDeCaja($dia);
        });

        // ¿Ese día ya se cerró? Entonces solo se muestra (sin editar) y solo se imprime.
        $guardado = $this->generar(function () use ($dia) {
            return $this->cierres->obtener($dia);
        });
        $cerrado   = !empty($guardado);
        $hayVentas = !empty($cierre['ventas']);   // sin ventas no hay nada que cerrar

        if ($this->pideCsv()) {
            if ($cerrado) {
                setFlash('aviso', 'Un cierre ya efectuado solo se puede imprimir.');
                redirigir(urlAccion('reporte', 'cierreCaja', ['fecha' => $dia]));
            }
            $this->csvCierreCaja($cierre);
        }

        // Efectivo que el sistema espera en gaveta: solo lo cobrado en EFECTIVO (no tarjeta, transferencia...).
        $efectivoSistema = (float) ($cierre['pagos']['efectivo']['monto'] ?? 0);

        $diaAnterior  = (new DateTime($dia))->modify('-1 day')->format('Y-m-d');
        $diaSiguiente = ($dia < $hoy) ? (new DateTime($dia))->modify('+1 day')->format('Y-m-d') : null;
        $esHoy        = ($dia === $hoy);
        $urlCsv       = $cerrado ? null : urlAccion('reporte', 'cierreCaja', ['fecha' => $dia, 'exportar' => 'csv']);

        require __DIR__ . '/../views/reportes/cierreCaja.php';
    }

    /**
     * Efectúa el cierre de caja de un día: guarda el arqueo, CIERRA LA SESIÓN y vuelve al login
     * con el aviso "Cierre exitoso". Todo se recalcula en el servidor; del formulario solo se
     * toman el día, el efectivo contado y las observaciones (nunca los totales).
     */
    public function registrarCierre(): void
    {
        exigirRol(self::ROLES_CAJA);
        $volver = urlAccion('reporte', 'historialCierres');
        exigirPostConCsrf($volver);

        $dia = validarFecha($_POST['fecha'] ?? null, false, 50);
        if ($dia === null) {
            setFlash('error', 'La fecha del cierre no es válida.');
            redirigir($volver);
        }
        $urlCierre = urlAccion('reporte', 'cierreCaja', ['fecha' => $dia]);

        $contado = validarDecimal($_POST['efectivo_contado'] ?? null, 8, 2, true);
        if ($contado === null) {
            setFlash('error', 'Indica el efectivo contado en gaveta (un monto válido, con máximo 2 decimales).');
            redirigir($urlCierre);
        }

        $obsCruda = trim((string) ($_POST['observaciones'] ?? ''));
        $observaciones = null;
        if ($obsCruda !== '') {
            $observaciones = validarTexto($obsCruda, 1, 500);
            if ($observaciones === null) {
                setFlash('error', 'Las observaciones no pueden pasar de 500 caracteres.');
                redirigir($urlCierre);
            }
        }

        $c = $this->generar(function () use ($dia) {
            return $this->modelo->cierreDeCaja($dia);
        });
        if (empty($c['ventas'])) {
            setFlash('aviso', 'No hubo ventas en esa fecha: no hay nada que cerrar.');
            redirigir($urlCierre);
        }

        $efectivoSistema = $c['pagos']['efectivo']['monto'] ?? 0.0;
        $diferencia      = aCentavos($contado) - aCentavos($efectivoSistema);

        $resultado = $this->cierres->registrar([
            'dia'              => $dia,
            'facturas'         => $c['facturas'],
            'total'            => deCentavos(aCentavos($c['total'])),
            'cobrado'          => deCentavos(aCentavos($c['cobrado'])),
            'efectivo_sistema' => deCentavos(aCentavos($efectivoSistema)),
            'efectivo_contado' => $contado,
            'diferencia'       => deCentavos($diferencia),
            'observaciones'    => $observaciones,
            'id_usuario'       => usuarioActualId(),
        ]);

        if (!$resultado['ok']) {
            if ($resultado['motivo'] === 'duplicado') {
                setFlash('aviso', 'Ese día ya tenía su cierre efectuado.');
            } else {
                setFlash('error', 'No se pudo guardar el cierre. Verifica que la base de datos tenga aplicada la migración migracion_cierres_caja.sql.');
            }
            redirigir($urlCierre);
        }

        // Cierre guardado: se termina la sesión (fin de turno) y se vuelve al login con el aviso.
        // No se usa session_destroy() porque el aviso (flash) vive en la sesión.
        $_SESSION = [];
        session_regenerate_id(true);
        setFlash('exito', 'Cierre exitoso: el cierre de caja del ' . formatoFecha($dia) . ' quedó registrado y la sesión se cerró.');
        redirigir(URL_LOGIN);
    }

    // =====================================================================
    // 2. INVENTARIO
    // =====================================================================

    public function inventario(): void
    {
        exigirRol(self::ROLES_INVENTARIO);
        evitarCache();

        $verCostos        = tieneRol(self::ROLES_COSTOS);
        $incluirInactivos = (($_GET['inactivos'] ?? '') === '1');
        $q                = validarTexto($_GET['q'] ?? null, 1, 60) ?? '';
        $idCategoria      = validarEntero($_GET['categoria'] ?? null, 1);
        $situacion        = validarClave($_GET['situacion'] ?? null, ['agotado' => true, 'bajo' => true, 'ok' => true]);

        $datos = $this->generar(function () use ($incluirInactivos, $q, $idCategoria, $situacion) {
            $todos = $this->modelo->inventario($incluirInactivos);
            $filas = $this->modelo->filtrarInventario($todos, $q, $idCategoria, $situacion);
            return [
                'filas'      => $filas,
                'totales'    => $this->modelo->totalesInventario($filas),
                'categorias' => $this->modelo->categorias(),
            ];
        });

        $filas      = $datos['filas'];
        $totales    = $datos['totales'];
        $categorias = $datos['categorias'];

        // El operador no ve costos: se quitan de los datos, no solo de la pantalla.
        if (!$verCostos) {
            foreach ($filas as &$fila) {
                $fila['costo_unitario'] = null;
                $fila['valor_costo']    = null;
            }
            unset($fila);
            $totales['valor_costo'] = null;
            $totales['sin_costo']   = 0;
        }

        if ($this->pideCsv()) {
            $this->csvInventario($filas, $totales, $verCostos);
        }

        $filtros = [
            'q'         => $q,
            'categoria' => $idCategoria,
            'situacion' => $situacion,
            'inactivos' => $incluirInactivos,
        ];
        $hayFiltros = ($q !== '' || $idCategoria !== null || $situacion !== null || $incluirInactivos);
        $urlCsv = urlAccion('reporte', 'inventario', $this->parametrosUrl([
            'q'         => $q,
            'categoria' => $idCategoria,
            'situacion' => $situacion,
            'inactivos' => $incluirInactivos ? '1' : null,
            'exportar'  => 'csv',
        ]));

        require __DIR__ . '/../views/reportes/inventario.php';
    }

    // =====================================================================
    // 3. GANANCIA POR PRODUCTO
    // =====================================================================

    public function ganancias(): void
    {
        exigirRol(self::ROLES_GERENCIA);
        evitarCache();

        $hoy = date('Y-m-d');
        [$desde, $hasta] = $this->leerRango($hoy);
        $idCategoria = validarEntero($_GET['categoria'] ?? null, 1);
        $orden = validarClave($_GET['orden'] ?? null, [
            'ganancia' => true, 'ingresos' => true, 'unidades' => true, 'margen' => true, 'nombre' => true,
        ]) ?? 'ganancia';

        $datos = $this->generar(function () use ($desde, $hasta, $idCategoria, $orden) {
            $r = $this->modelo->gananciaPorProducto($desde, $hasta, $idCategoria, $orden);
            $r['categorias'] = $this->modelo->categorias();
            return $r;
        });

        $filas      = $datos['filas'];
        $totales    = $datos['totales'];
        $categorias = $datos['categorias'];

        if ($this->pideCsv()) {
            $this->csvGanancias($filas, $totales, $desde, $hasta);
        }

        $nombresOrden = [
            'ganancia' => 'Mayor ganancia',
            'ingresos' => 'Mayores ingresos',
            'unidades' => 'Más unidades vendidas',
            'margen'   => 'Mayor margen',
            'nombre'   => 'Nombre (A-Z)',
        ];
        $urlCsv = urlAccion('reporte', 'ganancias', $this->parametrosUrl([
            'desde'     => $desde,
            'hasta'     => $hasta,
            'categoria' => $idCategoria,
            'orden'     => $orden,
            'exportar'  => 'csv',
        ]));

        require __DIR__ . '/../views/reportes/ganancias.php';
    }

    // =====================================================================
    // 4. AUMENTO Y DISMINUCIÓN DE LAS VENTAS
    // =====================================================================

    public function variacion(): void
    {
        exigirRol(self::ROLES_GERENCIA);
        evitarCache();

        $hoy = date('Y-m-d');
        [$desde, $hasta] = $this->leerRango($hoy);
        $idCategoria = validarEntero($_GET['categoria'] ?? null, 1);
        $metrica     = validarClave($_GET['metrica'] ?? null, ['unidades' => true, 'monto' => true]) ?? 'unidades';
        $tendencia   = validarClave($_GET['tendencia'] ?? null, ['aumento' => true, 'disminucion' => true, 'igual' => true]);

        // Período base: el que el usuario indique, o (si lo deja vacío) el período INMEDIATAMENTE
        // anterior y de la misma duración. Ej.: 01/10 al 30/10 (30 días) => 01/09 al 30/09.
        $baseDesdeCrudo = $_GET['base_desde'] ?? '';
        $baseHastaCrudo = $_GET['base_hasta'] ?? '';
        $baseDesde = ($baseDesdeCrudo !== '') ? $this->leerFecha('base_desde', null) : null;
        $baseHasta = ($baseHastaCrudo !== '') ? $this->leerFecha('base_hasta', null) : null;

        $baseManual = false;
        if ($baseDesde !== null && $baseHasta !== null) {
            if ($baseDesde > $baseHasta) {
                [$baseDesde, $baseHasta] = [$baseHasta, $baseDesde];
            }
            $baseManual = true;
        } else {
            if ($baseDesdeCrudo !== '' || $baseHastaCrudo !== '') {
                setFlash('aviso', 'Para elegir el período base indica las dos fechas (desde y hasta). Se usó el período anterior de igual duración.');
            }
            $dias      = (int) (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
            $baseHasta = (new DateTime($desde))->modify('-1 day')->format('Y-m-d');
            $baseDesde = (new DateTime($baseHasta))->modify('-' . ($dias - 1) . ' days')->format('Y-m-d');
        }

        $actual = ['desde' => $desde,     'hasta' => $hasta];
        $base   = ['desde' => $baseDesde, 'hasta' => $baseHasta];

        $datos = $this->generar(function () use ($actual, $base, $idCategoria, $metrica, $tendencia) {
            $r = $this->modelo->variacionDeVentas($actual, $base, $idCategoria, $metrica, $tendencia);
            $r['categorias'] = $this->modelo->categorias();
            return $r;
        });

        $filas      = $datos['filas'];
        $totales    = $datos['totales'];
        $conteo     = $datos['conteo'];
        $categorias = $datos['categorias'];

        if ($this->pideCsv()) {
            $this->csvVariacion($filas, $totales, $actual, $base);
        }

        $diasActual = (int) (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
        $diasBase   = (int) (new DateTime($baseDesde))->diff(new DateTime($baseHasta))->days + 1;
        $urlCsv = urlAccion('reporte', 'variacion', $this->parametrosUrl([
            'desde'      => $desde,
            'hasta'      => $hasta,
            'base_desde' => $baseManual ? $baseDesde : null,
            'base_hasta' => $baseManual ? $baseHasta : null,
            'categoria'  => $idCategoria,
            'metrica'    => $metrica,
            'tendencia'  => $tendencia,
            'exportar'   => 'csv',
        ]));

        require __DIR__ . '/../views/reportes/variacion.php';
    }

    // =====================================================================
    // ENTRADAS (fechas y rangos)
    // =====================================================================

    /**
     * Lee una fecha ('Y-m-d') de $_GET. Si falta, devuelve $defecto. Si es inválida
     * (formato, día inexistente, futura o de hace más de 50 años) la ignora y avisa.
     */
    private function leerFecha(string $campo, ?string $defecto): ?string
    {
        $crudo = $_GET[$campo] ?? '';
        if ($crudo === '') {
            return $defecto;
        }

        $fecha = validarFecha($crudo, false, 50);
        if ($fecha === null) {
            setFlash('aviso', 'Se ignoró una fecha no válida (no puede ser futura).');
            return $defecto;
        }
        return $fecha;
    }

    /**
     * Rango desde/hasta del período actual. Por defecto: del día 1 del mes en curso hasta hoy.
     * Si vienen al revés, se intercambian (igual que el listado de Ventas).
     */
    private function leerRango(string $hoy): array
    {
        $desde = $this->leerFecha('desde', date('Y-m-01'));
        $hasta = $this->leerFecha('hasta', $hoy);

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        return [$desde, $hasta];
    }

    /** ¿Piden el archivo CSV en lugar de la página? */
    private function pideCsv(): bool
    {
        return (($_GET['exportar'] ?? '') === 'csv');
    }

    /** Quita los parámetros vacíos para armar URLs limpias. */
    private function parametrosUrl(array $parametros): array
    {
        return array_filter($parametros, function ($v) {
            return $v !== null && $v !== '';
        });
    }

    /**
     * Ejecuta la consulta del reporte. Si la base de datos falla (por ejemplo, falta aplicar
     * migracion_pagos_factura.sql) se deja el detalle en el log del servidor, se avisa al
     * usuario con un mensaje claro y se vuelve al menú, en lugar de mostrar un error técnico.
     */
    private function generar(callable $consulta): array
    {
        try {
            return $consulta();
        } catch (PDOException $e) {
            error_log('ReporteController -> ' . $e->getMessage());
            setFlash('error', 'No se pudo generar el reporte. Verifica que la base de datos tenga aplicadas las migraciones migracion_pagos_factura.sql y migracion_cierres_caja.sql.');
            redirigir(urlAccion('reporte', 'index'));
        }
    }

    // =====================================================================
    // EXPORTACIÓN A CSV (una función por reporte: reutilizan los mismos datos de la pantalla)
    // =====================================================================

    private function csvCierreCaja(array $c): void
    {
        $filasPago = [];
        foreach (METODOS_PAGO as $clave => $etiqueta) {
            $p = $c['pagos'][$clave] ?? ['facturas' => 0, 'monto' => 0.0];
            $filasPago[] = [$etiqueta, $p['facturas'], $p['monto']];
        }
        $filasPago[] = ['TOTAL COBRADO', null, $c['cobrado']];
        $filasPago[] = ['TOTAL VENDIDO (con IVA)', $c['facturas'], $c['total']];

        $filasVentas = [];
        foreach ($c['ventas'] as $v) {
            $filasVentas[] = [
                $v['numero_factura'],
                formatoFecha($v['fecha_venta'], true),
                $v['cliente'],
                $v['usuario'],
                TIPOS_PAGO_VENTA[$v['tipo_pago']] ?? $v['tipo_pago'],
                ((int) $v['estado'] === 1) ? 'Registrada' : 'Anulada',
                (float) $v['total'],
            ];
        }

        enviarCsv('cierre_caja_' . $c['dia'] . '.csv', [
            [
                'titulo'      => 'Cierre de caja del ' . formatoFecha($c['dia']),
                'encabezados' => ['Forma de pago', 'Facturas', 'Monto'],
                'filas'       => $filasPago,
            ],
            [
                'titulo'      => 'Ventas del día',
                'encabezados' => ['Factura', 'Fecha y hora', 'Cliente', 'Usuario', 'Forma de pago', 'Estado', 'Total'],
                'filas'       => $filasVentas,
            ],
        ]);
    }

    private function csvInventario(array $filas, array $totales, bool $verCostos): void
    {
        $encabezados = ['Código', 'Producto', 'Categoría', 'Stock', 'Stock mínimo', 'Situación'];
        if ($verCostos) {
            $encabezados[] = 'Costo unitario';
        }
        $encabezados[] = 'Precio de venta';
        if ($verCostos) {
            $encabezados[] = 'Valor al costo';
        }
        $encabezados[] = 'Valor a precio de venta';

        $nombresSituacion = ['agotado' => 'Agotado', 'bajo' => 'Stock bajo', 'ok' => 'Disponible'];

        $datos = [];
        foreach ($filas as $f) {
            $fila = [$f['codigo'], $f['nombre'], $f['nombre_categoria'], $f['stock'], $f['minimo'], $nombresSituacion[$f['situacion']]];
            if ($verCostos) {
                $fila[] = $f['costo_unitario'];
            }
            $fila[] = $f['precio_venta'] > 0 ? (float) $f['precio_venta'] : null;
            if ($verCostos) {
                $fila[] = $f['valor_costo'];
            }
            $fila[] = $f['valor_venta'];
            $datos[] = $fila;
        }

        $total = ['TOTAL', null, null, $totales['unidades'], null, null];
        if ($verCostos) {
            $total[] = null;
        }
        $total[] = null;
        if ($verCostos) {
            $total[] = $totales['valor_costo'];
        }
        $total[] = $totales['valor_venta'];
        $datos[] = $total;

        enviarCsv('inventario_' . date('Y-m-d') . '.csv', [
            ['titulo' => 'Inventario al ' . date('d/m/Y H:i'), 'encabezados' => $encabezados, 'filas' => $datos],
        ]);
    }

    private function csvGanancias(array $filas, array $t, string $desde, string $hasta): void
    {
        $datos = [];
        foreach ($filas as $f) {
            $datos[] = [
                $f['codigo'], $f['nombre'], $f['nombre_categoria'], $f['unidades'],
                $f['ingresos'], $f['costo'], $f['ganancia'],
                $f['margen'] !== null ? round($f['margen'], 2) : null,
                $f['unidades_sin_costo'],
            ];
        }
        $datos[] = [
            'TOTAL', null, null, $t['unidades'], $t['ingresos'], $t['costo'], $t['ganancia'],
            $t['margen'] !== null ? round($t['margen'], 2) : null, $t['unidades_sin_costo'],
        ];

        enviarCsv('ganancias_' . $desde . '_a_' . $hasta . '.csv', [[
            'titulo'      => 'Ganancia por producto del ' . formatoFecha($desde) . ' al ' . formatoFecha($hasta) . ' (valores sin IVA)',
            'encabezados' => ['Código', 'Producto', 'Categoría', 'Unidades', 'Ingresos', 'Costo', 'Ganancia', 'Margen %', 'Unidades sin costo registrado'],
            'filas'       => $datos,
        ]]);
    }

    private function csvVariacion(array $filas, array $t, array $actual, array $base): void
    {
        $nombresTendencia = [
            'aumento' => 'Aumento', 'disminucion' => 'Disminución', 'igual' => 'Sin cambio',
            'nuevo' => 'Nuevo en el período', 'sin_ventas' => 'Sin ventas en el período',
        ];

        $datos = [];
        foreach ($filas as $f) {
            $datos[] = [
                $f['codigo'], $f['nombre'], $f['nombre_categoria'],
                $f['unidades_base'], $f['unidades_actual'], $f['dif_unidades'],
                $f['pct_unidades'] !== null ? round($f['pct_unidades'], 2) : null,
                $f['monto_base'], $f['monto_actual'], $f['dif_monto'],
                $f['pct_monto'] !== null ? round($f['pct_monto'], 2) : null,
                $nombresTendencia[$f['tendencia']],
            ];
        }
        $datos[] = [
            'TOTAL', null, null,
            $t['unidades_base'], $t['unidades_actual'], $t['dif_unidades'],
            $t['pct_unidades'] !== null ? round($t['pct_unidades'], 2) : null,
            $t['monto_base'], $t['monto_actual'], $t['dif_monto'],
            $t['pct_monto'] !== null ? round($t['pct_monto'], 2) : null,
            null,
        ];

        $titulo = 'Variación de ventas. Actual: ' . formatoFecha($actual['desde']) . ' al ' . formatoFecha($actual['hasta'])
                . ' | Base: ' . formatoFecha($base['desde']) . ' al ' . formatoFecha($base['hasta']) . ' (montos sin IVA)';

        enviarCsv('variacion_ventas_' . $actual['desde'] . '_a_' . $actual['hasta'] . '.csv', [[
            'titulo'      => $titulo,
            'encabezados' => [
                'Código', 'Producto', 'Categoría',
                'Unidades base', 'Unidades actual', 'Diferencia unidades', 'Variación unidades %',
                'Monto base', 'Monto actual', 'Diferencia monto', 'Variación monto %', 'Tendencia',
            ],
            'filas'       => $datos,
        ]]);
    }
}
