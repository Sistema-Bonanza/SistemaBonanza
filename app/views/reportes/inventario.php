<?php
// Variables que entrega ReporteController::inventario():
//   $filas, $totales, $categorias, $filtros (q, categoria, situacion, inactivos),
//   $hayFiltros, $verCostos, $urlCsv
// El operador no recibe costos (vienen en null): $verCostos decide qué columnas se dibujan.

$nombresSituacion = ['agotado' => 'Agotados', 'bajo' => 'Stock bajo', 'ok' => 'Disponibles'];

// Texto de los filtros para el encabezado del papel.
$partesFiltro = [];
if ($filtros['categoria'] !== null) {
    $partesFiltro[] = 'Categoría: ' . (nombreCategoria($categorias, $filtros['categoria']) ?? '—');
}
if ($filtros['situacion'] !== null) {
    $partesFiltro[] = 'Situación: ' . $nombresSituacion[$filtros['situacion']];
}
if ($filtros['q'] !== '') {
    $partesFiltro[] = 'Búsqueda: "' . $filtros['q'] . '"';
}
if ($filtros['inactivos']) {
    $partesFiltro[] = 'Incluye productos inactivos';
}

$tituloPagina       = 'Reporte de inventario';
$descripcionPagina  = 'Existencias de cada producto, stock mínimo y valor del inventario.';
$subtituloImpresion = 'Existencias al ' . date('d/m/Y h:i A')
                    . ($partesFiltro ? ' · ' . implode(' · ', $partesFiltro) : '')
                    . ' · Valores sin IVA';

$hayCostoDeCompra = false;   // ¿alguna fila usa el costo de la última compra? (para la leyenda)
foreach ($filas as $f) {
    if ($f['origen_costo'] === 'compra') {
        $hayCostoDeCompra = true;
        break;
    }
}
$columnas = $verCostos ? 10 : 8;

