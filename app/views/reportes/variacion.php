<?php
// Variables que entrega ReporteController::variacion():
//   $filas, $totales, $conteo, $categorias, $desde, $hasta, $baseDesde, $baseHasta, $baseManual,
//   $hoy, $idCategoria, $metrica ('unidades'|'monto'), $tendencia (null|'aumento'|'disminucion'|'igual'),
//   $diasActual, $diasBase, $urlCsv
// Montos SIN IVA. La tendencia se decide con la métrica elegida; las dos se muestran siempre.

$nombreCat      = nombreCategoria($categorias, $idCategoria);
$porMonto       = ($metrica === 'monto');
$nombresFiltro  = ['aumento' => 'Solo aumentos', 'disminucion' => 'Solo disminuciones', 'igual' => 'Solo sin cambio'];

// Etiqueta de cada tendencia: [clase de color, icono, texto].
$etiquetasTendencia = [
    'aumento'     => ['is-success', 'fa-arrow-trend-up',   'Aumento'],
    'disminucion' => ['is-danger',  'fa-arrow-trend-down', 'Disminución'],
    'igual'       => ['is-light',   'fa-equals',           'Sin cambio'],
    'nuevo'       => ['is-info',    'fa-star',             'Nuevo'],
    'sin_ventas'  => ['is-danger',  'fa-ban',              'Sin ventas'],
];

$tituloPagina       = 'Aumento y disminución de ventas';
$descripcionPagina  = 'Compara las ventas de cada producto entre dos períodos para identificar tendencias de crecimiento, contracción o referencias sin rotación comercial.';
$subtituloImpresion = 'Período actual: ' . formatoFecha($desde) . ' al ' . formatoFecha($hasta)
                    . ' · Período base: ' . formatoFecha($baseDesde) . ' al ' . formatoFecha($baseHasta)
                    . ' · Comparado por ' . ($porMonto ? 'monto' : 'unidades')
                    . ($nombreCat !== null ? ' · Categoría: ' . $nombreCat : '')
                    . ($tendencia !== null ? ' · ' . $nombresFiltro[$tendencia] : '')
                    . ' · Montos sin IVA';

// Los atajos de fecha conservan categoría, métrica y filtro de tendencia.
$conservar = array_filter(
    ['categoria' => $idCategoria, 'metrica' => $metrica, 'tendencia' => $tendencia],
    function ($v) {
        return $v !== null && $v !== '';
    }
);

// Tarjeta del total: sigue a la métrica elegida y se pinta según suba o baje.
$difTotal  = $porMonto ? $totales['dif_monto'] : $totales['dif_unidades'];
$pctTotal  = $porMonto ? $totales['pct_monto'] : $totales['pct_unidades'];
$colorTot  = $difTotal > 0 ? 'success' : ($difTotal < 0 ? 'danger' : 'info');
$hexTot    = $difTotal > 0 ? '#10b981' : ($difTotal < 0 ? '#dc2626' : '#2563eb');
$valorTot  = $porMonto ? dinero($totales['monto_actual'])        : unidades($totales['unidades_actual']);
$antesTot  = $porMonto ? dinero($totales['monto_base'])          : unidades($totales['unidades_base']);

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

    /* Control estricto de visibilidad para la vista de impresión */
    @media screen {
        .solo-impresion { display: none !important; }
    }
    @media print {
        .no-print { display: none !important; }
        .solo-impresion { display: block !important; }
    }
</style>

