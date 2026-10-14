<?php
/**
 * app/helpers/formato.php
 *
 * Funciones de presentación (solo formatean, no tocan la BD).
 * Se carga con require_once, igual que seguridad.php.
 *
 * Formato de montos: $1.234,56 (punto para miles, coma para decimales),
 * el mismo que ya usa la vista de movimientos.
 */

/** Monto para MOSTRAR en pantalla. dinero(1234.5) => "$1.234,50" */
function dinero($valor, int $decimales = 2): string
{
    return '$' . number_format((float) $valor, $decimales, ',', '.');
}

/**
 * Número para el atributo value de un <input type="number">.
 * Un input number exige punto decimal y sin separador de miles: "1234.50".
 */
function numeroInput($valor): string
{
    return number_format((float) $valor, 2, '.', '');
}

/** Porcentaje para mostrar. porcentaje(12.345) => "12,35 %". null => "—" */
function porcentaje(?float $valor, int $decimales = 2): string
{
    return $valor === null ? '—' : number_format($valor, $decimales, ',', '.') . ' %';
}

/**
 * Margen sobre el precio de venta: (venta - costo) / venta * 100.
 * Devuelve null si no se puede calcular (sin costo o sin precio).
 */
function calcularMargen($costo, $venta): ?float
{
    $costo = (float) $costo;
    $venta = (float) $venta;
    if ($costo <= 0 || $venta <= 0) {
        return null;
    }
    return ($venta - $costo) / $venta * 100;
}

/**
 * Variación porcentual entre dos precios: (nuevo - anterior) / anterior * 100.
 * Devuelve null si el precio anterior era 0 (no hay base de comparación).
 */
function variacionPorcentual($anterior, $nuevo): ?float
{
    $anterior = (float) $anterior;
    if ($anterior <= 0) {
        return null;
    }
    return (((float) $nuevo) - $anterior) / $anterior * 100;
}

/** Fecha "12/10/2026" o, con $conHora, "12/10/2026 15:30". Vacío si no hay fecha. */
function formatoFecha(?string $fecha, bool $conHora = false): string
{
    if ($fecha === null || $fecha === '') {
        return '';
    }
    $marca = strtotime($fecha);
    if ($marca === false) {
        return '';
    }
    return date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $marca);
}
