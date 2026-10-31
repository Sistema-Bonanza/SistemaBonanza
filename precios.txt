<?php
// Variables que entrega PrecioController::tablaPrecios():
//   $productos, $resumen, $categorias, $estados, $filtros, $puedeEditar, $margenMinimo

// Candado de seguridad (igual que las demás vistas)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Etiqueta y estilo del "badge" según la situación del precio
$situaciones = [
    'sin_precio'  => ['badge-warning',    'Sin precio',  'Este producto no tiene precio de venta.'],
    'sin_costo'   => ['badge-neutral',    'Sin costo',   'No hay costo registrado ni compras para calcular el margen.'],
    'bajo_costo'  => ['badge-inactive',   'Bajo costo',  'El precio de venta es menor que el costo.'],
    'margen_bajo' => ['badge-stock-low',  'Margen bajo', 'El margen está por debajo del mínimo recomendado.'],
    'ok'          => ['badge-active',     'Saludable',   'Margen dentro de lo esperado.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Precios · POS & Inventario</title>

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/precios.css" />
</head>
<body>

    <!-- ========== SIDEBAR (LAYOUT) ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== MAIN ========== -->
    <main class="main">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h1>💲 Control de Precios</h1>
            <p>Define costos y precios de venta de Distribuidora Bonanza C.A., vigila los márgenes y consulta cada cambio realizado.</p>
        </div>

        <!-- Mensajes de éxito / error / aviso (se muestran una sola vez) -->
        <?= mostrarFlash() ?>

        <div class="prices-section">

            <!-- PANEL IZQUIERDO: ACCIONES Y RESUMEN -->
            <div class="actions-panel">
                <h3>📌 Acciones rápidas</h3>
                <div class="btn-group">
                    <?php if ($puedeEditar): ?>
                        <a href="<?= esc(urlAccion('precio', 'formMasivo')) ?>" class="btn-action btn-primary-action">
                            📈 Ajuste por porcentaje
                        </a>
                    <?php endif; ?>
                    <a href="<?= esc(urlAccion('precio', 'historial')) ?>" class="btn-action btn-neutral-action">
                        🕘 Ver historial de cambios
                    </a>
                </div>

                <h3 class="sub">📊 Resumen</h3>
                <div class="stat-list">
                    <a class="stat-row" href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>">
                        <span>Productos activos</span>
                        <strong><?= (int) $resumen['total'] ?></strong>
                    </a>
                    <a class="stat-row <?= $resumen['sin_precio'] > 0 ? 'is-alert' : '' ?>"
                       href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'sin_precio'])) ?>">
                        <span>Sin precio de venta</span>
                        <strong><?= (int) $resumen['sin_precio'] ?></strong>
                    </a>
                    <a class="stat-row <?= $resumen['bajo_costo'] > 0 ? 'is-danger' : '' ?>"
                       href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'bajo_costo'])) ?>">
                        <span>Vendiendo bajo el costo</span>
                        <strong><?= (int) $resumen['bajo_costo'] ?></strong>
                    </a>
                    <a class="stat-row <?= $resumen['margen_bajo'] > 0 ? 'is-alert' : '' ?>"
                       href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'margen_bajo'])) ?>">
                        <span>Margen bajo (&lt; <?= esc(number_format($margenMinimo, 0)) ?> %)</span>
                        <strong><?= (int) $resumen['margen_bajo'] ?></strong>
                    </a>
                    <a class="stat-row"
                       href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'sin_costo'])) ?>">
                        <span>Sin costo para evaluar</span>
                        <strong><?= (int) $resumen['sin_costo'] ?></strong>
                    </a>
                </div>
            </div>

            <!-- PANEL DERECHO: LISTA DE PRECIOS -->
            <div class="prices-list-panel">
                <h3>
                    📋 Lista de Precios
                    <span class="price-counter"><?= count($productos) ?></span>
                </h3>

                <!-- FILTROS (método GET: el router necesita controller y action como campos ocultos) -->
                <form method="GET" action="index.php" class="filter-bar">
                    <input type="hidden" name="controller" value="precio">
                    <input type="hidden" name="action" value="tablaPrecios">

                    <div class="filter-field grow">
                        <label for="q">Buscar</label>
                        <input type="text" id="q" name="q" class="form-control" maxlength="100"
                               placeholder="Nombre o código"
                               value="<?= esc($filtros['q'] ?? '') ?>">
                    </div>

                    <div class="filter-field">
                        <label for="id_categoria">Categoría</label>
                        <select id="id_categoria" name="id_categoria" class="form-control">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>"
                                    <?= ((int) ($filtros['id_categoria'] ?? 0) === (int) $cat['id_categoria']) ? 'selected' : '' ?>>
                                    <?= esc($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="estado">Situación</label>
                        <select id="estado" name="estado" class="form-control">
                            <?php foreach ($estados as $clave => $etiqueta): ?>
                                <option value="<?= esc($clave) ?>" <?= (($filtros['estado'] ?? 'todos') === $clave) ? 'selected' : '' ?>>
                                    <?= esc($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-form btn-submit">Filtrar</button>
                    <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="btn-form btn-light">Limpiar</a>
                </form>

                <div class="table-container">
                    <table class="prices-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="num">Costo</th>
                                <th class="num">Precio venta</th>
                                <th class="center">Margen</th>
                                <th>Últ. cambio</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <?php [$claseBadge, $textoBadge, $ayudaBadge] = $situaciones[$p['situacion']]; ?>
                                    <tr>
                                        <td>
                                            <div class="product-name-display">
                                                <div class="product-avatar">
                                                    <?= esc(mb_strtoupper(mb_substr($p['nombre'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                                                </div>
                                                <div>
                                                    <strong><?= esc($p['nombre']) ?></strong>
                                                    <small>
                                                        <span class="badge badge-code"><?= esc($p['codigo']) ?></span>
                                                        <?php if ((int) $p['aplica_iva'] === 1): ?>
                                                            <span class="small-note">· con IVA</span>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td><?= esc($p['nombre_categoria']) ?></td>

                                        <td class="num">
                                            <?php if ($p['origen_costo'] === 'ninguno'): ?>
                                                <span class="muted">—</span>
                                            <?php else: ?>
                                                <?= esc(dinero($p['costo_ref'])) ?>
                                                <?php if ($p['origen_costo'] === 'compra'): ?>
                                                    <br><span class="small-note" title="No hay costo registrado: se usa el costo de la última compra.">compra</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>

                                        <td class="num">
                                            <?php if ((float) $p['precio_venta'] > 0): ?>
                                                <strong><?= esc(dinero($p['precio_venta'])) ?></strong>
                                            <?php else: ?>
                                                <span class="muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="center">
                                            <span class="badge <?= esc($claseBadge) ?>" title="<?= esc($ayudaBadge) ?>">
                                                <?= $p['margen'] !== null ? esc(porcentaje($p['margen'], 1)) : esc($textoBadge) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if (!empty($p['ultimo_cambio'])): ?>
                                                <?= esc(formatoFecha($p['ultimo_cambio'])) ?>
                                            <?php else: ?>
                                                <span class="muted">Sin cambios</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="actions-cell">
                                                <?php if ($puedeEditar): ?>
                                                    <a href="<?= esc(urlAccion('precio', 'formEditar', ['id' => $p['id_producto']])) ?>"
                                                       class="btn-table btn-edit" title="Actualizar precio">✏️</a>
                                                <?php endif; ?>
                                                <a href="<?= esc(urlAccion('precio', 'historial', ['id_producto' => $p['id_producto']])) ?>"
                                                   class="btn-table btn-history" title="Ver historial de este producto">🕘</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i>💲</i>
                                            <h4>No hay productos para mostrar</h4>
                                            <p>Prueba con otros filtros o registra productos en el módulo de Productos.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <p class="table-legend">
                    <strong>Margen</strong> = (precio de venta − costo) ÷ precio de venta.
                    Se marca «margen bajo» por debajo de <?= esc(number_format($margenMinimo, 0)) ?> %.
                    Si un producto no tiene costo registrado, se usa el costo de su última compra (señalado como «compra»).
                </p>
            </div>
        </div>

    </main>
</body>
</html>
