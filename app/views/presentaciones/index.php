<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Presentaciones · Bonanza</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>


    <main class="main-content" style="width: 100%; flex-grow: 1; max-width: 100%; overflow-x: hidden; padding: 30px;">
        <div class="container is-max-widescreen">
            
            <!-- CABECERA -->
            <div class="level mb-6">
                <div class="level-left">
                    <div>
                        <h1 class="title is-4 mb-1">Gestión de Presentaciones y Empaques</h1>
                        <p class="has-text-grey mt-1">Administre las excepciones de contenido para productos específicos.</p>
                    </div>
                </div>
                <div class="level-right">
                    <a href="<?= esc(urlAccion('Presentacion', 'crear')) ?>" class="button is-link has-text-weight-bold">
                        <span class="icon is-small"><i class="fas fa-plus"></i></span>
                        <span>Nueva Presentación Especial</span>
                    </a>
                </div>
            </div>

            <?php echo mostrarFlash(); ?>

            <!-- FILTRO DE PRODUCTOS -->
            <div class="box mb-5">
                <form method="GET" action="index.php">
                    <input type="hidden" name="controller" value="presentacion">
                    <input type="hidden" name="action" value="index">
                    
                    <div class="field is-horizontal is-align-items-center">
                        <div class="field-label is-normal" style="flex-grow: 0; margin-right: 15px;">
                            <label class="label has-text-grey">Filtrar Historial:</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control has-icons-left">
                                    <div class="select">
                                        <select name="id_producto" onchange="this.form.submit()">
                                            <option value="">Mostrar Todas las Activas</option>
                                            <?php 
                                            $prodActual = $_GET['id_producto'] ?? '';
                                            if(!empty($productos)): 
                                                foreach($productos as $prod): 
                                                    $selected = ($prodActual == $prod['id_producto']) ? 'selected' : '';
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
                                    <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-filter"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLA DE RESULTADOS -->
            <div class="box p-0" style="overflow: hidden;">
                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-vcentered mb-0">
                        <thead class="has-background-white-ter">
                            <tr>
                                <th>Producto</th>
                                <th>Etiqueta (Empaque)</th>
                                <th class="has-text-centered">Unidades</th>
                                <th class="has-text-centered">Estado</th>
                                <th class="has-text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($presentaciones)): ?>
                                <?php foreach ($presentaciones as $pres): ?>
                                <tr>
                                    <td class="has-text-weight-bold">
                                        <!-- Si filtramos por producto, no traemos el nombre en el query, así que mostramos un texto fijo o lo sacamos del array -->
                                        <?= esc($pres['nombre_producto'] ?? 'Producto Seleccionado') ?>
                                    </td>
                                    
                                    <td>
                                        <span class="has-text-weight-semibold"><?= esc($pres['etiqueta']) ?></span>
                                    </td>
                                    
                                    <td class="has-text-centered">
                                        <span class="tag is-info is-light has-text-weight-bold is-medium">
                                            <?= esc($pres['unidades']) ?>
                                        </span>
                                    </td>

                                    <td class="has-text-centered">
                                        <?php 
                                            // Por defecto asumimos activo si no viene el campo (listarActivas no trae estado)
                                            $estado = $pres['estado'] ?? 1; 
                                            if ($estado == 1): 
                                        ?>
                                            <span class="tag is-success is-light has-text-weight-bold">ACTIVA</span>
                                        <?php else: ?>
                                            <span class="tag is-danger is-light has-text-weight-bold">DESACTIVADA</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="has-text-right">
                                        <?php if ($estado == 1): ?>
                                            <!-- Formulario seguro por POST para desactivar -->
                                            <form action="<?= esc(urlAccion('presentacion', 'desactivar')) ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que desea desactivar esta presentación? No aparecerá en futuras compras.');">
                                                <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="id_presentacion" value="<?= esc($pres['id_presentacion']) ?>">
                                                <input type="hidden" name="id_producto" value="<?= esc($pres['id_producto']) ?>">
                                                
                                                <button type="submit" class="button is-danger is-small is-light" title="Desactivar">
                                                    <span class="icon"><i class="fas fa-ban"></i></span>
                                                    <span>Desactivar</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="has-text-grey-light is-size-7"><i class="fas fa-lock"></i> Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="has-text-centered py-6 has-text-grey">
                                        <span class="icon is-large mb-2"><i class="fas fa-box-open fa-2x"></i></span>
                                        <p>No hay presentaciones registradas para este criterio.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>