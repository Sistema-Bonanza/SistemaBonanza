<?php
/**
 * @var array $filas
 * @var array $totales
 * @var bool  $verCostos
 */
$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'DISTRIBUIDORA DE ALIMENTOS BONANZA C.A.';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';
$usuarioEmisor = $_SESSION['username'] ?? $_SESSION['nombre'] ?? 'Sistema';
$cols          = $verCostos ? 10 : 8;

$nombresSituacion = [
    'agotado' => 'AGOTADO',
    'bajo'    => 'STOCK BAJO',
    'ok'      => 'DISPONIBLE',
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
        <col width="115"> <!-- Código -->
        <col width="240"> <!-- Producto -->
        <col width="150"> <!-- Categoría -->
        <col width="95">  <!-- Stock -->
        <col width="110"> <!-- Stock mínimo -->
        <col width="125"> <!-- Situación -->
        <?php if ($verCostos): ?><col width="120"><?php endif; ?>
        <col width="125"> <!-- Precio venta -->
        <?php if ($verCostos): ?><col width="135"><?php endif; ?>
        <col width="145"> <!-- Valor venta -->

        <tr>
            <td colspan="<?= $cols ?>" class="banner-empresa">
                <?= esc($nombreEmpresa) ?> <?= $rifEmpresa !== '' ? ' · RIF: ' . esc($rifEmpresa) : '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $cols ?>" class="banner-titulo">
                REPORTE DE EXISTENCIAS Y VALORIZACIÓN DE INVENTARIO
            </td>
        </tr>
        <tr>
            <td colspan="<?= $cols ?>" class="meta-info">
                <strong>Generado por:</strong> <?= esc($usuarioEmisor) ?> &nbsp;|&nbsp;
                <strong>Corte al:</strong> <?= date('d/m/Y h:i A') ?> &nbsp;|&nbsp;
                <strong>Ítems listados:</strong> <?= count($filas) ?>
            </td>
        </tr>

        <tr><td colspan="<?= $cols ?>" style="height: 12px; border: none;"></td></tr>

        <tr>
            <th class="th-tabla">Código</th>
            <th class="th-tabla">Producto</th>
            <th class="th-tabla">Categoría</th>
            <th class="th-tabla">Stock Actual</th>
            <th class="th-tabla">Stock Mínimo</th>
            <th class="th-tabla">Situación</th>
            <?php if ($verCostos): ?><th class="th-tabla">Costo Unit.</th><?php endif; ?>
            <th class="th-tabla">Precio Venta</th>
            <?php if ($verCostos): ?><th class="th-tabla">Valor al Costo</th><?php endif; ?>
            <th class="th-tabla">Valor a Venta</th>
        </tr>

        <?php if (empty($filas)): ?>
            <tr>
                <td colspan="<?= $cols ?>" class="td-dato centro" style="padding: 18px; color: #64748b;">
                    No se encontraron productos con los filtros aplicados.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($filas as $i => $f): ?>
                <?php
                    $sit = $f['situacion'] ?? 'ok';
                    if ($sit === 'agotado') {
                        $claseFila  = 'fila-peligro';
                        $claseBadge = 'badge-peligro';
                    } elseif ($sit === 'bajo') {
                        $claseFila  = 'fila-alerta';
                        $claseBadge = 'badge-alerta';
                    } else {
                        $claseFila  = ($i % 2 === 1) ? 'fila-par' : '';
                        $claseBadge = 'badge-ok';
                    }
                ?>
                <tr class="<?= $claseFila ?>">
                    <td class="td-dato texto centro negrita"><?= esc($f['codigo']) ?></td>
                    <td class="td-dato negrita"><?= esc($f['nombre']) ?></td>
                    <td class="td-dato"><?= esc($f['nombre_categoria']) ?></td>
                    <td class="td-dato entero negrita"><?= (int) $f['stock'] ?></td>
                    <td class="td-dato entero"><?= (int) $f['minimo'] ?></td>
                    <td class="<?= $claseBadge ?>"><?= $nombresSituacion[$sit] ?? 'DISPONIBLE' ?></td>

                    <?php if ($verCostos): ?>
                        <td class="td-dato moneda">
                            <?= $f['costo_unitario'] !== null ? number_format((float) $f['costo_unitario'], 2, '.', '') : '0.00' ?>
                        </td>
                    <?php endif; ?>

                    <td class="td-dato moneda">
                        <?= (float) $f['precio_venta'] > 0 ? number_format((float) $f['precio_venta'], 2, '.', '') : '0.00' ?>
                    </td>

                    <?php if ($verCostos): ?>
                        <td class="td-dato moneda">
                            <?= $f['valor_costo'] !== null ? number_format((float) $f['valor_costo'], 2, '.', '') : '0.00' ?>
                        </td>
                    <?php endif; ?>

                    <td class="td-dato moneda negrita"><?= number_format((float) $f['valor_venta'], 2, '.', '') ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="fila-total">
                <td colspan="3" class="td-dato der">TOTALES GENERALES DEL INVENTARIO:</td>
                <td class="td-dato entero"><?= (int) $totales['unidades'] ?></td>
                <td class="td-dato"></td>
                <td class="td-dato centro"><?= count($filas) ?> SKUs</td>
                <?php if ($verCostos): ?><td class="td-dato"></td><?php endif; ?>
                <td class="td-dato"></td>
                <?php if ($verCostos): ?>
                    <td class="td-dato moneda"><?= number_format((float) ($totales['valor_costo'] ?? 0), 2, '.', '') ?></td>
                <?php endif; ?>
                <td class="td-dato moneda"><?= number_format((float) $totales['valor_venta'], 2, '.', '') ?></td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>