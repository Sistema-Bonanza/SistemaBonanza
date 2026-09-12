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
    <title>Gestión de Categorías </title>
    <!-- CSS externo del dashboard -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/categoria.css" />
    
</head>
<body>
 <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    

    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>🏷️ Gestión de Categorías</h1>
            <p>Administra las categorías de productos en el sistema.</p>
        </div>

        <!-- SECCIÓN DE CATEGORÍAS: BOTONES + LISTA -->
        <div class="users-section">

            <!-- PANEL IZQUIERDO: BOTONES DE ACCIÓN -->
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <a href="index.php?controller=categoria&action=fromcrearCategoria" class="btn-action btn-add-user">
                        <span class="icon-add"></span> Añadir Categoría
                    </a>
                </div>
            </div>

            <!-- PANEL DERECHO: LISTA DE CATEGORÍAS -->
            <div class="users-list-panel">
                <h3>
                     🏷️Categorías del Sistema
                    <span class="user-counter">
                        <?php echo isset($categorias) ? count($categorias) : 0; ?>
                    </span>
                </h3>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categorias)): ?>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td>
                                            <div class="user-name-display">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr(htmlspecialchars($categoria['nombre'] ?? 'C'), 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($categoria['nombre'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($categoria['descripcion'] ?? '') ?>
                                        </td>
                                        <td>
                                            <a href="index.php?controller=categoria&action=fromeditarCategoria&id=<?= $categoria['id_categoria'] ?? '' ?>" 
                                                   class="btn-table btn-edit" 
                                                   title="Editar categoría">
                                                    <span class="icon-edit"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i>🏷️</i>
                                            <h4>No hay categorías registradas</h4>
                                            <p style="font-size: 14px;">Haz clic en "Añadir Categoría" para crear la primera.</p>
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