<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
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
    <title>Registrar Producto · Bonanza</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/crearProducto.css" />
</head>
<body>
    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    
    <main class="main">
        <div class="welcome-card-product">
            <h1>📦 Registrar Nuevo Producto</h1>
            <p>Completa el formulario para añadir mercancía al catálogo de inventario.</p>
        </div>

        <div class="form-wrapper">
            <div class="form-header">
                Datos del Artículo
            </div>

            <form action="index.php?controller=producto&action=guardarProducto" method="POST" class="form-body">
                
                <h4 class="form-section-title">Datos Indispensables</h4>
                
                <div class="grid-2-cols">
                    <div class="form-group">
                        <label>Código de Producto <span class="required">*</span></label>
                        <input type="text" name="codigo" class="form-control" placeholder="Ej. PROD-001" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Producto <span class="required">*</span></label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Harina Pan 1Kg" required>
                    </div>
                </div>

                <div class="grid-2-cols">
                    <div class="form-group">
                        <label>Categoría <span class="required">*</span></label>
                        <select name="id_categoria" class="form-control" required>
                            <option value="" disabled selected>-- Seleccione una Categoría --</option>
                            <?php 
                            // Verificamos si la variable $categorias existe y tiene datos
                            if(isset($categorias) && !empty($categorias)): 
                                foreach($categorias as $cat): 
                            ?>
                                    <!-- Los nombres de las columnas (id_categoria, nombre) deben coincidir con tu BD -->
                                    <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre'] ?></option>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <option value="" disabled>No hay categorías registradas</option>
                            <?php endif; ?>
                        </select>
                    </div>
                  
                </div>

                <hr class="form-divider">

                <h4 class="form-section-title">Datos Adicionales</h4>


                <!-- Pasamos de grid-3-cols a grid-2-cols al eliminar el precio de venta -->
                <div class="grid-2-cols">
                                
                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="aplica_iva" value="1" checked>
                            <span>¿Aplica IVA?</span>
                        </label>
                    </div>
                </div>

                <div class="form-footer">
                    <a href="index.php?controller=producto&action=tablaProductos" class="btn-form btn-cancel">Volver Atrás</a>
                    <button type="submit" class="btn-form btn-submit">Guardar Producto</button>
                </div>

            </form>
        </div>
    </main>
</body>
</html>