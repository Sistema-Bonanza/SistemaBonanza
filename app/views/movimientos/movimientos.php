<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Operaciones · Bonanza</title>
    
    <!-- Fuentes y Recursos de Iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <!-- CSS del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- SIDEBAR ORIGINAL DE TU SISTEMA -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div id="caja-flash-oculta" style="display: none;">
    <?= mostrarFlash(); ?>
    </div>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        

        <!-- Contenedor con Padding Estandarizado (p-5) -->
        <div class="p-5" style="width: 100%;">

            <!-- ========================================== -->
            <!-- 1. BANNER PRINCIPAL (Estandarizado) -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #003899 0%, #004ac6 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">sync_alt</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Módulo de Movimientos</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Auditoría Kardex
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Auditoría general de ingresos y salidas del almacén central. Control estricto de trazabilidad física, inventario valorizado y comprobantes fiscales.
                            </p>
                        </div>
                    </div>

                    <!-- Botones Dinámicos de Acción -->
                    <div class="buttons mb-0">
                        <a href="<?= esc(urlAccion('movimiento', 'crearcompra')) ?>" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">add_circle</span></span>
                            <span>Registrar Entrada (Compra)</span>
                        </a>
                        <button class="button is-ghost has-text-white" disabled title="Próximamente" style="border-radius: 8px; background: rgba(255,255,255,0.15);">
                            <span class="icon"><span class="material-symbols-outlined">output</span></span>
                            <span>Registrar Salida / Venta</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 2. TARJETAS DE MÉTRICAS (KPIs) -->
            <!-- ========================================== -->
            <div class="columns is-multiline mb-4">
                
                <!-- KPI 1: Entradas -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 is-flex is-flex-direction-column" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #3e8ed0;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                            <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase">Entradas (Mes)</span>
                            <span class="tag is-info is-light is-rounded" style="font-size: 0.6rem;">Período Actual</span>
                        </div>
                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end mb-4">
                            <div>
                                <h2 class="title is-3 mb-1">1,250 <span class="is-size-6 has-text-grey has-text-weight-normal">unds.</span></h2>
                                <p class="is-size-7 has-text-info has-text-weight-bold is-flex is-align-items-center">
                                    <span class="material-symbols-outlined is-size-6 mr-1">trending_up</span> +$4,820.50 val.
                                </p>
                            </div>
                            <div class="has-background-info-light has-text-info is-flex is-align-items-center is-justify-content-center p-2" style="border-radius: 10px;">
                                <span class="material-symbols-outlined is-size-4">inventory_2</span>
                            </div>
                        </div>
                        <progress class="progress is-info is-small mb-0" value="74" max="100">74%</progress>
                    </div>
                </div>

                <!-- KPI 2: Salidas -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 is-flex is-flex-direction-column" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #48c774;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                            <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase">Salidas (Mes)</span>
                            <span class="tag is-success is-light is-rounded" style="font-size: 0.6rem;">Despachos</span>
                        </div>
                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end mb-4">
                            <div>
                                <h2 class="title is-3 mb-1">840 <span class="is-size-6 has-text-grey has-text-weight-normal">unds.</span></h2>
                                <p class="is-size-7 has-text-success has-text-weight-bold is-flex is-align-items-center">
                                    <span class="material-symbols-outlined is-size-6 mr-1">local_shipping</span> 67.2% rotación
                                </p>
                            </div>
                            <div class="has-background-success-light has-text-success is-flex is-align-items-center is-justify-content-center p-2" style="border-radius: 10px;">
                                <span class="material-symbols-outlined is-size-4">airport_shuttle</span>
                            </div>
                        </div>
                        <progress class="progress is-success is-small mb-0" value="58" max="100">58%</progress>
                    </div>
                </div>

                <!-- KPI 3: Stock Crítico -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 is-flex is-flex-direction-column" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #f14668;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                            <span class="is-size-7 has-text-danger has-text-weight-bold is-uppercase">Stock Crítico</span>
                            <span class="tag is-danger is-light is-rounded" style="font-size: 0.6rem;">Urgente</span>
                        </div>
                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end mb-4">
                            <div>
                                <h2 class="title is-3 mb-1 has-text-danger">8 <span class="is-size-6 has-text-danger has-text-weight-normal">SKUs</span></h2>
                                <p class="is-size-7 has-text-danger has-text-weight-bold is-flex is-align-items-center">
                                    <span class="material-symbols-outlined is-size-6 mr-1">warning</span> Requieren compra
                                </p>
                            </div>
                            <div class="has-background-danger-light has-text-danger is-flex is-align-items-center is-justify-content-center p-2" style="border-radius: 10px;">
                                <span class="material-symbols-outlined is-size-4">battery_alert</span>
                            </div>
                        </div>
                        <progress class="progress is-danger is-small mb-0" value="25" max="100">25%</progress>
                    </div>
                </div>

                <!-- KPI 4: Total Kardex -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 is-flex is-flex-direction-column" style="border: 1px solid #e2e8f0; border-radius: 12px; border-left: 4px solid #4a4a4a;">
                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                            <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase">Auditoría</span>
                            <span class="tag is-dark is-light">Total Kardex</span>
                        </div>
                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end mb-4">
                            <div>
                                <h2 class="title is-3 mb-1" id="total-registros-label"><?= !empty($compras) ? count($compras) : 0 ?></h2>
                                <p class="is-size-7 has-text-link has-text-weight-bold is-flex is-align-items-center">
                                    <span class="material-symbols-outlined is-size-6 mr-1">verified</span> 100% conciliado
                                </p>
                            </div>
                            <div class="has-background-link-light has-text-link is-flex is-align-items-center is-justify-content-center p-2" style="border-radius: 10px;">
                                <span class="material-symbols-outlined is-size-4">receipt_long</span>
                            </div>
                        </div>
                        <progress class="progress is-link is-small mb-0" value="100" max="100">100%</progress>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 3. BARRA DE FILTROS Y BÚSQUEDA -->
            <!-- ========================================== -->
            <div class="box is-shadowless mb-5 p-4" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                <div class="is-flex is-flex-wrap-wrap is-align-items-center is-justify-content-space-between" style="gap: 1rem;">
                    
                    <div class="control has-icons-left" style="flex-grow: 1; max-width: 400px;">
                        <input class="input" type="text" id="input-busqueda" placeholder="Buscar documento, entidad o usuario..." style="border-radius: 8px; background-color: #f8fafc;">
                        <span class="icon is-left has-text-grey-light"><span class="material-symbols-outlined">search</span></span>
                    </div>
                    
                    <div class="is-flex is-align-items-center is-flex-wrap-wrap" style="gap: 1rem;">
                        <div class="buttons has-addons mb-0">
                            <button class="button is-link is-selected btn-filtro" data-filtro="todas" style="border-radius: 6px 0 0 6px;">
                                <span class="icon is-small"><span class="material-symbols-outlined">list</span></span><span>Todas</span>
                            </button>
                            <button class="button btn-filtro" data-filtro="entradas">
                                <span class="icon is-small has-text-info"><span class="material-symbols-outlined">south_east</span></span><span>Entradas</span>
                            </button>
                            <button class="button btn-filtro" data-filtro="salidas" style="border-radius: 0 6px 6px 0;">
                                <span class="icon is-small has-text-success"><span class="material-symbols-outlined">north_east</span></span><span>Salidas</span>
                            </button>
                        </div>
                        
                        <div class="control">
                            <div class="select">
                                <select style="border-radius: 6px; background-color: #f8fafc;">
                                    <option>Rango: Últimos 30 días (Pronto)</option>
                                </select>
                            </div>
                        </div>
                        
                        <button class="button is-light" style="border-radius: 6px;">
                            <span class="icon"><span class="material-symbols-outlined">download</span></span>
                            <span>Exportar PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 4. TABLA DE OPERACIONES DINÁMICA -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <div class="p-4 is-flex is-align-items-center is-justify-content-space-between has-background-white-ter" style="border-bottom: 1px solid #e2e8f0;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <h2 class="title is-5 mb-0 has-text-dark">Resumen de Operaciones</h2>
                        <span class="tag is-white" style="border: 1px solid #cbd5e1;">Auditoría Permanente</span>
                    </div>
                </div>
                
                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-striped mb-0">
                        <thead>
                            <tr class="is-size-7 has-text-grey is-uppercase" style="background-color: #f8fafc;">
                                <th class="py-3 px-4">Fecha / Hora</th>
                                <th class="py-3 px-4">Operación</th>
                                <th class="py-3 px-4">Documento / Ref.</th>
                                <th class="py-3 px-4">Entidad / Usuario</th>
                                <th class="py-3 px-4 has-text-right">Monto Total</th>
                                <th class="py-3 px-4 has-text-centered">Estado Kardex</th>
                                <th class="py-3 px-4 has-text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="is-size-6" id="tbody-movimientos">
                            <?php if(!empty($compras)): ?>
                                <?php foreach ($compras as $compra): ?>
                                    <?php require __DIR__ . '/filas_ajax.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="has-text-centered py-6">
                                        <div class="is-flex is-flex-direction-column is-align-items-center">
                                            <span class="material-symbols-outlined has-text-grey-light mb-3" style="font-size: 3rem;">inventory_2</span>
                                            <p class="has-text-grey has-text-weight-bold is-size-5">No hay operaciones registradas.</p>
                                            <p class="has-text-grey-light is-size-7 mt-1">Haz clic en "Registrar Compra" para comenzar a poblar el Kardex.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación Footer -->
                <div class="p-4 has-background-white-ter is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="border-top: 1px solid #e2e8f0; gap: 1rem;">
                    <p class="is-size-7 has-text-grey">
                        Auditoría generada en tiempo real.
                    </p>
                    <nav id="contenedor-paginacion" class="pagination is-small is-right mb-0" role="navigation" aria-label="pagination">
                        <!-- JS reconstruirá los enlaces reales -->
                    </nav>
                </div>
            </div>

        </div> <!-- Fin de contenedor p-5 -->

        <!-- Inyectar Token de Seguridad -->
        <script>
            window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
        </script>
        
       <!-- 1. Cargamos SweetAlert2 primero -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- 2. Cargamos tu archivo JavaScript de la tabla -->
        <script src="assets/js/movimiento.js?v=<?= time() ?>"></script>

    <!-- 3. El interceptor inteligente de alertas -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Buscamos directamente la caja oculta por su ID exacto
            const cajaFlash = document.getElementById('caja-flash-oculta');
            
            // Si la caja existe y tiene algún texto adentro (PHP imprimió un mensaje)
            if (cajaFlash && cajaFlash.innerText.trim() !== '') {
                
                // Capturamos el mensaje exacto
                let textoMensaje = cajaFlash.innerText.trim();
                
                // Disparamos el SweetAlert moderno
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: textoMensaje,
                    confirmButtonColor: '#2563eb',
                    timer: 4000,
                    timerProgressBar: true
                });
                
                // Vaciamos la caja por precaución
                cajaFlash.innerHTML = '';
            }
        });
    </script>
    </main>
</body>
</html>