<?php
// Variables que entrega VentaController::tablaVentas():
//   $ventas, $resumen, $filtros, $filtrosUrl, $pagina, $totalPaginas, $total, $puedeVender

$hayFiltros = !empty($filtrosUrl);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas · Bonanza</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/ventas.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">

        <!-- CABECERA -->
        <div class="level mb-5">
            <div class="level-left">
                <div>
                    <h1 class="title is-4 mb-1">Módulo de Ventas</h1>
                    <p class="has-text-grey mt-1">Notas de entrega. Cada venta descuenta el stock y queda registrada en el kardex.</p>
                </div>
            </div>
            <div class="level-right">
                <div class="buttons">
                    <?php if ($puedeVender): ?>
                        <a href="<?= esc(urlAccion('venta', 'crearVenta')) ?>" class="button is-success has-text-weight-bold">
                            <span class="icon is-small"><i class="fas fa-cart-shopping"></i></span>
                            <span>Registrar Venta</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= esc(urlAccion('movimiento', 'tablaMovimientos')) ?>" class="button is-light">
                        <span class="icon is-small"><i class="fas fa-truck-ramp-box"></i></span>
                        <span>Ver Compras</span>
                    </a>
                </div>
            </div>
        </div>

        <?= mostrarFlash() ?>

        <!-- TARJETAS DE INDICADORES (datos reales; las ventas anuladas no suman) -->
        <div class="columns is-multiline mb-5">
            <div class="column is-4">
                <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #48c774; height: 100%;">
                    <div>
                        <p class="heading has-text-grey has-text-weight-bold mb-1">Vendido hoy</p>
                        <p class="title is-3 mb-0"><?= esc(dinero($resumen['total_hoy'])) ?></p>
                        <p class="is-size-7 has-text-grey mt-1">
                            <?= (int) $resumen['ventas_hoy'] ?> <?= $resumen['ventas_hoy'] === 1 ? 'nota de entrega' : 'notas de entrega' ?>
                        </p>
                    </div>
                    <div class="has-background-success-light has-text-success is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>

            <div class="column is-4">
                <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #3e8ed0; height: 100%;">
                    <div>
                        <p class="heading has-text-grey has-text-weight-bold mb-1">Vendido en el mes</p>
                        <p class="title is-3 mb-0"><?= esc(dinero($resumen['total_mes'])) ?></p>
                        <p class="is-size-7 has-text-grey mt-1">
                            <?= (int) $resumen['ventas_mes'] ?> <?= $resumen['ventas_mes'] === 1 ? 'venta' : 'ventas' ?>
                        </p>
                    </div>
                    <div class="has-background-info-light has-text-info is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="column is-4">
                <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #ffdd57; height: 100%;">
                    <div>
                        <p class="heading has-text-grey has-text-weight-bold mb-1">Despachado en el mes</p>
                        <p class="title is-3 mb-0"><?= esc(number_format($resumen['unidades_mes'], 0, ',', '.')) ?> <span class="is-size-6 has-text-grey has-text-weight-normal">unds.</span></p>
                    </div>
                    <div class="has-background-warning-light has-text-warning-dark is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                        <i class="fas fa-boxes-packing"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <form method="get" action="index.php" class="box mb-5">
            <input type="hidden" name="controller" value="venta">
            <input type="hidden" name="action" value="tablaVentas">

            <div class="columns is-multiline is-vcentered mb-0">
                <div class="column is-4 pb-0">
                    <label class="label is-small has-text-grey" for="filtroTexto">Buscar</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" id="filtroTexto" name="q" maxlength="60"
                               placeholder="N° de venta, cliente o documento"
                               value="<?= esc($filtros['q']) ?>">
                        <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                <div class="column is-2 pb-0">
                    <label class="label is-small has-text-grey" for="filtroDesde">Desde</label>
                    <input class="input" type="date" id="filtroDesde" name="desde"
                           max="<?= esc(date('Y-m-d')) ?>" value="<?= esc($filtros['desde']) ?>">
                </div>
                <div class="column is-2 pb-0">
                    <label class="label is-small has-text-grey" for="filtroHasta">Hasta</label>
                    <input class="input" type="date" id="filtroHasta" name="hasta"
                           max="<?= esc(date('Y-m-d')) ?>" value="<?= esc($filtros['hasta']) ?>">
                </div>
                <div class="column is-2 pb-0">
                    <label class="label is-small has-text-grey" for="filtroEstado">Estado</label>
                    <div class="select is-fullwidth">
                        <select id="filtroEstado" name="estado">
                            <option value="">Todas</option>
                            <option value="1" <?= $filtros['estado'] === '1' ? 'selected' : '' ?>>Registradas</option>
                            <option value="0" <?= $filtros['estado'] === '0' ? 'selected' : '' ?>>Anuladas</option>
                        </select>
                    </div>
                </div>
                <div class="column is-2 pb-0">
                    <div class="buttons">
                        <button type="submit" class="button is-link">
                            <span class="icon is-small"><i class="fas fa-filter"></i></span>
                            <span>Filtrar</span>
                        </button>
                        <?php if ($hayFiltros): ?>
                            <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>" class="button is-light" title="Quitar filtros">
                                <span class="icon is-small"><i class="fas fa-xmark"></i></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <!-- TABLA PRINCIPAL -->
        <div class="box p-0" style="overflow: hidden;">
            <div class="level px-4 py-3 mb-0" style="border-bottom: 1px solid #ededed;">
                <div class="level-left">
                    <h2 class="has-text-weight-bold is-size-5">Resumen de Ventas</h2>
                </div>
                <div class="level-right">
                    <span class="has-text-grey is-size-7">
                        <?= (int) $total ?> <?= $total === 1 ? 'resultado' : 'resultados' ?>
                    </span>
                </div>
            </div>

            <div class="table-container mb-0">
                <table class="table tabla-ventas is-fullwidth is-hoverable is-vcentered mb-0">
                    <thead class="has-background-white-ter">
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Nota de Entrega</th>
                            <th>Cliente / Usuario</th>
                            <th class="has-text-centered">Estado</th>
                            <th class="has-text-centered">Monto Total</th>
                            <th class="has-text-right">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ventas)): ?>
                            <?php foreach ($ventas as $venta): ?>
                                <?php
                                    $anulada    = ((int) $venta['estado'] === 0);
                                    $urlDetalle = urlAccion('venta', 'verDetalleVenta', ['id' => $venta['id_venta']]);
                                ?>
                                <tr class="<?= $anulada ? 'venta-anulada' : '' ?>">
                                    <!-- Columna 1: fecha y hora -->
                                    <td class="has-text-weight-bold">
                                        <?= esc(formatoFecha($venta['fecha_venta'])) ?>
                                        <br>
                                        <span class="has-text-grey is-size-7 has-text-weight-normal">
                                            <?= esc(date('h:i A', strtotime($venta['fecha_venta']))) ?>
                                        </span>
                                    </td>

                                    <!-- Columna 2: número de la nota -->
                                    <td class="has-text-weight-bold">
                                        <a href="<?= esc($urlDetalle) ?>" class="has-text-success">
                                            <?= esc($venta['numero_operacion']) ?>
                                        </a>
                                        <?php if (!empty($venta['observaciones'])): ?>
                                            <br>
                                            <span class="has-text-grey is-size-7 has-text-weight-normal">
                                                <?= esc(mb_strimwidth($venta['observaciones'], 0, 40, '…', 'UTF-8')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Columna 3: cliente y usuario que la registró -->
                                    <td class="has-text-weight-bold">
                                        <?= esc($venta['nombre_cliente']) ?>
                                        <br>
                                        <span class="has-text-grey is-size-7 has-text-weight-normal">
                                            <?= esc($venta['tipo_documento'] . '-' . $venta['num_documento']) ?>
                                        </span>
                                        <br>
                                        <span class="has-text-grey-light is-size-7 has-text-weight-normal">
                                            <i class="fas fa-user-circle"></i> <?= esc($venta['nombre_usuario'] ?? 'Sistema') ?>
                                        </span>
                                    </td>

                                    <!-- Columna 4: estado -->
                                    <td class="has-text-centered">
                                        <?php if ($anulada): ?>
                                            <span class="tag is-danger is-light has-text-weight-bold">ANULADA</span>
                                        <?php else: ?>
                                            <span class="tag is-success is-light has-text-weight-bold">REGISTRADA</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Columna 5: monto -->
                                    <td class="celda-total has-text-weight-bold has-text-centered">
                                        <?= esc(dinero($venta['total'])) ?>
                                    </td>

                                    <!-- Columna 6: ver nota -->
                                    <td class="has-text-right">
                                        <a href="<?= esc($urlDetalle) ?>" class="button is-small is-light" title="Ver nota de entrega" aria-label="Ver nota de entrega <?= esc($venta['numero_operacion']) ?>">
                                            <span class="icon"><i class="fas fa-eye"></i></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="has-text-centered py-5 has-text-grey">
                                    <?php if ($hayFiltros): ?>
                                        No hay ventas que coincidan con los filtros.
                                        <a href="<?= esc(urlAccion('venta', 'tablaVentas')) ?>">Quitar filtros</a>
                                    <?php elseif ($puedeVender): ?>
                                        Todavía no se ha registrado ninguna venta.
                                        <a href="<?= esc(urlAccion('venta', 'crearVenta')) ?>">Registrar la primera venta</a>
                                    <?php else: ?>
                                        Todavía no se ha registrado ninguna venta.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <?php if ($totalPaginas > 1): ?>
                <nav class="pagination is-centered p-4" role="navigation" aria-label="Paginación del listado de ventas">
                    <?php if ($pagina > 1): ?>
                        <a class="pagination-previous" href="<?= esc(urlAccion('venta', 'tablaVentas', $filtrosUrl + ['pagina' => $pagina - 1])) ?>">Anterior</a>
                    <?php else: ?>
                        <a class="pagination-previous" disabled>Anterior</a>
                    <?php endif; ?>

                    <?php if ($pagina < $totalPaginas): ?>
                        <a class="pagination-next" href="<?= esc(urlAccion('venta', 'tablaVentas', $filtrosUrl + ['pagina' => $pagina + 1])) ?>">Siguiente</a>
                    <?php else: ?>
                        <a class="pagination-next" disabled>Siguiente</a>
                    <?php endif; ?>

                    <ul class="pagination-list">
                        <?php
                            $inicio = max(1, $pagina - 2);
                            $fin    = min($totalPaginas, $pagina + 2);
                        ?>
                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li>
                                <?php if ($i === $pagina): ?>
                                    <a class="pagination-link is-current" aria-current="page" aria-label="Página <?= $i ?>"><?= $i ?></a>
                                <?php else: ?>
                                    <a class="pagination-link" aria-label="Ir a la página <?= $i ?>"
                                       href="<?= esc(urlAccion('venta', 'tablaVentas', $filtrosUrl + ['pagina' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
