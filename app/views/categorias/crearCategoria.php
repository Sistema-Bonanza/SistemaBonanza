<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Categoría · Industrias Bonanza</title>
    
    <!-- Fuentes y Bulma CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/tablas.css" />
</head>

<body class="is-flex" style="background-color: #f8f9ff; height: 100vh; overflow: hidden; width: 100%;">

    <!-- ========== SIDEBAR ========== -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <main class="main is-flex-grow-1" style="background-color: #f8f9ff; width: 100%; height: 100vh; overflow-y: auto; overflow-x: hidden;">
        
        <!-- Contenedor oculto para interceptar la alerta flash de PHP -->
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash() ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO -->
            <!-- ========================================== -->
            <div class="box mb-6" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">add_box</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">Catálogo Maestro</span>
                                <span class="has-text-white-ter is-size-7">• Nuevo Registro</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0">Registrar Nueva Categoría</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Registra una nueva categoría en el sistema para organizar los productos.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="index.php?controller=categoria&action=formcategoria" class="button is-white has-text-link has-text-weight-bold" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important; border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">arrow_back</span></span>
                            <span>Volver al listado</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="columns is-variable is-5">
                <!-- ========================================== -->
                <!-- COLUMNA IZQUIERDA: FORMULARIO (8 Cols) -->
                <!-- ========================================== -->
                <div class="column is-8">
                    <form action="index.php?controller=categoria&action=guardarCategoria" method="POST" class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem;">
                        
                        <div class="is-flex is-align-items-center justify-content-space-between mb-5" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div class="is-flex is-align-items-center gap-3">
                                <div class="has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center" style="width: 40px; height: 40px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">dataset</span>
                                </div>
                                <div>
                                    <h2 class="title is-5 mb-0 has-text-dark">Datos de la Categoría</h2>
                                    <p class="is-size-7 has-text-grey">Complete los datos para crear la nueva categoría.</p>
                                </div>
                            </div>
                            <span class="tag is-danger is-light has-text-weight-bold">Campos requeridos (*)</span>
                        </div>

                        <!-- Nombre de la Categoría -->
                        <div class="field mb-5">
                            <label class="label is-size-7 has-text-dark" for="nombre">
                                Nombre de la Categoría <span class="has-text-danger">*</span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="nombre" name="nombre" placeholder="Ej: Bebidas, Electrónica, Ropa" required minlength="2" maxlength="50" style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">label</span>
                                </span>
                            </div>
                        </div>

                        <!-- Descripción de la Categoría -->
                        <div class="field mb-6">
                            <label class="label is-size-7 has-text-dark is-flex is-justify-content-space-between" for="descripcion">
                                <span>Descripción de la Categoría</span>
                                <span class="has-text-grey has-text-weight-normal">Opcional</span>
                            </label>
                            <div class="control">
                                <textarea class="textarea" id="descripcion" name="descripcion" placeholder="Descripción de la categoría" rows="4" maxlength="255" style="border-radius: 8px; border-color: #cbd5e1; resize: none;"></textarea>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="is-flex is-align-items-center is-justify-content-flex-end gap-3" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                            <a href="index.php?controller=categoria&action=formcategoria" class="button is-light has-text-weight-semibold" style="border-radius: 8px;">
                                Cancelar
                            </a>
                            <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                                <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                <span>Guardar Categoría</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- COLUMNA DERECHA: PANELES INFORMATIVOS -->
                <!-- ========================================== -->
                <div class="column is-4">
                    
                    <!-- Panel 1: Anti-duplicidad (Categorías Actuales) -->
                    <div class="box is-shadowless mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center justify-content-space-between mb-4" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                            <div class="is-flex is-align-items-center gap-2">
                                <span class="material-symbols-outlined has-text-link is-size-5">view_list</span>
                                <h3 class="title is-6 mb-0 has-text-dark">Categorías Actuales</h3>
                            </div>
                        </div>
                        <p class="is-size-7 has-text-grey mb-3">Consulte los nombres preexistentes para evitar redundancias de catálogo:</p>
                        
                        <div style="max-height: 220px; overflow-y: auto; padding-right: 5px;">
                            <?php if (!empty($categorias)): ?>
                                <?php foreach ($categorias as $cat): ?>
                                    <?php if ((int)$cat['estado'] === 1): ?>
                                        <div class="is-flex is-align-items-center is-justify-content-space-between p-3 mb-2 has-background-white" style="border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                            <div class="is-flex is-align-items-center">
                                                <!-- Punto de estado con margen derecho y anillo de resplandor -->
                                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; margin-right: 12px; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);"></span>
                                                <span class="is-size-7 has-text-weight-bold has-text-dark"><?= htmlspecialchars($cat['nombre']) ?></span>
                                            </div>
                                            <!-- Etiqueta del ID para equilibrar visualmente la tarjeta -->
                                            <span class="tag is-light is-rounded has-text-grey" style="font-size: 0.65rem; font-family: monospace;">CAT-<?= $cat['id_categoria'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="is-size-7 has-text-grey-light is-italic">Aún no hay categorías registradas.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Panel 2: Instrucciones Operativas -->
                    <div class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                        <div class="is-flex is-align-items-center gap-2 mb-3">
                            <span class="material-symbols-outlined has-text-info is-size-5">lightbulb</span>
                            <h3 class="title is-6 mb-0 has-text-dark">Instrucciones Operativas</h3>
                        </div>
                        <ul class="is-size-7 has-text-grey is-flex is-flex-direction-column gap-3" style="line-height: 1.5;">
                            <li class="is-flex gap-2">
                                <span class="has-text-link">•</span> 
                                <span><strong class="has-text-dark">Definición:</strong> Ingrese un nombre representativo y en plural que agrupe adecuadamente los productos.</span>
                            </li>
                            <li class="is-flex gap-2">
                                <span class="has-text-link">•</span> 
                                <span><strong class="has-text-dark">Validación de Unicidad:</strong> El sistema rechaza automáticamente nombres duplicados para mantener el catálogo limpio.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // SweetAlert para errores/éxitos de PHP
            const cajaFlash = document.getElementById('caja-flash-oculta');
            if (cajaFlash && cajaFlash.innerText.trim() !== '') {
                const mensaje = cajaFlash.innerText.trim();
                const esError = mensaje.toLowerCase().includes('error') || mensaje.toLowerCase().includes('obligatorio') || mensaje.toLowerCase().includes('ya existe');
                
                Swal.fire({
                    icon: esError ? 'error' : 'success',
                    title: esError ? 'Atención' : '¡Éxito!',
                    text: mensaje,
                    confirmButtonColor: esError ? '#ef4444' : '#2563eb',
                });
                cajaFlash.innerHTML = '';
            }
        });
    </script>
</body>
</html>