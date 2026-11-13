<?php
/**
 * Panel de la tasa de cambio BCV (Bs por 1 USD) para Compras y Ventas.
 * Se incluye con:  <?php require __DIR__ . '/../layouts/tasaBcv.php'; ?>
 * Necesita cargar assets/js/tasaBcv.js (lo llena y lo mantiene al día con fetch).
 * Expone window.BonanzaTasa para que el JavaScript de cada pantalla muestre montos en Bs.
 */
$bcvPuedeFijar = tieneRol(['admin']);
?>
<div id="panelTasaBcv" class="box is-shadowless mb-5 p-3 is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap"
     style="border: 1px solid #e2e8f0; border-radius: 12px; gap: 0.75rem;"
     data-url-obtener="<?= esc(urlAccion('tasa', 'obtener')) ?>"
     data-url-fijar="<?= esc(urlAccion('tasa', 'guardarManual')) ?>"
     data-csrf="<?= esc(tokenCsrf()) ?>"
     data-puede-fijar="<?= $bcvPuedeFijar ? '1' : '0' ?>">
    <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
        <div class="is-flex is-align-items-center is-justify-content-center has-background-link-light has-text-link" style="width: 40px; height: 40px; border-radius: 10px;">
            <span class="material-symbols-outlined">currency_exchange</span>
        </div>
        <div>
            <p class="is-size-7 has-text-grey has-text-weight-bold is-uppercase mb-0">Tasa oficial BCV (del día)</p>
            <p class="mb-0">
                <span class="has-text-weight-bold has-text-dark is-size-5 font-mono" id="bcvTasaValor">Consultando…</span>
                <span class="is-size-7 has-text-grey ml-1">Bs por 1 USD</span>
            </p>
        </div>
    </div>
    <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
        <span class="tag is-light is-rounded" id="bcvTasaEstado">—</span>
        <button type="button" class="button is-small is-light" id="bcvRefrescar" title="Volver a consultar la tasa">
            <span class="icon"><span class="material-symbols-outlined">refresh</span></span>
        </button>
        <?php if ($bcvPuedeFijar): ?>
            <button type="button" class="button is-small is-link is-light" id="bcvFijar" title="Escribir la tasa a mano">
                <span class="icon"><span class="material-symbols-outlined">edit</span></span>
                <span>Fijar manual</span>
            </button>
        <?php endif; ?>
    </div>
</div>
