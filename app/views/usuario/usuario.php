<?php
//1. CANDADO DE SEGURIDAD
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Si no existe la variable de sesión, lo redirigimos al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Desactivar caché del navegador para vistas protegidas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios · POS & Inventario</title>
    
    <!-- CSS externo del dashboard -->
     <link rel="stylesheet" href="assets/css/sidebar.css" /> 
    <!-- Nuevo CSS exclusivo de Usuarios -->
    <link rel="stylesheet" href="assets/css/usuarios.css" />
</head>
<body>
 <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    
    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>👥 Gestión de Usuarios</h1>
            <p>Administra los usuarios del sistema. Solo los administradores pueden realizar cambios.</p>
        </div>

        <!-- SECCIÓN DE USUARIOS: BOTONES + LISTA -->
        <div class="users-section">

            <!-- PANEL IZQUIERDO: BOTONES DE ACCIÓN -->
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <a href="index.php?controller=usuario&action=formusuariocrear" class="btn-action btn-add-user">
                        <span class="icon-add"></span> Añadir Usuario
                    </a>
                </div>
            </div>

            <!-- PANEL DERECHO: LISTA DE USUARIOS -->
            <div class="users-list-panel">
                <h3>
                    📋 Usuarios del Sistema
                    <span class="user-counter">
                        <?php echo isset($usuarios) ? count($usuarios) : 0; ?>
                    </span>
                </h3>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $a): ?>
                                    <tr>
                                        <td>
                                            <div class="user-name-display">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr(htmlspecialchars($a['username'] ?? 'U'), 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($a['username'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                $rol = $a['rol'] ?? '';
                                                $badgeClass = 'badge-user';
                                                
                                                if (strtolower($rol) === 'admin' || strtolower($rol) === 'administrador') {
                                                    $badgeClass = 'badge-admin';
                                                } elseif (strtolower($rol) === 'operator' || strtolower($rol) === 'operador') {
                                                    $badgeClass = 'badge-operator';
                                                }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= htmlspecialchars($rol) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="index.php?controller=usuario&action=formusuarioeditar&id=<?= $a['id'] ?>" 
                                                   class="btn-table btn-edit" 
                                                   title="Editar usuario">
                                                    <span class="icon-edit"></span>
                                                </a>
                                                <a href="index.php?controller=usuario&action=eliminar&id=<?= $a['id'] ?>" 
                                                   class="btn-table btn-delete-table" 
                                                   title="Eliminar usuario"
                                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                    <span class="icon-delete-table"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i>👥</i>
                                            <h4>No hay usuarios registrados</h4>
                                            <p style="font-size: 14px;">Haz clic en "Añadir Usuario" para crear el primero.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

</body>
</html>