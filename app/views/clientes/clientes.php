<?php
$totalClientes     = isset($clientes) ? count($clientes) : 0;

// Datos KPI
$kpiConCompras     = $kpiConCompras ?? 0; // Clientes que han comprado este mes
$kpiPorcentajeC    = $totalClientes > 0 ? round(($kpiConCompras / $totalClientes) * 100) : 0;
$kpiLiderNombre    = $kpiLiderNombre ?? 'Sin datos';
$kpiLiderUbicacion = $kpiLiderUbicacion ?? 'N/A';
$kpiLiderPedidos   = $kpiLiderPedidos ?? 0;
$kpiHuerfanos      = $kpiHuerfanos ?? 0; // Clientes con errores de integridad (rif inválido, etc)

// Datos para botones de filtro
$countActivos      = $countActivos ?? $totalClientes; // Por defecto asumimos todos activos si no viene el dato
$countInactivos    = $countInactivos ?? 0;
$countConSaldo     = $countConSaldo ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes · Industrias Bonanza</title>
    
    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <!-- Tus estilos base -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        
        <!-- Interceptor de mensajes Flash de PHP para pasarlos a SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- BANNER HERO -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">groups</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Gestión de Clientes</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Directorio Comercial
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Administra el patrón de clientes, información fiscal y contacto comercial.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <!-- Enlace dinámico con tu controlador actual -->
                        <a href="index.php?controller=cliente&action=formcrearcliente" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">add</span></span>
                            <span>Añadir Cliente</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TARJETAS KPI -->
            <div class="columns is-multiline mb-5">
                
                <!-- KPI 1: Total Clientes -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 12px; background-color: #ffffff;">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Total Clientes</span>
                                <span class="tag is-info is-light is-rounded" style="font-size: 0.6rem;">Activos</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 has-text-dark"><?= $totalClientes ?></span>
                                <span class="is-size-7 has-text-grey has-text-weight-semibold">clientes</span>
                            </div>
                            <p class="is-size-7 has-text-success has-text-weight-semibold mt-1 is-flex is-align-items-center gap-1">
                                <span class="material-symbols-outlined is-size-6">trending_up</span> 100% operativos
                            </p>
                        </div>
                        <div class="has-background-info-light has-text-link p-3 ml-2 flex-shrink-0" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">folder_shared</span>
                        </div>
                    </div>
                </div>
                
                <!-- KPI 2: Clientes con Compras -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #4f46e5; border-radius: 12px; background-color: #ffffff;">
                        <div style="width: 100%;">
                            <div class="is-flex is-align-items-center justify-content-space-between mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Con Compras</span>
                                <span class="tag is-link is-light is-rounded" style="font-size: 0.6rem;">Mes Actual</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 has-text-dark"><?= $kpiConCompras ?></span>
                                <span class="is-size-7 has-text-grey has-text-weight-semibold">compradores</span>
                            </div>
                            <progress class="progress is-link is-small mt-2 mb-0" value="<?= $kpiPorcentajeC ?>" max="100" style="height: 6px;"><?= $kpiPorcentajeC ?>%</progress>
                        </div>
                        <div class="has-background-link-light has-text-link p-3 ml-3 flex-shrink-0" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">shopping_cart</span>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Mayor Volumen -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #d97706; border-radius: 12px; background-color: #ffffff;">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Mayor Volumen</span>
                                <span class="tag is-warning is-light is-rounded" style="font-size: 0.6rem;">Líder</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-5 mb-0 has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;"><?= htmlspecialchars($kpiLiderNombre) ?></span>
                            </div>
                            <p class="is-size-7 has-text-grey has-text-weight-semibold mt-1"><?= htmlspecialchars($kpiLiderUbicacion) ?> • <?= $kpiLiderPedidos ?> Pedidos</p>
                        </div>
                        <div class="has-background-warning-light has-text-warning-dark p-3 ml-2 flex-shrink-0" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">workspace_premium</span>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Integridad BD -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid <?= $kpiHuerfanos > 0 ? '#fecaca' : '#e2e8f0' ?>; border-left: 4px solid <?= $kpiHuerfanos > 0 ? '#ef4444' : '#10b981' ?>; border-radius: 12px; <?= $kpiHuerfanos > 0 ? 'background-color: #fef2f2;' : 'background-color: #ffffff;' ?>">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Integridad BD</span>
                                <span class="tag is-success is-light is-rounded" style="font-size: 0.6rem;">Auditado</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-dark' ?>"><?= $kpiHuerfanos > 0 ? 'Alerta' : '100%' ?></span>
                            </div>
                            <p class="is-size-7 has-text-weight-semibold mt-1 is-flex is-align-items-center gap-1 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-grey' ?>">
                                <span class="material-symbols-outlined is-size-6 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-info' ?>" style="vertical-align: middle;">
                                    <?= $kpiHuerfanos > 0 ? 'warning' : 'verified' ?>
                                </span> 
                                <?= $kpiHuerfanos ?> doc. huérfanos
                            </p>
                        </div>
                        <div class="<?= $kpiHuerfanos > 0 ? 'has-background-danger-light has-text-danger' : 'has-background-success-light has-text-success-dark' ?> p-3 ml-2 flex-shrink-0" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">shield_person</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- BARRA DE BÚSQUEDA Y FILTROS -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-4 mb-5 is-flex is-align-items-center is-justify-content-space-between flex-wrap gap-4" style="border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                
                <!-- Buscador -->
                <div class="control has-icons-left" style="width: 100%; max-width: 380px; flex-shrink: 0;">
                    <input class="input is-link is-light" type="text" id="buscadorClientes" placeholder="Buscar por cliente, documento..." style="border-radius: 8px;">
                    <span class="icon is-small is-left has-text-grey"><span class="material-symbols-outlined">search</span></span>
                </div>

                <!-- Botones de Filtro -->
                <div class="is-flex is-align-items-center flex-wrap" style="gap: 1rem;">
                    <div class="buttons has-addons mb-0" id="filtrosEstado">
                        <button class="button is-small is-link is-selected btn-filtro" data-filtro="todos">
                            Todas <span class="ml-1 tag is-white is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $totalClientes ?></span>
                        </button>
                        <button class="button is-small is-light btn-filtro" data-filtro="activos">
                            Activos <span class="ml-1 tag is-info is-light is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $countActivos ?? $totalClientes ?></span>
                        </button>
                        <button class="button is-small is-light btn-filtro" data-filtro="inactivos">
                            Inactivos <span class="ml-1 tag is-info is-light is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $countInactivos ?? 0 ?></span>
                        </button>
                    </div>
                    
                    <button class="button is-small is-white" style="border: 1px solid #e2e8f0; border-radius: 8px; padding-left: 1rem; padding-right: 1rem;">
                        <span class="icon has-text-grey"><span class="material-symbols-outlined">download</span></span>
                        <span class="has-text-weight-semibold has-text-dark">Exportar Padrón</span>
                    </button>
                </div>
            </div>

            <!-- TABLA DE CLIENTES -->
            <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <div class="p-4 is-flex is-align-items-center is-justify-content-space-between has-background-white" style="border-bottom: 1px solid #e2e8f0;">
                    <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                        <h3 class="title is-6 mb-0 has-text-dark">Directorio</h3>
                        <span class="tag is-info is-light is-rounded has-text-weight-bold" id="contadorFilas"><?= $totalClientes ?> registros</span>
                    </div>
                </div>

                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-vcentered mb-0" id="tablaClientes">
                        <thead>
                            <tr class="is-size-7 has-text-grey is-uppercase tracking-wider has-background-white-ter">
                                <th class="py-3 px-4">Cliente</th>
                                <th class="py-3 px-4">Documento</th>
                                <th class="py-3 px-4">Teléfono</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Dirección</th>
                                <th class="py-3 px-4 has-text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="is-size-6">
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <?php 
                                        $nombreCorto = strtoupper(substr(htmlspecialchars($c['nombre_razon_social'] ?? 'C'), 0, 2)); 
                                    ?>
                                    <tr class="fila-busqueda" data-estado="<?= (isset($c['estado']) && $c['estado'] == 1) ? 'activo' : 'inactivo' ?>">
                                        <td>
                                            <div class="is-flex is-align-items-center" style="gap: 14px;">
                                                <div class="is-flex is-align-items-center is-justify-content-center has-background-link-dark has-text-white has-text-weight-bold flex-shrink-0" style="width: 36px; height: 36px; border-radius: 10px; font-size: 0.8rem;">
                                                    <?= $nombreCorto ?>
                                                </div>
                                                <div>
                                                    <p class="has-text-weight-bold has-text-dark mb-0 nombre-cliente"><?= htmlspecialchars($c['nombre_razon_social'] ?? 'Sin Nombre') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="tag is-light has-text-weight-semibold font-mono doc-cliente">
                                                <?= htmlspecialchars(($c['tipo_documento'] ?? 'V') . '-' . ($c['num_documento'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="is-flex is-align-items-center gap-1 is-size-7 has-text-dark tlf-cliente">
                                                <span class="material-symbols-outlined has-text-grey is-size-6">call</span> 
                                                <?= htmlspecialchars($c['telefono'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="is-flex is-align-items-center gap-1 is-size-7 has-text-link">
                                                <span class="material-symbols-outlined has-text-grey is-size-6">mail</span> 
                                                <?= htmlspecialchars($c['email'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="is-flex is-align-items-center gap-1 is-size-7 has-text-dark" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <span class="material-symbols-outlined has-text-grey is-size-6">location_on</span> 
                                                <?= htmlspecialchars($c['direccion'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td class="has-text-right">
                                            <div class="buttons is-right mb-0">
                                                <!-- Botón Editar (Tradicional) -->
                                                <a href="index.php?controller=cliente&action=formeditarcliente&id=<?= $c['id_cliente'] ?? '' ?>" class="button is-small is-info is-light" title="Editar Cliente">
                                                    <span class="icon"><span class="material-symbols-outlined">edit</span></span>
                                                </a>
                                                <!-- Botón Eliminar/Inactivar (Preparado para AJAX) -->
                                                <button type="button" class="button is-small is-danger is-light btn-eliminar-ajax" data-id="<?= $c['id_cliente'] ?? '' ?>" data-nombre="<?= htmlspecialchars($c['nombre_razon_social'] ?? '') ?>" title="Inactivar Cliente">
                                                    <span class="icon"><span class="material-symbols-outlined">delete</span></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="has-text-centered py-6">
                                        <span class="material-symbols-outlined is-size-1 has-text-grey-light mb-3">person_off</span>
                                        <h4 class="title is-6 has-text-grey mb-1">No hay clientes registrados</h4>
                                        <p class="is-size-7 has-text-grey-light">Haz clic en "Añadir Cliente" para registrar el primero.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Librerías JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tu archivo JS Separado -->
    <script src="assets/js/cliente.js"></script>
</body>
</html>