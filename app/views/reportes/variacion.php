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
$descripcionPagina  = 'Compara las ventas de cada producto entre dos períodos para ver cuáles suben y cuáles bajan.';
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
$valorTot  = $porMonto ? dinero($totales['monto_actual'])      : unidades($totales['unidades_actual']);
$antesTot  = $porMonto ? dinero($totales['monto_base'])        : unidades($totales['unidades_base']);

require __DIR__ . '/_inicio.php';
?>

        <!-- FILTROS -->
        <form method="get" action="index.php" class="box mb-5 no-print">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="variacion">

            <div class="columns is-multiline mb-0">
                <div class="column is-6">
                    <p class="label is-small has-text-grey mb-2">Período actual</p>
                    <div class="columns is-mobile mb-0">
                        <div class="column pt-0">
                            <label class="is-size-7 has-text-grey" for="filtroDesde">Desde</label>
                            <input class="input" type="date" id="filtroDesde" name="desde" max="<?= esc($hoy) ?>" value="<?= esc($desde) ?>" required>
                        </div>
                        <div class="column pt-0">
                            <label class="is-size-7 has-text-grey" for="filtroHasta">Hasta</label>
                            <input class="input" type="date" id="filtroHasta" name="hasta" max="<?= esc($hoy) ?>" value="<?= esc($hasta) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="column is-6">
                    <p class="label is-small has-text-grey mb-2">Período base (opcional)</p>
                    <div class="columns is-mobile mb-0">
                        <div class="column pt-0">
                            <label class="is-size-7 has-text-grey" for="filtroBaseDesde">Desde</label>
                            <input class="input" type="date" id="filtroBaseDesde" name="base_desde" max="<?= esc($hoy) ?>" value="<?= $baseManual ? esc($baseDesde) : '' ?>">
                        </div>
                        <div class="column pt-0">
                            <label class="is-size-7 has-text-grey" for="filtroBaseHasta">Hasta</label>
                            <input class="input" type="date" id="filtroBaseHasta" name="base_hasta" max="<?= esc($hoy) ?>" value="<?= $baseManual ? esc($baseHasta) : '' ?>">
                        </div>
                    </div>
                    <p class="help">Vacío = el período anterior de igual duración.</p>
                </div>

                <div class="column is-4">
                    <label class="label is-small has-text-grey" for="filtroCategoria">Categoría</label>
                    <div class="select is-fullwidth">
                        <select id="filtroCategoria" name="categoria">
                            <?= opcionesCategoria($categorias, $idCategoria) ?>
                        </select>
                    </div>
                </div>
                <div class="column is-4">
                    <label class="label is-small has-text-grey" for="filtroMetrica">Decidir aumento o baja por</label>
                    <div class="select is-fullwidth">
                        <select id="filtroMetrica" name="metrica">
                            <option value="unidades" <?= !$porMonto ? 'selected' : '' ?>>Unidades vendidas</option>
                            <option value="monto"    <?= $porMonto ? 'selected' : '' ?>>Monto vendido (sin IVA)</option>
                        </select>
                    </div>
                </div>
                <div class="column is-4">
                    <label class="label is-small has-text-grey" for="filtroTendencia">Mostrar</label>
                    <div class="select is-fullwidth">
                        <select id="filtroTendencia" name="tendencia">
                            <option value="">Todos los productos</option>
                            <?php foreach ($nombresFiltro as $clave => $texto): ?>
                                <option value="<?= esc($clave) ?>" <?= $tendencia === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="is-flex is-flex-wrap-wrap is-align-items-center mt-2" style="gap: 0.75rem;">
                <button type="submit" class="button is-link">
                    <span class="icon is-small"><i class="fas fa-code-compare"></i></span>
                    <span>Comparar</span>
                </button>
                <div class="rangos-rapidos">
                    <span class="is-size-7 has-text-grey">Atajos (contra el período anterior):</span>
                    <?php foreach (rangosRapidos($hoy) as $etiqueta => [$d, $h]): ?>
                        <a class="button is-small <?= (!$baseManual && $d === $desde && $h === $hasta) ? 'is-link' : 'is-light' ?>"
                           href="<?= esc(urlAccion('reporte', 'variacion', ['desde' => $d, 'hasta' => $h] + $conservar)) ?>"><?= esc($etiqueta) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>

        <!-- QUÉ SE ESTÁ COMPARANDO (también sale en el papel) -->
        <p class="mb-4">
            <strong>Actual:</strong> <?= esc(formatoFecha($desde)) ?> al <?= esc(formatoFecha($hasta)) ?>
            <span class="has-text-grey">(<?= (int) $diasActual ?> <?= $diasActual === 1 ? 'día' : 'días' ?>)</span>
            &nbsp;·&nbsp;
            <strong>Base:</strong> <?= esc(formatoFecha($baseDesde)) ?> al <?= esc(formatoFecha($baseHasta)) ?>
            <span class="has-text-grey">(<?= (int) $diasBase ?> <?= $diasBase === 1 ? 'día' : 'días' ?>)</span>
        </p>

        <?php if ($diasActual !== $diasBase): ?>
            <div class="notification is-warning is-light">
                Los dos períodos no tienen la misma duración (<?= (int) $diasActual ?> y <?= (int) $diasBase ?> días):
                la comparación puede no ser equilibrada.
            </div>
        <?php endif; ?>

        <?php if ($desde <= $baseHasta && $baseDesde <= $hasta): ?>
            <div class="notification is-warning is-light">
                Los dos períodos se solapan: las ventas de los días en común cuentan en ambos
                y la comparación no es confiable. Elige un período base que termine antes de que empiece el actual.
            </div>
        <?php endif; ?>

        <!-- INDICADORES -->
        <div class="columns is-multiline mb-4">
            <?= tarjetaKpi('Aumentaron', (string) $conteo['aumentaron'],
                    $conteo['nuevos'] > 0 ? 'Incluye ' . $conteo['nuevos'] . ($conteo['nuevos'] === 1 ? ' producto nuevo' : ' productos nuevos') : 'Productos que vendieron más',
                    'success', 'fa-arrow-trend-up') ?>
            <?= tarjetaKpi('Disminuyeron', (string) $conteo['disminuyeron'],
                    $conteo['sin_ventas'] > 0 ? 'Incluye ' . $conteo['sin_ventas'] . ($conteo['sin_ventas'] === 1 ? ' que dejó de venderse' : ' que dejaron de venderse') : 'Productos que vendieron menos',
                    'danger', 'fa-arrow-trend-down') ?>
            <?= tarjetaKpi('Sin cambio', (string) $conteo['iguales'], 'Igual en ambos períodos', 'info', 'fa-equals') ?>
            <?= tarjetaKpi($porMonto ? 'Total vendido (sin IVA)' : 'Unidades vendidas', $valorTot,
                    'Antes: ' . $antesTot . ' (' . variacionTexto($pctTotal) . ')', $colorTot, 'fa-chart-line') ?>
        </div>

        <!-- TABLA -->
        <div class="box p-0 caja-tabla">
            <h2 class="titulo-tabla">
                Detalle por producto
                <span class="has-text-grey has-text-weight-normal is-size-7">(<?= count($filas) ?>) · de mayor aumento a mayor baja, según <?= $porMonto ? 'monto' : 'unidades' ?></span>
            </h2>
            <div class="table-container mb-0">
                <table class="table tabla-reporte is-fullwidth is-hoverable mb-0">
                    <thead>
                        <tr class="grupo">
                            <th rowspan="2" style="text-align:left; vertical-align:bottom;">Producto</th>
                            <th colspan="3" class="borde-izq">Unidades</th>
                            <th colspan="3" class="borde-izq">Monto (sin IVA)</th>
                            <th rowspan="2" class="centro borde-izq" style="vertical-align:bottom;">Tendencia</th>
                        </tr>
                        <tr>
                            <th class="num borde-izq">Base</th>
                            <th class="num">Actual</th>
                            <th class="num">Variación</th>
                            <th class="num borde-izq">Base</th>
                            <th class="num">Actual</th>
                            <th class="num">Variación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filas)): ?>
                            <?php foreach ($filas as $f): ?>
                                <?php [$claseTag, $iconoTag, $textoTag] = $etiquetasTendencia[$f['tendencia']]; ?>
                                <tr>
                                    <td>
                                        <span class="has-text-weight-bold"><?= esc($f['nombre']) ?></span>
                                        <span class="sub-linea"><?= esc($f['codigo']) ?> · <?= esc($f['nombre_categoria']) ?></span>
                                    </td>
                                    <td class="num borde-izq"><?= esc(unidades($f['unidades_base'])) ?></td>
                                    <td class="num has-text-weight-bold"><?= esc(unidades($f['unidades_actual'])) ?></td>
                                    <td class="num">
                                        <span class="<?= esc(claseVariacion($f['dif_unidades'])) ?>"><?= esc(difUnidades($f['dif_unidades'])) ?></span>
                                        <span class="sub-linea"><?= esc(variacionTexto($f['pct_unidades'])) ?></span>
                                    </td>
                                    <td class="num borde-izq"><?= esc(dinero($f['monto_base'])) ?></td>
                                    <td class="num has-text-weight-bold"><?= esc(dinero($f['monto_actual'])) ?></td>
                                    <td class="num">
                                        <span class="<?= esc(claseVariacion($f['dif_monto'])) ?>"><?= esc(difDinero($f['dif_monto'])) ?></span>
                                        <span class="sub-linea"><?= esc(variacionTexto($f['pct_monto'])) ?></span>
                                    </td>
                                    <td class="centro borde-izq">
                                        <span class="tag <?= esc($claseTag) ?> is-light has-text-weight-bold">
                                            <span class="icon is-small"><i class="fas <?= esc($iconoTag) ?>"></i></span>
                                            <span><?= esc($textoTag) ?></span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="has-text-centered has-text-grey py-5">
                                    <?= $tendencia !== null
                                        ? 'Ningún producto coincide con el filtro elegido.'
                                        : 'No hubo ventas' . ($nombreCat !== null ? ' de esa categoría' : '') . ' en ninguno de los dos períodos.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($filas)): ?>
                        <tfoot>
                            <tr>
                                <th>Total<?= $tendencia !== null ? ' (todos los productos)' : '' ?></th>
                                <th class="num borde-izq"><?= esc(unidades($totales['unidades_base'])) ?></th>
                                <th class="num"><?= esc(unidades($totales['unidades_actual'])) ?></th>
                                <th class="num">
                                    <span class="<?= esc(claseVariacion($totales['dif_unidades'])) ?>"><?= esc(difUnidades($totales['dif_unidades'])) ?></span>
                                    <span class="sub-linea"><?= esc(variacionTexto($totales['pct_unidades'])) ?></span>
                                </th>
                                <th class="num borde-izq"><?= esc(dinero($totales['monto_base'])) ?></th>
                                <th class="num"><?= esc(dinero($totales['monto_actual'])) ?></th>
                                <th class="num">
                                    <span class="<?= esc(claseVariacion($totales['dif_monto'])) ?>"><?= esc(difDinero($totales['dif_monto'])) ?></span>
                                    <span class="sub-linea"><?= esc(variacionTexto($totales['pct_monto'])) ?></span>
                                </th>
                                <th class="borde-izq"></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <p class="nota-tabla">
                <strong>Nuevo:</strong> no se vendió en el período base. <strong>Sin ventas:</strong> se vendió en el base y no en el actual (−100 %).
                Montos sin IVA; las ventas anuladas no cuentan. El total incluye todos los productos, aunque la tabla esté filtrada.
            </p>
        </div>

<?php require __DIR__ . '/_fin.php'; ?>
