<?php
// Variables que entrega ReporteController::ganancias():
//   $filas, $totales, $categorias, $desde, $hasta, $hoy, $idCategoria, $orden, $nombresOrden, $urlCsv
// Todos los montos van SIN IVA. Costo y ganancia solo cuentan las ventas con costo conocido.

$nombreCat = nombreCategoria($categorias, $idCategoria);

$tituloPagina       = 'Ganancia por producto';
$descripcionPagina  = 'Ingresos, costo y ganancia de cada producto vendido en el período.';
$subtituloImpresion = 'Período: ' . formatoFecha($desde) . ' al ' . formatoFecha($hasta)
                    . ($nombreCat !== null ? ' · Categoría: ' . $nombreCat : '')
                    . ' · Orden: ' . $nombresOrden[$orden]
                    . ' · Valores sin IVA';

// Los atajos de fecha conservan la categoría y el orden elegidos.
$conservar = array_filter(['categoria' => $idCategoria, 'orden' => $orden], function ($v) {
    return $v !== null && $v !== '';
});

// Lógica de color dinámica para KPI de Ganancia
$colorGanancia = ($totales['ganancia'] < 0) ? 'danger' : 'success';
$hexGanancia = ($totales['ganancia'] < 0) ? '#dc2626' : '#10b981';

require __DIR__ . '/_inicio.php';
?>

<style>
    /* Oculta la cabecera nativa duplicada que genera _inicio.php antes de este contenido */
    body > main > .container > .level:first-child,
    body > main > .container > .is-flex:first-child,
    .main-content > .level:first-child,
    .main-content > .is-flex:first-child { 
        display: none !important; 
    }

    /* Control estricto de visibilidad para vista de impresión */
    @media screen {
        .solo-impresion { display: none !important; }
    }
    @media print {
        .no-print { display: none !important; }
        .solo-impresion { display: block !important; }
    }
</style>

<!-- BEGIN: Banner Hero (Azul Institucional Bonanza) -->
<div class="box is-shadowless mb-5 p-5 no-print" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 16px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.15);">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
        
        <!-- Bloque Izquierdo: Ícono + Títulos -->
        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 12px; backdrop-filter: blur(4px); flex-shrink: 0; border: 1px solid rgba(255,255,255,0.25);">
                <i class="fas fa-chart-line fa-lg has-text-white"></i>
            </div>
            
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0"><?= esc($tituloPagina) ?></h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small" style="background-color: rgba(255,255,255,0.15) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.25);">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span>
                        Rentabilidad Comercial
                    </span>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 750px; opacity: 0.95;">
                    <?= esc($descripcionPagina) ?>
                </p>
            </div>
        </div>

        <!-- Bloque Derecho: Botones de Acción (Separación corregida) -->
        <div class="is-flex is-align-items-center flex-shrink-0" style="gap: 0.75rem;">
            <a href="index.php?controller=reporte&action=index" class="button is-ghost has-text-white mb-0" style="background-color: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25);">
                <span class="icon is-small mr-2"><i class="fas fa-arrow-left"></i></span>
                <span>Todos los reportes</span>
            </a>
            <?php if (isset($urlCsv) && $urlCsv): ?>
            <a href="<?= esc($urlCsv) ?>" class="button is-success mb-0" style="background-color: #10b981; border-color: #059669; font-weight: 600;">
                <span class="icon is-small mr-2"><i class="fas fa-download"></i></span>
                <span>Exportar CSV</span>
            </a>
            <?php endif; ?>
            <button type="button" onclick="window.print()" class="button is-white has-text-link has-text-weight-bold mb-0">
                <span class="icon is-small mr-2"><i class="fas fa-print"></i></span>
                <span>Imprimir Reporte</span>
            </button>
        </div>
    </div>
</div>
<!-- END: Banner Hero -->

<!-- Título exclusivo para impresión física -->
<div class="solo-impresion mb-4">
    <h1 class="title is-4 mb-1"><?= esc($tituloPagina) ?></h1>
    <p class="subtitle is-6 has-text-grey"><?= esc($subtituloImpresion) ?></p>
</div>

