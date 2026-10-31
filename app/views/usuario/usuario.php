<?php
// Garantizar que la variable $usuarios exista
$usuarios = $usuarios ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= tokenCsrf() ?>">
    <title>Gestión de Usuarios · Industrias Bonanza</title>

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
                            <span class="material-symbols-outlined is-size-3 has-text-white">manage_accounts</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Administración
                                </span>
                                <span class="has-text-white-ter is-size-7">• Seguridad y Accesos</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Gestión de Usuarios</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Administra los usuarios del sistema. Solo los administradores pueden realizar cambios estructurales o asignaciones de credenciales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CONTENIDO PRINCIPAL EN 2 COLUMNAS -->
            <!-- ========================================== -->
            <div class="columns is-variable is-5">

                <!-- COLUMNA IZQUIERDA: TABLA Y FILTROS (8 Cols) -->
                <div class="column is-8">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        
                        <!-- Header de la Tabla -->
                        <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap mb-4" style="gap: 1rem;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="has-background-slate-100 has-text-grey-dark is-flex is-align-items-center is-justify-content-center" style="width: 40px; height: 40px; border-radius: 10px; background-color: #f1f5f9;">
                                    <span class="material-symbols-outlined">badge</span>
                                </div>
                                <div>
                                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                                        <h2 class="title is-5 mb-0 has-text-dark">Usuarios del Sistema</h2>
                                        <span class="tag is-info is-light is-rounded has-text-weight-bold" id="contadorFilas">
                                            <?= count($usuarios) ?> Cuentas
                                        </span>
                                    </div>
                                    <p class="is-size-7 has-text-grey">Cuentas activas registradas en la base de datos local</p>
                                </div>
                            </div>

                            <!-- Botones de Filtro por Rol / Estado -->
                            <div class="buttons has-addons mb-0">
                                <button type="button" class="button is-small is-link is-selected btn-filtro" data-filtro="todos">Todos</button>
                                <button type="button" class="button is-small is-light btn-filtro" data-filtro="admin">Admin</button>
                                <button type="button" class="button is-small is-light btn-filtro" data-filtro="operador">Operador</button>
                            </div>
                        </div>

                        <!-- Buscador en tiempo real -->
                        <div class="field mb-5">
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="buscadorUsuarios" placeholder="Buscar usuario por nombre o identificador..." style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">search</span>
                                </span>
                            </div>
                        </div>

                        <!-- Tabla bulma adaptada -->
                        <div class="table-container">
                            <table class="table is-fullwidth is-hoverable" id="tablaUsuarios">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold">Usuario</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold">Rol Asignado</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold">Estado</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold text-right" style="text-align: right;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $a): ?>
                                            <?php
                                                $idUser = $a['id'] ?? '';
                                                $username = $a['username'] ?? 'user';
                                                $rolRaw = strtolower($a['rol'] ?? 'operador');
                                                $esAdmin = (str_contains($rolRaw, 'admin') || str_contains($rolRaw, 'administrador'));
                                                $esSuperAdmin = ($username === 'admin' || $idUser == 1);
                                                
                                                // CORRECCIÓN: Leemos directamente 'is_active' de la base de datos
                                                // Si no existe (null), asume 0 para no mostrar falsos activos.
                                                $estadoVal = $a['is_active'] ?? 0;
                                                $esActivo = ($estadoVal == 1);
                                                
                                                $inicial = strtoupper(substr($username, 0, 1));
                                            ?>
                                            <tr class="fila-busqueda" 
                                                data-rol="<?= $esAdmin ? 'admin' : 'operador' ?>" 
                                                data-estado="<?= $esActivo ? 'activo' : 'inactivo' ?>">
                                                
                                                <!-- Usuario / Username -->
                                                <td class="is-vcentered">
                                                    <div class="is-flex is-align-items-center" style="gap: 12px;">
                                                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white has-text-weight-bold flex-shrink-0" 
                                                             style="width: 40px; height: 40px; border-radius: 10px; font-size: 0.9rem; background: <?= $esAdmin ? 'linear-gradient(135deg, #1e3a8a, #0f172a)' : 'linear-gradient(135deg, #312e81, #1e1b4b)' ?>;">
                                                            <?= $inicial ?>
                                                        </div>
                                                        <div class="is-flex is-flex-direction-column">
                                                            <div class="is-flex is-align-items-center" style="gap: 4px;">
                                                                <span class="has-text-weight-semibold has-text-dark is-size-6 nombre-user"><?= htmlspecialchars($username) ?></span>
                                                                <?php if ($esSuperAdmin): ?>
                                                                    <span class="material-symbols-outlined is-size-6 has-text-warning" title="Superusuario">verified</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <span class="is-size-7 font-mono has-text-grey username-user">@<?= htmlspecialchars($username) ?></span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Rol Badge -->
                                                <td class="is-vcentered">
                                                    <span class="tag is-rounded has-text-weight-semibold <?= $esAdmin ? 'is-link' : 'is-info' ?> is-light" style="font-size: 0.75rem;">
                                                        <span class="material-symbols-outlined is-size-6 mr-1"><?= $esAdmin ? 'admin_panel_settings' : 'person' ?></span>
                                                        <?= htmlspecialchars($a['rol'] ?? 'Operador') ?>
                                                    </span>
                                                </td>

                                                <!-- Estado Badge -->
                                                <td class="is-vcentered">
                                                    <?php if ($esActivo): ?>
                                                        <span class="tag is-success is-light has-text-weight-semibold" style="border-radius: 6px;">
                                                            <?= $esSuperAdmin ? 'Activo (Maestra)' : 'Activo' ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="tag is-danger is-light has-text-weight-semibold" style="border-radius: 6px;">
                                                            Inactivo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Botones de Acción -->
                                                <td class="is-vcentered text-right" style="text-align: right;">
                                                    <div class="buttons is-right mb-0" style="gap: 6px; justify-content: flex-end;">
                                                        
                                                        <!-- Editar -->
                                                        <a href="<?= urlAccion('usuario', 'formusuarioeditar') ?>&id=<?= $idUser ?>" class="button is-small is-link is-light" title="Editar usuario">
                                                            <span class="icon"><span class="material-symbols-outlined">edit</span></span>
                                                        </a>

                                                        <!-- Soft Delete / Toggle Estado -->
                                                        <?php if ($esSuperAdmin): ?>
                                                            <button type="button" class="button is-small is-static" disabled title="No es posible suspender la cuenta maestra de control">
                                                                <span class="icon"><span class="material-symbols-outlined">person_off</span></span>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    class="button is-small <?= $esActivo ? 'is-danger' : 'is-success' ?> is-light btn-toggle-estado" 
                                                                    data-nombre="<?= htmlspecialchars($username) ?>" 
                                                                    data-activo="<?= $esActivo ? '1' : '0' ?>"
                                                                    data-url="<?= urlAccion('usuario', 'eliminar') ?>&id=<?= $idUser ?>"
                                                                    title="<?= $esActivo ? 'Desactivar acceso' : 'Reactivar acceso' ?>">
                                                                <span class="icon">
                                                                    <span class="material-symbols-outlined"><?= $esActivo ? 'person_off' : 'check_circle' ?></span>
                                                                </span>
                                                            </button>
                                                        <?php endif; ?>

                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="has-text-centered py-5 has-text-grey">
                                                No hay usuarios registrados en el sistema.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- COLUMNA DERECHA: ACCIONES RÁPIDAS Y NORMATIVA (4 Cols) -->
                <div class="column is-4">
                    
                    <!-- Tarjeta 1: Acciones Rápidas -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-4" style="gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-info-light has-text-link" style="width: 32px; height: 32px; border-radius: 8px;">
                                <span class="material-symbols-outlined">push_pin</span>
                            </div>
                            <h3 class="title is-6 mb-0 has-text-dark">Acciones Rápidas</h3>
                        </div>

                        <a href="<?= urlAccion('usuario', 'formusuariocrear') ?>" class="button is-link is-fullwidth has-text-weight-bold" style="border-radius: 10px; padding: 1.25rem;">
                            <span class="icon"><span class="material-symbols-outlined">person_add</span></span>
                            <span>Añadir Usuario</span>
                        </a>

                        <div class="notification is-light mt-4 p-3 is-size-7" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569;">
                            <div class="is-flex is-align-items-center mb-1 font-semibold" style="gap: 6px; color: #1e293b;">
                                <span class="material-symbols-outlined has-text-warning is-size-6">shield</span>
                                <span>Control de Privilegios</span>
                            </div>
                            <p class="mb-0">
                                Cuentas con rol <strong>Operador</strong> requieren autorización para movimientos especiales de stock; el rol <strong>Administrador</strong> tiene acceso irrestricto.
                            </p>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Normativa de Privilegios -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-3" style="gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-link-light has-text-link" style="width: 32px; height: 32px; border-radius: 8px;">
                                <span class="material-symbols-outlined">security</span>
                            </div>
                            <h3 class="title is-6 mb-0 has-text-dark">Normativa de Privilegios</h3>
                        </div>

                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column" style="gap: 12px; line-height: 1.4;">
                            <li class="is-flex" style="gap: 10px;">
                                <span class="tag is-link is-light is-rounded has-text-weight-bold" style="height: 1.5em;">1</span>
                                <span><strong class="has-text-dark">No compartir credenciales:</strong> Cada usuario responde individualmente por movimientos de inventario y arqueo.</span>
                            </li>
                            <li class="is-flex" style="gap: 10px;">
                                <span class="tag is-link is-light is-rounded has-text-weight-bold" style="height: 1.5em;">2</span>
                                <span><strong class="has-text-dark">Cuenta Maestra Protegida:</strong> El usuario superadministrador no puede ser bloqueado ni auto-eliminado.</span>
                            </li>
                            <li class="is-flex" style="gap: 10px;">
                                <span class="tag is-link is-light is-rounded has-text-weight-bold" style="height: 1.5em;">3</span>
                                <span><strong class="has-text-dark">Rotación preventiva:</strong> Se sugiere cambiar contraseñas periódicamente para mantener la certificación operativa.</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/usuario.js"></script>
</body>
</html>