CREATE DATABASE  IF NOT EXISTS `sistema_bonanza` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `sistema_bonanza`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: sistema_bonanza
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Cigarros','Productos derivados del Tabaco',1),(2,'Tabaco','Productos derivados del Tabaco',1),(3,'Chimo','pasta de tabaco de mascar',1);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_clientes` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text DEFAULT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_clientes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL AUTO_INCREMENT,
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
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_compra`),
  UNIQUE KEY `uq_compras_factura` (`id_proveedor`,`numero_factura`),
  KEY `fk_compras_usuario` (`id_usuario`),
  KEY `fk_compras_usuario_anula` (`id_usuario_anula`),
  CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_usuario_anula` FOREIGN KEY (`id_usuario_anula`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_compras_estado` CHECK (`estado` in (0,1)),
  CONSTRAINT `chk_compras_montos` CHECK (`subtotal` >= 0 and `monto_iva` >= 0 and `total` >= 0),
  CONSTRAINT `chk_compras_tasa` CHECK (`tasa_iva` >= 0 and `tasa_iva` <= 100),
  CONSTRAINT `chk_compras_total` CHECK (`total` = `subtotal` + `monto_iva`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (15,1,'001','2026-10-08','2026-10-08 15:57:25',3175.00,16.00,220.00,3395.00,NULL,1,1,NULL,NULL,NULL),(16,2,'002','2026-10-08','2026-10-08 21:33:42',435.00,16.00,16.80,451.80,NULL,1,1,NULL,NULL,NULL),(18,1,'003','2026-10-11','2026-10-11 18:30:48',210.00,16.00,33.60,243.60,NULL,1,1,NULL,NULL,NULL),(19,2,'004','2026-10-11','2026-10-11 18:35:02',1490.00,16.00,174.40,1664.40,NULL,1,1,NULL,NULL,NULL),(21,1,'005','2026-10-12','2026-10-12 01:13:08',180.00,16.00,28.80,208.80,NULL,1,9,NULL,NULL,NULL),(22,1,'006','2026-10-11','2026-10-12 01:30:10',255.00,16.00,40.80,295.80,NULL,1,9,NULL,NULL,NULL),(23,1,'007','2026-10-14','2026-10-14 15:15:17',90.00,16.00,14.40,104.40,NULL,1,1,NULL,NULL,NULL),(24,1,'009','2026-02-21','2026-10-14 15:38:41',1.00,16.00,0.16,1.16,NULL,1,1,NULL,NULL,NULL),(25,2,'010','2026-10-14','2026-10-14 15:44:10',525.00,16.00,84.00,609.00,NULL,1,1,NULL,NULL,NULL),(26,1,'011','2026-10-01','2026-10-14 16:01:23',3.00,16.00,0.48,3.48,NULL,1,1,NULL,NULL,NULL),(27,1,'012','2026-10-14','2026-10-14 20:14:55',18.00,16.00,2.88,20.88,NULL,1,1,NULL,NULL,NULL),(28,1,'013','2026-10-14','2026-10-14 20:16:15',155.00,16.00,10.40,165.40,NULL,1,1,NULL,NULL,NULL),(29,1,'014','2026-10-14','2026-10-14 21:26:58',18.00,16.00,2.88,20.88,NULL,1,1,NULL,NULL,NULL),(32,2,'015','2026-10-14','2026-10-14 22:41:47',312.00,16.00,32.64,344.64,NULL,1,1,NULL,NULL,NULL),(33,1,'016','2026-10-14','2026-10-14 22:55:04',9.00,16.00,1.44,10.44,NULL,1,1,NULL,NULL,NULL),(35,2,'017','2026-10-14','2026-10-14 23:09:16',20.00,16.00,2.56,22.56,NULL,1,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_compras`
--

DROP TABLE IF EXISTS `detalle_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_compras` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
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
  `subtotal_linea` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `fk_detalle_compra` (`id_compra`),
  KEY `fk_detalle_producto` (`id_producto`),
  KEY `fk_detalle_presentacion` (`id_presentacion`),
  CONSTRAINT `fk_detalle_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_presentacion` FOREIGN KEY (`id_presentacion`) REFERENCES `presentaciones` (`id_presentacion`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `chk_det_cant_empaques` CHECK (`cantidad_empaques` > 0),
  CONSTRAINT `chk_det_unid_empaque` CHECK (`unidades_por_empaque` > 0),
  CONSTRAINT `chk_det_cant_unidades` CHECK (`cantidad_unidades` > 0),
  CONSTRAINT `chk_det_costo_empaque` CHECK (`costo_por_empaque` > 0),
  CONSTRAINT `chk_det_costo_unitario` CHECK (`costo_unitario` > 0),
  CONSTRAINT `chk_det_aplica_iva` CHECK (`aplica_iva` in (0,1)),
  CONSTRAINT `chk_det_unidades_calc` CHECK (`cantidad_unidades` = `cantidad_empaques` * `unidades_por_empaque`),
  CONSTRAINT `chk_det_subtotal_calc` CHECK (`subtotal_linea` = `cantidad_empaques` * `costo_por_empaque`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_compras`
--

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
INSERT INTO `detalle_compras` VALUES (3,15,4,NULL,'brazo_cigarros',25,10,250,55.00,5.5000,1,1375.00),(4,15,3,NULL,'brazo_cigarros',30,10,300,60.00,6.0000,0,1800.00),(5,16,4,NULL,'brazo_cigarros',15,10,150,7.00,0.7000,1,105.00),(6,16,3,NULL,'brazo_cigarros',30,10,300,11.00,1.1000,0,330.00),(8,18,2,NULL,'paquete_tabaco',15,50,750,6.00,0.1200,1,90.00),(9,18,1,NULL,'paquete_chimo',30,12,360,4.00,0.3333,1,120.00),(10,19,4,NULL,'brazo_cigarros',40,10,400,12.00,1.2000,1,480.00),(11,19,3,NULL,'brazo_cigarros',40,10,400,10.00,1.0000,0,400.00),(12,19,2,NULL,'paquete_tabaco',30,50,1500,15.00,0.3000,1,450.00),(13,19,1,NULL,'paquete_chimo',20,12,240,8.00,0.6667,1,160.00),(14,21,1,NULL,'paquete_chimo',20,12,240,9.00,0.7500,1,180.00),(15,22,5,NULL,'paquete_tabaco',15,50,750,17.00,0.3400,1,255.00),(16,23,1,NULL,'paquete_chimo',10,12,120,9.00,0.7500,1,90.00),(17,24,4,NULL,'brazo_cigarros',1,10,10,1.00,0.1000,1,1.00),(18,25,4,NULL,'brazo_cigarros',10,10,100,50.00,5.0000,1,500.00),(19,25,4,NULL,'brazo_cigarros',5,10,50,5.00,0.5000,1,25.00),(20,26,2,NULL,'paquete_tabaco',1,50,50,3.00,0.0600,1,3.00),(21,27,5,NULL,'paquete_tabaco',2,50,100,9.00,0.1800,1,18.00),(22,28,5,NULL,'paquete_tabaco',5,50,250,13.00,0.2600,1,65.00),(23,28,3,NULL,'brazo_cigarros',10,10,100,9.00,0.9000,0,90.00),(24,29,5,NULL,'paquete_tabaco',3,50,150,6.00,0.1200,1,18.00),(25,32,1,NULL,'paquete_chimo',12,12,144,6.00,0.5000,1,72.00),(26,32,3,NULL,'brazo_cigarros',12,10,120,9.00,0.9000,0,108.00),(27,32,5,NULL,'paquete_tabaco',12,50,600,11.00,0.2200,1,132.00),(28,33,5,NULL,'paquete_tabaco',1,50,50,9.00,0.1800,1,9.00),(29,35,1,NULL,'paquete_chimo',1,12,12,4.00,0.3333,1,4.00),(30,35,2,NULL,'paquete_tabaco',1,50,50,4.00,0.0800,1,4.00),(31,35,3,NULL,'brazo_cigarros',1,10,10,4.00,0.4000,0,4.00),(32,35,4,NULL,'brazo_cigarros',1,10,10,4.00,0.4000,1,4.00),(33,35,5,NULL,'paquete_tabaco',1,50,50,4.00,0.0800,1,4.00);
/*!40000 ALTER TABLE `detalle_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_precios`
--

DROP TABLE IF EXISTS `historial_precios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_precios` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `precio_compra_anterior` decimal(10,2) NOT NULL,
  `precio_compra_nuevo` decimal(10,2) NOT NULL,
  `precio_venta_anterior` decimal(10,2) NOT NULL,
  `precio_venta_nuevo` decimal(10,2) NOT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `idx_hist_producto_fecha` (`id_producto`,`fecha`),
  KEY `fk_hist_compra` (`id_compra`),
  KEY `fk_hist_usuario` (`id_usuario`),
  CONSTRAINT `fk_hist_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hist_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hist_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_hist_precios` CHECK (`precio_compra_anterior` >= 0 and `precio_compra_nuevo` >= 0 and `precio_venta_anterior` >= 0 and `precio_venta_nuevo` >= 0),
  CONSTRAINT `chk_hist_hubo_cambio` CHECK (`precio_compra_anterior` <> `precio_compra_nuevo` or `precio_venta_anterior` <> `precio_venta_nuevo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_precios`
--

LOCK TABLES `historial_precios` WRITE;
/*!40000 ALTER TABLE `historial_precios` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_precios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos`
--

DROP TABLE IF EXISTS `movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_antes` int(11) NOT NULL,
  `stock_despues` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_mov_producto_fecha` (`id_producto`,`fecha`),
  KEY `fk_mov_compra` (`id_compra`),
  KEY `fk_mov_usuario` (`id_usuario`),
  CONSTRAINT `fk_mov_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_mov_cantidad` CHECK (`cantidad` > 0),
  CONSTRAINT `chk_mov_stock` CHECK (`stock_antes` >= 0 and `stock_despues` >= 0),
  CONSTRAINT `chk_mov_coherencia` CHECK (`tipo` = 'entrada' and `stock_despues` = `stock_antes` + `cantidad` or `tipo` = 'salida' and `stock_despues` = `stock_antes` - `cantidad`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos`
--

LOCK TABLES `movimientos` WRITE;
/*!40000 ALTER TABLE `movimientos` DISABLE KEYS */;
INSERT INTO `movimientos` VALUES (2,4,'entrada',250,0,250,'Compra Factura #001',15,1,'2026-10-08 15:57:25'),(3,3,'entrada',300,0,300,'Compra Factura #001',15,1,'2026-10-08 15:57:25'),(4,4,'entrada',150,250,400,'Compra Factura #002',16,1,'2026-10-08 21:33:42'),(5,3,'entrada',300,300,600,'Compra Factura #002',16,1,'2026-10-08 21:33:42'),(7,2,'entrada',750,0,750,'Compra Factura #003',18,1,'2026-10-11 18:30:48'),(8,1,'entrada',360,0,360,'Compra Factura #003',18,1,'2026-10-11 18:30:48'),(9,4,'entrada',400,400,800,'Compra Factura #004',19,1,'2026-10-11 18:35:02'),(10,3,'entrada',400,600,1000,'Compra Factura #004',19,1,'2026-10-11 18:35:02'),(11,2,'entrada',1500,750,2250,'Compra Factura #004',19,1,'2026-10-11 18:35:02'),(12,1,'entrada',240,360,600,'Compra Factura #004',19,1,'2026-10-11 18:35:02'),(13,1,'entrada',240,600,840,'Compra Factura #005',21,9,'2026-10-12 01:13:08'),(14,5,'entrada',750,0,750,'Compra Factura #006',22,9,'2026-10-12 01:30:10'),(15,1,'entrada',120,840,960,'Compra Factura #007',23,1,'2026-10-14 15:15:17'),(16,4,'entrada',10,800,810,'Compra Factura #009',24,1,'2026-10-14 15:38:41'),(17,4,'entrada',100,810,910,'Compra Factura #010',25,1,'2026-10-14 15:44:10'),(18,4,'entrada',50,910,960,'Compra Factura #010',25,1,'2026-10-14 15:44:10'),(19,2,'entrada',50,2250,2300,'Compra Factura #011',26,1,'2026-10-14 16:01:23'),(20,5,'entrada',100,750,850,'Compra Factura #012',27,1,'2026-10-14 20:14:55'),(21,5,'entrada',250,850,1100,'Compra Factura #013',28,1,'2026-10-14 20:16:15'),(22,3,'entrada',100,1000,1100,'Compra Factura #013',28,1,'2026-10-14 20:16:15'),(23,5,'entrada',150,1100,1250,'Compra Factura #014',29,1,'2026-10-14 21:26:58'),(24,1,'entrada',144,960,1104,'Compra Factura #015',32,1,'2026-10-14 22:41:47'),(25,3,'entrada',120,1100,1220,'Compra Factura #015',32,1,'2026-10-14 22:41:47'),(26,5,'entrada',600,1250,1850,'Compra Factura #015',32,1,'2026-10-14 22:41:47'),(27,5,'entrada',50,1850,1900,'Compra Factura #016',33,1,'2026-10-14 22:55:04'),(28,1,'entrada',12,1104,1116,'Compra Factura #017',35,1,'2026-10-14 23:09:16'),(29,2,'entrada',50,2300,2350,'Compra Factura #017',35,1,'2026-10-14 23:09:16'),(30,3,'entrada',10,1220,1230,'Compra Factura #017',35,1,'2026-10-14 23:09:16'),(31,4,'entrada',10,960,970,'Compra Factura #017',35,1,'2026-10-14 23:09:16'),(32,5,'entrada',50,1900,1950,'Compra Factura #017',35,1,'2026-10-14 23:09:16');
/*!40000 ALTER TABLE `movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presentaciones`
--

DROP TABLE IF EXISTS `presentaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presentaciones` (
  `id_presentacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `etiqueta` varchar(30) NOT NULL,
  `unidades` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_presentacion`),
  UNIQUE KEY `uq_present_producto_etiqueta` (`id_producto`,`etiqueta`),
  KEY `fk_present_usuario` (`id_usuario`),
  CONSTRAINT `fk_present_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_present_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_present_unidades` CHECK (`unidades` > 0 and `unidades` <= 1000),
  CONSTRAINT `chk_present_estado` CHECK (`estado` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presentaciones`
--

LOCK TABLES `presentaciones` WRITE;
/*!40000 ALTER TABLE `presentaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `presentaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `aplica_iva` tinyint(1) DEFAULT 1,
  `stock_actual` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `estado` tinyint(1) DEFAULT 1,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'001','Chimo Tapa Azul',3,0.00,0.00,1,1116,0,1,'2026-09-27 19:04:07'),(2,'002','Tabaco Horacio',2,0.00,0.00,1,2350,0,1,'2026-09-27 19:04:16'),(3,'003','Cigarro Don Ramon',1,0.00,0.00,0,1230,0,1,'2026-09-27 19:04:30'),(4,'004','Cigarro Lucky',1,0.00,0.00,1,970,0,1,'2026-10-03 22:39:09'),(5,'005','Tabaco La Hollada',2,0.00,0.00,1,1950,0,1,'2026-10-12 01:25:10');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `rif` varchar(20) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `nombre_contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_proveedor`),
  UNIQUE KEY `rif` (`rif`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'J-12345678-7','Comercio Divino Jesus','Elvis Santamaria','04120765743','DivinoJesus@gmail.com','AV principal centro de tinaquillo',1),(2,'J-12345678-9','Santo Domingo Cordova','Eduardo Brito','04121498756','SantoDomingo@gmail.com','Valencia estado Carabobo',1),(3,'J-98765432-1','Valle Verde','Beirut Montagne','04127432158','Beirut199@gmail.com','Tinaquillo Estado Cojedes',0);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_connection` datetime DEFAULT NULL,
  `rol` varchar(10) NOT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','$2y$10$hodgOVW4SJL1icrdw6t6gOD1qt0JMTKcVo1iwehgiHBM24cMYkCOO',NULL,'admin',1),(9,'Miguel','$2y$10$HoYieV4OUSVGRfJWgTfxhO1B96XCVYAKJs/GDfa/a4byToaHvmx9u',NULL,'Operador',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sistema_bonanza'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 23:45:28
