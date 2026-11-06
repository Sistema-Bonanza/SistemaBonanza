-- =====================================================================
-- CIERRES DE CAJA: guarda cada cierre efectuado (uno por día).
-- MariaDB 10.4 (XAMPP). Ejecútalo en la pestaña SQL de la base sistema_bonanza.
-- Si dice "already exists", ya estaba aplicado.
-- Sin esta tabla, el historial de cierres y el botón "Realizar cierre" avisan
-- que falta la migración (el detalle queda en el log del servidor).
-- =====================================================================
USE sistema_bonanza;

CREATE TABLE IF NOT EXISTS cierres_caja (
    id_cierre        INT(11)       NOT NULL AUTO_INCREMENT,
    fecha_cierre     DATE          NOT NULL COMMENT 'Día cerrado (uno por día)',
    facturas         INT(11)       NOT NULL DEFAULT 0,
    total_vendido    DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Ventas registradas, con IVA',
    total_cobrado    DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Todo menos crédito',
    total_credito    DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Por cobrar',
    efectivo_sistema DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Cobrado en efectivo según el sistema',
    efectivo_contado DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Efectivo contado en gaveta',
    diferencia       DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'contado - sistema',
    observaciones    VARCHAR(500)  NULL,
    id_usuario       INT(11)       NOT NULL COMMENT 'Quién hizo el cierre',
    creado_en        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cierre),
    UNIQUE KEY uq_cierre_fecha (fecha_cierre),
    KEY fk_cierre_usuario (id_usuario),
    CONSTRAINT fk_cierre_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
