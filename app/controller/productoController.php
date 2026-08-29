<?php
require_once '/../../config/database.php';
require_once '/../model/categoriaModel.php';
require_once '/../model/proveedorModel.php';


Class productoController{

  private $model;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        //inyectar la coneccion en el modelo.
        $this->model = new SistemaModel($pdo);
    }
//Metodos para mostrar la vistas de productos, crear producto, crear categoria y crear proveedor

public function TablaProductos(){
    $productos = $this->model->obtenerProductos();
    require_once __DIR__.'/../../app/views/productos.php';
}
public function formproduc(){
    //Este metodo solo sirve para mostrar la vista de crear producto.
    require_once __DIR__.'/../../app/views/crearProducto.php';
}
public function formcategoria(){
    //Este metodo solo sirve para mostrar la vista de crear categoria.
    require_once __DIR__.'/../../app/views/crearCategoria.php';
}
public function formproveedor(){
    //Este metodo solo sirve para mostrar la vista de crear proveedor.
    require_once __DIR__.'/../../app/views/crearProveedor.php';
}

//metodos relacionados a productos
public function guardarProducto(){

            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $id_categoria = trim($_POST['id_categoria'] ?? '');
            $id_proveedor = trim($_POST['id_proveedor'] ?? '');
            $unidad_media = trim($_POST['unidad_media'] ?? '');
            $unidades_por_empaque = trim($_POST['unidades_por_empaque'] ?? '');
            $precio_compra = trim($_POST['precio_compra'] ?? '');
            $precio_venta = trim($_POST['precio_venta'] ?? '');
            $aplica_iva = trim($_POST['aplica_iva'] ?? '');
            $stock_actual = trim($_POST['stock_actual'] ?? '');
            $stock_minimo = trim($_POST['stock_minimo'] ?? '');
            $estado = trim($_POST['estado'] ?? '');
            $create_at = trim($_POST['create_at'] ?? '');

    if(!empty($codigo) && !empty($nombre) && !empty($id_categoria) && !empty($id_proveedor) && !empty($unidad_media) && !empty($unidades_por_empaque) && !empty($precio_compra) && !empty($precio_venta) && !empty($aplica_iva) && !empty($stock_actual) && !empty($stock_minimo) && !empty($estado) && !empty($create_at)){
        $this->model->crearProducto($codigo, $nombre, $id_categoria, $id_proveedor, $unidad_media, $unidades_por_empaque, $precio_compra, $precio_venta, $aplica_iva, $stock_actual, $stock_minimo, $estado, $create_at);
    }
    header("Location: index.php?controller=producto&action=TablaProductos");
    exit;

}
public function actualizarProducto(){
    $id = $_POST['id'] ?? '';
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $id_categoria = trim($_POST['id_categoria'] ?? '');
    $id_proveedor = trim($_POST['id_proveedor'] ?? '');
    $unidad_media = trim($_POST['unidad_media'] ?? '');
    $unidades_por_empaque = trim($_POST['unidades_por_empaque'] ?? '');
    $precio_compra = trim($_POST['precio_compra'] ?? '');
    $precio_venta = trim($_POST['precio_venta'] ?? '');
    $aplica_iva = trim($_POST['aplica_iva'] ?? '');
    $stock_actual = trim($_POST['stock_actual'] ?? '');
    $stock_minimo = trim($_POST['stock_minimo'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $create_at = trim($_POST['create_at'] ?? '');

    if(!empty($id) && !empty($codigo) && !empty($nombre) && !empty($id_categoria) && !empty($id_proveedor) && !empty($unidad_media) && !empty($unidades_por_empaque) && !empty($precio_compra) && !empty($precio_venta) && !empty($aplica_iva) && !empty($stock_actual) && !empty($stock_minimo) && !empty($estado) && !empty($create_at)){
        $this->model->actualizarProducto($id, $codigo, $nombre, $id_categoria, $id_proveedor, $unidad_media, $unidades_por_empaque, $precio_compra, $precio_venta, $aplica_iva, $stock_actual, $stock_minimo, $estado, $create_at);
    }
    header("Location: index.php?controller=producto&action=TablaProductos");
    exit;

}

public function eliminarProducto(){
    $id = $_POST['id'] ?? '';
    if(!empty($id)){
        $this->model->eliminarProducto($id);
    }
    header("Location: index.php?controller=producto&action=TablaProductos");
    exit;

}
//metotos relacionados a categorias y proveedores

public function guardarCategoria(){
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if(!empty($nombre)){
        $this->model->crearCategoria($nombre, $descripcion);
    }
    header("Location: index.php?controller=producto&action=formcategoria");
    exit;
}

public function guardarProveedor(){
    $rif = trim($_POST['rif'] ?? '');
    $razon_social = trim($_POST['razon_social'] ?? '');
    $nombre_contacto = trim($_POST['nombre_contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    if(!empty($rif)){
        $this->model->crearProveedor($rif, $razon_social, $nombre_contacto, $telefono, $email, $direccion);
    }
    header("Location: index.php?controller=producto&action=formproveedor");
    exit;



}

}
   

?>