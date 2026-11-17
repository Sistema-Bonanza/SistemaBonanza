<?php
/**
 * Variables entregadas por MovimientoController::exportarPdf():
 * @var array       $movimientos
 * @var string      $busqueda
 * @var string      $filtro
 * @var string|null $fechaDesde
 * @var string|null $fechaHasta
 * @var float       $totalComprasUsd
 * @var float       $totalComprasBs
 * @var float       $totalVentasUsd
 * @var float       $totalVentasBs
 * @var int         $cantidadAnuladas
 */

$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'Distribuidora de Alimentos Bonanza';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';

$etiquetaTipo = [
    'todas'    => 'Todas las operaciones (Entradas y Salidas)',
    'entradas' => 'Solo Entradas (Compras)',
    'salidas'  => 'Solo Salidas (Ventas)',
][$filtro ?? 'todas'] ?? 'Todas las operaciones';

$etiquetaRango = 'Historial completo';
if (!empty($fechaDesde) && !empty($fechaHasta)) {
    $etiquetaRango = 'Del ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta));
} elseif (!empty($fechaDesde)) {
    $etiquetaRango = 'Desde el ' . date('d/m/Y', strtotime($fechaDesde));
} elseif (!empty($fechaHasta)) {
    $etiquetaRango = 'Hasta el ' . date('d/m/Y', strtotime($fechaHasta));
}

