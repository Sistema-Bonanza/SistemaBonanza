<?php
if(session_status() === PHP_SESSION_NONE){ session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes · POS & Inventario</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/cliente.css" />
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    
    <main class="main">
        <div class="welcome-card">
            <h1>👥 Gestión de Clientes</h1>
            <p>Administra los clientes del sistema. Solo los administradores pueden realizar cambios.</p>
        </div>

        <div class="providers-section">
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <a href="index.php?controller=cliente&action=formcrearcliente" class="btn-action btn-add-provider">
                        <span class="icon-add"></span> Añadir Cliente
                    </a>
                </div>
            </div>

            <div class="providers-list-panel">
                <h3>
                    📋 Clientes del Sistema
                    <span class="provider-counter">
                        <?php echo isset($clientes) ? count($clientes) : 0; ?>
                    </span>
                </h3>

                <div class="table-container">
                    <table class="providers-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="provider-name-display">
                                                <div class="provider-avatar">
                                                    <?= strtoupper(substr(htmlspecialchars($c['nombre_razon_social'] ?? 'C'), 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($c['nombre_razon_social'] ?? 'Sin Nombre') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-operator">
                                                <?= htmlspecialchars(($c['tipo_documento'] ?? 'V') . '-' . ($c['num_documento'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($c['telefono'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($c['email'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($c['direccion'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="index.php?controller=cliente&action=formeditarcliente&id=<?= $c['id_cliente'] ?? '' ?>" 
                                                   class="btn-table btn-edit" 
                                                   title="Editar cliente">
                                                    <span class="icon-edit"></span>
                                                </a>
                                                <a href="index.php?controller=cliente&action=eliminar&id=<?= $c['id_cliente'] ?? '' ?>" 
                                                   class="btn-table btn-delete-table" 
                                                   title="Eliminar cliente"
                                                   onclick="return confirm('¿Estás seguro de eliminar este cliente? Se marcará como inactivo.');">
                                                    <span class="icon-delete-table"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i>🏢</i>
                                            <h4>No hay clientes registrados</h4>
                                            <p style="font-size: 14px;">Haz clic en "Añadir Cliente" para registrar el primero.</p>
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