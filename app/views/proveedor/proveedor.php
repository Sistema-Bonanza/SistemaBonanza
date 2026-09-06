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
    <!-- CSS externo del dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    
    <style>
        /* --- ESTILOS ADICIONALES PARA GESTIÓN DE PROVEEDORES --- */
        /* Estos estilos complementan dashboard.css manteniendo coherencia visual */

        /* SECCIÓN DE PROVEEDORES - GRID */
        .providers-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            margin-top: 20px;
        }

        /* PANEL DE ACCIONES */
        .actions-panel {
            background-color: var(--white);
            padding: 25px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            align-self: start;
        }

        .actions-panel h3 {
            color: var(--text-dark);
            font-size: 16px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--white);
        }

        .btn-add-user {
            background-color: var(--primary);
        }

        .btn-add-user:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* PANEL DE LISTA DE USUARIOS */
        .users-list-panel {
            background-color: var(--white);
            padding: 25px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .users-list-panel h3 {
            color: var(--text-dark);
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-counter {
            background: var(--bg-body);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 13px;
            color: var(--text-dark);
            font-weight: 600;
        }

        /* TABLA DE USUARIOS - ESTILO COHERENTE CON DASHBOARD */
        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        .users-table thead {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        }

        .users-table th {
            text-align: left;
            padding: 12px 16px;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        .users-table td {
            padding: 12px 16px;
            color: var(--text-dark);
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .users-table tbody tr {
            transition: all 0.2s ease;
        }

        .users-table tbody tr:hover {
            background-color: #f8fafc;
            transform: scale(1.002);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .users-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* AVATAR DE USUARIO */
        .user-name-display {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        /* BADGES DE ROL */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-operator {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .badge-user {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* BOTONES DE ACCIÓN EN TABLA */
        .actions-cell {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .btn-table {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: var(--white);
            font-size: 13px;
        }

        .btn-table:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-edit {
            background-color: var(--primary);
        }

        .btn-edit:hover {
            background-color: #1d4ed8;
        }

        .btn-delete-table {
            background-color: var(--danger);
        }

        .btn-delete-table:hover {
            background-color: #dc2626;
        }

        /* ESTADO VACÍO */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }

        .empty-state h4 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        /* WELCOME CARD - MISMO ESTILO QUE EL DASHBOARD */
        .welcome-card {
            background: linear-gradient(135deg, #2563eb, #2656bd);
            color: var(--white);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .welcome-card h1 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .welcome-card p {
            opacity: 0.9;
            font-size: 14px;
        }

        /* ICONOS SIMPLES */
        .icon-add::before {
            content: "➕ ";
        }

        .icon-edit::before {
            content: "✏️";
        }

        .icon-delete-table::before {
            content: "🗑️";
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .users-section {
                grid-template-columns: 1fr;
            }

            .actions-cell {
                flex-direction: column;
                align-items: center;
                gap: 4px;
            }

            .users-table th,
            .users-table td {
                padding: 8px 10px;
                font-size: 13px;
            }

            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }
        }
    </style>
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
                    <!-- Mantenemos la clase users-table para conservar tu CSS -->
                    <table class="users-table">
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
                                <!-- Cambié $a por $p para que lógicamente signifique Proveedor -->
                                <?php foreach ($proveedores as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="user-name-display">
                                                <div class="user-avatar">
                                                    <!-- Extraemos la primera letra de la Razón Social -->
                                                    <?= strtoupper(substr(htmlspecialchars($p['razon_social'] ?? 'P'), 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($p['razon_social'] ?? 'Sin Nombre') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Reutilizamos tu estilo de badge para resaltar el RIF -->
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
                                                <!-- Actualizados los controladores y el parámetro ID (asumiendo id_proveedor) -->
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
                                    <!-- colspan=5 porque ahora tenemos 5 columnas -->
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