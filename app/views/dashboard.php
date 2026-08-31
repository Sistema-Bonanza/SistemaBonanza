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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard · POS & Inventario</title>
    <!-- CSS externo -->
     <link rel="stylesheet" href="assets/css/dashboard.css" /> 

</head>
<body>

    <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- Stats -->
        <section class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="number" id="totalProducts">0</div>
                <div class="label">Productos en total</div>
            </div>
            <div class="stat-card">
                <div class="number" id="availableProducts">0</div>
                <div class="label">Productos disponibles</div>
            </div>
            <div class="stat-card">
                <div class="number" id="totalStock">0</div>
                <div class="label">Cantidad total en stock</div>
            </div>
            <div class="stat-card">
                <div class="number" id="totalSuppliers">0</div>
                <div class="label">Total de proveedores</div>
            </div>
        </section>

        <!-- POS Row: Product List + Cart -->
        <section class="pos-row">
            <!-- Product list -->
            <div class="product-list">
                <h2>
                    Productos
                    <small>haz clic en ➕ para agregar</small>
                </h2>
                <div id="productList">
                    <!-- Los productos se inyectan desde JS -->
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="cart-panel">
                <h2>🛒 Carrito</h2>
                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">El carrito está vacío</div>
                </div>
                <div class="cart-total">
                    <span>Total</span>
                    <span id="cartTotal">$0.00</span>
                </div>
                <div class="cart-actions">
                    <button class="btn-reset" id="resetCartBtn">⟳ Reset Order</button>
                    <button class="btn-end" id="endShiftBtn">⏻ End Shift</button>
                </div>
            </div>
        </section>

        <!-- Inventory Table -->
        <section class="inventory-section">
            <h2>
                📋 Inventario completo
                <span class="text-muted">todos los productos</span>
            </h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Talla</th>
                            <th>Proveedor</th>
                            <th>Categoría</th>
                            <th>Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody">
                        <!-- Datos desde JS -->
                    </tbody>
                </table>
            </div>
            <div class="table-actions">
                <button class="btn-download">⬇ Descargar inventario</button>
                <button class="btn-add">➕ Añadir producto</button>
            </div>
        </section>

    </main>


</body>
</html>