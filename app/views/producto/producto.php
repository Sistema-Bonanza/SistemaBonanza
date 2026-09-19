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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos · POS & Inventario</title>
    
    <!-- CSS externo del dashboard -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/producto.css" />
    
    <style>

    </style>
</head>
<body>
    
    <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>📦 Gestión de Inventario</h1>
            <p>Administra los productos de Distribuidora Bonanza C.A. Controla costos, precios y niveles de stock.</p>
        </div>

        <!-- SECCIÓN DE PRODUCTOS: BOTONES + LISTA -->
        <div class="products-section">

            <!-- PANEL IZQUIERDO: BOTONES DE ACCIÓN -->
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <a href="index.php?controller=producto&action=formCrear" class="btn-action btn-add-product">
                        <span class="icon-add"></span> Añadir Producto
                    </a>
                </div>
            </div>

            <!-- PANEL DERECHO: LISTA DE PRODUCTOS -->
            <div class="products-list-panel">
                <h3>
                    📋 Catálogo de Productos
                    <span class="product-counter">
                        <?php echo isset($productos) ? count($productos) : 0; ?>
                    </span>
                </h3>

                <div class="table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Código</th>
                                <th>Costo</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="product-name-display">
                                                <div class="product-avatar">
                                                    <!-- Extraemos la primera letra del nombre del producto -->
                                                    <?= strtoupper(substr(htmlspecialchars($p['nombre_producto'] ?? 'P'), 0, 1)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($p['nombre'] ?? 'Sin Nombre') ?></strong>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <span class="badge badge-code">
                                                <?= htmlspecialchars($p['codigo'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            $<?= number_format($p['precio_compra'] ?? 0, 2) ?>
                                        </td>
                                        
                                        <td>
                                            <!-- Lógica visual de Stock -->
                                            <?php if (isset($p['stock_actual']) && isset($p['stock_minimo']) && $p['stock_actual'] <= $p['stock_minimo']): ?>
                                                <span class="badge badge-stock-low" title="Stock por debajo del mínimo">
                                                    ⚠️ <?= htmlspecialchars($p['stock_actual']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-stock-ok">
                                                    <?= htmlspecialchars($p['stock_actual'] ?? 0) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <!-- Lógica visual de Estado -->
                                            <?php if (($p['estado'] ?? 'activo') === 'activo'): ?>
                                                <span class="badge badge-active">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <div class="actions-cell">
                                                <!-- Enlaces a los controladores de edición y eliminación -->
                                                <a href="index.php?controller=producto&action=formEdit&id=<?= $p['id_producto'] ?? $p['id'] ?? '' ?>" 
                                                   class="btn-table btn-edit" 
                                                   title="Editar producto">
                                                    <span class="icon-edit"></span>
                                                </a>
                                                
                                                <a href="index.php?controller=producto&action=eliminar&id=<?= $p['id_producto'] ?? $p['id'] ?? '' ?>" 
                                                   class="btn-table btn-delete-table" 
                                                   title="Eliminar producto"
                                                   onclick="return confirm('¿Estás seguro de eliminar este producto del inventario?');">
                                                    <span class="icon-delete-table"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i>📦</i>
                                            <h4>No hay productos registrados</h4>
                                            <p style="font-size: 14px;">Haz clic en "Añadir Producto" para registrar mercancía en el sistema.</p>
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
</body>
</html>