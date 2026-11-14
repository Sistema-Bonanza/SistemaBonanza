-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2026 a las 18:36:30
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
-- Estructura de tabla para la tabla `cierres_caja`
--

CREATE TABLE `cierres_caja` (
  `id_cierre` int(11) NOT NULL,
  `fecha_cierre` date NOT NULL COMMENT 'Día cerrado (uno por día)',
  `facturas` int(11) NOT NULL DEFAULT 0,
  `total_vendido` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Ventas registradas, con IVA',
  `total_cobrado` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Todo menos crédito',
  `efectivo_sistema` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Cobrado en efectivo según el sistema',
  `efectivo_contado` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Efectivo contado en gaveta',
  `diferencia` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'contado - sistema',
  `observaciones` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'Quién hizo el cierre',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cierres_caja`
--

INSERT INTO `cierres_caja` (`id_cierre`, `fecha_cierre`, `facturas`, `total_vendido`, `total_cobrado`, `efectivo_sistema`, `efectivo_contado`, `diferencia`, `observaciones`, `id_usuario`, `creado_en`) VALUES
(1, '2026-10-28', 2, 34.80, 34.80, 34.80, 34.80, 0.00, 'En perfecto estado', 10, '2026-11-06 23:53:53'),
(2, '2026-11-06', 3, 24.68, 24.68, 4.17, 4.17, 0.00, 'En perfecto estado', 10, '2026-11-06 23:55:38');

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
(1, 'V', '32456745', 'Julio Perez', '0414468521', 'julioeldibujante@gmail.com', 'Valencia Estado Carabobo', 1, '2026-10-28 17:12:52'),
(2, 'V', '31267469', 'Cliente General', '0000000000', 'cliente@gmail.com', 'Sucursal', 1, '2026-11-06 21:49:50');

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
  `tasa_bcv` decimal(12,4) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `id_usuario_anula` int(11) DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `tasa_cambio` decimal(14,4) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `id_proveedor`, `numero_factura`, `fecha_factura`, `fecha_registro`, `subtotal`, `tasa_iva`, `monto_iva`, `total`, `tasa_bcv`, `observaciones`, `estado`, `id_usuario`, `fecha_anulacion`, `id_usuario_anula`, `motivo_anulacion`, `tasa_cambio`) VALUES
(1, 1, '001', '2026-10-18', '2026-10-18 23:41:34', 280.00, 16.00, 44.80, 324.80, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL),
(2, 2, '002', '2026-10-18', '2026-10-18 23:42:38', 66.00, 16.00, 10.56, 76.56, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL),
(3, 1, '003', '2026-10-18', '2026-10-18 23:43:43', 130.00, 16.00, 20.80, 150.80, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL),
(4, 1, '004', '2026-10-20', '2026-10-21 00:01:42', 70.00, 16.00, 11.20, 81.20, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL);

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
  `precio_lista` decimal(10,2) DEFAULT NULL,
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `aplica_iva` tinyint(1) NOT NULL,
  `subtotal_linea` decimal(12,2) NOT NULL,
  `costo_unitario` decimal(12,4) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `precio_lista`, `descuento_pct`, `aplica_iva`, `subtotal_linea`, `costo_unitario`) VALUES
(1, 1, 6, 10, 1.20, NULL, 0.00, 1, 12.00, 0.5800),
(2, 1, 5, 10, 1.20, NULL, 0.00, 1, 12.00, 0.5800),
(3, 2, 6, 6, 1.20, NULL, 0.00, 1, 7.20, 0.5800),
(4, 2, 5, 6, 1.20, NULL, 0.00, 1, 7.20, 0.5800),
(5, 3, 6, 5, 1.20, NULL, 0.00, 1, 6.00, 0.5800),
(6, 4, 6, 1, 1.20, NULL, 0.00, 1, 1.20, 0.5800),
(7, 4, 5, 1, 1.20, NULL, 0.00, 1, 1.20, 0.5800),
(8, 5, 5, 1, 1.20, NULL, 0.00, 1, 1.20, 0.5800),
(9, 6, 6, 5, 1.20, NULL, 0.00, 1, 6.00, 0.5800),
(10, 6, 1, 4, 1.12, NULL, 0.00, 1, 4.48, 0.6000),
(11, 6, 2, 3, 1.00, NULL, 0.00, 1, 3.00, 0.8000),
(12, 6, 4, 4, 1.05, NULL, 0.00, 1, 4.20, 0.7500),
(13, 7, 2, 1, 1.00, NULL, 0.00, 1, 1.00, 0.8000),
(14, 7, 5, 1, 1.20, NULL, 0.00, 1, 1.20, 0.5800);

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
(2, 5, 0.00, 0.58, 0.00, 1.20, NULL, 10, '2026-10-28 17:13:46'),
(3, 2, 0.00, 0.80, 0.00, 1.00, NULL, 10, '2026-11-06 21:44:39'),
(4, 1, 0.00, 0.60, 0.00, 1.12, NULL, 10, '2026-11-06 21:45:11'),
(5, 4, 0.00, 0.75, 0.00, 1.05, NULL, 10, '2026-11-06 21:45:37'),
(6, 3, 0.00, 1.00, 0.00, 1.45, NULL, 10, '2026-11-06 21:46:14');

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
(14, 6, 'salida', 5, 110, 105, 'Factura FAC-000003', NULL, 10, 3, '2026-10-28 23:26:35'),
(15, 6, 'salida', 1, 105, 104, 'Factura FAC-000004', NULL, 10, 4, '2026-11-06 21:42:43'),
(16, 5, 'salida', 1, 110, 109, 'Factura FAC-000004', NULL, 10, 4, '2026-11-06 21:42:43'),
(17, 5, 'salida', 1, 109, 108, 'Factura FAC-000005', NULL, 10, 5, '2026-11-06 21:43:21'),
(18, 6, 'salida', 5, 104, 99, 'Factura FAC-000006', NULL, 10, 6, '2026-11-06 21:51:39'),
(19, 1, 'salida', 4, 200, 196, 'Factura FAC-000006', NULL, 10, 6, '2026-11-06 21:51:39'),
(20, 2, 'salida', 3, 200, 197, 'Factura FAC-000006', NULL, 10, 6, '2026-11-06 21:51:39'),
(21, 4, 'salida', 4, 650, 646, 'Factura FAC-000006', NULL, 10, 6, '2026-11-06 21:51:39'),
(22, 2, 'salida', 1, 197, 196, 'Factura FAC-000007', NULL, 10, 7, '2026-11-11 15:05:54'),
(23, 5, 'salida', 1, 108, 107, 'Factura FAC-000007', NULL, 10, 7, '2026-11-11 15:05:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precios_cantidad`
--

CREATE TABLE `precios_cantidad` (
  `id_precio_cantidad` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad_min` int(11) NOT NULL,
  `modo` enum('porcentaje','precio') NOT NULL DEFAULT 'porcentaje',
  `valor` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `unidad_manejo` varchar(30) NOT NULL DEFAULT 'UNIDAD',
  `unidades_por_empaque` int(11) NOT NULL DEFAULT 1,
  `unidad_medida` varchar(20) NOT NULL DEFAULT 'Unidad',
  `estado` tinyint(1) DEFAULT 1,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo`, `nombre`, `id_categoria`, `precio_compra`, `precio_venta`, `aplica_iva`, `stock_actual`, `stock_minimo`, `unidad_manejo`, `unidades_por_empaque`, `unidad_medida`, `estado`, `create_at`) VALUES
(1, '001', 'Cigarro Lucky', 1, 0.60, 1.12, 1, 196, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:33:59'),
(2, '002', 'Cigarro Belmont', 1, 0.80, 1.00, 1, 196, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:34:15'),
(3, '003', 'Tabaco La Hollada', 2, 1.00, 1.45, 1, 150, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:34:29'),
(4, '004', 'Tabaco Kijih', 2, 0.75, 1.05, 1, 646, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:34:40'),
(5, '005', 'Chimo Tapa Azul', 3, 0.58, 1.20, 1, 107, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:34:51'),
(6, '006', 'Chimo Tapa Amarilla', 3, 0.58, 1.20, 1, 99, 0, 'UNIDAD', 1, 'Unidad', 1, '2026-10-18 23:35:05'),
(7, 'Gh0015', 'Cigarros Vicerroy', 3, 0.00, 0.00, 1, 0, 2, 'UNIDAD', 1, 'Unidad', 1, '2026-11-13 15:33:37');

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
-- Estructura de tabla para la tabla `tasas_cambio`
--

CREATE TABLE `tasas_cambio` (
  `fecha` date NOT NULL,
  `tasa` decimal(12,4) NOT NULL,
  `fuente` varchar(20) NOT NULL DEFAULT 'api',
  `id_usuario` int(11) DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tasas_cambio`
--

INSERT INTO `tasas_cambio` (`fecha`, `tasa`, `fuente`, `id_usuario`, `actualizado_en`) VALUES
('2026-11-12', 904.4485, 'api', NULL, '2026-11-12 15:47:51');

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
  `tasa_bcv` decimal(12,4) DEFAULT NULL,
  `tipo_pago` varchar(15) NOT NULL DEFAULT 'efectivo',
  `tasa_iva` decimal(5,2) NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 registrada, 0 anulada',
  `id_usuario` int(11) NOT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `id_usuario_anula` int(11) DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `tasa_cambio` decimal(14,4) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `numero_factura`, `id_cliente`, `fecha_venta`, `subtotal`, `monto_iva`, `total`, `tasa_bcv`, `tipo_pago`, `tasa_iva`, `observaciones`, `estado`, `id_usuario`, `fecha_anulacion`, `id_usuario_anula`, `motivo_anulacion`, `tasa_cambio`) VALUES
(1, 'FAC-000001', 1, '2026-10-28 17:14:46', 24.00, 3.84, 27.84, NULL, 'efectivo', 16.00, NULL, 1, 10, NULL, NULL, NULL, NULL),
(2, 'FAC-000002', 1, '2026-10-28 17:18:29', 14.40, 2.30, 16.70, NULL, 'mixto', 16.00, 'Entregado en sucursal principal', 0, 10, '2026-10-28 13:18:57', 10, 'Error de Factura', NULL),
(3, 'FAC-000003', 1, '2026-10-28 23:26:35', 6.00, 0.96, 6.96, NULL, 'efectivo', 16.00, NULL, 1, 10, NULL, NULL, NULL, NULL),
(4, 'FAC-000004', 1, '2026-11-06 21:42:43', 2.40, 0.38, 2.78, NULL, 'efectivo', 16.00, 'Entregado en sucursal principal', 1, 10, NULL, NULL, NULL, NULL),
(5, 'FAC-000005', 1, '2026-11-06 21:43:21', 1.20, 0.19, 1.39, NULL, 'efectivo', 16.00, 'Entregado en sucursal principal', 1, 10, NULL, NULL, NULL, NULL),
(6, 'FAC-000006', 2, '2026-11-06 21:51:39', 17.68, 2.83, 20.51, NULL, 'tarjeta', 16.00, 'Entregado en sucursal principal', 1, 10, NULL, NULL, NULL, NULL),
(7, 'FAC-000007', 2, '2026-11-11 15:05:54', 2.20, 0.35, 2.55, NULL, 'efectivo', 16.00, 'Entregado en sucursal principal', 1, 10, NULL, NULL, NULL, NULL);

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
(4, 3, 'efectivo', 6.96),
(5, 4, 'efectivo', 2.78),
(6, 5, 'efectivo', 1.39),
(7, 6, 'tarjeta', 20.51),
(8, 7, 'efectivo', 2.55);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `cierres_caja`
--
ALTER TABLE `cierres_caja`
  ADD PRIMARY KEY (`id_cierre`),
  ADD UNIQUE KEY `uq_cierre_fecha` (`fecha_cierre`),
  ADD KEY `fk_cierre_usuario` (`id_usuario`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `num_documento` (`num_documento`),
  ADD KEY `idx_cliente_nombre` (`nombre_razon_social`);

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
-- Indices de la tabla `precios_cantidad`
--
ALTER TABLE `precios_cantidad`
  ADD PRIMARY KEY (`id_precio_cantidad`),
  ADD UNIQUE KEY `uq_producto_cantidad` (`id_producto`,`cantidad_min`),
  ADD KEY `idx_precio_cantidad_producto` (`id_producto`);

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
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `idx_producto_nombre` (`nombre`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `rif` (`rif`),
  ADD KEY `idx_proveedor_nombre` (`razon_social`);

--
-- Indices de la tabla `tasas_cambio`
--
ALTER TABLE `tasas_cambio`
  ADD PRIMARY KEY (`fecha`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_usuario_nombre` (`username`);

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
-- AUTO_INCREMENT de la tabla `cierres_caja`
--
ALTER TABLE `cierres_caja`
  MODIFY `id_cierre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT de la tabla `precios_cantidad`
--
ALTER TABLE `precios_cantidad`
  MODIFY `id_precio_cantidad` int(11) NOT NULL AUTO_INCREMENT;

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
-- Filtros para la tabla `cierres_caja`
--
ALTER TABLE `cierres_caja`
  ADD CONSTRAINT `fk_cierre_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

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
-- Filtros para la tabla `precios_cantidad`
--
ALTER TABLE `precios_cantidad`
  ADD CONSTRAINT `fk_precio_cantidad_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

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
