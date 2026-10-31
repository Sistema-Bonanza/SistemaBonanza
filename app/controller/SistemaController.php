<?php
// Cargar archivos.
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../helpers/seguridad.php';
require_once __DIR__.'/../model/sistemaModel.php';
require_once __DIR__.'/../model/productoModel.php';
require_once __DIR__.'/../model/proveedorModel.php';

class SistemaController{
    private $model;
    private $productoModel;
    private $proveedorModel;

    public function __construct(){
        // Crear conexión con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Inyectar la conexión en los modelos.
        $this->model = new SistemaModel($pdo);
        $this->productoModel = new ProductoModel($pdo);
        $this->proveedorModel = new proveedorModel($pdo);
    }

    public function index(){
        // Cargar la vista del login
        require_once __DIR__.'/../../app/views/login/login.php';
    }

    public function procesarLogin(){
        if(isset($_POST['login_form'])){
            $usuario = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;

            $entradasValidas = is_string($usuario) && is_string($password)
                && $usuario !== '' && $password !== ''
                && mb_strlen($usuario, 'UTF-8') <= 100 && strlen($password) <= 1024;

            $datosUsuario = $entradasValidas ? $this->model->obtenerUsername($usuario) : false;

            if ($entradasValidas && $datosUsuario && password_verify($password, $datosUsuario['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $datosUsuario['id'];
                $_SESSION['username'] = $datosUsuario['username'];
                $_SESSION['is_admin'] = $datosUsuario['rol'];

                header("Location: index.php?controller=sistema&action=dashboard");
                exit();
            } else {
                $errorLogin = "Datos inválidos. Por favor, verifica tu usuario y contraseña.";
                require_once __DIR__ . '/../views/login/login.php'; 
                exit();
            }
        }
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit();
        }

        // 1. Obtener existencias e ítems del catálogo rápido desde ProductoModel
        $inventario  = $this->productoModel->obtenerProductos();
        $catalogo    = $this->productoModel->obtenerProductosActivos(6); // <-- Usa obtenerProductosActivos() en productoModel

        // 2. Obtener proveedores activos desde ProveedorModel
        $proveedores = $this->proveedorModel->getActivos(); // <-- Asegúrate de que use $this->proveedorModel y NO $this->productoModel

        $metricas = $this->productoModel->obtenerMetricasStock();

        $stats['stock_total'] = $metricas['stock_total'];
        $stats['valor_inventario'] = $metricas['valor_inventario'];

        // 3. Cálculo de métricas
        $totalProductos  = count($inventario);
        $disponibles     = 0;
        $stockTotal      = 0;
        $valorInventario = 0;

        foreach ($inventario as $prod) {
            $stockActual = (int) ($prod['stock_actual'] ?? 0);
            $precioVenta = (float) ($prod['precio_venta'] ?? 0);

            if ($stockActual > 0) {
                $disponibles++;
            }
            $stockTotal      += $stockActual;
            $valorInventario += ($stockActual * $precioVenta);
        }

        $estadisticas = [
            'total_productos'     => $totalProductos,
            'disponibles'         => $disponibles,
            'stock_total'         => $stockTotal,
            'valor_inventario'    => $valorInventario,
            'proveedores_activos' => count($proveedores)
        ];

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        require_once __DIR__ . '/../views/dashboard.php';
    }

    public function logout(){
        $_SESSION = array();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}