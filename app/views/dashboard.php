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
    <title>Dashboard - Sistema Bonanza</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-card p {
            color: var(--text-light);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .stat-card h3 {
            color: var(--text-dark);
            font-size: 24px;
        }

        .data-panel {
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .data-panel h3 {
            color: var(--text-dark);
            font-size: 16px;
            margin-bottom: 15px;
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
                <li class="nav-item active"><a href="index.php?controller=sistema&action=dashboard">📊 Inicio</a></li>
                <li class="nav-item"><a href="#">📦 Productos</a></li>
                <li class="nav-item"><a href="#">🔄 Movimientos</a></li>
                <li class="nav-item"><a href="index.php?controller=usuario&action=formusuario">👥 Usuarios</a></li>
                <li class="nav-item"><a href="#">⚙️ Configuración</a></li>
            </ul>
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <div class="main-wrapper">
        
        <!-- BARRA SUPERIOR -->
        <header class="topbar">
            <div>
                <strong style="color: var(--text-dark);">Panel de Control</strong>
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
                <h1>¡Hola de nuevo, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                <p>Bienvenido al Sistema de Gestión de Inventario. Aquí tienes el resumen del día.</p>
            </div>

            <!-- Tarjetas Informativas (Métricas básicas) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <p>Total Productos</p>
                    <h3>124</h3>
                </div>
                <div class="stat-card">
                    <p>Stock Bajo</p>
                    <h3 style="color: #eab308;">8</h3>
                </div>
                <div class="stat-card">
                    <p>Entradas de Hoy</p>
                    <h3 style="color: #22c55e;">+15</h3>
                </div>
                <div class="stat-card">
                    <p>ID de Usuario</p>
                    <h3>#<?php echo $_SESSION['user_id']; ?></h3>
                </div>
            </div>

            <!-- Panel vacio listo para cargar tablas o reportes -->
            <div class="data-panel">
                <h3>Actividad Reciente del Sistema</h3>
                <p style="color: var(--text-light); font-size: 14px;">El módulo de auditoría y registros se cargará aquí próximamente.</p>
            </div>

        </main>
    </div>

</body>
</html>