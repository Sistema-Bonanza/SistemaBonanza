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
    <title>Nuevo Usuario · POS & Inventario</title>
    <!-- CSS externo del dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    
    <style>
        /* --- ESTILOS ADICIONALES PARA EL FORMULARIO DE CREACIÓN --- */
        /* Estos estilos complementan dashboard.css manteniendo coherencia visual */

        /* PANEL DEL FORMULARIO */
        .data-panel {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
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

        .form-control.error {
            border-color: var(--danger);
        }

        .form-control.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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

        .btn-primary:active {
            transform: translateY(0);
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
            
            .welcome-card h1 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

    <!-- ========== SIDEBAR ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>➕ Nuevo Usuario</h1>
            <p>Registra un nuevo usuario en el sistema para control de acceso.</p>
        </div>

        <!-- PANEL DEL FORMULARIO -->
        <div class="data-panel">
            <h3>Registrar Nuevo Usuario</h3>
            <p>Complete los datos para dar acceso a un nuevo trabajador.</p>

            <!-- El atributo action apuntará a nuestro futuro Controlador -->
            <form action="index.php?controller=usuario&action=guardar" method="POST">
                
                <div class="form-group">
                    <label class="form-label" for="username">
                        Nombre de Usuario
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           required placeholder="Ej: m_pacheco" 
                           autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Contraseña
                        <span class="required">*</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-control" 
                           required placeholder="Ingrese una contraseña segura">
                    <small style="color: var(--text-light); font-size: 12px; display: block; margin-top: 5px;">
                        La contraseña debe tener al menos 6 caracteres.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rol">
                        Rol en el Sistema
                        <span class="required">*</span>
                    </label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccione un rol...</option>
                        <option value="Administrador">👑 Administrador</option>
                        <option value="Operador">🛠️ Operador</option>
                        <option value="Usuario">👤 Usuario</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Guardar Usuario</button>
            </form>
        </div>

    </main>

</body>
</html>