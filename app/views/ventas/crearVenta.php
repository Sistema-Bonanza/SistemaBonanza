<?php
// Validaciones de seguridad y recuperación de datos previos (del código original)
$texto = function ($valor) {
    return is_string($valor) ? $valor : '';
};
$lista = function ($clave) use ($previo, $texto) {
    $crudo = (isset($previo[$clave]) && is_array($previo[$clave])) ? $previo[$clave] : [];
    return array_map($texto, $crudo);
};

$clientePrevio = $texto($previo['id_cliente'] ?? '');
$obsPrevio     = $texto($previo['observaciones'] ?? '');

$tipoPrevio = $texto($previo['tipo_pago'] ?? '');
if (!array_key_exists($tipoPrevio, TIPOS_PAGO_VENTA)) {
    $tipoPrevio = 'efectivo';
}
$pagosPrev = [];
foreach (METODOS_PAGO as $clave => $etiqueta) {
    $pagosPrev[$clave] = $texto($previo['pago_' . $clave] ?? '');
}

$idsPrev        = $lista('id_producto');
$cantidadesPrev = $lista('cantidad');
$lineasPrevias  = [];
foreach ($idsPrev as $i => $idPrev) {
    $cantPrev = $cantidadesPrev[$i] ?? '';
    if (ctype_digit($idPrev) && ctype_digit($cantPrev)) {
        $lineasPrevias[] = ['id' => (int) $idPrev, 'cantidad' => (int) $cantPrev];
    }
}

