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
    <title>Editar Usuario · POS & Inventario</title>
    <!-- CSS externo del dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    
    <style>
        /* --- ESTILOS ADICIONALES PARA EL FORMULARIO DE EDICIÓN --- */
        /* Estos estilos complementan dashboard.css manteniendo coherencia visual */

        /* PANEL DEL FORMULARIO */
        .data-panel {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin-top: 20px;
        }

        .data-panel h3 {
            color: var(--text-dark);
            font-size: 20px;
            margin-bottom: 8px;
        }

        .data-panel p {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 25px;
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

        /* FORMULARIO */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            color: var(--text-dark);
            transition: border 0.2s, box-shadow 0.2s;
            background-color: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 15px;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .data-panel {
                padding: 20px;
                max-width: 100%;
            }
            
            .form-control {
                font-size: 13px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>

     <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>✏️ Editar Usuario</h1>
            <p>Actualiza la información del usuario en el sistema.</p>
        </div>

        <!-- PANEL DEL FORMULARIO -->
        <div class="data-panel">
            <h3>Editar Datos de Usuario</h3>
            <p>Complete los datos para actualizar la información del usuario.</p>

            <!-- El atributo action apuntará a nuestro futuro Controlador -->
            <form action="index.php?controller=usuario&action=editar" method="POST">
                
                <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id'] ?? '') ?>">
                
                <div class="form-group">
                    <label class="form-label" for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($usuario['username'] ?? '') ?>" 
                           placeholder="Ingrese el nombre de usuario" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           value="<?= htmlspecialchars($usuario['password'] ?? '') ?>" 
                           placeholder="Ingrese la nueva contraseña">
                    <small style="color: var(--text-light); font-size: 12px;">Dejar en blanco para mantener la contraseña actual</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rol">Rol en el Sistema</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccione un rol...</option>
                        <option value="Administrador" <?= (isset($usuario['rol']) && $usuario['rol'] == 'Administrador') ? 'selected' : '' ?>>Administrador</option>
                        <option value="Operador" <?= (isset($usuario['rol']) && $usuario['rol'] == 'Operador') ? 'selected' : '' ?>>Operador</option>
                        <option value="Usuario" <?= (isset($usuario['rol']) && $usuario['rol'] == 'Usuario') ? 'selected' : '' ?>>Usuario</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Actualizar Datos de Usuario</button>
            </form>
        </div>

    </main>

</body>
</html>