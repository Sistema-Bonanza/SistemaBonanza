-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2026 a las 16:50:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_bonanza`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Cigarros', 'Productos derivados del Tabaco', 1),
(2, 'Tabaco', 'Productos derivados del Tabaco', 1),
(3, 'Chimo', 'pasta de tabaco de mascar', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `tipo_documento` enum('V','E','J','G') NOT NULL DEFAULT 'V',
  `num_documento` varchar(20) NOT NULL,
  `nombre_razon_social` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `tipo_documento`, `num_documento`, `nombre_razon_social`, `telefono`, `email`, `direccion`, `estado`, `creado_en`) VALUES
(1, 'V', '32456745', 'Julio Perez', '0414468521', 'julioeldibujante@gmail.com', 'Valencia Estado Carabobo', 1, '2026-10-28 17:12:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `numero_factura` varchar(30) NOT NULL,
  `fecha_factura` date NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(12,2) NOT NULL,
  `tasa_iva` decimal(5,2) NOT NULL,
  `monto_iva` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `id_usuario_anula` int(11) DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `id_proveedor`, `numero_factura`, `fecha_factura`, `fecha_registro`, `subtotal`, `tasa_iva`, `monto_iva`, `total`, `observaciones`, `estado`, `id_usuario`, `fecha_anulacion`, `id_usuario_anula`, `motivo_anulacion`) VALUES
(1, 1, '001', '2026-10-18', '2026-10-18 23:41:34', 280.00, 16.00, 44.80, 324.80, NULL, 1, 1, NULL, NULL, NULL),
(2, 2, '002', '2026-10-18', '2026-10-18 23:42:38', 66.00, 16.00, 10.56, 76.56, NULL, 1, 1, NULL, NULL, NULL),
(3, 1, '003', '2026-10-18', '2026-10-18 23:43:43', 130.00, 16.00, 20.80, 150.80, NULL, 1, 1, NULL, NULL, NULL),
(4, 1, '004', '2026-10-20', '2026-10-21 00:01:42', 70.00, 16.00, 11.20, 81.20, NULL, 1, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compras`
--

