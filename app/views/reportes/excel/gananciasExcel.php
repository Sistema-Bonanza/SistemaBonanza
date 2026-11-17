<?php
/**
 * @var array  $filas
 * @var array  $totales
 * @var string $desde
 * @var string $hasta
 */
$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'DISTRIBUIDORA DE ALIMENTOS BONANZA C.A.';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';
$usuarioEmisor = $_SESSION['username'] ?? $_SESSION['nombre'] ?? 'Sistema';
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
        <col width="115"> <!-- Código -->
        <col width="235"> <!-- Producto -->
        <col width="145"> <!-- Categoría -->
        <col width="100"> <!-- Unidades -->
        <col width="125"> <!-- Ingresos -->
        <col width="125"> <!-- Costo -->
        <col width="125"> <!-- Ganancia -->
        <col width="105"> <!-- Margen % -->
        <col width="120"> <!-- Sin costo -->

        <tr>
            <td colspan="9" class="banner-empresa">
                <?= esc($nombreEmpresa) ?> <?= $rifEmpresa !== '' ? ' · RIF: ' . esc($rifEmpresa) : '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="9" class="banner-titulo">
                REPORTE DE GANANCIA Y MARGEN POR PRODUCTO — DEL <?= esc(formatoFecha($desde)) ?> AL <?= esc(formatoFecha($hasta)) ?>
            </td>
        </tr>
        <tr>
            <td colspan="9" class="meta-info">
                <strong>Generado por:</strong> <?= esc($usuarioEmisor) ?> &nbsp;|&nbsp;
                <strong>Emitido el:</strong> <?= date('d/m/Y h:i A') ?> &nbsp;|&nbsp;
                <strong>Nota fiscal:</strong> Valores expresados en USD ($) sin IVA
            </td>
        </tr>

        <tr><td colspan="9" style="height: 12px; border: none;"></td></tr>

        <tr>
            <th class="th-tabla">Código</th>
            <th class="th-tabla">Producto</th>
            <th class="th-tabla">Categoría</th>
            <th class="th-tabla">Unidades Vendidas</th>
            <th class="th-tabla">Ingresos Netos</th>
            <th class="th-tabla">Costo Total</th>
            <th class="th-tabla">Ganancia Bruta</th>
            <th class="th-tabla">Margen %</th>
            <th class="th-tabla">Uds. Sin Costo</th>
        </tr>

        <?php if (empty($filas)): ?>
            <tr>
                <td colspan="9" class="td-dato centro" style="padding: 18px; color: #64748b;">
                    No se registraron ventas de productos en el período seleccionado.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($filas as $i => $f): ?>
                <?php
                    $ganancia  = (float) $f['ganancia'];
                    $margen    = $f['margen'] !== null ? (float) $f['margen'] : null;
                    $esPerdida = ($ganancia < 0);
                    $claseFila = $esPerdida ? 'fila-peligro' : (($i % 2 === 1) ? 'fila-par' : '');
                ?>
                <tr class="<?= $claseFila ?>">
                    <td class="td-dato texto centro negrita"><?= esc($f['codigo']) ?></td>
                    <td class="td-dato negrita"><?= esc($f['nombre']) ?></td>
                    <td class="td-dato"><?= esc($f['nombre_categoria']) ?></td>
                    <td class="td-dato entero"><?= (int) $f['unidades'] ?></td>
                    <td class="td-dato moneda"><?= number_format((float) $f['ingresos'], 2, '.', '') ?></td>
                    <td class="td-dato moneda"><?= number_format((float) $f['costo'], 2, '.', '') ?></td>
                    <td class="td-dato moneda negrita <?= $esPerdida ? 'txt-rojo' : 'txt-verde' ?>">
                        <?= number_format($ganancia, 2, '.', '') ?>
                    </td>
                    <td class="td-dato porcentaje negrita">
                        <?= $margen !== null ? number_format($margen / 100, 4, '.', '') : '—' ?>
                    </td>
                    <td class="td-dato entero"><?= (int) $f['unidades_sin_costo'] ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="fila-total">
                <td colspan="3" class="td-dato der">TOTALES DEL PERÍODO CONSULTADO:</td>
                <td class="td-dato entero"><?= (int) $totales['unidades'] ?></td>
                <td class="td-dato moneda"><?= number_format((float) $totales['ingresos'], 2, '.', '') ?></td>
                <td class="td-dato moneda"><?= number_format((float) $totales['costo'], 2, '.', '') ?></td>
                <td class="td-dato moneda"><?= number_format((float) $totales['ganancia'], 2, '.', '') ?></td>
                <td class="td-dato porcentaje">
                    <?= $totales['margen'] !== null ? number_format(((float) $totales['margen']) / 100, 4, '.', '') : '—' ?>
                </td>
                <td class="td-dato entero"><?= (int) $totales['unidades_sin_costo'] ?></td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>