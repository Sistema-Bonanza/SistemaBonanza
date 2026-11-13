<?php
// Variables que entrega MovimientoController::verDetalleCompra():
//   $compra (cabecera con proveedor y usuario), $detalles (líneas), $numeroOperacion (COMP-000001)
//
// Es la nota de recepción de la mercancía: lo mismo que ve el usuario en el listado de Movimientos
// como COMPRA (entrada al inventario).

$anulada = ((int) $compra['estado'] === 0);

$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'Distribuidora de Alimentos Bonanza';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';

$totalUnidades = 0;
foreach ($detalles as $linea) {
    $totalUnidades += (int) $linea['cantidad_unidades'];
}
$tasa = (float) ($compra['tasa_cambio'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra <?= esc($numeroOperacion) ?> · Distribuidora de Alimentos Bonanza</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/ventas.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main-content">
        <div class="container is-max-desktop">

            <!-- BANNER (no se imprime) -->
            <div class="box mb-5 no-print" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">receipt_long</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Nota de Recepción</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Entrada de Mercancía
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Documento de la compra registrada en el inventario, con el detalle de cada producto recibido.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="<?= esc(urlAccion('movimiento', 'tablaMovimientos')) ?>" class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver a Movimientos</span>
                        </a>
                        <button type="button" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;" data-imprimir>
                            <span class="icon"><span class="material-symbols-outlined">print</span></span>
                            <span>Imprimir</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="no-print"><?= mostrarFlash() ?></div>

            <!-- ===================== LA NOTA ===================== -->
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
                        <p class="tipo">NOTA DE RECEPCIÓN</p>
                        <p class="numero"><?= esc($numeroOperacion) ?></p>
                    </div>
                </header>

                <div class="nota-datos">
                    <div>
                        <p class="rotulo">Proveedor</p>
                        <p class="valor"><?= esc($compra['razon_social']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">RIF</p>
                        <p class="valor"><?= esc($compra['rif']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Factura del proveedor</p>
                        <p class="valor"><?= esc($compra['numero_factura']) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Fecha de la factura</p>
                        <p class="valor"><?= esc(formatoFecha($compra['fecha_factura'])) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Recibido el</p>
                        <p class="valor"><?= esc(formatoFecha($compra['fecha_registro'], true)) ?></p>
                    </div>
                    <div>
                        <p class="rotulo">Registrado por</p>
                        <p class="valor"><?= esc($compra['nombre_usuario']) ?></p>
                    </div>
                    <?php if (!empty($compra['telefono_proveedor'])): ?>
                        <div>
                            <p class="rotulo">Teléfono</p>
                            <p class="valor"><?= esc($compra['telefono_proveedor']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($compra['direccion_proveedor'])): ?>
                        <div>
                            <p class="rotulo">Dirección</p>
                            <p class="valor"><?= esc($compra['direccion_proveedor']) ?></p>
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
                                <th>Empaque</th>
                                <th class="has-text-right">Empaques</th>
                                <th class="has-text-right">Unidades</th>
                                <th class="has-text-right">Costo / empaque</th>
                                <th class="has-text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $n => $linea): ?>
                                <tr>
                                    <td class="has-text-centered has-text-grey"><?= $n + 1 ?></td>
                                    <td class="num" style="text-align: left;"><?= esc($linea['codigo']) ?></td>
                                    <td><?= esc($linea['nombre']) ?></td>
                                    <td><?= esc($linea['tipo_empaque']) ?> <span class="has-text-grey is-size-7">(<?= (int) $linea['unidades_por_empaque'] ?> uds)</span></td>
                                    <td class="num"><?= (int) $linea['cantidad_empaques'] ?></td>
                                    <td class="num"><?= (int) $linea['cantidad_unidades'] ?></td>
                                    <td class="num">
                                        <?= esc(dinero($linea['costo_por_empaque'])) ?>
                                        <?php if ($tasa > 0): ?>
                                            <span class="is-block is-size-7 has-text-grey">Bs <?= esc(number_format((float) $linea['costo_por_empaque'] * $tasa, 2, ',', '.')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="num">
                                        <?= esc(dinero($linea['subtotal_linea'])) ?>
                                        <?php if ($tasa > 0): ?>
                                            <span class="is-block is-size-7 has-text-grey">Bs <?= esc(number_format((float) $linea['subtotal_linea'] * $tasa, 2, ',', '.')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="totales-venta">
                    <div class="fila-total">
                        <span class="has-text-grey">Unidades recibidas</span>
                        <span><?= (int) $totalUnidades ?></span>
                    </div>
                    <div class="fila-total">
                        <span class="has-text-grey">Subtotal</span>
                        <span><?= esc(dinero($compra['subtotal'])) ?></span>
                    </div>
                    <div class="fila-total">
                        <span class="has-text-grey">IVA (<?= esc(porcentaje((float) $compra['tasa_iva'])) ?>)</span>
                        <span><?= esc(dinero($compra['monto_iva'])) ?></span>
                    </div>
                    <div class="fila-total total-final">
                        <span>Total</span>
                        <span><?= esc(dinero($compra['total'])) ?></span>
                    </div>
                    <?php if ($tasa > 0): ?>
                        <div class="fila-total">
                            <span class="has-text-grey">Total en bolívares (tasa BCV <?= esc(number_format($tasa, 4, ',', '.')) ?>)</span>
                            <span>Bs <?= esc(number_format((float) $compra['total'] * $tasa, 2, ',', '.')) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($compra['observaciones'])): ?>
                    <p class="mt-4"><strong>Observaciones:</strong> <?= esc($compra['observaciones']) ?></p>
                <?php endif; ?>

                <?php if ($anulada): ?>
                    <div class="notification is-danger is-light mt-5 mb-0">
                        <p class="has-text-weight-bold mb-1">Esta compra fue anulada</p>
                        <?php if (!empty($compra['motivo_anulacion'])): ?>
                            <p><strong>Motivo:</strong> <?= esc($compra['motivo_anulacion']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </article>
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-imprimir]').forEach(function (b) {
            b.addEventListener('click', function () { window.print(); });
        });
    </script>
</body>
</html>
