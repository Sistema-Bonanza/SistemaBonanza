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
    <title> Editar Producto · POS & Inventario</title>
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
        
        /* Ajuste específico para el Checkbox del IVA */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0; /* Anula el margin-bottom del form-label normal */
            cursor: pointer;
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
            <h1>📦 Editar Producto</h1>
            <p>Modifica la información de identidad del artículo maestro seleccionado.</p>
        </div>

        <!-- PANEL DEL FORMULARIO -->
        <div class="data-panel">
            <h3>Datos del Artículo</h3>
            <p>Actualiza el código, nombre o clasificación del producto.</p>

            <form action="index.php?controller=producto&action=actualizarProducto" method="POST">

                <!-- ID OCULTO: Vital para saber qué producto vamos a actualizar -->
                <input type="hidden" name="id_producto" value="<?= htmlspecialchars($producto['id_producto'] ?? '') ?>">

                <div class="form-group">
                    <label class="form-label" for="codigo">Código de Producto <span class="required">*</span></label>
                    <input type="text" id="codigo" name="codigo" class="form-control" 
                           value="<?= htmlspecialchars($producto['codigo'] ?? '') ?>" 
                           placeholder="Ej. PROD-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del Producto <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" 
                           placeholder="Ej. Harina Pan 1Kg" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_categoria">Categoría <span class="required">*</span></label>
                    <select id="id_categoria" name="id_categoria" class="form-control" required>
                        <option value="">-- Seleccione una Categoría --</option>
                        
                        <!-- El Controlador debe pasar la variable $categorias a esta vista -->
                        <?php if(!empty($categorias)): ?>
                            <?php foreach($categorias as $cat): ?>
                                <!-- Lógica: Si el ID de la categoría actual coincide con la del producto, le pone 'selected' -->
                                <option value="<?= $cat['id_categoria'] ?>" 
                                    <?= (isset($producto['id_categoria']) && $producto['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                    </select>
                </div>

                <div class="form-group checkbox-group">
                    <!-- Lógica: Si aplica_iva es 1 en la base de datos, imprime el atributo 'checked' -->
                    <input type="checkbox" id="aplica_iva" name="aplica_iva" value="1" 
                           <?= (!empty($producto['aplica_iva']) && $producto['aplica_iva'] == 1) ? 'checked' : '' ?>>
                    <label class="form-label" for="aplica_iva">¿Aplica IVA?</label>
                </div>

                <button type="submit" class="btn-primary">Actualizar Producto</button>
             
            </form>
        </div>
    </main>

</body>
</html>