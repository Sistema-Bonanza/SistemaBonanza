<?php
 //1. CANDADO DE SEGURIDAD (¡Intocable!)
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Si no existe la variable de sesión, lo redirigimos al login
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
    <title>Nuevo Usuario - Sistema Bonanza</title>
    <style>
        /* --- TUS ESTILOS GENERALES (Intactos) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --bg-body: #f1f5f9; --sidebar-bg: #0f172a; --sidebar-hover: #1e293b; --primary: #2563eb; --text-dark: #334155; --text-light: #94a3b8; --white: #ffffff; --danger: #ef4444; --border: #e2e8f0; }
        body { background-color: var(--bg-body); display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background-color: var(--sidebar-bg); color: var(--white); display: flex; flex-direction: column; justify-content: space-between; padding: 20px 0; flex-shrink: 0; }
        .brand { padding: 0 20px 20px 20px; border-bottom: 1px solid var(--sidebar-hover); }
        .brand h2 { font-size: 18px; font-weight: 700; color: var(--white); }
        .brand span { color: var(--primary); }
        .nav-menu { list-style: none; margin-top: 20px; }
        .nav-item a { display: flex; align-items: center; padding: 12px 20px; color: var(--text-light); text-decoration: none; font-size: 14px; transition: all 0.2s ease; }
        .nav-item a:hover, .nav-item.active a { background-color: var(--sidebar-hover); color: var(--white); border-left: 4px solid var(--primary); }
        
        .main-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .topbar { background-color: var(--white); height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; border-bottom: 1px solid var(--border); }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-details { text-align: right; }
        .user-name { font-weight: 600; color: var(--text-dark); font-size: 14px; }
        .user-role { font-size: 12px; color: var(--text-light); }
        .btn-logout { background-color: #fee2e2; color: var(--danger); border: none; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.2s; }
        .btn-logout:hover { background-color: #fca5a5; }
        
        .content { padding: 30px; overflow-y: auto; }
        .data-panel { background-color: var(--white); padding: 25px; border-radius: 10px; border: 1px solid var(--border); max-width: 600px; /* Limitamos el ancho para que el formulario no se estire feo */ }
        .data-panel h3 { color: var(--text-dark); font-size: 20px; margin-bottom: 8px; }
        .data-panel p { color: var(--text-light); font-size: 14px; margin-bottom: 25px; }

        /* --- NUEVOS ESTILOS SOLO PARA EL FORMULARIO --- */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 14px; }
        .form-control { width: 100%; padding: 10px 15px; border: 1px solid var(--border); border-radius: 6px; outline: none; font-size: 14px; color: var(--text-dark); transition: border 0.2s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .btn-primary { background-color: var(--primary); color: var(--white); border: none; padding: 12px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%; font-size: 15px; }
        .btn-primary:hover { background-color: #1d4ed8; }
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
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></div>
                    <div class="user-role">
                        <?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'Administrador' : 'Operador'; ?>
                    </div>
                </div>
                <a href="index.php?controller=sistema&action=logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <!-- CONTENIDO DEL DASHBOARD -->
        <main class="content">
            
            <!-- PANEL DEL FORMULARIO -->
            <div class="data-panel">
                <h3>Editar Datos de Usuario</h3>
                <p>Complete los datos para actualizar la información del usuario.</p>

                <!-- El atributo action apuntará a nuestro futuro Controlador -->
                <form action="index.php?controller=usuario&action=editar" method="POST">
                    
                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id'] ?? '') ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Nombre de Usuario</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($usuario['username'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" value="<?= htmlspecialchars($usuario['password'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rol">Rol en el Sistema</label>
                        <select id="rol" name="rol" class="form-control" required>
                            <option value="">Seleccione un rol...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Operador">Operador</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Actualizar Datos de  Usuario</button>
                </form>
            </div>

        </main>
    </div>

</body>
</html>