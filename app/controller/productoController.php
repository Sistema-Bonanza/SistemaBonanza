<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/proveedorModel.php';
require_once __DIR__ . '/../model/productoModel.php';
require_once __DIR__ . '/../model/categoriaModel.php';


Class productoController{

    private $proveedorModel;
    private $productoModel;
    private $categoriaModel;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
    
        //Se instancia los 3 modelos porque se necesitan para guardar un producto.
       $this->proveedorModel = new ProveedorModel($pdo);
        $this->categoriaModel = new CategoriaModel($pdo);
        $this->productoModel = new ProductoModel($pdo);

    }


    //Metodos para mostrar la vistas de productos, crear producto, crear categoria y crear proveedor

    public function TablaProductos(){
        $productos = $this->productoModel->obtenerProductos();
        require_once __DIR__.'/../views/producto/producto.php';
    }
    public function formproduct(){
        //Este metodo solo sirve para mostrar la vista de crear producto.
        require_once __DIR__.'/../../app/views/producto/producto.php';
    }
    public function formcategoria(){
        //Este metodo solo sirve para mostrar la vista de crear categoria.
        require_once __DIR__.'/../../app/views/crearCategoria.php';
    }
    public function formproveedor(){
        //Este metodo solo sirve para mostrar la vista de crear proveedor.
        require_once __DIR__.'/../../app/views/crearProveedor.php';
    }

    public function formCrear(){

        // 1. Se ejecutan los métodos que extraen los datos y los guardas en variables
        $categorias = $this->categoriaModel->getAll();
        $proveedores = $this->proveedorModel->getActivos();

        // Como las variables $categorias y $proveedores ya están definidas aquí, 
        // el archivo HTML podrá leerlas sin problema gracias al 'require_once'.
        //ESTE METODO SOLO SIRVE PARA MOSTRAR LA VISTA DE CREAR PRODUCTO.
        require_once __DIR__.'/../../app/views/producto/crearProducto.php';
    }


    //metodos relacionados a productos  
    public function guardarProducto(){

        // DATOS INDISPENSABLES DEL FORMULARIO DE PRODUCTOS 
                $codigo = trim($_POST['codigo'] ?? '');
                $nombre = trim($_POST['nombre'] ?? '');
                $id_categoria = trim($_POST['id_categoria'] ?? '');
                $id_proveedor = trim($_POST['id_proveedor'] ?? '');


                //DATOS RESCATABLES (Si vienen vacíos, les asignamos un valor por defecto seguro)
            // Usamos un condicional corto: (Condición) ? (Si es verdad) : (Si es falso)

               $precio_compra = (isset($_POST['precio_compra']) && $_POST['precio_compra'] !== '') ? trim($_POST['precio_compra']) : 0;
               $precio_venta = 0;
                $stock_minimo = (isset($_POST['stock_minimo']) && $_POST['stock_minimo'] !== '') ? trim($_POST['stock_minimo']) : 0;
                $unidades_por_empaque = 1;

                $unidad_media = trim($_POST['unidad_medida'] ?? 'UNIDAD'); //SI VIENE VACIO, ASUMIMOS UNIDAD.
               $aplica_iva = isset($_POST['aplica_iva']) ? 1 : 0; // SI EL CHECKBOX SE MARCÓ ES 1. SI NO ES 0.
               $stock_actual = (isset($_POST['stock_actual']) && $_POST['stock_actual'] !== '') ? trim($_POST['stock_actual']) : 0;
            
                //$estado = trim($_POST['estado'] ?? '');
                //$create_at = trim($_POST['create_at'] ?? '');

                // los checkboxes no envian nada si no estan marcado, asi que validamos asi:

        if($codigo !== '' && $nombre !== '' && $id_categoria !== '' && $id_proveedor !== ''){

            //SI LAS CONDICIONES SE CUMPLEN, LOS ENVIAREMOS AL MODELO.
            $this->productoModel->crearProducto($codigo, $nombre, $id_categoria, $id_proveedor, $unidad_media, $unidades_por_empaque, $precio_compra, $precio_venta, $aplica_iva, $stock_actual, $stock_minimo);
            // REDIRECCION CON EXITO
            header("Location: index.php?controller=producto&action=tablaProductos");
            exit;
        } else {
            //OPCIONAL: SI FALTA UN DATO INDISPENSABLE, SE DEVUELVE AL FORMULARIO
            //AQUI SE AGREGA UN MENSAJE DE ERROR POR LA URL EJ (&ERROR=FALTAN_DATOS).

            header("location: index.php?controller=producto&action=formproduc&error=faltan_datos");
        }
    }
}

/* 
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
*/

?>