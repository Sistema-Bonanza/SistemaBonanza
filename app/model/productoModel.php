<?php
Class productoModel{

public function __construct($pdo){
$this->pdo = $pdo;
}

public function obtenerProductos(){
    $stmt = $this->pdo->query("SELECT * FROM productos ORDER BY id DESC");
    return $stmt->fetchAll();
}

public function obtenerProductoPorId($id){
    $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();

}

public function crearProducto($nombre, $descripcion, $precio, $stock){
        $sql = "INSERT INTO `productos`(`id_producto`, `codigo`, `nombre`, `id_categoria`, `id_proveedor`, `unidad_media`, `unidades_por_empaque`, `precio_compra`, `precio_venta`, `aplica_iva`, `stock_actual`, `stock_minimo`, `estado`, `create_at`) 
        VALUES (:id_producto, :codigo, :nombre, :id_categoria, :id_proveedor, :unidad_media, :unidades_por_empaque, :precio_compra, :precio_venta, :aplica_iva, :stock_actual, :stock_minimo, :estado, :create_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_producto' => $id_producto,
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':id_categoria' => $id_categoria,
            ':id_proveedor' => $id_proveedor,
            ':unidad_media' => $unidad_media,
            ':unidades_por_empaque' => $unidades_por_empaque,
            ':precio_compra' => $precio_compra,
            ':precio_venta' => $precio_venta,
            ':aplica_iva' => $aplica_iva,
            ':stock_actual' => $stock_actual,
            ':stock_minimo' => $stock_minimo,
            ':estado' => $estado,
            ':create_at' => $create_at,
        ]);
        return ;

}

public function actualizarProducto($id, $nombre, $descripcion, $precio, $stock){
        $sql = "UPDATE productos SET id_producto = :id_producto, codigo = :codigo, nombre = :nombre, id_categoria = :id_categoria, id_proveedor = :id_proveedor, unidad_media = :unidad_media, unidades_por_empaque = :unidades_por_empaque, precio_compra = :precio_compra, precio_venta = :precio_venta, aplica_iva = :aplica_iva, stock_actual = :stock_actual, stock_minimo = :stock_minimo, estado = :estado, create_at = :create_at WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':id_producto' => $id_producto,
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':id_categoria' => $id_categoria,
            ':id_proveedor' => $id_proveedor,
            ':unidad_media' => $unidad_media,
            ':unidades_por_empaque' => $unidades_por_empaque,
            ':precio_compra' => $precio_compra,
            ':precio_venta' => $precio_venta,
            ':aplica_iva' => $aplica_iva,
            ':stock_actual' => $stock_actual,
            ':stock_minimo' => $stock_minimo,
            ':estado' => $estado,
            ':create_at' => $create_at,
        ]);
        return ;

}

public function eliminarProducto($id){
        $sql = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return ;

}


}
?>