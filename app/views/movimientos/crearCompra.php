<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada · Distribuidora Bonanza</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
   <!-- Ruta absoluta a tu CSS centralizado -->
     <link rel="stylesheet" href="assets/css/sidebar.css" />
</head>
<body>

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <?php require_once __DIR__ . '/../../helpers/seguridad.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content"  style="width: 100%; flex-grow: 1; max-width: 100%; overflow-x: hidden; padding: 30px;">
        <div class="container is-max-widescreen">
            
            <!-- CABECERA DE LA PÁGINA -->
            <div class="level mb-6">
                <div class="level-left">
                    <div>
                        <h1 class="title is-4 mb-1">Registrar Entrada de Mercancía</h1>
                        <p class="has-text-grey mt-1">Generar nueva recepción (Kardex y Costos)</p>
                    </div>
                </div>
                <div class="level-right">
                    <a href="<?= urlAccion('movimiento', 'tablaMovimientos') ?>" class="button is-light">
                        <span class="icon is-small"><i class="fas fa-arrow-left"></i></span>
                        <span>Volver al listado</span>
                    </a>
                </div>
            </div>

            <!-- A12: MOSTRAR MENSAJES FLASH ARRIBA DEL FORMULARIO -->
            <?php echo mostrarFlash(); ?>

            <form action="<?php echo esc(urlAccion('movimiento', 'guardarCompra')); ?>" method="POST">
                <?php echo campoCsrf(); ?>

                <!-- 1. DATOS DE LA FACTURA -->
                <div class="box mb-6">
                    <h2 class="title is-5 mb-4 pb-2" style="border-bottom: 1px solid #ededed;">Datos de la Factura (Cabecera)</h2>
                    
                    <div class="columns is-multiline">
                        <!-- Proveedor -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label has-text-grey">Proveedor</label>
                                <div class="control has-icons-left is-expanded">
                                    <div class="select is-fullwidth">
                                        <select name="id_proveedor" required class="has-text-weight-semibold" id="<?= esc('id_proveedor') ?>">
                                            <option value="">Seleccione un proveedor...</option>
                                            <?php 
                                            // A12: Repoblar select
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
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-truck"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- N° Factura -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-grey">N° de Factura</label>
                                <div class="control has-icons-left">
                                    <!-- A12: maxlength y recuperar valor -->
                                    <input type="text" name="numero_factura" id="<?= esc('numero_factura') ?>" class="input has-text-weight-bold" style="font-family: monospace;" required placeholder="Ej: 001-54321" maxlength="30" value="<?= esc(recuperarFormulario('numero_factura')) ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-hashtag"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-grey">Fecha de Emisión</label>
                                <div class="control has-icons-left">
                                    <!-- A12: max igual a hoy y recuperar valor -->
                                    <?php 
                                        $fechaHoy = date('Y-m-d');
                                        $fechaValue = recuperarFormulario('fecha_factura') ?: $fechaHoy;
                                    ?>
                                    <input type="date" name="fecha_factura" id="<?= esc('fecha_factura') ?>" class="input" style="font-family: monospace;" required value="<?= esc($fechaValue) ?>" max="<?= $fechaHoy ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. DETALLE DE PRODUCTOS -->
                <div class="box p-0 mb-6" style="overflow: hidden;">
                    <div class="level px-4 py-3 has-background-white-ter mb-0" style="border-bottom: 1px solid #ededed;">
                        <div class="level-left">
                            <h2 class="has-text-weight-bold is-size-5">Productos Ingresados (Detalle)</h2>
                        </div>
                        <div class="level-right">
                            <span class="tag is-info is-light has-text-weight-bold">Máx 8 líneas (Fase 1)</span>
                        </div>
                    </div>
                    
                    <div class="table-container mb-0">
                        <table class="table is-fullwidth is-hoverable is-vcentered mb-0">
                            <thead class="has-background-white-ter">
                                <tr>
                                    <th class="has-text-centered" style="width: 50px;">#</th>
                                    <th style="width: 35%;">Producto</th>
                                    <th>Empaque</th>
                                    <th class="has-text-centered" style="width: 120px;">Cant.</th>
                                    <!-- A12: Renombrar columna -->
                                    <th class="has-text-right" style="width: 180px;">Costo por empaque ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // A12: Recuperar arreglos del detalle para repoblar
                                    $prodsPost = recuperarFormulario('id_producto') ?: [];
                                    $empaquesPost = recuperarFormulario('tipo_empaque') ?: [];
                                    $cantsPost = recuperarFormulario('cantidad_empaques') ?: [];
                                    $costosPost = recuperarFormulario('costo_por_empaque') ?: [];
                                
                                    for ($i = 0; $i < 8; $i++): 
                                        $prodVal = isset($prodsPost[$i]) ? $prodsPost[$i] : '';
                                        $empVal = isset($empaquesPost[$i]) ? $empaquesPost[$i] : '';
                                        $cantVal = isset($cantsPost[$i]) ? $cantsPost[$i] : '';
                                        $costoVal = isset($costosPost[$i]) ? $costosPost[$i] : '';
                                ?>
                                    <tr>
                                        <td class="has-text-centered has-text-grey-light has-text-weight-bold"><?= $i + 1 ?></td>
                                        
                                        <td>
                                            <div class="select is-small is-fullwidth">
                                                <select name="id_producto[]" id="<?= esc('id_producto_' . $i) ?>">
                                                    <option value="">-- Dejar vacío si no se usa --</option>
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
                                        
                                        <td>
                                            <div class="field has-addons">
                                                <div class="control is-expanded">
                                                    <div class="select is-small is-fullwidth">
                                                        <select name="tipo_empaque[]" id="<?= esc('tipo_empaque_' . $i) ?>">
                                                            <option value ="">--- Selecciones ---</option>
                                                            <?php foreach($empaques as $emp): 
                                                                $selected = ($empVal == $emp['id_presentacion']) ? 'selected' : '';
                                                            ?>
                                                                <option value="<?= esc($emp['id_presentacion']) ?>" <?= $selected ?>>
                                                                    <!-- Imprimimos: Caja (12 uds) - Nombre del Producto -->
                                                                    <?= esc($emp['etiqueta'] . ' (' . $emp['unidades'] . ' uds) - ' . $emp['nombre_producto']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="control">
                                                    <button type="button" class="button is-info is-light is-small" onclick="abrirModalPresentacion()" title="Añadir nueva presentación">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <!-- A12: step 1, max, y recuperar valor -->
                                            <input type="number" name="cantidad_empaques[]" id="<?= esc('cantidad_empaques_' . $i) ?>" class="input is-small has-text-centered" style="font-family: monospace;" min="1" step="1" max="1000" placeholder="0" value="<?= esc($cantVal) ?>">
                                        </td>
                                        
                                        <td>
                                            <div class="control has-icons-left">
                                                <!-- A12: min 0.01 y recuperar valor -->
                                                <input type="number" step="0.01" name="costo_por_empaque[]" id="<?= esc('costo_por_empaque_' . $i) ?>" class="input is-small has-text-right" style="font-family: monospace;" min="0.01" placeholder="0.00" value="<?= esc($costoVal) ?>">
                                                <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-dollar-sign"></i></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. BOTÓN DE PROCESAR -->
                <div class="is-flex is-justify-content-flex-end" style="gap: 1rem;">
                    <a href="<?= urlAccion('movimiento', 'tablaMovimientos') ?>" class="button is-ghost has-text-grey">Cancelar</a>
                    
                    <button type="submit" class="button is-link has-text-weight-bold px-6" onclick="return confirm('¿Confirma que los costos y cantidades son correctos? La factura no podrá editarse después.');">
                        <span class="icon is-small"><i class="fas fa-check-double"></i></span>
                        <span>Procesar y Actualizar Kardex</span>
                    </button>
                </div>

            </form>
        </div>
    </main>

    <!-- ======================================================= -->
    <!-- MODAL PARA CREAR NUEVA PRESENTACIÓN (EMPAQUE) -->
    <!-- ======================================================= -->
    <div class="modal" id="modalPresentacion">
    <div class="modal-background" onclick="cerrarModalPresentacion()"></div>
    <div class="modal-card">
        <header class="modal-card-head">
        <p class="modal-card-title">Registrar Nueva Presentación</p>
        <button class="delete" aria-label="close" type="button" onclick="cerrarModalPresentacion()"></button>
        </header>
        <section class="modal-card-body">
        <!-- Formulario AJAX -->
        <form id="formNuevaPresentacion">
        <!-- El token de seguridad es vital para AJAX también -->
        <input type="hidden" name="csrf_token" id="csrf_token_modal" value="<?= esc($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="columns is-multiline">
            <div class="column is-12">
                <div class="field">
                    <label class="label">Producto al que pertenece</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <!-- Le quitamos el 'required' -->
                            <select name="id_producto" id="producto_presentacion">
                                <!-- Opción principal vacía -->
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
            </div>
            <div class="column is-8">
                <div class="field">
                    <label class="label">Nombre de la Presentación</label>
                    <div class="control">
                        <input class="input" type="text" name="nombre" id="nombre_presentacion" placeholder="Ej. Caja, Bulto, Paca" required>
                    </div>
                </div>
            </div>
            <div class="column is-4">
                <div class="field">
                    <label class="label">Unidades</label>
                    <div class="control">
                        <input class="input" type="number" name="unidades" id="unidades_presentacion" min="1" placeholder="Ej. 12" required>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contenedor para mostrar errores desde AJAX -->
        <div id="mensajeErrorModal" class="notification is-danger is-hidden is-light is-small"></div>
      </form>
        </section>
        <footer class="modal-card-foot is-justify-content-flex-end">
        <button class="button" type="button" onclick="cerrarModalPresentacion()">Cancelar</button>
        <button class="button is-success" type="button" onclick="guardarPresentacionAjax()" id="btnGuardarPresentacion">Guardar Empaque</button>
        </footer>
    </div>
    </div>

    <script>
    // Abrir el modal
    function abrirModalPresentacion() {
        document.getElementById('modalPresentacion').classList.add('is-active');
    }

    // Cerrar el modal y limpiar los campos
    function cerrarModalPresentacion() {
        document.getElementById('modalPresentacion').classList.remove('is-active');
        document.getElementById('formNuevaPresentacion').reset();
        document.getElementById('mensajeErrorModal').classList.add('is-hidden');
        document.getElementById('mensajeErrorModal').innerHTML = '';
    }

    async function guardarPresentacionAjax() {
        const btnGuardar = document.getElementById('btnGuardarPresentacion');
        const mensajeError = document.getElementById('mensajeErrorModal');
        
        // Capturamos los datos
        const id_producto = document.getElementById('producto_presentacion').value;
        const nombre = document.getElementById('nombre_presentacion').value.trim();
        const unidades = document.getElementById('unidades_presentacion').value;
        const csrf = document.getElementById('csrf_token_modal').value;

        // Validación básica frontend
        if (!nombre || !unidades || unidades <= 0) {
            mensajeError.innerHTML = "Por favor selecciona un producto y completa todos los campos correctamente.";
            mensajeError.classList.remove('is-hidden');
            return;
        }

        // Efecto visual de carga
        btnGuardar.classList.add('is-loading');
        mensajeError.classList.add('is-hidden');

        try {
            // Preparamos el paquete de datos
            const formData = new FormData();
            formData.append('id_producto', id_producto);
            formData.append('nombre', nombre);
            formData.append('unidades', unidades);
            formData.append('csrf_token', csrf);

            // Enviamos los datos a PHP en segundo plano
            const response = await fetch("<?= urlAccion('movimiento', 'guardarPresentacionAjax') ?>", {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.ok) {
                // ÉXITO: Inyectar la nueva presentación en todos los <select> de la tabla
                const selectsEmpaques = document.querySelectorAll('select[name="tipo_empaque[]"]');
                selectsEmpaques.forEach(select => {
                    const nuevaOpcion = document.createElement('option');
                    // En el futuro, el value será el ID de la base de datos
                    nuevaOpcion.value = result.id_presentacion || result.nombre; 
                    nuevaOpcion.textContent = `${result.nombre} (${result.unidades} uds)`;
                    select.appendChild(nuevaOpcion);
                });

                // Cerrar modal
                cerrarModalPresentacion();
                alert("¡Presentación creada e inyectada en el formulario con éxito!");
            } else {
                // ERROR DESDE PHP
                mensajeError.innerHTML = result.error || "Error al procesar la solicitud.";
                mensajeError.classList.remove('is-hidden');
            }

        } catch (error) {
            mensajeError.innerHTML = "Error de conexión con el servidor.";
            mensajeError.classList.remove('is-hidden');
        } finally {
            btnGuardar.classList.remove('is-loading');
        }
    }
    </script>
</body>
</html> 