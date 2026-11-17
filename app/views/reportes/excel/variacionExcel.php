<?php
/**
 * @var array $filas
 * @var array $totales
 * @var array $actual
 * @var array $base
 */
$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'DISTRIBUIDORA DE ALIMENTOS BONANZA C.A.';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';
$usuarioEmisor = $_SESSION['username'] ?? $_SESSION['nombre'] ?? 'Sistema';

$nombresTendencia = [
    'aumento'     => ['texto' => 'AUMENTO',      'clase' => 'badge-ok'],
    'disminucion' => ['texto' => 'DISMINUCIÓN',  'clase' => 'badge-peligro'],
    'igual'       => ['texto' => 'SIN CAMBIO',   'clase' => 'badge-neutro'],
    'nuevo'       => ['texto' => 'NUEVO',        'clase' => 'badge-info'],
    'sin_ventas'  => ['texto' => 'SIN VENTAS',   'clase' => 'badge-alerta'],
];
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <?php require __DIR__ . '/_estilosExcel.php'; ?>
</head>
<body>
    <table>
        <col width="110"> <!-- Código -->
        <col width="220"> <!-- Producto -->
        <col width="135"> <!-- Categoría -->
        <col width="100"> <!-- Uds Base -->
        <col width="100"> <!-- Uds Actual -->
        <col width="100"> <!-- Dif Uds -->
        <col width="95">  <!-- Var Uds % -->
        <col width="115"> <!-- Monto Base -->
        <col width="115"> <!-- Monto Actual -->
        <col width="115"> <!-- Dif Monto -->
        <col width="95">  <!-- Var Monto % -->
        <col width="125"> <!-- Tendencia -->

        <tr>
            <td colspan="12" class="banner-empresa">
                <?= esc($nombreEmpresa) ?> <?= $rifEmpresa !== '' ? ' · RIF: ' . esc($rifEmpresa) : '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="12" class="banner-titulo">
                ANÁLISIS COMPARATIVO: AUMENTO Y DISMINUCIÓN DE VENTAS
            </td>
        </tr>
        <tr>
            <td colspan="12" class="meta-info">
                <strong>Período Actual:</strong> <?= esc(formatoFecha($actual['desde'])) ?> al <?= esc(formatoFecha($actual['hasta'])) ?> &nbsp;|&nbsp;
                <strong>Período Base:</strong> <?= esc(formatoFecha($base['desde'])) ?> al <?= esc(formatoFecha($base['hasta'])) ?> &nbsp;|&nbsp;
                <strong>Nota:</strong> Montos en USD ($) sin IVA · Emitido por <?= esc($usuarioEmisor) ?>
            </td>
        </tr>

        <tr><td colspan="12" style="height: 12px; border: none;"></td></tr>

        <!-- ENCABEZADO DE GRUPOS -->
        <tr>
            <th colspan="3" class="th-grupo">Identificación del Producto</th>
            <th colspan="4" class="th-grupo">Comparativa en Unidades</th>
            <th colspan="4" class="th-grupo">Comparativa en Montos (USD)</th>
            <th class="th-grupo">Resultado</th>
        </tr>
        <tr>
            <th class="th-tabla">Código</th>
            <th class="th-tabla">Producto</th>
            <th class="th-tabla">Categoría</th>
            <th class="th-tabla">Uds. Base</th>
            <th class="th-tabla">Uds. Actual</th>
            <th class="th-tabla">Diferencia</th>
            <th class="th-tabla">Var. %</th>
            <th class="th-tabla">Monto Base</th>
            <th class="th-tabla">Monto Actual</th>
            <th class="th-tabla">Diferencia</th>
            <th class="th-tabla">Var. %</th>
            <th class="th-tabla">Tendencia</th>
        </tr>

        <?php if (empty($filas)): ?>
            <tr>
                <td colspan="12" class="td-dato centro" style="padding: 18px; color: #64748b;">
                    No se encontraron datos comparativos para los períodos seleccionados.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($filas as $i => $f): ?>
                <?php
                    $tend      = $f['tendencia'] ?? 'igual';
                    $metaTend  = $nombresTendencia[$tend] ?? ['texto' => strtoupper($tend), 'clase' => 'badge-neutro'];
                    $difUds    = (int) $f['dif_unidades'];
                    $difMonto  = (float) $f['dif_monto'];
                    $claseFila = ($i % 2 === 1) ? 'fila-par' : '';
                ?>
                <tr class="<?= $claseFila ?>">
                    <td class="td-dato texto centro negrita"><?= esc($f['codigo']) ?></td>
                    <td class="td-dato negrita"><?= esc($f['nombre']) ?></td>
                    <td class="td-dato"><?= esc($f['nombre_categoria']) ?></td>

                    <td class="td-dato entero"><?= (int) $f['unidades_base'] ?></td>
                    <td class="td-dato entero negrita"><?= (int) $f['unidades_actual'] ?></td>
                    <td class="td-dato entero-dif negrita <?= $difUds > 0 ? 'txt-verde' : ($difUds < 0 ? 'txt-rojo' : '') ?>">
                        <?= $difUds ?>
                    </td>
                    <td class="td-dato porcentaje">
                        <?= $f['pct_unidades'] !== null ? number_format(((float) $f['pct_unidades']) / 100, 4, '.', '') : '—' ?>
                    </td>

                    <td class="td-dato moneda"><?= number_format((float) $f['monto_base'], 2, '.', '') ?></td>
                    <td class="td-dato moneda negrita"><?= number_format((float) $f['monto_actual'], 2, '.', '') ?></td>
                    <td class="td-dato moneda-dif negrita <?= $difMonto > 0 ? 'txt-verde' : ($difMonto < 0 ? 'txt-rojo' : '') ?>">
                        <?= number_format($difMonto, 2, '.', '') ?>
                    </td>
                    <td class="td-dato porcentaje">
                        <?= $f['pct_monto'] !== null ? number_format(((float) $f['pct_monto']) / 100, 4, '.', '') : '—' ?>
                    </td>

                    <td class="<?= $metaTend['clase'] ?>"><?= $metaTend['texto'] ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="fila-total">
                <td colspan="3" class="td-dato der">TOTALES COMPARATIVOS:</td>
                <td class="td-dato entero"><?= (int) $totales['unidades_base'] ?></td>
                <td class="td-dato entero"><?= (int) $totales['unidades_actual'] ?></td>
                <td class="td-dato entero-dif"><?= (int) $totales['dif_unidades'] ?></td>
                <td class="td-dato porcentaje">
                    <?= $totales['pct_unidades'] !== null ? number_format(((float) $totales['pct_unidades']) / 100, 4, '.', '') : '—' ?>
                </td>
                <td class="td-dato moneda"><?= number_format((float) $totales['monto_base'], 2, '.', '') ?></td>
                <td class="td-dato moneda"><?= number_format((float) $totales['monto_actual'], 2, '.', '') ?></td>
                <td class="td-dato moneda-dif"><?= number_format((float) $totales['dif_monto'], 2, '.', '') ?></td>
                <td class="td-dato porcentaje">
                    <?= $totales['pct_monto'] !== null ? number_format(((float) $totales['pct_monto']) / 100, 4, '.', '') : '—' ?>
                </td>
                <td class="td-dato centro"><?= count($filas) ?> SKUs</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>