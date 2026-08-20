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
    <title>Gestión de Usuarios - Sistema Bonanza</title>
    <style>
        /* --- ESTILOS GENERALES Y RESETS --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --bg-body: #f1f5f9;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --primary: #2563eb;
            --text-dark: #334155;
            --text-light: #94a3b8;
            --white: #ffffff;
            --danger: #ef4444;
            --success: #22c55e;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            display: flex;
            min-height: 100vh;
        }

        /* --- BARRA LATERAL (SIDEBAR) --- */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .brand {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid var(--sidebar-hover);
        }

        .brand h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--white);
        }

        .brand span {
            color: var(--primary);
        }

        .nav-menu {
            list-style: none;
            margin-top: 20px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .nav-item a:hover, .nav-item.active a {
            background-color: var(--sidebar-hover);
            color: var(--white);
            border-left: 4px solid var(--primary);
        }

        /* --- CONTENIDO PRINCIPAL --- */
        .main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        /* BARRA SUPERIOR (TOPBAR) */
        .topbar {
            background-color: var(--white);
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-light);
        }

        .btn-logout {
            background-color: #fee2e2;
            color: var(--danger);
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background-color: #fca5a5;
        }

        /* ÁREA DE CONTENIDO Y TARJETAS */
        .content {
            padding: 30px;
            overflow-y: auto;
        }

        .welcome-card {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
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

        /* --- NUEVA SECCIÓN: PANEL DE USUARIOS --- */
        .users-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
        }

        /* PANEL DE ACCIONES (BOTONES) */
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

        .btn-add {
            background-color: var(--primary);
        }

        .btn-add:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-delete {
            background-color: var(--danger);
        }

        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-action i {
            font-size: 18px;
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

        /* --- ESTILOS MEJORADOS DE LA TABLA --- */
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

        .user-id-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 13px;
        }

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

        .btn-view {
            background-color: var(--success);
        }

        .btn-view:hover {
            background-color: #16a34a;
        }

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

        /* --- RESPONSIVE --- */
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

        /* Iconos simples (sin Font Awesome) */
        .icon-add::before {
            content: "➕ ";
        }

        .icon-delete::before {
            content: "🗑️ ";
        }
        
        /* Iconos para tabla */
        .icon-edit::before {
            content: "✏️";
        }
        
        .icon-delete-table::before {
            content: "🗑️";
        }
        
        .icon-view::before {
            content: "👁️";
        }

    </style>
</head>
<body>

    <!-- BARRA LATERAL DE NAVEGACIÓN -->
    <aside class="sidebar">
        <div>
            <div class="brand">
                <h2>Inventario <span>Bonanza</span></h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php?controller=sistema&action=dashboard">📊 Inicio</a></li>
                <li class="nav-item"><a href="#">📦 Productos</a></li>
                <li class="nav-item"><a href="#">🔄 Movimientos</a></li>
                <li class="nav-item active"><a href="index.php?controller=usuario&action=formusuario">👥 Usuarios</a></li>
                <li class="nav-item"><a href="#">⚙️ Configuración</a></li>
            </ul>
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <div class="main-wrapper">
        
        <!-- BARRA SUPERIOR -->
        <header class="topbar">
            <div>
                <strong style="color: var(--text-dark);">Gestión de Usuarios</strong>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="user-role">
                        <?php echo ($_SESSION['is_admin'] == 1) ? 'Administrador' : 'Operador'; ?>
                    </div>
                </div>
                <!-- Botón para cerrar sesión -->
                <a href="index.php?controller=sistema&action=logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <!-- CONTENIDO DEL DASHBOARD -->
        <main class="content">
            
            <!-- Mensaje de Bienvenida -->
            <div class="welcome-card">
                <h1>👥 Gestión de Usuarios</h1>
                <p>Administra los usuarios del sistema. Solo los administradores pueden realizar cambios.</p>
            </div>

            <!-- SECCIÓN DE USUARIOS: BOTONES + LISTA -->
            <div class="users-section">
                
                <!-- PANEL IZQUIERDO: BOTONES DE ACCIÓN -->
                <div class="actions-panel">
                    <div class="btn-group">
                        <!-- Botón Añadir Usuario -->
                        <a href="index.php?controller=usuario&action=formcrear" class="btn-action btn-add">
                            <span class="icon-add"></span> Añadir Usuario
                        </a>
                    </div>
                </div>

                <!-- PANEL DERECHO: LISTA DE USUARIOS MEJORADA -->
                <div class="users-list-panel">
                    <h3>
                        📋 Usuarios del Sistema
                        <span style="background: var(--bg-body); padding: 2px 12px; border-radius: 12px; font-size: 13px; color: var(--text-dark);">
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
                                                    <a href="index.php?controller=usuario&action=formeditar&id=<?= $a['id'] ?>" 
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
                                        <td colspan="4">
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
    </div>

</body>
</html>