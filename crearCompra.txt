<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada · Distribuidora Bonanza</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
   <!-- Ruta absoluta a tu CSS centralizado -->
     <link rel="stylesheet" href="assets/css/sidebar.css" />
</head>
<body>

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <?php require_once __DIR__ . '/../../helpers/seguridad.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content"  style="width: 100%; flex-grow: 1; max-width: 100%; overflow-x: hidden; padding: 30px;">
        <div class="container is-max-widescreen">
            
            <!-- CABECERA DE LA PÁGINA -->
            <div class="level mb-6">
                <div class="level-left">
                    <div>
                        <h1 class="title is-4 mb-1">Registrar Entrada de Mercancía</h1>
                        <p class="has-text-grey mt-1">Generar nueva recepción (Kardex y Costos)</p>
                    </div>
                </div>
                <div class="level-right">
                    <a href="<?= urlAccion('movimiento', 'tablaMovimientos') ?>" class="button is-light">
                        <span class="icon is-small"><i class="fas fa-arrow-left"></i></span>
                        <span>Volver al listado</span>
                    </a>
                </div>
            </div>

            <!-- A12: MOSTRAR MENSAJES FLASH ARRIBA DEL FORMULARIO -->
            <?php echo mostrarFlash(); ?>

            <form action="<?php echo esc(urlAccion('movimiento', 'guardarCompra')); ?>" method="POST">
                <?php echo campoCsrf(); ?>

                <!-- 1. DATOS DE LA FACTURA -->
                <div class="box mb-6">
                    <h2 class="title is-5 mb-4 pb-2" style="border-bottom: 1px solid #ededed;">Datos de la Factura (Cabecera)</h2>
                    
                    <div class="columns is-multiline">
                        <!-- Proveedor -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label has-text-grey">Proveedor</label>
                                <div class="control has-icons-left is-expanded">
                                    <div class="select is-fullwidth">
                                        <select name="id_proveedor" required class="has-text-weight-semibold" id="<?= esc('id_proveedor') ?>">
                                            <option value="">Seleccione un proveedor...</option>
                                            <?php 
                                            // A12: Repoblar select
                                            $provSeleccionado = recuperarFormulario('id_proveedor');
                                            foreach($proveedores as $prov): 
                                                $selected = ($provSeleccionado == $prov['id_proveedor']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= esc($prov['id_proveedor']) ?>" <?= $selected ?>>
                                                    <?= esc($prov['rif'] . ' - ' . $prov['razon_social']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-truck"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- N° Factura -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-grey">N° de Factura</label>
                                <div class="control has-icons-left">
                                    <!-- A12: maxlength y recuperar valor -->
                                    <input type="text" name="numero_factura" id="<?= esc('numero_factura') ?>" class="input has-text-weight-bold" style="font-family: monospace;" required placeholder="Ej: 001-54321" maxlength="30" value="<?= esc(recuperarFormulario('numero_factura')) ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-hashtag"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-grey">Fecha de Emisión</label>
                                <div class="control has-icons-left">
                                    <!-- A12: max igual a hoy y recuperar valor -->
                                    <?php 
                                        $fechaHoy = date('Y-m-d');
                                        $fechaValue = recuperarFormulario('fecha_factura') ?: $fechaHoy;
                                    ?>
                                    <input type="date" name="fecha_factura" id="<?= esc('fecha_factura') ?>" class="input" style="font-family: monospace;" required value="<?= esc($fechaValue) ?>" max="<?= $fechaHoy ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. DETALLE DE PRODUCTOS -->
                <div class="box p-0 mb-6" style="overflow: hidden;">
                    <div class="level px-4 py-3 has-background-white-ter mb-0" style="border-bottom: 1px solid #ededed;">
                        <div class="level-left">
                            <h2 class="has-text-weight-bold is-size-5">Productos Ingresados (Detalle)</h2>
                        </div>
                        <div class="level-right">
                            <span class="tag is-info is-light has-text-weight-bold">Máx 8 líneas (Fase 1)</span>
                        </div>
                    </div>
                    
                    <div class="table-container mb-0">
                        <table class="table is-fullwidth is-hoverable is-vcentered mb-0">
                            <thead class="has-background-white-ter">
                                <tr>
                                    <th class="has-text-centered" style="width: 50px;">#</th>
                                    <th style="width: 35%;">Producto</th>
                                    <th>Empaque</th>
                                    <th class="has-text-centered" style="width: 120px;">Cant.</th>
                                    <!-- A12: Renombrar columna -->
                                    <th class="has-text-right" style="width: 180px;">Costo por empaque ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // A12: Recuperar arreglos del detalle para repoblar
                                    $prodsPost = recuperarFormulario('id_producto') ?: [];
                                    $empaquesPost = recuperarFormulario('tipo_empaque') ?: [];
                                    $cantsPost = recuperarFormulario('cantidad_empaques') ?: [];
                                    $costosPost = recuperarFormulario('costo_por_empaque') ?: [];
                                
                                    for ($i = 0; $i < 8; $i++): 
                                        $prodVal = isset($prodsPost[$i]) ? $prodsPost[$i] : '';
                                        $empVal = isset($empaquesPost[$i]) ? $empaquesPost[$i] : '';
                                        $cantVal = isset($cantsPost[$i]) ? $cantsPost[$i] : '';
                                        $costoVal = isset($costosPost[$i]) ? $costosPost[$i] : '';
                                ?>
                                    <tr>
                                        <td class="has-text-centered has-text-grey-light has-text-weight-bold"><?= $i + 1 ?></td>
                                        
                                        <td>
                                            <div class="select is-small is-fullwidth">
                                                <select name="id_producto[]" id="<?= esc('id_producto_' . $i) ?>">
                                                    <option value="">-- Dejar vacío si no se usa --</option>
                                                    <?php foreach($productos as $prod): 
                                                        $selected = ($prodVal == $prod['id_producto']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= esc($prod['id_producto']) ?>" <?= $selected ?>>
                                                            <?= esc($prod['codigo'] . ' - ' . $prod['nombre']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="select is-small is-fullwidth">
                                                <select name="tipo_empaque[]" id="<?= esc('tipo_empaque_' . $i) ?>">
                                                    <option value ="">--- Selecciones ---</option>
                                                    <?php foreach($empaques as $clave => $datosEmpaque): 
                                                        $selected = ($empVal == $clave) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= esc($clave) ?>" <?= $selected ?>>
                                                            <!-- A12: Mostrar unidades en el texto del select -->
                                                             <?= esc($datosEmpaque['etiqueta'] . ' (' . $datosEmpaque['unidades'] . ' uds)') ?>
                                                        </option>
                                                    <?php endforeach; ?>

                                                </select>
                                            </div>
                                        </td>

                                        <td>
                                            <!-- A12: step 1, max, y recuperar valor -->
                                            <input type="number" name="cantidad_empaques[]" id="<?= esc('cantidad_empaques_' . $i) ?>" class="input is-small has-text-centered" style="font-family: monospace;" min="1" step="1" max="1000" placeholder="0" value="<?= esc($cantVal) ?>">
                                        </td>
                                        
                                        <td>
                                            <div class="control has-icons-left">
                                                <!-- A12: min 0.01 y recuperar valor -->
                                                <input type="number" step="0.01" name="costo_por_empaque[]" id="<?= esc('costo_por_empaque_' . $i) ?>" class="input is-small has-text-right" style="font-family: monospace;" min="0.01" placeholder="0.00" value="<?= esc($costoVal) ?>">
                                                <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-dollar-sign"></i></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. BOTÓN DE PROCESAR -->
                <div class="is-flex is-justify-content-flex-end" style="gap: 1rem;">
                    <a href="<?= urlAccion('movimiento', 'tablaMovimientos') ?>" class="button is-ghost has-text-grey">Cancelar</a>
                    
                    <button type="submit" class="button is-link has-text-weight-bold px-6" onclick="return confirm('¿Confirma que los costos y cantidades son correctos? La factura no podrá editarse después.');">
                        <span class="icon is-small"><i class="fas fa-check-double"></i></span>
                        <span>Procesar y Actualizar Kardex</span>
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>