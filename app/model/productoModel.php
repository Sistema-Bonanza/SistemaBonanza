<?php
class productoModel {
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    // ==================================================================================
    // OBTENER TODOS LOS PRODUCTOS ACTIVOS
    // ==================================================================================
    public function obtenerProductos(){
        try {
            $sql = "SELECT
                        prod.id_producto,
                        prod.codigo,
                        prod.nombre,
                        c.nombre AS nombre_categoria,
                        prod.stock_actual,
                        prod.precio_compra,
                        prod.precio_venta,
                        prod.aplica_iva
                        FROM productos prod
                        INNER JOIN categorias c ON prod.id_categoria = c.id_categoria
                        WHERE prod.estado = 1 
                        ORDER BY prod.id_producto DESC";
                    
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }

    // ==================================================================================
    // OBTENER PRODUCTOS ACTIVOS CON LÍMITE (PARA CATÁLOGO RÁPIDO)
    // ==================================================================================
    public function obtenerProductosActivos($limit = null) {
        try {
            $sql = "SELECT
                        prod.id_producto,
                        prod.codigo,
                        prod.nombre,
                        c.nombre AS nombre_categoria,
                        prod.stock_actual AS stock,
                        prod.precio_compra,
                        prod.precio_venta,
                        prod.aplica_iva
                        FROM productos prod
                        INNER JOIN categorias c ON prod.id_categoria = c.id_categoria
                        WHERE prod.estado = 1 
                        ORDER BY prod.id_producto DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos activos: " . $e->getMessage());
            return [];
        }
    }

    // ==================================================================================
    // OBTENER PRODUCTO POR ID 
    // ==================================================================================
    public function obtenerProductoPorId($id_producto){
        try {
            $sql = "SELECT * FROM productos WHERE id_producto = :id_producto"; 
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener producto por ID: " . $e->getMessage());
            return false;
        }
    }

    // ==================================================================================
    // CREAR PRODUCTO   
    // ==================================================================================
    public function crearProducto($codigo, $nombre, $id_categoria, $aplica_iva, $stock_minimo = 0, $stock_actual = 0, $precio_compra = 0.00, $precio_venta = 0.00){
        try {
            $sql = "INSERT INTO productos (codigo, nombre, id_categoria, aplica_iva, stock_minimo, stock_actual, precio_compra, precio_venta) 
                    VALUES 
                    (:codigo, :nombre, :id_categoria, :aplica_iva, :stock_minimo, :stock_actual, :precio_compra, :precio_venta)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':codigo'       => $codigo,
                ':nombre'       => $nombre,
                ':id_categoria' => $id_categoria,
                ':aplica_iva'   => $aplica_iva,
                ':stock_minimo' => $stock_minimo,
                ':stock_actual' => $stock_actual,
                ':precio_compra'=> $precio_compra,
                ':precio_venta' => $precio_venta
            ]);

            return true;
        } catch(PDOException $e){
            error_log("Error al crear el producto: " . $e->getMessage());
            return false;
        }
    }

    // ==================================================================================
    // ACTUALIZAR PRODUCTO
    // ==================================================================================
    public function actualizarProducto($id_producto, $codigo, $nombre, $id_categoria, $aplica_iva){
        try {
            $sql = "UPDATE productos SET
                        codigo = :codigo, 
                        nombre = :nombre, 
                        id_categoria = :id_categoria, 
                        aplica_iva = :aplica_iva 
                    WHERE id_producto = :id_producto";
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_producto'   => $id_producto,
                ':codigo'        => $codigo,
                ':nombre'        => $nombre,
                ':id_categoria'  => $id_categoria,
                ':aplica_iva'    => $aplica_iva
            ]);
            
            return true;
        } catch(PDOException $e) {
            error_log("Error al actualizar el producto: " . $e->getMessage());
            return false;
        }
    }

    // ==================================================================================
    // ELIMINAR PRODUCTO (BORRADO LÓGICO)
    // ==================================================================================
    public function eliminarProducto($id_producto){
        try {
            $sql = "UPDATE productos SET estado = 0 WHERE id_producto = :id_producto";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            return true;
        } catch (PDOException $e){
            error_log("Error al eliminar el producto: " . $e->getMessage());
            return false;
        }
    }

    // ==================================================================================
    // VERIFICAR SI EXISTE CÓDIGO
    // ==================================================================================
    public function existeCodigo($codigo, $excluirId = null){
        try {
            $sql = "SELECT COUNT(*) FROM productos WHERE codigo = :codigo";
            $params = [':codigo' => $codigo];
            if ($excluirId !== null) {
                $sql .= " AND id_producto <> :id";
                $params[':id'] = (int) $excluirId;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar código: " . $e->getMessage());
            return false;
        }
    }

    // ==================================================================================
    // PRESENTACIONES (EMPAQUES)
    // ==================================================================================
    public function crearPresentacion($id_producto, $etiqueta, $unidades, $id_usuario) {
        try {
            $sql = "INSERT INTO presentaciones (id_producto, etiqueta, unidades, id_usuario) 
                    VALUES (:id_producto, :etiqueta, :unidades, :id_usuario)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_producto' => $id_producto,
                ':etiqueta'    => strtoupper(trim($etiqueta)), 
                ':unidades'    => $unidades,
                ':id_usuario'  => $id_usuario
            ]);

            return [
                'ok' => true,
                'id_presentacion' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return ['ok' => false, 'motivo' => 'duplicado'];
            }
            error_log("Error al crear presentación: " . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error_interno'];
        }
    }

    public function obtenerTodasLasPresentacionesActivas() {
        try {
            $sql = "SELECT p.id_presentacion, p.etiqueta, p.unidades, 
                    COALESCE(prod.nombre, '★ ESTÁNDAR GLOBAL ★') as nombre_producto 
                    FROM presentaciones p
                    LEFT JOIN productos prod ON p.id_producto = prod.id_producto
                    WHERE p.estado = 1
                    ORDER BY p.id_producto IS NULL DESC, p.etiqueta ASC";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener presentaciones activas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPresentacionValida($id_presentacion, $id_producto) {
        try {
            $sql = "SELECT id_presentacion, etiqueta, unidades 
                    FROM presentaciones 
                    WHERE id_presentacion = ? AND (id_producto = ? OR id_producto IS NULL) AND estado = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_presentacion, $id_producto]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener presentación válida: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerMetricasStock() {
        try {
            $sql = "SELECT 
                        COALESCE(SUM(stock_actual), 0) AS stock_total,
                        COALESCE(SUM(stock_actual * precio_venta), 0.00) AS valor_inventario
                    FROM productos 
                    WHERE estado = 1";
                    
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener métricas de stock: " . $e->getMessage());
            return ['stock_total' => 0, 'valor_inventario' => 0.00];
        }
    }
}