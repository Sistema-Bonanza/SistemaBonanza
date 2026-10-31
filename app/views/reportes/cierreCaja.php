<?php
// Variables que entrega ReporteController::cierreCaja():
//   $cierre (ver ReporteModel::cierreDeCaja), $dia, $hoy, $esHoy, $diaAnterior, $diaSiguiente, $urlCsv

$hayVentas = ($cierre['facturas'] > 0);

$tituloPagina       = 'Cierre de caja diario';
$descripcionPagina  = 'Resumen de lo vendido y cobrado en el día, por forma de pago.';
$subtituloImpresion = 'Fecha: ' . formatoFecha($dia) . ($esHoy ? ' (hoy)' : '');

require __DIR__ . '/_inicio.php';
?>

        <!-- SELECTOR DE DÍA -->
        <form method="get" action="index.php" class="box mb-5 no-print">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="cierreCaja">

            <div class="columns is-vcentered mb-0">
                <div class="column is-narrow">
                    <label class="label is-small has-text-grey" for="filtroFecha">Día del cierre</label>
                    <input class="input" type="date" id="filtroFecha" name="fecha"
                           max="<?= esc($hoy) ?>" value="<?= esc($dia) ?>" required>
                </div>
                <div class="column is-narrow">
                    <label class="label is-small">&nbsp;</label>
                    <div class="buttons">
                        <button type="submit" class="button is-link">
                            <span class="icon is-small"><i class="fas fa-calendar-check"></i></span>
                            <span>Ver cierre</span>
                        </button>
                    </div>
                </div>
                <div class="column">
                    <label class="label is-small">&nbsp;</label>
                    <div class="buttons is-right">
                        <a class="button is-light" href="<?= esc(urlAccion('reporte', 'cierreCaja', ['fecha' => $diaAnterior])) ?>">
                            <span class="icon is-small"><i class="fas fa-chevron-left"></i></span>
                            <span>Día anterior</span>
                        </a>
                        <?php if ($diaSiguiente !== null): ?>
                            <a class="button is-light" href="<?= esc(urlAccion('reporte', 'cierreCaja', ['fecha' => $diaSiguiente])) ?>">
                                <span>Día siguiente</span>
                                <span class="icon is-small"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        <?php endif; ?>
                        <?php if (!$esHoy): ?>
                            <a class="button is-light" href="<?= esc(urlAccion('reporte', 'cierreCaja')) ?>">Ir a hoy</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <!-- AVISO: pagos que no cuadran con el total (no debería pasar; si pasa, hay que revisarlo) -->
        <?php if (abs($cierre['diferencia_pagos']) >= 0.005): ?>
            <div class="notification is-warning is-light">
                <strong>Atención:</strong> la suma de las formas de pago (<?= esc(dinero($cierre['cobrado'] + $cierre['credito'])) ?>)
                no coincide con el total vendido (<?= esc(dinero($cierre['total'])) ?>);
                la diferencia es de <?= esc(dinero(abs($cierre['diferencia_pagos']))) ?>.
                Hay ventas sin el desglose de pago registrado: revisa la tabla <code>venta_pagos</code>.
            </div>
        <?php endif; ?>

        <!-- INDICADORES -->
        <div class="columns is-multiline mb-4">
            <?= tarjetaKpi('Total vendido', dinero($cierre['total']),
                    $cierre['facturas'] . ($cierre['facturas'] === 1 ? ' factura' : ' facturas') . ' · ' . unidades($cierre['unidades']) . ' unds.',
                    'info', 'fa-file-invoice-dollar') ?>
            <?= tarjetaKpi('Total cobrado', dinero($cierre['cobrado']), 'Todo lo que entró, sin el crédito', 'success', 'fa-cash-register') ?>
            <?= tarjetaKpi('Vendido a crédito', dinero($cierre['credito']), 'Queda por cobrar', 'warning', 'fa-hand-holding-dollar') ?>
            <?= tarjetaKpi('Ventas anuladas', (string) $cierre['anuladas']['cantidad'],
                    $cierre['anuladas']['cantidad'] > 0 ? 'Por ' . dinero($cierre['anuladas']['total']) . ' (no suman)' : 'Ninguna este día',
                    'danger', 'fa-ban') ?>
        </div>

        <div class="columns">
            <!-- FORMAS DE PAGO -->
            <div class="column is-7">
                <div class="box p-0 caja-tabla">
                    <h2 class="titulo-tabla">Detalle por forma de pago</h2>
                    <div class="table-container mb-0">
                        <table class="table tabla-reporte is-fullwidth mb-0">
                            <thead>
                                <tr>
                                    <th>Forma de pago</th>
                                    <th class="num">Facturas</th>
                                    <th class="num">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (METODOS_PAGO as $clave => $etiqueta): ?>
                                    <?php if ($clave === 'credito') { continue; } ?>
                                    <?php $p = $cierre['pagos'][$clave] ?? ['facturas' => 0, 'monto' => 0.0]; ?>
                                    <tr class="<?= $p['facturas'] === 0 ? 'fila-vacia' : '' ?>">
                                        <td><?= esc($etiqueta) ?></td>
                                        <td class="num"><?= (int) $p['facturas'] ?></td>
                                        <td class="num"><?= esc(dinero($p['monto'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="fila-subtotal">
                                    <th colspan="2">Total cobrado</th>
                                    <th class="num"><?= esc(dinero($cierre['cobrado'])) ?></th>
                                </tr>
                                <?php $cr = $cierre['pagos']['credito'] ?? ['facturas' => 0, 'monto' => 0.0]; ?>
                                <tr class="<?= $cr['facturas'] === 0 ? 'fila-vacia' : '' ?>">
                                    <td><?= esc(METODOS_PAGO['credito']) ?> <span class="has-text-grey is-size-7">(por cobrar)</span></td>
                                    <td class="num"><?= (int) $cr['facturas'] ?></td>
                                    <td class="num"><?= esc(dinero($cr['monto'])) ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total vendido</th>
                                    <th class="num"><?= (int) $cierre['facturas'] ?></th>
                                    <th class="num"><?= esc(dinero($cierre['total'])) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="nota-tabla">Una venta con pago mixto aparece en cada forma de pago que se usó, por la parte que se pagó con ella.</p>
                </div>
            </div>

            <!-- BASE E IVA + POR USUARIO -->
            <div class="column is-5">
                <div class="box p-0 caja-tabla">
                    <h2 class="titulo-tabla">Base imponible e IVA</h2>
                    <div class="table-container mb-0">
                        <table class="table tabla-reporte is-fullwidth mb-0">
                            <tbody>
                                <tr><td>Subtotal (sin IVA)</td><td class="num"><?= esc(dinero($cierre['subtotal'])) ?></td></tr>
                                <tr><td>IVA</td><td class="num"><?= esc(dinero($cierre['iva'])) ?></td></tr>
                            </tbody>
                            <tfoot>
                                <tr><th>Total</th><th class="num"><?= esc(dinero($cierre['total'])) ?></th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="box p-0 caja-tabla">
                    <h2 class="titulo-tabla">Ventas por usuario</h2>
                    <div class="table-container mb-0">
                        <table class="table tabla-reporte is-fullwidth mb-0">
                            <thead>
                                <tr><th>Usuario</th><th class="num">Facturas</th><th class="num">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cierre['por_usuario'])): ?>
                                    <?php foreach ($cierre['por_usuario'] as $u): ?>
                                        <tr>
                                            <td><?= esc($u['username']) ?></td>
                                            <td class="num"><?= (int) $u['facturas'] ?></td>
                                            <td class="num"><?= esc(dinero($u['total_vendido'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="has-text-grey has-text-centered">Sin ventas este día.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANULACIONES DE DÍAS ANTERIORES (solo si las hay) -->
        <?php if ($cierre['anulaciones_previas']['cantidad'] > 0): ?>
            <div class="notification is-info is-light">
                <strong>Anulaciones de ventas de días anteriores:</strong>
                hoy se anularon <?= (int) $cierre['anulaciones_previas']['cantidad'] ?>
                <?= $cierre['anulaciones_previas']['cantidad'] === 1 ? 'venta' : 'ventas' ?>
                hechas antes de esta fecha, por <?= esc(dinero($cierre['anulaciones_previas']['total'])) ?>.
                No están en este cierre, pero si ya se habían cobrado, ese dinero pudo salir de la caja de este día.
            </div>
        <?php endif; ?>

        <!-- VENTAS DEL DÍA -->
        <div class="box p-0 caja-tabla">
            <h2 class="titulo-tabla">Ventas del día <span class="has-text-grey has-text-weight-normal is-size-7">(<?= count($cierre['ventas']) ?>)</span></h2>
            <div class="table-container mb-0">
                <table class="table tabla-reporte is-fullwidth is-hoverable mb-0">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Usuario</th>
                            <th>Forma de pago</th>
                            <th class="has-text-centered">Estado</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cierre['ventas'])): ?>
                            <?php foreach ($cierre['ventas'] as $v): ?>
                                <?php $anulada = ((int) $v['estado'] === 0); ?>
                                <tr class="<?= $anulada ? 'fila-anulada' : '' ?>">
                                    <td><?= esc(date('h:i A', strtotime($v['fecha_venta']))) ?></td>
                                    <td class="has-text-weight-bold">
                                        <a href="<?= esc(urlAccion('venta', 'verDetalleVenta', ['id' => $v['id_venta']])) ?>" class="no-print-link"><?= esc($v['numero_factura']) ?></a>
                                    </td>
                                    <td><?= esc($v['cliente']) ?></td>
                                    <td><?= esc($v['usuario']) ?></td>
                                    <td><?= esc(TIPOS_PAGO_VENTA[$v['tipo_pago']] ?? $v['tipo_pago']) ?></td>
                                    <td class="has-text-centered">
                                        <?php if ($anulada): ?>
                                            <span class="tag is-danger is-light has-text-weight-bold">ANULADA</span>
                                        <?php else: ?>
                                            <span class="tag is-success is-light has-text-weight-bold">REGISTRADA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="num monto"><?= esc(dinero($v['total'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="has-text-centered has-text-grey py-5">No hubo ventas en esta fecha.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CONTEO Y FIRMAS (para el cierre en papel) -->
        <div class="box arqueo">
            <h2 class="titulo-tabla sin-borde">Conteo de caja y firmas</h2>
            <div class="columns">
                <div class="column is-6">
                    <p class="linea-llenar"><span>Efectivo contado:</span><i></i></p>
                    <p class="linea-llenar"><span>Diferencia (contado − cobrado en efectivo):</span><i></i></p>
                    <p class="linea-llenar"><span>Observaciones:</span><i></i></p>
                </div>
                <div class="column is-6">
                    <div class="firmas">
                        <div><i></i><span>Entregado por</span></div>
                        <div><i></i><span>Recibido por</span></div>
                    </div>
                </div>
            </div>
        </div>

<?php require __DIR__ . '/_fin.php'; ?>