<!-- BEGIN: Banner Hero (Azul Institucional Bonanza) -->
<div id="ajax-reporte" data-ajax-lista>
<div class="box mb-5 no-print" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                <span class="material-symbols-outlined is-size-3 has-text-white">monitoring</span>
            </div>
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0"><?= esc($tituloPagina) ?></h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Auditoría Comercial
                    </span>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                    <?= esc($descripcionPagina) ?>
                </p>
            </div>
        </div>
        <!-- Bloque Derecho: Botones de Acción -->
        <div class="is-flex is-align-items-center flex-shrink-0" style="gap: 0.75rem;">
            <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc(urlAccion('reporte', 'index')) ?>">
                <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                <span>Todos los reportes</span>
            </a>
            <?php if (isset($urlCsv) && $urlCsv): ?>
            <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc($urlCsv) ?>">
                <span class="icon"><span class="material-symbols-outlined">download</span></span>
                <span>Exportar CSV</span>
            </a>
            <?php endif; ?>
            <button class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;" type="button" onclick="window.print()">
                <span class="icon"><span class="material-symbols-outlined">print</span></span>
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
    <form method="get" action="index.php" data-ajax-filtro="#ajax-reporte">
        <input type="hidden" name="controller" value="reporte">
        <input type="hidden" name="action" value="variacion">

        <!-- Selección de Períodos: Actual vs Base -->
        <div class="columns is-multiline mb-3">
            <!-- Período Actual -->
            <div class="column is-12-mobile is-6-desktop">
                <div class="p-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <div class="is-flex is-align-items-center justify-content-space-between mb-3">
                        <span class="is-size-7 has-text-weight-bold has-text-dark is-uppercase tracking-wider">
                            <span class="has-background-link is-rounded mr-2" style="display: inline-block; width: 8px; height: 8px;"></span>
                            Período actual
                        </span>
                        <span class="is-size-7 has-text-grey">Rango de inspección</span>
                    </div>
                    <div class="columns is-mobile">
                        <div class="column is-6">
                            <label class="label is-size-7 has-text-grey" for="filtroDesde">Desde</label>
                            <input class="input is-shadowless" type="date" id="filtroDesde" name="desde" max="<?= esc($hoy) ?>" value="<?= esc($desde) ?>" required style="border-color: #cbd5e1;">
                        </div>
                        <div class="column is-6">
                            <label class="label is-size-7 has-text-grey" for="filtroHasta">Hasta</label>
                            <input class="input is-shadowless" type="date" id="filtroHasta" name="hasta" max="<?= esc($hoy) ?>" value="<?= esc($hasta) ?>" required style="border-color: #cbd5e1;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Período Base -->
            <div class="column is-12-mobile is-6-desktop">
                <div class="p-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <div class="is-flex is-align-items-center justify-content-space-between mb-3">
                        <span class="is-size-7 has-text-weight-bold has-text-dark is-uppercase tracking-wider">
                            <span class="has-background-grey-light is-rounded mr-2" style="display: inline-block; width: 8px; height: 8px;"></span>
                            Período base (opcional)
                        </span>
                        <span class="is-size-7 has-text-grey-light font-italic">Vacío = anterior simétrico</span>
                    </div>
                    <div class="columns is-mobile">
                        <div class="column is-6">
                            <label class="label is-size-7 has-text-grey" for="filtroBaseDesde">Desde</label>
                            <input class="input is-shadowless" type="date" id="filtroBaseDesde" name="base_desde" max="<?= esc($hoy) ?>" value="<?= $baseManual ? esc($baseDesde) : '' ?>" style="border-color: #cbd5e1;">
                        </div>
                        <div class="column is-6">
                            <label class="label is-size-7 has-text-grey" for="filtroBaseHasta">Hasta</label>
                            <input class="input is-shadowless" type="date" id="filtroBaseHasta" name="base_hasta" max="<?= esc($hoy) ?>" value="<?= $baseManual ? esc($baseHasta) : '' ?>" style="border-color: #cbd5e1;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selectores Operativos -->
        <div class="columns is-multiline">
            <div class="column is-12-mobile is-4-tablet">
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
            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroMetrica">Decidir aumento o baja por</label>
                    <div class="control">
                        <div class="select is-fullwidth is-shadowless" style="border-color: #e2e8f0;">
                            <select id="filtroMetrica" name="metrica">
                                <option value="unidades" <?= !$porMonto ? 'selected' : '' ?>>Unidades vendidas</option>
                                <option value="monto"    <?= $porMonto ? 'selected' : '' ?>>Monto vendido (sin IVA)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label is-size-7 has-text-grey is-uppercase tracking-wider" for="filtroTendencia">Mostrar</label>
                    <div class="control">
                        <div class="select is-fullwidth is-shadowless" style="border-color: #e2e8f0;">
                            <select id="filtroTendencia" name="tendencia">
                                <option value="">Todos los productos</option>
                                <?php foreach ($nombresFiltro as $clave => $texto): ?>
                                    <option value="<?= esc($clave) ?>" <?= $tendencia === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
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
                <span class="icon is-small mr-2"><i class="fas fa-exchange-alt"></i></span>
                <span>Comparar</span>
            </button>
            <div class="is-flex is-align-items-center flex-wrap" style="gap: 0.5rem;">
                <span class="is-size-7 has-text-weight-bold has-text-grey mr-2">Atajos (vs. período anterior):</span>
                <?php foreach (rangosRapidos($hoy) as $etiqueta => [$d, $h]): ?>
                    <a href="<?= esc(urlAccion('reporte', 'variacion', ['desde' => $d, 'hasta' => $h] + $conservar)) ?>" 
                       class="button is-small <?= (!$baseManual && $d === $desde && $h === $hasta) ? 'is-link' : 'is-light has-text-grey-dark' ?>">
                        <?= esc($etiqueta) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>
