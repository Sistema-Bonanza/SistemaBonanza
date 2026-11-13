<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto · POS & Inventario</title>
    
    <!-- Framework Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- CSS del Sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    <link rel="stylesheet" href="assets/css/editar-producto.css" />
</head>
<body class="is-flex" style="height: 100vh; overflow: hidden; width: 100%;">
    
    <!-- ========== SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto;">
        <?= mostrarFlash() ?>

        <div class="p-5" style="width: 100%;">
            <div class="max-w-5xl mx-auto w-full space-y-6">

                <!-- 1. BANNER SUPERIOR (HERO) CORREGIDO Y DEFINITIVO -->
                <div class="box mb-5" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                        <div class="is-flex is-align-items-center" style="gap: 1rem;">
                            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                                <span class="material-symbols-outlined is-size-3 has-text-white">edit_note</span>
                            </div>
                            <div>
                                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                    <h1 class="title is-4 has-text-white mb-0">Editar Producto</h1>
                                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> ID: <?= htmlspecialchars($producto['id_producto'] ?? '') ?>
                                    </span>
                                </div>
                                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                    Modifica la información de identidad del artículo maestro seleccionado en el sistema de inventario.
                                </p>
                            </div>
                        </div>
                        <div class="buttons mb-0">
                            <button class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" onclick="window.history.back()" type="button">
                                <span class="icon"><span class="material-symbols-outlined">keyboard_backspace</span></span>
                                <span>Regresar</span>
                            </button>
                        </div>
                    
                    </div>
                </div>

            </div>

                <!-- 2. CONTENEDOR PRINCIPAL: GRID DIVIDIDA (8 y 4 Columnas) -->
                <div class="columns is-variable is-4 items-start">
                    
                    <!-- COLUMNA IZQUIERDA: FORMULARIO DE EDICIÓN (8 Col) -->
                    <div class="column is-8-desktop">
                        <div class="box is-shadowless p-5" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            
                            <!-- Header de la tarjeta del formulario -->
                            <div class="is-flex is-justify-content-space-between is-align-items-center pb-4 mb-4" style="border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                        <span class="material-symbols-outlined has-text-link">tune</span>
                                        <h2 class="title is-6 mb-0">Datos del Artículo</h2>
                                    </div>
                                    <p class="is-size-7 has-text-grey mt-1">Actualiza el código, nombre o clasificación del producto.</p>
                                </div>
                                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                    <span class="tag is-info is-light"># ID Activo: <?= htmlspecialchars($producto['codigo'] ?? '000') ?></span>
                                    <span class="tag is-success is-light">En Catálogo</span>
                                </div>
                            </div>

                            <!-- Formulario Principal -->
                            <form action="index.php?controller=producto&action=actualizarProducto" method="POST" id="editProductForm" data-ajax>

                                <!-- ID Oculto -->
                                <input type="hidden" name="id_producto" value="<?= htmlspecialchars($producto['id_producto'] ?? '') ?>">

                                <div class="columns">
                                    <!-- Código SKU -->
                                    <div class="column">
                                        <div class="field">
                                            <label class="label is-small" for="codigo">Código de Producto <span class="has-text-danger">*</span></label>
                                            <div class="control has-icons-left">
                                                <input class="input" type="text" id="codigo" name="codigo" 
                                                       value="<?= htmlspecialchars($producto['codigo'] ?? '') ?>" required>
                                                <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">barcode_scanner</span></span>
                                            </div>
                                            <p class="help">Clave primaria única registrada.</p>
                                        </div>
                                    </div>

                                    <!-- Categoría Dinámica -->
                                    <div class="column">
                                        <div class="field">
                                            <label class="label is-small" for="id_categoria">Categoría <span class="has-text-danger">*</span></label>
                                            <div class="control has-icons-left">
                                                <div class="select is-fullwidth">
                                                    <select id="id_categoria" name="id_categoria" required>
                                                        <option value="">-- Seleccione --</option>
                                                        <?php if(!empty($categorias)): ?>
                                                            <?php foreach($categorias as $cat): ?>
                                                                <option value="<?= $cat['id_categoria'] ?>" 
                                                                    <?= (isset($producto['id_categoria']) && $producto['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">category</span></span>
                                            </div>
                                            <p class="help">Línea de producción clasificada.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nombre del Producto -->
                                <div class="field mt-3">
                                    <label class="label is-small" for="nombre">Nombre del Producto <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" id="nombre" name="nombre" 
                                               value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>
                                        <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">label</span></span>
                                    </div>
                                    <p class="help">Etiqueta de impresión física en lotes.</p>
                                </div>

                                <!-- Checkbox de IVA interactivo -->
                                <div class="field mt-4">
                                    <div class="box is-shadowless" style="background-color: #f8f9ff; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <label class="checkbox is-flex is-align-items-start" style="cursor: pointer; gap: 10px;">
                                            <input type="checkbox" id="aplica_iva" name="aplica_iva" value="1" 
                                                   <?= (!empty($producto['aplica_iva']) && $producto['aplica_iva'] == 1) ? 'checked' : '' ?> style="margin-top: 4px;">
                                            <div class="is-flex is-justify-content-space-between is-align-items-center w-full">
                                                <div>
                                                    <strong>¿Aplica Impuesto al Valor Agregado (IVA)?</strong>
                                                    <p class="is-size-7 has-text-grey mt-1">Grava el porcentaje tributario fiscal nacional (16.0%) en órdenes y facturación.</p>
                                                </div>
                                                <span class="tag is-success is-light">Tributable 16%</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Stock mínimo (alerta de reposición) -->
                                <div class="box is-shadowless mt-4" style="background-color: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px;">
                                    <div class="columns is-vcentered is-mobile is-multiline">
                                        <div class="column is-12-mobile is-7-tablet">
                                            <strong>Stock mínimo</strong>
                                            <p class="is-size-7 has-text-grey mt-1">
                                                Si las existencias bajan de esta cantidad, la tabla de productos marca el artículo en rojo y avisa con una alerta.
                                                Usa 0 para no recibir alerta.
                                            </p>
                                        </div>
                                        <div class="column is-12-mobile is-5-tablet">
                                            <div class="field has-addons">
                                                <div class="control is-expanded">
                                                    <input class="input has-text-right" type="number" id="stock_minimo" name="stock_minimo"
                                                           min="0" max="1000000" step="1" inputmode="numeric"
                                                           value="<?= (int) ($producto['stock_minimo'] ?? 0) ?>">
                                                </div>
                                                <div class="control"><span class="button is-static">unidades</span></div>
                                            </div>
                                            <p class="help">Existencias actuales: <strong><?= (int) ($producto['stock_actual'] ?? 0) ?></strong></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Unidades del producto (cómo viene empacado) -->
                                <div class="box is-shadowless mt-4" style="background-color: #f8f9ff; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <strong>Unidad de manejo</strong>
                                    <p class="is-size-7 has-text-grey mt-1 mb-3">Cómo se presenta el producto y cuántas unidades trae cada empaque (ej.: CAJA de 12 unidades).</p>
                                    <div class="columns is-mobile is-multiline">
                                        <div class="column is-12-mobile is-6-tablet">
                                            <div class="field">
                                                <label class="label is-small" for="unidad_manejo">Presentación</label>
                                                <div class="control">
                                                    <input class="input" type="text" id="unidad_manejo" name="unidad_manejo" maxlength="30"
                                                           list="sugerenciasUnidad" placeholder="UNIDAD, CAJA, PAQUETE..."
                                                           value="<?= htmlspecialchars($producto['unidad_manejo'] ?? 'UNIDAD') ?>">
                                                    <datalist id="sugerenciasUnidad">
                                                        <option value="UNIDAD"><option value="PAQUETE"><option value="CAJA"><option value="BULTO"><option value="DOCENA"><option value="PACA">
                                                    </datalist>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="column is-12-mobile is-6-tablet">
                                            <div class="field">
                                                <label class="label is-small" for="unidades_por_empaque">Unidades por empaque</label>
                                                <div class="control">
                                                    <input class="input" type="number" id="unidades_por_empaque" name="unidades_por_empaque"
                                                           min="1" max="100000" step="1" inputmode="numeric"
                                                           value="<?= (int) ($producto['unidades_por_empaque'] ?? 1) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr style="background-color: #e2e8f0; height: 1px; border: none; margin: 2rem 0;" />

                                <!-- Botones inferiores -->
                                <div class="is-flex is-flex-direction-column-touch is-justify-content-space-between is-align-items-center" style="gap: 1rem;">
                                    <button class="button is-light" type="button" onclick="window.history.back()">
                                        <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                                        <span>Volver Atrás / Descartar</span>
                                    </button>
                                    
                                    <button class="button is-link has-text-weight-bold" type="submit">
                                        <span class="icon"><span class="material-symbols-outlined">sync</span></span>
                                        <span>Actualizar Producto</span>
                                    </button>
                                </div>

                                <!-- Botón de desactivación sutil -->
                                <div class="mt-4 pt-3 is-flex is-justify-content-space-between is-align-items-center" style="border-top: 1px dashed #e2e8f0;">
                                    <span class="is-size-7 has-text-grey">¿Este producto ha sido descontinuado en la planta?</span>
                                    <button class="button is-small is-danger is-light" id="btnDeactivate" type="button" data-csrf="<?= htmlspecialchars(tokenCsrf()) ?>">
                                        <span class="icon"><span class="material-symbols-outlined is-size-6">do_not_disturb_on</span></span>
                                        <span>Desactivar producto del catálogo</span>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: FICHA TÉCNICA Y TRAZABILIDAD (4 Col) -->
                    <div class="column is-4-desktop">
                        <div class="box is-shadowless p-4" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                            <div class="is-flex is-align-items-center mb-3" style="gap: 0.5rem;">
                                <span class="material-symbols-outlined has-text-grey">history_toggle_off</span>
                                <h3 class="title is-6 mb-0">Trazabilidad de Registro</h3>
                            </div>
                            
                            <div class="content is-small space-y-2">
                                <div class="is-flex is-justify-content-space-between py-1 px-2 has-background-white-ter rounded">
                                    <span class="has-text-grey">Creado por:</span>
                                    <strong>admin (Gral)</strong>
                                </div>
                                <div class="is-flex is-justify-content-space-between py-1 px-2 has-background-white-ter rounded">
                                    <span class="has-text-grey">Stock en Piso:</span>
                                    <?php $bajoMin = ((int) ($producto['stock_minimo'] ?? 0)) > 0 && ((int) $producto['stock_actual']) < ((int) $producto['stock_minimo']); ?>
                                    <strong class="<?= $bajoMin ? 'has-text-danger' : 'has-text-link' ?>"><?= (int) ($producto['stock_actual'] ?? 0) ?> UND</strong>
                                </div>
                                <div class="is-flex is-justify-content-space-between py-1 px-2 has-background-white-ter rounded">
                                    <span class="has-text-grey">Stock mínimo:</span>
                                    <strong id="resumenStockMinimo"><?= (int) ($producto['stock_minimo'] ?? 0) ?> UND</strong>
                                </div>
                                <div class="is-flex is-justify-content-space-between py-1 px-2 has-background-white-ter rounded">
                                    <span class="has-text-grey">Unidad de Manejo:</span>
                                    <strong id="resumenUnidadManejo"><?= htmlspecialchars(($producto['unidad_manejo'] ?? 'UNIDAD')) ?> · <?= (int) ($producto['unidades_por_empaque'] ?? 1) ?> <?= ((int) ($producto['unidades_por_empaque'] ?? 1)) === 1 ? 'unidad' : 'unidades' ?></strong>
                                </div>
                            </div>

                            <div class="notification is-info is-light p-3 mt-3 is-size-7">
                                <span class="material-symbols-outlined is-size-6" style="vertical-align: middle;">info</span>
                                La modificación de datos maestros del artículo se registrará en la bitácora de auditoría del sistema.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        
        <!-- Cargar script desacoplado -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="assets/js/ajax.js"></script>
        <script src="assets/js/editar-producto.js"></script>
    </main>

</body>
</html>