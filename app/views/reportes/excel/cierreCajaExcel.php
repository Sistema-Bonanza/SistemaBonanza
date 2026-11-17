<?php
/** @var array $c */
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
        <col width="145">
        <col width="165">
        <col width="225">
        <col width="120">
        <col width="150">
        <col width="120">
        <col width="130">

        <tr>
            <td colspan="7" class="banner-empresa">
                <?= esc($nombreEmpresa) ?> <?= $rifEmpresa !== '' ? ' · RIF: ' . esc($rifEmpresa) : '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="7" class="banner-titulo">
                REPORTE DE CIERRE DE CAJA DIARIO — FECHA: <?= esc(formatoFecha($c['dia'])) ?>
            </td>
        </tr>
        <tr>
            <td colspan="7" class="meta-info">
                <strong>Generado por:</strong> <?= esc($usuarioEmisor) ?> &nbsp;|&nbsp;
                <strong>Emitido el:</strong> <?= date('d/m/Y h:i A') ?> &nbsp;|&nbsp;
                <strong>Moneda base:</strong> Dólar Estadounidense (USD $)
            </td>
        </tr>

        <tr><td colspan="7" style="height: 12px; border: none;"></td></tr>

        <!-- BLOQUE 1: FORMAS DE PAGO -->
        <tr>
            <td colspan="3" class="seccion-titulo">1. Resumen de Ingresos por Forma de Pago</td>
            <td colspan="4" style="border: none;"></td>
        </tr>
        <tr>
            <th class="th-tabla">Forma de Pago</th>
            <th class="th-tabla">Cant. Facturas</th>
            <th class="th-tabla">Monto Recaudado</th>
            <td colspan="4" style="border: none;"></td>
        </tr>

        <?php $i = 0; foreach (METODOS_PAGO as $clave => $etiqueta): ?>
            <?php $p = $c['pagos'][$clave] ?? ['facturas' => 0, 'monto' => 0.0]; ?>
            <tr class="<?= ($i % 2 === 1) ? 'fila-par' : '' ?>">
                <td class="td-dato negrita"><?= esc($etiqueta) ?></td>
                <td class="td-dato entero"><?= (int) $p['facturas'] ?></td>
                <td class="td-dato moneda"><?= number_format((float) $p['monto'], 2, '.', '') ?></td>
                <td colspan="4" style="border: none;"></td>
            </tr>
        <?php $i++; endforeach; ?>

        <tr class="fila-subtotal">
            <td class="td-dato izq">TOTAL COBRADO EN CAJA</td>
            <td class="td-dato centro">—</td>
            <td class="td-dato moneda"><?= number_format((float) $c['cobrado'], 2, '.', '') ?></td>
            <td colspan="4" style="border: none;"></td>
        </tr>
        <tr class="fila-total">
            <td class="td-dato izq">TOTAL VENDIDO (CON IVA)</td>
            <td class="td-dato entero"><?= (int) $c['facturas'] ?></td>
            <td class="td-dato moneda"><?= number_format((float) $c['total'], 2, '.', '') ?></td>
            <td colspan="4" style="border: none;"></td>
        </tr>

        <tr><td colspan="7" style="height: 16px; border: none;"></td></tr>

        <!-- BLOQUE 2: VENTAS DEL DÍA -->
        <tr>
            <td colspan="7" class="seccion-titulo">2. Detalle Cronológico de Ventas del Día</td>
        </tr>
        <tr>
            <th class="th-tabla">N° Factura</th>
            <th class="th-tabla">Fecha y Hora</th>
            <th class="th-tabla">Cliente</th>
            <th class="th-tabla">Usuario</th>
            <th class="th-tabla">Forma de Pago</th>
            <th class="th-tabla">Estado</th>
            <th class="th-tabla">Total</th>
        </tr>

        <?php if (empty($c['ventas'])): ?>
            <tr>
                <td colspan="7" class="td-dato centro" style="padding: 18px; color: #64748b;">
                    No se registraron ventas en esta fecha.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($c['ventas'] as $idx => $v): ?>
                <?php
                    $esAnulada = ((int) $v['estado'] !== 1);
                    $claseFila = $esAnulada ? 'fila-peligro' : (($idx % 2 === 1) ? 'fila-par' : '');
                    $formaPago = TIPOS_PAGO_VENTA[$v['tipo_pago']] ?? $v['tipo_pago'];
                ?>
                <tr class="<?= $claseFila ?>">
                    <td class="td-dato texto centro negrita"><?= esc($v['numero_factura']) ?></td>
                    <td class="td-dato centro"><?= esc(formatoFecha($v['fecha_venta'], true)) ?></td>
                    <td class="td-dato"><?= esc($v['cliente']) ?></td>
                    <td class="td-dato centro"><?= esc($v['usuario']) ?></td>
                    <td class="td-dato centro"><?= esc($formaPago) ?></td>
                    <td class="<?= $esAnulada ? 'badge-peligro' : 'badge-ok' ?>">
                        <?= $esAnulada ? 'ANULADA' : 'Registrada' ?>
                    </td>
                    <td class="td-dato moneda negrita <?= $esAnulada ? 'tachado' : '' ?>">
                        <?= number_format((float) $v['total'], 2, '.', '') ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="fila-total">
                <td colspan="6" class="td-dato der">TOTAL OPERACIONES VÁLIDAS DEL DÍA (Excluye anuladas):</td>
                <td class="td-dato moneda"><?= number_format((float) $c['total'], 2, '.', '') ?></td>
            </tr>
        <?php endif; ?>

        <!-- FIRMAS -->
        <tr><td colspan="7" style="height: 36px; border: none;"></td></tr>
        <tr>
            <td colspan="2" class="caja-firma">
                <strong>Entregado por (Cajero / Operador)</strong><br>Firma y Cédula
            </td>
            <td style="border: none;"></td>
            <td colspan="2" class="caja-firma">
                <strong>Recibido / Verificado por (Administración)</strong><br>Firma y Sello
            </td>
            <td colspan="2" style="border: none;"></td>
        </tr>
    </table>
</body>
</html>