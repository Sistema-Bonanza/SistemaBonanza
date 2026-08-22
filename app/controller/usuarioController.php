<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../model/usuarioModel.php';

    class usuarioController{
        private $model;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        //inyectar la coneccion en el modelo.
        $this->model = new usuarioModel($pdo);
    }
    
    public function formusuario(){
        //Este metodo solo sirve para mostrar la vista de usuario.
         $usuarios = $this->model->getAll();
        require_once '../app/views/usuario/usuario.php';
    }

  
    public function formcrear(){
        //Este metodo solo sirve para mostrar la vista de crear usuario.
        require_once '../app/views/usuario/crear.php';
    }

   public function formeditar(){
    $id = (int)($_GET['id'] ?? 0);
    if($id <= 0){
        header("Location: index.php?controller=usuario&action=formusuario");
        exit;
    }
    
    $usuario = $this->model->getById($id);
    if(!$usuario){
        $_SESSION['error'] = "Usuario no encontrado";
        header("Location: index.php?controller=usuario&action=formusuario");
        exit;
    }
    
    require_once '../app/views/usuario/editar.php';
}

    public function guardar(){
        //Recolección de los datos de reguistro del usuario
        $username= trim($_POST['username'] ?? '');
        $password= trim ($_POST['password'] ?? '');
        $rol= trim ($_POST['rol'] ?? '');
         
        //Encriptamiento de la clave de acceso
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        //verificación de campos
    if(!empty($username) && !empty($password_hash) && !empty($rol)){
        $this->model->crearUsuario($username, $password_hash, $rol);
    }
    //Redireccionamiento
    header("Location: index.php?controller=usuario&action=formusuario");
        exit;
    }

    public function editar(){
        //Recolección de los datos de reguistro del usuario
        $id = (int) ($_POST['id'] ?? 0);
        $username= trim($_POST['username'] ?? '');
        $password= trim ($_POST['password'] ?? '');
        $rol= trim ($_POST['rol'] ?? '');
         
        //Encriptamiento de la clave de acceso
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        //verificación de campos
        if(!empty($id) && !empty($username) && !empty($password_hash) && !empty($rol)){
            $this->model->actualizarUsuario($id, $username, $password_hash, $rol);
        }

        //Redireccionamiento
        header("Location: index.php?controller=usuario&action=formusuario");
        exit;
    }

    public function eliminar(){
        //Recolección del ID del usuario a eliminar
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
        $this->model->eliminarUsuario($id);
        }

        //Redireccionamiento
        header("Location: index.php?controller=usuario&action=formusuario");
        exit;
    }

        
}

?>