require __DIR__ . '/_inicio.php';
?>

        <!-- FILTROS -->
        <form method="get" action="index.php" class="box mb-5 no-print">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="inventario">

            <div class="columns is-vcentered is-multiline mb-0">
                <div class="column is-4">
                    <label class="label is-small has-text-grey" for="filtroBuscar">Buscar por código o nombre</label>
                    <input class="input" type="search" id="filtroBuscar" name="q" maxlength="60"
                           placeholder="Ej.: tornillo" value="<?= esc($filtros['q']) ?>">
                </div>
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroCategoria">Categoría</label>
                    <div class="select is-fullwidth">
                        <select id="filtroCategoria" name="categoria">
                            <?= opcionesCategoria($categorias, $filtros['categoria']) ?>
                        </select>
                    </div>
                </div>
                <div class="column is-3">
                    <label class="label is-small has-text-grey" for="filtroSituacion">Situación</label>
                    <div class="select is-fullwidth">
                        <select id="filtroSituacion" name="situacion">
                            <option value="">Todas</option>
                            <?php foreach ($nombresSituacion as $clave => $texto): ?>
                                <option value="<?= esc($clave) ?>" <?= $filtros['situacion'] === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="column is-2">
                    <label class="checkbox mt-5">
                        <input type="checkbox" name="inactivos" value="1" <?= $filtros['inactivos'] ? 'checked' : '' ?>>
                        Incluir inactivos
                    </label>
                </div>
            </div>

            <div class="buttons mt-2">
                <button type="submit" class="button is-link">
                    <span class="icon is-small"><i class="fas fa-filter"></i></span>
                    <span>Aplicar filtros</span>
                </button>
                <?php if ($hayFiltros): ?>
                    <a href="<?= esc(urlAccion('reporte', 'inventario')) ?>" class="button is-light">Quitar filtros</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- AVISOS: datos que faltan y por eso no suman -->
        <?php if ($verCostos && $totales['sin_costo'] > 0): ?>
            <div class="notification is-warning is-light">
                <strong><?= (int) $totales['sin_costo'] ?></strong>
                <?= $totales['sin_costo'] === 1 ? 'producto tiene' : 'productos tienen' ?> existencias pero no tienen costo
                (ni precio de compra registrado ni compras): no suman al valor al costo.
            </div>
        <?php endif; ?>
        <?php if ($totales['sin_precio'] > 0): ?>
            <div class="notification is-warning is-light">
                <strong><?= (int) $totales['sin_precio'] ?></strong>
                <?= $totales['sin_precio'] === 1 ? 'producto tiene' : 'productos tienen' ?> existencias pero no tienen precio de venta:
                no suman al valor a precio de venta.
            </div>
        <?php endif; ?>

        <!-- INDICADORES -->
        <div class="columns is-multiline mb-4">
            <?= tarjetaKpi('Productos', (string) $totales['productos'],
                    unidades($totales['unidades']) . ' unidades en existencia' . ($hayFiltros ? ' (con los filtros aplicados)' : ''),
                    'info', 'fa-boxes-stacked') ?>
            <?php if ($verCostos): ?>
                <?= tarjetaKpi('Valor al costo', dinero($totales['valor_costo']),
                        'A precio de venta: ' . dinero($totales['valor_venta']), 'success', 'fa-sack-dollar') ?>
            <?php else: ?>
                <?= tarjetaKpi('Valor a precio de venta', dinero($totales['valor_venta']), 'Sin IVA', 'success', 'fa-sack-dollar') ?>
            <?php endif; ?>
            <?= tarjetaKpi('Agotados', (string) $totales['agotados'], 'Sin existencias', 'danger', 'fa-box-open') ?>
            <?= tarjetaKpi('Stock bajo', (string) $totales['bajos'], 'En o por debajo del mínimo', 'warning', 'fa-triangle-exclamation') ?>
        </div>

        <!-- TABLA DE EXISTENCIAS -->
        <div class="box p-0 caja-tabla">
            <h2 class="titulo-tabla">
                Existencias <span class="has-text-grey has-text-weight-normal is-size-7">(<?= (int) $totales['productos'] ?>)</span>
            </h2>
            <div class="table-container mb-0">
                <table class="table tabla-reporte is-fullwidth is-hoverable mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th class="num">Stock</th>
                            <th class="num">Mínimo</th>
                            <th class="centro">Situación</th>
                            <?php if ($verCostos): ?><th class="num">Costo unit.</th><?php endif; ?>
                            <th class="num">Precio venta</th>
                            <?php if ($verCostos): ?><th class="num">Valor al costo</th><?php endif; ?>
                            <th class="num">Valor a precio venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filas)): ?>
                            <?php foreach ($filas as $f): ?>
                                <tr class="<?= $f['situacion'] === 'agotado' ? 'fila-agotado' : ($f['situacion'] === 'bajo' ? 'fila-bajo' : '') ?>">
                                    <td class="has-text-weight-bold"><?= esc($f['codigo']) ?></td>
                                    <td>
                                        <?= esc($f['nombre']) ?>
                                        <?php if ((int) $f['estado'] !== 1): ?>
                                            <span class="tag is-light ml-1">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($f['nombre_categoria']) ?></td>
                                    <td class="num has-text-weight-bold"><?= esc(unidades($f['stock'])) ?></td>
                                    <td class="num"><?= esc(unidades($f['minimo'])) ?></td>
                                    <td class="centro"><?= etiquetaStock($f['situacion']) ?></td>
                                    <?php if ($verCostos): ?>
                                        <td class="num">
                                            <?= esc(dineroUnitario($f['costo_unitario'])) ?>
                                            <?php if ($f['origen_costo'] === 'compra'): ?>
                                                <span class="has-text-grey" title="Costo tomado de la última compra">*</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="num"><?= $f['precio_venta'] > 0 ? esc(dineroUnitario($f['precio_venta'])) : '—' ?></td>
                                    <?php if ($verCostos): ?>
                                        <td class="num"><?= esc(dineroOGuion($f['valor_costo'])) ?></td>
                                    <?php endif; ?>
                                    <td class="num"><?= esc(dineroOGuion($f['valor_venta'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $columnas ?>" class="has-text-centered has-text-grey py-5">
                                    <?= $hayFiltros ? 'Ningún producto coincide con los filtros.' : 'No hay productos registrados.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($filas)): ?>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total (<?= (int) $totales['productos'] ?> <?= $totales['productos'] === 1 ? 'producto' : 'productos' ?>)</th>
                                <th class="num"><?= esc(unidades($totales['unidades'])) ?></th>
                                <th colspan="2"></th>
                                <?php if ($verCostos): ?><th></th><?php endif; ?>
                                <th></th>
                                <?php if ($verCostos): ?><th class="num"><?= esc(dinero($totales['valor_costo'])) ?></th><?php endif; ?>
                                <th class="num"><?= esc(dinero($totales['valor_venta'])) ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <p class="nota-tabla">
                Stock bajo: existencias iguales o inferiores al mínimo del producto. Valores sin IVA.
                <?php if ($verCostos && $hayCostoDeCompra): ?>
                    * Costo tomado de la última compra, porque el producto no tiene precio de compra registrado.
                <?php endif; ?>
            </p>
        </div>

<?php require __DIR__ . '/_fin.php'; ?>
