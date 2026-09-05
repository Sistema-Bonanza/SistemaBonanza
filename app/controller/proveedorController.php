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
}
?>