<?php
// Variables que entrega VentaController::verDetalleVenta():
//   $venta, $detalles, $pagos, $numeroOperacion (VEN-), $numeroFactura (FAC-), $puedeAnular, $autoImprimir

$anulada = ((int) $venta['estado'] === 0);

$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'Industrias Bonanza';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';

// Unidades que volverían al inventario si se anula (se muestra en el aviso del modal).
$totalUnidades = 0;
foreach ($detalles as $linea) {
    $totalUnidades += (int) $linea['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= esc($numeroFactura) ?> · Bonanza</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/ventas.css" />
</head>
<body<?= $autoImprimir ? ' data-autoimprimir' : '' ?>>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main-content">
        <div class="container is-max-desktop">

            <!-- BARRA DE ACCIONES (no se imprime) -->
            <div class="level mb-5 no-print">
                <div class="level-left">
                    <div>
                        <h1 class="title is-4 mb-1">Factura</h1>
                        <p class="has-text-grey mt-1">Documento de la venta, listo para imprimir.</p>
                    </div>
                </div>
                <div class="level-right">
                    <div class="buttons">
                        <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-light">
                            <span class="icon is-small"><i class="fas fa-arrow-left"></i></span>
                            <span>Volver al listado</span>
                        </a>
                        <button type="button" class="button is-link" data-imprimir>
                            <span class="icon is-small"><i class="fas fa-print"></i></span>
                            <span>Imprimir</span>
                        </button>
                        <?php if ($puedeAnular): ?>
                            <button type="button" class="button is-danger is-light" data-modal-abrir="modalAnular">
                                <span class="icon is-small"><i class="fas fa-ban"></i></span>
                                <span>Anular factura</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="no-print"><?= mostrarFlash() ?></div>

            <!-- ===================== LA FACTURA ===================== -->
            <article class="box nota <?= $anulada ? 'nota-anulada' : '' ?>">

                <?php if ($anulada): ?>
                    <div class="sello-anulada" aria-hidden="true">ANULADA</div>
                <?php endif; ?>

                <header class="nota-encabezado">
                    <div>
                        <p class="nota-empresa"><?= esc($nombreEmpresa) ?></p>
                        <?php if ($rifEmpresa !== ''): ?>
                            <p class="has-text-grey">RIF: <?= esc($rifEmpresa) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="nota-titulo">
                        <p class="tipo">FACTURA</p>
                        <p class="numero"><?= esc($numeroFactura) ?></p>
                    </div>
                </header>

                <div class="nota-datos">
                    <div>
                        <p class="rotulo">Cliente</p>
                        <p class="valor"><?= esc($venta['nombre_cliente']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Fecha de emisión</p>
                        <p class="valor"><?= esc(formatoFecha($venta['fecha_venta'], true)) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Documento</p>
                        <p class="valor"><?= esc($venta['tipo_documento'] . '-' . $venta['num_documento']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Atendido por</p>
                        <p class="valor"><?= esc($venta['nombre_usuario']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Venta N.º</p>
                        <p class="valor"><?= esc($numeroOperacion) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Forma de pago</p>
                        <p class="valor"><?= esc(TIPOS_PAGO_VENTA[$venta['tipo_pago'] ?? ''] ?? 'No registrada') ?></p>
                    </div>
                    <?php if (!empty($venta['telefono_cliente'])): ?>
                        <div>
                            <p class="rotulo">Teléfono</p>
                            <p class="valor"><?= esc($venta['telefono_cliente']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($venta['direccion_cliente'])): ?>
                        <div>
                            <p class="rotulo">Dirección</p>
                            <p class="valor"><?= esc($venta['direccion_cliente']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="table-container">
                    <table class="table tabla-nota is-fullwidth is-vcentered">
                        <thead>
                            <tr>
                                <th class="has-text-centered" style="width: 44px;">#</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="has-text-right">Cant.</th>
                                <th class="has-text-right">Precio unit.</th>
                                <th class="has-text-centered">IVA</th>
                                <th class="has-text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $n => $linea): ?>
                                <tr>
                                    <td class="has-text-centered has-text-grey"><?= $n + 1 ?></td>
                                    <td class="num" style="text-align: left;"><?= esc($linea['codigo']) ?></td>
                                    <td><?= esc($linea['nombre']) ?></td>
                                    <td class="num"><?= (int) $linea['cantidad'] ?></td>
                                    <td class="num"><?= esc(dinero($linea['precio_unitario'])) ?></td>
                                    <td class="has-text-centered">
                                        <?= ((int) $linea['aplica_iva'] === 1) ? esc(porcentaje($venta['tasa_iva'], 0)) : 'Exento' ?>
                                    </td>
                                    <td class="num"><?= esc(dinero($linea['subtotal_linea'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="totales-venta">
                    <div class="fila-total">
                        <span class="has-text-grey">Subtotal</span>
                        <span><?= esc(dinero($venta['subtotal'])) ?></span>
                    </div>
                    <div class="fila-total">
                        <span class="has-text-grey">IVA (<?= esc(porcentaje($venta['tasa_iva'])) ?>)</span>
                        <span><?= esc(dinero($venta['monto_iva'])) ?></span>
                    </div>
                    <div class="fila-total total-final">
                        <span>Total</span>
                        <span><?= esc(dinero($venta['total'])) ?></span>
                    </div>
                </div>

                <?php if (!empty($pagos)): ?>
                    <div class="pagos-factura">
                        <p class="has-text-weight-bold mb-1 has-text-right">Detalle del pago</p>
                        <table class="table is-narrow is-fullwidth">
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td><?= esc(METODOS_PAGO[$pago['metodo']] ?? $pago['metodo']) ?></td>
                                        <td class="num"><?= esc(dinero($pago['monto'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php foreach ($pagos as $pago): ?>
                            <?php if ($pago['metodo'] === 'credito'): ?>
                                <p class="has-text-right is-size-7 has-text-grey">
                                    <?= esc(dinero($pago['monto'])) ?> quedaron a crédito (pendientes de cobro).
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($venta['observaciones'])): ?>
                    <p class="mt-4"><strong>Observaciones:</strong> <?= esc($venta['observaciones']) ?></p>
                <?php endif; ?>

                <?php if ($anulada): ?>
                    <div class="notification is-danger is-light mt-5 mb-0">
                        <p class="has-text-weight-bold mb-1">Esta venta fue anulada</p>
                        <p>
                            <?= esc(formatoFecha($venta['fecha_anulacion'], true)) ?>
                            <?php if (!empty($venta['nombre_usuario_anula'])): ?>
                                · por <?= esc($venta['nombre_usuario_anula']) ?>
                            <?php endif; ?>
                        </p>
                        <p><strong>Motivo:</strong> <?= esc($venta['motivo_anulacion']) ?></p>
                        <p class="is-size-7 mt-1">El stock de los productos volvió al inventario.</p>
                    </div>
                <?php endif; ?>

                <div class="nota-firmas">
                    <div>Entregado por</div>
                    <div>Recibido conforme (nombre, cédula y firma)</div>
                </div>
            </article>
        </div>
    </main>

    <?php if ($puedeAnular): ?>
        <!-- MODAL DE ANULACIÓN (solo administración) -->
        <div class="modal no-print" id="modalAnular">
            <div class="modal-background" data-modal-cerrar></div>
            <form class="modal-card" method="POST" action="<?= esc(urlAccion('venta', 'anularVenta')) ?>"
                  data-confirmar="¿Anular la factura <?= esc($numeroFactura) ?>? Esta acción no se puede deshacer.">
                <?= campoCsrf() ?>
                <input type="hidden" name="id_venta" value="<?= (int) $venta['id_venta'] ?>">

                <header class="modal-card-head">
                    <p class="modal-card-title">Anular factura <?= esc($numeroFactura) ?></p>
                    <button type="button" class="delete" aria-label="Cerrar" data-modal-cerrar></button>
                </header>

                <section class="modal-card-body">
                    <div class="notification is-warning is-light">
                        Se devolverán <strong><?= (int) $totalUnidades ?> <?= $totalUnidades === 1 ? 'unidad' : 'unidades' ?></strong>
                        al inventario y la venta dejará de sumar en los indicadores.
                        La factura se conserva como <strong>ANULADA</strong>; no se borra.
                    </div>

                    <div class="field">
                        <label class="label" for="motivo_anulacion">Motivo de la anulación</label>
                        <div class="control">
                            <textarea class="textarea" name="motivo_anulacion" id="motivo_anulacion" rows="3"
                                      minlength="5" maxlength="255" required
                                      placeholder="Ej: El cliente devolvió la mercancía"></textarea>
                        </div>
                        <p class="help">Entre 5 y 255 caracteres. Queda registrado junto con tu usuario.</p>
                    </div>
                </section>

                <footer class="modal-card-foot is-justify-content-flex-end">
                    <button type="button" class="button" data-modal-cerrar>Cancelar</button>
                    <button type="submit" class="button is-danger has-text-weight-bold">Anular factura</button>
                </footer>
            </form>
        </div>
    <?php endif; ?>

    <script src="assets/js/venta.js"></script>
</body>
</html>
