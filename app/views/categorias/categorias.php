<?php
// =========================================================================
// PREPARACIÓN DE VARIABLES (Espera estos datos desde el Controlador)
// =========================================================================
$totalCategorias   = isset($categorias) ? count($categorias) : 0;
$kpiTotalProductos = $kpiTotalProductos ?? 0; // Total de productos en el sistema
$kpiMayorRotacion  = $kpiMayorRotacion ?? 'Sin datos'; // Nombre de la categoría que más se vende
$kpiHuerfanos      = $kpiHuerfanos ?? 0; // Cantidad de productos que tienen un id_categoria que ya no existe o es NULL

// Cálculo dinámico de integridad
$porcentajeIntegridad = 100;
if ($kpiTotalProductos > 0 && $kpiHuerfanos > 0) {
    $porcentajeIntegridad = round((($kpiTotalProductos - $kpiHuerfanos) / $kpiTotalProductos) * 100, 1);
} elseif ($kpiTotalProductos == 0 && $kpiHuerfanos > 0) {
    $porcentajeIntegridad = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías · Industrias Bonanza</title>
    
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
        
        <div id="caja-flash-oculta" style="display: none;">
            <?= mostrarFlash(); ?>
        </div>

        <div class="p-5" style="width: 100%; padding-bottom: 4rem !important;">

            <!-- ========================================== -->
            <!-- BANNER HERO -->
            <!-- ========================================== -->
            <div class="box mb-5" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
                <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                            <span class="material-symbols-outlined is-size-3 has-text-white">category</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                <h1 class="title is-4 has-text-white mb-0">Gestión de Categorías</h1>
                                <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Catálogo Maestro
                                </span>
                            </div>
                            <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                                Administra y clasifica las familias de productos del sistema. Estructuración jerárquica para control de inventario y trazabilidad.
                            </p>
                        </div>
                    </div>
                    <div class="buttons mb-0">
                        <a href="index.php?controller=categoria&action=fromcrearCategoria" class="button is-white has-text-link has-text-weight-bold" style="border-radius: 8px;">
                            <span class="icon"><span class="material-symbols-outlined">add</span></span>
                            <span>Nueva Categoría</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TARJETAS KPI (4 COLUMNAS) -->
            <!-- ========================================== -->
            <div class="columns is-multiline mb-5">
                <!-- KPI 1: Total Categorías (Azul Institucional) -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 12px; background-color: #ffffff;">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Total Registradas</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 has-text-dark"><?= $totalCategorias ?></span>
                                <span class="is-size-7 has-text-grey has-text-weight-semibold">familias</span>
                            </div>
                        </div>
                        <div class="has-background-info-light has-text-link p-3" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">folder_open</span>
                        </div>
                    </div>
                </div>
                
                <!-- KPI 2: Total SKUs Vinculados (Índigo Operativo) -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #4f46e5; border-radius: 12px; background-color: #ffffff;">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">SKUs en Sistema</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 has-text-dark"><?= $kpiTotalProductos ?></span>
                                <span class="is-size-7 has-text-grey has-text-weight-semibold">artículos</span>
                            </div>
                        </div>
                        <div class="has-background-link-light has-text-link p-3" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">inventory_2</span>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Mayor Rotación (Ámbar Destacado) -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid #e2e8f0; border-left: 4px solid #d97706; border-radius: 12px; background-color: #ffffff;">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Mayor Rotación</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-5 mb-0 has-text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;"><?= htmlspecialchars($kpiMayorRotacion) ?></span>
                            </div>
                            <p class="is-size-7 has-text-warning-dark has-text-weight-semibold mt-1">Categoría Líder</p>
                        </div>
                        <div class="has-background-warning-light has-text-warning-dark p-3" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">star</span>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Integridad BD (Verde Esmeralda o Alerta Roja si hay huérfanos) -->
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box is-shadowless h-100 p-4 is-flex is-justify-content-space-between is-align-items-center" style="border: 1px solid <?= $kpiHuerfanos > 0 ? '#fecaca' : '#e2e8f0' ?>; border-left: 4px solid <?= $kpiHuerfanos > 0 ? '#dc2626' : '#10b981' ?>; border-radius: 12px; <?= $kpiHuerfanos > 0 ? 'background-color: #fef2f2;' : 'background-color: #ffffff;' ?>">
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <span class="is-size-7 has-text-grey has-text-weight-bold is-uppercase tracking-wider">Integridad BD</span>
                            </div>
                            <div class="is-flex is-align-items-baseline gap-2">
                                <span class="title is-3 mb-0 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-dark' ?>"><?= $porcentajeIntegridad ?>%</span>
                            </div>
                            <p class="is-size-7 has-text-weight-semibold mt-1 is-flex is-align-items-center gap-1 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-grey' ?>">
                                <span class="material-symbols-outlined is-size-6 <?= $kpiHuerfanos > 0 ? 'has-text-danger' : 'has-text-link' ?>">
                                    <?= $kpiHuerfanos > 0 ? 'warning' : 'verified' ?>
                                </span> 
                                <?= $kpiHuerfanos ?> prod. huérfanos
                            </p>
                        </div>
                        <div class="<?= $kpiHuerfanos > 0 ? 'has-background-danger-light has-text-danger' : 'has-background-success-light has-text-success-dark' ?> p-3" style="border-radius: 12px;">
                            <span class="material-symbols-outlined is-size-4">database_check</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- BARRA DE BÚSQUEDA (Panel Horizontal) -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-4 mb-5 is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap gap-3" style="border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <div class="is-flex is-align-items-center gap-2">
                    <span class="material-symbols-outlined has-text-grey">search</span>
                    <h3 class="has-text-weight-bold has-text-dark m-0">Buscar Categoría</h3>
                </div>
                <div class="control has-icons-left is-expanded" style="max-width: 500px; flex: 1;">
                    <input class="input is-link is-light" type="text" id="buscadorCategorias" placeholder="Escribe el nombre de la categoría para filtrar..." style="border-radius: 8px;">
                    <span class="icon is-small is-left has-text-link"><span class="material-symbols-outlined">filter_list</span></span>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TABLA DE CATEGORÍAS -->
            <!-- ========================================== -->
            <div class="box is-shadowless p-0 mb-5" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                
                <div class="p-4 is-flex is-align-items-center is-justify-content-space-between has-background-white" style="border-bottom: 1px solid #e2e8f0;">
                    <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                        <h3 class="title is-6 mb-0 has-text-dark">Familias del Sistema</h3>
                        <span class="tag is-info is-light is-rounded has-text-weight-bold" id="contadorFilas"><?= $totalCategorias ?> registros</span>
                    </div>
                    <button class="button is-small is-white has-text-grey" title="Recargar lista" onclick="location.reload();">
                        <span class="icon"><span class="material-symbols-outlined">refresh</span></span>
                    </button>
                </div>

                <div class="table-container mb-0">
                    <table class="table is-fullwidth is-hoverable is-vcentered mb-0" id="tablaCategorias">
                        <thead>
                            <tr class="is-size-7 has-text-grey is-uppercase tracking-wider has-background-white-ter">
                                <th class="py-3 px-4 has-text-centered" style="width: 80px;">Icono</th>
                                <th class="py-3 px-4">Categoría</th>
                                <th class="py-3 px-4">Descripción Oficial</th>
                                <th class="py-3 px-4 has-text-centered" style="width: 140px;">Artículos Vinculados</th>
                                <th class="py-3 px-4 has-text-centered" style="width: 100px;">Estado</th>
                                <th class="py-3 px-4 has-text-right" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="is-size-6">
                            
                            <?php if (!empty($categorias)): ?>
                                <?php foreach ($categorias as $categoria): ?>
                                    <?php 
                                        $inicial = strtoupper(substr(htmlspecialchars($categoria['nombre'] ?? 'C'), 0, 2)); 
                                        // Datos dinámicos esperados del modelo (Si no existen, usamos default)
                                        $totalArticulos = $categoria['total_articulos'] ?? 0;
                                        $estadoActivo = isset($categoria['estado']) ? (int)$categoria['estado'] : 1; 
                                    ?>
                                    <tr class="fila-busqueda">
                                        <td class="has-text-centered">
                                            <div class="is-flex is-align-items-center is-justify-content-center has-background-link-dark has-text-white has-text-weight-bold" style="width: 36px; height: 36px; border-radius: 10px; margin: 0 auto; font-size: 0.8rem;">
                                                <?= $inicial ?>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="has-text-weight-bold has-text-dark mb-0 nombre-cat"><?= htmlspecialchars($categoria['nombre'] ?? '') ?></p>
                                            <p class="is-size-7 has-text-grey font-mono">ID: CAT-<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?></p>
                                        </td>
                                        <td>
                                            <p class="is-size-7 has-text-grey" style="max-width: 350px;"><?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción registrada.') ?></p>
                                        </td>
                                        
                                        <!-- NUEVA COLUMNA: Artículos Vinculados -->
                                        <td class="has-text-centered">
                                            <?php if ($totalArticulos > 0): ?>
                                                <span class="tag is-info is-light has-text-weight-bold">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6; margin-right: 6px;"></span> <?= $totalArticulos ?> SKUs
                                                </span>
                                            <?php else: ?>
                                                <span class="tag is-light has-text-grey has-text-weight-bold">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; margin-right: 6px;"></span> 0 SKUs
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- NUEVA COLUMNA: Estado -->
                                        <td class="has-text-centered">
                                            <?php if ($estadoActivo === 1): ?>
                                                <span class="tag is-success is-light has-text-weight-bold">Activo</span>
                                            <?php else: ?>
                                                <span class="tag is-danger is-light has-text-weight-bold">Inactivo</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="has-text-right">
                                            <div class="buttons is-right mb-0">
                                                <!-- Botón de Editar siempre disponible -->
                                                <a href="index.php?controller=categoria&action=fromeditarCategoria&id=<?= $categoria['id_categoria'] ?? '' ?>" class="button is-small is-info is-light" title="Editar Categoría">
                                                    <span class="icon"><span class="material-symbols-outlined">edit</span></span>
                                                </a>
                                                
                                                <?php if ($totalArticulos > 0): ?>
                                                    <!-- BLOQUEO 1: Tiene productos vinculados -->
                                                    <button class="button is-small is-light has-text-grey-light" disabled title="Restringido: Primero debes reasignar o eliminar los <?= $totalArticulos ?> artículos vinculados">
                                                        <span class="icon"><span class="material-symbols-outlined">lock</span></span>
                                                    </button>

                                                <?php elseif ($estadoActivo === 0): ?>
                                                    <!-- BLOQUEO 2: Ya fue inactivada (borrado lógico aplicado) -->
                                                    <button class="button is-small is-light has-text-grey-light" disabled title="Esta categoría ya se encuentra inactiva">
                                                        <span class="icon"><span class="material-symbols-outlined">lock</span></span>
                                                    </button>

                                                <?php else: ?>
                                                    <!-- ACCIÓN PERMITIDA: Tiene 0 artículos y está Activa -->
                                                    <a href="index.php?controller=categoria&action=eliminarCategoria&id=<?= $categoria['id_categoria'] ?? '' ?>" class="button is-small is-danger is-light" title="Inactivar Categoría (Borrado Lógico)" onclick="return confirm('La categoría está vacía. ¿Seguro que deseas inactivarla?');">
                                                        <span class="icon"><span class="material-symbols-outlined">delete</span></span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="has-text-centered py-6">
                                        <span class="material-symbols-outlined is-size-1 has-text-grey-light mb-3">folder_off</span>
                                        <h4 class="title is-6 has-text-grey mb-1">No hay categorías registradas</h4>
                                        <p class="is-size-7 has-text-grey-light">Haz clic en "Nueva Categoría" para crear la primera familia de productos.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- PROTECCIÓN DE HISTORIAL (BORRADO LÓGICO) -->
            <!-- ========================================== -->
            <div class="box" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; border: 1px solid #334155;">
                <div class="is-flex is-align-items-center justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem;">
                    <div class="is-flex is-align-items-flex-start" style="gap: 1rem; flex: 1;">
                        <div class="p-3 has-background-success-dark has-text-success-light" style="border-radius: 12px; border: 1px solid #059669;">
                            <span class="material-symbols-outlined is-size-4">history_edu</span>
                        </div>
                        <div>
                            <div class="is-flex is-align-items-center gap-2 mb-1">
                                <h4 class="title is-6 has-text-white mb-0 is-uppercase tracking-wider" style="font-size: 0.85rem;">Protección de Historial (Borrado Lógico)</h4>
                                <span class="tag is-success is-dark has-text-weight-bold" style="font-size: 0.65rem; border: 1px solid #10b981;">SAFE DELETE</span>
                            </div>
                            <p class="has-text-grey-light is-size-7" style="max-width: 800px; line-height: 1.5;">
                                El sistema utiliza <strong>borrado lógico</strong>. Al eliminar una categoría, esta simplemente se "inactiva" para ocultarla de los nuevos registros. Esto blinda la base de datos evitando que las notas de entrega antiguas, facturas y auditorías del Kardex pierdan su referencia o se corrompan.
                            </p>
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
            const cajaFlash = document.getElementById('caja-flash-oculta');
            if (cajaFlash && cajaFlash.innerText.trim() !== '') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: cajaFlash.innerText.trim(),
                    confirmButtonColor: '#2563eb',
                    timer: 3000,
                    timerProgressBar: true
                });
                cajaFlash.innerHTML = ''; 
            }

            const inputBuscador = document.getElementById('buscadorCategorias');
            if (inputBuscador) {
                inputBuscador.addEventListener('keyup', function() {
                    const texto = this.value.toLowerCase();
                    const filas = document.querySelectorAll('.fila-busqueda');
                    let count = 0;
                    
                    filas.forEach(fila => {
                        const nombreCat = fila.querySelector('.nombre-cat').textContent.toLowerCase();
                        if (nombreCat.includes(texto)) {
                            fila.style.display = '';
                            count++;
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                    
                    const contadorTag = document.getElementById('contadorFilas');
                    if(contadorTag) contadorTag.textContent = `${count} registros`;
                });
            }
        });
    </script>
</body>
</html>