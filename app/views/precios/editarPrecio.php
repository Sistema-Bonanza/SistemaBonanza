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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Precio · Bonanza</title>

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/precios.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main">

        <div class="welcome-card">
            <h1>💲 Actualizar Precio</h1>
            <p>Define el costo y el precio de venta. Cada cambio queda registrado en el historial con fecha y usuario.</p>
        </div>

        <?= mostrarFlash() ?>

        <div class="form-wrapper">
            <div class="form-header">Costo y Precio de Venta</div>

            <!-- Ficha del producto -->
            <div class="product-card">
                <div class="product-avatar">
                    <?= esc(mb_strtoupper(mb_substr($producto['nombre'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                </div>
                <div>
                    <h2><?= esc($producto['nombre']) ?></h2>
                    <div class="meta">
                        <span class="badge badge-code"><?= esc($producto['codigo']) ?></span>
                        <span><?= esc($producto['nombre_categoria']) ?></span>
                        <?php if ($aplicaIva): ?>
                            <span class="badge badge-info">Aplica IVA</span>
                        <?php else: ?>
                            <span class="badge badge-neutral">Exento de IVA</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <form action="<?= esc(urlAccion('precio', 'guardarPrecio')) ?>" method="POST" class="form-body" id="form-precio">

                <?= campoCsrf() ?>
                <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
                <!-- Lo que el usuario VIO al abrir el formulario: el servidor lo compara para detectar
                     que otra persona cambió el precio mientras tanto. -->
                <input type="hidden" name="precio_compra_actual" value="<?= esc(numeroInput($producto['precio_compra'])) ?>">
                <input type="hidden" name="precio_venta_actual"  value="<?= esc(numeroInput($producto['precio_venta'])) ?>">

                <h4 class="form-section-title">Precios por unidad</h4>

                <div class="grid-2-cols">
                    <div class="form-group">
                        <label for="precio_compra">Costo de compra ($) <span class="required">*</span></label>
                        <input type="number" id="precio_compra" name="precio_compra" class="form-control"
                               step="0.01" min="0" max="99999999.99" required
                               value="<?= esc($valorCosto) ?>">
                        <span class="field-hint">Puede ser 0 si aún no lo conoces.</span>
                    </div>
                    <div class="form-group">
                        <label for="precio_venta">Precio de venta ($) <span class="required">*</span></label>
                        <input type="number" id="precio_venta" name="precio_venta" class="form-control"
                               step="0.01" min="0.01" max="99999999.99" required
                               value="<?= esc($valorVenta) ?>" placeholder="0.00">
                        <span class="field-hint">Debe ser mayor que 0.</span>
                    </div>
                </div>

                <!-- Ayudas (opcionales) -->
                <div class="helper-box">
                    <div class="helper-row">
                        <span>
                            Último costo de compra:
                            <?php if ($ultimoCosto !== null): ?>
                                <strong><?= esc(dinero($ultimoCosto)) ?></strong>
                            <?php else: ?>
                                <span class="muted">sin compras registradas</span>
                            <?php endif; ?>
                        </span>
                        <?php if ($ultimoCosto !== null): ?>
                            <button type="button" class="btn-mini" id="btn-usar-costo"
                                    data-costo="<?= esc(numeroInput($ultimoCosto)) ?>">Usar este costo</button>
                        <?php endif; ?>
                    </div>
                    <div class="helper-row">
                        <label for="porc-ganancia">Calcular el precio con una ganancia sobre el costo de</label>
                        <input type="number" id="porc-ganancia" class="form-control" step="0.01" min="0" max="1000" placeholder="25">
                        <span>%</span>
                        <button type="button" class="btn-mini" id="btn-calcular">Calcular precio</button>
                    </div>
                </div>

                <!-- Resultado en vivo -->
                <div class="live-result">
                    <div class="item"><small>Ganancia por unidad</small><strong id="res-ganancia">—</strong></div>
                    <div class="item"><small>Margen sobre la venta</small><strong id="res-margen">—</strong></div>
                    <div class="item">
                        <small>Precio final con IVA (referencial)</small>
                        <strong id="res-iva"><?= $aplicaIva ? '—' : 'Exento' ?></strong>
                    </div>
                </div>

                <!-- Aviso de venta bajo el costo. Se oculta por JS cuando no aplica;
                     sin JavaScript queda visible y el servidor valida igual. -->
                <div class="alert-loss" id="alerta-perdida">
                    ⚠️ El precio de venta es menor que el costo: venderías con pérdida.
                    <label class="checkbox-group">
                        <input type="checkbox" name="confirmar_perdida" value="1" id="confirmar_perdida"
                               <?= $confirmoPrevio ? 'checked' : '' ?>>
                        <span>Entiendo y quiero guardar este precio de todas formas.</span>
                    </label>
                </div>

                <div class="form-footer">
                    <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="btn-form btn-cancel">Volver Atrás</a>
                    <button type="submit" class="btn-form btn-submit">Guardar Precios</button>
                </div>
            </form>
        </div>

        <!-- Últimos cambios de este producto -->
        <div class="panel panel-recent">
            <h3>🕘 Últimos cambios de este producto</h3>
            <?php if (!empty($recientes)): ?>
                <div class="table-container">
                    <table class="prices-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Costo</th>
                                <th>Venta</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recientes as $h): ?>
                                <tr>
                                    <td><?= esc(formatoFecha($h['fecha'], true)) ?></td>
                                    <td class="price-change">
                                        <?= esc(dinero($h['precio_compra_anterior'])) ?><span class="arrow">→</span><?= esc(dinero($h['precio_compra_nuevo'])) ?>
                                    </td>
                                    <td class="price-change">
                                        <?= esc(dinero($h['precio_venta_anterior'])) ?><span class="arrow">→</span><strong><?= esc(dinero($h['precio_venta_nuevo'])) ?></strong>
                                    </td>
                                    <td><?= esc($h['nombre_usuario']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="table-legend">
                    <a href="<?= esc(urlAccion('precio', 'historial', ['id_producto' => $producto['id_producto']])) ?>">Ver historial completo</a>
                </p>
            <?php else: ?>
                <p class="muted" style="margin:0;">Este producto aún no tiene cambios de precio registrados.</p>
            <?php endif; ?>
        </div>

    </main>

    <script>
    // Solo comodidad visual. La validación real la hace PrecioController en el servidor.
    (function () {
        var tasaIva   = <?= json_encode((float) $tasaIva) ?>;
        var aplicaIva = <?= $aplicaIva ? 'true' : 'false' ?>;

        var costo   = document.getElementById('precio_compra');
        var venta   = document.getElementById('precio_venta');
        var resGan  = document.getElementById('res-ganancia');
        var resMar  = document.getElementById('res-margen');
        var resIva  = document.getElementById('res-iva');
        var alerta  = document.getElementById('alerta-perdida');
        var chk     = document.getElementById('confirmar_perdida');

        function num(campo) {
            var n = parseFloat(campo.value);
            return isNaN(n) ? 0 : n;
        }
        // Mismo formato que el servidor: 1.234,56
        function fmt(n) {
            return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function actualizar() {
            var c = num(costo), v = num(venta);

            resGan.textContent = v > 0 ? '$' + fmt(v - c) : '—';
            resMar.textContent = (v > 0 && c > 0) ? fmt((v - c) / v * 100) + ' %' : '—';
            if (aplicaIva) {
                resIva.textContent = v > 0 ? '$' + fmt(v * (1 + tasaIva / 100)) : '—';
            }

            var perdida = v > 0 && c > 0 && v < c;
            alerta.hidden = !perdida;
            if (!perdida) { chk.checked = false; }
        }

        costo.addEventListener('input', actualizar);
        venta.addEventListener('input', actualizar);

        var btnCosto = document.getElementById('btn-usar-costo');
        if (btnCosto) {
            btnCosto.addEventListener('click', function () {
                costo.value = btnCosto.getAttribute('data-costo');
                actualizar();
            });
        }

        document.getElementById('btn-calcular').addEventListener('click', function () {
            var porc = parseFloat(document.getElementById('porc-ganancia').value);
            var c = num(costo);
            if (isNaN(porc) || porc < 0 || c <= 0) {
                alert('Indica un costo mayor que 0 y un porcentaje de ganancia.');
                return;
            }
            venta.value = (c * (1 + porc / 100)).toFixed(2);
            actualizar();
        });

        // Al enviar, se desactiva el botón para evitar un doble clic (doble registro).
        document.getElementById('form-precio').addEventListener('submit', function () {
            var boton = this.querySelector('button[type="submit"]');
            if (boton) { boton.disabled = true; }
        });

        actualizar();
    })();
    </script>
</body>
</html>