<!-- END: Panel de Filtros -->

<!-- Rango comparativo activo -->
<div class="is-flex is-align-items-center justify-content-space-between mb-4 px-2">
    <div class="is-size-7 has-text-grey">
        <span class="icon mr-1"><i class="fas fa-history"></i></span>
        <strong class="has-text-dark">Actual:</strong> <?= esc(formatoFecha($desde)) ?> al <?= esc(formatoFecha($hasta)) ?>
        <span class="has-text-grey-light">(<?= (int) $diasActual ?> <?= $diasActual === 1 ? 'día' : 'días' ?>)</span>
        <span class="mx-2">•</span>
        <strong class="has-text-dark">Base:</strong> <?= esc(formatoFecha($baseDesde)) ?> al <?= esc(formatoFecha($baseHasta)) ?>
        <span class="has-text-grey-light">(<?= (int) $diasBase ?> <?= $diasBase === 1 ? 'día' : 'días' ?>)</span>
    </div>
    <span class="tag is-light is-rounded is-size-7 font-mono">Valores sin IVA</span>
</div>

<!-- AVISOS DE AUDITORÍA -->
<?php if ($diasActual !== $diasBase): ?>
    <div class="notification is-warning is-light mb-4 no-print" style="border-radius: 12px; border: 1px solid #fde047;">
        <span class="icon mr-2"><i class="fas fa-exclamation-triangle"></i></span>
        Los dos períodos no tienen la misma duración (<strong><?= (int) $diasActual ?></strong> y <strong><?= (int) $diasBase ?> días</strong>): la comparación puede no ser equilibrada.
    </div>
<?php endif; ?>

<?php if ($desde <= $baseHasta && $baseDesde <= $hasta): ?>
    <div class="notification is-warning is-light mb-4 no-print" style="border-radius: 12px; border: 1px solid #fde047;">
        <span class="icon mr-2"><i class="fas fa-exclamation-triangle"></i></span>
        Los dos períodos se solapan: las ventas de los días en común cuentan en ambos y la comparación no es confiable. Elige un período base que termine antes de que empiece el actual.
    </div>
<?php endif; ?>

<!-- BEGIN: KPIs Financieros -->
<div class="columns is-multiline mb-5">
    <!-- KPI 1: Aumentaron (Verde Esmeralda) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #10b981; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Aumentaron</p>
                <p class="title is-3 mb-0 has-text-success-dark"><?= (int) $conteo['aumentaron'] ?></p>
                <p class="is-size-7 has-text-success mt-1">
                    <?= $conteo['nuevos'] > 0 ? 'Incluye ' . $conteo['nuevos'] . ($conteo['nuevos'] === 1 ? ' producto nuevo' : ' productos nuevos') : 'Vendieron más que el base' ?>
                </p>
            </div>
            <div class="has-background-success-light has-text-success-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-arrow-trend-up fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 2: Disminuyeron (Rojo de Alerta) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #dc2626; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Disminuyeron</p>
                <p class="title is-3 mb-0 has-text-danger-dark"><?= (int) $conteo['disminuyeron'] ?></p>
                <p class="is-size-7 has-text-danger mt-1">
                    <?= $conteo['sin_ventas'] > 0 ? 'Incluye ' . $conteo['sin_ventas'] . ($conteo['sin_ventas'] === 1 ? ' que dejó de venderse' : ' que dejaron de venderse') : 'Vendieron menos que el base' ?>
                </p>
            </div>
            <div class="has-background-danger-light has-text-danger-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-arrow-trend-down fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 3: Sin Cambio (Gris Neutro) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #64748b; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">Sin cambio</p>
                <p class="title is-3 mb-0 has-text-grey-dark"><?= (int) $conteo['iguales'] ?></p>
                <p class="is-size-7 has-text-grey mt-1">Igual en ambos períodos</p>
            </div>
            <div class="has-background-light has-text-grey-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-equals fa-lg"></i></span>
            </div>
        </div>
    </div>

    <!-- KPI 4: Métrica Global Dinámica (Azul o según resultado) -->
    <div class="column is-12-mobile is-6-tablet is-3-desktop">
        <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid <?= $hexTot ?>; border-radius: 12px; background-color: #ffffff;">
            <div>
                <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 0.05em;">
                    <?= $porMonto ? 'Total vendido' : 'Unidades vendidas' ?>
                </p>
                <p class="title is-3 mb-0 has-text-dark"><?= esc($valorTot) ?></p>
                <p class="is-size-7 mt-1">
                    <span class="has-text-grey">Antes: <?= esc($antesTot) ?></span>
                    <span class="tag is-<?= $colorTot ?> is-light is-small ml-1 has-text-weight-bold">
                        <?= esc(variacionTexto($pctTotal)) ?>
                    </span>
                </p>
            </div>
            <div class="has-background-<?= $colorTot ?>-light has-text-<?= $colorTot ?>-dark p-3 flex-shrink-0" style="border-radius: 12px; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px;">
                <span class="icon is-medium"><i class="fas fa-chart-line fa-lg"></i></span>
            </div>
        </div>
    </div>
