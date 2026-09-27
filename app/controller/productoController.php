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


    // Metodos relacionados a productos   
    public function guardarProducto(){

        // 1. DATOS INDISPENSABLES DEL FORMULARIO
        $codigo       = trim($_POST['codigo'] ?? '');
        $nombre       = trim($_POST['nombre'] ?? '');
        // Asegúrate de que en el HTML el <select> tenga name="id_categoria"
        $id_categoria = trim($_POST['id_categoria'] ?? '');


        // 2. DATOS OPCIONALES O CON VALORES POR DEFECTO
        $precio_compra = 0;
        $precio_venta  = 0; // Se gestionará posteriormente en el módulo de precios
        $stock_minimo  = 0;

        $aplica_iva   = isset($_POST['aplica_iva']) ? 1 : 0; // Checkbox booleano
        $stock_actual = 0;


        // 3. VALIDACIÓN DE CAMPOS INDISPENSABLES
        if($codigo !== '' && $nombre !== '' && $id_categoria !== ''){

            // Ejecutar el modelo y guardar el resultado en una variable ($exito)
            $exito = $this->productoModel->crearProducto(
                $codigo, 
                $nombre, 
                $id_categoria,
                $aplica_iva,
                $stock_minimo,
                $stock_actual,
                $precio_compra, 
                $precio_venta
            );

            if($exito){
                // REDIRECCIÓN SOLO SI SE GUARDÓ CORRECTAMENTE EN BD
                header("Location: index.php?controller=producto&action=TablaProductos");
                exit;
            } else {
                // SI FALLÓ LA BASE DE DATOS, DETENEMOS LA REDIRECCIÓN PARA VER EL ERROR EN PANTALLA
                echo "<br><b>No se pudo guardar el registro en la base de datos. Revisa el mensaje de PDO arriba.</b>";
            }

        } else {
            // SI FALTA UN DATO OBLIGATORIO EN EL FORMULARIO
            header("Location: index.php?controller=producto&action=formCrear&error=faltan_datos");
            exit;
        }
    }
    public function formEditar(){
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            // OBTENER LOS DATOS  ACTUALES DEL PRODUCTO
            $producto = $this->productoModel->obtenerProductoPorId($id);

            // VALIDAMOS QUE EL PRODUCTO REALMENTE EXISTA EN LA BASE DE DATOS
            if($producto){
            
            // Obtener todas las categorías para llenar el menú desplegable
            $categorias = $this->categoriaModel->getAll();

            //CARGAR LA VISTA CON LOS DATOS LISTOS
            require_once __DIR__.'/../../app/views/producto/editarProducto.php';
    
            } else {
                // ERROR: El ID existe en la URL pero no en la BD (ej. un producto eliminado)
                header("Location: index.php?controller=producto&action=TablaProductos&error=producto_no_encontrado");
                exit;
            }
        } else {
            // ERROR: Entraron a la URL sin pasar ningún ID
            header("Location: index.php?controller=producto&action=TablaProductos");
            exit;
            require_once __DIR__.'/../../app/views/producto/editarProducto.php';
        }
    }

    public function actualizarProducto(){
        // 1. Capturar los datos enviados por el formulario HTML
        $id_producto  = trim($_POST['id_producto'] ?? '');
        $codigo       = trim($_POST['codigo'] ?? '');
        $nombre       = trim($_POST['nombre'] ?? '');
        $id_categoria = trim($_POST['id_categoria'] ?? '');
        
        // Si el checkbox está marcado llega el valor, sino se asigna 0
        $aplica_iva   = isset($_POST['aplica_iva']) ? 1 : 0; 

        // 2. Validar que los campos indispensables no estén vacíos
        if($id_producto !== '' && $codigo !== '' && $nombre !== '' && $id_categoria !== ''){

            // 3. Ejecutar la actualización en el modelo
            $exito = $this->productoModel->actualizarProducto(
                $id_producto, 
                $codigo, 
                $nombre, 
                $id_categoria, 
                $aplica_iva
            );

            if($exito){
                // Redirigir a la tabla si todo salió bien
                header("Location: index.php?controller=producto&action=TablaProductos");
                exit;
            } else {
                echo "<br><b>Error: Ocurrió un problema al actualizar el registro en la base de datos.</b>";
            }

        } else {
            // Si falta algún dato, lo devolvemos al formulario de edición del mismo producto
            header("Location: index.php?controller=producto&action=formEditar&id=".$id_producto."&error=faltan_datos");
            exit;
        }
    }

    public function eliminarProducto(){
        // Capturamos el ID ya sea que venga por URL (GET) o por Formulario oculto (POST)
        $id = $_GET['id'] ?? $_POST['id'] ?? '';
        if(!empty($id)){
            // Ejecutamos el borrado lógico en el modelo
            $exito =$this->productoModel->eliminarProducto($id);
            if($exito){
                // Redirigimos a la tabla principal
                header("Location: index.php?controller=producto&action=TablaProductos");
                exit;
            } else {
                echo "<br><b>Error: Ocurrió un problema al intentar desactivar el producto.</b>";
            }
        } else {
            // Si intentan entrar sin enviar un ID
            header("Location: index.php?controller=producto&action=TablaProductos");
            exit;
        }
    }
}
        

?>