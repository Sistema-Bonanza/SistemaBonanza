<?php
$tituloPagina       = '';
$descripcionPagina  = '';
$subtituloImpresion = '';
$esMenu             = true;
$ocultarCabecera    = true;

require __DIR__ . '/_inicio.php';
?>

<!-- BANNER HERO ESTANDARIZADO -->
<div class="box mb-5" style="background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #4338ca 100%); border-radius: 12px; border: none; overflow: hidden; position: relative;">
    <div class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap" style="gap: 1.5rem; position: relative; z-index: 2;">
        <div class="is-flex is-align-items-center" style="gap: 1rem;">
            <div class="is-flex is-align-items-center is-justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; backdrop-filter: blur(4px);">
                <span class="material-symbols-outlined is-size-3 has-text-white">pie_chart</span>
            </div>
            <div>
                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                    <h1 class="title is-4 has-text-white mb-0">Módulo de Reportes</h1>
                    <span class="tag is-success is-light is-rounded has-text-weight-bold is-small">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399; margin-right: 6px; box-shadow: 0 0 8px #34d399;"></span> Centro de Inteligencia
                    </span>
                </div>
                <p class="has-text-white-ter is-size-7 mt-1" style="max-width: 700px;">
                    Elige el reporte que necesitas. Todos se pueden consultar en pantalla, imprimir y exportar a formato Excel (CSV) o PDF oficial.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- CATÁLOGOS Y GRILLA DE REPORTES -->
<?php if (empty($reportes)): ?>
    <div class="box has-text-centered has-text-grey py-6" style="border: 1px dashed #cbd5e1; border-radius: 12px; background: white;">
        <span class="icon is-large has-text-grey-light mb-2"><i class="fas fa-lock fa-2x"></i></span>
        <p class="is-size-6 font-weight-bold has-text-dark">Sin reportes asignados</p>
        <p class="is-size-7">Tu usuario no tiene reportes disponibles para su nivel de acceso. Consulta con el administrador del sistema.</p>
    </div>
<?php else: ?>
    <div class="columns is-multiline is-variable is-4 mb-5">
        <?php foreach ($reportes as $r): ?>
            <?php 
                $color = esc($r['color'] ?? 'link');
                $bgBadge = match($color) {
                    'success' => '#ecfdf5',
                    'info'    => '#eff6ff',
                    'warning' => '#fffbeb',
                    'danger'  => '#fff1f2',
                    default   => '#f8fafc'
                };
                $borderBadge = match($color) {
                    'success' => '#a7f3d0',
                    'info'    => '#bfdbfe',
                    'warning' => '#fde68a',
                    'danger'  => '#fecdd3',
                    default   => '#e2e8f0'
                };
                $textBadge = match($color) {
                    'success' => '#059669',
                    'info'    => '#2563eb',
                    'warning' => '#d97706',
                    'danger'  => '#e11d48',
                    default   => '#475569'
                };
            ?>
            <div class="column is-6-desktop">
                <article class="box is-shadowless h-100 p-5 is-flex is-flex-direction-column is-justify-content-space-between" style="border: 1px solid #cbd5e1; border-radius: 14px; background: white;">
                    <div>
                        <div class="is-flex is-align-items-flex-start" style="gap: 1rem;">
                            <div class="p-3 is-flex is-align-items-center is-justify-content-center" style="background: <?= $bgBadge ?>; border: 1px solid <?= $borderBadge ?>; border-radius: 12px; color: <?= $textBadge ?>; width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="fas <?= esc($r['icono']) ?> fa-lg"></i>
                            </div>
                            <div style="flex: 1;">
                                <h2 class="title is-5 has-text-dark mb-1 font-weight-bold">
                                    <?= esc($r['titulo']) ?>
                                </h2>
                                <p class="is-size-7 has-text-grey-dark leading-relaxed mb-0">
                                    <?= esc($r['descripcion']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 is-flex is-justify-content-space-between is-align-items-center" style="border-top: 1px solid #f1f5f9;">
                        <div class="tags are-small mb-0">
                            <span class="tag is-light font-mono">CSV Excel</span>
                            <span class="tag is-light font-mono">Imprimir PDF</span>
                        </div>
                        <a href="<?= esc(urlAccion('reporte', $r['accion'])) ?>" class="button is-<?= $color ?> is-light is-small has-text-weight-bold">
                            <span>Abrir reporte</span>
                            <span class="icon"><i class="fas fa-arrow-right ml-1"></i></span>
                        </a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- NOTA FISCAL Y CONTABLE -->
<footer class="mt-5">
    <div class="box is-shadowless p-4" style="background: #e2e8f0; border-radius: 10px; border: 1px solid #cbd5e1;">
        <div class="is-flex is-align-items-flex-start" style="gap: 0.75rem;">
            <span class="icon has-text-grey-dark mt-1"><i class="fas fa-info-circle"></i></span>
            <p class="is-size-7 has-text-grey-dark mb-0 leading-normal">
                <strong class="has-text-dark font-semibold">Nota fiscal y contable:</strong> Los montos de ingresos, costos y ganancias se muestran sin IVA por defecto. El cierre de caja muestra el total neto y el total cobrado con IVA desglosado para conciliación bancaria y auditoría.
            </p>
        </div>
    </div>
</footer>

<?php require __DIR__ . '/_fin.php'; ?>