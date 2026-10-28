-- =====================================================================
-- VENTAS: TIPO DE PAGO + FACTURA + PAGOS MIXTOS
-- MariaDB 10.4 (XAMPP). Ejecuta CADA bloque por separado, en orden,
-- desde la pestaña SQL de la base sistema_bonanza.
-- phpMyAdmin puede mostrar "errores de análisis estático" en los CHECK:
-- es solo una advertencia de su analizador; vale el recuadro verde/rojo de abajo.
-- Si un bloque dice "Duplicate ..." / "already exists", ya estaba aplicado.
-- =====================================================================
USE sistema_bonanza;

-- 1) ventas: forma de pago y número de factura
ALTER TABLE ventas
    ADD COLUMN IF NOT EXISTS numero_factura VARCHAR(20) NULL AFTER id_venta,
    ADD COLUMN IF NOT EXISTS tipo_pago VARCHAR(15) NOT NULL DEFAULT 'efectivo' AFTER total;

-- 2) Si ya hiciste ventas de prueba: se les asigna su factura (FAC- + el mismo número de la venta)
UPDATE ventas
   SET numero_factura = CONCAT('FAC-', LPAD(id_venta, 6, '0'))
 WHERE numero_factura IS NULL;

-- 3) Restricciones de ventas
ALTER TABLE ventas
    ADD CONSTRAINT uq_ventas_numero_factura UNIQUE (numero_factura);

ALTER TABLE ventas
    ADD CONSTRAINT chk_ventas_tipo_pago
    CHECK (tipo_pago IN ('efectivo','tarjeta','transferencia','divisa','credito','mixto'));

-- 4) Pagos de cada venta: una fila por forma de pago usada
--    (venta normal = 1 fila por el total; pago mixto = varias filas que suman el total)
CREATE TABLE IF NOT EXISTS venta_pagos (
    id_pago  INT           NOT NULL AUTO_INCREMENT,
    id_venta INT           NOT NULL,
    metodo   VARCHAR(15)   NOT NULL,
    monto    DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_pago),
    CONSTRAINT uq_ventapago_venta_metodo UNIQUE (id_venta, metodo),
    CONSTRAINT fk_ventapago_venta FOREIGN KEY (id_venta)
        REFERENCES ventas (id_venta) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE venta_pagos
    ADD CONSTRAINT chk_ventapago_metodo
    CHECK (metodo IN ('efectivo','tarjeta','transferencia','divisa','credito'));

ALTER TABLE venta_pagos
    ADD CONSTRAINT chk_ventapago_monto CHECK (monto > 0);

-- 5) Solo si ya existen ventas de prueba: se registran como pagadas en efectivo
INSERT INTO venta_pagos (id_venta, metodo, monto)
SELECT v.id_venta, 'efectivo', v.total
  FROM ventas v
 WHERE NOT EXISTS (SELECT 1 FROM venta_pagos p WHERE p.id_venta = v.id_venta);

-- 6) Verificacion (debe devolver 2 filas y luego las columnas nuevas)
SHOW TABLES LIKE 'venta_pagos';
SHOW COLUMNS FROM ventas WHERE Field IN ('numero_factura','tipo_pago');
