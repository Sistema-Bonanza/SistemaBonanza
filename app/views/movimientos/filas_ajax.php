<?php 
/** @var array $compra */

$meses_es = ['Jan'=>'Ene', 'Feb'=>'Feb', 'Mar'=>'Mar', 'Apr'=>'Abr', 'May'=>'May', 'Jun'=>'Jun', 'Jul'=>'Jul', 'Aug'=>'Ago', 'Sep'=>'Sep', 'Oct'=>'Oct', 'Nov'=>'Nov', 'Dec'=>'Dic'];

// Formateo de Fechas en Español
$fecha = new DateTime($compra['fecha_registro'] ?? 'now');
$mesIngles = $fecha->format('M');
$fechaFormat = $fecha->format('d ') . ($meses_es[$mesIngles] ?? $mesIngles) . $fecha->format(' Y');
$horaFormat = $fecha->format('h:i A');

// Generador de Iniciales (Avatar)
$nombreProv = $compra['nombre_proveedor'] ?? 'Proveedor';
$iniciales = strtoupper(substr(esc($nombreProv), 0, 2));

// Código de Operación Basado en ID
$idCompra = $compra['id_compra'] ?? 0;
$codigoOperacion = 'COMP-' . str_pad($idCompra, 6, '0', STR_PAD_LEFT);
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
        <span class="tag is-info is-light has-text-weight-bold">
            <span class="material-symbols-outlined is-size-6 mr-1">south_east</span> COMPRA
        </span>
        <?php if(isset($compra['estado']) && (int)$compra['estado'] === 0): ?>
            <br><span class="tag is-danger is-light is-small mt-1" style="font-size: 0.65rem;">ANULADA</span>
        <?php endif; ?>
    </td>

    <!-- Col 3: Documento / Ref -->
    <td class="is-vcentered px-4">
        <a href="<?= esc(urlAccion('movimiento', 'verDetalleCompra', ['id' => $idCompra])) ?>" class="has-text-weight-bold has-text-success mb-0 is-block" style="font-family: monospace;">
            <?= esc($codigoOperacion) ?>
        </a>
        <p class="is-size-7 has-text-grey mt-1">Ref: Factura <?= esc($compra['numero_factura'] ?? 'S/N') ?></p>
    </td>

    <!-- Col 4: Entidad / Usuario -->
    <td class="is-vcentered px-4">
        <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
            <div class="has-background-link-light has-text-link has-text-weight-bold is-flex is-align-items-center is-justify-content-center" style="width: 32px; height: 32px; border-radius: 50%;">
                <?= $iniciales ?>
            </div>
            <div>
                <p class="has-text-weight-bold has-text-dark mb-0"><?= esc($nombreProv) ?></p>
                <p class="is-size-7 has-text-grey is-flex is-align-items-center mt-1">
                    <?= esc($compra['rif_proveedor'] ?? 'SIN RIF') ?> 
                    <span class="mx-1">•</span> 
                    <span class="has-text-link is-flex is-align-items-center">
                        <span class="material-symbols-outlined is-size-7 mr-1">person</span> 
                        <?= esc($compra['nombre_usuario'] ?? 'Sistema') ?>
                    </span>
                </p>
            </div>
        </div>
    </td>

    <!-- Col 5: Monto Total -->
    <td class="is-vcentered px-4 has-text-right">
        <p class="has-text-weight-bold has-text-dark mb-0 is-size-5">$<?= number_format((float)($compra['total'] ?? 0), 2, ',', '.') ?></p>
        <p class="is-size-7 has-text-grey mt-1">USD Base</p>
    </td>

    <!-- Col 6: Estado Kardex -->
    <td class="is-vcentered px-4 has-text-centered">
        <?php if(isset($compra['estado']) && (int)$compra['estado'] === 0): ?>
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
            <a href="<?= esc(urlAccion('movimiento', 'verDetalleCompra', ['id' => $idCompra])) ?>" class="button is-small is-light" title="Ver Detalle">
                <span class="icon has-text-grey"><span class="material-symbols-outlined">visibility</span></span>
            </a>
            <button class="button is-small is-light" title="Descargar Comprobante" disabled>
                <span class="icon has-text-grey-light"><span class="material-symbols-outlined">description</span></span>
            </button>
        </div>
    </td>
</tr>