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
    <title>Nuevo Cliente · POS & Inventario</title>
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

        /* Botón Cancelar */
        .btn-primary.btn-cancelar {
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

        .btn-primary.btn-cancelar:hover {
            background-color: #d81f1f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(216, 31, 31, 0.3);
        }

        .btn-primary.btn-cancelar:active {
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
        <h1>🏢 Registrar Nuevo Cliente</h1>
        <p>Complete los datos fiscales y de contacto para dar de alta a un nuevo cliente en el sistema.</p>
    </div>

    <!-- SECCIÓN PRINCIPAL: FORMULARIO A LA IZQUIERDA + PANEL A LA DERECHA -->
    <div class="providers-section" style="display: flex; gap: 20px;"    >

        <!-- PANEL IZQUIERDO: FORMULARIO DE REGISTRO -->
        <div class="data-panel">
            <h3>Datos del Cliente</h3>
            <p>Los campos marcados con (<span class="required">*</span>) son obligatorios.</p>

            <form action="index.php?controller=cliente&action=guardar" method="POST">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           required placeholder="Ej: Luis Perez" autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cedula">
                        Cédula / RIF <span class="required">*</span>
                    </label>
                    <input type="text" id="cedula" name="cedula" class="form-control" 
                           required placeholder="Ej: 12345678" pattern="\d{7,8}" title="Ingrese un número de cédula válido (7 u 8 dígitos)">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono de Contacto<span class="required">*</span></label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           required placeholder="Ej: 0414-1234567">
                </div>

                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección Fiscal<span class="required">*</span></label>
                    <input type="text" id="direccion" name="direccion" class="form-control" 
                           required placeholder="Ej: Av. Principal, Zona Industrial...">
                </div>

               <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-primary">Guardar Cliente</button>
                    <a href="index.php?controller=cliente&action=TablaClientes" 
                    class="btn-primary btn-cancelar"><center>Cancelar</center></a>
                </div>

            </form>
        </div>

       <!-- PANEL DERECHO: NOTA DESTACADA Y NAVEGACIÓN -->
        <div class="actions-panel">
            
            <!-- CAJA DE NOTA INFORMATIVA -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 6px; margin-bottom: 20px; color: #856404;">
                <h4 style="margin: 0 0 8px 0; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                    ⚠️ <span>Nota Importante</span>
                </h4>
                <p style="margin: 0; font-size: 13px; line-height: 1.4;">
                    Los campos marcados con (<span class="required" style="color: #d9534f; font-weight: bold;">*</span>) son estrictamente obligatorios para poder dar de alta al proveedor.
                </p>
            </div>

        </div>

    </div>

</main>