$totalRegistros   = count($movimientos ?? []);
$totalAsentados   = $totalRegistros - ($cantidadAnuladas ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimientos · <?= esc($nombreEmpresa) ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= esc(URL_BASE) ?>/assets/css/ventas.css" />

    <style>
        body {
            background-color: #f1f5f9;
            padding: 1.5rem;
        }
        .fila-anulada td {
            color: #94a3b8 !important;
            text-decoration: line-through;
        }
        .fila-anulada td.sin-tachar {
            text-decoration: none;
        }
        @media print {
            @page { size: letter portrait; margin: 10mm; }
            body { background: #fff !important; padding: 0 !important; }
            .nota { box-shadow: none !important; border: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

    <div class="container is-max-desktop">

        <!-- BARRA SUPERIOR DE ACCIONES (no se imprime) -->
        <div class="is-flex is-justify-content-flex-end mb-4 no-print" style="gap: 0.75rem;">
            <button type="button" class="button is-white has-text-weight-semibold" style="border-radius: 8px;" onclick="window.close()">
                <span class="icon"><span class="material-symbols-outlined">close</span></span>
                <span>Cerrar pestaña</span>
            </button>
            <button type="button" class="button is-link has-text-weight-bold" style="border-radius: 8px;" data-imprimir>
                <span class="icon"><span class="material-symbols-outlined">print</span></span>
                <span>Imprimir / Guardar PDF</span>
            </button>
        </div>

        <!-- ===================== HOJA DEL REPORTE ===================== -->
        <article class="box nota">

            <header class="nota-encabezado">
                <div>
                    <p class="nota-empresa"><?= esc($nombreEmpresa) ?></p>
                    <?php if ($rifEmpresa !== ''): ?>
                        <p class="has-text-grey">RIF: <?= esc($rifEmpresa) ?></p>
                    <?php endif; ?>
                    <p class="is-size-7 has-text-grey mt-1">Auditoría de Inventario y Kardex</p>
                </div>
                <div class="nota-titulo">
                    <p class="tipo">REPORTE DE MOVIMIENTOS</p>
                    <p class="is-size-7 has-text-grey mt-1">Emitido: <?= date('d/m/Y h:i A') ?></p>
                </div>
            </header>

            <!-- METADATOS DE FILTROS APLICADOS -->
            <div class="nota-datos">
                <div>
                    <p class="rotulo">Período consultado</p>
                    <p class="valor"><?= esc($etiquetaRango) ?></p>
                </div>
                <div>
                    <p class="rotulo">Tipo de movimiento</p>
                    <p class="valor"><?= esc($etiquetaTipo) ?></p>
                </div>
                <div>
                    <p class="rotulo">Registros encontrados</p>
                    <p class="valor"><?= $totalRegistros ?> (<?= $totalAsentados ?> asentados · <?= (int) ($cantidadAnuladas ?? 0) ?> anulados)</p>
                </div>
                <?php if (!empty($busqueda)): ?>
                    <div>
                        <p class="rotulo">Búsqueda aplicada</p>
                        <p class="valor">"<?= esc($busqueda) ?>"</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RESUMEN DE TOTALES EN TARJETAS BULMA -->
            <div class="columns is-mobile mb-4">
                <div class="column">
                    <div class="box has-background-link-light p-3" style="box-shadow: none; border: 1px solid #bfdbfe;">
                        <p class="is-size-7 has-text-weight-bold has-text-link is-uppercase">Total Entradas (Compras)</p>
                        <p class="title is-5 has-text-link-dark mb-1 mt-1"><?= esc(dinero($totalComprasUsd ?? 0)) ?></p>
                        <p class="is-size-7 has-text-grey-dark">Bs <?= number_format((float) ($totalComprasBs ?? 0), 2, ',', '.') ?></p>
                    </div>
                </div>
                <div class="column">
                    <div class="box has-background-success-light p-3" style="box-shadow: none; border: 1px solid #bbf7d0;">
                        <p class="is-size-7 has-text-weight-bold has-text-success-dark is-uppercase">Total Salidas (Ventas)</p>
                        <p class="title is-5 has-text-success-dark mb-1 mt-1"><?= esc(dinero($totalVentasUsd ?? 0)) ?></p>
                        <p class="is-size-7 has-text-grey-dark">Bs <?= number_format((float) ($totalVentasBs ?? 0), 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <!-- TABLA DE OPERACIONES -->
            <div class="table-container">
                <table class="table tabla-nota is-fullwidth is-vcentered is-size-7">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Entidad (Proveedor / Cliente)</th>
                            <th>Usuario</th>
                            <th class="has-text-centered">Estado</th>
                            <th class="has-text-right">Monto USD</th>
                            <th class="has-text-right">Monto Bs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="8" class="has-text-centered has-text-grey py-5">
                                    No se encontraron movimientos para el rango de fechas y filtros seleccionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                                <?php
                                    $esVenta = (($m['tipo'] ?? '') === 'venta');
                                    $anulada = ((int) ($m['estado'] ?? 1) === 0);
                                    $idOp    = (int) ($m['id'] ?? 0);
                                    $doc     = $esVenta ? ($m['documento'] ?? numeroOperacion('FAC', $idOp)) : numeroOperacion('COMP', $idOp);
                                    $monto   = (float) ($m['total'] ?? 0);
                                    $tasa    = (float) ($m['tasa_cambio'] ?? 0);
                                ?>
                                <tr class="<?= $anulada ? 'fila-anulada' : '' ?>">
                                    <td><?= date('d/m/Y h:i A', strtotime($m['fecha'])) ?></td>
                                    <td class="sin-tachar">
                                        <span class="tag is-small has-text-weight-bold <?= $esVenta ? 'is-success is-light' : 'is-link is-light' ?>">
                                            <?= $esVenta ? 'VENTA' : 'COMPRA' ?>
                                        </span>
                                    </td>
                                    <td style="font-family: monospace; font-weight: 600;"><?= esc($doc) ?></td>
                                    <td>
                                        <p class="has-text-weight-semibold mb-0"><?= esc($m['entidad'] ?? '') ?></p>
                                        <p class="has-text-grey mb-0" style="font-size: 0.7rem;"><?= esc($m['identificacion'] ?? '') ?></p>
                                    </td>
                                    <td><?= esc($m['nombre_usuario'] ?? 'Sistema') ?></td>
                                    <td class="has-text-centered sin-tachar">
                                        <?php if ($anulada): ?>
                                            <span class="tag is-danger is-light is-small has-text-weight-bold">ANULADA</span>
                                        <?php else: ?>
                                            <span class="has-text-success-dark has-text-weight-semibold">Asentado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="num has-text-weight-bold"><?= esc(dinero($monto)) ?></td>
                                    <td class="num"><?= $tasa > 0 ? 'Bs ' . number_format($monto * $tasa, 2, ',', '.') : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="is-flex is-justify-content-space-between is-size-7 has-text-grey mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
                <span><?= esc($nombreEmpresa) ?> · Reporte generado automáticamente</span>
                <span>Las operaciones anuladas se muestran tachadas y no suman en los totales.</span>
            </div>

        </article>
    </div>

    <script>
        document.querySelectorAll('[data-imprimir]').forEach(function (btn) {
            btn.addEventListener('click', function () { window.print(); });
        });
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 350);
        });
    </script>
</body>
</html>