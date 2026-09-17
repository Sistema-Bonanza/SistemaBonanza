<?php
require_once __DIR__ . '/../model/clienteModel.php';
require_once __DIR__ . '/../../config/database.php';

Class clienteController{
private $clienteModel;
public function __construct(){
    $database = new Database();
    $pdo = $database->getConnection();
    $this->clienteModel = new clienteModel($pdo);
}

public function tablaClientes(){
    $clientes = $this->clienteModel->getAll();
    require_once __DIR__ . '/../views/clientes/clientes.php';
}
public function formcrearcliente(){
    require_once __DIR__ . '/../views/clientes/crearcliente.php';

}

public function formeditarcliente(){
     $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            $clientes = $this->clienteModel->getById($id);
            if ($clientes) {
                require_once __DIR__ . '/../../app/views/clientes/editarcliente.php';
                return;
            }
        }
        // Si no se encuentra la categoría, redirigir a la lista de categorías
        header("Location: index.php?controller=cliente&action=tablaClientes");
        exit;
}


public function guardar(){
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recibimos los datos del formulario
            $nombre = $_POST['nombre'] ?? '';
            $cedula = $_POST['cedula'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $direccion = $_POST['direccion'] ?? '';

            // Enviamos los datos al modelo para hacer el INSERT
            $exito = $this->clienteModel->createcliente($nombre, $cedula, $telefono, $direccion);

            if ($exito) {
                // Si se guardó, redirigimos al dashboard de clientes
                header("Location: index.php?controller=cliente&action=tablaClientes");
                exit();
            } else {
                echo "Hubo un error al registrar el cliente.";
            }
        }
}

public function actualizar(){
//Recibimos los datos del formulario
        $id = (int) ($_POST['id'] ?? 0);
        $nombre= trim($_POST['nombre'] ?? '');
        $cedula= trim ($_POST['cedula'] ?? '');
        $telefono= trim ($_POST['telefono'] ?? '');
        $direccion= trim ($_POST['direccion'] ?? '');
         
        //verificación de campos
        if(!empty($id) && !empty($nombre) && !empty($cedula) && !empty($telefono) && !empty($direccion)){
            $this->clienteModel->updatecliente($id, $nombre, $cedula, $telefono, $direccion);
        }

        //Redireccionamiento
        header("Location: index.php?controller=cliente&action=tablaClientes");
        exit;
}

}

?>