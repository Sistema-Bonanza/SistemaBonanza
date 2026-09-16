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
    <title>Editar Cliente · POS & Inventario</title>
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
        background-color: #d81f1f;
        text-decoration: none;
        text-align: center;
        display: inline-block;
        width: auto;
        padding: 12px 20px;
        border-radius: 6px;
        color: var(--white);
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
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
        <h1>✏️ Editar Cliente</h1>
        <p>Actualice los datos de contacto del cliente. Los datos como Cédula de Identidad y Nombre permanecen protegidos por integridad del sistema.</p>
    </div>

    <!-- SECCIÓN PRINCIPAL: FORMULARIO + PANEL DE INFORMACIÓN -->
    <div class="providers-section" style="display: flex; gap: 20px; align-items: flex-start;">

        <!-- PANEL IZQUIERDO: FORMULARIO DE EDICIÓN -->
        <div class="data-panel" style="flex: 2;">
            <h3>Modificar Información de Cliente</h3>

            <form action="index.php?controller=cliente&action=actualizar" method="POST">
                
                <!-- ID / RIF OCULTO PARA EL CONTROLADOR -->
                <input type="hidden" name="id_clientes" value="<?= $clientes['id_clientes'] ?? '' ?>">

                <!-- CAMPO BLOQUEADO: RIF -->
                <div class="form-group">
                    <label class="form-label" for="rif">
                        Nombre 🔒
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= $clientes['nombre'] ?? '' ?>" 
                           readonly 
                           style="background-color: #e9ecef; cursor: not-allowed; color: #495057;">
                </div>

                <!-- CAMPO BLOQUEADO: RAZÓN SOCIAL -->
                <div class="form-group">
                    <label class="form-label" for="razon_social">
                        Cédula / RIF 🔒
                    </label>
                    <input type="text" id="cedula" name="cedula" class="form-control" 
                           value="<?= $clientes['cedula'] ?? '' ?>" 
                           readonly 
                           style="background-color: #e9ecef; cursor: not-allowed; color: #495057;">
                </div>

                <!-- CAMPO EDITABLE: TELÉFONO -->
                <div class="form-group">
                    <label class="form-label" for="telefono">
                        Teléfono de Contacto <span class="required">*</span>
                    </label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= htmlspecialchars($clientes['telefono'] ?? '') ?>" 
                           required placeholder="Ej: 0414-1234567">
                </div>

                    <!-- CAMPO EDITABLE: DIRECCIÓN -->
                <div class="form-group">
                    <label class="form-label" for="direccion">
                        Dirección <span class="required">*</span>
                    </label>
                    <input type="text" id="direccion" name="direccion" class="form-control" 
                           value="<?= htmlspecialchars($clientes['direccion'] ?? '') ?>" 
                           required placeholder="Ej: Calle Principal, Ciudad, País">
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-primary">Actualizar Cambios</button>
                    <a href="index.php?controller=cliente&action=tablaClientes" 
                       class="btn-primary btn-cancelar">
                      Cancelar

                    </a>
                </div>

            </form>
        </div>

        <!-- PANEL DERECHO: NOTA EXPLICATIVA -->
        <div class="actions-panel" style="flex: 1;">
            <div style="background-color: #e2e3e5; border-left: 4px solid #6c757d; padding: 15px; border-radius: 6px; color: #383d41;">
                <h4 style="margin: 0 0 8px 0; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                    ℹ️ <span>Campos Protegidos</span>
                </h4>
                <p style="margin: 0; font-size: 13px; line-height: 1.4;">
                    Por políticas de seguridad y consistencia en los registros de compras, la <strong>Razón Social</strong>, el <strong>RIF</strong> y la <strong>Dirección Fiscal</strong> no se pueden modificar. Solo se permite actualizar la información del personal de contacto.
                </p>
            </div>
        </div>

    </div>

</main>



