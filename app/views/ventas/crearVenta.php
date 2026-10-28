<?php
// Variables que entrega VentaController::crearVenta():
//   $clientes, $catalogo, $previo, $tasaIva

// $previo trae lo que el usuario escribió si el servidor rechazó el envío anterior.
// Todo se normaliza a texto: un atacante puede haber enviado arreglos en lugar de valores.
$texto = function ($valor) {
    return is_string($valor) ? $valor : '';
};
$lista = function ($clave) use ($previo, $texto) {
    $crudo = (isset($previo[$clave]) && is_array($previo[$clave])) ? $previo[$clave] : [];
    return array_map($texto, $crudo);
};

$clientePrevio = $texto($previo['id_cliente'] ?? '');
$obsPrevio     = $texto($previo['observaciones'] ?? '');

// Forma de pago: la anterior si era válida; si no, efectivo.
$tipoPrevio = $texto($previo['tipo_pago'] ?? '');
if (!array_key_exists($tipoPrevio, TIPOS_PAGO_VENTA)) {
    $tipoPrevio = 'efectivo';
}
$pagosPrev = [];
foreach (METODOS_PAGO as $clave => $etiqueta) {
    $pagosPrev[$clave] = $texto($previo['pago_' . $clave] ?? '');
}

// Líneas anteriores: el JavaScript las vuelve a dibujar en la tabla.
$idsPrev        = $lista('id_producto');
$cantidadesPrev = $lista('cantidad');
$lineasPrevias  = [];
foreach ($idsPrev as $i => $idPrev) {
    $cantPrev = $cantidadesPrev[$i] ?? '';
    if (ctype_digit($idPrev) && ctype_digit($cantPrev)) {
        $lineasPrevias[] = ['id' => (int) $idPrev, 'cantidad' => (int) $cantPrev];
    }
}

