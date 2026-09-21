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
            
            $tipo_documento = $_POST['tipo_documento'] ?? '';
            $num_documento = $_POST['num_documento'] ?? '';
            $nombre_razon_social = $_POST['nombre_razon_social'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $email = $_POST['email'] ?? '';
            $direccion = $_POST['direccion'] ?? '';

            // Enviamos los datos al modelo para hacer el INSERT
            $exito = $this->clienteModel->createcliente($tipo_documento, $num_documento, $nombre_razon_social, $telefono, $email, $direccion);

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
        $tipo_documento= trim($_POST['tipo_documento'] ?? '');
        $num_documento= trim ($_POST['num_documento'] ?? '');
        $nombre_razon_social= trim($_POST['nombre_razon_social'] ?? '');
        $telefono= trim ($_POST['telefono'] ?? '');
        $email= trim ($_POST['email'] ?? '');
        $direccion= trim ($_POST['direccion'] ?? '');
        $estado= trim ($_POST['estado'] ?? '');
        $creado_en= trim ($_POST['creado_en'] ?? '');
         
        //verificación de campos
        if(!empty($id) && !empty($tipo_documento) && !empty($nombre_razon_social) && !empty($num_documento) && !empty($telefono)
             && !empty($email) && !empty($direccion) && !empty($estado) && !empty($creado_en)){
            $this->clienteModel->updatecliente($id, $tipo_documento, $nombre_razon_social, 
            $num_documento, $telefono, $email, $direccion, $estado, $creado_en);
        }

        //Redireccionamiento
        header("Location: index.php?controller=cliente&action=tablaClientes");
        exit;
}

}


?>