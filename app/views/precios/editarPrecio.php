<?php
// Variables que entrega PrecioController::formEditar():
//   $producto, $previo, $recientes, $tasaIva

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Valores iniciales: lo que el usuario escribió antes (si el servidor rechazó el envío) o lo vigente.
$valorCosto = $previo['precio_compra'] ?? numeroInput($producto['precio_compra']);
$valorVenta = $previo['precio_venta']  ?? (((float) $producto['precio_venta'] > 0) ? numeroInput($producto['precio_venta']) : '');
$ultimoCosto = ($producto['ultimo_costo_compra'] !== null && (float) $producto['ultimo_costo_compra'] > 0)
    ? (float) $producto['ultimo_costo_compra'] : null;
$aplicaIva = ((int) $producto['aplica_iva'] === 1);
$confirmoPrevio = (($previo['confirmar_perdida'] ?? '') === '1');

// ---- Datos adicionales que usa el diseño nuevo ----
$inicialProducto = mb_strtoupper(mb_substr($producto['nombre'], 0, 1, 'UTF-8'), 'UTF-8');

// Margen mínimo de referencia: el mismo que usa el listado de precios para marcar «Margen bajo».
$margenMinimo = class_exists('PrecioModel', false) ? (float) PrecioModel::MARGEN_MINIMO : 10.0;

// El stock solo se muestra si la consulta del modelo lo trae (hoy obtenerProducto() no lo incluye).
$stockActual = isset($producto['stock_actual']) ? (int) $producto['stock_actual'] : null;

