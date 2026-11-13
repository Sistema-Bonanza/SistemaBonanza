<?php 
/**
 * Una fila del listado de Movimientos. Sirve para los dos orígenes:
 *   $mov['tipo'] = 'compra'  -> entrada al inventario (módulo de Compras)
 *   $mov['tipo'] = 'venta'   -> salida del inventario  (módulo de Ventas)
 * Columnas que entrega movimientoModel: tipo, id, documento, fecha, total, estado,
 * entidad, identificacion, nombre_usuario, tasa_cambio (BCV usada, puede ser null).
 *
 * @var array $mov
 */

$meses_es = ['Jan'=>'Ene', 'Feb'=>'Feb', 'Mar'=>'Mar', 'Apr'=>'Abr', 'May'=>'May', 'Jun'=>'Jun', 'Jul'=>'Jul', 'Aug'=>'Ago', 'Sep'=>'Sep', 'Oct'=>'Oct', 'Nov'=>'Nov', 'Dec'=>'Dic'];

$esVenta = (($mov['tipo'] ?? 'compra') === 'venta');
$anulado = (isset($mov['estado']) && (int) $mov['estado'] === 0);

// Formateo de Fechas en Español
$fecha = new DateTime($mov['fecha'] ?? 'now');
$mesIngles = $fecha->format('M');
$fechaFormat = $fecha->format('d ') . ($meses_es[$mesIngles] ?? $mesIngles) . $fecha->format(' Y');
$horaFormat = $fecha->format('h:i A');

// Generador de Iniciales (Avatar)
$nombreEntidad = $mov['entidad'] ?? ($esVenta ? 'Cliente' : 'Proveedor');
$iniciales = strtoupper(substr($nombreEntidad, 0, 2));

// Código de la operación y enlace al detalle según el origen
$idOperacion = (int) ($mov['id'] ?? 0);
if ($esVenta) {
    $codigoOperacion = $mov['documento'] ?? numeroOperacion('FAC', $idOperacion);
    $refOperacion    = 'Venta ' . numeroOperacion('VEN', $idOperacion);
    $urlDetalle      = urlAccion('venta', 'verDetalleVenta', ['id' => $idOperacion]);
    $colorDoc        = 'has-text-link';
    $colorAvatar     = 'has-background-success-light has-text-success';
} else {
    $codigoOperacion = numeroOperacion('COMP', $idOperacion);
    $refOperacion    = 'Ref: Factura ' . ($mov['documento'] ?? 'S/N');
    $urlDetalle      = urlAccion('movimiento', 'verDetalleCompra', ['id' => $idOperacion]);
    $colorDoc        = 'has-text-success';
    $colorAvatar     = 'has-background-link-light has-text-link';
}
?>
<tr>
    <!-- Col 1: Fecha / Hora -->
    <td class="is-vcentered px-4">
        <p class="has-text-weight-bold has-text-dark mb-0"><?= $fechaFormat ?></p>
        <p class="is-size-7 has-text-grey is-flex is-align-items-center mt-1">
            <span class="material-symbols-outlined is-size-7 mr-1">schedule</span> <?= $horaFormat ?>
        </p>
    </td>

    <!-- Col 2: Operación -->
    <td class="is-vcentered px-4">
        <?php if ($esVenta): ?>
            <span class="tag is-success is-light has-text-weight-bold">
                <span class="material-symbols-outlined is-size-6 mr-1">north_east</span> VENTA
            </span>
        <?php else: ?>
            <span class="tag is-info is-light has-text-weight-bold">
                <span class="material-symbols-outlined is-size-6 mr-1">south_east</span> COMPRA
            </span>
        <?php endif; ?>
        <?php if ($anulado): ?>
            <br><span class="tag is-danger is-light is-small mt-1" style="font-size: 0.65rem;">ANULADA</span>
        <?php endif; ?>
    </td>

    <!-- Col 3: Documento / Ref -->
    <td class="is-vcentered px-4">
        <a href="<?= esc($urlDetalle) ?>" class="has-text-weight-bold <?= $colorDoc ?> mb-0 is-block" style="font-family: monospace;">
            <?= esc($codigoOperacion) ?>
        </a>
        <p class="is-size-7 has-text-grey mt-1"><?= esc($refOperacion) ?></p>
    </td>

    <!-- Col 4: Entidad / Usuario -->
    <td class="is-vcentered px-4">
        <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
            <div class="<?= $colorAvatar ?> has-text-weight-bold is-flex is-align-items-center is-justify-content-center" style="width: 32px; height: 32px; border-radius: 50%;">
                <?= esc($iniciales) ?>
            </div>
            <div>
                <p class="has-text-weight-bold has-text-dark mb-0"><?= esc($nombreEntidad) ?></p>
                <p class="is-size-7 has-text-grey is-flex is-align-items-center mt-1">
                    <?= esc($mov['identificacion'] ?? ($esVenta ? 'SIN DOCUMENTO' : 'SIN RIF')) ?> 
                    <span class="mx-1">•</span> 
                    <span class="has-text-link is-flex is-align-items-center">
                        <span class="material-symbols-outlined is-size-7 mr-1">person</span> 
                        <?= esc($mov['nombre_usuario'] ?? 'Sistema') ?>
                    </span>
                </p>
            </div>
        </div>
    </td>

    <!-- Col 5: Monto Total -->
    <td class="is-vcentered px-4 has-text-right">
        <p class="has-text-weight-bold mb-0 is-size-5 <?= $anulado ? 'has-text-grey-light' : 'has-text-dark' ?>" <?= $anulado ? 'style="text-decoration: line-through;"' : '' ?>>$<?= number_format((float)($mov['total'] ?? 0), 2, ',', '.') ?></p>
        <?php $tasaFila = (float) ($mov['tasa_cambio'] ?? 0); ?>
        <?php if ($tasaFila > 0): ?>
            <p class="is-size-7 has-text-weight-bold has-text-grey-dark mb-0" title="Tasa BCV de ese día: <?= esc(number_format($tasaFila, 4, ',', '.')) ?>">
                Bs <?= number_format((float)($mov['total'] ?? 0) * $tasaFila, 2, ',', '.') ?>
            </p>
        <?php endif; ?>
        <p class="is-size-7 has-text-grey mt-1"><?= $esVenta ? 'USD · con IVA' : 'USD Base' ?></p>
    </td>

    <!-- Col 6: Estado Kardex -->
    <td class="is-vcentered px-4 has-text-centered">
        <?php if ($anulado): ?>
            <span class="tag is-rounded is-danger is-light">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #f14668; margin-right: 6px;"></span> Revertido
            </span>
        <?php else: ?>
            <span class="tag is-rounded is-success is-light">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #48c774; margin-right: 6px;"></span> Asentado
            </span>
        <?php endif; ?>
    </td>

    <!-- Col 7: Acciones -->
    <td class="is-vcentered px-4 has-text-right">
        <div class="buttons is-right mb-0">
            <a href="<?= esc($urlDetalle) ?>" class="button is-small is-light" title="Ver Detalle">
                <span class="icon has-text-grey"><span class="material-symbols-outlined">visibility</span></span>
            </a>
            <button class="button is-small is-light" title="Descargar Comprobante" disabled>
                <span class="icon has-text-grey-light"><span class="material-symbols-outlined">description</span></span>
            </button>
        </div>
    </td>
</tr>
