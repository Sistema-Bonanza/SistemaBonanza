<?php
// Variables que entrega ReporteController::historialCierres():
// $cierres (filas de la página), $totalCierres, $pagina, $totalPaginas,
// $q, $desde, $hasta, $hayFiltros, $filtrosUrl, $hoy

// Apagamos los "gatillos" para que _inicio.php NO genere su barra de botones
// (esta vista trae su propio banner, igual que el cierre de caja).
$urlCsv             = null;
$subtituloImpresion = 'Historial de cierres de caja';
$esMenu             = true;
$tituloPagina       = 'Historial de cierres de caja';
$descripcionPagina  = '';

require __DIR__ . '/_inicio.php';
?>

<style type="text/css">
    @media print {
        aside, .no-print, .buttons, .navbar, footer, .pagination { display: none !important; }
        main, .container, body { background: white !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .box { border: 1px solid #cbd5e1 !important; box-shadow: none !important; }
    }
</style>

<!-- ========================================== -->
<!-- BANNER (mismo estilo del cierre de caja) -->
<!-- ========================================== -->
<div class="box mb-5 no-print" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">

        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 56px; height: 56px; background: rgba(255,255,255,0.12); border-radius: 12px; backdrop-filter: blur(4px); flex-shrink: 0;">
                <i class="fas fa-clock-rotate-left fa-lg has-text-white"></i>
            </div>
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0">Cierres de Caja</h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span>
                        Historial
                    </span>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 750px;">
                    Consulta los cierres de días anteriores o realiza el cierre de caja de hoy. Cada fila es un día con ventas; ábrela para ver su detalle completo.
                </p>
            </div>
        </div>

        <!-- Botones: volver a la vista inicial del módulo y realizar el cierre -->
        <div class="buttons mb-0">
            <a href="<?= esc(urlAccion('reporte', 'index')) ?>" class="button is-white is-outlined font-semibold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4); color: #ffffff;">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver a reportes</span>
            </a>
            <a href="<?= esc(urlAccion('reporte', 'cierreCaja')) ?>" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                <span class="icon"><i class="fas fa-cash-register"></i></span>
                <span>Realizar cierre de caja</span>
            </a>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- BUSCADOR Y FILTROS -->
<!-- ========================================== -->
<div class="box is-shadowless p-4 mb-5 no-print" style="background: white; border: 1px solid #cbd5e1; border-radius: 12px;">
    <form method="get" action="index.php" class="is-flex is-flex-wrap-wrap is-align-items-flex-end" style="gap: 1rem;">
        <input type="hidden" name="controller" value="reporte">
        <input type="hidden" name="action" value="historialCierres">

        <div class="field mb-0" style="flex: 1 1 300px;">
            <label for="buscarCierre" class="label is-size-7 is-uppercase">Buscar cierre</label>
            <div class="control has-icons-left">
                <input class="input" type="search" id="buscarCierre" name="q" value="<?= esc($q) ?>" maxlength="60"
                       placeholder="Fecha (15/10/2026 o 10/2026), factura, cliente o usuario..." style="border-radius: 8px; border-color: #cbd5e1;">
                <span class="icon is-left has-text-grey-light"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <div class="field mb-0">
            <label for="cierreDesde" class="label is-size-7 is-uppercase">Desde</label>
            <input class="input" type="date" id="cierreDesde" name="desde" max="<?= esc($hoy) ?>" value="<?= esc($desde ?? '') ?>" style="border-radius: 8px; border-color: #cbd5e1;">
        </div>
        <div class="field mb-0">
            <label for="cierreHasta" class="label is-size-7 is-uppercase">Hasta</label>
            <input class="input" type="date" id="cierreHasta" name="hasta" max="<?= esc($hoy) ?>" value="<?= esc($hasta ?? '') ?>" style="border-radius: 8px; border-color: #cbd5e1;">
        </div>

        <div class="buttons mb-0">
            <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                <span class="icon"><i class="fas fa-search"></i></span>
                <span>Buscar</span>
            </button>
            <?php if ($hayFiltros): ?>
                <a href="<?= esc(urlAccion('reporte', 'historialCierres')) ?>" class="button is-light" style="border-radius: 8px;">
                    <span class="icon"><i class="fas fa-xmark"></i></span>
                    <span>Limpiar</span>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- LISTADO DE CIERRES -->
<!-- ========================================== -->
<div class="box is-shadowless p-0 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
    <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">
            Cierres registrados <span class="has-text-grey font-weight-normal">(<?= (int) $totalCierres ?>)</span>
        </h3>
        <span class="tag is-link is-light font-weight-bold">
            <?= $hayFiltros ? 'Resultados de la búsqueda' : 'Del más reciente al más antiguo' ?>
        </span>
    </div>

    <div class="table-container">
        <table class="table is-fullwidth is-hoverable is-striped mb-0 text-sm">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th class="is-size-7 font-weight-bold is-uppercase">Fecha del cierre</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Facturas</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Total vendido</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Total cobrado</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">A crédito</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Anuladas</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Estado</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right no-print">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cierres)): ?>
                    <?php
                        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    ?>
                    <?php foreach ($cierres as $c): ?>
                        <?php
                            $urlCierre = urlAccion('reporte', 'cierreCaja', ['fecha' => $c['dia']]);
                            $esHoyFila = ($c['dia'] === $hoy);
                            $nombreDia = $diasSemana[(int) date('w', strtotime($c['dia']))];
                        ?>
                        <tr>
                            <td>
                                <a href="<?= esc($urlCierre) ?>" class="has-text-weight-bold has-text-link">
                                    <?= esc(formatoFecha($c['dia'])) ?>
                                </a>
                                <span class="is-size-7 has-text-grey ml-1"><?= esc($nombreDia) ?></span>
                                <?php if ($esHoyFila): ?>
                                    <span class="tag is-warning is-light is-small ml-1">Hoy · en curso</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-centered font-weight-semibold"><?= (int) $c['facturas'] ?></td>
                            <td class="has-text-right font-mono font-weight-bold has-text-dark"><?= esc(dinero($c['total'])) ?></td>
                            <td class="has-text-right font-mono font-weight-bold has-text-success"><?= esc(dinero($c['cobrado'])) ?></td>
                            <td class="has-text-right font-mono <?= $c['credito'] > 0 ? 'has-text-warning-dark font-weight-bold' : 'has-text-grey-light' ?>">
                                <?= esc(dinero($c['credito'])) ?>
                            </td>
                            <td class="has-text-centered">
                                <?php if ($c['anuladas'] > 0): ?>
                                    <span class="tag is-danger is-light" title="Por <?= esc(dinero($c['total_anulado'])) ?>"><?= (int) $c['anuladas'] ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey-light">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-centered">
                                <?php if (!empty($c['cierre'])): ?>
                                    <span class="tag is-dark is-light has-text-weight-bold" title="Cerrado el <?= esc(formatoFecha($c['cierre']['creado_en'], true)) ?> por <?= esc($c['cierre']['username']) ?>">
                                        <i class="fas fa-lock mr-1"></i> Cerrado
                                    </span>
                                    <p class="is-size-7 has-text-grey mt-1 mb-0">por <?= esc($c['cierre']['username']) ?></p>
                                <?php else: ?>
                                    <span class="tag is-warning is-light has-text-weight-bold">Sin cerrar</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-right no-print">
                                <div class="buttons is-right mb-0">
                                    <a href="<?= esc($urlCierre) ?>" class="button is-small is-link is-light has-text-weight-bold" title="Ver el cierre de este día">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                        <span><?= empty($c['cierre']) ? 'Ver cierre' : 'Ver / imprimir' ?></span>
                                    </a>
                                    <?php if (empty($c['cierre'])): ?>
                                        <a href="<?= esc(urlAccion('reporte', 'cierreCaja', ['fecha' => $c['dia'], 'exportar' => 'csv'])) ?>" class="button is-small is-light" title="Descargar CSV (Excel)">
                                            <span class="icon"><i class="fas fa-file-csv"></i></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="has-text-centered has-text-grey py-6">
                            <span class="icon is-large has-text-grey-light"><i class="fas fa-folder-open fa-2x"></i></span>
                            <p class="has-text-weight-bold mt-2">
                                <?= $hayFiltros ? 'No se encontraron cierres con esa búsqueda.' : 'Todavía no hay ventas registradas, así que no hay cierres.' ?>
                            </p>
                            <?php if ($hayFiltros): ?>
                                <p class="is-size-7 mt-1">Prueba con otra fecha (15/10/2026), un mes (10/2026), un número de factura o el nombre de un cliente.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="p-3 is-size-7 has-text-grey" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        Solo suman las ventas registradas; las anuladas se cuentan aparte. «Total cobrado» es lo que entró a caja (sin crédito). Los cierres «Cerrados» son de solo lectura: únicamente se pueden imprimir.
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($totalPaginas > 1): ?>
        <nav class="pagination is-centered p-4 no-print" role="navigation" aria-label="Paginación del historial de cierres">
            <?php if ($pagina > 1): ?>
                <a class="pagination-previous" href="<?= esc(urlAccion('reporte', 'historialCierres', $filtrosUrl + ['pagina' => $pagina - 1])) ?>">Anterior</a>
            <?php else: ?>
                <a class="pagination-previous" disabled>Anterior</a>
            <?php endif; ?>

            <?php if ($pagina < $totalPaginas): ?>
                <a class="pagination-next" href="<?= esc(urlAccion('reporte', 'historialCierres', $filtrosUrl + ['pagina' => $pagina + 1])) ?>">Siguiente</a>
            <?php else: ?>
                <a class="pagination-next" disabled>Siguiente</a>
            <?php endif; ?>

            <ul class="pagination-list">
                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li>
                        <?php if ($i === $pagina): ?>
                            <a class="pagination-link is-current" aria-current="page" aria-label="Página <?= $i ?>"><?= $i ?></a>
                        <?php else: ?>
                            <a class="pagination-link" aria-label="Ir a la página <?= $i ?>"
                               href="<?= esc(urlAccion('reporte', 'historialCierres', $filtrosUrl + ['pagina' => $i])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/_fin.php'; ?>
