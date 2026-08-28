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
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (:nombre, :descripcion, :precio, :stock)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
        ]);
        return ;

}

public function actualizarProducto($id, $nombre, $descripcion, $precio, $stock){
        $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
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