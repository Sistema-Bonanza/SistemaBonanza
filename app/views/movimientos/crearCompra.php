<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada · Industrias Bonanza</title>
    
    <!-- Fuentes y Recursos de Iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <!-- CSS del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- SIDEBAR DEL SISTEMA -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <?php require_once __DIR__ . '/../../helpers/seguridad.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        
        <!-- Mensajes Flash del Sistema -->
        <?= mostrarFlash(); ?>

        <div class="p-5" style="width: 100%;">

            <!-- ========================================== -->
            <!-- 1. BANNER PRINCIPAL (ESTANDARIZADO BULMA) -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #003899 0%, #004ac6 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">input</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Registrar Entrada de Mercancía</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Recepción y Kardex
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Generar nueva recepción de almacén. Alimenta automáticamente el stock físico y pondera costos unitarios en el inventario.
                            </p>
                        </div>
                    </div>

                    <!-- Botón de Retorno -->
                    <div class="buttons mb-0">
                        <a href="<?= esc(urlAccion('movimiento', 'tablaMovimientos')) ?>" class="button is-white is-outlined has-text-white style-round" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- FORMULARIO DE RECEPCIÓN (KARDEX) -->
            <!-- ========================================== -->
            <form id="formCrearCompra" action="<?= esc(urlAccion('movimiento', 'guardarCompra')) ?>" method="POST">
                <?= campoCsrf(); ?>

                <!-- SECCIÓN 1: CABECERA Y PROVEEDOR -->
                <div class="box is-shadowless mb-5 p-5" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                    <div class="is-flex is-align-items-center is-justify-content-space-between pb-3 mb-4" style="border-bottom: 1px solid #e2e8f0;">
                        <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                            <span class="material-symbols-outlined has-text-link">receipt_long</span>
                            <h2 class="title is-5 mb-0 has-text-dark">1. Datos del Documento y Proveedor (Cabecera)</h2>
                        </div>
                        <span class="tag is-light is-danger is-small">Obligatorio (*)</span>
                    </div>

                    <div class="columns is-multiline">
                        <!-- Proveedor -->
                        <div class="column is-12-mobile is-6-tablet is-5-desktop">
                            <div class="field">
                                <label class="label is-size-7 has-text-grey" for="id_proveedor">Proveedor <span class="has-text-danger">*</span></label>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select name="id_proveedor" id="id_proveedor" required class="has-text-weight-semibold">
                                            <option value="">Seleccione un proveedor...</option>
                                            <?php 
                                            $provSeleccionado = recuperarFormulario('id_proveedor');
                                            foreach($proveedores as $prov): 
                                                $selected = ($provSeleccionado == $prov['id_proveedor']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= esc($prov['id_proveedor']) ?>" <?= $selected ?>>
                                                    <?= esc($prov['rif'] . ' - ' . $prov['razon_social']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey-light">
                                        <span class="material-symbols-outlined">local_shipping</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- N° de Factura / Control Fiscal -->
                        <div class="column is-12-mobile is-6-tablet is-3.5-desktop">
                            <div class="field">
                                <label class="label is-size-7 has-text-grey" for="numero_factura">N° de Factura / Control Fiscal <span class="has-text-danger">*</span></label>
                                <div class="control has-icons-left">
                                    <input type="text" name="numero_factura" id="numero_factura" class="input has-text-weight-bold" style="font-family: monospace;" required placeholder="Ej: 001-54321" maxlength="30" value="<?= esc(recuperarFormulario('numero_factura')) ?>">
                                    <span class="icon is-small is-left has-text-grey-light">
                                        <span class="material-symbols-outlined">tag</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha de Emisión -->
                        <div class="column is-12-mobile is-6-tablet is-3.5-desktop">
                            <div class="field">
                                <label class="label is-size-7 has-text-grey" for="fecha_factura">Fecha de Emisión <span class="has-text-danger">*</span></label>
                                <div class="control has-icons-left">
                                    <?php 
                                        $fechaHoy = date('Y-m-d');
                                        $fechaValue = recuperarFormulario('fecha_factura') ?: $fechaHoy;
                                    ?>
                                    <input type="date" name="fecha_factura" id="fecha_factura" class="input" style="font-family: monospace;" required value="<?= esc($fechaValue) ?>" max="<?= $fechaHoy ?>">
                                    <span class="icon is-small is-left has-text-grey-light">
                                        <span class="material-symbols-outlined">calendar_today</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: TABLA DE PRODUCTOS INGRESADOS -->
    <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <div class="p-4 is-flex is-align-items-center is-justify-content-space-between has-background-white-ter" style="border-bottom: 1px solid #e2e8f0;">
            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                <span class="material-symbols-outlined has-text-link">view_list</span>
                <div>
                    <h2 class="title is-5 mb-0 has-text-dark">2. Detalle de Productos Ingresados</h2>
                    <p class="is-size-7 has-text-grey">Capture cada ítem recibido para calcular inventario físico y costos ponderados</p>
                </div>
            </div>
            <!-- Actualizamos la etiqueta -->
            <span class="tag is-success is-light has-text-weight-bold">Líneas dinámicas</span>
        </div>

        <div class="table-container mb-0">
            <table class="table is-fullwidth is-hoverable is-vcentered mb-0" id="tablaDetalles">
                <thead>
                    <tr class="is-size-7 has-text-grey is-uppercase" style="background-color: #f8fafc;">
                        <th class="py-3 px-4 has-text-centered" style="width: 50px;">#</th>
                        <th class="py-3 px-4" style="width: 32%;">Producto Maestro</th>
                        <th class="py-3 px-4" style="width: 28%;">Empaque / Presentación</th>
                        <th class="py-3 px-4 has-text-centered" style="width: 110px;">Cant.</th>
                        <th class="py-3 px-4 has-text-right" style="width: 150px;">Costo / Empaque ($)</th>
                        <th class="py-3 px-4 has-text-right" style="width: 140px;">Subtotal ($)</th>
                        <th class="py-3 px-4 has-text-centered" style="width: 60px;">Acción</th>
                    </tr>
                </thead>
                <tbody id="tbodyDetalles" class="is-size-6">
                    <?php 
                        $prodsPost = recuperarFormulario('id_producto') ?: [];
                        $empaquesPost = recuperarFormulario('tipo_empaque') ?: [];
                        $cantsPost = recuperarFormulario('cantidad_empaques') ?: [];
                        $costosPost = recuperarFormulario('costo_por_empaque') ?: [];
                    
                        // Imprime mínimo 1 fila vacía, o las que traiga el POST en caso de error
                        $numFilasIniciales = max(1, count($prodsPost));
                    
                        for ($i = 0; $i < $numFilasIniciales; $i++): 
                            $prodVal = isset($prodsPost[$i]) ? $prodsPost[$i] : '';
                            $empVal = isset($empaquesPost[$i]) ? $empaquesPost[$i] : '';
                            $cantVal = isset($cantsPost[$i]) ? $cantsPost[$i] : '';
                            $costoVal = isset($costosPost[$i]) ? $costosPost[$i] : '';
                    ?>
                        <tr class="fila-detalle" data-index="<?= $i ?>">
                            <td class="has-text-centered has-text-grey-light has-text-weight-bold numero-fila"><?= $i + 1 ?></td>
                            
                            <!-- Producto -->
                            <td>
                                <div class="select is-small is-fullwidth">
                                    <select name="id_producto[]" class="select-producto" id="id_producto_<?= $i ?>">
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach($productos as $prod): 
                                            $selected = ($prodVal == $prod['id_producto']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= esc($prod['id_producto']) ?>" <?= $selected ?>>
                                                <?= esc($prod['codigo'] . ' - ' . $prod['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            
                            <!-- Presentación -->
                            <td>
                                <div class="field has-addons mb-0">
                                    <div class="control is-expanded">
                                        <div class="select is-small is-fullwidth">
                                            <select name="tipo_empaque[]" class="select-empaque" id="tipo_empaque_<?= $i ?>">
                                                <option value="">--- Seleccione ---</option>
                                                <?php foreach($empaques as $emp): 
                                                    $selected = ($empVal == $emp['id_presentacion']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= esc($emp['id_presentacion']) ?>" <?= $selected ?>>
                                                        <?= esc($emp['etiqueta'] . ' (' . $emp['unidades'] . ' uds) - ' . $emp['nombre_producto']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="control">
                                        <button type="button" class="button is-link is-light is-small btn-modal-emp" onclick="abrirModalPresentacion(<?= $i ?>)" title="Nueva presentación">
                                            <span class="icon is-small"><span class="material-symbols-outlined">add</span></span>
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Cantidad -->
                            <td>
                                <input type="number" name="cantidad_empaques[]" id="cantidad_empaques_<?= $i ?>" class="input is-small has-text-centered input-cantidad" style="font-family: monospace;" min="1" step="1" max="1000" placeholder="0" value="<?= esc($cantVal) ?>">
                            </td>
                            
                            <!-- Costo -->
                            <td>
                                <div class="control has-icons-left">
                                    <input type="number" step="0.01" name="costo_por_empaque[]" id="costo_por_empaque_<?= $i ?>" class="input is-small has-text-right input-costo" style="font-family: monospace;" min="0.01" placeholder="0.00" value="<?= esc($costoVal) ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                            </td>

                            <!-- Subtotal por Línea -->
                            <td class="has-text-right">
                                <span class="has-text-weight-bold text-subtotal" style="font-family: monospace;">$ 0,00</span>
                            </td>

                            <!-- Botón Eliminar Fila Mejorado -->
                            <td class="has-text-centered">
                                <button type="button" class="button is-small is-danger is-light btn-eliminar-fila" title="Eliminar fila de la factura">
                                    <span class="icon is-small"><span class="material-symbols-outlined">delete</span></span>
                                </button>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    
        <!-- NUEVA BARRA: Agregar Fila Dinámica -->
        <div class="p-3 has-background-white-ter is-flex is-align-items-center" style="border-top: 1px solid #e2e8f0;">
            <button type="button" class="button is-small is-link is-outlined has-text-weight-bold" id="btnAgregarFila">
                <span class="icon"><span class="material-symbols-outlined">add_circle</span></span>
                <span>Agregar nueva línea de producto</span>
            </button>
            <span class="is-size-7 has-text-grey ml-3">Puedes agregar todas las filas que necesites.</span>
        </div>
    </div>

                <!-- SECCIÓN 3: IMPACTO KARDEX Y RESUMEN ECONÓMICO -->
                <div class="columns is-multiline mb-5">
                    
                    <!-- Panel TSU Trazabilidad -->
                    <div class="column is-12-mobile is-7-desktop">
                        <div class="box is-shadowless h-100 p-5" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #f8fafc;">
                            <div class="is-flex is-align-items-center mb-4" style="gap: 0.75rem;">
                                <div class="has-background-success-light has-text-success p-2" style="border-radius: 10px;">
                                    <span class="material-symbols-outlined is-size-4">verified</span>
                                </div>
                                <div>
                                    <h3 class="title is-6 mb-0">Trazabilidad e Impacto Kardex</h3>
                                    <p class="is-size-7 has-text-grey">Validaciones operativas automáticas al confirmar la recepción</p>
                                </div>
                            </div>

                            <div class="columns is-multiline mb-3">
                                <!-- Caja Izquierda: Historial -->
                                <div class="column is-6">
                                    <div class="box is-shadowless p-3" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px;">
                                        <p class="has-text-weight-bold is-size-7 text-dark">Registro Histórico</p>
                                        <p class="is-size-7 has-text-link has-text-weight-semibold mb-1">Comprobante de Recepción</p>
                                        <p class="is-size-7 has-text-grey mt-1" style="line-height: 1.4;">
                                            Guarda el soporte legal, la fecha exacta, los datos del proveedor y el usuario responsable de la operación.
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Caja Derecha: Inventario -->
                                <div class="column is-6">
                                    <div class="box is-shadowless p-3" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px;">
                                        <p class="has-text-weight-bold is-size-7 text-dark">Impacto en Almacén</p>
                                        <p class="is-size-7 has-text-success has-text-weight-semibold mb-1">Stock y Valoración</p>
                                        <p class="is-size-7 has-text-grey mt-1" style="line-height: 1.4;">
                                            Suma la nueva mercancía a las existencias y actualiza el costo promedio ponderado automáticamente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Resumen Económico -->
                    <div class="column is-12-mobile is-5-desktop">
                        <div class="box is-shadowless p-5" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                            <div class="is-flex is-align-items-center is-justify-content-space-between pb-3 mb-3" style="border-bottom: 1px solid #e2e8f0;">
                                <h3 class="title is-6 mb-0">Resumen y Liquidación</h3>
                                <span class="material-symbols-outlined has-text-grey">payments</span>
                            </div>

                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                <span class="is-size-7 has-text-grey">Unidades / Empaques:</span>
                                <span class="has-text-weight-bold" id="totalEmpaquesLabel">0 unidades</span>
                            </div>

                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                <span class="is-size-7 has-text-grey">Subtotal Neto:</span>
                                <span class="has-text-weight-bold" id="subtotalLabel">$ 0,00</span>
                            </div>

                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
                                <span class="is-size-7 has-text-grey">Impuesto IVA (<?= defined('TASA_IVA') ? TASA_IVA : 16 ?>%):</span>
                                <span class="has-text-weight-bold" id="ivaLabel">$ 0,00</span>
                            </div>

                            <div class="p-3 has-background-link-light" style="border-radius: 10px;">
                                <div class="is-flex is-justify-content-space-between is-align-items-center">
                                    <div>
                                        <p class="is-size-7 has-text-link-dark is-uppercase has-text-weight-bold">Total Documento</p>
                                        <p class="is-size-7 has-text-link">USD Moneda Base</p>
                                    </div>
                                    <div class="has-text-right">
                                        <p class="title is-4 has-text-link mb-0" id="grandTotalLabel">$ 0,00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: BOTONERA DE ACCIÓN -->
                <div class="box is-shadowless p-4" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                    <div class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap" style="gap: 1rem;">
                        <div class="buttons mb-0">
                            <a href="<?= esc(urlAccion('movimiento', 'tablaMovimientos')) ?>" class="button is-light">Cancelar / Volver</a>
                            <button type="button" class="button is-ghost has-text-grey" id="btnResetForm">
                                <span class="icon is-small"><span class="material-symbols-outlined">restart_alt</span></span>
                                <span>Limpiar Formulario</span>
                            </button>
                        </div>

                        <button type="submit" class="button is-link has-text-weight-bold px-6" id="btnSubmitForm">
                            <span class="icon is-small"><span class="material-symbols-outlined">check_circle</span></span>
                            <span>Procesar y Actualizar Kardex</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <!-- ======================================================= -->
    <!-- MODAL AJAX PARA CREAR NUEVA PRESENTACIÓN -->
    <!-- ======================================================= -->
    <div class="modal" id="modalPresentacion">
        <div class="modal-background" onclick="cerrarModalPresentacion()"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title is-size-5 has-text-weight-bold">Registrar Nueva Presentación</p>
                <button class="delete" aria-label="close" type="button" onclick="cerrarModalPresentacion()"></button>
            </header>
            <section class="modal-card-body">
                <form id="formNuevaPresentacion">
                    <input type="hidden" name="csrf_token" id="csrf_token_modal" value="<?= esc($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="field mb-3">
                        <label class="label is-size-7">Producto al que pertenece</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="id_producto" id="producto_presentacion">
                                    <option value="">-- ★ ESTÁNDAR GLOBAL (Aplica a todos) ★ --</option>
                                    <?php foreach($productos as $prod): ?>
                                        <option value="<?= esc($prod['id_producto']) ?>">
                                            <?= esc($prod['codigo'] . ' - ' . $prod['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="columns is-mobile mb-0">
                        <div class="column is-8">
                            <div class="field">
                                <label class="label is-size-7">Nombre de la Presentación</label>
                                <div class="control">
                                    <input class="input" type="text" name="nombre" id="nombre_presentacion" placeholder="Ej. Caja, Bulto, Paca" required>
                                </div>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="field">
                                <label class="label is-size-7">Unidades</label>
                                <div class="control">
                                    <input class="input" type="number" name="unidades" id="unidades_presentacion" min="1" placeholder="Ej. 12" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="mensajeErrorModal" class="notification is-danger is-hidden is-light is-small mt-3"></div>
                </form>
            </section>
            <footer class="modal-card-foot is-justify-content-flex-end">
                <button class="button is-light" type="button" onclick="cerrarModalPresentacion()">Cancelar</button>
                <button class="button is-success" type="button" onclick="guardarPresentacionAjax()" id="btnGuardarPresentacion">Guardar Empaque</button>
            </footer>
        </div>
    </div>

    <!-- Inyectar Configuración Global de PHP a JavaScript -->
    <script>
        window.tasaIva = <?= defined('TASA_IVA') ? (float)TASA_IVA : 16.0 ?>;
        window.urlGuardarPresentacion = "<?= urlAccion('movimiento', 'guardarPresentacionAjax') ?>";
    </script>

    <!-- Librería SweetAlert2 y Script de Lógica Interactivas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/crearCompra.js?v=<?= time() ?>"></script>
</body>
</html>