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

public function formeditarcliente($id){
      // Verificar que el ID no esté vacío
    if (!empty($id)) {
        // Obtener el cliente desde el modelo (SINGULAR)
        $clientes = $this->clienteModel->getById($id);
        
        if ($clientes) {
            // Cargar la vista (verifica el nombre exacto de la carpeta)
            require_once __DIR__ . '/../views/clientes/editarcliente.php';
            return;
        }
    }
    
    // Si no se encuentra el cliente, redirigir a la lista
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recibimos los datos del formulario
        $id = $_POST['id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';

        // Enviamos los datos al modelo para hacer el UPDATE
        $exito = $this->clienteModel->updatecliente($id, $nombre, $cedula, $telefono, $direccion);

        if ($exito) {
            // Si se actualizó, redirigimos al dashboard de clientes
            header("Location: index.php?controller=cliente&action=tablaClientes");
            exit();
        } else {
            echo "Hubo un error al actualizar el cliente.";
        }
    }
}
}

?>