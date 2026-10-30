<?php

class CategoriaModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $sql = "SELECT c.id_categoria, c.nombre, c.descripcion, c.estado,
                       COUNT(p.id_producto) AS total_articulos
                FROM categorias c
                LEFT JOIN productos p ON c.id_categoria = p.id_categoria AND p.estado = 1
                GROUP BY c.id_categoria, c.nombre, c.descripcion, c.estado
                ORDER BY c.nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener métricas reales para los KPIs de la vista
    public function getKpis() {
        $kpis = [
            'total_productos' => 0,
            'huerfanos' => 0,
            'mayor_rotacion' => 'Sin datos'
        ];

        try {
            // Total de productos en el sistema
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos");
            $kpis['total_productos'] = (int) $stmt->fetchColumn();

            // Integridad BD: Productos huérfanos (categoría eliminada a la fuerza o en NULL)
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos WHERE id_categoria IS NULL OR id_categoria NOT IN (SELECT id_categoria FROM categorias)");
            $kpis['huerfanos'] = (int) $stmt->fetchColumn();

            // Mayor rotación (Aproximación por volumen de productos. Si tienes tabla kardex o ventas, puedes ajustarlo después)
            $sqlRotacion = "SELECT c.nombre
                            FROM categorias c
                            JOIN productos p ON c.id_categoria = p.id_categoria
                            GROUP BY c.id_categoria, c.nombre
                            ORDER BY COUNT(p.id_producto) DESC
                            LIMIT 1";
            $stmt = $this->pdo->query($sqlRotacion);
            $rotacion = $stmt->fetchColumn();
            
            if ($rotacion) {
                $kpis['mayor_rotacion'] = $rotacion;
            }
        } catch (PDOException $e) {
            // Captura silenciosa para no romper la vista si la tabla productos no existe aún
        }

        return $kpis;
    }

    public function getById($id) {
        $sql = "SELECT id_categoria as id, nombre, descripcion FROM categorias WHERE id_categoria = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCategoria($nombre, $descripcion) {
        try {
            $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
            ]);
        } catch(PDOException $e) {
            error_log("Error en la BD (createCategoria): " . $e->getMessage());
            return false;
        }
    }

    public function updateCategoria($id, $nombre, $descripcion) {
        $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id_categoria";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_categoria' => $id,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
        ]);
    }

    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM categorias WHERE nombre = :nombre";
        $params = [':nombre' => $nombre];
        if ($excluirId !== null) {
            $sql .= " AND id_categoria <> :id";
            $params[':id'] = (int) $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Realiza un borrado lógico (Soft Delete) cambiando el estado a 0
    public function inactivarCategoria($id) {
        $sql = "UPDATE categorias SET estado = 0 WHERE id_categoria = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Obtener los productos vinculados a una categoría específica
    public function getProductosByCategoria($id_categoria) {
        try {
            // Utilizamos las columnas reales: codigo y stock_actual
            $sql = "SELECT id_producto, nombre, codigo, stock_actual 
                    FROM productos 
                    WHERE id_categoria = :id_categoria AND estado = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_categoria' => $id_categoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error SQL en Productos: " . $e->getMessage()); 
        }
    }
}
?>