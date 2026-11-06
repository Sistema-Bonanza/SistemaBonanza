<?php
// Variables que entrega ReporteController::inventario():
// $filas, $totales, $categorias, $filtros (q, categoria, situacion, inactivos),
// $hayFiltros, $verCostos, $urlCsv
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
    $partesFiltro[] = 'Incluye inactivos';
}

$textoFecha = 'Existencias al ' . date('d/m/Y h:i A')
            . ($partesFiltro ? ' · ' . implode(' · ', $partesFiltro) : '')
            . ' · Valores sin IVA';

$hayCostoDeCompra = false;
foreach ($filas as $f) {
    if ($f['origen_costo'] === 'compra') {
        $hayCostoDeCompra = true;
        break;
    }
}
$columnas = $verCostos ? 10 : 8;

// 1. Respaldamos y apagamos variables para evitar botones duplicados en _inicio.php
$linkCsv            = $urlCsv ?? null;
$urlCsv             = null;
$subtituloImpresion = '';
$esMenu             = true;
$tituloPagina       = '';
$descripcionPagina  = '';
$ocultarCabecera    = true;

require __DIR__ . '/_inicio.php';

// 2. Restauramos variables para uso interno
$urlCsv = $linkCsv;
?>

<!-- Estilos específicos para vista en pantalla e impresión limpia -->
<style type="text/css">
    @media print {
        aside, .no-print, .buttons, .navbar, footer {
            display: none !important;
        }
        main, .container, body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .box {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
    }
</style>

<!-- ========================================== -->
<!-- 1. BANNER HERO ESTANDARIZADO (INVENTARIO) -->
<!-- ========================================== -->
<div class="box mb-5 no-print" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
        
        <!-- Bloque Izquierdo: Ícono + Títulos -->
        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 56px; height: 56px; background: rgba(255,255,255,0.12); border-radius: 12px; backdrop-filter: blur(4px); flex-shrink: 0;">
                <i class="fas fa-boxes-stacked fa-lg has-text-white"></i>
            </div>
            
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0">Reporte de Inventario</h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span>
                        Kardex Actualizado
                    </span>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 800px;">
                    <?= esc($textoFecha) ?>
                </p>
            </div>
        </div>

        <!-- Botones de Acción Superiores -->
        <div class="buttons mb-0">
            <a href="<?= esc(urlAccion('reporte', 'index')) ?>" class="button is-white is-outlined font-semibold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4); color: #ffffff;">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Todos los reportes</span>
            </a>
            <?php if (!empty($urlCsv)): ?>
                <a href="<?= esc($urlCsv) ?>" class="button is-white is-outlined font-semibold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4); color: #ffffff;">
                    <span class="icon"><i class="fas fa-file-csv"></i></span>
                    <span>Exportar CSV</span>
                </a>
            <?php endif; ?>
            <button type="button" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;" onclick="window.print()">
                <span class="icon"><i class="fas fa-print"></i></span>
                <span>Imprimir Inventario</span>
            </button>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- 2. FILTROS DE CONSULTA -->
<!-- ========================================== -->
<form method="get" action="index.php" class="box is-shadowless p-4 mb-5 no-print" style="background: white; border: 1px solid #cbd5e1; border-radius: 12px;">
    <input type="hidden" name="controller" value="reporte">
    <input type="hidden" name="action" value="inventario">

    <div class="columns is-vcentered is-multiline mb-2">
        <div class="column is-4-desktop is-12-tablet">
            <label class="label is-size-7 is-uppercase has-text-grey-dark" for="filtroBuscar">Buscar por código o nombre</label>
            <div class="control has-icons-left">
                <input class="input" type="search" id="filtroBuscar" name="q" maxlength="60" placeholder="Ej: tornillo o 006" value="<?= esc($filtros['q']) ?>" style="border-radius: 8px; border-color: #cbd5e1;">
                <span class="icon is-small is-left has-text-grey"><i class="fas fa-search"></i></span>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet">
            <label class="label is-size-7 is-uppercase has-text-grey-dark" for="filtroCategoria">Categoría</label>
            <div class="control has-icons-left">
                <div class="select is-fullwidth">
                    <select id="filtroCategoria" name="categoria" style="border-radius: 8px; border-color: #cbd5e1;">
                        <?= opcionesCategoria($categorias, $filtros['categoria']) ?>
                    </select>
                </div>
                <span class="icon is-small is-left has-text-grey"><i class="fas fa-tags"></i></span>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet">
            <label class="label is-size-7 is-uppercase has-text-grey-dark" for="filtroSituacion">Situación</label>
            <div class="control has-icons-left">
                <div class="select is-fullwidth">
                    <select id="filtroSituacion" name="situacion" style="border-radius: 8px; border-color: #cbd5e1;">
                        <option value="">Todas</option>
                        <?php foreach ($nombresSituacion as $clave => $texto): ?>
                            <option value="<?= esc($clave) ?>" <?= $filtros['situacion'] === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <span class="icon is-small is-left has-text-grey"><i class="fas fa-battery-half"></i></span>
            </div>
        </div>
        <div class="column is-2-desktop is-12-tablet">
            <label class="checkbox is-size-7 font-weight-bold has-text-dark mt-5" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="inactivos" value="1" <?= $filtros['inactivos'] ? 'checked' : '' ?>>
                Incluir inactivos
            </label>
        </div>
    </div>

    <div class="buttons mt-2">
        <button type="submit" class="button is-link font-weight-bold" style="border-radius: 8px;">
            <span class="icon is-small"><i class="fas fa-filter"></i></span>
            <span>Aplicar filtros</span>
        </button>
        <?php if ($hayFiltros): ?>
            <a href="<?= esc(urlAccion('reporte', 'inventario')) ?>" class="button is-light font-weight-bold" style="border-radius: 8px;">Quitar filtros</a>
        <?php endif; ?>
    </div>
</form>

<!-- ========================================== -->
<!-- 3. AVISOS DE DISCREPANCIAS (Suma de Costos/Precios) -->
<!-- ========================================== -->
<?php if ($verCostos && $totales['sin_costo'] > 0): ?>
    <div class="notification is-warning is-light mb-5" style="border: 1px solid #fde68a; border-radius: 10px;">
        <span class="icon mr-1"><i class="fas fa-exclamation-triangle"></i></span>
        <strong><?= (int) $totales['sin_costo'] ?></strong> <?= $totales['sin_costo'] === 1 ? 'producto tiene' : 'productos tienen' ?> existencias pero no tienen costo (ni precio de compra registrado ni compras), por lo que <strong>no suman al valor al costo</strong>.
    </div>
<?php endif; ?>

<?php if ($totales['sin_precio'] > 0): ?>
    <div class="notification is-warning is-light mb-5" style="border: 1px solid #fde68a; border-radius: 10px;">
        <span class="icon mr-1"><i class="fas fa-exclamation-triangle"></i></span>
        <strong><?= (int) $totales['sin_precio'] ?></strong> <?= $totales['sin_precio'] === 1 ? 'producto tiene' : 'productos tienen' ?> existencias pero no tienen precio de venta: <strong>no suman al valor a precio de venta</strong>.
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- 4. TARJETAS MÉTRICAS (KPIs INVENTARIO) -->
<!-- ========================================== -->
<div class="columns is-multiline is-variable is-4 mb-5">
    
    <!-- KPI 1: Productos -->
    <div class="column is-3-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Productos</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #eff6ff; color: #2563eb; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-dark">
                <?= (int) $totales['productos'] ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                <strong class="has-text-dark"><?= esc(unidades($totales['unidades'])) ?></strong> unidades en existencia
            </div>
        </div>
    </div>

    <!-- KPI 2: Valor Monetario (Depende de $verCostos) -->
    <div class="column is-3-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #059669; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">
                    <?= $verCostos ? 'Valor al Costo' : 'Valor de Venta (Sin IVA)' ?>
                </span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #ecfdf5; color: #059669; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-sack-dollar"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-success">
                <?= $verCostos ? esc(dinero($totales['valor_costo'])) : esc(dinero($totales['valor_venta'])) ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                <?php if ($verCostos): ?>
                    A precio de venta: <strong class="has-text-dark"><?= esc(dinero($totales['valor_venta'])) ?></strong>
                <?php else: ?>
                    Total proyectado de ingresos
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- KPI 3: Agotados -->
    <div class="column is-3-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #e11d48; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Agotados</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #fff1f2; color: #e11d48; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-danger">
                <?= (int) $totales['agotados'] ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                Sin existencias en almacén
            </div>
        </div>
    </div>

    <!-- KPI 4: Stock Bajo -->
    <div class="column is-3-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #d97706; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Stock Bajo</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #fffbeb; color: #d97706; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-warning-dark">
                <?= (int) $totales['bajos'] ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                Igual o por debajo del mínimo
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- 5. TABLA DETALLADA DE EXISTENCIAS -->
<!-- ========================================== -->
<div class="box is-shadowless p-0 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
    <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">
            Existencias <span class="has-text-grey font-weight-normal is-size-7 ml-1">(<?= (int) $totales['productos'] ?>)</span>
        </h3>
        <span class="tag is-info is-light font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Kardex Activo</span>
    </div>

    <div class="table-container mb-0">
        <table class="table is-fullwidth is-hoverable is-striped mb-0 text-sm">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th class="is-size-7 font-weight-bold is-uppercase">Código</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Producto</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Categoría</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Stock</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Mínimo</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Situación</th>
                    <?php if ($verCostos): ?>
                        <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Costo unit.</th>
                    <?php endif; ?>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Precio venta</th>
                    <?php if ($verCostos): ?>
                        <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Valor al costo</th>
                    <?php endif; ?>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Valor a venta</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filas)): ?>
                    <?php foreach ($filas as $f): ?>
                        <?php 
                            // Colores de situación integrados con Bulma
                            $situacionClase = 'is-success';
                            if ($f['situacion'] === 'agotado') $situacionClase = 'is-danger';
                            elseif ($f['situacion'] === 'bajo') $situacionClase = 'is-warning';
                            
                            $rowClass = ($f['situacion'] === 'agotado') ? 'has-background-danger-light' : (($f['situacion'] === 'bajo') ? 'has-background-warning-light' : '');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="font-mono font-weight-bold has-text-link"><?= esc($f['codigo']) ?></td>
                            <td>
                                <strong class="has-text-dark"><?= esc($f['nombre']) ?></strong>
                                <?php if ((int) $f['estado'] !== 1): ?>
                                    <span class="tag is-light is-rounded is-small ml-1 font-weight-bold">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-grey"><?= esc($f['nombre_categoria']) ?></td>
                            <td class="has-text-right font-mono font-weight-bold"><?= esc(unidades($f['stock'])) ?></td>
                            <td class="has-text-right font-mono has-text-grey"><?= esc(unidades($f['minimo'])) ?></td>
                            <td class="has-text-centered">
                                <span class="tag <?= $situacionClase ?> is-light font-weight-bold">
                                    <?= esc($nombresSituacion[$f['situacion']] ?? 'Desconocido') ?>
                                </span>
                            </td>
                            
                            <?php if ($verCostos): ?>
                                <td class="has-text-right font-mono has-text-grey">
                                    <?= esc(dineroUnitario($f['costo_unitario'])) ?>
                                    <?php if ($f['origen_costo'] === 'compra'): ?>
                                        <span class="has-text-grey-light" title="Costo tomado de la última compra">*</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            
                            <td class="has-text-right font-mono font-weight-bold has-text-dark">
                                <?= $f['precio_venta'] > 0 ? esc(dineroUnitario($f['precio_venta'])) : '—' ?>
                            </td>
                            
                            <?php if ($verCostos): ?>
                                <td class="has-text-right font-mono font-weight-bold has-text-dark">
                                    <?= esc(dineroOGuion($f['valor_costo'])) ?>
                                </td>
                            <?php endif; ?>
                            
                            <td class="has-text-right font-mono font-weight-bold has-text-link">
                                <?= esc(dineroOGuion($f['valor_venta'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $columnas ?>" class="has-text-centered has-text-grey py-5">
                            <?= $hayFiltros ? 'Ningún producto coincide con los filtros de búsqueda aplicados.' : 'No hay productos registrados en el almacén.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <!-- PIE DE TABLA / TOTALES GENERALES -->
            <?php if (!empty($filas)): ?>
                <tfoot>
                    <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                        <th class="py-3 px-4 font-weight-bold has-text-dark" colspan="3">
                            <i class="fas fa-calculator has-text-link mr-2"></i> Total (<span class="has-text-link"><?= (int) $totales['productos'] ?></span> <?= $totales['productos'] === 1 ? 'producto' : 'productos' ?>)
                        </th>
                        <th class="py-3 px-4 text-right font-mono font-weight-bold has-text-link text-[14px]">
                            <?= esc(unidades($totales['unidades'])) ?>
                        </th>
                        <th class="py-3 px-4 text-right font-mono has-text-grey" colspan="2">—</th>
                        
                        <?php if ($verCostos): ?>
                            <th class="py-3 px-4"></th>
                        <?php endif; ?>
                        
                        <th class="py-3 px-4"></th>
                        
                        <?php if ($verCostos): ?>
                            <th class="py-3 px-4 text-right font-mono font-weight-bold has-text-dark text-[14px]">
                                <?= esc(dinero($totales['valor_costo'])) ?>
                            </th>
                        <?php endif; ?>
                        
                        <th class="py-3 px-4 text-right font-mono font-weight-bold has-text-link text-[15px]">
                            <?= esc(dinero($totales['valor_venta'])) ?>
                        </th>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    
    <!-- Leyenda y Métricas al pie -->
    <div class="px-6 py-3 is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap gap-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <p class="is-size-7 has-text-grey mb-0">
            <i class="fas fa-info-circle mr-1"></i> Stock bajo: existencias iguales o inferiores al mínimo. Valores expresados sin IVA.
            <?php if ($verCostos && $hayCostoDeCompra): ?>
                <br><span class="has-text-grey-light">*</span> Costo tomado de la última compra (producto sin precio base).
            <?php endif; ?>
        </p>
    </div>
</div>

<?php require __DIR__ . '/_fin.php'; ?>