// ¿Los valores iniciales ya implican vender con pérdida? (pasa cuando el servidor rechazó el envío).
// Así el aviso y su casilla aparecen aunque el JavaScript no cargue.
$hayPerdidaInicial = ((float) $valorVenta > 0 && (float) $valorCosto > 0 && (float) $valorVenta < (float) $valorCosto);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Precio · Bonanza</title>

    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <!-- Mensajes del servidor: el JS los lee y los muestra con SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========== BANNER ========== -->
            <div class="box mb-6" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">

                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white" style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-2">sell</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white;">
                                    Control de Precios
                                </span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Actualizar Precio</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Define el costo y el precio de venta. Cada cambio queda registrado en el historial con fecha y usuario.
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-white is-outlined" style="border-radius: 8px; border-color: rgba(255,255,255,0.3); color: white;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- ========== GRID DE 2 COLUMNAS ========== -->
            <div class="columns is-variable is-5">

                <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                <div class="column is-8">

                    <div class="box is-shadowless p-0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white;">

                        <!-- Encabezado de la tarjeta -->
                        <div class="has-background-link px-5 py-3 is-flex is-align-items-center is-justify-content-space-between">
                            <div class="is-flex is-align-items-center" style="gap: 8px;">
                                <span class="material-symbols-outlined has-text-white">price_change</span>
                                <h2 class="title is-6 mb-0 has-text-white">Costo y Precio de Venta</h2>
                            </div>
                        </div>

                        <div class="p-5">

                            <!-- Ficha del producto -->
                            <div class="is-flex is-align-items-center is-justify-content-space-between p-3 mb-5" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                                <div class="is-flex is-align-items-center" style="gap: 15px;">
                                    <div class="is-flex is-align-items-center is-justify-content-center has-text-white has-text-weight-bold is-size-4 is-flex-shrink-0" style="width: 48px; height: 48px; border-radius: 10px; background: #3b82f6;">
                                        <?= esc($inicialProducto) ?>
                                    </div>
                                    <div class="is-flex is-flex-direction-column">
                                        <div class="is-flex is-align-items-center" style="gap: 8px;">
                                            <h3 class="title is-5 mb-0 has-text-dark"><?= esc($producto['nombre']) ?></h3>
                                            <span class="tag is-info is-light is-small has-text-weight-bold"><?= esc($producto['codigo']) ?></span>
                                        </div>
                                        <div class="is-flex is-align-items-center is-size-7 mt-1" style="gap: 8px;">
                                            <span class="has-text-grey-dark">Categoría: <strong><?= esc($producto['nombre_categoria']) ?></strong></span>
                                            <span class="has-text-grey-light">•</span>
                                            <?php if ($aplicaIva): ?>
                                                <span class="has-text-link has-text-weight-medium">Aplica IVA (<?= esc((float) $tasaIva) ?> %)</span>
                                            <?php else: ?>
                                                <span class="has-text-grey">Exento de IVA</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($stockActual !== null): ?>
                                    <div class="has-text-right is-hidden-mobile">
                                        <span class="is-size-7 has-text-grey is-uppercase">Stock</span><br>
                                        <span class="title is-5 has-text-dark m-0"><?= esc($stockActual) ?> und.</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ========== FORMULARIO ========== -->
                            <form action="<?= esc(urlAccion('precio', 'guardarPrecio')) ?>" method="POST" id="form-precio" autocomplete="off">

                                <?= campoCsrf() ?>
                                <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
                                <!-- Lo que el usuario VIO al abrir el formulario: el servidor lo compara para detectar
                                     que otra persona cambió el precio mientras tanto. -->
                                <input type="hidden" name="precio_compra_actual" value="<?= esc(numeroInput($producto['precio_compra'])) ?>">
                                <input type="hidden" name="precio_venta_actual"  value="<?= esc(numeroInput($producto['precio_venta'])) ?>">

                                <h4 class="is-size-7 has-text-weight-bold has-text-grey is-uppercase mb-3">Precios por unidad</h4>

                                <div class="columns is-variable is-4 mb-2">
                                    <!-- Costo -->
                                    <div class="column is-half">
                                        <div class="field">
                                            <label class="label is-size-7 mb-1 has-text-grey-dark" for="precio_compra">
                                                Costo de compra ($) <span class="has-text-danger">*</span>
                                            </label>
                                            <div class="control has-icons-left">
                                                <input type="number" id="precio_compra" name="precio_compra" class="input has-text-weight-semibold"
                                                       step="0.01" min="0" max="99999999.99" required
                                                       value="<?= esc($valorCosto) ?>" style="border-radius: 8px;">
                                                <span class="icon is-small is-left has-text-grey"><span class="material-symbols-outlined">attach_money</span></span>
                                            </div>
                                            <p class="help has-text-grey">Puede ser 0 si aún no lo conoces.</p>
                                        </div>
                                    </div>
                                    <!-- Precio de venta -->
                                    <div class="column is-half">
                                        <div class="field">
                                            <label class="label is-size-7 mb-1 has-text-grey-dark" for="precio_venta">
                                                Precio de venta ($) <span class="has-text-danger">*</span>
                                            </label>
                                            <div class="control has-icons-left">
                                                <input type="number" id="precio_venta" name="precio_venta" class="input has-text-weight-bold has-text-link"
                                                       step="0.01" min="0.01" max="99999999.99" required
                                                       value="<?= esc($valorVenta) ?>" placeholder="0.00" style="border-radius: 8px;">
                                                <span class="icon is-small is-left has-text-link"><span class="material-symbols-outlined">attach_money</span></span>
                                            </div>
                                            <p class="help has-text-grey">Debe ser mayor que 0.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Asistente de cálculo -->
                                <div class="box is-shadowless p-4 mb-5" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px;">
                                    <div class="is-flex is-align-items-center is-justify-content-space-between mb-3 pb-3" style="border-bottom: 1px solid #e2e8f0;">
                                        <div class="is-flex is-align-items-center" style="gap: 8px;">
                                            <span class="material-symbols-outlined is-size-5 has-text-link">info</span>
                                            <span class="is-size-7 has-text-grey-dark">Último costo de compra:
                                                <?php if ($ultimoCosto !== null): ?>
                                                    <strong class="has-text-dark"><?= esc(dinero($ultimoCosto)) ?></strong>
                                                <?php else: ?>
                                                    <span class="has-text-grey-light">sin compras registradas</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if ($ultimoCosto !== null): ?>
                                            <button type="button" id="btn-usar-costo" class="button is-small is-info is-light"
                                                    data-costo="<?= esc(numeroInput($ultimoCosto)) ?>">Usar este costo</button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="field is-grouped is-grouped-multiline is-align-items-center mb-0">
                                        <div class="control">
                                            <label class="label is-size-7 has-text-grey-dark" for="porc-ganancia" style="margin-bottom: 0;">Calcular el precio con una ganancia sobre el costo de:</label>
                                        </div>
                                        <div class="control has-icons-right">
                                            <input class="input is-small" type="number" id="porc-ganancia" step="0.01" min="0" max="1000" placeholder="25" style="width: 100px;">
                                            <span class="icon is-small is-right"><span class="material-symbols-outlined" style="font-size: 16px;">percent</span></span>
                                        </div>
                                        <div class="control">
                                            <button type="button" id="btn-calcular" class="button is-small is-link is-light">Calcular precio</button>
                                        </div>
                                    </div>
                                    <?php if ($ultimoCosto !== null): ?>
                                        <p class="help has-text-grey mt-2">Si el costo está en 0, se usará el último costo de compra.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Resultado en vivo -->
                                <div class="columns is-mobile mb-4">
                                    <div class="column is-4">
                                        <div class="box is-shadowless p-3 has-text-centered" style="height: 100%; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px;">
                                            <p class="is-size-7 has-text-link-dark mb-1">Ganancia por unidad</p>
                                            <p class="title is-4 has-text-link" id="res-ganancia">—</p>
                                        </div>
                                    </div>
                                    <div class="column is-4">
                                        <div class="box is-shadowless p-3 has-text-centered" style="height: 100%; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 10px;">
                                            <p class="is-size-7 has-text-success-dark mb-1">Margen sobre la venta</p>
                                            <p class="title is-4 has-text-success mb-1" id="res-margen">—</p>
                                            <p class="is-size-7 has-text-warning-dark" id="res-margen-nota"></p>
                                        </div>
                                    </div>
                                    <div class="column is-4">
                                        <div class="box is-shadowless p-3 has-text-centered" style="height: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                                            <p class="is-size-7 has-text-grey-dark mb-1">Precio final con IVA (referencial)</p>
                                            <p class="title is-4 has-text-dark" id="res-iva"><?= $aplicaIva ? '—' : 'Exento' ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Aviso de venta bajo el costo.
                                     Visible desde el servidor cuando ya hay pérdida; el JS lo muestra u oculta después. -->
                                <div id="alerta-perdida" class="notification is-danger is-light mb-5 p-3 <?= $hayPerdidaInicial ? '' : 'is-hidden' ?>" style="border-radius: 8px;">
                                    <div class="is-flex is-align-items-center mb-2" style="gap: 8px;">
                                        <span class="material-symbols-outlined is-size-5">warning</span>
                                        <p class="is-size-7 has-text-weight-bold">El precio de venta es menor que el costo: venderías con pérdida.</p>
                                    </div>
                                    <label class="checkbox is-size-7 has-text-danger-dark ml-5">
                                        <input type="checkbox" name="confirmar_perdida" id="confirmar_perdida" value="1" <?= $confirmoPrevio ? 'checked' : '' ?>>
                                        Entiendo y quiero guardar este precio de todas formas.
                                    </label>
                                </div>

                                <!-- Acciones -->
                                <div class="is-flex is-justify-content-flex-end pt-4 mt-2" style="border-top: 1px solid #e2e8f0; gap: 10px;">
                                    <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-white" style="border: 1px solid #cbd5e1;">Volver Atrás</a>
                                    <button type="submit" class="button is-link has-text-weight-bold">
                                        <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                        <span>Guardar Precios</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: REGLAS E HISTORIAL -->
                <div class="column is-4">

                    <!-- Reglas reales de este formulario -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-4 pb-3" style="gap: 8px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-warning-light has-text-warning-dark" style="width: 32px; height: 32px; border-radius: 8px;">
                                <span class="material-symbols-outlined is-size-5">gavel</span>
                            </div>
                            <h3 class="title is-6 mb-0 has-text-dark">Reglas de este formulario</h3>
                        </div>

                        <div class="is-flex is-flex-direction-column" style="gap: 12px;">
                            <div class="notification is-warning is-light p-3 mb-0" style="border-radius: 8px;">
                                <p class="is-size-7 has-text-weight-bold has-text-warning-dark mb-1">Margen mínimo de referencia: <?= esc($margenMinimo) ?> %</p>
                                <p class="is-size-7 has-text-warning-dark" style="line-height: 1.4;">Los productos con menos margen se marcan como «Margen bajo» en el listado de precios. No impide guardar.</p>
                            </div>

                            <div class="is-flex is-align-items-start" style="gap: 8px;">
                                <span class="material-symbols-outlined is-size-6 has-text-success mt-1">check_circle</span>
                                <p class="is-size-7 has-text-grey-dark"><strong class="has-text-dark">Venta bajo el costo:</strong> antes de guardar se pide confirmar que quieres vender con pérdida.</p>
                            </div>

                            <div class="is-flex is-align-items-start" style="gap: 8px;">
                                <span class="material-symbols-outlined is-size-6 has-text-success mt-1">check_circle</span>
                                <p class="is-size-7 has-text-grey-dark"><strong class="has-text-dark">Cambios simultáneos:</strong> si otra persona modifica estos precios mientras editas, el sistema te avisa y no los sobrescribe.</p>
                            </div>

                            <div class="is-flex is-align-items-start" style="gap: 8px;">
                                <span class="material-symbols-outlined is-size-6 has-text-success mt-1">check_circle</span>
                                <p class="is-size-7 has-text-grey-dark"><strong class="has-text-dark">Historial:</strong> cada cambio guarda el precio anterior y el nuevo, con fecha y usuario.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Últimos cambios de este producto -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-4 pb-3" style="gap: 8px; border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center is-justify-content-center has-background-dark has-text-white" style="width: 32px; height: 32px; border-radius: 8px;">
                                <span class="material-symbols-outlined is-size-6">history</span>
                            </div>
                            <h3 class="title is-6 mb-0 has-text-dark">Últimos cambios de este producto</h3>
                        </div>

                        <?php if (!empty($recientes)): ?>
                            <div class="is-flex is-flex-direction-column" style="gap: 10px;">
                                <?php foreach ($recientes as $h): ?>
                                    <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                            <span class="is-size-7 has-text-weight-bold has-text-dark"><?= esc(formatoFecha($h['fecha'], true)) ?></span>
                                            <span class="tag is-white is-small" style="border: 1px solid #cbd5e1;"><?= esc($h['nombre_usuario']) ?></span>
                                        </div>
                                        <p class="is-size-7 has-text-grey">
                                            Costo: <?= esc(dinero($h['precio_compra_anterior'])) ?> → <strong class="has-text-dark"><?= esc(dinero($h['precio_compra_nuevo'])) ?></strong>
                                        </p>
                                        <p class="is-size-7 has-text-grey mt-1">
                                            Venta: <?= esc(dinero($h['precio_venta_anterior'])) ?> → <strong class="has-text-link"><?= esc(dinero($h['precio_venta_nuevo'])) ?></strong>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="has-text-centered mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
                                <a href="<?= esc(urlAccion('precio', 'historial', ['id_producto' => $producto['id_producto']])) ?>" class="is-size-7 has-text-link has-text-weight-medium">
                                    Ver historial completo
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="notification is-light has-text-centered is-size-7 p-4 mb-0">
                                Este producto aún no tiene cambios de precio registrados.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Configuración para el JavaScript: se define ANTES de cargar editarPrecio.js -->
    <script>
        window.bonanzaConfig = {
            tasaIva: <?= json_encode((float) $tasaIva) ?>,
            aplicaIva: <?= $aplicaIva ? 'true' : 'false' ?>,
            margenMinimo: <?= json_encode($margenMinimo) ?>
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/editarPrecio.js?v=<?= time() ?>"></script>
</body>
</html>