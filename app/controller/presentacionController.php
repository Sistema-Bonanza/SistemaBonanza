<?php
// app/controllers/PresentacionController.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/presentacionModel.php';
require_once __DIR__ . '/../model/productoModel.php'; // Lo necesitamos para validar si el producto existe
require_once __DIR__ . '/../helpers/seguridad.php';

class PresentacionController {
    private $presentacionModel;
    private $productoModel;
    private $pdo;

    public function __construct() {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->presentacionModel = new PresentacionModel($pdo);
        // Asegúrate de que el nombre de tu clase y archivo coincidan con lo que usas en tu proyecto
        $this->productoModel = new ProductoModel($pdo); 
    }


    public function menuConfig(){
        require_once __DIR__ . '/../views/presentaciones/index.php';
    }

    // ========================================================================
    // 1. LISTADO (Acción por defecto)
    // Muestra todas las presentaciones o las de un producto específico.
    // ========================================================================
    public function index() {
        // Obtenemos el ID del producto si viene por GET para filtrar, si no, mostramos todo.
        // En un sistema real, podrías querer listar todos los productos primero, pero 
        // para B5, un listado general filtrable es lo ideal.
        $id_producto = isset($_GET['id_producto']) ? (int) $_GET['id_producto'] : null;
        
        $presentaciones = [];
        if ($id_producto) {
            $presentaciones = $this->presentacionModel->listarPorProducto($id_producto);
            // Validar si el producto existe para el título
            $productoInfo = $this->productoModel->obtenerProductoPorId($id_producto); 
            if(!$productoInfo){
                 setFlash('error', 'El producto indicado no existe.');
                 redirigir(urlAccion('producto', 'index')); // Redirigir al listado de productos
            }
        } else {
           // Si quieres mostrar TODAS las presentaciones activas en una vista general.
           // Usaremos listarActivas() por ahora, aunque B2 pedía listarPorProducto.
           // Lo ideal para la gestión es entrar desde el producto.
           $presentaciones = $this->presentacionModel->listarActivas(); 
        }

        $productos = $this->productoModel->obtenerProductos(); // Para el select de filtro/creación
        
        // Aquí llamarás a la vista que crearemos en B5
        require_once __DIR__ . '/../views/presentaciones/index.php';
    }

    // ========================================================================
    // 2. FORMULARIO DE CREACIÓN
    // ========================================================================
    public function crear() {
        $productos = $this->productoModel->obtenerProductos(); 
        // Vista del formulario (B5)
        require_once __DIR__ . '/../views/presentaciones/crearEmpaque.php';
    }

    // ========================================================================
    // 3. GUARDAR (Recibe el POST del formulario)
    // ========================================================================
    public function guardar() {
        // Seguridad B3: Solo método POST y Validar CSRF
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Acceso denegado.");
        }

        // Validación CSRF exacta a tu helper
        if(!validarCsrf($_POST['csrf_token'] ?? null)){
            setFlash('error', 'El formulario expiró o es inválido. Intenta de nuevo.');
            redirigir(urlAccion('presentacion', 'crear'));
            exit; // Detenemos la ejecución
        }

        // 1. Recibir y sanitizar datos
        $id_producto = isset($_POST['id_producto']) ? (int) $_POST['id_producto'] : 0;
        $etiqueta = isset($_POST['etiqueta']) ? trim($_POST['etiqueta']) : '';
        $unidades = isset($_POST['unidades']) ? (int) $_POST['unidades'] : 0;
        
        // Asegúrate de que esta función devuelve el ID numérico (como vimos en la Parte A)
        $id_usuario = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 1; 

        $errores = [];

        // 2. Validaciones B3 (Rigurosas)
        // a) Validar Producto
        if ($id_producto <= 0) {
            $errores[] = "Debe seleccionar un producto válido.";
        } else {
             // Opcional pero recomendado: verificar que el producto realmente existe y está activo
             $prodCheck = $this->productoModel->obtenerProductoPorId($id_producto);
             if (!$prodCheck || $prodCheck['estado'] == 0) {
                 $errores[] = "El producto seleccionado no existe o está inactivo.";
             }
        }

        // b) Validar Etiqueta (Texto de 1 a 30 caracteres)
        if (empty($etiqueta) || strlen($etiqueta) > 30) {
            $errores[] = "La etiqueta no puede estar vacía y debe tener máximo 30 caracteres.";
        }

        // c) Validar Unidades (Entero de 1 a 1000)
        if ($unidades < 1 || $unidades > 1000) {
            $errores[] = "Las unidades deben ser un número entre 1 y 1000.";
        }

        // Si hay errores, regresamos al formulario
        if (!empty($errores)) {
            guardarFormulario($_POST);
            setFlash('error', implode("<br>", $errores));
            redirigir(urlAccion('presentacion', 'crear'));
            exit;
        }

        // 3. Guardar en Base de Datos (Si no hay errores)
        renovarCsrf(); // Renovamos el token por seguridad
        
        $resultado = $this->presentacionModel->crear($id_producto, $etiqueta, $unidades, $id_usuario);

        // 4. Manejo de Respuesta (El Contrato B3)
        if ($resultado['ok'] === true) {
            setFlash('exito', '¡Presentación creada exitosamente!');
            // Redirigir al listado filtrado por ese producto
            redirigir(urlAccion('presentacion', 'index') . "&id_producto=" . $id_producto); 
        } else {
            // Manejo amable del error duplicado (1062)
            if ($resultado['motivo'] === 'duplicado') {
                setFlash('error', "Ese producto ya tiene una presentación activa con la etiqueta '{$etiqueta}'.");
            } else {
                setFlash('error', 'Error en la base de datos al guardar la presentación.');
            }
            guardarFormulario($_POST);
            redirigir(urlAccion('presentacion', 'crear'));
        }
    }

    // ========================================================================
    // 4. DESACTIVAR (Borrado lógico)
    // ========================================================================
    public function desactivar() {
         // Seguridad B3: Solo método POST y Validar CSRF para acciones destructivas
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Acceso denegado.");
        }

       // Validación CSRF exacta a tu helper
        if(!validarCsrf($_POST['csrf_token'] ?? null)){
            setFlash('error', 'El formulario expiró o es inválido. Intenta de nuevo.');
            $id_producto_retorno = isset($_POST['id_producto']) ? (int) $_POST['id_producto'] : 0;
            redirigir(urlAccion('presentacion', 'index') . "&id_producto=" . $id_producto_retorno);
            exit; // Detenemos la ejecución
        }

        $id_presentacion = isset($_POST['id_presentacion']) ? (int) $_POST['id_presentacion'] : 0;
        $id_producto_retorno = isset($_POST['id_producto']) ? (int) $_POST['id_producto'] : 0;

        if ($id_presentacion > 0) {
            $this->presentacionModel->desactivar($id_presentacion);
            setFlash('exito', 'La presentación ha sido desactivada. Ya no aparecerá en el formulario de compras.');
        } else {
             setFlash('error', 'ID de presentación inválido.');
        }

        // Redirigimos de vuelta al historial de ese producto
        redirigir(urlAccion('presentacion', 'index') . "&id_producto=" . $id_producto_retorno);
    }
}
?>