$flagsJson    = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
$catalogoJson = json_encode($catalogo, $flagsJson) ?: '[]';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta · Industrias Bonanza</title>
    
    <!-- Fuentes y Bulma CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        
        <!-- Envolvemos la alerta flash para ocultarla y que SweetAlert la atrape -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash(); ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 6rem !important;">

            <!-- ========================================== -->
            <!-- BANNER SUPERIOR -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #003899 0%, #004ac6 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">point_of_sale</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Registrar Venta / Despacho de Mercancía</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Salida y Kardex
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Genera la factura legal de venta y descuenta el stock físico del almacén en tiempo real.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-white is-outlined has-text-white style-round" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ESTATUS OPERATIVO -->
            <div class="notification is-white is-shadowless mb-5 p-3 is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; animation: pulse 2s infinite;"></span>
                    <span class="is-size-7 has-text-weight-semibold text-dark">Base de Datos Operativa: Motor de ventas activo y sincronizado</span>
                </div>
            </div>

            <form id="formVenta" action="<?= esc(urlAccion('venta', 'guardarVenta')) ?>" method="POST" data-tasa-iva="<?= esc(numeroInput($tasaIva)) ?>">
                <?= campoCsrf() ?>
                
                <!-- Truco Oculto: El input radio verdadero de la forma de pago (El JS lo controlará) -->
                <div class="is-hidden" id="radiosFormaPago">
                    <input type="radio" name="tipo_pago" value="efectivo" <?= $tipoPrevio === 'efectivo' ? 'checked' : '' ?>>
                    <input type="radio" name="tipo_pago" value="tarjeta" <?= $tipoPrevio === 'tarjeta' ? 'checked' : '' ?>>
                    <input type="radio" name="tipo_pago" value="transferencia" <?= $tipoPrevio === 'transferencia' ? 'checked' : '' ?>>
                    <input type="radio" name="tipo_pago" value="divisa" <?= $tipoPrevio === 'divisa' ? 'checked' : '' ?>>
                    <input type="radio" name="tipo_pago" value="credito" <?= $tipoPrevio === 'credito' ? 'checked' : '' ?>>
                    <input type="radio" name="tipo_pago" value="mixto" <?= $tipoPrevio === 'mixto' ? 'checked' : '' ?>>
                </div>

                <!-- ========================================== -->
                <!-- 1. CABECERA DE LA VENTA -->
                <!-- ========================================== -->
                <div class="box is-shadowless mb-5 p-5" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                    <div class="is-flex is-align-items-center is-justify-content-space-between pb-3 mb-4" style="border-bottom: 1px solid #e2e8f0;">
                        <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                            <span class="material-symbols-outlined has-text-link">badge</span>
                            <h2 class="title is-5 mb-0 has-text-dark">1. Datos del Cliente (Cabecera)</h2>
                        </div>
                        <span class="tag is-light is-danger is-small">Obligatorio (*)</span>
                    </div>

                    <div class="columns is-multiline">
                        <!-- Cliente -->
                        <div class="column is-12-mobile is-12-tablet is-6-desktop">
                            <div class="field">
                                <div class="is-flex is-justify-content-space-between">
                                    <label class="label is-size-7 has-text-grey" for="id_cliente">Cliente Registrado <span class="has-text-danger">*</span></label>
                                    <a href="<?= esc(urlAccion('cliente', 'formcrearcliente')) ?>" target="_blank" rel="noopener" class="is-size-7 has-text-link has-text-weight-semibold">¿Cliente nuevo? Registrar aquí</a>
                                </div>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select name="id_cliente" id="id_cliente" required class="has-text-weight-semibold">
                                            <option value="">Seleccione un cliente...</option>
                                            <?php foreach ($clientes as $cli): ?>
                                                <option value="<?= esc($cli['id_cliente']) ?>" <?= ($clientePrevio === (string) $cli['id_cliente']) ? 'selected' : '' ?>>
                                                    <?= esc($cli['tipo_documento'] . '-' . $cli['num_documento'] . ' · ' . $cli['nombre_razon_social']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey-light"><span class="material-symbols-outlined">person</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="column is-12-mobile is-12-tablet is-6-desktop">
                            <div class="field mb-0">
                                <label class="label is-size-7 has-text-grey" for="observaciones">Observaciones y Destino de Despacho (Opcional)</label>
                                <div class="control has-icons-left">
                                    <input type="text" name="observaciones" id="observaciones" class="input" maxlength="255" placeholder="Ej: Dirección de entrega, transporte asignado o notas..." value="<?= esc($obsPrevio) ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><span class="material-symbols-outlined">local_shipping</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- 2. DETALLE DE PRODUCTOS VENDIDOS -->
                <!-- ========================================== -->
                <div class="box is-shadowless mb-5 p-0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <div class="p-4 is-flex is-align-items-center is-justify-content-space-between has-background-white-ter" style="border-bottom: 1px solid #e2e8f0;">
                        <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                            <span class="material-symbols-outlined has-text-link">inventory_2</span>
                            <h2 class="title is-5 mb-0 has-text-dark">2. Detalle de Productos Vendidos (Salida Kardex)</h2>
                        </div>
                        <span class="tag is-info is-light has-text-weight-bold" id="contadorLineas">0 productos</span>
                    </div>

                    <!-- Buscador -->
                    <div class="p-4" style="border-bottom: 1px solid #e2e8f0;">
                    <!-- LA CLAVE ESTÁ AQUÍ: agregamos style="position: relative;" -->
                        <div class="field mb-0" style="position: relative;">
                            <label class="label is-size-7 has-text-grey" for="buscarProducto">Agregar producto por Código de Barra, SKU o Nombre:</label>
                            <div class="control has-icons-left has-icons-right">
                                <input type="text" id="buscarProducto" class="input is-medium is-link is-light" placeholder="Escribe el código SKU o nombre y presiona Enter..." autocomplete="off">
                                <span class="icon is-left has-text-link"><span class="material-symbols-outlined">search</span></span>
                                <span class="icon is-right"><span class="tag is-white" style="border: 1px solid #e2e8f0;"><span class="material-symbols-outlined is-size-6 has-text-grey">barcode_scanner</span></span></span>
                            </div>
        
                    <!-- Dropdown Flotante ajustado -->
                        <div id="resultadosProducto" class="box is-shadowless p-0" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 100; border: 1px solid #cbd5e1; display: none; max-height: 280px; overflow-y: auto; margin-top: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;"></div>
                        </div>
                    </div>

                    <!-- Tabla de Productos -->
                    <div class="table-container mb-0">
                        <table class="table is-fullwidth is-hoverable is-vcentered mb-0" id="tablaVentas">
                            <thead>
                                <tr class="is-size-7 has-text-grey is-uppercase" style="background-color: #f8fafc;">
                                    <th class="py-3 px-4 has-text-centered" style="width: 50px;">#</th>
                                    <th class="py-3 px-4">Producto Maestro</th>
                                    <th class="py-3 px-4 has-text-centered" style="width: 100px;">Disp.</th>
                                    <th class="py-3 px-4 has-text-centered" style="width: 130px;">Cantidad</th>
                                    <th class="py-3 px-4 has-text-right" style="width: 140px;">Precio Unit. ($)</th>
                                    <th class="py-3 px-4 has-text-centered" style="width: 100px;">IVA</th>
                                    <th class="py-3 px-4 has-text-right" style="width: 140px;">Subtotal ($)</th>
                                    <th class="py-3 px-4 has-text-centered" style="width: 60px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="lineasVenta" data-lineas-previas="<?= esc(json_encode($lineasPrevias)) ?>" class="is-size-6">
                                <tr id="filaVacia">
                                    <td colspan="8" class="has-text-centered has-text-grey py-5">Aún no hay productos. Búscalos arriba para agregarlos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- 3. MÉTODO DE PAGO Y TOTALES -->
                <!-- ========================================== -->
                <div class="columns is-multiline mb-5">
                    <!-- Métodos de Pago -->
                    <div class="column is-12-mobile is-7-desktop">
                        <div class="box is-shadowless mb-5 p-5 h-100" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                            <div class="is-flex is-align-items-center is-justify-content-space-between pb-3 mb-4" style="border-bottom: 1px solid #e2e8f0;">
                                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                    <span class="material-symbols-outlined has-text-link">payments</span>
                                    <h2 class="title is-5 mb-0 has-text-dark">3. Forma de Pago</h2>
                                </div>
                                <span class="is-size-7 has-text-grey">Moneda Base: ($ / USD)</span>
                            </div>

                            <div class="columns is-mobile is-multiline mb-2" id="contenedorBotonesPago">
                                <!-- Los botones UI que controlan los radio buttons ocultos -->
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="efectivo" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">payments</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Efectivo</p>
                                    </div>
                                </div>
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="tarjeta" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">credit_card</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Tarjeta</p>
                                    </div>
                                </div>
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="transferencia" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">account_balance</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Transf.</p>
                                    </div>
                                </div>
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="divisa" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">attach_money</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Divisa</p>
                                    </div>
                                </div>
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="credito" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">handshake</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Crédito</p>
                                    </div>
                                </div>
                                <div class="column is-4-mobile is-4-tablet is-4-desktop">
                                    <div class="box btn-pago-ui is-shadowless has-text-centered p-3 is-clickable" data-pago="mixto" style="border: 1px solid #e2e8f0;">
                                        <span class="material-symbols-outlined is-size-3 has-text-grey mb-1">receipt</span>
                                        <p class="has-text-weight-bold is-size-7 text-dark mb-0">Mixto</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen del desglose Mixto -->
                            <div id="resumenMixto" class="notification is-link is-light p-3 is-hidden">
                                <div class="is-flex is-justify-content-space-between is-align-items-center" style="gap: 1rem; flex-wrap: wrap;">
                                    <p id="textoResumenMixto" class="mb-0 is-size-7 has-text-weight-semibold"></p>
                                    <button type="button" class="button is-small is-link" data-modal-abrir="modalMixto">
                                        <span class="icon is-small"><span class="material-symbols-outlined">edit</span></span>
                                        <span>Editar desglose</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen y Liquidación -->
                    <div class="column is-12-mobile is-5-desktop">
                        <div class="box is-shadowless p-5 h-100 is-flex is-flex-direction-column is-justify-content-space-between" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                            <div>
                                <div class="is-flex is-align-items-center is-justify-content-space-between pb-3 mb-4" style="border-bottom: 1px solid #e2e8f0;">
                                    <div class="is-flex is-align-items-center gap-2">
                                        <span class="material-symbols-outlined has-text-link">calculate</span>
                                        <h3 class="title is-6 mb-0">Liquidación de Venta</h3>
                                    </div>
                                </div>

                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                    <span class="is-size-7 has-text-grey">Subtotal Neto Imponible:</span>
                                    <span class="has-text-weight-bold font-mono" id="totalSubtotal"><?= esc(dinero(0)) ?></span>
                                </div>

                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                                    <span class="is-size-7 has-text-grey">Impuesto IVA (<?= esc(porcentaje($tasaIva)) ?>):</span>
                                    <span class="has-text-weight-bold font-mono" id="totalIva"><?= esc(dinero(0)) ?></span>
                                </div>
                            </div>

                            <!-- Total Caja Fuerte -->
                            <div class="p-4" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 12px;">
                                <div class="is-flex is-justify-content-space-between is-align-items-center">
                                    <div>
                                        <p class="is-size-7 has-text-grey is-uppercase has-text-weight-bold mb-1">Total a Pagar</p>
                                        <p class="is-size-7 has-text-dark">Total Factura (USD)</p>
                                    </div>
                                    <div class="has-text-right">
                                        <p class="title is-3 has-text-link mb-0 font-mono" id="totalGeneral"><?= esc(dinero(0)) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- BARRA INFERIOR DE ACCIONES FLOTANTE -->
                <!-- ========================================== -->
                <div class="box is-shadowless p-4 is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap" style="border: 1px solid #e2e8f0; border-radius: 12px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); position: sticky; bottom: 20px; z-index: 30; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;">
                    
                    <div class="buttons mb-0">
                        <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-white has-text-grey">
                            <span class="icon"><span class="material-symbols-outlined">close</span></span>
                            <span>Cancelar</span>
                        </a>
                        <button type="button" class="button is-light has-text-grey" id="btnResetVenta">
                            <span class="icon"><span class="material-symbols-outlined">restart_alt</span></span>
                            <span>Limpiar Venta</span>
                        </button>
                    </div>

                    <div class="buttons mb-0">
                        <button type="submit" name="accion" value="imprimir" class="button is-link is-light has-text-weight-bold">
                            <span class="icon"><span class="material-symbols-outlined">print</span></span>
                            <span>Solo Imprimir</span>
                        </button>
                        <button type="submit" name="accion" value="guardar" class="button is-success has-text-weight-bold px-5" id="btnGuardarVenta">
                            <span class="icon"><span class="material-symbols-outlined">check_circle</span></span>
                            <span>Registrar Venta y Descontar Stock</span>
                        </button>
                    </div>
                </div>

                <!-- ===================== MODAL: PAGO MIXTO ===================== -->
                <div class="modal" id="modalMixto">
                    <div class="modal-background" data-modal-cerrar></div>
                    <div class="modal-card">
                        <header class="modal-card-head">
                            <p class="modal-card-title is-size-5 has-text-weight-bold">Desglose de Pago Mixto</p>
                            <button type="button" class="delete" aria-label="Cerrar" data-modal-cerrar></button>
                        </header>
                        <section class="modal-card-body">
                            <div class="notification is-info is-light p-3 mb-4">
                                <p class="is-size-7">Total a cubrir: <strong class="is-size-6" id="mixtoTotal"><?= esc(dinero(0)) ?></strong></p>
                            </div>
                            
                            <div class="columns is-multiline">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label is-size-7"><i class="fas fa-money-bill-wave has-text-success"></i> Efectivo</label>
                                        <input type="number" class="input" id="pago_efectivo" name="pago_efectivo" min="0" step="0.01" value="<?= esc($pagosPrev['efectivo']) ?>">
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label is-size-7"><i class="fas fa-credit-card has-text-grey"></i> Tarjeta</label>
                                        <input type="number" class="input" id="pago_tarjeta" name="pago_tarjeta" min="0" step="0.01" value="<?= esc($pagosPrev['tarjeta']) ?>">
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label is-size-7"><i class="fas fa-building-columns has-text-link"></i> Transferencia</label>
                                        <input type="number" class="input" id="pago_transferencia" name="pago_transferencia" min="0" step="0.01" value="<?= esc($pagosPrev['transferencia']) ?>">
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label is-size-7"><i class="fas fa-dollar-sign has-text-success"></i> Divisa</label>
                                        <input type="number" class="input" id="pago_divisa" name="pago_divisa" min="0" step="0.01" value="<?= esc($pagosPrev['divisa']) ?>">
                                    </div>
                                </div>
                                <div class="column is-12">
                                    <div class="field">
                                        <label class="label is-size-7"><i class="fas fa-handshake has-text-warning"></i> Crédito</label>
                                        <input type="number" class="input" id="pago_credito" name="pago_credito" min="0" step="0.01" value="<?= esc($pagosPrev['credito']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="notification is-warning is-light p-3 mt-2">
                                <p class="is-size-7 has-text-centered has-text-weight-bold" id="saldoMixto"></p>
                            </div>
                        </section>
                        <footer class="modal-card-foot is-justify-content-flex-end">
                            <button type="button" class="button is-light" data-modal-cerrar>Cerrar</button>
                            <button type="button" class="button is-success has-text-weight-bold" id="aceptarMixto">Guardar desglose</button>
                        </footer>
                    </div>
                </div>

                <!-- Plantilla de fila para Javascript -->
                <template id="plantillaLinea">
                    <tr class="fila-venta">
                        <td class="celda-num has-text-centered has-text-grey-light has-text-weight-bold"></td>
                        <td>
                            <span class="linea-codigo tag is-light font-mono has-text-weight-bold is-pulled-right"></span>
                            <div class="is-flex is-align-items-center gap-2">
                                <div class="has-background-light p-1" style="border-radius: 6px;"><span class="material-symbols-outlined is-size-5 has-text-link">inventory</span></div>
                                <div>
                                    <p class="linea-nombre has-text-weight-semibold is-size-7 mb-0"></p>
                                    <input type="hidden" name="id_producto[]" value="">
                                </div>
                            </div>
                        </td>
                        <td class="has-text-centered"><span class="celda-stock tag is-success is-light has-text-weight-bold"></span></td>
                        <td>
                            <input type="number" name="cantidad[]" class="input is-small has-text-centered input-cantidad" min="1" max="100000" step="1" placeholder="0">
                        </td>
                        <td class="celda-precio has-text-right font-mono text-dark"></td>
                        <td class="has-text-centered"><span class="celda-iva tag is-warning is-light is-small"></span></td>
                        <td class="celda-subtotal has-text-right has-text-link has-text-weight-bold font-mono text-[15px]"></td>
                        <td class="has-text-centered">
                            <button type="button" class="button is-small is-danger is-light" data-quitar>
                                <span class="icon"><span class="material-symbols-outlined">delete</span></span>
                            </button>
                        </td>
                    </tr>
                </template>

            </form>
            
            <script type="application/json" id="catalogoProductos"><?= $catalogoJson ?></script>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/crearVenta.js?v=<?= time() ?>"></script>
</body>
</html>