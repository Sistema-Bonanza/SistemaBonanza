<?php
// Variables que entrega ReporteController::cierreCaja():
// $cierre, $dia, $hoy, $esHoy, $diaAnterior, $diaSiguiente, $urlCsv,
// $cerrado (¿ya se efectuó el cierre de ese día?), $guardado (el cierre guardado, o []),
// $hayVentas (¿hubo ventas? sin ventas no hay nada que cerrar), $efectivoSistema

// 1. Respaldamos las variables que usaremos en nuestro banner
$linkCsv = $urlCsv ?? null;
$textoFecha = 'Fecha: ' . formatoFecha($dia) . ($esHoy ? ' (hoy)' : '');

// 2. Apagamos los "gatillos" para que _inicio.php NO genere su barra de botones
$urlCsv             = null;  // Oculta el botón "Exportar CSV" de arriba
$subtituloImpresion = 'Cierre de caja del ' . formatoFecha($dia)
    . ($cerrado ? ' · Cierre efectuado el ' . formatoFecha($guardado['creado_en'], true) . ' por ' . $guardado['username'] : '');  // texto del papel al imprimir
$esMenu             = true;  // Oculta el botón "<- Todos los reportes" de arriba
$tituloPagina       = '';
$descripcionPagina  = '';

// 3. Cargamos la plantilla base limpia
require __DIR__ . '/_inicio.php';

// 4. Restauramos la URL del CSV para usarla dentro de nuestro banner azul
$urlCsv = $linkCsv;
?>

