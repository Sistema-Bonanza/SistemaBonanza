<?php
// Variables que entrega PrecioController::historial():
//   $registros, $catalogo, $filtros, $filtrosUrl, $pagina, $totalPaginas, $total

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/** Muestra la variación porcentual con mayor contraste y claridad. */
function mostrarVariacion($anterior, $nuevo): string
{
    $variacion = variacionPorcentual($anterior, $nuevo);
    if ($variacion === null) {
        return '<span class="has-text-grey-light">—</span>';
    }
    if (abs($variacion) < 0.005) {
        return '<span class="tag is-light is-rounded is-small has-text-weight-semibold">0,00 %</span>';
    }
    $isPositivo = $variacion > 0;
    $claseTag = $isPositivo ? 'is-success' : 'is-danger';
    $signo = $isPositivo ? '▲ +' : '▼ ';
    return '<span class="tag ' . $claseTag . ' is-rounded is-small has-text-weight-bold">' . $signo . esc(number_format($variacion, 2, ',', '.')) . ' %</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Precios · Industrias Bonanza</title>

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
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f1f5f9; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <!-- Mensajes Flash ocultos para SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO (Alto contraste) -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative; box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white" style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-2">history</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.2); color: white !important;">
                                    Auditoría y Trazabilidad
                                </span>
                                <span class="has-text-white is-size-7 font-weight-medium">• Finanzas y Comercial</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Historial de Precios</h1>
                            <p class="has-text-white-bis is-size-7 mt-1" style="max-width: 700px; opacity: 0.95;">
                                Auditoría de cada cambio de costo y de precio de venta: qué cambió, cuándo y quién lo hizo.
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-white has-text-weight-bold" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver a la lista de precios</span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- TARJETA DE FILTROS (Alto contraste) -->
            <!-- ========================================== -->
            <div class="box is-shadowless mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white;">
                <div class="is-flex is-align-items-center is-justify-content-space-between mb-4 pb-3" style="border-bottom: 2px solid #f1f5f9;">
                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-background-link-light has-text-link" style="width: 32px; height: 32px; border-radius: 8px;">
                            <span class="material-symbols-outlined is-size-6">filter_alt</span>
                        </div>
                        <h3 class="title is-6 mb-0 has-text-dark has-text-weight-bold">Cambios registrados</h3>
                    </div>
                    <div class="is-flex is-align-items-center" style="gap: 6px;">
                        <span class="is-size-7 has-text-weight-semibold has-text-grey-dark">Total de eventos:</span>
                        <span class="tag is-link is-rounded has-text-weight-bold"><?= (int) $total ?> registros</span>
                    </div>
                </div>

                <form method="GET" action="index.php">
                    <input type="hidden" name="controller" value="precio">
                    <input type="hidden" name="action" value="historial">

                    <div class="columns is-variable is-3 is-multiline">
                        <!-- Producto -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="id_producto">Producto auditado</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="id_producto" name="id_producto" style="border-color: #94a3b8;">
                                            <option value="">Todos los productos</option>
                                            <?php foreach ($catalogo as $p): ?>
                                                <option value="<?= (int) $p['id_producto'] ?>"
                                                    <?= ((int) ($filtros['id_producto'] ?? 0) === (int) $p['id_producto']) ? 'selected' : '' ?>>
                                                    <?= esc($p['codigo'] . ' · ' . $p['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha Desde -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="desde">Desde</label>
                                <div class="control has-icons-left">
                                    <input type="date" id="desde" name="desde" class="input" style="border-color: #94a3b8;"
                                           max="<?= esc(date('Y-m-d')) ?>" value="<?= esc($filtros['desde'] ?? '') ?>">
                                    <span class="icon is-small is-left"><span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="hasta">Hasta</label>
                                <div class="control has-icons-left">
                                    <input type="date" id="hasta" name="hasta" class="input" style="border-color: #94a3b8;"
                                           max="<?= esc(date('Y-m-d')) ?>" value="<?= esc($filtros['hasta'] ?? '') ?>">
                                    <span class="icon is-small is-left"><span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="is-flex is-justify-content-flex-end mt-3" style="gap: 10px;">
                        <a href="<?= esc(urlAccion('precio', 'historial')) ?>" class="button is-light is-small has-text-weight-semibold" style="border: 1px solid #94a3b8;">Limpiar</a>
                        <button type="submit" class="button is-link is-small has-text-weight-bold">
                            <span class="icon"><span class="material-symbols-outlined" style="font-size: 16px;">search</span></span>
                            <span>Filtrar</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- TABLA DE HISTORIAL / BITÁCORA -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; background: white;">
                
                <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="background-color: #1e293b; color: white;">
                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                        <span class="tag is-link is-rounded is-small"></span>
                        <h3 class="title is-6 mb-0 has-text-white has-text-weight-bold">Bitácora de Precios Registrada</h3>
                    </div>
                    <span class="is-size-7 has-text-grey-light">Moneda base: <strong class="has-text-white">USD ($)</strong></span>
                </div>

                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-vcentered mb-0 is-size-7">
                        <thead>
                            <tr style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                <th class="has-text-weight-bold has-text-dark">Fecha / Hora</th>
                                <th class="has-text-weight-bold has-text-dark">Producto</th>
                                <th class="has-text-weight-bold has-text-dark">Costo</th>
                                <th class="has-text-weight-bold has-text-dark">Precio de Venta</th>
                                <th class="has-text-weight-bold has-text-dark has-text-centered">Variación Venta</th>
                                <th class="has-text-weight-bold has-text-dark has-text-centered">Origen</th>
                                <th class="has-text-weight-bold has-text-dark">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($registros)): ?>
                                <?php foreach ($registros as $h): 
                                    $inicial = mb_strtoupper(mb_substr($h['nombre_producto'], 0, 1, 'UTF-8'), 'UTF-8');
                                ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <!-- Fecha -->
                                        <td class="font-mono">
                                            <div class="has-text-weight-bold has-text-dark"><?= esc(date('d/m/Y', strtotime($h['fecha']))) ?></div>
                                            <div class="is-size-7 has-text-grey"><?= esc(date('h:i A', strtotime($h['fecha']))) ?></div>
                                        </td>

                                        <!-- Producto -->
                                        <td>
                                            <div class="is-flex is-align-items-center" style="gap: 10px;">
                                                <div class="is-flex is-align-items-center is-justify-content-center has-text-weight-bold" style="width: 34px; height: 34px; background: #e0f2fe; color: #0369a1; border-radius: 8px; font-size: 13px; border: 1px solid #bae6fd;">
                                                    <?= esc($inicial) ?>
                                                </div>
                                                <div>
                                                    <div class="has-text-weight-bold has-text-dark"><?= esc($h['nombre_producto']) ?></div>
                                                    <span class="tag is-dark is-rounded is-small font-mono mt-1"><?= esc($h['codigo']) ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Costo -->
                                        <td>
                                            <?php if ($h['precio_compra_anterior'] === $h['precio_compra_nuevo']): ?>
                                                <span class="tag is-light is-rounded is-small font-mono" style="background: #e2e8f0; color: #334155; font-weight: 600;">
                                                    <?= esc(dinero($h['precio_compra_nuevo'])) ?> <span class="has-text-grey">(sin cambio)</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="has-text-grey-dark"><?= esc(dinero($h['precio_compra_anterior'])) ?></span> 
                                                <span class="has-text-weight-bold mx-1" style="color: #64748b;">→</span> 
                                                <strong class="has-text-dark"><?= esc(dinero($h['precio_compra_nuevo'])) ?></strong>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Precio de venta -->
                                        <td class="font-mono">
                                            <?php if ($h['precio_venta_anterior'] === $h['precio_venta_nuevo']): ?>
                                                <span class="has-text-dark"><?= esc(dinero($h['precio_venta_nuevo'])) ?> <small class="has-text-grey">(sin cambio)</small></span>
                                            <?php else: ?>
                                                <span class="has-text-grey" style="text-decoration: line-through;"><?= esc(dinero($h['precio_venta_anterior'])) ?></span> 
                                                <span class="has-text-weight-bold mx-1" style="color: #64748b;">→</span> 
                                                <strong class="has-text-link is-size-6" style="color: #1d4ed8;"><?= esc(dinero($h['precio_venta_nuevo'])) ?></strong>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Variación -->
                                        <td class="has-text-centered"><?= mostrarVariacion($h['precio_venta_anterior'], $h['precio_venta_nuevo']) ?></td>

                                        <!-- Origen -->
                                        <td class="has-text-centered">
                                            <?php if (!empty($h['id_compra'])): ?>
                                                <span class="tag is-info is-rounded is-small has-text-weight-semibold">Compra #<?= esc($h['numero_factura'] ?? $h['id_compra']) ?></span>
                                            <?php else: ?>
                                                <span class="tag is-light is-rounded is-small has-text-weight-semibold" style="background: #e2e8f0; color: #1e293b;">Ajuste manual</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Usuario -->
                                        <td>
                                            <div class="is-flex is-align-items-center" style="gap: 6px;">
                                                <div class="is-flex is-align-items-center is-justify-content-center has-background-link has-text-white font-bold" style="width: 24px; height: 24px; border-radius: 50%; font-size: 10px;">
                                                    AD
                                                </div>
                                                <span class="has-text-weight-bold has-text-dark"><?= esc($h['nombre_usuario']) ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="has-text-centered py-6">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-2">event_busy</span>
                                        <p class="has-text-weight-bold has-text-dark">No hay cambios registrados</p>
                                        <p class="is-size-7 has-text-grey">Aún no se ha modificado ningún precio con estos filtros.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ========================================== -->
                <!-- PAGINACIÓN -->
                <!-- ========================================== -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="border-top: 2px solid #e2e8f0; background-color: #f8fafc;">
                        <span class="is-size-7 has-text-weight-semibold has-text-dark">Mostrando <strong><?= count($registros) ?></strong> de <strong><?= (int) $total ?></strong> cambios históricos registrados</span>
                        
                        <div class="is-flex is-align-items-center" style="gap: 8px;">
                            <span class="tag is-dark font-mono has-text-weight-bold">Página <?= (int) $pagina ?> de <?= (int) $totalPaginas ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="border-top: 2px solid #e2e8f0; background-color: #f8fafc;">
                        <span class="is-size-7 has-text-weight-semibold has-text-dark">Mostrando <strong><?= (int) $total ?></strong> de <strong><?= (int) $total ?></strong> cambios históricos registrados</span>
                        <span class="tag is-dark font-mono has-text-weight-bold">Página 1 de 1</span>
                    </div>
                <?php endif; ?>

            </div>

            <!-- ========================================== -->
            <!-- TARJETAS DE RESUMEN OPERATIVO (Inferiores con contraste) -->
            <!-- ========================================== -->
            <div class="columns is-variable is-4 mb-5">
                <!-- Card 1 -->
                <div class="column is-4">
                    <div class="box is-shadowless p-5 is-flex" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; gap: 15px; border-left: 5px solid #10b981;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-background-success-light has-text-success" style="width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <div>
                            <span class="is-size-7 has-text-grey-dark is-uppercase has-text-weight-bold tracking-wide">Precio Actual de Venta</span>
                            <div class="title is-4 has-text-dark mt-1 mb-1 font-weight-black">
                                <?php 
                                $precioActual = !empty($registros) ? $registros[0]['precio_venta_nuevo'] : 0;
                                echo esc(dinero($precioActual)); 
                                ?> USD
                            </div>
                            <p class="is-size-7 has-text-success-dark has-text-weight-bold">Última tarifa registrada en sistema</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="column is-4">
                    <div class="box is-shadowless p-5 is-flex" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; gap: 15px; border-left: 5px solid #2563eb;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-background-info-light has-text-info" style="width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;">
                            <span class="material-symbols-outlined">trending_up</span>
                        </div>
                        <div>
                            <span class="is-size-7 has-text-grey-dark is-uppercase has-text-weight-bold tracking-wide">Modificaciones Recientes</span>
                            <div class="title is-4 has-text-dark mt-1 mb-1 font-weight-black"><?= (int) $total ?> Cambios</div>
                            <p class="is-size-7 has-text-grey-dark has-text-weight-semibold">En el periodo de auditoría actual</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="column is-4">
                    <div class="box is-shadowless p-5 is-flex" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; gap: 15px; border-left: 5px solid #4338ca;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; background: #e0e7ff; color: #4338ca; flex-shrink: 0;">
                            <span class="material-symbols-outlined">verified_user</span>
                        </div>
                        <div>
                            <span class="is-size-7 has-text-grey-dark is-uppercase has-text-weight-bold tracking-wide">Auditoría Conforme</span>
                            <div class="title is-4 has-text-dark mt-1 mb-1 font-weight-black">100 % Firmado</div>
                            <p class="is-size-7 has-text-weight-bold" style="color: #4338ca;">Todas las transacciones autorizadas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- ACCIONES INFERIORES -->
            <!-- ========================================== -->
            <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 15px;">
                <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-dark has-text-weight-bold" style="border-radius: 10px;">
                    <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                    <span>Volver a la lista de precios</span>
                </a>

                <div class="is-flex" style="gap: 10px;">
                    <button class="button is-white has-text-weight-semibold" style="border: 1px solid #94a3b8; border-radius: 10px;" type="button" onclick="window.print();">
                        <span class="icon has-text-danger"><span class="material-symbols-outlined">print</span></span>
                        <span>Imprimir Bitácora (PDF)</span>
                    </button>
                    <button class="button is-white has-text-weight-semibold" style="border: 1px solid #94a3b8; border-radius: 10px;" type="button">
                        <span class="icon has-text-success"><span class="material-symbols-outlined">table_view</span></span>
                        <span>Exportar Historial (Excel)</span>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- Librería SweetAlert2 y Script JS del Módulo Historial -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/modulos/historialPrecios.js?v=<?= time() ?>"></script>
</body>
</html>