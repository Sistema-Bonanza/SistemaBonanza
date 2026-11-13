-- =====================================================================
-- migracion_mejoras_nov2026.sql
-- Ejecutar UNA vez sobre la base `sistema_bonanza` (MariaDB 10.4+).
-- Es segura de repetir: usa IF NOT EXISTS en todo.
--
-- Qué agrega:
--   1. productos.unidad_manejo / unidades_por_empaque   (editar producto: "unidades del producto")
--   2. precios_cantidad                                  (precio por cantidad / descuento por volumen)
--   3. tasas_cambio                                      (caché diaria de la tasa BCV)
--   4. compras.tasa_cambio y ventas.tasa_cambio          (tasa BCV usada al registrar cada operación)
--   5. detalle_ventas.precio_lista / descuento_pct       (rastro del descuento por cantidad aplicado)
-- =====================================================================

START TRANSACTION;

-- 1. Unidad de manejo del producto ------------------------------------
ALTER TABLE `productos`
  ADD COLUMN IF NOT EXISTS `unidad_manejo` VARCHAR(30) NOT NULL DEFAULT 'UNIDAD' AFTER `stock_minimo`,
  ADD COLUMN IF NOT EXISTS `unidades_por_empaque` INT(11) NOT NULL DEFAULT 1 AFTER `unidad_manejo`;

-- 2. Precio por cantidad ----------------------------------------------
CREATE TABLE IF NOT EXISTS `precios_cantidad` (
  `id_escala`        INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto`      INT(11) NOT NULL,
  `cantidad_minima`  INT(11) NOT NULL,
  `descuento_pct`    DECIMAL(5,2) NOT NULL,
  `id_usuario`       INT(11) NOT NULL,
  `actualizado_en`   TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_escala`),
  UNIQUE KEY `uq_escala_producto_cantidad` (`id_producto`,`cantidad_minima`),
  KEY `fk_escala_usuario` (`id_usuario`),
  CONSTRAINT `fk_escala_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_escala_usuario`  FOREIGN KEY (`id_usuario`)  REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_escala_cantidad` CHECK (`cantidad_minima` >= 2),
  CONSTRAINT `chk_escala_descuento` CHECK (`descuento_pct` > 0 AND `descuento_pct` < 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Caché de la tasa BCV ----------------------------------------------
CREATE TABLE IF NOT EXISTS `tasas_cambio` (
  `fecha`       DATE NOT NULL,
  `tasa`        DECIMAL(14,4) NOT NULL,
  `fuente`      VARCHAR(40) NOT NULL DEFAULT 'BCV',
  `manual`      TINYINT(1) NOT NULL DEFAULT 0,
  `id_usuario`  INT(11) DEFAULT NULL,
  `creado_en`   TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tasa usada en cada compra y venta ----------------------------------
ALTER TABLE `compras` ADD COLUMN IF NOT EXISTS `tasa_cambio` DECIMAL(14,4) DEFAULT NULL;
ALTER TABLE `ventas`  ADD COLUMN IF NOT EXISTS `tasa_cambio` DECIMAL(14,4) DEFAULT NULL;

-- 5. Descuento por cantidad aplicado en cada línea de venta -------------
ALTER TABLE `detalle_ventas`
  ADD COLUMN IF NOT EXISTS `precio_lista`  DECIMAL(10,2) DEFAULT NULL AFTER `precio_unitario`,
  ADD COLUMN IF NOT EXISTS `descuento_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `precio_lista`;

COMMIT;
