<?php
// Cabecera común de las vistas de Reportes: <head>, sidebar, título con botones y
// encabezado que solo aparece al imprimir.
//
// Variables que la vista debe definir ANTES de incluir este archivo:
//   $tituloPagina        título del reporte
//   $descripcionPagina   frase corta bajo el título
//   $subtituloImpresion  texto del período / parámetros (sale en el papel)
// Opcionales:
//   $urlCsv              si existe, se muestra el botón "Exportar CSV"
//   $esMenu              true en el menú de reportes (sin botón Imprimir ni "Todos los reportes")

$esMenu        = $esMenu ?? false;
$urlCsv        = $urlCsv ?? null;
$nombreEmpresa = defined('NOMBRE_EMPRESA') ? NOMBRE_EMPRESA : 'Industrias Bonanza';
$rifEmpresa    = defined('RIF_EMPRESA') ? RIF_EMPRESA : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($tituloPagina) ?> · Bonanza</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/reportes.css" />
</head>
<body>

    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">

        <!-- CABECERA (no se imprime) -->
        <div class="level mb-5 no-print">
            <div class="level-left">
                <div>
                    <h1 class="title is-4 mb-1"><?= esc($tituloPagina) ?></h1>
                    <p class="has-text-grey mt-1"><?= esc($descripcionPagina) ?></p>
                </div>
            </div>
            <?php if (!$esMenu): ?>
                <div class="level-right">
                    <div class="buttons">
                        <a href="<?= esc(urlAccion('reporte', 'index')) ?>" class="button is-light">
                            <span class="icon is-small"><i class="fas fa-arrow-left"></i></span>
                            <span>Todos los reportes</span>
                        </a>
                        <?php if ($urlCsv): ?>
                            <a href="<?= esc($urlCsv) ?>" class="button is-success is-light" title="Descarga un archivo que se abre en Excel">
                                <span class="icon is-small"><i class="fas fa-file-csv"></i></span>
                                <span>Exportar CSV</span>
                            </a>
                        <?php endif; ?>
                        <button type="button" class="button is-link" data-imprimir>
                            <span class="icon is-small"><i class="fas fa-print"></i></span>
                            <span>Imprimir</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ENCABEZADO DEL PAPEL (solo se ve al imprimir) -->
        <div class="solo-impresion encabezado-impresion">
            <p class="empresa"><?= esc($nombreEmpresa) ?><?= $rifEmpresa !== '' ? ' · RIF ' . esc($rifEmpresa) : '' ?></p>
            <h2><?= esc($tituloPagina) ?></h2>
            <p><?= esc($subtituloImpresion ?? '') ?></p>
            <p class="generado">
                Generado el <?= esc(date('d/m/Y h:i A')) ?>
                por <?= esc($_SESSION['username'] ?? 'Usuario') ?>
            </p>
        </div>

        <?= mostrarFlash() ?>
