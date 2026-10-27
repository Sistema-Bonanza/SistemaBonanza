<?php 
// =================================================================
// CÁLCULOS DINÁMICOS PARA LAS TARJETAS KPI DEL DASHBOARD
// =================================================================
$totalProductos = isset($productos) ? count($productos) : 0;
$totalStock = 0;
$activos = 0;

if (!empty($productos)) {
    foreach ($productos as $p) {
        $totalStock += (int)($p['stock_actual'] ?? 0);
        if (($p['estado'] ?? 'activo') === 'activo') {
            $activos++;
        }
    }
}
$porcentajeActivos = $totalProductos > 0 ? round(($activos / $totalProductos) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos · POS & Inventario</title>
    
    <!-- 1. Framework Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <!-- 2. Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- 3. CSS externos originales de tu sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>
<body class="is-flex" style="height: 100vh; overflow: hidden; width: 100%;">
    
    <!-- ========== SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto;">
        <?= mostrarFlash() ?>

        <div class="p-5" style="width: 100%;">
            
            <!-- ========================================== -->
    <!-- BANNER PRINCIPAL (Estandarizado) -->
    <!-- ========================================== -->
    <div class="box mb-5" style="background: linear-gradient(90deg, #003899 0%, #004ac6 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
        <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
            
            <div class="is-flex is-align-items-center" style="gap: 1rem;">
                <!-- Icono translúcido estandarizado -->
                <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                    <span class="material-symbols-outlined is-size-3 has-text-white">inventory_2</span>
                </div>
                
                <!-- Textos -->
                <div>
                    <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                        <h1 class="title is-4 has-text-white mb-0">Gestión de Inventario</h1>
                        <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">Matriz Bonanza</span>
                    </div>
                    <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                        Administra los productos de Distribuidora Bonanza C.A. Controla costos, precios y niveles de stock con trazabilidad total.
                    </p>
                </div>
            </div>

            <!-- Botón de Acción Estandarizado -->
            <div class="buttons mb-0">
                <!-- Asegúrate de que el action de urlAccion coincida con tu enrutador -->
                <a href="<?= esc(urlAccion('producto', 'formCrear')) ?>" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                    <span class="icon"><span class="material-symbols-outlined">add</span></span>
                    <span>Añadir Producto</span>
                </a>
            </div>
            
        </div>
    </div>

            <!-- 2. TARJETAS DE MÉTRICAS (KPIs DINÁMICOS) -->
            <div class="columns is-multiline mb-4">
                
                <!-- KPI 1: Total Registrados (Azul / Información) -->
                <div class="column is-4-desktop">
                    <div class="box is-shadowless is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #3e8ed0;">
                        <div>
                            <p class="heading has-text-grey mb-1">Total Registrados</p>
                            <div class="is-flex is-align-items-baseline" style="gap: 0.5rem;">
                                <p class="title is-3 mb-0"><?= $totalProductos ?></p>
                                <p class="is-size-7 has-text-info has-text-weight-bold is-flex is-align-items-center">
                                    <span class="material-symbols-outlined is-size-6 mr-1">check_circle</span> <?= $porcentajeActivos ?>% Activos
                                </p>
                            </div>
                        </div>
                        <div class="has-background-info-light has-text-info" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">category</span>
                        </div>
                    </div>
                </div>
                
                <!-- KPI 2: Stock Total (Turquesa / Volumen) -->
                <div class="column is-4-desktop">
                    <div class="box is-shadowless is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #00d1b2;">
                        <div>
                            <p class="heading has-text-grey mb-1">Stock Total en Almacén</p>
                            <div class="is-flex is-align-items-baseline" style="gap: 0.5rem;">
                                <p class="title is-3 mb-0"><?= number_format($totalStock, 0) ?></p>
                                <p class="is-size-7 has-text-grey">unidades físicas</p>
                            </div>
                        </div>
                        <div class="has-background-primary-light has-text-primary" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">pallet</span>
                        </div>
                    </div>
                </div>
                
                <!-- KPI 3: Estado de Salud (Verde / Éxito) -->
                <div class="column is-4-desktop">
                    <div class="box is-shadowless is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #48c774;">
                        <div>
                            <p class="heading has-text-grey mb-1">Estado de Salud</p>
                            <p class="title is-5 mb-0 has-text-success">Óptimo</p>
                            <p class="is-size-7 has-text-grey mt-1"><?= $activos ?> de <?= $totalProductos ?> listos para venta</p>
                        </div>
                        <div class="has-background-success-light has-text-success" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">verified</span>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- 3. BARRA DE FILTROS Y BÚSQUEDA -->
            <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                <div class="is-flex is-flex-direction-column-touch is-align-items-center-desktop is-justify-content-space-between" style="gap: 1rem;">
                    <div class="is-flex is-flex-direction-column-touch is-align-items-center-desktop" style="gap: 1rem; width: 100%; max-width: 800px;">
                        
                        <!-- Buscador -->
                        <div class="field mb-0" style="flex: 1; width: 100%;">
                            <div class="control has-icons-left">
                                <input class="input has-background-white-ter" type="text" id="product-search-input" placeholder="Buscar por código o nombre...">
                                <span class="icon is-left has-text-grey"><span class="material-symbols-outlined">search</span></span>
                            </div>
                        </div>
                        
                        <!-- Botones de Filtro (JS) -->
                        <div class="buttons has-addons mb-0 is-flex-wrap-nowrap" style="overflow-x: auto;">
                            <button class="button is-link filter-tab" data-filter="all">Todos</button>
                            <button class="button is-light filter-tab" data-filter="General">General</button>
                            <!-- Agrega más categorías aquí si lo necesitas -->
                        </div>
                    </div>

                    <div class="buttons mb-0">
                        <button class="button is-light has-text-weight-bold" id="btn-export-csv">
                            <span class="icon"><span class="material-symbols-outlined">file_download</span></span>
                            <span>Exportar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. TABLA DE CATÁLOGO (DINÁMICA) -->
            <div class="box is-shadowless p-0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <!-- Cabecera de la tabla -->
                <div class="has-background-white-ter p-4 is-flex is-justify-content-space-between is-align-items-center" style="border-bottom: 1px solid #e2e8f0;">
                    <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                        <span class="material-symbols-outlined has-text-link">inventory</span>
                        <h2 class="title is-6 mb-0">Catálogo de Productos</h2>
                        <span class="tag is-info is-light is-rounded has-text-weight-bold ml-2" id="product-counter"><?= $totalProductos ?> registros</span>
                    </div>
                </div>

                <!-- Contenedor de la tabla -->
                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-striped mb-0 text-xxs">
                        <thead>
                            <tr class="has-text-grey is-uppercase is-size-7">
                                <th class="py-3 px-4">Producto</th>
                                <th class="py-3 px-4 has-text-centered">Código</th>
                                <th class="py-3 px-4 has-text-right">Costo Unitario</th>
                                <th class="py-3 px-4 has-text-centered">Stock Disponible</th>
                                <th class="py-3 px-4 has-text-centered">Estado</th>
                                <th class="py-3 px-4 has-text-centered">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-tbody">
                            
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <?php 
                                        // Definición de variables limpias
                                        $id = $p['id_producto'] ?? $p['id'] ?? '';
                                        $nombre = htmlspecialchars($p['nombre'] ?? 'Sin Nombre');
                                        $codigo = htmlspecialchars($p['codigo'] ?? 'N/A');
                                        $costo = number_format($p['precio_compra'] ?? 0, 2);
                                        $stock = (int)($p['stock_actual'] ?? 0);
                                        $stockMinimo = (int)($p['stock_minimo'] ?? 0);
                                        $esActivo = (($p['estado'] ?? 'activo') === 'activo');
                                        
                                        // Inicial del Avatar
                                        $inicial = strtoupper(substr($p['nombre_producto'] ?? $nombre, 0, 1));
                                    ?>
                                    
                                    <!-- Fila de Producto -->
                                    <tr class="product-row" data-category="General" data-code="<?= $codigo ?>" data-name="<?= $nombre ?>">
                                        <td class="py-3 px-4">
                                            <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                                                <div class="has-background-info-light has-text-link has-text-weight-bold is-size-5" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <?= $inicial ?>
                                                </div>
                                                <div>
                                                    <p class="has-text-weight-bold has-text-dark is-size-6 mb-0" style="line-height: 1.2;"><?= $nombre ?></p>
                                                    <p class="is-size-7 has-text-grey">Categoría General</p>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="py-3 px-4 has-text-centered align-middle">
                                            <span class="tag is-light is-medium font-mono has-text-weight-bold"><?= $codigo ?></span>
                                        </td>
                                        
                                        <td class="py-3 px-4 has-text-right align-middle font-mono has-text-weight-bold">
                                            $<?= $costo ?>
                                        </td>
                                        
                                        <td class="py-3 px-4 has-text-centered align-middle">
                                            <?php if ($stock <= $stockMinimo): ?>
                                                <span class="tag is-warning is-light is-rounded has-text-weight-bold" title="Stock por debajo del mínimo">
                                                    ⚠️ <?= $stock ?> unids
                                                </span>
                                            <?php else: ?>
                                                <span class="tag is-link is-light is-rounded has-text-weight-bold">
                                                    <?= $stock ?> unids
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="py-3 px-4 has-text-centered align-middle">
                                            <?php if ($esActivo): ?>
                                                <span class="tag is-success is-light is-rounded has-text-weight-bold">
                                                    <span style="width:6px; height:6px; background:#48c774; border-radius:50%; margin-right:4px;"></span> Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="tag is-danger is-light is-rounded has-text-weight-bold">
                                                    <span style="width:6px; height:6px; background:#f14668; border-radius:50%; margin-right:4px;"></span> Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="py-3 px-4 has-text-centered align-middle">
                                            <div class="buttons is-centered mb-0 are-small">
                                                <!-- BOTÓN EDITAR -->
                                                <a href="index.php?controller=producto&action=formEditar&id=<?= $id ?>" class="button is-light has-text-link has-text-weight-bold" title="Editar">
                                                    <span class="icon"><span class="material-symbols-outlined is-size-6">edit</span></span>
                                                    <span>Editar</span>
                                                </a>
                                                
                                                <!-- BOTÓN DESACTIVAR (Controlado por SweetAlert) -->
                                            <form action="index.php?controller=producto&action=eliminarproducto" method="POST" style="display: inline-block;">
                                                <?= campoCsrf() ?>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                                                
                                                <!-- Nota que cambiamos type="submit" a type="button" y agregamos el onclick -->
                                                <button type="button" class="button is-warning is-small is-light" title="Desactivar Producto" onclick="confirmarDesactivacion(this.form)">
                                                    <span class="icon"><span class="material-symbols-outlined is-size-6">block</span></span>
                                                </button>
                                            </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- ESTADO VACÍO -->
                                <tr>
                                    <td colspan="6" class="has-text-centered py-6">
                                        <div class="is-flex is-flex-direction-column is-align-items-center">
                                            <span class="material-symbols-outlined is-size-1 has-text-grey-light mb-3">inventory_2</span>
                                            <h4 class="title is-5 has-text-grey">No hay productos registrados</h4>
                                            <p class="has-text-grey">Haz clic en "Añadir Producto" para registrar mercancía en el sistema.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación Footer -->
                <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="border-top: 1px solid #e2e8f0;">
                    <p class="is-size-7 has-text-grey" id="pagination-info">
                        Mostrando <strong class="has-text-dark"><?= $totalProductos > 0 ? 1 : 0 ?> - <?= $totalProductos ?></strong> de <strong class="has-text-dark"><?= $totalProductos ?></strong> productos registrados
                    </p>
                </div>
            </div>
            
        </div>
        
        <!-- 1. CDN de SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- 2. Puente PHP -> JS (Solo se imprime si hay un error) -->
        <?php if (isset($_SESSION['sweet_error_stock'])): ?>
            <script>
                // Guardamos el mensaje de PHP en una variable global de JS
                window.sweetErrorStock = "<?= htmlspecialchars($_SESSION['sweet_error_stock'], ENT_QUOTES, 'UTF-8') ?>";
            </script>
            <?php unset($_SESSION['sweet_error_stock']); ?>
        <?php endif; ?>

        <!-- 3. Tu archivo JS (Asegúrate de que la ruta absoluta o relativa sea la correcta según lo vimos) -->
        <script src="assets/js/productos.js?v=<?= time() ?>"></script>
    
    </main>

</body>
</html>