<!-- Estilos específicos para vista en pantalla e impresión limpia -->
<style type="text/css">
    @media print {
        aside, .no-print, .buttons, .navbar, footer {
            display: none !important;
        }
        main, .container, body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .box {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
    }
</style>

<!-- ========================================== -->
<!-- BANNER HERO ESTANDARIZADO (REPORTE CAJA) -->
<!-- ========================================== -->
<div class="box mb-5 no-print" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                <span class="material-symbols-outlined is-size-3 has-text-white">point_of_sale</span>
            </div>
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0">Cierre de Caja Diario</h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> <?= esc($textoFecha) ?>
                    </span>
                    <?php if ($cerrado): ?>
                        <span class="tag is-dark is-rounded has-text-weight-bold is-small">
                            <span class="material-symbols-outlined is-size-6 mr-1">lock</span> Cierre efectuado
                        </span>
                    <?php endif; ?>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                    Resumen integral de lo facturado, cobrado y liquidado durante la jornada por forma de pago, desglose tributario de IVA y conciliación en gaveta.
                </p>
            </div>
        </div>
        <!-- Botones de Acción Superiores (Único bloque visible) -->
        <div class="buttons mb-0">
            <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc(urlAccion('reporte', 'historialCierres')) ?>">
                <span class="icon"><span class="material-symbols-outlined">history</span></span>
                <span>Historial de cierres</span>
            </a>
            <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc(urlAccion('reporte', 'index')) ?>">
                <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                <span>Todos los reportes</span>
            </a>
            <?php if (!empty($urlCsv)): ?>
                <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc($urlCsv) ?>">
                    <span class="icon"><span class="material-symbols-outlined">download</span></span>
                    <span>Exportar CSV</span>
                </a>
            <?php endif; ?>
            <button class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;" type="button" onclick="window.print()">
                <span class="icon"><span class="material-symbols-outlined">print</span></span>
                <span>Imprimir Cierre</span>
            </button>
        </div>
    
    </div>
</div>

<!-- ========================================== -->
<!-- 2. BARRA DE FILTRADO DE FECHA -->
<!-- ========================================== -->
<?php if (!$cerrado): ?>
<div class="box is-shadowless p-4 mb-5 no-print" style="background: white; border: 1px solid #cbd5e1; border-radius: 12px;">
    <form method="get" action="index.php" class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap" style="gap: 1rem;">
        <input type="hidden" name="controller" value="reporte">
        <input type="hidden" name="action" value="cierreCaja">

        <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
            <label for="filtroFecha" class="is-size-7 has-text-weight-bold is-uppercase has-text-grey-dark">Día del cierre:</label>
            <div class="control">
                <input class="input is-small font-weight-bold" type="date" id="filtroFecha" name="fecha" max="<?= esc($hoy) ?>" value="<?= esc($dia) ?>" required style="border-radius: 6px; border-color: #cbd5e1;">
            </div>
            <button type="submit" class="button is-link is-small font-weight-bold" style="border-radius: 6px;">
                <span class="icon"><i class="fas fa-calendar-check"></i></span>
                <span>Ver cierre</span>
            </button>
        </div>

        <div class="buttons are-small mb-0">
            <a href="<?= esc(urlAccion('reporte', 'cierreCaja', ['fecha' => $diaAnterior])) ?>" class="button is-light">
                <span class="icon"><i class="fas fa-chevron-left"></i></span>
                <span>Día anterior</span>
            </a>
            <?php if ($diaSiguiente !== null): ?>
                <a href="<?= esc(urlAccion('reporte', 'cierreCaja', ['fecha' => $diaSiguiente])) ?>" class="button is-light">
                    <span>Día siguiente</span>
                    <span class="icon"><i class="fas fa-chevron-right"></i></span>
                </a>
            <?php endif; ?>
            <?php if (!$esHoy): ?>
                <a href="<?= esc(urlAccion('reporte', 'cierreCaja')) ?>" class="button is-link is-light font-weight-bold">Ir a hoy</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- 3. NOTIFICACIONES Y ADVERTENCIAS AUDITORÍA -->
<!-- ========================================== -->
<?php if ($cerrado): ?>
    <div class="notification is-dark is-light mb-5 no-print" style="border: 1px solid #cbd5e1; border-radius: 10px;">
        <span class="icon mr-1"><i class="fas fa-lock"></i></span>
        <strong>Cierre efectuado</strong> el <?= esc(formatoFecha($guardado['creado_en'], true)) ?> por <strong><?= esc($guardado['username']) ?></strong>.
        Este documento es de solo lectura: únicamente se puede imprimir.
    </div>
    <?php if (abs($cierre['total'] - (float) $guardado['total_vendido']) >= 0.005): ?>
        <div class="notification is-warning is-light mb-5" style="border: 1px solid #fde68a; border-radius: 10px;">
            <span class="icon mr-1"><i class="fas fa-exclamation-triangle"></i></span>
            <strong>Atención:</strong> al efectuarse el cierre el total vendido era <?= esc(dinero($guardado['total_vendido'])) ?>,
            pero las ventas actuales de ese día suman <?= esc(dinero($cierre['total'])) ?> (hubo cambios, por ejemplo anulaciones, después del cierre).
            El arqueo de abajo conserva los valores del momento del cierre.
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (abs($cierre['diferencia_pagos']) >= 0.005): ?>
    <div class="notification is-warning is-light mb-5" style="border: 1px solid #fde68a; border-radius: 10px;">
        <span class="icon mr-1"><i class="fas fa-exclamation-triangle"></i></span>
        <strong>Atención:</strong> La suma de las formas de pago (<?= esc(dinero($cierre['cobrado'])) ?>) 
        no coincide con el total vendido (<?= esc(dinero($cierre['total'])) ?>); 
        la diferencia es de <strong><?= esc(dinero(abs($cierre['diferencia_pagos']))) ?></strong>. 
        Revisa las operaciones en la tabla <code>venta_pagos</code>.
    </div>
<?php endif; ?>

<?php if ($cierre['anulaciones_previas']['cantidad'] > 0): ?>
    <div class="notification is-info is-light mb-5" style="border: 1px solid #bfdbfe; border-radius: 10px;">
        <span class="icon mr-1"><i class="fas fa-info-circle"></i></span>
        <strong>Anulaciones de ventas de días anteriores:</strong> Hoy se anularon <?= (int) $cierre['anulaciones_previas']['cantidad'] ?> 
        <?= $cierre['anulaciones_previas']['cantidad'] === 1 ? 'venta realizada' : 'ventas realizadas' ?> antes de esta fecha, por un monto de <strong><?= esc(dinero($cierre['anulaciones_previas']['total'])) ?></strong>.
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- 4. TARJETAS MÉTRICAS (KPIs DEL DÍA) -->
<!-- ========================================== -->
<div class="columns is-multiline is-variable is-4 mb-5">
    
    <!-- KPI 1: Total Vendido -->
    <div class="column is-4-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Total Vendido</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #eff6ff; color: #2563eb; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-dark">
                <?= esc(dinero($cierre['total'])) ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                <strong class="has-text-dark"><?= (int)$cierre['facturas'] ?> <?= $cierre['facturas'] === 1 ? 'factura' : 'facturas' ?></strong> · <?= esc(unidades($cierre['unidades'])) ?> unds.
            </div>
        </div>
    </div>

    <!-- KPI 2: Total Cobrado -->
    <div class="column is-4-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #059669; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Total Cobrado</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #ecfdf5; color: #059669; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-success">
                <?= esc(dinero($cierre['cobrado'])) ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                <strong class="has-text-success">Ingreso neto en caja</strong>
            </div>
        </div>
    </div>

    <!-- KPI 3: Ventas Anuladas -->
    <div class="column is-4-desktop is-6-tablet">
        <div class="box is-shadowless h-100 p-4" style="border: 1px solid #cbd5e1; border-left: 4px solid #e11d48; border-radius: 12px; background: white;">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey">Ventas Anuladas</span>
                <div class="p-2 is-flex is-align-items-center is-justify-content-center" style="background: #fff1f2; color: #e11d48; border-radius: 8px; width: 36px; height: 36px;">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <h2 class="title is-3 mb-2 font-mono font-weight-bold has-text-dark">
                <?= (int) $cierre['anuladas']['cantidad'] ?>
            </h2>
            <div class="pt-2 is-size-7 has-text-grey" style="border-top: 1px solid #f1f5f9;">
                <?php if ($cierre['anuladas']['cantidad'] > 0): ?>
                    <strong class="has-text-danger">Por <?= esc(dinero($cierre['anuladas']['total'])) ?></strong>
                <?php else: ?>
                    <strong class="has-text-success">Sin anulaciones el día de hoy</strong>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- 5. DESGLOSE FINANCIERO Y TRIBUTARIO -->
<!-- ========================================== -->
<div class="columns is-multiline is-variable is-4 mb-5">
    
    <!-- Columna Izquierda: Formas de Pago -->
    <div class="column is-7-desktop">
        <div class="box is-shadowless p-0" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
            <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">Detalle por Forma de Pago</h3>
                <span class="tag is-info is-light font-mono font-weight-bold">Desglose de Caja</span>
            </div>

            <div class="table-container">
                <table class="table is-fullwidth is-hoverable mb-0 text-sm">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th class="is-size-7 font-weight-bold is-uppercase">Forma de Pago</th>
                            <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Facturas</th>
                            <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (METODOS_PAGO as $clave => $etiqueta): ?>
                            <?php $p = $cierre['pagos'][$clave] ?? ['facturas' => 0, 'monto' => 0.0]; ?>
                            <tr class="<?= $p['facturas'] === 0 ? 'has-background-light' : '' ?>">
                                <td><strong class="has-text-dark"><?= esc($etiqueta) ?></strong></td>
                                <td class="has-text-centered font-weight-semibold"><?= (int) $p['facturas'] ?></td>
                                <td class="has-text-right font-mono font-weight-bold"><?= esc(dinero($p['monto'])) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Fila Total Cobrado -->
                        <tr style="background: #f0fdf4; border-top: 2px solid #a7f3d0;">
                            <td class="font-weight-bold has-text-success-dark">TOTAL COBRADO (Entró a caja)</td>
                            <td class="has-text-centered font-weight-bold">-</td>
                            <td class="has-text-right font-mono title is-5 has-text-success mb-0 font-weight-bold"><?= esc(dinero($cierre['cobrado'])) ?></td>
                        </tr>

                        <!-- Fila Total Vendido -->
                        <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                            <td class="title is-6 mb-0 font-weight-bold has-text-dark">TOTAL VENDIDO</td>
                            <td class="has-text-centered font-weight-bold"><?= (int) $cierre['facturas'] ?> facturas</td>
                            <td class="has-text-right font-mono title is-4 has-text-link mb-0 font-weight-bold"><?= esc(dinero($cierre['total'])) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 is-size-7 has-text-grey" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                * Una venta con pago mixto aparece en cada forma de pago usada por la parte correspondiente.
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Base Imponible e IVA + Ventas por Usuario -->
    <div class="column is-5-desktop">
        <!-- Base Imponible e IVA -->
        <div class="box is-shadowless p-0 mb-4" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
            <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">Base Imponible e IVA</h3>
                <span class="tag is-light font-mono">Fiscal</span>
            </div>
            <div class="p-5">
                <div class="is-flex is-justify-content-space-between mb-3">
                    <span class="is-size-7 has-text-grey font-weight-semibold">Subtotal (sin IVA)</span>
                    <span class="font-mono font-weight-bold has-text-dark"><?= esc(dinero($cierre['subtotal'])) ?></span>
                </div>
                <div class="is-flex is-justify-content-space-between mb-3">
                    <span class="is-size-7 has-text-grey font-weight-semibold">IVA</span>
                    <span class="font-mono font-weight-bold has-text-dark"><?= esc(dinero($cierre['iva'])) ?></span>
                </div>
                <div class="pt-3 is-flex is-justify-content-space-between is-align-items-center" style="border-top: 1px solid #e2e8f0;">
                    <span class="font-weight-bold has-text-dark">Total General</span>
                    <span class="title is-4 font-mono has-text-link mb-0 font-weight-bold"><?= esc(dinero($cierre['total'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Ventas por Cajero / Usuario -->
        <div class="box is-shadowless p-0" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
            <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">Ventas por Usuario</h3>
                <span class="tag is-success is-light font-mono font-weight-bold">Operadores</span>
            </div>
            <div class="table-container">
                <table class="table is-fullwidth mb-0 text-sm">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th class="is-size-7 font-weight-bold is-uppercase">Usuario</th>
                            <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Facturas</th>
                            <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cierre['por_usuario'])): ?>
                            <?php foreach ($cierre['por_usuario'] as $u): ?>
                                <tr>
                                    <td>
                                        <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                            <span class="tag is-link is-medium font-weight-bold"><?= esc(strtoupper(substr($u['username'], 0, 2))) ?></span>
                                            <div>
                                                <strong class="has-text-dark"><?= esc($u['username']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="has-text-centered font-weight-semibold"><?= (int) $u['facturas'] ?></td>
                                    <td class="has-text-right font-mono font-weight-bold"><?= esc(dinero($u['total_vendido'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="has-text-grey has-text-centered py-4">Sin ventas en este día.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- 6. TABLA DE VENTAS REGISTRADAS DEL DÍA -->
<!-- ========================================== -->
<div class="box is-shadowless p-0 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
    <div class="p-4 is-flex is-justify-content-space-between is-align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h3 class="title is-6 mb-0 font-weight-bold has-text-dark">
            Ventas del Día <span class="has-text-grey font-weight-normal"> (<?= count($cierre['ventas']) ?>)</span>
        </h3>
        <span class="tag is-link is-light font-weight-bold">Transacciones Registradas</span>
    </div>

    <div class="table-container">
        <table class="table is-fullwidth is-hoverable is-striped mb-0 text-sm">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th class="is-size-7 font-weight-bold is-uppercase">Hora</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Factura</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Cliente</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Usuario</th>
                    <th class="is-size-7 font-weight-bold is-uppercase">Forma de Pago</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-centered">Estado</th>
                    <th class="is-size-7 font-weight-bold is-uppercase has-text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cierre['ventas'])): ?>
                    <?php foreach ($cierre['ventas'] as $v): ?>
                        <?php $anulada = ((int) $v['estado'] === 0); ?>
                        <tr class="<?= $anulada ? 'has-background-danger-light' : '' ?>">
                            <td class="font-mono is-size-7"><?= esc(date('h:i A', strtotime($v['fecha_venta']))) ?></td>
                            <td class="font-mono font-weight-bold">
                                <a href="<?= esc(urlAccion('venta', 'verDetalleVenta', ['id' => $v['id_venta']])) ?>" class="has-text-link">
                                    <?= esc($v['numero_factura']) ?>
                                </a>
                            </td>
                            <td><strong class="has-text-dark"><?= esc($v['cliente']) ?></strong></td>
                            <td class="is-size-7"><?= esc($v['usuario']) ?></td>
                            <td>
                                <span class="tag is-light font-weight-bold">
                                    <?= esc(defined('TIPOS_PAGO_VENTA') && isset(TIPOS_PAGO_VENTA[$v['tipo_pago']]) ? TIPOS_PAGO_VENTA[$v['tipo_pago']] : $v['tipo_pago']) ?>
                                </span>
                            </td>
                            <td class="has-text-centered">
                                <?php if ($anulada): ?>
                                    <span class="tag is-danger font-weight-bold">ANULADA</span>
                                <?php else: ?>
                                    <span class="tag is-success font-weight-bold">REGISTRADA</span>
                                <?php endif; ?>
                            </td>
                            <td class="has-text-right font-mono font-weight-bold <?= $anulada ? 'has-text-danger' : 'has-text-dark' ?>">
                                <?= esc(dinero($v['total'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="has-text-centered has-text-grey py-5">No hubo ventas en esta fecha.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 7. CONTEO DE CAJA Y FIRMAS (AUDITORÍA FÍSICA) -->
<!-- ========================================== -->
<?php
    // Valores del arqueo: los guardados si el día ya se cerró; si no, los del sistema en vivo.
    $sistemaArqueo = $cerrado ? (float) $guardado['efectivo_sistema'] : $efectivoSistema;
    $contadoGuard  = $cerrado ? (float) $guardado['efectivo_contado'] : null;
    $difGuardada   = $cerrado ? (float) $guardado['diferencia'] : null;
    $claseDif = 'has-text-grey-dark';
    $textoDif = '';
    if ($cerrado) {
        if (abs($difGuardada) < 0.005)  { $claseDif = 'has-text-success';        $textoDif = 'La caja cuadra'; }
        elseif ($difGuardada > 0)       { $claseDif = 'has-text-warning-dark';   $textoDif = 'Sobrante'; }
        else                            { $claseDif = 'has-text-danger';         $textoDif = 'Faltante'; }
    }
?>
<div class="box is-shadowless p-5 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white;">
    <h3 class="title is-5 mb-1 font-weight-bold has-text-dark">Arqueo Físico de Caja y Firmas de Auditoría</h3>
    <p class="is-size-7 has-text-grey mb-4">Verificación cruzada entre el dinero físico auditado en gaveta y el total reportado en el sistema.</p>

    <?php if (!$cerrado && $hayVentas): ?>
    <form method="post" action="index.php?controller=reporte&amp;action=registrarCierre" id="formCierre">
        <?= campoCsrf() ?>
        <input type="hidden" name="fecha" value="<?= esc($dia) ?>">
    <?php endif; ?>

    <!-- Campos de Arqueo -->
    <div class="columns is-multiline mb-4">
        <div class="column is-4">
            <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <label for="efectivoContado" class="is-size-7 font-weight-bold is-uppercase has-text-grey-dark block mb-1">1. Efectivo Contado (Gaveta):</label>
                <?php if ($cerrado): ?>
                    <p class="title is-4 font-mono font-weight-bold has-text-dark mb-0"><?= esc(dinero($contadoGuard)) ?></p>
                <?php else: ?>
                    <div class="control has-icons-left">
                        <input class="input font-mono font-weight-bold" type="number" step="0.01" min="0" max="99999999.99" inputmode="decimal"
                               id="efectivoContado" name="efectivo_contado" placeholder="0.00" style="border-color: #cbd5e1;"
                               <?= $hayVentas ? 'required' : 'disabled' ?>>
                        <span class="icon is-small is-left"><i class="fas fa-dollar-sign"></i></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="column is-4">
            <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey-dark block mb-1">2. Cobrado en Efectivo (Sistema):</span>
                <p class="title is-4 font-mono font-weight-bold has-text-dark mb-0" id="efectivoSistemaTexto" data-centavos="<?= (int) round($sistemaArqueo * 100) ?>"><?= esc(dinero($sistemaArqueo)) ?></p>
                <p class="is-size-7 has-text-grey mt-1 mb-0">Solo pagos en efectivo (no incluye tarjeta, transferencia ni divisa).</p>
            </div>
        </div>
        <div class="column is-4">
            <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <span class="is-size-7 font-weight-bold is-uppercase has-text-grey-dark block mb-1">3. Diferencia (Contado - Sistema):</span>
                <?php if ($cerrado): ?>
                    <p class="title is-4 font-mono font-weight-bold <?= $claseDif ?> mb-0"><?= ($difGuardada > 0 ? '+' : ($difGuardada < 0 ? '-' : '')) . esc(dinero(abs($difGuardada))) ?></p>
                    <p class="is-size-7 has-text-weight-bold <?= $claseDif ?> mt-1 mb-0"><?= esc($textoDif) ?></p>
                <?php else: ?>
                    <p class="title is-4 font-mono font-weight-bold has-text-grey-dark mb-0" id="diferenciaCaja" aria-live="polite">$0,00</p>
                    <p class="is-size-7 has-text-grey mt-1 mb-0" id="diferenciaEstado">Escribe el efectivo contado para calcularla.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Observaciones -->
    <div class="field mb-5">
        <label for="observaciones" class="label is-size-7 is-uppercase">Observaciones del Cierre:</label>
        <?php if ($cerrado): ?>
            <p class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; white-space: pre-wrap; min-height: 3rem;"><?= ($guardado['observaciones'] ?? '') !== '' ? esc($guardado['observaciones']) : '—' ?></p>
        <?php else: ?>
            <div class="control">
                <textarea class="textarea is-small" id="observaciones" name="observaciones" rows="2" maxlength="500" placeholder="Escriba aquí si existió algún inconveniente con el punto de venta, ajustes de cambio..." style="border-color: #cbd5e1;" <?= $hayVentas ? '' : 'disabled' ?>></textarea>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$cerrado): ?>
        <!-- Botón de cierre (no se imprime) -->
        <div class="no-print mb-5">
            <?php if ($hayVentas): ?>
                <button type="submit" class="button is-link has-text-weight-bold" id="btnRealizarCierre" style="border-radius: 8px;">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <span>Realizar cierre de caja</span>
                </button>
                <p class="is-size-7 has-text-grey mt-2 mb-0">
                    Al realizar el cierre se guarda el arqueo, ya no podrá modificarse y se cerrará tu sesión.
                </p>
            <?php else: ?>
                <p class="is-size-7 has-text-grey mb-0"><i class="fas fa-info-circle mr-1"></i> No hubo ventas en esta fecha: no hay nada que cerrar.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Bloque de Firmas -->
    <div class="pt-5 mt-5" style="border-top: 2px dashed #cbd5e1;">
        <div class="columns is-mobile is-multiline">
            <div class="column is-6 has-text-centered">
                <div class="mb-2" style="border-bottom: 2px solid #64748b; width: 80%; margin: 40px auto 10px auto;"></div>
                <p class="is-size-7 font-weight-bold has-text-dark mb-0">Entregado por (Cajero / Operador)</p>
                <p class="is-size-7 has-text-grey mb-0">Firma autógrafa / Sello digital</p>
            </div>
            <div class="column is-6 has-text-centered">
                <div class="mb-2" style="border-bottom: 2px solid #64748b; width: 80%; margin: 40px auto 10px auto;"></div>
                <p class="is-size-7 font-weight-bold has-text-dark mb-0">Recibido y Verificado por (Supervisor / Auditor)</p>
                <p class="is-size-7 has-text-grey mb-0">Gerencia de Operaciones & Administración</p>
            </div>
        </div>
    </div>

    <?php if (!$cerrado && $hayVentas): ?>
    </form>
    <?php endif; ?>
</div>

<?php if (!$cerrado && $hayVentas): ?>
<script>
// Ecuación automática: Diferencia = Efectivo contado - Efectivo cobrado según el sistema.
// Se calcula en centavos enteros (evita errores de decimales como 0.1 + 0.2) y se actualiza al escribir.
(function () {
    var entrada  = document.getElementById('efectivoContado');
    var sistema  = document.getElementById('efectivoSistemaTexto');
    var salida   = document.getElementById('diferenciaCaja');
    var estado   = document.getElementById('diferenciaEstado');
    var formCierre = document.getElementById('formCierre');
    if (!entrada || !sistema || !salida) { return; }

    var centavosSistema = parseInt(sistema.getAttribute('data-centavos'), 10) || 0;
    var formato = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var clases = ['has-text-grey-dark', 'has-text-success', 'has-text-warning-dark', 'has-text-danger'];

    function dinero(centavos) {
        return (centavos < 0 ? '-' : '') + '$' + formato.format(Math.abs(centavos) / 100);
    }
    function pintar(clase, texto, nota) {
        clases.forEach(function (c) { salida.classList.remove(c); });
        salida.classList.add(clase);
        salida.textContent = texto;
        if (estado) { estado.textContent = nota; estado.className = 'is-size-7 mt-1 mb-0 ' + (clase === 'has-text-grey-dark' ? 'has-text-grey' : clase + ' has-text-weight-bold'); }
    }
    function calcular() {
        var texto = entrada.value.trim().replace(',', '.');
        if (texto === '' || isNaN(Number(texto))) {
            pintar('has-text-grey-dark', '$0,00', 'Escribe el efectivo contado para calcularla.');
            return null;
        }
        var diferencia = Math.round(Number(texto) * 100) - centavosSistema;
        if (diferencia === 0)      { pintar('has-text-success', dinero(0), 'La caja cuadra'); }
        else if (diferencia > 0)   { pintar('has-text-warning-dark', '+' + dinero(diferencia), 'Sobrante: hay más efectivo del que registra el sistema'); }
        else                       { pintar('has-text-danger', dinero(diferencia), 'Faltante: hay menos efectivo del que registra el sistema'); }
        return diferencia;
    }

    entrada.addEventListener('input', calcular);
    entrada.addEventListener('change', calcular);
    calcular();   // por si el navegador conserva el valor al recargar

    // Confirmación antes de cerrar (el cierre no se puede deshacer y termina la sesión).
    if (formCierre) {
        var enviando = false;
        formCierre.addEventListener('submit', function (e) {
            if (enviando) { e.preventDefault(); return; }
            var dif = calcular();
            var aviso = 'Vas a realizar el cierre de caja de este día.\n\n';
            if (dif !== null && dif !== 0) { aviso += 'La caja NO cuadra (' + (dif > 0 ? '+' : '') + dinero(dif) + ').\n\n'; }
            aviso += 'El cierre no se podrá modificar y tu sesión se cerrará. ¿Continuar?';
            if (!window.confirm(aviso)) { e.preventDefault(); return; }
            enviando = true;   // evita el doble envío (doble clic)
            var boton = document.getElementById('btnRealizarCierre');
            if (boton) { boton.classList.add('is-loading'); }
        });
    }
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/_fin.php'; ?>