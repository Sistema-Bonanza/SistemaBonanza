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
    <title>Gestión de Proveedores · POS & Inventario</title>
    <!-- CSS externo -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/proveedor.css" />
</head>
<body>
 <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    
   <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>👥 Gestión de Proveedores</h1>
            <p>Administra los proveedores del sistema. Solo los administradores pueden realizar cambios.</p>
        </div>

        <!-- SECCIÓN DE PROVEEDORES: BOTONES + LISTA -->
        <div class="providers-section">

            <!-- PANEL IZQUIERDO: BOTONES DE ACCIÓN -->
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <a href="index.php?controller=proveedor&action=formcrearproveedor" class="btn-action btn-add-provider">
                        <span class="icon-add"></span> Añadir Proveedor
                    </a>
                </div>
            </div>

            <!-- PANEL DERECHO: LISTA DE PROVEEDORES -->
            <div class="providers-list-panel">
                <h3>
                    📋 Proveedores del Sistema
                    <span class="provider-counter">
                        <?php echo isset($proveedores) ? count($proveedores) : 0; ?>
                    </span>
                </h3>

                <div class="table-container">
                    <table class="providers-table">
                        <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>RIF</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($proveedores)): ?>
                                <?php foreach ($proveedores as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="provider-name-display">
                                                <div class="provider-avatar">
                                                    <!-- Extraemos la primera letra de la Razón Social -->
                                                    <?= strtoupper(substr(htmlspecialchars($p['razon_social'] ?? 'P'), 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($p['razon_social'] ?? 'Sin Nombre') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-operator">
                                                <?= htmlspecialchars($p['rif'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($p['nombre_contacto'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($p['telefono'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="index.php?controller=proveedor&action=formEdit&id=<?= $p['id_proveedor'] ?? $p['id'] ?? '' ?>" 
                                                   class="btn-table btn-edit" 
                                                   title="Editar proveedor">
                                                    <span class="icon-edit"></span>
                                                </a>
                                                <a href="index.php?controller=proveedor&action=eliminar&id=<?= $p['id_proveedor'] ?? $p['id'] ?? '' ?>" 
                                                   class="btn-table btn-delete-table" 
                                                   title="Eliminar proveedor"
                                                   onclick="return confirm('¿Estás seguro de eliminar a este proveedor del sistema?');">
                                                    <span class="icon-delete-table"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i>🏢</i>
                                            <h4>No hay proveedores registrados</h4>
                                            <p style="font-size: 14px;">Haz clic en "Añadir Proveedor" para registrar el primero.</p>
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