<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Producto · POS & Inventario</title>
    
    <!-- Framework Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- CSS Propios del Sistema -->
    <link rel="stylesheet" href="<?= esc(URL_BASE) ?>/assets/css/sidebar.css" />
    <link rel="stylesheet" href="<?= esc(URL_BASE) ?>/assets/css/producto.css" />
</head>
<body class="is-flex" style="height: 100vh; overflow: hidden; width: 100%;">
    
    <!-- ========== SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto;">
        <?= mostrarFlash() ?>

        <div class="p-5" style="width: 100%;">
            
            <!-- 1. BANNER PRINCIPAL (HERO) -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">inventory</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Registrar Nuevo Producto</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Catálogo 2026
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Completa el formulario para incorporar mercancía estandarizada al catálogo general de inventario.
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

            <!-- 2. CONTENEDOR PRINCIPAL (GRID DIVIDIDA) -->
            <div class="columns is-variable is-4">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO (8 Columnas) -->
                <div class="column is-8-desktop">
                    <div class="box is-shadowless p-0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        
                        <!-- Cabecera del Formulario -->
                        <div class="has-background-link p-4 is-flex is-justify-content-space-between is-align-items-center">
                            <div class="is-flex is-align-items-center has-text-white" style="gap: 0.5rem;">
                                <span class="material-symbols-outlined">assignment_add</span>
                                <h2 class="title is-6 has-text-white mb-0">Datos del Artículo</h2>
                            </div>
                            <span class="tag is-success is-light is-rounded has-text-weight-bold">Formulario Activo</span>
                        </div>

                        <!-- Formulario de Registro (Apunta a tu controlador) -->
                        <form action="<?= esc(urlAccion('producto', 'guardarProducto')) ?>" method="POST" class="p-5" data-ajax>
                            <?= campoCsrf() ?>
                            
                            <!-- SECCIÓN 1: Datos Indispensables -->
                            <div class="mb-5">
                                <div class="is-flex is-align-items-center mb-4" style="gap: 0.5rem;">
                                    <span style="width: 6px; height: 16px; background-color: #2563eb; border-radius: 4px;"></span>
                                    <h3 class="title is-6 mb-0" style="font-size: 15px; text-transform: uppercase;">1. Datos Indispensables</h3>
                                </div>

                                <div class="columns">
                                    <!-- Código SKU -->
                                    <div class="column">
                                        <div class="field">
                                            <label class="label is-small" for="codigo">Código de Producto (SKU) <span class="has-text-danger">*</span></label>
                                            <div class="control has-icons-left has-icons-right">
                                                <input class="input" type="text" id="codigo" name="codigo" placeholder="Ej. PROD-001" required style="text-transform: uppercase;">
                                                <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">barcode_scanner</span></span>
                                        
                                            </div>
                                            <p class="help">Identificador único de tarima o casillero.</p>
                                        </div>
                                    </div>

                                    <!-- Nombre del Producto -->
                                    <div class="column">
                                        <div class="field">
                                            <label class="label is-small" for="nombre">Nombre del Producto <span class="has-text-danger">*</span></label>
                                            <div class="control has-icons-left">
                                                <input class="input" type="text" id="nombre" name="nombre" placeholder="Ej. Harina Pan 1Kg" required>
                                                <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">tag</span></span>
                                            </div>
                                            <p class="help">Denominación comercial clara registrada.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Categoría Operativa -->
                                <div class="field mt-3">
                                    <label class="label is-small" for="id_categoria">Categoría Operativa <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <div class="select is-fullwidth">
                                            <select id="id_categoria" name="id_categoria" required>
                                                <option value="" disabled selected>-- Seleccione una Categoría --</option>
                                                
                                                <!-- Bucle dinámico para mostrar las categorías reales de la Base de Datos -->
                                                <?php if (!empty($categorias)): ?>
                                                    <?php foreach ($categorias as $cat): ?>
                                                        <option value="<?= $cat['id_categoria'] ?>">
                                                            <?= htmlspecialchars($cat['nombre']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                
                                            </select>
                                        </div>
        <span class="icon is-left has-text-grey"><span class="material-symbols-outlined is-size-5">category</span></span>
    </div>
</div>
                            </div>

                            <hr style="background-color: #e2e8f0; height: 1px; border: none;" />

                            <!-- SECCIÓN 2: Datos Adicionales e Inventario -->
                            <div class="mb-5">
                                <div class="is-flex is-align-items-center mb-4" style="gap: 0.5rem;">
                                    <span style="width: 6px; height: 16px; background-color: #48c774; border-radius: 4px;"></span>
                                    <h3 class="title is-6 mb-0" style="font-size: 15px; text-transform: uppercase;">2. Atributos Fiscales</h3>
                                </div>

                                <!-- Checkbox de IVA -->
                                <div class="box is-shadowless" style="background-color: #f8f9ff; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <label class="checkbox is-flex is-align-items-start" style="cursor: pointer; gap: 10px;">
                                        <input type="checkbox" id="aplica_iva" name="aplica_iva" value="1" checked style="margin-top: 4px;">
                                        <div>
                                            <strong>¿Aplica IVA? (Alícuota General 16%)</strong>
                                            <p class="is-size-7 has-text-grey mt-1">Marcar si el producto está sujeto a impuesto sobre el valor agregado en facturación fiscal.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <hr style="background-color: #e2e8f0; height: 1px; border: none;" />

                            <!-- 3. INVENTARIO: stock mínimo y unidad de manejo -->
                            <div class="mb-5">
                                <div class="is-flex is-align-items-center mb-4" style="gap: 0.5rem;">
                                    <span style="width: 6px; height: 16px; background-color: #f59e0b; border-radius: 4px;"></span>
                                    <h3 class="title is-6 mb-0" style="font-size: 15px; text-transform: uppercase;">3. Inventario</h3>
                                </div>
                                <div class="columns is-multiline">
                                    <div class="column is-12-mobile is-4-tablet">
                                        <div class="field">
                                            <label class="label is-small" for="stock_minimo">Stock mínimo</label>
                                            <div class="control">
                                                <input class="input" type="number" id="stock_minimo" name="stock_minimo" min="0" max="1000000" step="1" value="0" inputmode="numeric">
                                            </div>
                                            <p class="help">Por debajo de esta cantidad el producto se marca en rojo. 0 = sin alerta.</p>
                                        </div>
                                    </div>
                                    <div class="column is-12-mobile is-4-tablet">
                                        <div class="field">
                                            <label class="label is-small" for="unidad_manejo">Unidad de manejo</label>
                                            <div class="control">
                                                <input class="input" type="text" id="unidad_manejo" name="unidad_manejo" maxlength="30" value="UNIDAD" list="sugerenciasUnidad">
                                                <datalist id="sugerenciasUnidad">
                                                    <option value="UNIDAD"><option value="PAQUETE"><option value="CAJA"><option value="BULTO"><option value="DOCENA"><option value="PACA">
                                                </datalist>
                                            </div>
                                            <p class="help">Cómo viene empacado: UNIDAD, CAJA, PAQUETE...</p>
                                        </div>
                                    </div>
                                    <div class="column is-12-mobile is-4-tablet">
                                        <div class="field">
                                            <label class="label is-small" for="unidades_por_empaque">Unidades por empaque</label>
                                            <div class="control">
                                                <input class="input" type="number" id="unidades_por_empaque" name="unidades_por_empaque" min="1" max="100000" step="1" value="1" inputmode="numeric">
                                            </div>
                                            <p class="help">Cuántas unidades trae cada empaque.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr style="background-color: #e2e8f0; height: 1px; border: none;" />

                            <!-- BOTONES DE ACCIÓN (FOOTER DEL FORMULARIO) -->
                            <div class="is-flex is-justify-content-space-between is-align-items-center">
                                <button class="button is-light" type="reset">Limpiar Campos</button>
                                <button class="button is-link has-text-weight-bold" type="submit">
                                    <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                    <span>Guardar Producto</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ASISTENTES TÉCNICOS (4 Columnas) -->
                <div class="column is-4-desktop">
                    
                    <!-- Tarjeta de Instrucciones -->
                    <div class="box is-shadowless mb-4" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                        <div class="is-flex is-align-items-center mb-2 has-text-link" style="gap: 0.5rem;">
                            <span class="material-symbols-outlined">info</span>
                            <h4 class="title is-6 mb-0">Instrucciones Operativas</h4>
                        </div>
                        <p class="is-size-7 has-text-grey-dark">
                            Los campos señalados con asterisco (<span class="has-text-danger font-weight-bold">*</span>) son obligatorios para generar la ficha de lote y habilitar movimientos de carga y descarga.
                        </p>
                    </div>

                    <!-- Tarjeta de Integridad y Validación -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                        <div class="is-flex is-align-items-center justify-content-space-between pb-3 mb-3" style="border-bottom: 1px solid #e2e8f0;">
                            <div class="is-flex is-align-items-center has-text-link" style="gap: 0.5rem;">
                                <span class="material-symbols-outlined">verified_user</span>
                                <h4 class="title is-6 mb-0">Integridad del Sistema</h4>
                            </div>
                        </div>

                        <div class="content is-small">
                            <div class="is-flex is-align-items-start mb-3" style="gap: 8px;">
                                <span class="material-symbols-outlined has-text-success is-size-5">check_circle</span>
                                <div>
                                    <strong>Código único verificado</strong>
                                    <p class="has-text-grey mb-0">Validación estricta de Primary Key para evitar duplicados en almacén.</p>
                                </div>
                            </div>

                            <div class="is-flex is-align-items-start" style="gap: 8px;">
                                <span class="material-symbols-outlined has-text-link is-size-5">info</span>
                                <div>
                                    <strong>Impacto en Existencias</strong>
                                    <p class="has-text-grey mb-0">El stock inicial se registrará en cero (0) y se alimentará mediante notas de recepción.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        
        <!-- Cargar script desacoplado -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="assets/js/ajax.js"></script>
        <script src="assets/js/crear-producto.js"></script>
    </main>

</body>
</html>