<?php
// Variables que entrega ReporteController::index():
//   $reportes   lista de reportes que el rol actual puede abrir (accion, titulo, descripcion, icono, color)

$tituloPagina       = 'Módulo de Reportes';
$descripcionPagina  = 'Elige el reporte que necesitas. Todos se pueden imprimir y exportar a Excel (CSV).';
$subtituloImpresion = '';
$esMenu             = true;

require __DIR__ . '/_inicio.php';
?>

        <?php if (empty($reportes)): ?>
            <div class="box has-text-centered has-text-grey py-6">
                Tu usuario no tiene reportes disponibles. Consulta con el administrador.
            </div>
        <?php else: ?>
            <div class="columns is-multiline">
                <?php foreach ($reportes as $r): ?>
                    <div class="column is-6-desktop">
                        <a href="<?= esc(urlAccion('reporte', $r['accion'])) ?>" class="box tarjeta-reporte kpi-<?= esc($r['color']) ?>">
                            <div class="kpi-icono kpi-icono-<?= esc($r['color']) ?>">
                                <i class="fas <?= esc($r['icono']) ?>"></i>
                            </div>
                            <div>
                                <h2 class="title is-5 mb-2"><?= esc($r['titulo']) ?></h2>
                                <p class="has-text-grey"><?= esc($r['descripcion']) ?></p>
                                <p class="mt-3 has-text-link has-text-weight-bold">
                                    Abrir reporte <i class="fas fa-arrow-right ml-1"></i>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="is-size-7 has-text-grey mt-2">
                Los montos de ingresos, costos y ganancias se muestran sin IVA. El cierre de caja muestra el total cobrado, con IVA.
            </p>
        <?php endif; ?>

<?php require __DIR__ . '/_fin.php'; ?>
