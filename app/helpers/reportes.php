<?php
/**
 * app/helpers/reportes.php
 *
 * Funciones de presentación del módulo de Reportes (solo formatean; no tocan la BD).
 * Se apoya en formato.php (dinero, formatoFecha...) y seguridad.php (esc).
 */

require_once __DIR__ . '/formato.php';
require_once __DIR__ . '/seguridad.php';

/** Unidades con separador de miles: 12345 => "12.345". */
function unidades($n): string
{
    return number_format((float) $n, 0, ',', '.');
}

/**
 * Variación porcentual con signo: +12,50 % / -3,00 % / 0,00 %. null => "—" (no había base).
 * Se usa el signo menos tipográfico (−) para que no se confunda con un guion.
 */
function variacionTexto(?float $pct): string
{
    if ($pct === null) {
        return '—';
    }
    $redondeado = round($pct, 2);
    $signo = $redondeado > 0 ? '+' : ($redondeado < 0 ? '−' : '');
    return $signo . number_format(abs($redondeado), 2, ',', '.') . ' %';
}

/** Diferencia con signo para unidades: +15 / −3 / 0. */
function difUnidades($n): string
{
    $n = (int) $n;
    return ($n > 0 ? '+' : ($n < 0 ? '−' : '')) . number_format(abs($n), 0, ',', '.');
}

/** Diferencia con signo para dinero: +$1.250,00 / −$30,00 / $0,00. */
function difDinero($monto): string
{
    $monto = round((float) $monto, 2);
    if ($monto > 0) {
        return '+' . dinero($monto);
    }
    if ($monto < 0) {
        return '−' . dinero(abs($monto));
    }
    return dinero(0);
}

/** Clase CSS según el signo: verde si sube, rojo si baja, gris si no cambia. */
function claseVariacion($n): string
{
    $n = round((float) $n, 2);
    return $n > 0 ? 'var-sube' : ($n < 0 ? 'var-baja' : 'var-igual');
}

/** Dinero, o "—" cuando no hay dato (null). Se usa para costos o precios que pueden faltar. */
function dineroOGuion($monto): string
{
    return $monto === null ? '—' : dinero($monto);
}

/**
 * Costo o precio unitario: 2 decimales, o 4 cuando el valor los necesita ($0,5833).
 * null => "—". Así un costo de $10 no se ve como "$10,0000".
 */
function dineroUnitario($monto): string
{
    if ($monto === null) {
        return '—';
    }
    $monto    = (float) $monto;
    $decimales = (abs($monto * 100 - round($monto * 100)) > 0.0001) ? 4 : 2;
    return dinero($monto, $decimales);
}

/** Nombre de la categoría con ese id, o null si no hay filtro / no existe. */
function nombreCategoria(array $categorias, ?int $idCategoria): ?string
{
    if ($idCategoria === null) {
        return null;
    }
    foreach ($categorias as $c) {
        if ((int) $c['id_categoria'] === $idCategoria) {
            return $c['nombre'];
        }
    }
    return null;
}

/**
 * <option> de un selector de categorías, ya escapados (imprímelos sin esc()).
 * Incluye la opción "Todas las categorías" (valor vacío).
 */
function opcionesCategoria(array $categorias, ?int $seleccionada): string
{
    $html = '<option value="">Todas las categorías</option>';
    foreach ($categorias as $c) {
        $id    = (int) $c['id_categoria'];
        $html .= '<option value="' . $id . '"' . ($id === $seleccionada ? ' selected' : '') . '>'
               . esc($c['nombre']) . '</option>';
    }
    return $html;
}

/**
 * Rangos de fechas de un clic para los filtros: etiqueta => [desde, hasta] (Y-m-d).
 * Ninguno llega al futuro: el controlador rechaza fechas posteriores a hoy.
 */
function rangosRapidos(string $hoy): array
{
    $f = function (string $modificador) use ($hoy): string {
        return (new DateTime($hoy))->modify($modificador)->format('Y-m-d');
    };

    return [
        'Hoy'            => [$hoy, $hoy],
        'Últimos 7 días' => [$f('-6 days'), $hoy],
        'Últimos 30 días' => [$f('-29 days'), $hoy],
        'Este mes'       => [$f('first day of this month'), $hoy],
        'Mes anterior'   => [$f('first day of last month'), $f('last day of last month')],
    ];
}

