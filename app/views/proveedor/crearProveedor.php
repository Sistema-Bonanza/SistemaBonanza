<?php
// Inicialización preventiva del listado para los paneles laterales
$proveedores = $proveedores ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Proveedor · Industrias Bonanza</title>
    
    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        
        <!-- Interceptor de mensajes Flash PHP para SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO -->
            <!-- ========================================== -->
            <div class="box mb-6" style="background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">domain_add</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Gestión de Suministros
                                </span>
                                <span class="has-text-white-ter is-size-7">• Alta de Proveedores</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Registrar Nuevo Proveedor</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Complete los datos fiscales y de contacto para dar de alta a un nuevo proveedor en el sistema.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="index.php?controller=proveedor&action=TablaProveedores" class="button is-white has-text-link has-text-weight-bold" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important; border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="columns is-variable is-5">
                <!-- ========================================== -->
                <!-- COLUMNA IZQUIERDA: FORMULARIO (8 Cols) -->
                <!-- ========================================== -->
                <div class="column is-8">
                    <form action="index.php?controller=proveedor&action=guardar" method="POST" id="formCrearProveedor" class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem;">
                        
                        <!-- Header del Formulario -->
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-5" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center" style="width: 40px; height: 40px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">badge</span>
                                </div>
                                <div>
                                    <h2 class="title is-5 mb-0 has-text-dark">Datos del Proveedor</h2>
                                    <p class="is-size-7 has-text-grey">Ingreso de parámetros tributarios y canales directos.</p>
                                </div>
                            </div>
                            <span class="tag is-danger is-light has-text-weight-bold">Campos obligatorios (*)</span>
                        </div>


                        <!-- FILA 1: RIF / Identificación Fiscal -->
                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark" for="rif">
                                RIF / Identificación Fiscal <span class="has-text-danger">*</span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input font-mono" type="text" id="rif" name="rif" placeholder="Ej: J-12345678-0" required autofocus style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-link">
                                    <span class="material-symbols-outlined">pin</span>
                                </span>
                            </div>
                            <p class="help is-size-7 has-text-grey">Formato estándar recomendado: J-12345678-0 / V-12345678-0</p>
                        </div>

                        <!-- FILA 2: Razón Social / Nombre Comercial -->
                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark" for="razon_social">
                                Razón Social / Nombre Comercial <span class="has-text-danger">*</span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="razon_social" name="razon_social" placeholder="Ej: Distribuidora Bonanza C.A." required style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">corporate_fare</span>
                                </span>
                            </div>
                        </div>

                        <!-- FILA 3: Nombre del Contacto y Teléfono -->
                        <div class="columns is-variable is-3 mb-4">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-size-7 has-text-dark" for="nombre_contacto">
                                        Nombre del Contacto
                                    </label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" id="nombre_contacto" name="nombre_contacto" placeholder="Ej: Carlos Pérez" style="border-radius: 8px; border-color: #cbd5e1;">
                                        <span class="icon is-small is-left has-text-grey">
                                            <span class="material-symbols-outlined">person</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-size-7 has-text-dark" for="telefono">
                                        Teléfono de Contacto
                                    </label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" id="telefono" name="telefono" placeholder="Ej: 0414-1234567" style="border-radius: 8px; border-color: #cbd5e1;">
                                        <span class="icon is-small is-left has-text-grey">
                                            <span class="material-symbols-outlined">call</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILA 4: Correo Electrónico -->
                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark" for="email">
                                Correo Electrónico
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="email" id="email" name="email" placeholder="Ej: contacto@proveedor.com" style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">alternate_email</span>
                                </span>
                            </div>
                        </div>

                        <!-- FILA 5: Dirección Fiscal -->
                        <div class="field mb-6">
                            <label class="label is-size-7 has-text-dark" for="direccion">
                                Dirección Fiscal
                            </label>
                            <div class="control">
                                <textarea class="textarea" id="direccion" name="direccion" placeholder="Ej: Av. Principal, Zona Industrial..." rows="3" style="border-radius: 8px; border-color: #cbd5e1; resize: none;"></textarea>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="is-flex is-align-items-center is-justify-content-flex-end" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem; gap: 12px;">
                            <a href="index.php?controller=proveedor&action=TablaProveedores" class="button is-light has-text-weight-semibold" style="border-radius: 8px;">
                                Cancelar
                            </a>
                            <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                                <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                <span>Guardar Proveedor</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- COLUMNA DERECHA: PANELES DE APOYO (4 Cols) -->
                <!-- ========================================== -->
                <div class="column is-4">
                    
                    <!-- Panel 1: Registros Existentes -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-4" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <div class="is-flex is-align-items-center" style="gap: 8px;">
                                <span class="material-symbols-outlined has-text-link is-size-5">inventory_2</span>
                                <h3 class="title is-6 mb-0 has-text-dark">Padrón Actual</h3>
                            </div>
                            <span class="tag is-success is-light is-rounded has-text-weight-bold" style="font-size: 0.65rem;">
                                <?= count($proveedores) ?> Activos
                            </span>
                        </div>
                        <p class="is-size-7 has-text-grey mb-3">Consulte los registros actuales para prevenir duplicidades:</p>
                        
                        <div style="max-height: 240px; overflow-y: auto; padding-right: 4px;">
                            <?php if (!empty($proveedores)): ?>
                                <?php $recientes = array_slice(array_reverse($proveedores), 0, 4); ?>
                                <?php foreach ($recientes as $prov): ?>
                                    <?php 
                                        $razon = $prov['razon_social'] ?? $prov['nombre_razon_social'] ?? 'Sin Nombre';
                                        $rifVal = $prov['rif'] ?? (isset($prov['tipo_documento'], $prov['num_documento']) ? $prov['tipo_documento'].'-'.$prov['num_documento'] : 'N/A');
                                    ?>
                                    <div class="is-flex is-align-items-center is-justify-content-space-between p-3 mb-2 has-background-white" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <div class="is-flex is-flex-direction-column" style="max-width: 150px;">
                                            <span class="is-size-7 has-text-weight-bold has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($razon) ?></span>
                                            <span class="is-size-7 has-text-grey"><?= htmlspecialchars($prov['telefono'] ?? 'S/N') ?></span>
                                        </div>
                                        <span class="tag is-light is-rounded font-mono has-text-link has-text-weight-bold" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($rifVal) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="is-size-7 has-text-grey-light is-italic">No hay proveedores registrados aún.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Panel 2: Instrucciones Operativas -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center mb-3" style="gap: 8px;">
                            <span class="material-symbols-outlined has-text-info is-size-5">rule</span>
                            <h3 class="title is-6 mb-0 has-text-dark">Instrucciones Operativas</h3>
                        </div>
                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column" style="gap: 12px; line-height: 1.4;">
                            <li class="is-flex" style="gap: 8px;">
                                <span class="tag is-info is-light is-rounded has-text-weight-bold" style="height: 1.4em;">1</span>
                                <span><strong class="has-text-dark">Formato de RIF:</strong> Asegúrese de escribir la letra del RIF (J, V, G, E) seguida de guión y números.</span>
                            </li>
                            <li class="is-flex" style="gap: 8px;">
                                <span class="tag is-success is-light is-rounded has-text-weight-bold" style="height: 1.4em;">2</span>
                                <span><strong class="has-text-dark">Disponibilidad:</strong> Al guardar, el proveedor quedará habilitado de inmediato para las órdenes de compra.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/crearProveedor.js"></script>
</body>
</html>