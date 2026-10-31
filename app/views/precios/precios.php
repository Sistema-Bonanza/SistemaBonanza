<?php
// Candado de seguridad y variables del controlador[cite: 15]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Configuración de etiquetas según la situación del precio[cite: 15]
$situaciones = [
    'sin_precio'  => ['is-danger is-light', 'Sin precio',  'Este producto no tiene precio de venta.'],
    'sin_costo'   => ['is-warning is-light','Sin costo',   'No hay costo registrado ni compras para calcular el margen.'],
    'bajo_costo'  => ['is-danger',          'Bajo costo',  'El precio de venta es menor que el costo.'],
    'margen_bajo' => ['is-warning',         'Margen bajo', 'El margen está por debajo del mínimo recomendado.'],
    'ok'          => ['is-success is-light','Saludable',   'Margen dentro de lo esperado.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Precios · Industrias Bonanza</title>

    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO -->
            <!-- ========================================== -->
            <div class="box mb-6" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white has-background-white-ter" style="width: 60px; height: 60px; background: rgba(255,255,255,0.2) !important; border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-2">attach_money</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">
                                    Finanzas y Comercial
                                </span>
                                <span class="has-text-white-ter is-size-7">• Tarifario General</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Control de Precios</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Define costos y precios de venta, vigila los márgenes operativos y consulta cada cambio realizado.
                            </p>
                        </div>
                    </div>

                    <div class="is-flex is-flex-direction-column is-align-items-flex-end" style="gap: 6px;">
                        <span class="tag is-medium has-text-weight-bold" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25);">
                            <span class="material-symbols-outlined is-size-5 has-text-success mr-2">paid</span>
                            Divisa Base: USD ($)
                        </span>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- GRID 2 COLUMNAS -->
            <!-- ========================================== -->
            <div class="columns is-variable is-5">

                <!-- COLUMNA IZQUIERDA: ACCIONES Y RESUMEN -->
                <div class="column is-4 space-y-5">
                    
                    <!-- Acciones Rápidas -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-4" style="gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <span class="is-size-5">📌</span>
                            <h2 class="title is-6 mb-0 has-text-dark">Acciones Rápidas</h2>
                        </div>
                        
                        <div class="is-flex is-flex-direction-column" style="gap: 12px;">
                            <!-- Botón dinámico según permisos[cite: 15] -->
                            <?php if ($puedeEditar): ?>
                                <a href="<?= esc(urlAccion('precio', 'formMasivo')) ?>" class="button is-link is-fullwidth has-text-weight-bold" style="border-radius: 10px;">
                                    <span class="icon"><span class="material-symbols-outlined">percent</span></span>
                                    <span>Ajuste por porcentaje</span>
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?= esc(urlAccion('precio', 'historial')) ?>" class="button is-dark is-fullwidth has-text-weight-bold" style="border-radius: 10px;">
                                <span class="icon"><span class="material-symbols-outlined">history</span></span>
                                <span>Ver historial global</span>
                            </a>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        <div class="is-flex is-align-items-center mb-4" style="gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <span class="is-size-5">📊</span>
                            <h2 class="title is-6 mb-0 has-text-dark">Resumen</h2>
                        </div>

                        <!-- Panel de estadísticas dinámico[cite: 15] -->
                        <div class="is-flex is-flex-direction-column" style="gap: 10px;">
                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="is-flex is-justify-content-space-between is-align-items-center p-2 has-text-dark" style="background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: background 0.2s;">
                                <span class="is-size-7 has-text-weight-medium">Productos activos</span>
                                <span class="tag is-link is-light is-rounded has-text-weight-bold"><?= (int) $resumen['total'] ?></span>
                            </a>
                            
                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'sin_precio'])) ?>" class="is-flex is-justify-content-space-between is-align-items-center p-2 has-text-dark" style="background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: background 0.2s;">
                                <span class="is-size-7 has-text-weight-medium">Sin precio de venta</span>
                                <span class="tag is-rounded has-text-weight-bold <?= $resumen['sin_precio'] > 0 ? 'is-danger is-light' : 'is-light' ?>"><?= (int) $resumen['sin_precio'] ?></span>
                            </a>

                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'bajo_costo'])) ?>" class="is-flex is-justify-content-space-between is-align-items-center p-2 has-text-dark" style="background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: background 0.2s;">
                                <span class="is-size-7 has-text-weight-medium">Vendiendo bajo costo</span>
                                <span class="tag is-rounded has-text-weight-bold <?= $resumen['bajo_costo'] > 0 ? 'is-danger' : 'is-success is-light' ?>"><?= (int) $resumen['bajo_costo'] ?></span>
                            </a>

                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'margen_bajo'])) ?>" class="is-flex is-justify-content-space-between is-align-items-center p-2 has-text-dark" style="background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: background 0.2s;">
                                <span class="is-size-7 has-text-weight-medium">Margen bajo (&lt; <?= esc(number_format($margenMinimo, 0)) ?> %)</span>
                                <span class="tag is-rounded has-text-weight-bold <?= $resumen['margen_bajo'] > 0 ? 'is-warning' : 'is-light' ?>"><?= (int) $resumen['margen_bajo'] ?></span>
                            </a>

                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios', ['estado' => 'sin_costo'])) ?>" class="is-flex is-justify-content-space-between is-align-items-center p-2 has-text-dark" style="background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: background 0.2s;">
                                <span class="is-size-7 has-text-weight-medium">Sin costo para evaluar</span>
                                <span class="tag is-rounded has-text-weight-bold <?= $resumen['sin_costo'] > 0 ? 'is-warning is-light' : 'is-light' ?>"><?= (int) $resumen['sin_costo'] ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: TABLA Y FILTROS -->
                <div class="column is-8">
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
                        
                        <div class="is-flex is-align-items-center justify-content-space-between mb-4 pb-3" style="border-bottom: 1px solid #f1f5f9;">
                            <div class="is-flex is-align-items-center" style="gap: 12px;">
                                <div class="w-8 h-8 rounded-lg has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center">
                                    <span class="material-symbols-outlined is-size-5">price_change</span>
                                </div>
                                <h2 class="title is-5 mb-0 has-text-dark">Lista de Precios</h2>
                            </div>
                            <span class="tag is-light is-rounded has-text-weight-bold"><?= count($productos) ?> listados</span>
                        </div>

                        <!-- Barra de Filtros Backend[cite: 15] -->
                        <form method="GET" action="index.php" class="mb-5 p-3 has-background-white-bis" style="border: 1px solid #e2e8f0; border-radius: 10px;">
                            <input type="hidden" name="controller" value="precio">
                            <input type="hidden" name="action" value="tablaPrecios">

                            <div class="columns is-mobile is-multiline is-vcentered mb-0">
                                
                                <div class="column is-12-mobile is-5-tablet pb-0">
                                    <div class="field">
                                        <label class="label is-size-7 mb-1" for="q">Buscar</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-small" type="text" id="q" name="q" value="<?= esc($filtros['q'] ?? '') ?>" placeholder="Nombre o código..." style="border-radius: 6px;">
                                            <span class="icon is-small is-left"><span class="material-symbols-outlined">search</span></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="column is-6-mobile is-3-tablet pb-0">
                                    <div class="field">
                                        <label class="label is-size-7 mb-1" for="id_categoria">Categoría</label>
                                        <div class="control">
                                            <div class="select is-small is-fullwidth">
                                                <select id="id_categoria" name="id_categoria" style="border-radius: 6px;">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($categorias as $cat): ?>
                                                        <option value="<?= (int) $cat['id_categoria'] ?>" <?= ((int) ($filtros['id_categoria'] ?? 0) === (int) $cat['id_categoria']) ? 'selected' : '' ?>>
                                                            <?= esc($cat['nombre']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="column is-6-mobile is-4-tablet pb-0">
                                    <div class="field">
                                        <label class="label is-size-7 mb-1" for="estado">Situación</label>
                                        <div class="control">
                                            <div class="select is-small is-fullwidth">
                                                <select id="estado" name="estado" style="border-radius: 6px;">
                                                    <?php foreach ($estados as $clave => $etiqueta): ?>
                                                        <option value="<?= esc($clave) ?>" <?= (($filtros['estado'] ?? 'todos') === $clave) ? 'selected' : '' ?>>
                                                            <?= esc($etiqueta) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="column is-12 mt-2 pt-0 is-flex is-justify-content-flex-end" style="gap: 8px;">
                                    <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-small is-white border" style="border-color: #e2e8f0;">Limpiar</a>
                                    <button type="submit" class="button is-small is-link">Filtrar Base de Datos</button>
                                </div>
                            </div>
                        </form>

                        <!-- Tabla de Datos -->
                        <div class="table-container mb-4">
                            <table class="table is-fullwidth is-hoverable">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold">Producto</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold">Categoría</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold has-text-right">Costo</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold has-text-right">Precio Venta</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold has-text-centered">Margen</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold has-text-centered">Cambio</th>
                                        <th class="is-size-7 is-uppercase has-text-grey-semibold has-text-centered">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($productos)): ?>
                                        <?php foreach ($productos as $p): ?>
                                            <?php 
                                                // Asignación de clases y textos dinámicos desde el backend[cite: 15]
                                                [$claseBadge, $textoBadge, $ayudaBadge] = $situaciones[$p['situacion']]; 
                                                $inicial = mb_strtoupper(mb_substr($p['nombre'], 0, 1, 'UTF-8'), 'UTF-8');
                                            ?>
                                            <tr>
                                                <td class="is-vcentered">
                                                    <div class="is-flex is-align-items-center" style="gap: 10px;">
                                                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white has-text-weight-bold flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; font-size: 0.8rem; background: #3b82f6;">
                                                            <?= esc($inicial) ?>
                                                        </div>
                                                        <div class="is-flex is-flex-direction-column">
                                                            <span class="has-text-weight-bold has-text-dark is-size-7"><?= esc($p['nombre']) ?></span>
                                                            <span class="is-size-7 font-mono has-text-grey">
                                                                <?= esc($p['codigo']) ?>
                                                                <?php if ((int) $p['aplica_iva'] === 1): ?>
                                                                    <span class="has-text-grey-light">•</span> con IVA
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <td class="is-vcentered is-size-7 has-text-grey-dark"><?= esc($p['nombre_categoria']) ?></td>
                                                
                                                <td class="is-vcentered is-size-7 has-text-weight-semibold has-text-grey-dark has-text-right">
                                                    <?php if ($p['origen_costo'] === 'ninguno'): ?>
                                                        <span class="has-text-grey-light">—</span>
                                                    <?php else: ?>
                                                        <?= esc(dinero($p['costo_ref'])) ?>
                                                        <?php if ($p['origen_costo'] === 'compra'): ?>
                                                            <br><span class="is-size-7 has-text-grey" title="No hay costo registrado: se usa el costo de la última compra.">compra</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="is-vcentered is-size-6 has-text-weight-bold has-text-dark has-text-right">
                                                    <?php if ((float) $p['precio_venta'] > 0): ?>
                                                        <?= esc(dinero($p['precio_venta'])) ?>
                                                    <?php else: ?>
                                                        <span class="has-text-grey-light">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="is-vcentered has-text-centered">
                                                    <span class="tag <?= esc($claseBadge) ?> is-rounded has-text-weight-bold" title="<?= esc($ayudaBadge) ?>" style="font-size: 0.7rem;">
                                                        <?= $p['margen'] !== null ? esc(porcentaje($p['margen'], 1)) : esc($textoBadge) ?>
                                                    </span>
                                                </td>
                                                
                                                <td class="is-vcentered has-text-centered is-size-7">
                                                    <?php if (!empty($p['ultimo_cambio'])): ?>
                                                        <?= esc(formatoFecha($p['ultimo_cambio'])) ?>
                                                    <?php else: ?>
                                                        <span class="has-text-grey-light">Sin cambios</span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="is-vcentered has-text-centered">
                                                    <div class="buttons is-centered mb-0" style="gap: 4px;">
                                                        <?php if ($puedeEditar): ?>
                                                            <a href="<?= esc(urlAccion('precio', 'formEditar', ['id' => $p['id_producto']])) ?>" class="button is-small is-link is-light" title="Actualizar precio">
                                                                <span class="icon is-small"><span class="material-symbols-outlined" style="font-size: 16px;">edit</span></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="<?= esc(urlAccion('precio', 'historial', ['id_producto' => $p['id_producto']])) ?>" class="button is-small is-dark is-light" title="Ver historial">
                                                            <span class="icon is-small"><span class="material-symbols-outlined" style="font-size: 16px;">history</span></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="has-text-centered py-5 has-text-grey">
                                                No hay productos para mostrar con los filtros aplicados.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Nota de Cálculo[cite: 15] -->
                        <div class="is-flex is-align-items-start pt-3" style="gap: 8px; border-top: 1px solid #f1f5f9;">
                            <span class="material-symbols-outlined is-size-6 has-text-grey">functions</span>
                            <p class="is-size-7 has-text-grey" style="line-height: 1.4;">
                                <strong class="has-text-dark">Margen</strong> = (precio de venta – costo) ÷ precio de venta. Se marca «margen bajo» por debajo de <?= esc(number_format($margenMinimo, 0)) ?> %. Si un producto no tiene costo registrado, se usa el costo de su última compra (señalado como «compra»).
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Interceptor para el mensaje Flash
        document.addEventListener('DOMContentLoaded', () => {
            const cajaFlash = document.getElementById('caja-flash-oculta');
            if (cajaFlash && cajaFlash.innerText.trim() !== '') {
                const mensaje = cajaFlash.innerText.trim();
                const esError = mensaje.toLowerCase().includes('error') || mensaje.toLowerCase().includes('falló');
                Swal.fire({
                    icon: esError ? 'error' : 'success',
                    title: esError ? 'Atención' : '¡Éxito!',
                    text: mensaje,
                    confirmButtonColor: esError ? '#ef4444' : '#2563eb',
                    timer: esError ? undefined : 3000,
                    timerProgressBar: !esError
                });
            }
        });
    </script>
</body>
</html>