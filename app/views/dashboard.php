<?php
// Variables esperadas del DashboardController (ejemplo robusto adaptado al sistema real):
//   $estadisticas (total_productos, disponibles, stock_total, valor_inventario, proveedores_activos)
//   $catalogo (lista de productos rápidos)
//   $inventario (tabla completa de stock)
//   $usuario (nombre, rol, turno)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Datos por defecto por si el controlador entrega arrays vacíos
$stats = $estadisticas ?? [
    'total_productos' => 124,
    'disponibles' => 98,
    'stock_total' => 1450,
    'valor_inventario' => 42850.00,
    'proveedores_activos' => 18
];
$productosCatalogo = $catalogo ?? [];
$listaInventario = $inventario ?? [];
$userName = $_SESSION['username'] ?? 'admin';
$userRole = $_SESSION['role_name'] ?? 'Supervisor General';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control General · Industrias Bonanza</title>

    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f1f5f9; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f1f5f9; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <!-- Mensajes Flash ocultos para SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO INSTITUCIONAL (Alto Contraste) -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative; box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white" style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-2">dashboard_customize</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.2); color: white !important;">
                                    Operaciones Globales
                                </span>
                                <span class="has-text-white is-size-7">• Control de Almacén Central</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Panel de Control General</h1>
                            <p class="has-text-white-bis is-size-7 mt-1" style="max-width: 700px; opacity: 0.95;">
                                Planta Central • Almacén General de Dotaciones e Insumos Industriales.
                            </p>
                        </div>
                    </div>

                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="has-text-right has-text-white is-hidden-touch">
                            <p class="is-size-7 has-text-weight-bold mb-0" id="live-fecha">Sábado, 31 Octubre 2026</p>
                            <p class="is-size-7 has-text-white-bis font-mono" id="live-chronometer">05:14:00 AM</p>
                        </div>
                        <a href="<?= esc(urlAccion('movimiento', 'crear')) ?>" class="button is-white has-text-weight-bold" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <span class="icon has-text-link"><span class="material-symbols-outlined">add_circle</span></span>
                            <span>+ Nueva Entrada</span>
                        </a>
                    </div>
 
                </div>
            </div>

            <!-- ========================================== -->
            <!-- FILA DE TARJETAS KPI (4 MÉTRICAS) -->
            <!-- ========================================== -->
            <div class="columns is-variable is-4 mb-5">
                <!-- KPI 1: Productos en Total -->
                <div class="column is-3">
                    <div class="box is-shadowless p-4" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; border-left: 5px solid #2563eb;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                            <span class="is-size-7 has-text-weight-bold has-text-grey-dark is-uppercase">Productos Total</span>
                            <span class="icon has-text-link has-background-link-light p-3" style="border-radius: 8px;"><span class="material-symbols-outlined">inventory</span></span>
                        </div>
                        <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                            <span class="title is-3 mb-0 has-text-dark font-weight-black"><?= (int) ($stats['total_productos'] ?? 0) ?></span>
                            <span class="tag is-success is-light is-small has-text-weight-bold">Activos</span>
                        </div>
                        <p class="is-size-7 has-text-grey mt-1">Registrados en catálogo</p>
                    </div>
                </div>

                <!-- KPI 2: Productos Disponibles (Con stock > 0) -->
                <div class="column is-3">
                    <div class="box is-shadowless p-4" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; border-left: 5px solid #10b981;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                            <span class="is-size-7 has-text-weight-bold has-text-grey-dark is-uppercase">Disponibles</span>
                            <span class="icon has-text-success has-background-success-light p-3" style="border-radius: 8px;"><span class="material-symbols-outlined">check_circle</span></span>
                        </div>
                        <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                            <span class="title is-3 mb-0 has-text-dark font-weight-black"><?= (int) ($stats['disponibles'] ?? 0) ?></span>
                            <span class="tag is-info is-light is-small has-text-weight-bold">Aptos</span>
                        </div>
                        <p class="is-size-7 has-text-grey mt-1">Listos para salida inmediata</p>
                    </div>
                </div>

                <!-- KPI 3: Stock Total (Suma de unidades + Valorización) -->
                <div class="column is-3">
                    <div class="box is-shadowless p-4" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; border-left: 5px solid #0284c7;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                            <span class="is-size-7 has-text-weight-bold has-text-grey-dark is-uppercase">Stock Total</span>
                            <span class="icon has-text-info has-background-info-light p-3" style="border-radius: 8px;"><span class="material-symbols-outlined">package_2</span></span>
                        </div>
                        <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                            <span class="title is-3 mb-0 has-text-dark font-weight-black"><?= number_format($stats['stock_total'] ?? 0, 0, ',', '.') ?></span>
                            <span class="is-size-7 has-text-grey font-semibold">unidades</span>
                        </div>
                        <p class="is-size-7 has-text-grey mt-1">
                            Valor: <strong><?= function_exists('dinero') ? esc(dinero($stats['valor_inventario'] ?? 0)) : '$' . number_format($stats['valor_inventario'] ?? 0, 2, ',', '.') ?></strong>
                        </p>
                    </div>
                </div>

                <!-- KPI 4: Proveedores Activos -->
                <div class="column is-3">
                    <div class="box is-shadowless p-4" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; border-left: 5px solid #d97706;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                            <span class="is-size-7 has-text-weight-bold has-text-grey-dark is-uppercase">Proveedores</span>
                            <span class="icon has-text-warning-dark has-background-warning-light p-3" style="border-radius: 8px;"><span class="material-symbols-outlined">local_shipping</span></span>
                        </div>
                        <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                            <span class="title is-3 mb-0 has-text-dark font-weight-black"><?= (int) ($stats['proveedores_activos'] ?? 18) ?></span>
                            <span class="tag is-success is-light is-small has-text-weight-bold">Certificados</span>
                        </div>
                        <p class="is-size-7 has-text-grey mt-1">Industriales activos</p>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- ÁREA INTERMEDIA: CATÁLOGO RÁPIDO + CARRITO -->
            <!-- ========================================== -->
            <div class="columns is-variable is-4 mb-5">
                
                <!-- Columna Izquierda: Catálogo Rápido (8 cols) -->
                <div class="column is-8">
                    <div class="box is-shadowless p-5 h-100" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4 pb-3" style="border-bottom: 2px solid #f1f5f9;">
                            <div>
                                <h3 class="title is-6 mb-1 has-text-dark has-text-weight-bold">Catálogo Rápido de Productos</h3>
                                <p class="is-size-7 has-text-grey">Haz clic en <strong class="has-text-link">+</strong> para agregar ítems al carrito de despacho</p>
                            </div>
                            <div class="buttons are-small mb-0">
                                <button class="button is-link is-rounded is-small font-weight-bold">Todos</button>
                                <button class="button is-light is-rounded is-small">Seguridad</button>
                                <button class="button is-light is-rounded is-small">Calzado</button>
                            </div>
                        </div>

                        <!-- Grilla Dinámica del Catálogo -->
                        <div class="columns is-multiline">
                            <?php if (!empty($productosCatalogo)): ?>
                                <?php foreach ($productosCatalogo as $prod): ?>
                                    <div class="column is-6">
                                        <div class="box is-shadowless p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                                            <div class="is-flex is-justify-content-space-between mb-2">
                                                <span class="tag is-dark is-rounded is-small font-mono"><?= esc($prod['codigo']) ?></span>
                                                <span class="tag is-success is-light is-rounded is-small">Stock: <?= (int) $prod['stock'] ?></span>
                                            </div>
                                            <p class="is-size-7 has-text-weight-bold has-text-dark mb-1"><?= esc($prod['nombre']) ?></p>
                                            <p class="is-size-7 has-text-grey mb-3">Cat: <strong><?= esc($prod['nombre_categoria'] ?? 'General') ?></strong></p>
                                            
                                            <div class="is-flex is-justify-content-space-between is-align-items-flex-end pt-2" style="border-top: 1px solid #e2e8f0;">
                                                <div>
                                                    <span class="is-size-7 has-text-grey is-uppercase has-text-weight-bold">Precio Unit.</span>
                                                    <?= function_exists('dinero') ? esc(dinero($prod['precio_venta'])) : '$' . number_format($prod['precio_venta'], 2, ',', '.') ?>
                                                </div>
                                                <button type="button" class="button is-link is-small has-text-weight-bold" 
                                                        onclick="addItemToCart('<?= esc($prod['nombre']) ?>', <?= (float) $prod['precio_venta'] ?>)">
                                                    <span class="icon"><span class="material-symbols-outlined">add</span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Datos de ejemplo por defecto si el array está vacío -->
                                <div class="column is-6">
                                    <div class="box is-shadowless p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                                        <div class="is-flex is-justify-content-space-between mb-2">
                                            <span class="tag is-dark is-rounded is-small font-mono">SKU-BON-0101</span>
                                            <span class="tag is-success is-light is-rounded is-small">Stock: 48 unids</span>
                                        </div>
                                        <p class="is-size-7 has-text-weight-bold has-text-dark mb-1">Casco Dieléctrico Tipo I Clase E</p>
                                        <p class="is-size-7 has-text-grey mb-3">Talla: <strong>Ajustable</strong> • Prov: <strong>Segurind</strong></p>
                                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end pt-2" style="border-top: 1px solid #e2e8f0;">
                                            <div>
                                                <span class="is-size-7 has-text-grey is-uppercase has-text-weight-bold">Precio Unit.</span>
                                                <p class="is-size-5 has-text-weight-bold has-text-link mb-0">$18.50</p>
                                            </div>
                                            <button type="button" class="button is-link is-small has-text-weight-bold" onclick="addItemToCart('Casco Dieléctrico', 18.50)">
                                                <span class="icon"><span class="material-symbols-outlined">add</span></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="box is-shadowless p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                                        <div class="is-flex is-justify-content-space-between mb-2">
                                            <span class="tag is-dark is-rounded is-small font-mono">SKU-BON-0340</span>
                                            <span class="tag is-success is-light is-rounded is-small">Stock: 65 piezas</span>
                                        </div>
                                        <p class="is-size-7 has-text-weight-bold has-text-dark mb-1">Camisa Drill Reforzada Manga Larga</p>
                                        <p class="is-size-7 has-text-grey mb-3">Talla: <strong>L (40-42)</strong> • Prov: <strong>Textil Ind.</strong></p>
                                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end pt-2" style="border-top: 1px solid #e2e8f0;">
                                            <div>
                                                <span class="is-size-7 has-text-grey is-uppercase has-text-weight-bold">Precio Unit.</span>
                                                <p class="is-size-5 has-text-weight-bold has-text-link mb-0">$22.00</p>
                                            </div>
                                            <button type="button" class="button is-link is-small has-text-weight-bold" onclick="addItemToCart('Camisa Drill (L)', 22.00)">
                                                <span class="icon"><span class="material-symbols-outlined">add</span></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Carrito de Despacho (4 cols) -->
                <div class="column is-4">
                    <div class="box is-shadowless p-5 h-100 is-flex is-flex-direction-column" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4 pb-3" style="border-bottom: 2px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <div class="is-flex is-align-items-center is-justify-content-center has-background-link-light has-text-link" style="width: 36px; height: 36px; border-radius: 8px;">
                                    <span class="material-symbols-outlined is-size-5">shopping_cart_checkout</span>
                                </div>
                                <div>
                                    <h3 class="title is-6 mb-0 has-text-dark has-text-weight-bold">Carrito Despacho</h3>
                                    <p class="is-size-7 has-text-grey font-mono">ORD-2026-089</p>
                                </div>
                            </div>
                            <span class="tag is-link is-light is-rounded has-text-weight-bold" id="cart-item-count">2 ítems</span>
                        </div>

                        <!-- Lista de Ítems -->
                        <div class="mb-4" style="max-height: 220px; overflow-y: auto; flex-grow: 1;" id="cart-items-container">
                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3 pb-2" style="border-bottom: 1px solid #f1f5f9;" id="row-casco">
                                <div style="flex: 1; min-width: 0;">
                                    <p class="is-size-7 has-text-weight-bold has-text-dark">Casco Dieléctrico</p>
                                    <p class="is-size-7 has-text-grey">$18.50 c/u</p>
                                </div>
                                <div class="is-flex is-align-items-center has-background-light rounded border mx-2 px-1">
                                    <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="updateQty('casco', -1, 18.50)">-</button>
                                    <span class="is-size-7 has-text-weight-bold px-2" id="qty-casco">2</span>
                                    <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="updateQty('casco', 1, 18.50)">+</button>
                                </div>
                                <div class="has-text-right">
                                    <p class="is-size-7 has-text-weight-bold font-mono has-text-dark" id="total-casco">$37.00</p>
                                    <a href="#" class="has-text-danger is-size-7" onclick="removeItem('row-casco'); return false;">Quitar</a>
                                </div>
                            </div>

                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3 pb-2" style="border-bottom: 1px solid #f1f5f9;" id="row-camisa">
                                <div style="flex: 1; min-width: 0;">
                                    <p class="is-size-7 has-text-weight-bold has-text-dark">Camisa Drill (L)</p>
                                    <p class="is-size-7 has-text-grey">$22.00 c/u</p>
                                </div>
                                <div class="is-flex is-align-items-center has-background-light rounded border mx-2 px-1">
                                    <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="updateQty('camisa', -1, 22.00)">-</button>
                                    <span class="is-size-7 has-text-weight-bold px-2" id="qty-camisa">1</span>
                                    <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="updateQty('camisa', 1, 22.00)">+</button>
                                </div>
                                <div class="has-text-right">
                                    <p class="is-size-7 has-text-weight-bold font-mono has-text-dark" id="total-camisa">$22.00</p>
                                    <a href="#" class="has-text-danger is-size-7" onclick="removeItem('row-camisa'); return false;">Quitar</a>
                                </div>
                            </div>
                        </div>

                        <!-- Totales y Botones -->
                        <div class="pt-3" style="border-top: 2px solid #e2e8f0;">
                            <div class="is-flex is-justify-content-space-between mb-1">
                                <span class="is-size-7 has-text-grey">Subtotal</span>
                                <span class="is-size-7 has-text-weight-bold font-mono" id="cart-subtotal">$59.00</span>
                            </div>
                            <div class="is-flex is-justify-content-space-between mb-3">
                                <span class="is-size-7 has-text-grey">IVA (16%)</span>
                                <span class="is-size-7 has-text-weight-bold font-mono" id="cart-tax">$9.44</span>
                            </div>
                            <div class="is-flex is-justify-content-space-between is-align-items-baseline mb-4 pt-2" style="border-top: 1px solid #cbd5e1;">
                                <span class="has-text-weight-bold has-text-dark">Total Liquidado</span>
                                <span class="title is-4 has-text-link mb-0 font-mono" id="cart-total">$68.44</span>
                            </div>
                            
                            <div class="is-flex" style="gap: 10px;">
                                <button type="button" class="button is-light is-small has-text-weight-bold is-fullwidth" onclick="resetCart()">Reset</button>
                                <button type="button" class="button is-link is-small has-text-weight-bold is-fullwidth" onclick="triggerCheckout()">Cobrar</button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TABLA INFERIOR DE INVENTARIO COMPLETO -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-0" style="border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; background: white;">
                
                <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="background-color: #1e293b; color: white;">
                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                        <span class="tag is-link is-rounded is-small"></span>
                        <h3 class="title is-6 mb-0 has-text-white has-text-weight-bold">Inventario Completo y Stock en Servidor</h3>
                    </div>
                    <a href="<?= esc(urlAccion('producto', 'crear')) ?>" class="button is-link is-small has-text-weight-bold">
                        <span class="icon"><span class="material-symbols-outlined">add_box</span></span>
                        <span>Añadir Producto</span>
                    </a>
                </div>

                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-vcentered mb-0 is-size-7">
                        <thead>
                            <tr style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                <th class="has-text-weight-bold has-text-dark">SKU / Código</th>
                                <th class="has-text-weight-bold has-text-dark">Producto</th>
                                <th class="has-text-weight-bold has-text-dark text-center">Categoría</th>
                                <th class="has-text-weight-bold has-text-dark has-text-right">Stock</th>
                                <th class="has-text-weight-bold has-text-dark has-text-right">Precio Venta</th>
                                <th class="has-text-weight-bold has-text-dark has-text-centered">Estado</th>
                                <th class="has-text-weight-bold has-text-dark has-text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($listaInventario)): ?>
                                <?php foreach ($listaInventario as $inv): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="font-mono has-text-weight-bold text-primary"><?= esc($inv['codigo']) ?></td>
                                        <td>
                                            <div class="has-text-weight-bold has-text-dark"><?= esc($inv['nombre']) ?></div>
                                        </td>
                                        <td class="text-center"><span class="tag is-light"><?= esc($inv['nombre_categoria'] ?? 'General') ?></span></td>
                                        
                                        <!-- Corregido de 'stock' a 'stock_actual' -->
                                        <td class="has-text-right font-mono has-text-weight-bold is-size-6"><?= (int) ($inv['stock_actual'] ?? 0) ?></td>
                                        
                                        <!-- Protegido con validación por si la función dinero() no está cargada -->
                                        <td class="has-text-right font-mono has-text-weight-bold"><?= function_exists('dinero') ? esc(dinero($inv['precio_venta'])) : '$' . number_format($inv['precio_venta'] ?? 0, 2, ',', '.') ?></td>
                                        
                                        <td class="has-text-centered">
                                            <?php $stockVal = (int) ($inv['stock_actual'] ?? 0); ?>
                                            <?php if ($stockVal > 10): ?>
                                                <span class="tag is-success is-rounded is-small font-weight-bold">En Stock</span>
                                            <?php elseif ($stockVal > 0): ?>
                                                <span class="tag is-warning is-rounded is-small font-weight-bold">Bajo Stock</span>
                                            <?php else: ?>
                                                <span class="tag is-danger is-rounded is-small font-weight-bold">Agotado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="has-text-right">
                                            <div class="is-flex is-justify-content-flex-end" style="gap: 5px;">
                                                <a href="<?= esc(urlAccion('producto', 'editar', ['id' => $inv['id_producto']])) ?>" class="button is-white is-small" style="border: 1px solid #cbd5e1;" title="Editar">
                                                    <span class="icon has-text-link"><span class="material-symbols-outlined" style="font-size: 16px;">edit</span></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="has-text-centered py-4 has-text-grey">No hay productos registrados en el inventario.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                    <span class="is-size-7 has-text-weight-semibold has-text-dark">Inventario sincronizado con servidor central</span>
                    <span class="tag is-dark font-mono has-text-weight-bold">Terminal Activa</span>
                </div>

            </div>

        </div>
    </main>

    <!-- Librerías de JavaScript y SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>