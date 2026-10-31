<?php
// Variables que entrega PrecioController::mostrarMasivo():
//   $entrada (id_categoria, tipo, porcentaje), $preview (null o ['cambios'=>[], 'omitidos'=>n]), $categorias

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$hayPreview = ($preview !== null);
$etiquetaTipo = (($entrada['tipo'] ?? 'aumento') === 'descuento') ? 'descuento' : 'aumento';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuste Masivo de Precios · Industrias Bonanza</title>

    <!-- Librerías CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <!-- Hojas de estilo del sistema -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f1f5f9; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR NATIVO ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f1f5f9; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">

        <!-- Mensajes Flash ocultos para SweetAlert -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO (Alto Contraste) -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%); border-radius: 12px; border: none; overflow: hidden; position: relative; box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center has-text-white" style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-2">trending_up</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <span class="tag is-white is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.2); color: white !important;">
                                    Finanzas y Gestión Comercial
                                </span>
                                <span class="has-text-white is-size-7">• Revalorización Masiva</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0 mt-1">Ajuste de Precios por Porcentaje</h1>
                            <p class="has-text-white-bis is-size-7 mt-1" style="max-width: 700px; opacity: 0.95;">
                                Sube o baja el precio de venta de varios productos a la vez. Primero verás el resultado simulado; nada se guarda en el catálogo central hasta que confirmes la transacción.
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-white has-text-weight-bold" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver a la lista</span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- PASO 1: DEFINIR PARÁMETROS -->
            <!-- ========================================== -->
            <div class="box is-shadowless mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; background: white; overflow: hidden;">
                
                <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="background-color: #1e293b; color: white;">
                    <div class="is-flex is-align-items-center" style="gap: 8px;">
                        <span class="tag is-link is-rounded is-small"></span>
                        <h3 class="title is-6 mb-0 has-text-white has-text-weight-bold">Paso 1 · Define los Parámetros del Ajuste</h3>
                    </div>
                    <span class="is-size-7 font-mono has-text-grey-light">ID: ADJ-BATCH-04</span>
                </div>

                <div class="p-5">
                    <form action="<?= esc(urlAccion('precio', 'previsualizarMasivo')) ?>" method="POST" id="form-masivo">
                        <?= campoCsrf() ?>

                        <div class="columns is-variable is-4">
                            <!-- Categoría -->
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="id_categoria">Categoría Objetivo</label>
                                    <div class="control has-icons-left">
                                        <div class="select is-fullwidth">
                                            <select id="id_categoria" name="id_categoria" style="border-color: #94a3b8;">
                                                <option value="">Todas las categorías</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?= (int) $cat['id_categoria'] ?>"
                                                        <?= ((string) ($entrada['id_categoria'] ?? '') === (string) $cat['id_categoria']) ? 'selected' : '' ?>>
                                                        <?= esc($cat['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <span class="icon is-small is-left"><span class="material-symbols-outlined" style="font-size: 16px;">category</span></span>
                                    </div>
                                    <p class="help has-text-grey">Afectará a los ítems vinculados</p>
                                </div>
                            </div>

                            <!-- Tipo de Ajuste -->
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="tipo">
                                        Tipo de ajuste <span class="has-text-danger">*</span>
                                    </label>
                                    <div class="control has-icons-left">
                                        <div class="select is-fullwidth">
                                            <select id="tipo" name="tipo" class="form-control" required style="border-color: #94a3b8;">
                                                <option value="aumento"   <?= $etiquetaTipo === 'aumento'   ? 'selected' : '' ?>>Aumento (+)</option>
                                                <option value="descuento" <?= $etiquetaTipo === 'descuento' ? 'selected' : '' ?>>Descuento (−)</option>
                                            </select>
                                        </div>
                                        <span class="icon is-small is-left"><span class="material-symbols-outlined" style="font-size: 16px;">swap_vertical_circle</span></span>
                                    </div>
                                    <p class="help has-text-grey">Dirección del cálculo</p>
                                </div>
                            </div>

                            <!-- Porcentaje -->
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label is-size-7 has-text-weight-bold has-text-grey-dark" for="porcentaje">
                                        Porcentaje (%) <span class="has-text-danger">*</span>
                                    </label>
                                    <div class="control has-icons-right">
                                        <input type="number" id="porcentaje" name="porcentaje" class="input has-text-weight-bold has-text-link"
                                               step="0.01" min="0.01" max="100" required placeholder="Ej. 5"
                                               value="<?= esc($entrada['porcentaje'] ?? '') ?>" style="border-color: #94a3b8;">
                                        <span class="icon is-small is-right has-text-link font-bold">%</span>
                                    </div>
                                    <p class="help has-text-grey">Aplica fórmula directa</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Presets Rápidos -->
                        <div class="my-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <span class="is-size-7 has-text-weight-bold has-text-grey-dark uppercase mb-2 block">Preajustes rápidos:</span>
                            <div class="buttons are-small mb-0">
                                <button type="button" class="button is-light" onclick="setPorcentaje(3)">+3 %</button>
                                <button type="button" class="button is-info is-light" onclick="setPorcentaje(5)">+5 % Inflación</button>
                                <button type="button" class="button is-light" onclick="setPorcentaje(8.5)">+8.5 %</button>
                                <button type="button" class="button is-light" onclick="setPorcentaje(10)">+10 % Trimestral</button>
                                <button type="button" class="button is-light" onclick="setPorcentaje(15)">+15 %</button>
                                <button type="button" class="button is-light" onclick="setPorcentaje(20)">+20 % Devaluación</button>
                            </div>
                        </div>

                        <!-- Aviso de Reglas -->
                        <div class="notification is-info is-light p-3 mb-4" style="border-radius: 8px; border-left: 4px solid #2563eb;">
                            <div class="is-flex is-align-items-center" style="gap: 8px;">
                                <span class="material-symbols-outlined is-size-5 text-link">info</span>
                                <p class="is-size-7 has-text-weight-bold has-text-dark">Regla de Operación Masiva</p>
                            </div>
                            <p class="is-size-7 has-text-grey-dark mt-1">
                                Solo cambia el <strong>precio de venta</strong>; el costo no se toca. Los productos sin precio de venta se omiten (no hay base para aplicar un porcentaje). Los precios se redondean a 2 decimales.
                            </p>
                        </div>

                        <div class="is-flex is-justify-content-flex-end pt-3" style="border-top: 1px solid #e2e8f0; gap: 10px;">
                            <a href="<?= esc(urlAccion('precio', 'tablaPrecios')) ?>" class="button is-light has-text-weight-semibold" style="border: 1px solid #94a3b8;">Volver Atrás</a>
                            <button type="submit" class="button is-link has-text-weight-bold">
                                <span class="icon"><span class="material-symbols-outlined">visibility</span></span>
                                <span>Previsualizar Ajuste</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- PASO 2: VISTA PREVIA Y CONFIRMACIÓN -->
            <!-- ========================================== -->
            <?php if ($hayPreview): ?>
                <?php $cambios = $preview['cambios']; ?>
                <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; background: white;">
                    
                    <div class="px-5 py-3 is-flex is-align-items-center is-justify-content-space-between" style="background-color: #0f172a; color: white;">
                        <div class="is-flex is-align-items-center" style="gap: 8px;">
                            <span class="material-symbols-outlined has-text-warning">preview</span>
                            <h3 class="title is-6 mb-0 has-text-white has-text-weight-bold">Paso 2 · Vista Previa del Lote</h3>
                        </div>
                        <span class="tag is-warning is-rounded font-mono has-text-weight-bold"><?= count($cambios) ?> producto(s) afectados</span>
                    </div>

                    <?php if (!empty($cambios)): ?>
                        <div class="table-container mb-0">
                            <table class="table is-fullwidth is-hoverable is-vcentered mb-0 is-size-7">
                                <thead>
                                    <tr style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                        <th class="has-text-weight-bold has-text-dark">Producto</th>
                                        <th class="has-text-weight-bold has-text-dark">Categoría</th>
                                        <th class="has-text-weight-bold has-text-dark has-text-right">Precio Actual</th>
                                        <th class="has-text-weight-bold has-text-dark has-text-right">Precio Nuevo</th>
                                        <th class="has-text-weight-bold has-text-dark has-text-centered">Variación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cambios as $c): ?>
                                        <?php 
                                        $variacion = variacionPorcentual($c['precio_venta'], $c['nuevo']);
                                        $inicial = mb_strtoupper(mb_substr($c['nombre'], 0, 1, 'UTF-8'), 'UTF-8');
                                        ?>
                                        <tr style="border-bottom: 1px solid #e2e8f0;">
                                            <td>
                                                <div class="is-flex is-align-items-center" style="gap: 10px;">
                                                    <div class="is-flex is-align-items-center is-justify-content-center has-text-weight-bold" style="width: 32px; height: 32px; background: #e0f2fe; color: #0369a1; border-radius: 8px; font-size: 13px; border: 1px solid #bae6fd;">
                                                        <?= esc($inicial) ?>
                                                    </div>
                                                    <div>
                                                        <div class="has-text-weight-bold has-text-dark"><?= esc($c['nombre']) ?></div>
                                                        <span class="tag is-dark is-rounded is-small font-mono mt-1"><?= esc($c['codigo']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="tag is-light font-medium"><?= esc($c['nombre_categoria']) ?></span></td>
                                            <td class="has-text-right font-mono has-text-grey"><?= esc(dinero($c['precio_venta'])) ?></td>
                                            <td class="has-text-right font-mono"><strong class="has-text-dark is-size-6"><?= esc(dinero($c['nuevo'])) ?></strong></td>
                                            <td class="has-text-centered">
                                                <?php if ($variacion !== null): ?>
                                                    <span class="tag <?= $variacion >= 0 ? 'is-success' : 'is-danger' ?> is-rounded is-small has-text-weight-bold">
                                                        <?= $variacion >= 0 ? '▲ +' : '▼ ' ?><?= esc(number_format($variacion, 2, ',', '.')) ?> %
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="has-text-centered py-6">
                            <span class="material-symbols-outlined is-size-3 has-text-grey mb-2">info</span>
                            <p class="has-text-weight-bold has-text-dark">Ningún producto cambiaría de precio</p>
                            <p class="is-size-7 has-text-grey">Todos los productos de esta selección están sin precio de venta o quedarían igual.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($preview['omitidos'] > 0): ?>
                        <div class="px-5 py-2 has-background-warning-light has-text-warning-dark is-size-7 font-medium" style="border-top: 1px solid #e2e8f0;">
                            ⚠️ <?= (int) $preview['omitidos'] ?> producto(s) se omiten (sin precio de venta o sin variación al redondear).
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($cambios)): ?>
                        <div class="px-5 py-4 has-background-light is-flex is-align-items-center is-justify-content-space-between" style="border-top: 2px solid #cbd5e1;">
                            <span class="is-size-7 has-text-grey-dark">Revisa los valores antes de registrar la actualización oficial en la base de datos.</span>
                            
                            <form action="<?= esc(urlAccion('precio', 'aplicarMasivo')) ?>" method="POST" id="form-aplicar">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="id_categoria" value="<?= esc($entrada['id_categoria'] ?? '') ?>">
                                <input type="hidden" name="tipo" value="<?= esc($entrada['tipo']) ?>">
                                <input type="hidden" name="porcentaje" value="<?= esc($entrada['porcentaje']) ?>">

                                <button type="submit" class="button is-success has-text-weight-bold">
                                    <span class="icon"><span class="material-symbols-outlined">check_circle</span></span>
                                    <span>Confirmar y aplicar <?= esc($etiquetaTipo) ?> de <?= esc(number_format((float) $entrada['porcentaje'], 2, ',', '.')) ?> %</span>
                                </button>
                            </form>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const formAplicar = document.getElementById('form-aplicar');
                            if (formAplicar) {
                                formAplicar.addEventListener('submit', function (e) {
                                    e.preventDefault();
                                    
                                    const totalProductos = <?= count($cambios) ?>;

                                    Swal.fire({
                                        title: '¿Confirmar actualización masiva?',
                                        text: `Se modificará el precio de ${totalProductos} producto(s). ¿Continuar?`,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#10b981',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Sí, aplicar cambios',
                                        cancelButtonText: 'Cancelar',
                                        reverseButtons: true
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            const boton = formAplicar.querySelector('button[type="submit"]');
                                            if (boton) {
                                                boton.classList.add('is-loading');
                                                boton.disabled = true;
                                            }
                                            formAplicar.submit();
                                        }
                                    });
                                });
                            }
                        });
                        </script>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Script auxiliar para presets rápidos -->
    <script>
    function setPorcentaje(val) {
        const input = document.getElementById('porcentaje');
        if (input) {
            input.value = val.toFixed(2);
        }
    }
    </script>

    <!-- Librerías de JavaScript del Sistema -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/precioMasivo.js?v=<?= time() ?>"></script>
</body>
</html>