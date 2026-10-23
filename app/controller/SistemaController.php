<?php
//cargar archivos.

require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../helpers/seguridad.php';
require_once __DIR__.'/../model/sistemaModel.php';


class SistemaController{
    private $model;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        //inyectar la coneccion en el modelo.
        $this->model = new SistemaModel($pdo);
    }


    public function index(){
        //cargar la vista del login
        require_once __DIR__.'/../../app/views/login/login.php';
    }

    public function procesarLogin(){
        if(isset($_POST['login_form'])){
            $usuario = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;

            // PASO 0: Las entradas deben ser TEXTO y de tamaño razonable. Si alguien envía un arreglo
            // (password[]=x), password_verify() lanzaba un error fatal. Se responde igual que con
            // datos incorrectos, sin dar pistas. (El límite del usuario es el de la columna: 100.)
            $entradasValidas = is_string($usuario) && is_string($password)
                && $usuario !== '' && $password !== ''
                && mb_strlen($usuario, 'UTF-8') <= 100 && strlen($password) <= 1024;

            // PASO 1: Obtener los datos del usuario desde la BD
            $datosUsuario = $entradasValidas ? $this->model->obtenerUsername($usuario) : false;


            // PASO 2: Validar si existe el usuario Y si la contraseña coincide con el hash
            if ($entradasValidas && $datosUsuario && password_verify($password, $datosUsuario['password_hash'])) {

            //paso 3: Iniciar sesión y redirigir al usuario a la página de inicio

            // Se renueva el ID de sesión al autenticarse: evita que alguien que conocía el ID
            // anterior (fijación de sesión) herede la sesión ya autenticada.
            session_regenerate_id(true);

            $_SESSION['user_id'] = $datosUsuario['id'];
            $_SESSION['username'] = $datosUsuario['username'];
            $_SESSION['is_admin'] = $datosUsuario['rol'];

            header("Location: index.php?controller=sistema&action=dashboard");  //index.php?controller=tienda&action=index
            exit();


            }else{
                //si el usuario no existe o la contraseña es incorrecta, mostrar un mensaje de error
                $errorLogin = "Datos inválidos. Por favor, verifica tu usuario y contraseña.";
                require_once __DIR__ . '/../views/login/login.php'; 
                exit();

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
        $_SESSION = array();

    //destruimos completamente la sesion
        session_destroy();

        //redireccionamos al login
        header("Location: index.php");
        exit();
    }
} 