<?php
// Llamamos al helper de seguridad
require_once __DIR__ . '/../helpers/Seguridad.php';

// Llamamos a la base de datos y a los modelos
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/movimientoModel.php';
require_once __DIR__ . '/../model/proveedorModel.php';
require_once __DIR__ . '/../model/productoModel.php';

class MovimientoController {
    private $movimientoModel;
    private $proveedorModel;
    private $productoModel;

    public function __construct() {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Instanciamos los modelos
        $this->movimientoModel = new MovimientoModel($pdo);
        $this->proveedorModel = new ProveedorModel($pdo);
        $this->productoModel = new ProductoModel($pdo);
    }

    // =======================================================
    // 1. MÉTODO PARA MOSTRAR LA VISTA (El que crea las variables)
    // =======================================================
    // Metodo que carga la vista de movimientos, que es la tabla principal.
    public function tablaMovimientos(){
        // 1. Validamos la session 
        exigirSesion();

        require_once __DIR__ . '/../views/movimientos/movimientos.php';
    }


    // metodo que carga la vista de crear compra, que es el formulario para registrar una nueva compra.
    public function crearcompra(){
        // 1. Validamos la sesion.
        exigirSesion();

        // 2. Extraemos los datos de la base de datos
        // Asegúrate de que los nombres de estas funciones coincidan exactamente con tu modelo

        $proveedores = $this->proveedorModel->getActivos();
        $productos = $this->productoModel->obtenerProductos();

        // 3. Cargamos el arreglo de empaques desde el archivo
            $empaques = require __DIR__ . '/../../config/empaques.php';
        // 4. redireccionamos a la vista de crear compra
        require_once __DIR__ . '/../views/movimientos/crearCompra.php';
    }


    // =======================================================
    // 2. MÉTODO PARA RECIBIR Y PROCESAR EL FORMULARIO
    // =======================================================
    public function guardarCompra() {
    exigirSesion(); 

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // 1. Cabecera
            $id_proveedor = trim($_POST['id_proveedor']);
            $numero_factura = trim($_POST['numero_factura']);
            $fecha_factura = trim($_POST['fecha_factura']);
            
            // REVISA ESTO: Cambia 'id_usuario' por el nombre real de tu variable de sesión
            $id_usuario = $_SESSION['id_usuario']; 

            $tasa_iva_global = 16.00;
            $subtotal_factura = 0;
            $monto_iva_factura = 0;

            // 2. Detalles (Arreglos)
            $productos = $_POST['id_producto'];
            $empaques_seleccionados = $_POST['tipo_empaque'];
            $cantidades = $_POST['cantidad_empaques'];
            $costos_empaque = $_POST['costo_por_empaque'];

            $lista_empaques = require __DIR__ . '/../../config/empaques.php';
            $detalles_validos = [];

            // 3. Procesar y calcular filas
            for ($i = 0; $i < count($productos); $i++) {
                if (!empty($productos[$i]) && $cantidades[$i] > 0 && $costos_empaque[$i] >= 0) {
                    
                    $id_prod = $productos[$i];
                    $tipo_emp = $empaques_seleccionados[$i];
                    $cant_emp = $cantidades[$i];
                    $costo_emp = $costos_empaque[$i];

                    // Cálculos
                    $unidades_por_empaque = $lista_empaques[$tipo_emp]['unidades'];
                    $cantidad_unidades_reales = $cant_emp * $unidades_por_empaque;
                    $costo_unitario = ($unidades_por_empaque > 0) ? ($costo_emp / $unidades_por_empaque) : 0;
                    $subtotal_linea = $cant_emp * $costo_emp;

                    // Extraemos dinámicamente si aplica IVA desde la base de datos
                    $datos_producto = $this->productoModel->obtenerProductoPorId($id_prod);
                    $aplica_iva = $datos_producto['aplica_iva'];

                    // Sumamos a los totales globales de la factura
                    $subtotal_factura += $subtotal_linea;
                    if ($aplica_iva == 1) {
                        $monto_iva_factura += ($subtotal_linea * ($tasa_iva_global / 100));
                    }

                    $detalles_validos[] = [
                        'id_producto' => $id_prod,
                        'tipo_empaque' => $tipo_emp,
                        'cantidad_empaques' => $cant_emp,
                        'unidades_por_empaque' => $unidades_por_empaque,
                        'cantidad_unidades' => $cantidad_unidades_reales,
                        'costo_por_empaque' => $costo_emp,
                        'costo_unitario' => $costo_unitario,
                        'aplica_iva' => $aplica_iva,
                        'subtotal_linea' => $subtotal_linea
                    ];
                }
            }

            if (count($detalles_validos) === 0) {
                die("Error: Debe ingresar cantidades válidas para procesar la compra.");
            }

            $total_factura = $subtotal_factura + $monto_iva_factura;

            $datos_cabecera = [
                'id_proveedor' => $id_proveedor,
                'numero_factura' => $numero_factura,
                'fecha_factura' => $fecha_factura,
                'subtotal' => $subtotal_factura,
                'tasa_iva' => $tasa_iva_global,
                'monto_iva' => $monto_iva_factura,
                'total' => $total_factura,
                'id_usuario' => $id_usuario
            ];

            // 4. Guardar todo usando el Modelo
            $resultado = $this->movimientoModel->registrarCompraCompleta($datos_cabecera, $detalles_validos);

            if ($resultado) {
                header("Location: index.php?controller=movimiento&action=tablaMovimientos");
                exit();
            } else {
                die("Error de base de datos. Se hizo RollBack.");
            }
        }
    }
}