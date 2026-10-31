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

$colorGanancia = ($totales['ganancia'] < 0) ? 'danger' : 'success';

require __DIR__ . '/_inicio.php';
?>

        <!-- FILTROS -->
        <form method="get" action="index.php" class="box mb-5 no-print">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="ganancias">

            <div class="columns is-vcentered is-multiline mb-0">
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroDesde">Desde</label>
                    <input class="input" type="date" id="filtroDesde" name="desde" max="<?= esc($hoy) ?>" value="<?= esc($desde) ?>" required>
                </div>
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroHasta">Hasta</label>
                    <input class="input" type="date" id="filtroHasta" name="hasta" max="<?= esc($hoy) ?>" value="<?= esc($hasta) ?>" required>
                </div>
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroCategoria">Categoría</label>
                    <div class="select is-fullwidth">
                        <select id="filtroCategoria" name="categoria">
                            <?= opcionesCategoria($categorias, $idCategoria) ?>
                        </select>
                    </div>
                </div>
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroOrden">Ordenar por</label>
                    <div class="select is-fullwidth">
                        <select id="filtroOrden" name="orden">
                            <?php foreach ($nombresOrden as $clave => $texto): ?>
                                <option value="<?= esc($clave) ?>" <?= $orden === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="is-flex is-flex-wrap-wrap is-align-items-center mt-2" style="gap: 0.75rem;">
                <button type="submit" class="button is-link">
                    <span class="icon is-small"><i class="fas fa-filter"></i></span>
                    <span>Ver reporte</span>
                </button>
                <div class="rangos-rapidos">
                    <span class="is-size-7 has-text-grey">Atajos:</span>
                    <?php foreach (rangosRapidos($hoy) as $etiqueta => [$d, $h]): ?>
                        <a class="button is-small <?= ($d === $desde && $h === $hasta) ? 'is-link' : 'is-light' ?>"
                           href="<?= esc(urlAccion('reporte', 'ganancias', ['desde' => $d, 'hasta' => $h] + $conservar)) ?>"><?= esc($etiqueta) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>

        <!-- AVISO: ventas sin costo (no se inventa un costo) -->
        <?php if ($totales['unidades_sin_costo'] > 0): ?>
            <div class="notification is-warning is-light">
                <strong><?= esc(unidades($totales['unidades_sin_costo'])) ?></strong>
                <?= $totales['unidades_sin_costo'] === 1 ? 'unidad' : 'unidades' ?>
                (<?= (int) $totales['productos_sin_costo'] ?> <?= $totales['productos_sin_costo'] === 1 ? 'producto' : 'productos' ?>)
                se vendieron sin costo registrado, por <?= esc(dinero($totales['ingresos_sin_costo'])) ?>.
                No entran en el costo ni en la ganancia; el margen se calcula solo sobre lo que sí tiene costo.
            </div>
        <?php endif; ?>

        <!-- INDICADORES -->
        <div class="columns is-multiline mb-4">
            <?= tarjetaKpi('Ingresos', dinero($totales['ingresos']),
                    unidades($totales['unidades']) . ' unds. · ' . (int) $totales['productos'] . ($totales['productos'] === 1 ? ' producto' : ' productos'),
                    'info', 'fa-file-invoice-dollar') ?>
            <?= tarjetaKpi('Costo de lo vendido', dinero($totales['costo']), 'Solo ventas con costo conocido', 'warning', 'fa-receipt') ?>
            <?= tarjetaKpi('Ganancia', dinero($totales['ganancia']), 'Ingresos con costo − costo', $colorGanancia, 'fa-coins') ?>
            <?= tarjetaKpi('Margen', porcentaje($totales['margen']), 'Ganancia ÷ ingresos con costo', 'info', 'fa-percent') ?>
        </div>

        <!-- TABLA -->
        <div class="box p-0 caja-tabla">
            <h2 class="titulo-tabla">
                Detalle por producto <span class="has-text-grey has-text-weight-normal is-size-7">(<?= (int) $totales['productos'] ?>)</span>
            </h2>
            <div class="table-container mb-0">
                <table class="table tabla-reporte is-fullwidth is-hoverable mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="num">Unidades</th>
                            <th class="num">Ingresos</th>
                            <th class="num">Costo</th>
                            <th class="num">Ganancia</th>
                            <th class="num">Margen</th>
                            <th class="num">% de la ganancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filas)): ?>
                            <?php foreach ($filas as $f): ?>
                                <tr>
                                    <td>
                                        <span class="has-text-weight-bold"><?= esc($f['nombre']) ?></span>
                                        <span class="sub-linea"><?= esc($f['codigo']) ?> · <?= esc($f['nombre_categoria']) ?></span>
                                    </td>
                                    <td class="num">
                                        <?= esc(unidades($f['unidades'])) ?>
                                        <?php if ($f['unidades_sin_costo'] > 0 && $f['costo'] !== null): ?>
                                            <span class="sub-linea"><?= esc(unidades($f['unidades_sin_costo'])) ?> sin costo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="num"><?= esc(dinero($f['ingresos'])) ?></td>
                                    <?php if ($f['costo'] === null): ?>
                                        <td class="num has-text-grey">—</td>
                                        <td class="num" colspan="3"><span class="tag is-warning is-light">Sin costo registrado</span></td>
                                    <?php else: ?>
                                        <td class="num"><?= esc(dinero($f['costo'])) ?></td>
                                        <td class="num <?= esc(claseVariacion($f['ganancia'])) ?>"><?= esc(dinero($f['ganancia'])) ?></td>
                                        <td class="num"><?= esc(porcentaje($f['margen'])) ?></td>
                                        <td class="num"><?= esc(porcentaje($f['pct_ganancia'])) ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="has-text-centered has-text-grey py-5">
                                    No hubo ventas<?= $nombreCat !== null ? ' de esa categoría' : '' ?> entre el <?= esc(formatoFecha($desde)) ?> y el <?= esc(formatoFecha($hasta)) ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($filas)): ?>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="num"><?= esc(unidades($totales['unidades'])) ?></th>
                                <th class="num"><?= esc(dinero($totales['ingresos'])) ?></th>
                                <th class="num"><?= esc(dinero($totales['costo'])) ?></th>
                                <th class="num"><?= esc(dinero($totales['ganancia'])) ?></th>
                                <th class="num"><?= esc(porcentaje($totales['margen'])) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <p class="nota-tabla">
                Montos sin IVA. El costo es el que se guardó en cada venta. Las ventas anuladas no cuentan.
                Margen = ganancia ÷ ingresos (sobre lo vendido con costo conocido).
            </p>
        </div>

<?php require __DIR__ . '/_fin.php'; ?>
