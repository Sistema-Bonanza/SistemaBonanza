<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario · Industrias Bonanza</title>

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
            <div class="box mb-6" style="background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">person_add</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Administración
                                </span>
                                <span class="has-text-white-ter is-size-7">• Seguridad y Accesos</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Registrar Nuevo Usuario</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Complete los datos del formulario para dar de alta a un nuevo operador o administrador en el sistema.
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
                        
                        <div class="is-flex is-align-items-center mb-5 pb-3" style="gap: 12px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-info-light has-text-link" style="width: 38px; height: 38px; border-radius: 10px;">
                                <span class="material-symbols-outlined">badge</span>
                            </div>
                            <div>
                                <h2 class="title is-5 mb-0 has-text-dark">Datos de Acceso del Trabajador</h2>
                                <p class="is-size-7 has-text-grey">Los campos marcados con (<span class="has-text-danger font-bold">*</span>) son requeridos para emitir credenciales.</p>
                            </div>
                        </div>

                        <!-- Formulario de Alta -->
                        <form action="<?= urlAccion('usuario', 'guardar') ?>" method="POST" id="formCrearUsuario">
                            <?= campoCsrf() ?>

                            <!-- CAMPO 1: NOMBRE DE USUARIO -->
                            <div class="field mb-4">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="username">
                                        Nombre de Usuario <span class="has-text-danger">*</span>
                                    </label>
                                    <span class="tag is-info is-light is-rounded is-small font-mono">Identificador único</span>
                                </div>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" id="username" name="username" required placeholder="Ej: m_pacheco" autofocus style="border-radius: 8px; border-color: #cbd5e1;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">alternate_email</span>
                                    </span>
                                </div>
                                <p class="help has-text-grey is-size-7 mt-1">
                                    <span class="material-symbols-outlined is-size-7" style="vertical-align: middle;">info</span>
                                    Utilice letras minúsculas, números o guion bajo sin espacios (3 a 30 caracteres).
                                </p>
                            </div>

                            <!-- CAMPO 2: CONTRASEÑA -->
                            <div class="field mb-4">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="password">
                                        Contraseña <span class="has-text-danger">*</span>
                                    </label>
                                    <span class="tag is-link is-light is-rounded is-small">Mínimo 6 caracteres</span>
                                </div>
                                <div class="control has-icons-left has-icons-right">
                                    <input class="input" type="password" id="password" name="password" minlength="6" required placeholder="Ingrese una contraseña segura" style="border-radius: 8px; border-color: #cbd5e1;">
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">lock</span>
                                    </span>
                                    <button type="button" id="togglePassword" class="button is-small is-text is-right" style="position: absolute; right: 5px; top: 4px; height: 30px; text-decoration: none;" aria-label="Alternar visibilidad">
                                        <span class="material-symbols-outlined has-text-grey" id="eyeIcon">visibility</span>
                                    </button>
                                </div>
                            </div>

                            <!-- CAMPO 3: ROL EN EL SISTEMA -->
                            <div class="field mb-5">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <label class="label is-size-7 mb-0 has-text-grey-dark" for="rol">
                                        Rol en el Sistema <span class="has-text-danger">*</span>
                                    </label>
                                    <span class="tag is-success is-light is-rounded is-small">Privilegios RBAC</span>
                                </div>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select id="rol" name="rol" required style="border-color: #cbd5e1; border-radius: 8px;">
                                            <option value="" disabled selected>Seleccione un rol...</option>
                                            <option value="operador">Operador</option>
                                            <option value="administrador">Administrador</option>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey">
                                        <span class="material-symbols-outlined">admin_panel_settings</span>
                                    </span>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN -->
                            <div class="buttons is-right mt-5 pt-4" style="border-top: 1px solid #f1f5f9;">
                                <a href="<?= urlAccion('usuario', 'formusuario') ?>" class="button is-light" style="border-radius: 8px;">
                                    Cancelar
                                </a>
                                <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px; padding-left: 1.5rem; padding-right: 1.5rem;">
                                    <span class="icon"><span class="material-symbols-outlined">check_circle</span></span>
                                    <span>Guardar Usuario</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- COLUMNA DERECHA: GUÍAS TÉCNICAS RBAC (5 Cols) -->
                <div class="column is-5">
                    
                    <!-- Tarjeta 1: Políticas de Contraseña -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; background: white;">
                        <div class="is-flex is-align-items-center mb-3 pb-2" style="gap: 10px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-warning-light has-text-warning-dark" style="width: 34px; height: 34px; border-radius: 8px;">
                                <span class="material-symbols-outlined">security</span>
                            </div>
                            <div>
                                <h3 class="title is-6 mb-0 has-text-dark">Políticas de Seguridad</h3>
                                <p class="is-size-7 has-text-grey">Normativas para resguardo de la estación</p>
                            </div>
                        </div>

                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column" style="gap: 8px;">
                            <li class="p-2 is-flex is-align-items-center" style="background: #f8fafc; border-radius: 6px; gap: 8px;">
                                <span class="material-symbols-outlined has-text-success is-size-6">check_circle</span>
                                <span>Longitud mínima de <strong>8 caracteres</strong> alfanuméricos.</span>
                            </li>
                            <li class="p-2 is-flex is-align-items-center" style="background: #f8fafc; border-radius: 6px; gap: 8px;">
                                <span class="material-symbols-outlined has-text-success is-size-6">check_circle</span>
                                <span>Prohibido el uso de secuencias obvias o datos evidentes.</span>
                            </li>
                            <li class="p-2 is-flex is-align-items-center" style="background: #f8fafc; border-radius: 6px; gap: 8px;">
                                <span class="material-symbols-outlined has-text-link is-size-6">lock</span>
                                <span>Cifrado unidireccional `password_hash` en la base de datos.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Tarjeta 2: Niveles de Privilegio RBAC -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; background: white;">
                        <div class="is-flex is-align-items-center mb-3 pb-2" style="gap: 10px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-link-light has-text-link" style="width: 34px; height: 34px; border-radius: 8px;">
                                <span class="material-symbols-outlined">manage_accounts</span>
                            </div>
                            <div>
                                <h3 class="title is-6 mb-0 has-text-dark">Niveles de Privilegio (RBAC)</h3>
                                <p class="is-size-7 has-text-grey">Perfiles asignables en el sistema</p>
                            </div>
                        </div>

                        <div class="is-flex is-flex-direction-column" style="gap: 10px;">
                            <div class="p-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <span class="has-text-weight-bold is-size-7 has-text-dark">Operador</span>
                                    <span class="tag is-success is-light is-small font-semibold">Nivel Operativo</span>
                                </div>
                                <p class="is-size-7 has-text-grey-dark mb-0">
                                    Registro diario de entradas y salidas de almacén, verificación de existencias y consulta de proveedores.
                                </p>
                            </div>

                            <div class="p-3" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                    <span class="has-text-weight-bold is-size-7 has-text-dark">Administrador</span>
                                    <span class="tag is-link is-light is-small font-semibold">Control Total</span>
                                </div>
                                <p class="is-size-7 has-text-grey-dark mb-0">
                                    Gestión de usuarios, auditoría, configuración global del sistema y edición del catálogo de precios.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/crearUsuario.js"></script>
</body>
</html>