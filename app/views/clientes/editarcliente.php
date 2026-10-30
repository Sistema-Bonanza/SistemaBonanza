<?php
// Manejo seguro de la variable recibida desde el controlador
$c = $clientes ?? [];
$id = $c['id'] ?? $c['id_cliente'] ?? '';
$tipoDoc = $c['tipo_documento'] ?? 'V';
$numDoc = $c['num_documento'] ?? '';
$nombre = $c['nombre_razon_social'] ?? '';
$telefono = $c['telefono'] ?? '';
$email = $c['email'] ?? '';
$direccion = $c['direccion'] ?? '';
$estado = $c['estado'] ?? 1;
$creadoEn = $c['creado_en'] ?? '';

// Iniciales para el avatar del panel lateral
$iniciales = !empty($nombre) ? strtoupper(substr($nombre, 0, 2)) : 'CL';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente · Industrias Bonanza</title>
    
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
                            <span class="material-symbols-outlined is-size-3 has-text-warning">edit_square</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Directorio Comercial
                                </span>
                                <span class="has-text-white-ter is-size-7">• Edición de Ficha</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Editar Cliente</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Actualice los datos de contacto y despacho del cliente en el maestro del sistema.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="index.php?controller=cliente&action=tablaClientes" class="button is-white has-text-link has-text-weight-bold" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important; border-radius: 8px;">
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
                    <form action="index.php?controller=cliente&action=actualizar" method="POST" id="formEditarCliente" class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem;">
                        
                        <!-- CAMPOS OCULTOS -->
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="estado" value="<?= htmlspecialchars($estado) ?>">
                        <input type="hidden" name="creado_en" value="<?= htmlspecialchars($creadoEn) ?>">
                        
                        <!-- REENVIAMOS LOS DATOS BLOQUEADOS EN CAMPOS OCULTOS PARA QUE POST LOS RECIBA -->
                        <input type="hidden" name="tipo_documento" value="<?= htmlspecialchars($tipoDoc) ?>">
                        <input type="hidden" name="num_documento" value="<?= htmlspecialchars($numDoc) ?>">
                        <input type="hidden" name="nombre_razon_social" value="<?= htmlspecialchars($nombre) ?>">

                        <!-- Header del Formulario -->
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-5" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center" style="width: 40px; height: 40px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">manage_accounts</span>
                                </div>
                                <div>
                                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                                        <h2 class="title is-5 mb-0 has-text-dark">Modificar Información</h2>
                                        <?php if ($id): ?>
                                            <span class="tag is-info is-light font-mono has-text-weight-bold">ID: CLI-<?= htmlspecialchars($id) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="is-size-7 has-text-grey">Los datos fiscales principales están protegidos por auditoría.</p>
                                </div>
                            </div>
                            <span class="tag is-<?= $estado == 1 ? 'success' : 'danger' ?> is-light has-text-weight-bold">
                                <?= $estado == 1 ? 'Cliente Activo' : 'Cliente Inactivo' ?>
                            </span>
                        </div>

                        <!-- Banner de Integridad -->
                        <div class="notification is-info is-light p-3 is-size-7 is-flex is-align-items-center mb-5" style="border-radius: 8px; border: 1px solid #bfdbfe; gap: 10px;">
                            <span class="material-symbols-outlined has-text-link is-size-5">lock</span>
                            <span><strong>Integridad de Datos:</strong> Documento de Identificación y Razón Social protegidos contra modificación directa. Solo se permite actualizar contacto y despacho.</span>
                        </div>

                        <!-- FILA 1: Tipo y Número de Documento (BLOQUEADOS) -->
                        <div class="columns is-variable is-3 mb-4">
                            <div class="column is-4">
                                <div class="field">
                                    <div class="is-flex is-align-items-center is-justify-content-space-between mb-1">
                                        <label class="label is-size-7 has-text-grey mb-0">Tipo</label>
                                        <span class="is-size-7 has-text-grey-light is-flex is-align-items-center" style="gap: 2px;">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">lock</span> Bloqueado
                                        </span>
                                    </div>
                                    <div class="control has-icons-left">
                                        <div class="select is-fullwidth">
                                            <select disabled style="border-radius: 8px; border-color: #cbd5e1; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
                                                <option value="V" <?= $tipoDoc === 'V' ? 'selected' : '' ?>>V - Venezolano</option>
                                                <option value="E" <?= $tipoDoc === 'E' ? 'selected' : '' ?>>E - Extranjero</option>
                                                <option value="J" <?= $tipoDoc === 'J' ? 'selected' : '' ?>>J - Jurídico</option>
                                                <option value="G" <?= $tipoDoc === 'G' ? 'selected' : '' ?>>G - Gubernamental</option>
                                            </select>
                                        </div>
                                        <span class="icon is-small is-left has-text-grey-light">
                                            <span class="material-symbols-outlined">badge</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-8">
                                <div class="field">
                                    <div class="is-flex is-align-items-center is-justify-content-space-between mb-1">
                                        <label class="label is-size-7 has-text-grey mb-0">Número</label>
                                        <span class="is-size-7 has-text-grey-light is-flex is-align-items-center" style="gap: 2px;">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">lock</span> Solo Lectura
                                        </span>
                                    </div>
                                    <div class="control has-icons-left">
                                        <input class="input font-mono" type="text" value="<?= htmlspecialchars($numDoc) ?>" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
                                        <span class="icon is-small is-left has-text-grey-light">
                                            <span class="material-symbols-outlined">tag</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILA 2: Nombre / Razón Social (BLOQUEADO) -->
                        <div class="field mb-4">
                            <div class="is-flex is-align-items-center is-justify-content-space-between mb-1">
                                <label class="label is-size-7 has-text-grey mb-0">Nombre / Razón Social</label>
                                <span class="is-size-7 has-text-grey-light is-flex is-align-items-center" style="gap: 2px;">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">lock</span> Protegido por Auditoría
                                </span>
                            </div>
                            <div class="control has-icons-left">
                                <input class="input" type="text" value="<?= htmlspecialchars($nombre) ?>" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
                                <span class="icon is-small is-left has-text-grey-light">
                                    <span class="material-symbols-outlined">domain</span>
                                </span>
                            </div>
                        </div>

                        <!-- FILA 3: Teléfono y Email (EDITABLES) -->
                        <div class="columns is-variable is-3 mb-4">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-size-7 has-text-dark" for="telefono">
                                        Teléfono de Contacto
                                    </label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono) ?>" placeholder="Ej: 0414-1234567" style="border-radius: 8px; border-color: #cbd5e1;">
                                        <span class="icon is-small is-left has-text-link">
                                            <span class="material-symbols-outlined">call</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label is-size-7 has-text-dark" for="email">
                                        Correo Electrónico
                                    </label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Ej: cliente@correo.com" style="border-radius: 8px; border-color: #cbd5e1;">
                                        <span class="icon is-small is-left has-text-link">
                                            <span class="material-symbols-outlined">mail</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILA 4: Dirección (EDITABLE) -->
                        <div class="field mb-6">
                            <label class="label is-size-7 has-text-dark" for="direccion">
                                Dirección Fiscal / Despacho
                            </label>
                            <div class="control">
                                <textarea class="textarea" id="direccion" name="direccion" rows="3" placeholder="Ej: Av. Principal, Zona Industrial..." style="border-radius: 8px; border-color: #cbd5e1; resize: none;"><?= htmlspecialchars($direccion) ?></textarea>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="is-flex is-align-items-center is-justify-content-flex-end" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem; gap: 12px;">
                            <a href="index.php?controller=cliente&action=tablaClientes" class="button is-light has-text-weight-semibold" style="border-radius: 8px;">
                                Cancelar
                            </a>
                            <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                                <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                <span>Actualizar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- COLUMNA DERECHA: PANELES DE APOYO (4 Cols) -->
                <!-- ========================================== -->
                <div class="column is-4">
                    
                    <!-- Panel 1: Ficha Resumen del Cliente -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-4" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <h3 class="title is-6 mb-0 has-text-dark">Ficha Actual</h3>
                            <span class="tag is-<?= $estado == 1 ? 'success' : 'danger' ?> is-light is-rounded has-text-weight-bold" style="font-size: 0.65rem;">
                                <?= $estado == 1 ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                        
                        <!-- Encabezado de Perfil -->
                        <div class="is-flex is-align-items-center mb-4" style="gap: 12px;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-link-dark has-text-white has-text-weight-bold" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1rem;">
                                <?= $iniciales ?>
                            </div>
                            <div>
                                <p class="has-text-weight-bold has-text-dark mb-0" style="font-size: 0.95rem;"><?= htmlspecialchars($nombre ?: 'Cliente') ?></p>
                                <p class="is-size-7 has-text-grey font-mono mb-0"><?= htmlspecialchars($tipoDoc . '-' . $numDoc) ?></p>
                            </div>
                        </div>

                        <!-- Micro Métricas -->
                        <div class="p-3 mb-2 has-background-white-ter" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span class="is-size-7 is-uppercase has-text-grey has-text-weight-bold is-block">Código Interno</span>
                            <span class="is-size-6 font-mono has-text-weight-bold has-text-dark">CLI-<?= htmlspecialchars($id ?: '---') ?></span>
                        </div>
                        <?php if ($creadoEn): ?>
                            <div class="p-3 has-background-white-ter" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                <span class="is-size-7 is-uppercase has-text-grey has-text-weight-bold is-block">Fecha de Registro</span>
                                <span class="is-size-7 font-mono has-text-dark"><?= htmlspecialchars($creadoEn) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Panel 2: Instrucciones Operativas -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center mb-3" style="gap: 8px;">
                            <span class="material-symbols-outlined has-text-info is-size-5">lightbulb</span>
                            <h3 class="title is-6 mb-0 has-text-dark">Instrucciones Operativas</h3>
                        </div>
                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column" style="gap: 12px; line-height: 1.4;">
                            <li class="is-flex" style="gap: 8px;">
                                <span class="tag is-info is-light is-rounded has-text-weight-bold" style="height: 1.4em;">1</span>
                                <span><strong class="has-text-dark">Datos de Contacto:</strong> Puede modificar libremente teléfono, correo electrónico y dirección.</span>
                            </li>
                            <li class="is-flex" style="gap: 8px;">
                                <span class="tag is-success is-light is-rounded has-text-weight-bold" style="height: 1.4em;">2</span>
                                <span><strong class="has-text-dark">Sincronización:</strong> Los cambios de dirección o teléfono se actualizarán de inmediato en los nuevos despachos.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/modulos/editar_cliente.js"></script>
</body>
</html> 