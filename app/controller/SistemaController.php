<?php
//cargar archivos.

require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../model/sistemaModel.php';


class SistemaController{
    private $model;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        //inyectar la coneccion en el modelo.
        $this->model = new UsuarioModel($pdo);
    }


    public function index(){
        //cargar la vista del login
        require_once __DIR__.'/../../app/views/login.html';
    }

    public function procesarLogin(){
        if(isset($_POST['login_form'])){
            $usuario = $_POST['username'];
            $password = $_POST['password'];

            // PASO 1: Obtener los datos del usuario desde la BD
            $datosUsuario = $this->model->obtenerUsername($usuario);


            // PASO 2: Validar si existe el usuario Y si la contraseña coincide con el hash
            if ($datosUsuario && password_verify($password, $datosUsuario['password_hash'])) {

            //paso 3: Iniciar sesión y redirigir al usuario a la página de inicio
    
            $_SESSION['user_id'] = $datosUsuario['id'];
            $_SESSION['username'] = $datosUsuario['username'];
            $_SESSION['is_admin'] = $datosUsuario['rol'];

            header("Location: index.php?controller=sistema&action=dashboard");  //index.php?controller=tienda&action=index
            exit();


            }else{
                //si el usuario no existe o la contraseña es incorrecta, mostrar un mensaje de error
                echo "<p style='color:red;'>Datos Invalidos. Por favor, verifica tu usuario y contraseña.</p>";
                require_once __DIR__ . '/../views/login.html'; // o login.html


            }
        }
    }

    public function dashboard() {
    // Verificamos la sesión antes de cargar la vista del Dashboard
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
    //Cabeceras HTTP anti-caché (se envían antes de incluir la vista)
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: 0"); // Proxies

    //Cargar la vista
    require_once __DIR__ . '/../views/dashboard.php';
}

public function logout(){
    //Limpiamos todas las variables de la sesion
    $_SESSIN = array();

 //destruimos completamente la sesion
    session_destroy();

    //redireccionamos al login
    header("Location: index.php");
    exit();
}
}   