<?php
// Requerimos el modelo para poder instanciarlo
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/proveedorModel.php';

// Asegúrate de requerir tu archivo de conexión a la base de datos
// require_once __DIR__ . '/../../config/database.php'; 

class ProveedorController {
    private $proveedorModel;

    public function __construct() {
         //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
       
        $this->proveedorModel = new proveedorModel($pdo);
     
        // Aquí debes instanciar tu conexión PDO a la base de datos.
        // Ejemplo si tienes una clase Conexion:
        // $conexion = new Conexion();
        // $this->pdo = $conexion->get_conexion();
        
        // TEMPORAL: Ajusta esto según cómo llames a tu conexión en el sistema
    
    }

    // Acción por defecto: Carga la tabla con todos los proveedores
    public function tablaProveedores() {
        // 1. Pedimos al modelo que nos traiga todos los proveedores
        $proveedores = $this->proveedorModel->getAll();

        // 2. Cargamos la vista. La variable $proveedores estará disponible allí.
        require_once __DIR__ . '/../views/proveedor/proveedor.php'; 
    }

    // Acción para mostrar el formulario de creación
    public function formcrearproveedor() {
        // Aquí simplemente cargamos la vista del formulario HTML
        require_once __DIR__ . '/../views/proveedor/crearProveedor.php';
    }

    public function formEdit(){ 
        //VERIFICAMOS QUE LLEGUE EL ID POR URL (ej: index.php?controller=proveedor&action=editar&id=5)
        if(isset($_GET['id'])){
            $id = $_GET['id'];

            //BUSCAMOS LOS DATOS ACTUALES DE ESE PROVEEDOR.
            $proveedor = $this->proveedorModel->obtenerPorId($id);

            //SI EL PROVEEDOR EXISTE, CARGAMOS LA VISTA DE EDITAR.
            if($proveedor){
                require_once __DIR__ . '/../views/proveedor/editarProveedor.php';
            } else {
                //SI ALGUIEN PONE UN ID QUE NO EXISTE, LO DEVOLVEMOS A LA TABLA DE PROVEEDORES.
                header("location: index.php?controller=proveedor&action=tablaProveedores");
            }
        }
    }

    // Acción para recibir los datos del formulario por POST y guardarlos
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recibimos los datos del formulario
            $rif = $_POST['rif'] ?? '';
            $razon_social = $_POST['razon_social'] ?? '';
            $nombre_contacto = $_POST['nombre_contacto'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $email = $_POST['email'] ?? '';
            $direccion = $_POST['direccion'] ?? '';

            // Enviamos los datos al modelo para hacer el INSERT
            $exito = $this->proveedorModel->crearProveedor($rif, $razon_social, $nombre_contacto, $telefono, $email, $direccion);

            if ($exito) {
                // Si se guardó, redirigimos al dashboard de proveedores
                header("Location: index.php?controller=proveedor&action=tablaProveedores");
                exit();
            } else {
                echo "Hubo un error al registrar el proveedor.";
            }
        }
    }


    public function actualizar(){
        //COMPROBAMOS QUE LOS DATOS VENGAN POR EL METODO POST.
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = $_POST['id_proveedor'];

            //SOLO CAPTURAMOS LOS DATOS QUE SE PUEDEN EDITAR (CONTACTO,TELEFONO,EMAIL).
            $nombre_contacto = $_POST['nombre_contacto'];
            $telefono = $_POST['telefono'];
            $email = $_POST['email'];

            // USAMOS EL MODELO DEL CONSTRUCTOR PARA ACTUALIZAR
            $resultado = $this->proveedorModel->actualizarProveedor($id, $nombre_contacto, $telefono, $email);

            if($resultado){
                // SI TODO SALE BIEN, REGRESAMOS A LA TABLA
                header("location: index.php?controller=proveedor&action=tablaProveedores");
                exit();
            } else {
                echo "Hubo un error al actualizar el proveedor.";
            }
        }
    }
}
?>