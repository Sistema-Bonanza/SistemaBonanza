<?php
// Validar que la variable $usuario exista para prevenir errores si no llega
$usuario =$usuario ?? [];
$idUser =$usuario['id'] ?? '';
$username =$usuario['username'] ?? '';
// Asegurar que coincida con los 'value' del select
$rol = ucfirst(strtolower($usuario['rol'] ?? 'Operador')); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario · Industrias Bonanza</title>

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

            <!-- BANNER HERO INSTITUCIONAL -->
            <div class="box mb-6" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">edit_square</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Administración
                                </span>
                                <span class="has-text-white-ter is-size-7">• Seguridad y Accesos</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Editar Usuario</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Actualiza la información, permisos y credenciales de acceso del usuario en el sistema.
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="<?= urlAccion('usuario', 'formusuario') ?>" class="button is-white is-outlined" style="border-radius: 8px; border-color: rgba(255,255,255,0.3); color: white;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL EN 2 COLUMNAS -->
            <div class="columns is-variable is-5">

                <!-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL (7 Cols) -->
                <div class="column is-7">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.75rem; background: white;">
                        
                        <div class="is-flex is-align-items-center is-justify-content-space-between mb-5 pb-3" style="border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="is-flex is-align-items-center is-justify-content-center has-background-info-light has-text-link" style="width: 38px; height: 38px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">manage_accounts</span>
                                </div>
                                <div>
                                    <h2 class="title is-5 mb-0 has-text-dark">Editar Datos de Usuario</h2>
                                    <p class="is-size-7 has-text-grey">Actualizando la cuenta seleccionada del sistema.</p>
                                </div>
                            </div>
                            <!-- ID del usuario en formato de badge -->
                            <span class="tag is-info is-light is-rounded has-text-weight-bold">UID: #<?= htmlspecialchars($idUser) ?></span>
                        </div>

                        <!-- Formulario de Edición -->
                        <form action="<?= urlAccion('usuario', 'editar') ?>" method="POST" id="formEditarUsuario">
                            
                            <?= campoCsrf() ?>
                            
                            <!-- Campos ocultos requeridos por el controlador y JS -->
                            <input type="hidden" name="id" value="<?= htmlspecialchars($idUser) ?>">
                            <input type="hidden" id="originalUsername" value="<?= htmlspecialchars($username) ?>">

                            <!-- CAMPO 1: NOMBRE DE USUARIO -->
                            <div class="field mb-4">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="username">
                                        Nombre de Usuario <span class="has-text-danger">*</span>
                                    </label>
                                    <span class="tag is-info is-light is-rounded is-small font-mono">Identificador único</span>
                                </div>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required style="border-radius: 8px; border-color: #cbd5e1;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">alternate_email</span>
                                    </span>
                                </div>
                            </div>

                            <!-- CAMPO 2: CONTRASEÑA (OPCIONAL) -->
                            <div class="field mb-4">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="password">
                                        Contraseña <span class="has-text-grey font-normal">(Opcional)</span>
                                    </label>
                                    <span class="tag is-link is-light is-rounded is-small">Mínimo 6 caracteres</span>
                                </div>
                                <div class="control has-icons-left has-icons-right">
                                    <input class="input" type="password" id="password" name="password" minlength="6" placeholder="Dejar en blanco para mantener la actual" style="border-radius: 8px; border-color: #cbd5e1;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">lock_reset</span>
                                    </span>
                                    <button type="button" id="togglePassword" class="button is-small is-text is-right" style="position: absolute; right: 5px; top: 4px; height: 30px; text-decoration: none;" aria-label="Alternar visibilidad">
                                        <span class="material-symbols-outlined has-text-grey" id="eyeIcon">visibility</span>
                                    </button>
                                </div>
                                <p class="help has-text-grey is-size-7 mt-1">
                                    Si no deseas cambiar la contraseña, simplemente deja este campo vacío.
                                </p>
                            </div>

                            <!-- CAMPO 3: ROL EN EL SISTEMA -->
                            <div class="field mb-5">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="rol">
                                        Rol en el Sistema <span class="has-text-danger">*</span>
                                    </label>
                                </div>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select id="rol" name="rol" required style="border-color: #cbd5e1; border-radius: 8px;">
                                            <option value="Operador" <?= ($rol === 'Operador') ? 'selected' : '' ?>>Operador</option>
                                            <option value="Administrador" <?= ($rol === 'Administrador') ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">admin_panel_settings</span>
                                    </span>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN -->
                            <div class="buttons is-right mt-5 pt-4" style="border-top: 1px solid #f1f5f9;">
                                <button type="button" id="btnRestaurar" class="button is-light mr-auto" style="border-radius: 8px;">
                                    <span class="icon"><span class="material-symbols-outlined">restart_alt</span></span>
                                    <span>Restaurar</span>
                                </button>
                                <a href="<?= urlAccion('usuario', 'formusuario') ?>" class="button is-white border" style="border-radius: 8px; border-color: #e2e8f0;">
                                    Cancelar
                                </a>
                                <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px; padding-left: 1.5rem; padding-right: 1.5rem;">
                                    <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                    <span>Actualizar Datos</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- COLUMNA DERECHA: GUÍAS TÉCNICAS (5 Cols) -->
                <div class="column is-5">
                    
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; background: white;">
                        <div class="is-flex is-align-items-center mb-3 pb-2" style="gap: 10px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-warning-light has-text-warning-dark" style="width: 34px; height: 34px; border-radius: 8px;">
                                <span class="material-symbols-outlined">security</span>
                            </div>
                            <div>
                                <h3 class="title is-6 mb-0 has-text-dark">Políticas de Edición</h3>
                                <p class="is-size-7 has-text-grey">Protección de cuenta</p>
                            </div>
                        </div>

                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column" style="gap: 8px;">
                            <li class="p-2 is-flex is-align-items-start" style="background: #f8fafc; border-radius: 6px; gap: 8px;">
                                <span class="material-symbols-outlined has-text-success is-size-6 mt-1">check_circle</span>
                                <div><strong class="has-text-dark">Cifrado unidireccional:</strong> Las contraseñas se almacenan con hashing seguro y nunca se muestran en texto plano.</div>
                            </li>
                            <li class="p-2 is-flex is-align-items-start" style="background: #f8fafc; border-radius: 6px; gap: 8px;">
                                <span class="material-symbols-outlined has-text-link is-size-6 mt-1">logout</span>
                                <div><strong class="has-text-dark">Cierre de sesión:</strong> Modificar la contraseña desconectará automáticamente otras terminales del usuario.</div>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/modulos/editarUsuario.js"></script>
</body>
</html>