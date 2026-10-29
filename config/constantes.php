<?php
// config/constantes.php

// Tasa de IVA aplicable en Venezuela
define('TASA_IVA', 16.00);

// Datos de la empresa que se imprimen en la nota de entrega (módulo de Ventas).
// RIF_EMPRESA queda vacío a propósito: si lo llenas (ej: 'J-12345678-9'), aparece en el documento.
define('NOMBRE_EMPRESA', 'Industrias Bonanza');
define('RIF_EMPRESA', '');

// Formas de pago de una venta: clave que se guarda en la BD => texto que se muestra.
// 'mixto' no es una forma en sí: reparte el total entre las demás (tabla venta_pagos).
define('METODOS_PAGO', [
    'efectivo'      => 'Efectivo',
    'tarjeta'       => 'Tarjeta',
    'transferencia' => 'Transferencia',
    'divisa'        => 'Divisa',
    'credito'       => 'Crédito',
]);
define('TIPOS_PAGO_VENTA', METODOS_PAGO + ['mixto' => 'Pago Mixto']);

?>