CREATE TABLE `detalle_compras` (
  `id_detalle` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_presentacion` int(11) DEFAULT NULL,
  `tipo_empaque` varchar(30) NOT NULL,
  `cantidad_empaques` int(11) NOT NULL,
  `unidades_por_empaque` int(11) NOT NULL,
  `cantidad_unidades` int(11) NOT NULL,
  `costo_por_empaque` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(12,4) NOT NULL,
  `aplica_iva` tinyint(1) NOT NULL,
  `subtotal_linea` decimal(12,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `detalle_compras`
--

INSERT INTO `detalle_compras` (`id_detalle`, `id_compra`, `id_producto`, `id_presentacion`, `tipo_empaque`, `cantidad_empaques`, `unidades_por_empaque`, `cantidad_unidades`, `costo_por_empaque`, `costo_unitario`, `aplica_iva`, `subtotal_linea`) VALUES
(1, 1, 1, 1, 'BRAZO DE CIGARROS', 20, 10, 200, 6.00, 0.6000, 1, 120.00),
(2, 1, 2, 1, 'BRAZO DE CIGARROS', 20, 10, 200, 8.00, 0.8000, 1, 160.00),
(3, 2, 3, 2, 'PAQUETE DE TABACOS', 3, 50, 150, 12.00, 0.2400, 1, 36.00),
(4, 2, 4, 2, 'PAQUETE DE TABACOS', 3, 50, 150, 10.00, 0.2000, 1, 30.00),
(5, 3, 5, 3, 'PAQUETE DE CHIMO', 10, 12, 120, 6.00, 0.5000, 1, 60.00),
(6, 3, 6, 3, 'PAQUETE DE CHIMO', 10, 12, 120, 7.00, 0.5833, 1, 70.00),
(7, 4, 4, 2, 'PAQUETE DE TABACOS', 10, 50, 500, 7.00, 0.1400, 1, 70.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `aplica_iva` tinyint(1) NOT NULL,
  `subtotal_linea` decimal(12,2) NOT NULL,
  `costo_unitario` decimal(12,4) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `aplica_iva`, `subtotal_linea`, `costo_unitario`) VALUES
(1, 1, 6, 10, 1.20, 1, 12.00, 0.5800),
(2, 1, 5, 10, 1.20, 1, 12.00, 0.5800),
(3, 2, 6, 6, 1.20, 1, 7.20, 0.5800),
(4, 2, 5, 6, 1.20, 1, 7.20, 0.5800),
(5, 3, 6, 5, 1.20, 1, 6.00, 0.5800);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_precios`
--

CREATE TABLE `historial_precios` (
  `id_historial` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `precio_compra_anterior` decimal(10,2) NOT NULL,
  `precio_compra_nuevo` decimal(10,2) NOT NULL,
  `precio_venta_anterior` decimal(10,2) NOT NULL,
  `precio_venta_nuevo` decimal(10,2) NOT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `historial_precios`
--

INSERT INTO `historial_precios` (`id_historial`, `id_producto`, `precio_compra_anterior`, `precio_compra_nuevo`, `precio_venta_anterior`, `precio_venta_nuevo`, `id_compra`, `id_usuario`, `fecha`) VALUES
(1, 6, 0.00, 0.58, 0.00, 1.20, NULL, 10, '2026-10-28 17:13:31'),
(2, 5, 0.00, 0.58, 0.00, 1.20, NULL, 10, '2026-10-28 17:13:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id_movimiento` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_antes` int(11) NOT NULL,
  `stock_despues` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id_movimiento`, `id_producto`, `tipo`, `cantidad`, `stock_antes`, `stock_despues`, `motivo`, `id_compra`, `id_usuario`, `id_venta`, `fecha`) VALUES
(1, 1, 'entrada', 200, 0, 200, 'Compra Factura #001', 1, 1, NULL, '2026-10-18 23:41:34'),
(2, 2, 'entrada', 200, 0, 200, 'Compra Factura #001', 1, 1, NULL, '2026-10-18 23:41:34'),
(3, 3, 'entrada', 150, 0, 150, 'Compra Factura #002', 2, 1, NULL, '2026-10-18 23:42:38'),
(4, 4, 'entrada', 150, 0, 150, 'Compra Factura #002', 2, 1, NULL, '2026-10-18 23:42:38'),
(5, 5, 'entrada', 120, 0, 120, 'Compra Factura #003', 3, 1, NULL, '2026-10-18 23:43:43'),
(6, 6, 'entrada', 120, 0, 120, 'Compra Factura #003', 3, 1, NULL, '2026-10-18 23:43:43'),
(7, 4, 'entrada', 500, 150, 650, 'Compra Factura #004', 4, 1, NULL, '2026-10-21 00:01:42'),
(8, 6, 'salida', 10, 120, 110, 'Factura FAC-000001', NULL, 10, 1, '2026-10-28 17:14:46'),
(9, 5, 'salida', 10, 120, 110, 'Factura FAC-000001', NULL, 10, 1, '2026-10-28 17:14:46'),
(10, 6, 'salida', 6, 110, 104, 'Factura FAC-000002', NULL, 10, 2, '2026-10-28 17:18:29'),
(11, 5, 'salida', 6, 110, 104, 'Factura FAC-000002', NULL, 10, 2, '2026-10-28 17:18:29'),
(12, 5, 'entrada', 6, 104, 110, 'Anulación factura FAC-000002', NULL, 10, 2, '2026-10-28 17:18:57'),
(13, 6, 'entrada', 6, 104, 110, 'Anulación factura FAC-000002', NULL, 10, 2, '2026-10-28 17:18:57'),
(14, 6, 'salida', 5, 110, 105, 'Factura FAC-000003', NULL, 10, 3, '2026-10-28 23:26:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentaciones`
--

CREATE TABLE `presentaciones` (
  `id_presentacion` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `etiqueta` varchar(30) NOT NULL,
  `unidades` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `presentaciones`
--

INSERT INTO `presentaciones` (`id_presentacion`, `id_producto`, `etiqueta`, `unidades`, `estado`, `id_usuario`, `fecha_creacion`) VALUES
(1, NULL, 'BRAZO DE CIGARROS', 10, 1, 1, '2026-10-18 23:41:10'),
(2, NULL, 'PAQUETE DE TABACOS', 50, 1, 1, '2026-10-18 23:42:10'),
(3, NULL, 'PAQUETE DE CHIMO', 12, 1, 1, '2026-10-18 23:43:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `aplica_iva` tinyint(1) DEFAULT 1,
  `stock_actual` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `estado` tinyint(1) DEFAULT 1,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo`, `nombre`, `id_categoria`, `precio_compra`, `precio_venta`, `aplica_iva`, `stock_actual`, `stock_minimo`, `estado`, `create_at`) VALUES
(1, '001', 'Cigarro Lucky', 1, 0.00, 0.00, 1, 200, 0, 1, '2026-10-18 23:33:59'),
(2, '002', 'Cigarro Belmont', 1, 0.00, 0.00, 1, 200, 0, 1, '2026-10-18 23:34:15'),
(3, '003', 'Tabaco La Hollada', 2, 0.00, 0.00, 1, 150, 0, 1, '2026-10-18 23:34:29'),
(4, '004', 'Tabaco Kijih', 2, 0.00, 0.00, 1, 650, 0, 1, '2026-10-18 23:34:40'),
(5, '005', 'Chimo Tapa Azul', 3, 0.58, 1.20, 1, 110, 0, 1, '2026-10-18 23:34:51'),
(6, '006', 'Chimo Tapa Amarilla', 3, 0.58, 1.20, 1, 105, 0, 1, '2026-10-18 23:35:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `nombre_contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `rif`, `razon_social`, `nombre_contacto`, `telefono`, `email`, `direccion`, `estado`) VALUES
(1, 'J-12345678-7', 'Comercio Divino Jesus', 'Elvis Santamaria', '04120765743', 'DivinoJesus@gmail.com', 'AV principal centro de tinaquillo', 1),
(2, 'J-12345678-9', 'Santo Domingo Cordova', 'Eduardo Brito', '04121498756', 'SantoDomingo@gmail.com', 'Valencia estado Carabobo', 1),
(3, 'J-98765432-1', 'Valle Verde', 'Beirut Montagne', '04127432158', 'Beirut199@gmail.com', 'Tinaquillo Estado Cojedes', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_connection` datetime DEFAULT NULL,
  `rol` varchar(10) NOT NULL,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password_hash`, `last_connection`, `rol`, `is_active`) VALUES
(1, 'admin', '$2y$10$hodgOVW4SJL1icrdw6t6gOD1qt0JMTKcVo1iwehgiHBM24cMYkCOO', NULL, 'admin', 1),
(9, 'Miguel', '$2y$10$HoYieV4OUSVGRfJWgTfxhO1B96XCVYAKJs/GDfa/a4byToaHvmx9u', NULL, 'operador', 1),
(10, 'Manuel', '$2y$10$K.CxpClIjhPamJ9LNsmBDe6v8enEDNtGX41./MK4aDH/Gum773U76', NULL, 'admin', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `numero_factura` varchar(20) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(12,2) NOT NULL,
  `monto_iva` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `tipo_pago` varchar(15) NOT NULL DEFAULT 'efectivo',
  `tasa_iva` decimal(5,2) NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 registrada, 0 anulada',
  `id_usuario` int(11) NOT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `id_usuario_anula` int(11) DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `numero_factura`, `id_cliente`, `fecha_venta`, `subtotal`, `monto_iva`, `total`, `tipo_pago`, `tasa_iva`, `observaciones`, `estado`, `id_usuario`, `fecha_anulacion`, `id_usuario_anula`, `motivo_anulacion`) VALUES
(1, 'FAC-000001', 1, '2026-10-28 17:14:46', 24.00, 3.84, 27.84, 'efectivo', 16.00, NULL, 1, 10, NULL, NULL, NULL),
(2, 'FAC-000002', 1, '2026-10-28 17:18:29', 14.40, 2.30, 16.70, 'mixto', 16.00, 'Entregado en sucursal principal', 0, 10, '2026-10-28 13:18:57', 10, 'Error de Factura'),
(3, 'FAC-000003', 1, '2026-10-28 23:26:35', 6.00, 0.96, 6.96, 'efectivo', 16.00, NULL, 1, 10, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_pagos`
--

CREATE TABLE `venta_pagos` (
  `id_pago` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `metodo` varchar(15) NOT NULL,
  `monto` decimal(12,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `venta_pagos`
--

INSERT INTO `venta_pagos` (`id_pago`, `id_venta`, `metodo`, `monto`) VALUES
(1, 1, 'efectivo', 27.84),
(2, 2, 'efectivo', 10.00),
(3, 2, 'transferencia', 6.70),
(4, 3, 'efectivo', 6.96);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `num_documento` (`num_documento`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`),
  ADD UNIQUE KEY `uq_compras_factura` (`id_proveedor`,`numero_factura`),
  ADD KEY `fk_compras_usuario` (`id_usuario`),
  ADD KEY `fk_compras_usuario_anula` (`id_usuario_anula`);

--
-- Indices de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_compra` (`id_compra`),
  ADD KEY `fk_detalle_producto` (`id_producto`),
  ADD KEY `fk_detalle_presentacion` (`id_presentacion`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD UNIQUE KEY `uq_detventa_venta_producto` (`id_venta`,`id_producto`),
  ADD KEY `fk_detventa_producto` (`id_producto`);

--
-- Indices de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_hist_producto_fecha` (`id_producto`,`fecha`),
  ADD KEY `fk_hist_compra` (`id_compra`),
  ADD KEY `fk_hist_usuario` (`id_usuario`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_mov_producto_fecha` (`id_producto`,`fecha`),
  ADD KEY `fk_mov_compra` (`id_compra`),
  ADD KEY `fk_mov_usuario` (`id_usuario`),
  ADD KEY `fk_movimiento_vta` (`id_venta`);

--
-- Indices de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD PRIMARY KEY (`id_presentacion`),
  ADD UNIQUE KEY `uq_present_producto_etiqueta` (`id_producto`,`etiqueta`),
  ADD KEY `fk_present_usuario` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `rif` (`rif`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `uq_ventas_numero_factura` (`numero_factura`),
  ADD KEY `fk_ventas_cliente` (`id_cliente`),
  ADD KEY `fk_ventas_usuario` (`id_usuario`),
  ADD KEY `fk_ventas_usuario_anula` (`id_usuario_anula`);

--
-- Indices de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD UNIQUE KEY `uq_ventapago_venta_metodo` (`id_venta`,`metodo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  MODIFY `id_presentacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario_anula` FOREIGN KEY (`id_usuario_anula`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `fk_detalle_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_presentacion` FOREIGN KEY (`id_presentacion`) REFERENCES `presentaciones` (`id_presentacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `fk_detventa_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detventa_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD CONSTRAINT `fk_hist_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD CONSTRAINT `fk_mov_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movimiento_vta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

--
-- Filtros para la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD CONSTRAINT `fk_present_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_present_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_ventas_usuario_anula` FOREIGN KEY (`id_usuario_anula`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  ADD CONSTRAINT `fk_ventapago_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