/**
 * Tarjeta de indicador (el mismo estilo de las tarjetas de Ventas).
 * Devuelve HTML ya escapado; imprímela sin esc():  <?= tarjetaKpi(...) ?>
 * $color: 'success' | 'info' | 'warning' | 'danger'.
 */
function tarjetaKpi(string $titulo, string $valor, string $nota = '', string $color = 'info', string $icono = 'fa-chart-simple'): string
{
    $colores = ['success', 'info', 'warning', 'danger'];
    if (!in_array($color, $colores, true)) {
        $color = 'info';
    }

    $html  = '<div class="column is-3-desktop is-6-tablet">';
    $html .= '<div class="box kpi kpi-' . $color . '">';
    $html .= '<div class="kpi-texto">';
    $html .= '<p class="heading has-text-grey has-text-weight-bold mb-1">' . esc($titulo) . '</p>';
    $html .= '<p class="kpi-valor">' . esc($valor) . '</p>';
    if ($nota !== '') {
        $html .= '<p class="is-size-7 has-text-grey mt-1">' . esc($nota) . '</p>';
    }
    $html .= '</div>';
    $html .= '<div class="kpi-icono kpi-icono-' . $color . ' no-print"><i class="fas ' . esc($icono) . '"></i></div>';
    $html .= '</div></div>';

    return $html;
}

/** Etiqueta (tag de Bulma) de la situación de stock de un producto. */
function etiquetaStock(string $situacion): string
{
    switch ($situacion) {
        case 'agotado':
            return '<span class="tag is-danger is-light has-text-weight-bold">Agotado</span>';
        case 'bajo':
            return '<span class="tag is-warning is-light has-text-weight-bold">Stock bajo</span>';
        default:
            return '<span class="tag is-success is-light has-text-weight-bold">Disponible</span>';
    }
}

/**
 * Envía un archivo CSV al navegador y termina el script.
 *
 * Pensado para Excel en español: separador ";" , coma decimal y BOM UTF-8 (para que
 * las tildes y la ñ se vean bien). Cada "bloque" es una tabla del reporte:
 *   ['titulo' => 'Texto opcional', 'encabezados' => [...], 'filas' => [[...], ...]]
 * Los bloques se separan con una línea en blanco.
 *
 * En las filas, los int/float se escriben como NÚMEROS (coma decimal, sin separador de miles,
 * para que Excel pueda sumarlos) y null queda como celda vacía; todo lo demás es TEXTO.
 *
 * Seguridad (inyección de fórmulas): si un texto empieza con = + - @ (o tabulación / retorno),
 * Excel lo ejecutaría como fórmula. Se le antepone un apóstrofo para que se lea como texto.
 */
function enviarCsv(string $nombreArchivo, array $bloques): void
{
    // Nombre de archivo seguro: solo letras, números, guion y punto.
    $nombreArchivo = preg_replace('/[^A-Za-z0-9._-]/', '_', $nombreArchivo);

    // Si ya se escribió algo en el búfer, se descarta: el CSV tiene que empezar limpio.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('X-Content-Type-Options: nosniff');

    $salida = fopen('php://output', 'w');
    fwrite($salida, "\xEF\xBB\xBF");   // BOM UTF-8

    // Escape vacío = CSV estándar (comillas dobladas). Ese valor solo existe desde PHP 7.4.
    $escape = PHP_VERSION_ID >= 70400 ? '' : '\\';

    $celda = function ($v): string {
        if ($v === null) {
            return '';
        }
        if (is_int($v)) {
            return (string) $v;
        }
        if (is_float($v)) {
            return str_replace('.', ',', rtrim(rtrim(number_format($v, 4, '.', ''), '0'), '.'));
        }
        $texto = (string) $v;
        if ($texto !== '' && strpbrk($texto[0], "=+-@\t\r") !== false) {
            $texto = "'" . $texto;
        }
        return $texto;
    };

    $primero = true;
    foreach ($bloques as $bloque) {
        if (!$primero) {
            fputcsv($salida, [], ';', '"', $escape);
        }
        $primero = false;

        if (!empty($bloque['titulo'])) {
            fputcsv($salida, [$celda($bloque['titulo'])], ';', '"', $escape);
        }
        fputcsv($salida, array_map($celda, $bloque['encabezados']), ';', '"', $escape);
        foreach ($bloque['filas'] as $fila) {
            fputcsv($salida, array_map($celda, $fila), ';', '"', $escape);
        }
    }

    fclose($salida);
    exit;
}
