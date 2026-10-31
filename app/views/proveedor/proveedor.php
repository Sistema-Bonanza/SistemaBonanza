<?php
// Garantizamos la lectura segura del arreglo de proveedores
$proveedores = $proveedores ?? [];
$totalProveedores = count($proveedores);

// Conteo de métricas
$countActivos = count(array_filter($proveedores, fn($p) => ($p['estado'] ?? 1) == 1));
$countInactivos = $totalProveedores - $countActivos;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores · Industrias Bonanza</title>

    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <!-- Interceptor de mensajes Flash PHP para SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">domain</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Gestión de Suministros
                                </span>
                                <span class="has-text-white-ter is-size-7">• Directorio de Proveedores</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Gestión de Proveedores</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Administra los proveedores del sistema. Solo los administradores pueden realizar cambios.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="index.php?controller=proveedor&action=formcrearproveedor" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">add</span></span>
                            <span>Añadir Proveedor</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TARJETAS METRICAS (KPIs) -->
            <!-- ========================================== -->
            <div class="columns is-variable is-3 mb-5">
                <div class="column">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between">
                            <div>
                                <p class="is-size-7 is-uppercase has-text-grey has-text-weight-bold">Total Proveedores</p>
                                <h3 class="title is-4 mb-0 mt-1 has-text-dark"><?= $totalProveedores ?></h3>
                                <p class="is-size-7 has-text-success has-text-weight-semibold mt-1">Directorio registrado</p>
                            </div>
                            <div class="has-background-info-light has-text-link p-3" style="border-radius: 10px;">
                                <span class="material-symbols-outlined">badge</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between">
                            <div>
                                <p class="is-size-7 is-uppercase has-text-grey has-text-weight-bold">Activos</p>
                                <h3 class="title is-4 mb-0 mt-1 has-text-dark"><?= $countActivos ?></h3>
                                <p class="is-size-7 has-text-link has-text-weight-semibold mt-1">Disponibles para compras</p>
                            </div>
                            <div class="has-background-success-light has-text-success p-3" style="border-radius: 10px;">
                                <span class="material-symbols-outlined">check_circle</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between">
                            <div>
                                <p class="is-size-7 is-uppercase has-text-grey has-text-weight-bold">Inactivos</p>
                                <h3 class="title is-4 mb-0 mt-1 has-text-dark"><?= $countInactivos ?></h3>
                                <p class="is-size-7 has-text-danger has-text-weight-semibold mt-1">Inhabilitados</p>
                            </div>
                            <div class="has-background-danger-light has-text-danger p-3" style="border-radius: 10px;">
                                <span class="material-symbols-outlined">block</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                        <div class="is-flex is-align-items-center is-justify-content-space-between">
                            <div>
                                <p class="is-size-7 is-uppercase has-text-grey has-text-weight-bold">Integridad RIF</p>
                                <h3 class="title is-4 mb-0 mt-1 has-text-success">100%</h3>
                                <p class="is-size-7 has-text-grey mt-1">Auditado y válido</p>
                            </div>
                            <div class="has-background-warning-light has-text-warning-dark p-3" style="border-radius: 10px;">
                                <span class="material-symbols-outlined">verified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- BARRA DE BÚSQUEDA Y FILTROS -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-4 mb-5 is-flex is-align-items-center is-justify-content-space-between flex-wrap" style="border: 1px solid #e2e8f0; border-radius: 12px; background: white; gap: 1.5rem;">
                
                <!-- Buscador (Máximo 380px) -->
                <div class="control has-icons-left" style="width: 100%; max-width: 380px; flex-shrink: 0;">
                    <input class="input is-link is-light" type="text" id="buscadorProveedores" placeholder="Buscar por razón social, RIF, contacto..." style="border-radius: 8px;">
                    <span class="icon is-small is-left has-text-grey"><span class="material-symbols-outlined">search</span></span>
                </div>

                <!-- Botones de Filtro -->
                <div class="is-flex is-align-items-center flex-wrap" style="gap: 1rem;">
                    <div class="buttons has-addons mb-0" id="filtrosEstado">
                        <button class="button is-small is-link is-selected btn-filtro" data-filtro="todos">
                            Todas <span class="ml-1 tag is-white is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $totalProveedores ?></span>
                        </button>
                        <button class="button is-small is-light btn-filtro" data-filtro="activos">
                            Activos <span class="ml-1 tag is-info is-light is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $countActivos ?></span>
                        </button>
                        <button class="button is-small is-light btn-filtro" data-filtro="inactivos">
                            Inactivos <span class="ml-1 tag is-info is-light is-rounded ml-2" style="height: 1.25em; padding: 0 0.4em; font-size: 0.65rem;"><?= $countInactivos ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TABLA DE PROVEEDORES -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white;">
                <div class="p-4 is-flex is-align-items-center is-justify-content-space-between" style="border-bottom: 1px solid #f1f5f9; background-color: #f8fafc;">
                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                        <span class="material-symbols-outlined has-text-link">domain</span>
                        <h2 class="title is-6 mb-0 has-text-dark">Proveedores del Sistema</h2>
                    </div>
                    <span class="tag is-info is-light is-rounded has-text-weight-bold" id="contadorFilas"><?= $totalProveedores ?> registros</span>
                </div>

                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable" id="tablaProveedores">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #edf2f7;">
                                <th class="is-size-7 has-text-grey is-uppercase py-3">Razón Social</th>
                                <th class="is-size-7 has-text-grey is-uppercase py-3">RIF</th>
                                <th class="is-size-7 has-text-grey is-uppercase py-3">Contacto</th>
                                <th class="is-size-7 has-text-grey is-uppercase py-3">Teléfono</th>
                                <th class="is-size-7 has-text-grey is-uppercase py-3 text-center">Estado</th>
                                <th class="is-size-7 has-text-grey is-uppercase py-3 text-right" style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="is-size-6">
                            <?php if (!empty($proveedores)): ?>
                                <?php foreach ($proveedores as $p): ?>
                                    <?php 
                                        $idProv = $p['id_proveedor'] ?? $p['id'] ?? '';
                                        $razonSocial = $p['razon_social'] ?? $p['nombre_razon_social'] ?? 'Sin Nombre';
                                        $rif = $p['rif'] ?? (isset($p['tipo_documento'], $p['num_documento']) ? $p['tipo_documento'].'-'.$p['num_documento'] : 'N/A');
                                        $contacto = $p['nombre_contacto'] ?? $p['contacto_directo'] ?? $p['contacto'] ?? 'N/A';
                                        $telefono = $p['telefono'] ?? 'N/A';
                                        $esActivo = ($p['estado'] ?? 1) == 1;

                                        $inicial = strtoupper(substr(htmlspecialchars($razonSocial), 0, 1));
                                    ?>
                                    <tr class="fila-busqueda" data-estado="<?= $esActivo ? 'activo' : 'inactivo' ?>">
                                        <!-- Razón Social + Icono Avatar -->
                                        <td>
                                            <div class="is-flex is-align-items-center" style="gap: 14px;">
                                                <div class="is-flex is-align-items-center is-justify-content-center has-background-link-dark has-text-white has-text-weight-bold flex-shrink-0" style="width: 36px; height: 36px; border-radius: 10px; font-size: 0.8rem;">
                                                    <?= $inicial ?>
                                                </div>
                                                <div>
                                                    <p class="has-text-weight-bold has-text-dark mb-0 nombre-proveedor"><?= htmlspecialchars($razonSocial) ?></p>
                                                    <p class="is-size-7 has-text-grey mb-0"><?= htmlspecialchars($p['direccion'] ?? '') ?></p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- RIF -->
                                        <td class="is-vcentered">
                                            <span class="tag is-light has-text-weight-semibold font-mono doc-proveedor">
                                                <?= htmlspecialchars($rif) ?>
                                            </span>
                                        </td>

                                        <!-- Contacto -->
                                        <td class="is-vcentered">
                                            <div class="is-flex is-align-items-center" style="gap: 6px;">
                                                <span class="material-symbols-outlined has-text-grey is-size-6">person</span>
                                                <span class="is-size-7 has-text-dark contacto-proveedor"><?= htmlspecialchars($contacto) ?></span>
                                            </div>
                                        </td>

                                        <!-- Teléfono -->
                                        <td class="is-vcentered">
                                            <div class="is-flex is-align-items-center" style="gap: 6px;">
                                                <span class="material-symbols-outlined has-text-grey is-size-6">call</span>
                                                <span class="is-size-7 has-text-dark font-mono tlf-proveedor"><?= htmlspecialchars($telefono) ?></span>
                                            </div>
                                        </td>

                                        <!-- Estado -->
                                        <td class="is-vcentered text-center" style="text-align: center;">
                                            <span class="tag is-rounded <?= $esActivo ? 'is-success' : 'is-danger' ?> is-light has-text-weight-semibold">
                                                <?= $esActivo ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="is-vcentered text-right" style="text-align: right;">
                                            <div class="buttons is-right mb-0" style="gap: 6px; justify-content: flex-end;">
                                                
                                                <!-- Editar -->
                                                <a href="<?= urlAccion('proveedor', 'formedit') ?>&id=<?= $idProv ?>" class="button is-small is-link is-light" title="Editar proveedor">
                                                    <span class="icon"><span class="material-symbols-outlined">edit</span></span>
                                                </a>

                                                <!-- Soft Delete / Reactivación en 1 Clic -->
                                                <button type="button" 
                                                        class="button is-small <?= $esActivo ? 'is-danger' : 'is-success' ?> is-light btn-toggle-estado" 
                                                        data-nombre="<?= htmlspecialchars($razonSocial) ?>" 
                                                        data-activo="<?= $esActivo ? '1' : '0' ?>"
                                                        data-url="<?= urlAccion('proveedor', 'cambiarEstado') ?>&id=<?= $idProv ?>"
                                                        title="<?= $esActivo ? 'Inactivar proveedor' : 'Reactivar proveedor' ?>">
                                                    <span class="icon">
                                                        <span class="material-symbols-outlined"><?= $esActivo ? 'block' : 'check_circle' ?></span>
                                                    </span>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="has-text-centered py-6 has-text-grey">
                                        <span class="material-symbols-outlined is-size-1 has-text-grey-light">domain_disabled</span>
                                        <p class="mt-2 font-medium">No hay proveedores registrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/proveedor.js"></script>
</body>
</html>