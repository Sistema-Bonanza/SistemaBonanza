<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría · Industrias Bonanza</title>
    
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
                            <span class="material-symbols-outlined is-size-3 has-text-white">edit_note</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="tag is-white is-light is-rounded has-text-weight-bold is-small" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white !important;">Catálogo Maestro</span>
                                <span class="has-text-white-ter is-size-7">• Edición de Registro</span>
                            </div>
                            <h1 class="title is-4 has-text-white mb-0">Editar Categoría</h1>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Actualiza la información taxonómica y clasificación de la familia seleccionada en el sistema.
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
                    <form action="index.php?controller=categoria&action=actualizarCategoria" method="POST" class="box is-shadowless" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem;">
                        
                        <!-- CAMPO OCULTO IMPORTANTE PARA EL CONTROLADOR -->
                        <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id'] ?? '') ?>">

                        <div class="is-flex is-align-items-center justify-content-space-between mb-5 is-flex-wrap-wrap gap-3" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div class="is-flex is-align-items-center">
                                <!-- Ícono con margen derecho (mr-3) para separarlo del texto -->
                                <div class="has-background-info-light has-text-link is-flex is-align-items-center is-justify-content-center flex-shrink-0 mr-3" style="width: 40px; height: 40px; border-radius: 10px;">
                                    <span class="material-symbols-outlined">category</span>
                                </div>
                                <div>
                                    <h2 class="title is-5 mb-0 has-text-dark">Datos de la Categoría</h2>
                                    <p class="is-size-7 has-text-grey">Actualiza los parámetros maestros de la familia de productos.</p>
                                </div>
                            </div>
                            <!-- Etiquetas con separación segura (gap-3) -->
                            <div class="is-flex is-align-items-center flex-shrink-0 ml-5">
                                <span class="tag is-info is-light has-text-weight-bold font-mono px-3 py-2 mr-3">ID: CAT-<?= htmlspecialchars($categoria['id'] ?? '') ?></span>
                                <span class="tag is-danger is-light has-text-weight-bold px-3 py-2">Requeridos (*)</span>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="notification is-info is-light p-3 is-size-7 is-flex is-align-items-center gap-2 mb-5" style="border-radius: 8px; border: 1px solid #bfdbfe;">
                            <span class="material-symbols-outlined text-link is-size-5">info</span>
                            <span>Edición en vivo. Esta actualización se sincronizará inmediatamente sobre el maestro de existencias.</span>
                        </div>

                        <!-- Nombre de la Categoría -->
                        <div class="field mb-5">
                            <label class="label is-size-7 has-text-dark is-flex justify-content-space-between" for="nombre">
                                <span>Nombre de la Categoría <span class="has-text-danger">*</span></span>
                            </label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" required minlength="2" maxlength="50" style="border-radius: 8px; border-color: #cbd5e1;">
                                <span class="icon is-small is-left has-text-grey">
                                    <span class="material-symbols-outlined">label</span>
                                </span>
                            </div>
                            <p class="help has-text-grey">Nombre visible en listados maestros, selectores de filtrado y notas de entrega.</p>
                        </div>

                        <!-- Descripción de la Categoría -->
                        <div class="field mb-4">
                            <label class="label is-size-7 has-text-dark is-flex is-justify-content-space-between" for="descripcion">
                                <span>Descripción de la Categoría <span class="has-text-grey has-text-weight-normal">(Opcional)</span></span>
                                <span class="has-text-grey has-text-weight-normal font-mono" id="char-counter">0 / 250</span>
                            </label>
                            <div class="control">
                                <textarea class="textarea" id="descripcion" name="descripcion" rows="4" maxlength="250" style="border-radius: 8px; border-color: #cbd5e1; resize: none;"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Efecto Cascada Callout -->
                        <div class="notification is-link is-light p-4 mb-6 is-flex is-align-items-flex-start gap-4" style="border-radius: 12px;">
                            <!-- Contenedor del icono con ancho fijo y margen para separarlo del texto -->
                            <span class="material-symbols-outlined has-text-link mt-1 flex-shrink-0" style="margin-right: 4px;">sync_alt</span>
                            <div class="is-flex-grow-1">
                                <h4 class="has-text-weight-bold has-text-link-dark is-size-7 mb-1">Efecto en cascada sobre el inventario</h4>
                                <p class="is-size-7 has-text-link-dark" style="line-height: 1.4;">
                                    Modificar este nombre actualizará la clasificación visual de todos los productos vinculados sin alterar sus existencias en Kardex ni histórico de ventas.
                                </p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="is-flex is-align-items-center is-justify-content-space-between gap-3" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                            <div>
                                <button type="button" id="reset-btn" class="button is-ghost has-text-grey has-text-weight-semibold">
                                    <span class="icon"><span class="material-symbols-outlined">restart_alt</span></span>
                                    <span>Restablecer</span>
                                </button>
                            </div>
                            <div class="buttons mb-0">
                                <a href="index.php?controller=categoria&action=formcategoria" class="button is-light has-text-weight-semibold" style="border-radius: 8px;">
                                    Cancelar
                                </a>
                                <button type="submit" class="button is-link has-text-weight-bold" style="border-radius: 8px;">
                                    <span class="icon"><span class="material-symbols-outlined">save</span></span>
                                    <span>Guardar Cambios</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- COLUMNA DERECHA: PANELES INFORMATIVOS (4 Cols) -->
                <!-- ========================================== -->
                <div class="column is-4">
    
                    <!-- Panel 1: Ficha Operativa -->
                    <div class="box is-shadowless p-0 mb-6 overflow-hidden" style="border: 1px solid #e2e8f0; border-radius: 14px; background-color: #f8fafc;">
                        
                        <!-- Cabecera Azul -->
                        <div class="p-5 is-flex is-align-items-center justify-content-space-between is-flex-wrap-wrap gap-4" style="background: linear-gradient(135deg, #3b82f6 0%, #3730a3 100%);">
                            <div class="is-flex is-align-items-center is-flex-grow-1">
                                <!-- Ícono con margen derecho controlado -->
                                <div class="is-flex is-align-items-center is-justify-content-center flex-shrink-0 mr-4" style="width: 48px; height: 48px; border-radius: 10px; background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                                    <span class="material-symbols-outlined has-text-white is-size-4">outbox</span>
                                </div>
                                <div class="is-flex-grow-1">
                                    <h3 class="is-size-5 has-text-weight-bold has-text-white mb-1" style="line-height: 1.2;">Ficha Operativa</h3>
                                    <p class="has-text-white" style="font-size: 0.75rem; opacity: 0.9; line-height: 1.3;">Resumen taxonómico de almacén</p>
                                </div>
                            </div>
                            <span class="tag is-rounded has-text-weight-bold px-3 py-2 flex-shrink-0" style="background-color: rgba(255,255,255,0.15); color: #6ee7b7; border: 1px solid rgba(255,255,255,0.1); font-size: 0.75rem;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #34d399; margin-right: 8px;"></span>Activo
                            </span>
                        </div>

                        <!-- Cuerpo de la Ficha -->
                        <div class="p-5">
                            <!-- Grid 2 columnas (Azul / Verde) -->
                            <div class="columns is-mobile is-variable is-3 mb-5">
                                <!-- Código Interno -->
                                <div class="column is-6">
                                    <div class="p-4 is-flex is-flex-direction-column is-justify-content-center h-100" style="background-color: #eff6ff; border-radius: 10px; border: 1px solid #dbeafe;">
                                        <span class="is-block has-text-weight-bold is-uppercase tracking-wider mb-2" style="font-size: 0.65rem; color: #1d4ed8; line-height: 1.3;">Código Interno</span>
                                        <div class="is-flex is-align-items-center">
                                            <span class="material-symbols-outlined is-size-5 flex-shrink-0 mr-3" style="color: #2563eb;">tag</span>
                                            <span class="is-size-5 has-text-weight-bold" style="color: #1e3a8a;">CAT-<?= htmlspecialchars($categoria['id'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Artículos Vinculados -->
                                <div class="column is-6">
                                    <div class="p-4 is-flex is-flex-direction-column is-justify-content-center h-100" style="background-color: #ecfdf5; border-radius: 10px; border: 1px solid #d1fae5;">
                                        <span class="is-block has-text-weight-bold is-uppercase tracking-wider mb-2" style="font-size: 0.65rem; color: #047857; line-height: 1.3;"> Artículos Vinculados</span>
                                        <div class="is-flex is-align-items-center">
                                            <span class="material-symbols-outlined is-size-4 flex-shrink-0 mr-3" style="color: #059669;">inventory_2</span>
                                            <div class="is-flex is-align-items-baseline gap-1.5">
                                                <span class="is-size-4 has-text-weight-bold" style="color: #064e3b; line-height: 1;"><?= $totalArticulos ?? 0 ?></span>
                                                <span class="is-size-7 has-text-weight-bold" style="color: #064e3b;">Prod.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Título Productos -->
                            <div class="is-flex is-align-items-center justify-content-space-between mb-4 mt-2 gap-3">
                                <span class="has-text-weight-bold is-uppercase tracking-wider" style="font-size: 0.75rem; color: #64748b;">Productos en esta familia</span>
                                <span class="tag is-info is-light has-text-weight-bold px-3 py-2 flex-shrink-0" style="font-size: 0.75rem;">Total: <?= $stockTotal ?? 0 ?> un.</span>
                            </div>

                            <!-- Lista Dinámica de Productos (SEPARACIÓN ENTRE TARJETAS AUMENTADA CON mb-3) -->
                            <div style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                                
                                <?php if (!empty($productosAsociados)): ?>
                                    <?php foreach ($productosAsociados as $prod): ?>
                                        <div class="is-flex is-align-items-center p-4 mb-3" style="border: 1px solid #e2e8f0; border-radius: 10px; background-color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                            
                                            <!-- Icono con margen derecho (mr-4) -->
                                            <div class="is-flex is-align-items-center is-justify-content-center flex-shrink-0 mr-4" style="width: 38px; height: 38px; border-radius: 8px; background-color: #eff6ff; color: #2563eb;">
                                                <span class="material-symbols-outlined is-size-5">inventory</span>
                                            </div>
                                            
                                            <!-- Textos (Flexibles) -->
                                            <div class="is-flex-grow-1 mr-3" style="min-width: 0;">
                                                <div class="is-size-7 has-text-weight-bold has-text-dark mb-1" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($prod['nombre']) ?>">
                                                    <?= htmlspecialchars($prod['nombre']) ?>
                                                </div>
                                                <div class="has-text-grey font-mono" style="font-size: 0.7rem;">
                                                    Cód: <?= htmlspecialchars($prod['codigo'] ?? 'N/A') ?>
                                                </div>
                                            </div>

                                            <!-- Tag de cantidad (Fijo) -->
                                            <span class="tag has-text-weight-bold has-text-dark px-3 py-2 flex-shrink-0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.75rem;">
                                                <?= (int)($prod['stock_actual'] ?? 0) ?> un.
                                            </span>
                                            
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="has-text-centered p-5" style="background-color: #f1f5f9; border-radius: 10px; border: 1px dashed #cbd5e1;">
                                        <span class="material-symbols-outlined has-text-grey-light is-size-3 mb-2">inventory_2</span>
                                        <p class="is-size-7 has-text-grey has-text-weight-medium">Esta categoría no tiene productos asociados aún.</p>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Instrucciones Operativas -->
                    <div class="box is-shadowless p-6 mb-5" style="border: 1px solid #e2e8f0; border-radius: 14px; position: relative; overflow: hidden; background-color: #f8fafc;">
                        <!-- Borde superior azul -->
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background-color: #2563eb;"></div>
                        
                        <div class="is-flex is-align-items-flex-start justify-content-space-between mb-5 mt-2 gap-4">
                            <div class="is-flex is-align-items-flex-start is-flex-grow-1">
                                <!-- Ícono con margen derecho (mr-4) -->
                                <div class="is-flex is-align-items-center is-justify-content-center flex-shrink-0 mt-1 mr-4" style="width: 44px; height: 44px; border-radius: 10px; background-color: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;">
                                    <span class="material-symbols-outlined is-size-5">assignment</span>
                                </div>
                                <div class="is-flex-grow-1">
                                    <h3 class="has-text-weight-bold has-text-dark mb-1" style="font-size: 1rem; line-height: 1.2;">Instrucciones Operativas</h3>
                                    <p class="has-text-grey" style="font-size: 0.8rem; line-height: 1.3;">Guía de consistencia de catálogo</p>
                                </div>
                            </div>
                            <span class="tag is-info is-light has-text-weight-bold is-uppercase tracking-wider px-3 py-2 flex-shrink-0" style="font-size: 0.7rem;">Reglas</span>
                        </div>
                        
                        <div class="p-5" style="border: 1px solid #e2e8f0; border-radius: 10px; background-color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div class="is-flex is-align-items-flex-start">
                                <!-- Ícono con margen derecho (mr-4) -->
                                <span class="material-symbols-outlined is-size-5 mt-1 flex-shrink-0 mr-4" style="color: #2563eb;">check_circle</span>
                                <div class="is-flex-grow-1">
                                    <span class="is-block has-text-weight-bold has-text-dark is-size-7 mb-2">Coherencia Semántica</span>
                                    <p class="has-text-grey" style="font-size: 0.8rem; line-height: 1.5;">Mantén nombres breves y estandarizados para una visualización más legible en las terminales portátiles de almacén.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                

                <!-- Panel 2: Instrucciones Operativas -->
                <div class="box is-shadowless p-6 mb-5" style="border: 1px solid #e2e8f0; border-radius: 14px; position: relative; overflow: hidden; background-color: #f8fafc;">
                    <!-- Borde superior azul -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background-color: #2563eb;"></div>
                    
                    <div class="is-flex is-align-items-flex-start justify-content-space-between mb-5 mt-2 gap-3">
                        <div class="is-flex is-align-items-flex-start gap-4 is-flex-grow-1">
                            <div class="is-flex is-align-items-center is-justify-content-center flex-shrink-0 mt-1" style="width: 44px; height: 44px; border-radius: 10px; background-color: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;">
                                <span class="material-symbols-outlined is-size-5">assignment</span>
                            </div>
                            <div class="is-flex-grow-1">
                                <h3 class="has-text-weight-bold has-text-dark mb-1" style="font-size: 1rem; line-height: 1.2;">Instrucciones Operativas</h3>
                                <p class="has-text-grey" style="font-size: 0.8rem; line-height: 1.3;">Guía de consistencia de catálogo</p>
                            </div>
                        </div>
                        <span class="tag is-info is-light has-text-weight-bold is-uppercase tracking-wider px-3 py-2 flex-shrink-0" style="font-size: 0.7rem;">Reglas</span>
                    </div>
                    
                    <div class="p-5" style="border: 1px solid #e2e8f0; border-radius: 10px; background-color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        <div class="is-flex is-align-items-flex-start gap-4">
                            <span class="material-symbols-outlined is-size-5 mt-1 flex-shrink-0" style="color: #2563eb;">check_circle</span>
                            <div class="is-flex-grow-1">
                                <span class="is-block has-text-weight-bold has-text-dark is-size-7 mb-2">Coherencia Semántica</span>
                                <p class="has-text-grey" style="font-size: 0.8rem; line-height: 1.5;">Mantén nombres breves y estandarizados para una visualización más legible en las terminales portátiles de almacén.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. Manejo del contador de caracteres
            const textarea = document.getElementById('descripcion');
            const counter = document.getElementById('char-counter');
            const resetBtn = document.getElementById('reset-btn');
            const inputName = document.getElementById('nombre');

            // Valores originales para el botón de reset
            const originalName = inputName ? inputName.value : '';
            const originalDesc = textarea ? textarea.value : '';

            function updateCounter() {
                if (textarea && counter) {
                    counter.textContent = `${textarea.value.length} / 250`;
                }
            }

            if (textarea) {
                updateCounter(); // Contar al inicio
                textarea.addEventListener('input', updateCounter);
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (inputName) inputName.value = originalName;
                    if (textarea) {
                        textarea.value = originalDesc;
                        updateCounter();
                    }
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'info', 
                        title: 'Campos restaurados', showConfirmButton: false, timer: 1500
                    });
                });
            }

            // 2. SweetAlert para errores/éxitos de PHP
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