<!-- BEGIN: Panel de Filtros -->
<div class="box is-shadowless mb-5 p-5 no-print" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
    <form method="get" action="index.php">
        <input type="hidden" name="controller" value="reporte">
        <input type="hidden" name="action" value="ganancias">

        <div class="columns is-multiline">
            <div class="column is-12-mobile is-6-tablet is-3-desktop">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroDesde">Desde</label>
                    <div class="control">
                        <input class="input is-shadowless" type="date" id="filtroDesde" name="desde" max="<?= esc($hoy) ?>" value="<?= esc($desde) ?>" required style="border-color: #e2e8f0;">
                    </div>
                </div>
            </div>
            <div class="column is-12-mobile is-6-tablet is-3-desktop">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroHasta">Hasta</label>
                    <div class="control">
                        <input class="input is-shadowless" type="date" id="filtroHasta" name="hasta" max="<?= esc($hoy) ?>" value="<?= esc($hasta) ?>" required style="border-color: #e2e8f0;">
                    </div>
                </div>
            </div>
            <div class="column is-12-mobile is-6-tablet is-3-desktop">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroCategoria">Categoría</label>
                    <div class="control">
                        <div class="select is-fullwidth is-shadowless" style="border-color: #e2e8f0;">
                            <select id="filtroCategoria" name="categoria">
                                <?= opcionesCategoria($categorias, $idCategoria) ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-12-mobile is-6-tablet is-3-desktop">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroOrden">Ordenar por</label>
                    <div class="control">
                        <div class="select is-fullwidth is-shadowless" style="border-color: #e2e8f0;">
                            <select id="filtroOrden" name="orden">
                                <?php foreach ($nombresOrden as $clave => $texto): ?>
                                    <option value="<?= esc($clave) ?>" <?= $orden === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción y Atajos -->
        <div class="is-flex is-flex-wrap-wrap is-align-items-center justify-content-space-between mt-3 pt-4" style="border-top: 1px solid #f1f5f9; gap: 1rem;">
            <button class="button is-link has-text-weight-bold px-5" type="submit">
                <span class="icon is-small mr-2"><i class="fas fa-filter"></i></span>
                <span>Ver reporte</span>
            </button>
            <div class="is-flex is-align-items-center flex-wrap" style="gap: 0.5rem;">
                <span class="is-size-7 has-text-weight-bold has-text-grey mr-2">Atajos:</span>
                <?php foreach (rangosRapidos($hoy) as $etiqueta => [$d, $h]): ?>
                    <a href="<?= esc(urlAccion('reporte', 'ganancias', ['desde' => $d, 'hasta' => $h] + $conservar)) ?>" 
                       class="button is-small <?= ($d === $desde && $h === $hasta) ? 'is-link' : 'is-light has-text-grey-dark' ?>">
                        <?= esc($etiqueta) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>
<!-- END: Panel de Filtros -->

<!-- AVISO: Ventas sin costo -->
<?php if ($totales['unidades_sin_costo'] > 0): ?>
    <div class="notification is-warning is-light mb-5 no-print" style="border-radius: 12px; border: 1px solid #fde047;">
        <span class="icon mr-2"><i class="fas fa-exclamation-triangle"></i></span>
        <strong><?= esc(unidades($totales['unidades_sin_costo'])) ?></strong>
        <?= $totales['unidades_sin_costo'] === 1 ? 'unidad' : 'unidades' ?>
        (<?= (int) $totales['productos_sin_costo'] ?> <?= $totales['productos_sin_costo'] === 1 ? 'producto' : 'productos' ?>)
        se vendieron sin costo registrado, por <strong><?= esc(dinero($totales['ingresos_sin_costo'])) ?></strong>.
        No entran en el costo ni en la ganancia; el margen se calcula solo sobre lo que sí tiene costo.
    </div>
<?php endif; ?>

<!-- BEGIN: KPIs Financieros -->
<div class="columns is-multiline mb-5">
    <!-- KPI 1: Ingresos (Azul) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Ingresos</p>
                <p class="title is-3 mb-0 has-text-dark"><?= esc(dinero($totales['ingresos'])) ?></p>
                <p class="is-size-7 has-text-grey mt-1"><?= esc(unidades($totales['unidades'])) ?> unds. • <?= (int) $totales['productos'] ?> <?= $totales['productos'] === 1 ? 'prod.' : 'prods.' ?></p>
            </div>
            <div class="has-background-info-light has-text-link p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-file-invoice-dollar fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 2: Costos (Ámbar) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #d97706; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Costo de lo vendido</p>
                <p class="title is-3 mb-0 has-text-dark"><?= esc(dinero($totales['costo'])) ?></p>
                <p class="is-size-7 has-text-warning-dark mt-1">Solo ventas con costo</p>
            </div>
            <div class="has-background-warning-light has-text-warning-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-receipt fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 3: Ganancia (Dinámico) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid <?= $hexGanancia ?>; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Ganancia</p>
                <p class="title is-3 mb-0 has-text-<?= $colorGanancia ?>-dark"><?= esc(dinero($totales['ganancia'])) ?></p>
                <p class="is-size-7 has-text-grey mt-1">Ingresos – costo</p>
            </div>
            <div class="has-background-<?= $colorGanancia ?>-light has-text-<?= $colorGanancia ?>-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-coins fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 4: Margen (Celeste) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #0ea5e9; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Margen</p>
                <p class="title is-3 mb-0 has-text-info-dark"><?= esc(porcentaje($totales['margen'])) ?></p>
                <p class="is-size-7 has-text-grey mt-1">Ganancia ÷ ingresos</p>
            </div>
            <div class="has-background-info-light has-text-info-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-percent fa-lg"></i></span>
            </div>
        </div>
    </div>
</div>
<!-- END: KPIs Financieros -->

