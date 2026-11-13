<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Presentación Especial · Distribuidora de Alimentos Bonanza</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content" style="width: 100%; flex-grow: 1; max-width: 100%; overflow-x: hidden; padding: 30px;">
        <div class="container is-max-desktop">
            
            <div class="box mb-5" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">add_box</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Nueva Presentación Especial</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Catálogo Maestro
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Crear un empaque exclusivo (excepción) para un producto.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a class="button is-white is-outlined has-text-weight-bold" style="border-radius: 8px; border-color: rgba(255,255,255,0.4);" href="<?= esc(urlAccion('presentacion', 'index')) ?>">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al Historial</span>
                        </a>
                    </div>
                
                </div>
            </div>

            <?php echo mostrarFlash(); ?>

            <div class="box">
                <!-- Atributo action dinámico a guardar -->
                <form action="<?= esc(urlAccion('presentacion', 'guardar')) ?>" method="POST" data-ajax>
                    
                    <!-- Campo CSRF Obligatorio -->
                    <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token'] ?? '') ?>">

                    <!-- SELECCIÓN DE PRODUCTO -->
                    <div class="field mb-5">
                        <label class="label has-text-grey">1. Seleccione el Producto</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="id_producto" required>
                                    <option value="">-- Buscar Producto --</option>
                                    <?php 
                                    $prodRepoblado = $_SESSION['form_data']['id_producto'] ?? '';
                                    if(isset($productos)): 
                                        foreach($productos as $prod): 
                                            $selected = ($prodRepoblado == $prod['id_producto']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= esc($prod['id_producto']) ?>" <?= $selected ?>>
                                            <?= esc($prod['codigo'] . ' - ' . $prod['nombre']) ?>
                                        </option>
                                    <?php 
                                        endforeach; 
                                    endif; 
                                    ?>
                                </select>
                            </div>
                            <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-box"></i></span>
                        </div>
                        <p class="help">¿A qué producto pertenece esta presentación exclusiva?</p>
                    </div>

                    <div class="columns">
                        <!-- ETIQUETA -->
                        <div class="column is-8">
                            <div class="field mb-5">
                                <label class="label has-text-grey">2. Etiqueta (Nombre de la presentación)</label>
                                <div class="control has-icons-left">
                                    <input type="text" name="etiqueta" class="input" required maxlength="30" placeholder="Ej: Brazo Especial (12 uds)" value="<?= esc($_SESSION['form_data']['etiqueta'] ?? '') ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-tag"></i></span>
                                </div>
                                <p class="help">Máximo 30 caracteres. Así se verá en el formulario de compras.</p>
                            </div>
                        </div>

                        <!-- UNIDADES -->
                        <div class="column is-4">
                            <div class="field mb-5">
                                <label class="label has-text-grey">3. Unidades (Contenido)</label>
                                <div class="control has-icons-left">
                                    <input type="number" name="unidades" class="input" required min="1" max="1000" placeholder="Ej: 12" value="<?= esc($_SESSION['form_data']['unidades'] ?? '') ?>">
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-layer-group"></i></span>
                                </div>
                                <p class="help">¿Cuántas unidades individuales trae este empaque?</p>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="field is-grouped is-grouped-right mt-5 pt-4" style="border-top: 1px solid #ededed;">
                        <p class="control">
                            <a href="<?= esc(urlAccion('presentacion', 'index')) ?>" class="button is-ghost has-text-grey">Cancelar</a>
                        </p>
                        <p class="control">
                            <button type="submit" class="button is-link">
                                <span class="icon is-small"><i class="fas fa-save"></i></span>
                                <span>Guardar Presentación</span>
                            </button>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/ajax.js?v=<?= time() ?>"></script>
</body>
</html>