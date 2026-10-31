<?php
// Aseguramos la lectura segura de las variables enviadas desde el controlador
$proveedor = $proveedor ?? [];
$idProv = $proveedor['id_proveedor'] ?? $proveedor['id'] ?? '';
$rifVal = $proveedor['rif'] ?? (isset($proveedor['tipo_documento'], $proveedor['num_documento']) ? $proveedor['tipo_documento'] . '-' . $proveedor['num_documento'] : '');
$razonVal = $proveedor['razon_social'] ?? $proveedor['nombre_razon_social'] ?? 'Sin Nombre';
$contactoVal = $proveedor['nombre_contacto'] ?? $proveedor['contacto_directo'] ?? $proveedor['contacto'] ?? '';
$telefonoVal = $proveedor['telefono'] ?? '';
$emailVal = $proveedor['email'] ?? '';
$direccionVal = $proveedor['direccion'] ?? '';
$estadoVal = $proveedor['estado'] ?? 1;

// Métricas del proveedor para la tarjeta informativa
$totalRecepciones = $proveedor['total_recepciones'] ?? $totalRecepciones ?? 0;
$ultimaEntrada = $proveedor['ultima_entrada'] ?? $ultimaEntrada ?? 'Sin entradas';
$iniciales = strtoupper(substr(htmlspecialchars($razonVal), 0, 2));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor · Industrias Bonanza</title>

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

            <!-- BANNER HERO -->
            <div class="box mb-6" style="background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">edit_note</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Gestión de Suministros
                                </span>
                                <span class="has-text-white-ter is-size-7">• Actualización de Ficha</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Editar Proveedor</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Actualice los datos de contacto directos de <strong><?= htmlspecialchars($razonVal) ?></strong>.
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
                <!-- COLUMNA IZQUIERDA: FORMULARIO (8 Cols) -->
                <div class="column is-8">
                    <form action="index.php?controller=proveedor&action=actualizar" method="POST" id="formEditarProveedor" class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem;">
                        
                        <!-- ID Oculto para la actualización -->
                        <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars($idProv) ?>">

                        <!-- Header del Formulario -->
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-5" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center" style="width: 40px; height: 40px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">edit</span>
                                </div>
                                <div>
                                    <h2 class="title is-5 mb-0 has-text-dark">Modificar Información de Contacto</h2>
                                    <p class="is-size-7 has-text-grey">Valide los accesos telefónicos y electrónicos directos.</p>
                                </div>
                            </div>
                            <span class="tag is-info is-light font-mono">ID: #<?= htmlspecialchars($idProv) ?></span>
                        </div>

                        <!-- SECCIÓN CAMPOS BLOQUEADOS POR AUDITORÍA -->
                        <div class="notification is-light mb-5" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem;">
                            <div class="is-flex is-align-items-center mb-3" style="gap: 6px;">
                                <span class="material-symbols-outlined has-text-grey">lock</span>
                                <span class="is-size-7 is-uppercase font-semibold has-text-grey">Información Fiscal Protegida</span>
                            </div>

                            <!-- CAMPO BLOQUEADO: RIF -->
                            <div class="field mb-3">
                                <label class="label is-size-7 has-text-grey" for="rif">
                                    RIF / Identificación Fiscal 🔒
                                </label>
                                <div class="control has-icons-left">
                                    <input class="input font-mono" type="text" id="rif" name="rif" value="<?= htmlspecialchars($rifVal) ?>" readonly style="background-color: #e2e8f0; cursor: not-allowed; color: #475569; border-radius: 8px;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">badge</span>
                                    </span>
                                </div>
                            </div>

                            <!-- CAMPO BLOQUEADO: RAZÓN SOCIAL -->
                            <div class="field mb-3">
                                <label class="label is-size-7 has-text-grey" for="razon_social">
                                    Razón Social / Nombre Comercial 🔒
                                </label>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" id="razon_social" name="razon_social" value="<?= htmlspecialchars($razonVal) ?>" readonly style="background-color: #e2e8f0; cursor: not-allowed; color: #475569; border-radius: 8px;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">corporate_fare</span>
                                    </span>
                                </div>
                            </div>

                            <!-- CAMPO BLOQUEADO: DIRECCIÓN FISCAL -->
                            <div class="field mb-0">
                                <label class="label is-size-7 has-text-grey" for="direccion">
                                    Dirección Fiscal 🔒
                                </label>
                                <div class="control">
                                    <textarea class="textarea" id="direccion" name="direccion" readonly rows="2" style="background-color: #e2e8f0; cursor: not-allowed; color: #475569; border-radius: 8px; resize: none;"><?= htmlspecialchars($direccionVal) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CAMPOS EDITABLES -->
                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark" for="nombre_contacto">
                                Nombre del Contacto <span class="has-text-danger">*</span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="nombre_contacto" name="nombre_contacto" value="<?= htmlspecialchars($contactoVal) ?>" required placeholder="Ej: Carlos Pérez" autofocus style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-link">
                                    <span class="material-symbols-outlined">person</span>
                                </span>
                            </div>
                        </div>

                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark" for="telefono">
                                Teléfono de Contacto <span class="has-text-danger">*</span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($telefonoVal) ?>" required placeholder="Ej: 0414-1234567" style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">call</span>
                                </span>
                            </div>
                        </div>

                        <div class="field mb-6">
                            <label class="label is-size-7 has-text-dark" for="email">
                                Correo Electrónico de Contacto
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="email" id="email" name="email" value="<?= htmlspecialchars($emailVal) ?>" placeholder="Ej: contacto@proveedor.com" style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">alternate_email</span>
                                </span>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="is-flex is-align-items-center is-justify-content-flex-end" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem; gap: 12px;">
                            <a href="index.php?controller=proveedor&action=TablaProveedores" class="button is-light has-text-weight-semibold" style="border-radius: 8px;">
                                Cancelar
                            </a>
                            <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                                <span class="icon"><span class="material-symbols-outlined">sync</span></span>
                                <span>Actualizar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- COLUMNA DERECHA: INFORMACIÓN DEL PROVEEDOR (4 Cols) -->
                <div class="column is-4">
                    
                    <!-- CUADRO SUPERIOR DERECHO -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        
                        <!-- Header / Avatar e Identificación -->
                        <div class="is-flex is-align-items-center mb-4" style="gap: 14px;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-link-dark has-text-white has-text-weight-bold flex-shrink-0" style="width: 50px; height: 50px; border-radius: 12px; font-size: 1.1rem; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                                <?= $iniciales ?>
                            </div>
                            <div class="is-flex is-flex-direction-column min-w-0">
                                <h3 class="title is-6 mb-0 has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($razonVal) ?></h3>
                                <span class="is-size-7 font-mono has-text-grey mt-0.5">RIF: <?= htmlspecialchars($rifVal) ?></span>
                            </div>
                        </div>

                        <!-- Pill de Estado Comercial -->
                        <div class="p-3 mb-4 is-flex is-align-items-center is-justify-content-space-between" style="border-radius: 8px; background-color: <?= $estadoVal == 1 ? '#ecfdf5' : '#fef2f2' ?>; border: 1px solid <?= $estadoVal == 1 ? '#a7f3d0' : '#fecaca' ?>;">
                            <span class="is-size-7 font-semibold" style="color: <?= $estadoVal == 1 ? '#065f46' : '#991b1b' ?>;">Estado Comercial</span>
                            <span class="tag is-rounded <?= $estadoVal == 1 ? 'is-success' : 'is-danger' ?> is-light has-text-weight-bold" style="font-size: 0.7rem;">
                                <?= $estadoVal == 1 ? 'Proveedor Activo' : 'Proveedor Inactivo' ?>
                            </span>
                        </div>

                        <!-- Métricas Rápidas -->
                        <div class="is-flex is-flex-direction-column" style="gap: 8px;">
                            <div class="is-flex is-align-items-center is-justify-content-space-between p-3" style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">
                                <div class="is-flex is-align-items-center" style="gap: 8px;">
                                    <span class="material-symbols-outlined has-text-link is-size-6">inventory_2</span>
                                    <span class="is-size-7 has-text-dark font-medium">Total Recepciones</span>
                                </div>
                                <span class="is-size-7 font-semibold has-text-link"><?= htmlspecialchars($totalRecepciones) ?> Órdenes</span>
                            </div>

                            <div class="is-flex is-align-items-center is-justify-content-space-between p-3" style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">
                                <div class="is-flex is-align-items-center" style="gap: 8px;">
                                    <span class="material-symbols-outlined has-text-grey is-size-6">calendar_month</span>
                                    <span class="is-size-7 has-text-dark font-medium">Última Entrada</span>
                                </div>
                                <span class="is-size-7 font-semibold has-text-grey"><?= htmlspecialchars($ultimaEntrada) ?></span>
                            </div>
                        </div>

                    </div>

                    <!-- Panel Explicativo -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-3" style="gap: 8px;">
                            <span class="material-symbols-outlined has-text-link is-size-5">info</span>
                            <h3 class="title is-6 mb-0 has-text-dark">Modificación Rápida</h3>
                        </div>
                        <p class="is-size-7 has-text-grey mb-0" style="line-height: 1.5;">
                            Para activar o inactivar (soft delete) a este proveedor, utilice el botón de estado directamente en la <strong class="has-text-dark">Tabla Principal de Proveedores</strong>.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/editarProveedor.js"></script>
</body>
</html>