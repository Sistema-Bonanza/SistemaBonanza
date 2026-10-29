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

/**
 * Número de operación legible a partir del id.
 * numeroOperacion('VEN', 15) => "VEN-000015"   (Compras usa el mismo formato con 'COMP').
 */
function numeroOperacion(string $prefijo, $id, int $largo = 6): string
{
    return $prefijo . '-' . str_pad((string) (int) $id, $largo, '0', STR_PAD_LEFT);
}


// ============================================================
// CÁLCULOS DE DOCUMENTOS (ventas, y compras si las migras)
// ------------------------------------------------------------
// Los montos se manejan en CENTAVOS ENTEROS mientras se calcula.
// Con floats, 0.1 + 0.2 da 0.30000000000000004 y la BD tiene un
// CHECK (total = subtotal + monto_iva) que exige exactitud.
// Los resultados salen como texto con 2 decimales ("37.50"),
// listos para enlazar a una columna DECIMAL.
// ============================================================

/** "12.35" => 1235. Redondea al centavo más cercano. */
function aCentavos($valor): int
{
    return (int) round(((float) $valor) * 100);
}

/** 1235 => "12.35" (punto decimal, sin separador de miles). */
function deCentavos(int $centavos): string
{
    return number_format($centavos / 100, 2, '.', '');
}

/** Subtotal de una línea: cantidad x precio, exacto. subtotalLinea(3, '12.50') => "37.50" */
function subtotalLinea(int $cantidad, $precioUnitario): string
{
    return deCentavos($cantidad * aCentavos($precioUnitario));
}

/**
 * Totales de un documento a partir de sus líneas.
 * Cada línea necesita 'subtotal_linea' y 'aplica_iva' (0 o 1).
 *
 * El IVA se calcula POR LÍNEA (redondeado al centavo) y se suma: es la misma
 * regla que usa Compras, así los dos módulos cuadran igual.
 *
 * Devuelve ['subtotal' => '0.00', 'monto_iva' => '0.00', 'total' => '0.00'].
 */
function calcularTotalesDocumento(array $lineas, float $tasaIva): array
{
    $subtotal = 0;
    $iva      = 0;

    foreach ($lineas as $linea) {
        $centavos  = aCentavos($linea['subtotal_linea']);
        $subtotal += $centavos;
        if ((int) $linea['aplica_iva'] === 1) {
            $iva += (int) round($centavos * $tasaIva / 100);
        }
    }

    return [
        'subtotal'  => deCentavos($subtotal),
        'monto_iva' => deCentavos($iva),
        'total'     => deCentavos($subtotal + $iva),
    ];
}
