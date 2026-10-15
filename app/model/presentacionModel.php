<?php

class PresentacionModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ========================================================================
    // 1. CREAR: Registra una nueva presentación (excepción de empaque)
    // ========================================================================
    public function crear($id_producto, $etiqueta, $unidades, $id_usuario) {
        try {
            $sql = "INSERT INTO presentaciones (id_producto, etiqueta, unidades, id_usuario, estado) 
                    VALUES (?, ?, ?, ?, 1)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_producto, $etiqueta, $unidades, $id_usuario]);
            
            return ['ok' => true, 'id' => $this->pdo->lastInsertId()];

        } catch (PDOException $e) {
            // Manejo amable del error 1062 (Registro duplicado por la restricción UNIQUE)
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                return ['ok' => false, 'motivo' => 'duplicado'];
            }
            error_log("Error en PresentacionModel->crear: " . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error'];
        }
    }

    // ========================================================================
    // 2. LISTAR POR PRODUCTO: Para que el Admin vea el historial de un producto
    // ========================================================================
    public function listarPorProducto($id_producto) {
        $sql = "SELECT p.*, u.username as nombre_usuario 
                FROM presentaciones p
                INNER JOIN usuarios u ON p.id_usuario = u.id
                WHERE p.id_producto = ?
                ORDER BY p.estado DESC, p.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // 3. LISTAR ACTIVAS: Se usa para armar el <select> en el formulario de compras
    // ========================================================================
    public function listarActivas() {
        // Traemos también el nombre del producto para agrupar visualmente en la vista
        $sql = "SELECT p.id_presentacion, p.id_producto, p.etiqueta, p.unidades, prod.nombre as nombre_producto 
                FROM presentaciones p
                INNER JOIN productos prod ON p.id_producto = prod.id_producto
                WHERE p.estado = 1
                ORDER BY prod.nombre ASC, p.etiqueta ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // 4. OBTENER ACTIVA DE PRODUCTO: El backend usa esto al procesar la compra 
    // para asegurarse de que nadie trucó el HTML y cruzó productos.
    // ========================================================================
    public function obtenerActivaDeProducto($id_presentacion, $id_producto) {
        $sql = "SELECT id_presentacion, etiqueta, unidades 
                FROM presentaciones 
                WHERE id_presentacion = ? AND id_producto = ? AND estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_presentacion, $id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // 5. DESACTIVAR: Borrado lógico (Nunca hacemos DELETE)
    // ========================================================================
    public function desactivar($id_presentacion) {
        $sql = "UPDATE presentaciones SET estado = 0 WHERE id_presentacion = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_presentacion]);
    }

    // ========================================================================
    // 6. EXISTE ETIQUETA: Validación extra antes de guardar
    // ========================================================================
    public function existeEtiqueta($id_producto, $etiqueta) {
        $sql = "SELECT COUNT(*) FROM presentaciones 
                WHERE id_producto = ? AND LOWER(etiqueta) = LOWER(?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_producto, $etiqueta]);
        return $stmt->fetchColumn() > 0;
    }
}
?>