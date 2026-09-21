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
    <title>Editar Cliente · POS & Inventario</title>
    <link rel="stylesheet" href="assets/css/dashboard.css" />
    <style>
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
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }
        .form-label .required { color: var(--danger); margin-left: 2px; }
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
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
        }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-cancelar {
            background-color: #d81f1f;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
        }
        .btn-cancelar:hover { background-color: #b91c1c; }
        .welcome-card {
            background: linear-gradient(135deg, #2563eb, #2656bd);
            color: var(--white);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .welcome-card h1 { font-size: 22px; margin-bottom: 8px; }
        .welcome-card p { opacity: 0.9; font-size: 14px; }
        .form-row { display: flex; gap: 10px; }
        .form-row .form-group:first-child { flex: 0 0 100px; }
        .form-row .form-group:last-child { flex: 1; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main">
        <div class="welcome-card">
            <h1>✏️ Editar Cliente</h1>
            <p>Actualice los datos del cliente.</p>
        </div>

        <div class="data-panel">
            <h3>Modificar Información de Cliente</h3>

            <form action="index.php?controller=cliente&action=actualizar" method="POST">
                
                <!-- ID OCULTO -->
                <input type="hidden" name="id" value="<?= $clientes['id'] ?? '' ?>">
                <input type="hidden" name="estado" value="<?= $clientes['estado'] ?? '' ?>">
                <input type="hidden" name="creado_en" value="<?= $clientes['creado_en'] ?? '' ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="tipo_documento">Tipo <span class="required">*</span></label>
                        <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                            <option value="V" <?= (($clientes['tipo_documento'] ?? '') === 'V') ? 'selected' : '' ?>>V - Venezolano</option>
                            <option value="E" <?= (($clientes['tipo_documento'] ?? '') === 'E') ? 'selected' : '' ?>>E - Extranjero</option>
                            <option value="J" <?= (($clientes['tipo_documento'] ?? '') === 'J') ? 'selected' : '' ?>>J - Jurídico</option>
                            <option value="G" <?= (($clientes['tipo_documento'] ?? '') === 'G') ? 'selected' : '' ?>>G - Gubernamental</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="num_documento">Número <span class="required">*</span></label>
                        <input type="text" id="num_documento" name="num_documento" class="form-control" 
                               value="<?= htmlspecialchars($clientes['num_documento'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre_razon_social">
                        Nombre / Razón Social <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre_razon_social" name="nombre_razon_social" 
                           class="form-control" 
                           value="<?= htmlspecialchars($clientes['nombre_razon_social'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= htmlspecialchars($clientes['telefono'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($clientes['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" 
                           value="<?= htmlspecialchars($clientes['direccion'] ?? '') ?>">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-primary">Actualizar Cambios</button>
                    <a href="index.php?controller=cliente&action=tablaClientes" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>