// JSON seguro para incrustarlo en la página (HEX_TAG evita que un nombre con </script> rompa el HTML).
$flagsJson    = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
$catalogoJson = json_encode($catalogo, $flagsJson) ?: '[]';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta · Bonanza</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/ventas.css" />
</head>
<body>

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <div class="container is-max-widescreen">

            <!-- CABECERA DE LA PÁGINA -->
            <div class="level mb-6">
                <div class="level-left">
                    <div>
                        <h1 class="title is-4 mb-1">Registrar Venta</h1>
                        <p class="has-text-grey mt-1">Genera la factura y descuenta el stock del almacén (Kardex)</p>
                    </div>
                </div>
                <div class="level-right">
                    <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-light">
                        <span class="icon is-small"><i class="fas fa-arrow-left"></i></span>
                        <span>Volver al listado</span>
                    </a>
                </div>
            </div>

            <!-- MENSAJES (errores de validación, avisos) -->
            <?= mostrarFlash() ?>

            <form id="formVenta" action="<?= esc(urlAccion('venta', 'guardarVenta')) ?>" method="POST"
                  data-tasa-iva="<?= esc(numeroInput($tasaIva)) ?>">
                <?= campoCsrf() ?>

                <!-- 1. DATOS DE LA VENTA -->
                <div class="box mb-5">
                    <h2 class="title is-5 mb-4 pb-2" style="border-bottom: 1px solid #ededed;">Datos de la Venta (Cabecera)</h2>

                    <div class="columns is-multiline">
                        <!-- Cliente -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label has-text-grey" for="id_cliente">Cliente</label>
                                <div class="control has-icons-left is-expanded">
                                    <div class="select is-fullwidth">
                                        <select name="id_cliente" id="id_cliente" required class="has-text-weight-semibold">
                                            <option value="">Seleccione un cliente...</option>
                                            <?php foreach ($clientes as $cli): ?>
                                                <option value="<?= esc($cli['id_cliente']) ?>"
                                                    <?= ($clientePrevio === (string) $cli['id_cliente']) ? 'selected' : '' ?>>
                                                    <?= esc($cli['tipo_documento'] . '-' . $cli['num_documento'] . ' · ' . $cli['nombre_razon_social']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-user"></i></span>
                                </div>
                                <p class="help">
                                    ¿Cliente nuevo?
                                    <a href="<?= esc(urlAccion('cliente', 'formcrearcliente')) ?>" target="_blank" rel="noopener">Regístralo aquí</a>
                                    (se abre en otra pestaña) y luego recarga esta página.
                                </p>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label has-text-grey" for="observaciones">Observaciones <span class="has-text-weight-normal">(opcional)</span></label>
                                <div class="control has-icons-left">
                                    <input type="text" name="observaciones" id="observaciones" class="input"
                                           maxlength="255" placeholder="Ej: Entrega en la sucursal de Valencia"
                                           value="<?= esc($obsPrevio) ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-comment"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PRODUCTOS: BUSCADOR + TABLA SIN LÍMITE DE FILAS -->
                <div class="box p-0 mb-5">
                    <div class="level px-4 py-3 has-background-white-ter mb-0" style="border-bottom: 1px solid #ededed; border-radius: 6px 6px 0 0;">
                        <div class="level-left">
                            <h2 class="has-text-weight-bold is-size-5">Productos Vendidos (Detalle)</h2>
                        </div>
                        <div class="level-right">
                            <span class="tag is-info is-light has-text-weight-bold" id="contadorLineas">0 productos</span>
                        </div>
                    </div>

                    <!-- Buscador -->
                    <div class="buscador-productos px-4 py-3">
                        <label class="label is-small has-text-grey" for="buscarProducto">Buscar producto</label>
                        <div class="control has-icons-left">
                            <input type="text" id="buscarProducto" class="input is-medium" autocomplete="off"
                                   placeholder="Escribe el código o el nombre y presiona Enter para agregarlo"
                                   role="combobox" aria-expanded="false" aria-controls="resultadosProducto" aria-autocomplete="list">
                            <span class="icon is-left has-text-grey-light"><i class="fas fa-magnifying-glass"></i></span>
                        </div>
                        <div id="resultadosProducto" class="resultados-productos" role="listbox" hidden></div>
                        <p class="help" id="avisoBuscador">Con un lector de código de barras, escanea y el producto se agrega solo.</p>
                    </div>

                    <div class="table-container mb-0">
                        <table class="table tabla-lineas is-fullwidth is-hoverable is-vcentered mb-0">
                            <thead class="has-background-white-ter">
                                <tr>
                                    <th class="has-text-centered" style="width: 50px;">#</th>
                                    <th>Producto</th>
                                    <th class="has-text-centered" style="width: 100px;">Disponible</th>
                                    <th class="has-text-centered" style="width: 130px;">Cantidad (unds.)</th>
                                    <th class="has-text-right" style="width: 130px;">Precio unit.</th>
                                    <th class="has-text-centered" style="width: 90px;">IVA</th>
                                    <th class="has-text-right" style="width: 140px;">Subtotal</th>
                                    <th style="width: 50px;"><span class="is-sr-only">Quitar</span></th>
                                </tr>
                            </thead>
                            <tbody id="lineasVenta" data-lineas-previas="<?= esc(json_encode($lineasPrevias)) ?>">
                                <tr id="filaVacia">
                                    <td colspan="8" class="has-text-centered has-text-grey py-5">
                                        Aún no hay productos. Búscalos arriba para cargarlos a la venta.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="help px-4 py-3 mb-0" style="border-top: 1px solid #ededed;">
                        El precio sale de <strong>Control de Precios</strong> y no se cambia aquí. Los productos agotados o sin precio no se pueden agregar.
                        Los montos de esta pantalla son una vista previa: el sistema los recalcula al guardar.
                    </p>
                </div>

                <!-- 3. FORMA DE PAGO -->
                <div class="box mb-5">
                    <h2 class="title is-5 mb-4 pb-2" style="border-bottom: 1px solid #ededed;">Forma de Pago</h2>

                    <div class="barra-pago" role="radiogroup" aria-label="Forma de pago">
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="efectivo" <?= $tipoPrevio === 'efectivo' ? 'checked' : '' ?>>
                            <span><i class="fas fa-money-bill-wave"></i> Efectivo</span>
                        </label>
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="tarjeta" <?= $tipoPrevio === 'tarjeta' ? 'checked' : '' ?>>
                            <span><i class="fas fa-credit-card"></i> Tarjeta</span>
                        </label>
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="transferencia" <?= $tipoPrevio === 'transferencia' ? 'checked' : '' ?>>
                            <span><i class="fas fa-building-columns"></i> Transferencia</span>
                        </label>
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="divisa" <?= $tipoPrevio === 'divisa' ? 'checked' : '' ?>>
                            <span><i class="fas fa-dollar-sign"></i> Divisa</span>
                        </label>
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="credito" <?= $tipoPrevio === 'credito' ? 'checked' : '' ?>>
                            <span><i class="fas fa-handshake"></i> Crédito</span>
                        </label>
                        <label class="opcion-pago">
                            <input type="radio" name="tipo_pago" value="mixto" <?= $tipoPrevio === 'mixto' ? 'checked' : '' ?>>
                            <span><i class="fas fa-coins"></i> Pago Mixto</span>
                        </label>
                    </div>

                    <!-- Resumen del desglose (solo con Pago Mixto) -->
                    <div id="resumenMixto" class="resumen-mixto mt-4" hidden>
                        <div class="is-flex is-justify-content-space-between is-align-items-center" style="gap: 1rem; flex-wrap: wrap;">
                            <p id="textoResumenMixto" class="mb-0"></p>
                            <button type="button" class="button is-small is-link is-light" data-modal-abrir="modalMixto">
                                <span class="icon is-small"><i class="fas fa-pen"></i></span>
                                <span>Editar desglose</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. TOTALES Y BOTONES -->
                <div class="box mb-5">
                    <div class="totales-venta">
                        <div class="fila-total">
                            <span class="has-text-grey">Subtotal</span>
                            <span id="totalSubtotal"><?= esc(dinero(0)) ?></span>
                        </div>
                        <div class="fila-total">
                            <span class="has-text-grey">IVA (<?= esc(porcentaje($tasaIva)) ?>)</span>
                            <span id="totalIva"><?= esc(dinero(0)) ?></span>
                        </div>
                        <div class="fila-total total-final">
                            <span>Total</span>
                            <span id="totalGeneral"><?= esc(dinero(0)) ?></span>
                        </div>
                    </div>
                </div>

                <div class="is-flex is-justify-content-flex-end" style="gap: 1rem; flex-wrap: wrap;">
                    <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-ghost has-text-grey">Cancelar</a>

                    <button type="submit" name="accion" value="imprimir" class="button is-link is-light has-text-weight-bold">
                        <span class="icon is-small"><i class="fas fa-print"></i></span>
                        <span>Registrar e imprimir</span>
                    </button>

                    <button type="submit" name="accion" value="guardar" class="button is-success has-text-weight-bold px-6">
                        <span class="icon is-small"><i class="fas fa-check-double"></i></span>
                        <span>Registrar venta y descontar stock</span>
                    </button>
                </div>

                <!-- ===================== MODAL: PAGO MIXTO ===================== -->
                <!-- Va dentro del formulario: sus campos se envían junto con la venta. -->
                <div class="modal" id="modalMixto">
                    <div class="modal-background" data-modal-cerrar></div>
                    <div class="modal-card modal-pago" role="dialog" aria-modal="true" aria-labelledby="tituloMixto">
                        <header class="modal-card-head">
                            <p class="modal-card-title" id="tituloMixto">Finalizar venta · Pago mixto</p>
                            <button type="button" class="delete is-large" aria-label="Cerrar" data-modal-cerrar></button>
                        </header>

                        <section class="modal-card-body">
                            <p class="mb-4">
                                Total de la venta: <strong id="mixtoTotal"><?= esc(dinero(0)) ?></strong>.
                                Indica cuánto se pagó con cada forma de pago; deja vacías las que no se usaron.
                            </p>

                            <div class="campos-pago">
                                <div class="campo-pago">
                                    <label for="pago_efectivo"><i class="fas fa-money-bill-wave"></i> Efectivo</label>
                                    <input type="number" class="input" id="pago_efectivo" name="pago_efectivo"
                                           min="0" step="0.01" inputmode="decimal" placeholder="0.00" value="<?= esc($pagosPrev['efectivo']) ?>">
                                </div>
                                <div class="campo-pago">
                                    <label for="pago_tarjeta"><i class="fas fa-credit-card"></i> Tarjeta</label>
                                    <input type="number" class="input" id="pago_tarjeta" name="pago_tarjeta"
                                           min="0" step="0.01" inputmode="decimal" placeholder="0.00" value="<?= esc($pagosPrev['tarjeta']) ?>">
                                </div>
                                <div class="campo-pago">
                                    <label for="pago_transferencia"><i class="fas fa-building-columns"></i> Transferencia</label>
                                    <input type="number" class="input" id="pago_transferencia" name="pago_transferencia"
                                           min="0" step="0.01" inputmode="decimal" placeholder="0.00" value="<?= esc($pagosPrev['transferencia']) ?>">
                                </div>
                                <div class="campo-pago">
                                    <label for="pago_divisa"><i class="fas fa-dollar-sign"></i> Divisa</label>
                                    <input type="number" class="input" id="pago_divisa" name="pago_divisa"
                                           min="0" step="0.01" inputmode="decimal" placeholder="0.00" value="<?= esc($pagosPrev['divisa']) ?>">
                                </div>
                                <div class="campo-pago">
                                    <label for="pago_credito"><i class="fas fa-handshake"></i> Crédito</label>
                                    <input type="number" class="input" id="pago_credito" name="pago_credito"
                                           min="0" step="0.01" inputmode="decimal" placeholder="0.00" value="<?= esc($pagosPrev['credito']) ?>">
                                </div>
                            </div>

                            <fieldset class="saldo-mixto mt-5" id="cajaSaldoMixto">
                                <legend>No hay vuelto en pagos mixtos</legend>
                                <p class="saldo-valor" id="saldoMixto" aria-live="polite"></p>
                            </fieldset>
                        </section>

                        <footer class="modal-card-foot is-justify-content-flex-end">
                            <button type="button" class="button" data-modal-cerrar>Cerrar</button>
                            <button type="button" class="button is-success has-text-weight-bold" id="aceptarMixto">
                                <span class="icon is-small"><i class="fas fa-check"></i></span>
                                <span>Aceptar desglose</span>
                            </button>
                        </footer>
                    </div>
                </div>

                <!-- Fila de la tabla de productos (el JavaScript la copia por cada producto agregado) -->
                <template id="plantillaLinea">
                    <tr class="fila-venta">
                        <td class="celda-num has-text-centered has-text-grey-light has-text-weight-bold"></td>
                        <td>
                            <span class="linea-nombre has-text-weight-semibold"></span><br>
                            <span class="linea-codigo is-size-7 has-text-grey"></span>
                            <input type="hidden" name="id_producto[]" value="">
                        </td>
                        <td class="celda-stock"></td>
                        <td>
                            <input type="number" name="cantidad[]" class="input is-small has-text-centered"
                                   min="1" max="100000" step="1" placeholder="0" aria-label="Cantidad">
                        </td>
                        <td class="celda-precio"></td>
                        <td class="celda-iva"></td>
                        <td class="celda-subtotal"></td>
                        <td class="has-text-centered">
                            <button type="button" class="delete" data-quitar aria-label="Quitar producto"></button>
                        </td>
                    </tr>
                </template>

            </form>

            <!-- Catálogo para el buscador (datos, no código) -->
            <script type="application/json" id="catalogoProductos"><?= $catalogoJson ?></script>
        </div>
    </main>

    <script src="assets/js/venta.js"></script>
</body>
</html>