</div>
<!-- END: KPIs Financieros -->

<!-- BEGIN: Tabla de Detalle -->
<div class="box is-shadowless p-0 overflow-hidden mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
    <div class="p-4 is-flex is-align-items-center justify-content-space-between" style="border-bottom: 1px solid #f1f5f9;">
        <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
            <h2 class="title is-5 mb-0 has-text-dark">Detalle por producto</h2>
            <span class="tag is-info is-light has-text-weight-bold"><?= count($filas) ?> evaluados</span>
        </div>
        <span class="is-size-7 has-text-grey has-text-weight-semibold is-hidden-mobile">
            Ordenado según <?= $porMonto ? 'monto' : 'unidades' ?>
        </span>
    </div>
    
    <div class="table-container m-0">
        <table class="table is-fullwidth is-hoverable mb-0">
            <thead class="has-background-light">
                <!-- Primera Fila: Categorización de Secciones -->
                <tr>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider pl-5 py-3" style="vertical-align: middle;">Producto</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-center py-3" colspan="3" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; background-color: #f1f5f9;">Unidades</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-center py-3" colspan="3">Monto (sin IVA)</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-center pr-5 py-3" style="vertical-align: middle;">Tendencia</th>
                </tr>
                <!-- Segunda Fila: Subcolumnas de Datos -->
                <tr style="border-top: 1px solid #e2e8f0;">
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider pl-5 py-2">Nombre / Código</th>
                    
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2" style="border-left: 1px solid #e2e8f0;">Base</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2">Actual</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2" style="border-right: 1px solid #e2e8f0;">Variación</th>
                    
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2">Base</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2">Actual</th>
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-right py-2">Variación</th>
                    
                    <th class="has-text-grey is-size-7 is-uppercase tracking-wider has-text-center pr-5 py-2">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filas)): ?>
                    <?php foreach ($filas as $f): ?>
                        <?php [$claseTag, $iconoTag, $textoTag] = $etiquetasTendencia[$f['tendencia']]; ?>
                        <tr>
                            <!-- Producto -->
                            <td class="pl-5 py-4">
                                <div class="has-text-weight-bold has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px;">
                                    <?= esc($f['nombre']) ?>
                                </div>
                                <div class="is-size-7 has-text-grey mt-1"><?= esc($f['codigo']) ?> • <?= esc($f['nombre_categoria']) ?></div>
                            </td>

                            <!-- Unidades -->
                            <td class="has-text-right has-text-grey py-4 font-mono" style="border-left: 1px solid #f1f5f9;"><?= esc(unidades($f['unidades_base'])) ?></td>
                            <td class="has-text-right has-text-weight-bold has-text-dark py-4 font-mono"><?= esc(unidades($f['unidades_actual'])) ?></td>
                            <td class="has-text-right py-4 font-mono" style="border-right: 1px solid #f1f5f9;">
                                <span class="has-text-weight-bold <?= esc(claseVariacion($f['dif_unidades'])) ?>">
                                    <?= esc(difUnidades($f['dif_unidades'])) ?>
                                </span>
                                <span class="is-size-7 is-block has-text-grey mt-0.5">
                                    <?= esc(variacionTexto($f['pct_unidades'])) ?>
                                </span>
                            </td>

                            <!-- Monto (sin IVA) -->
                            <td class="has-text-right has-text-grey py-4 font-mono"><?= esc(dinero($f['monto_base'])) ?></td>
                            <td class="has-text-right has-text-weight-bold has-text-dark py-4 font-mono"><?= esc(dinero($f['monto_actual'])) ?></td>
                            <td class="has-text-right py-4 font-mono">
                                <span class="has-text-weight-bold <?= esc(claseVariacion($f['dif_monto'])) ?>">
                                    <?= esc(difDinero($f['dif_monto'])) ?>
                                </span>
                                <span class="is-size-7 is-block has-text-grey mt-0.5">
                                    <?= esc(variacionTexto($f['pct_monto'])) ?>
                                </span>
                            </td>

                            <!-- Tendencia / Estado -->
                            <td class="has-text-center pr-5 py-4">
                                <span class="tag <?= esc($claseTag) ?> is-light has-text-weight-bold is-rounded">
                                    <span class="icon is-small mr-1"><i class="fas <?= esc($iconoTag) ?>"></i></span>
                                    <span><?= esc($textoTag) ?></span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="has-text-centered has-text-grey py-6">
                            <span class="icon is-large mb-2" style="opacity: 0.5;"><i class="fas fa-exchange-alt fa-2x"></i></span><br>
                            <?= $tendencia !== null
                                ? 'Ningún producto coincide con el filtro elegido.'
                                : 'No hubo ventas' . ($nombreCat !== null ? ' de esa categoría' : '') . ' en ninguno de los dos períodos.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($filas)): ?>
                <tfoot class="has-background-light" style="border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td class="pl-5 py-4 has-text-weight-bold is-size-7 is-uppercase tracking-wider">
                            Total<?= $tendencia !== null ? ' (todos los prods.)' : '' ?>
                        </td>
                        
                        <!-- Totales Unidades -->
                        <td class="has-text-right has-text-weight-bold has-text-grey py-4 font-mono" style="border-left: 1px solid #cbd5e1;"><?= esc(unidades($totales['unidades_base'])) ?></td>
                        <td class="has-text-right has-text-weight-bold has-text-dark py-4 font-mono"><?= esc(unidades($totales['unidades_actual'])) ?></td>
                        <td class="has-text-right py-4 font-mono" style="border-right: 1px solid #cbd5e1;">
                            <span class="has-text-weight-bold <?= esc(claseVariacion($totales['dif_unidades'])) ?>">
                                <?= esc(difUnidades($totales['dif_unidades'])) ?>
                            </span>
                            <span class="is-size-7 is-block has-text-weight-semibold">
                                <?= esc(variacionTexto($totales['pct_unidades'])) ?>
                            </span>
                        </td>

                        <!-- Totales Monto -->
                        <td class="has-text-right has-text-weight-bold has-text-grey py-4 font-mono"><?= esc(dinero($totales['monto_base'])) ?></td>
                        <td class="has-text-right has-text-weight-bold has-text-dark py-4 font-mono"><?= esc(dinero($totales['monto_actual'])) ?></td>
                        <td class="has-text-right py-4 font-mono">
                            <span class="has-text-weight-bold <?= esc(claseVariacion($totales['dif_monto'])) ?>">
                                <?= esc(difDinero($totales['dif_monto'])) ?>
                            </span>
                            <span class="is-size-7 is-block has-text-weight-semibold">
                                <?= esc(variacionTexto($totales['pct_monto'])) ?>
                            </span>
                        </td>

                        <!-- Columna de cierre -->
                        <td class="has-text-center pr-5 py-4">
                            <span class="tag is-<?= $colorTot ?> has-text-weight-bold">
                                <?= esc(variacionTexto($pctTotal)) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    
    <!-- Footer disclaimer -->
    <div class="p-4 has-background-white-ter is-flex is-align-items-start" style="gap: 0.5rem; border-top: 1px solid #e2e8f0;">
        <span class="icon has-text-grey-light mt-1 flex-shrink-0"><i class="fas fa-info-circle"></i></span>
        <p class="is-size-7 has-text-grey">
            <strong>Nuevo:</strong> no se vendió en el período base. <strong>Sin ventas:</strong> se vendió en el base y no en el actual (−100 %).
            Montos sin IVA; las ventas anuladas no cuentan. El total incluye todos los productos, aunque la tabla esté filtrada.
        </p>
    </div>
</div>
<!-- END: Tabla de Detalle -->

</div><!-- /ajax-reporte: los filtros actualizan el reporte por AJAX -->
<?php require __DIR__ . '/_fin.php'; ?>