<!-- BEGIN: Tabla de Detalle -->
<div class="box is-shadowless p-0 overflow-hidden" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
    <div class="p-4 is-flex is-align-items-center justify-content-space-between" style="border-bottom: 1px solid #f1f5f9;">
        <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
            <h2 class="title is-5 mb-0 has-text-dark">Detalle por producto</h2>
            <span class="tag is-info is-light has-text-weight-bold"><?= (int) $totales['productos'] ?> analizados</span>
        </div>
        <span class="is-size-7 has-text-grey has-text-weight-semibold is-hidden-mobile">Valores al cierre</span>
    </div>
    
    <div class="table-container m-0">
        <table class="table is-fullwidth is-hoverable mb-0">
            <thead class="has-background-light">
                <tr>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider pl-5 py-3">Producto</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-3">Unidades</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-3">Ingresos</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-3">Costo</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-3">Ganancia</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-3">Margen</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right pr-5 py-3">% de la Ganancia</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filas)): ?>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="pl-5 py-4">
                                <div class="has-text-weight-bold has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
                                    <?= esc($f['nombre']) ?>
                                </div>
                                <div class="is-size-7 has-text-grey mt-1"><?= esc($f['codigo']) ?> • <?= esc($f['nombre_categoria']) ?></div>
                            </td>
                            <td class="has-text-right has-text-weight-semibold py-4">
                                <?= esc(unidades($f['unidades'])) ?>
                                <?php if ($f['unidades_sin_costo'] > 0 && $f['costo'] !== null): ?>
                                    <br><span class="is-size-7 has-text-grey"><?= esc(unidades($f['unidades_sin_costo'])) ?> sin costo</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-right has-text-weight-semibold py-4"><?= esc(dinero($f['ingresos'])) ?></td>
                            
                            <?php if ($f['costo'] === null): ?>
                                <td class="has-text-right has-text-grey py-4">—</td>
                                <td class="has-text-right py-4" colspan="3">
                                    <span class="tag is-warning is-light has-text-weight-bold">Sin costo registrado</span>
                                </td>
                            <?php else: ?>
                                <td class="has-text-right has-text-grey py-4"><?= esc(dinero($f['costo'])) ?></td>
                                <td class="has-text-right has-text-weight-bold <?= esc(claseVariacion($f['ganancia'])) ?> py-4">
                                    <?= esc(dinero($f['ganancia'])) ?>
                                </td>
                                <td class="has-text-right py-4">
                                    <span class="tag is-light has-text-weight-bold"><?= esc(porcentaje($f['margen'])) ?></span>
                                </td>
                                <td class="has-text-right pr-5 py-4">
                                    <div class="is-flex is-align-items-center is-justify-content-flex-end" style="gap: 0.75rem;">
                                        <span class="has-text-weight-semibold"><?= esc(porcentaje($f['pct_ganancia'])) ?></span>
                                        <div style="width: 50px; background-color: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden; flex-shrink: 0;">
                                            <div style="width: <?= floatval($f['pct_ganancia']) ?>%; background-color: <?= floatval($f['pct_ganancia']) < 0 ? '#dc2626' : '#2563eb' ?>; height: 100%;"></div>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="has-text-centered has-text-grey py-6">
                            <span class="icon is-large mb-2" style="opacity: 0.5;"><i class="fas fa-receipt fa-2x"></i></span><br>
                            No hubo ventas<?= $nombreCat !== null ? ' de esa categoría' : '' ?> entre el <?= esc(formatoFecha($desde)) ?> y el <?= esc(formatoFecha($hasta)) ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($filas)): ?>
                <tfoot class="has-background-light" style="border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td class="pl-5 py-4 has-text-weight-bold is-size-7 is-uppercase tracking-wider">Total <span class="has-text-grey is-lowercase font-normal">(<?= (int) $totales['productos'] ?> prods.)</span></td>
                        <td class="has-text-right has-text-weight-bold has-text-dark py-4"><?= esc(unidades($totales['unidades'])) ?></td>
                        <td class="has-text-right has-text-weight-bold has-text-dark py-4"><?= esc(dinero($totales['ingresos'])) ?></td>
                        <td class="has-text-right has-text-weight-bold has-text-grey-dark py-4"><?= esc(dinero($totales['costo'])) ?></td>
                        <td class="has-text-right has-text-weight-bold has-text-<?= $colorGanancia ?>-dark py-4"><?= esc(dinero($totales['ganancia'])) ?></td>
                        <td class="has-text-right py-4">
                            <span class="tag is-info is-light has-text-weight-bold"><?= esc(porcentaje($totales['margen'])) ?></span>
                        </td>
                        <td class="has-text-right pr-5 py-4 has-text-weight-bold has-text-dark">100%</td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    
    <!-- Footer disclaimer -->
    <div class="p-4 has-background-white-ter is-flex is-align-items-start" style="gap: 0.5rem; border-top: 1px solid #e2e8f0;">
        <span class="icon has-text-grey-light mt-1 flex-shrink-0"><i class="fas fa-info-circle"></i></span>
        <p class="is-size-7 has-text-grey">Montos expresados en USD sin IVA. El costo aplicado corresponde al registro contable al momento de cada venta. Las ventas anuladas quedan excluidas del cómputo. Margen = ganancia ÷ ingresos (sobre lo vendido con costo conocido).</p>
    </div>
</div>
<!-- END: Tabla de Detalle -->

<?php require __DIR__ . '/_fin.php'; ?>