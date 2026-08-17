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
    public function crear(){
        //Este metodo solo sirve para mostrar la vista de crear usuario.
        require_once '../app/views/usuario/crear.php';
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
    header("Location: index.php?controller=sistema&action=dashboard");
        exit;
    }

    
}

?>