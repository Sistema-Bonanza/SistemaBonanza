<?php
/**
 * Variables entregadas por productoController::exportarExcel():
 * @var array $productos
 * @var int   $totalUnidades
 * @var float $totalValorCosto
 * @var float $totalValorVenta
 * @var int   $productosCriticos
 */

$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'Distribuidora de Alimentos Bonanza';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <style>
        body {
            font-family: BlinkMacSystemFont, -apple-system, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 10.5pt;
            color: #363636;
        }
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .titulo-bulma {
            font-size: 15pt;
            font-weight: 700;
            color: #2563eb;
            border: none !important;
        }
        .subtitulo-bulma {
            font-size: 10pt;
            color: #4a4a4a;
            border: none !important;
        }
        /* Encabezado estilo Bulma is-link */
        .thead-bulma th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            border: 1px solid #1d4ed8;
            padding: 8px 6px;
            font-size: 10pt;
        }
        .table td {
            border: 1px solid #dbdbdb;
            padding: 6px;
            vertical-align: middle;
        }
        /* Striped y estados estilo Bulma (is-danger is-light / is-success is-light) */
        .is-striped-row { background-color: #fafafa; }
        .is-danger-light { background-color: #feecf0; color: #cc0f35; }
        .is-success-light { background-color: #effaf5; color: #257953; font-weight: 700; }
        .is-warning-light { background-color: #fffaeb; color: #946c00; font-weight: 700; }
        .is-danger-tag   { background-color: #feecf0; color: #cc0f35; font-weight: 700; }

        /* Alineaciones Bulma + formatos numéricos nativos de Excel */
        .has-text-centered { text-align: center; }
        .has-text-right    { text-align: right; }
        .has-text-weight-bold { font-weight: 700; }
        .texto  { mso-number-format: "\@"; }
        .entero { mso-number-format: "#,##0"; text-align: right; }
        .moneda { mso-number-format: "\$#,##0.00"; text-align: right; }

        .tfoot-bulma td {
            background-color: #eff5fb;
            color: #1d4ed8;
            font-weight: 700;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }
    </style>
</head>
<body>
    <table class="table is-bordered is-striped is-fullwidth">
        <!-- MEMBRETE -->
        <tr>
            <td colspan="14" class="titulo-bulma">
                <?= esc($nombreEmpresa) ?> <?= $rifEmpresa !== '' ? '· RIF: ' . esc($rifEmpresa) : '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="14" class="subtitulo-bulma" style="padding-bottom: 12px;">
                Catálogo Maestro de Productos e Inventario · Emitido el <?= date('d/m/Y h:i A') ?> ·
                Productos activos: <?= count($productos) ?> (<?= $productosCriticos ?> requieren reposición)
            </td>
        </tr>

        <!-- ENCABEZADOS -->
        <thead class="thead-bulma">
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Presentación</th>
                <th>Impuesto</th>
                <th>Costo Unit.</th>
                <th>Precio Venta</th>
                <th>Precio por Cantidad</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Valor Total Costo</th>
                <th>Valor Total Venta</th>
                <th>Estado Stock</th>
            </tr>
        </thead>

        <!-- FILAS -->
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="14" class="has-text-centered" style="padding: 20px;">
                        No hay productos activos registrados en el catálogo.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $i => $p): ?>
                    <?php
                        $stock      = (int) ($p['stock_actual'] ?? 0);
                        $minimo     = (int) ($p['stock_minimo'] ?? 0);
                        $costo      = (float) ($p['precio_compra'] ?? 0);
                        $venta      = (float) ($p['precio_venta'] ?? 0);
                        $valorCosto = $stock * $costo;
                        $valorVenta = $stock * $venta;
                        $aplicaIva  = ((int) ($p['aplica_iva'] ?? 0) === 1) ? 'IVA 16%' : 'Exento';

                        // Formatear escalas_txt (ej: "10:fijo:0.85,20:porcentaje:5")
                        $textoEscalas = 'Sin escala';
                        if (!empty($p['escalas_txt'])) {
                            $partes = explode(',', (string) $p['escalas_txt']);
                            $legibles = [];
                            foreach ($partes as $item) {
                                $datosEscala = explode(':', $item);
                                if (count($datosEscala) === 3) {
                                    [$cantMin, $modo, $val] = $datosEscala;
                                    if ($modo === 'porcentaje' || $modo === 'descuento') {
                                        $legibles[] = "≥{$cantMin} uds: -" . number_format((float) $val, 0) . "%";
                                    } else {
                                        $legibles[] = "≥{$cantMin} uds: $" . number_format((float) $val, 2, '.', '');
                                    }
                                }
                            }
                            if (!empty($legibles)) {
                                $textoEscalas = implode(' | ', $legibles);
                            }
                        }

                        $esAgotado = ($stock === 0);
                        $esCritico = (!$esAgotado && $minimo > 0 && $stock <= $minimo);

                        if ($esAgotado) {
                            $estadoTexto = 'AGOTADO';
                            $claseFila   = 'is-danger-light';
                            $claseEstado = 'is-danger-tag';
                        } elseif ($esCritico) {
                            $estadoTexto = 'STOCK CRÍTICO';
                            $claseFila   = 'is-danger-light';
                            $claseEstado = 'is-warning-light';
                        } else {
                            $estadoTexto = 'ÓPTIMO';
                            $claseFila   = ($i % 2 === 1) ? 'is-striped-row' : '';
                            $claseEstado = 'is-success-light';
                        }

                        $presentacion = ($p['unidad_manejo'] ?? 'UNIDAD') . ' (' . (int) ($p['unidades_por_empaque'] ?? 1) . ' uds)';
                    ?>
                    <tr class="<?= $claseFila ?>">
                        <td class="has-text-centered"><?= $i + 1 ?></td>
                        <td class="texto has-text-centered has-text-weight-bold"><?= esc($p['codigo'] ?? '') ?></td>
                        <td class="has-text-weight-bold"><?= esc($p['nombre'] ?? '') ?></td>
                        <td><?= esc($p['nombre_categoria'] ?? 'General') ?></td>
                        <td class="has-text-centered"><?= esc($presentacion) ?></td>
                        <td class="has-text-centered"><?= $aplicaIva ?></td>
                        <td class="moneda"><?= number_format($costo, 2, '.', '') ?></td>
                        <td class="moneda"><?= number_format($venta, 2, '.', '') ?></td>
                        <td class="texto has-text-centered"><?= esc($textoEscalas) ?></td>
                        <td class="entero has-text-weight-bold"><?= $stock ?></td>
                        <td class="entero"><?= $minimo ?></td>
                        <td class="moneda"><?= number_format($valorCosto, 2, '.', '') ?></td>
                        <td class="moneda"><?= number_format($valorVenta, 2, '.', '') ?></td>
                        <td class="has-text-centered <?= $claseEstado ?>"><?= $estadoTexto ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- TOTALES -->
                <tr class="tfoot-bulma">
                    <td colspan="9" class="has-text-right">TOTALES GENERALES DEL INVENTARIO:</td>
                    <td class="entero"><?= $totalUnidades ?></td>
                    <td></td>
                    <td class="moneda"><?= number_format($totalValorCosto, 2, '.', '') ?></td>
                    <td class="moneda"><?= number_format($totalValorVenta, 2, '.', '') ?></td>
                    <td class="has-text-centered"><?= count($productos) ?> ítems</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>