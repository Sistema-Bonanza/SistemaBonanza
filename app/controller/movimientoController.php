<?php
// 1. CARGA OBLIGATORIA DE HELPERS Y CONFIGURACIÓN
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Llamamos a los modelos necesarios
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
    // 1. MÉTODO PARA MOSTRAR LA VISTA DE LA TABLA PRINCIPAL
    // =======================================================
    public function tablaMovimientos(){
        exigirSesion();
        evitarCache();
        
        // Obtenemos todas las compras iniciales desde el modelo para la carga síncrona
        // En un futuro, podrías cargar la vista vacía y dejar que JS haga la primera petición, 
        // pero lo mantenemos así si tu diseño actual lo requiere.
        $compras = $this->movimientoModel->obtenerMovimientosPaginados('', 'todas', 0, 10);

        require_once __DIR__ . '/../views/movimientos/movimientos.php'; // Asegúrate que la ruta coincida ('movimientos' vs 'movimiento')
    }

    // =======================================================
    // 2. PETICIÓN AJAX: BÚSQUEDA, FILTRO Y PAGINACIÓN DINÁMICA
    // =======================================================
    public function buscarajax() {
        // Limpiamos cualquier rastro previo de búfer para evitar espacios o HTML accidental
        if (ob_get_length()) {
            ob_clean();
        }

        exigirSesion();
        
        try {
            // Capturamos los parámetros por GET
            $busqueda = $_GET['busqueda'] ?? '';
            $filtro = $_GET['filtro'] ?? 'todas';
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            if ($pagina < 1) $pagina = 1;
            
            $limitePorPagina = 10;
            $offset = ($pagina - 1) * $limitePorPagina;

            $resultados = $this->movimientoModel->obtenerMovimientosPaginados($busqueda, $filtro, $offset, $limitePorPagina);
            $totalRegistros = $this->movimientoModel->contarMovimientosPaginados($busqueda, $filtro);

            $filasHTML = '';
            if (!empty($resultados)) {
                foreach ($resultados as $compra) {
                    ob_start();
                    require __DIR__ . '/../views/movimientos/filas_ajax.php'; 
                    $filasHTML .= ob_get_clean(); 
                }
            } else {
                $filasHTML = '<tr><td colspan="7" class="has-text-centered py-6"><div class="is-flex is-flex-direction-column is-align-items-center"><span class="material-symbols-outlined has-text-grey-light mb-3" style="font-size: 3rem;">inventory_2</span><p class="has-text-grey has-text-weight-bold is-size-5">No se encontraron operaciones.</p></div></td></tr>';
            }

            $paginacionHTML = ''; 
            $totalPaginas = ceil($totalRegistros / $limitePorPagina);

            if ($totalPaginas > 1) {
                $prevDisabled = ($pagina <= 1) ? 'disabled style="pointer-events:none; opacity:0.5;"' : '';
                $prevPage = $pagina - 1;
                $paginacionHTML .= "<a class='pagination-previous' data-pagina='{$prevPage}' {$prevDisabled}><span class='icon'><span class='material-symbols-outlined'>chevron_left</span></span></a>";

                $nextDisabled = ($pagina >= $totalPaginas) ? 'disabled style="pointer-events:none; opacity:0.5;"' : '';
                $nextPage = $pagina + 1;
                $paginacionHTML .= "<a class='pagination-next' data-pagina='{$nextPage}' {$nextDisabled}><span class='icon'><span class='material-symbols-outlined'>chevron_right</span></span></a>";

                $paginacionHTML .= "<ul class='pagination-list'>";
                for ($i = 1; $i <= $totalPaginas; $i++) {
                    $isCurrent = ($i === $pagina) ? 'is-current has-background-link has-border-link' : '';
                    $paginacionHTML .= "<li><a class='pagination-link {$isCurrent}' data-pagina='{$i}'>{$i}</a></li>";
                }
                $paginacionHTML .= "</ul>";
            }

            // Forzamos la cabecera JSON y detenemos cualquier ejecución extra
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'filasHTML' => $filasHTML,
                'paginacionHTML' => $paginacionHTML,
                'totalRegistros' => $totalRegistros
            ]);
            exit;

        } catch (Throwable $e) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // =======================================================
    // 3. MÉTODOS PARA CREAR UNA COMPRA Y PROCESARLA
    // =======================================================
    
    // Método que carga la vista de crear compra (formulario)
    public function crearcompra(){
        exigirSesion();
        
        $proveedores = $this->proveedorModel->getActivos();
        $productos = $this->productoModel->obtenerProductos();
        $empaques = $this->productoModel->obtenerTodasLasPresentacionesActivas();
        
        require_once __DIR__ . '/../views/movimientos/crearCompra.php';
    }

    public function guardarCompra() {
        exigirRol(['admin' , 'operador']);
        exigirPostConCsrf(urlAccion('movimiento', 'crearcompra'));

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            setFlash('error', 'Acceso denegado. Debes enviar los datos desde el formulario.');
            redirigir(urlAccion('movimiento', 'crearCompra'));
        }        

        if(!validarCsrf($_POST['csrf_token'] ?? null)){
            setFlash('error', 'El formulario expiró o es inválido. Intenta de nuevo.');
            redirigir(urlAccion('movimiento', 'crearCompra'));
        }

        $errores = [];

        // Validar Proveedor
        $id_proveedor = validarEntero($_POST['id_proveedor'] ?? null, 1);
        if($id_proveedor === null){
            $errores[] = "Debe seleccionar un proveedor válido.";
        } else {
            $datos_proveedor = $this->proveedorModel->obtenerPorId($id_proveedor);
            if(!$datos_proveedor || $datos_proveedor['estado'] != 1){
                $errores[] = "El proveedor seleccionado no existe o se encuentra inactivo.";
                $id_proveedor = null;
            }
        }

        // Validar Factura
        $numero_factura = validarTexto($_POST['numero_factura'] ?? null, 1, 30, '/^[A-Za-z0-9-]+\z/');
        if($numero_factura === null){
            $errores[] = "El numero de factura es invalido (solo letras, numero y guiones, maximo 30 caracteres).";
        } else {
            $numero_factura = strtoupper($numero_factura);
        }

        // Validar Fecha
        $fecha_factura = validarFecha($_POST['fecha_factura'] ?? null);
        if($fecha_factura === null){
            $errores[] = "La fecha de la factura no tiene un formato valido o es una fecha futura.";
        }

        // ==========================================
        // PROCESAR DETALLES DE LA COMPRA
        // ==========================================
        $id_productos = postArray('id_producto');
        $tipos_empaques = postArray('tipo_empaque');
        $cantidades = postArray('cantidad_empaques');
        $costos_empaque = postArray('costo_por_empaque');

        $filas_validas = 0;
        $filas_procesadas_unicas = []; 
        $max_filas = count($id_productos);

        $subtotal_factura = 0;
        $monto_iva_factura = 0;
        $detalles_validos = []; 

        for ($i = 0; $i < $max_filas; $i++) {
            $n_fila = $i + 1; 
                
            $prod_id = $id_productos[$i] ?? '';
            $empaque_actual = $tipos_empaques[$i] ?? '';
            $cant = $cantidades[$i] ?? '';
            $costo = $costos_empaque[$i] ?? '';

            $cant_num = (float) $cant;
            $costo_num = (float) $costo;

            if ($prod_id === '' && $empaque_actual === '' && ($cant === '' || $cant_num == 0) && ($costo === '' || $costo_num == 0)) {
                continue;
            }

            if ($prod_id === '' || $empaque_actual === '' || $cant === '' || $costo === '' || $cant_num <= 0 || $costo_num <= 0) {
                $errores[] = "Fila $n_fila: Has dejado campos incompletos o con valor cero.";
                continue;
            }

            $prod_id = validarEntero($prod_id, 1);
            $aplica_iva = 0; 

            if ($prod_id === null) {
                $errores[] = "Fila $n_fila: El producto seleccionado es inválido.";
            } else {
                $datos_producto = $this->productoModel->obtenerProductoPorId($prod_id);
                if (!$datos_producto || $datos_producto['estado'] != 1){
                    $errores[] = "Fila $n_fila: El producto no existe o se encuentra inactivo.";
                    $prod_id = null; 
                } else {
                    $aplica_iva = $datos_producto['aplica_iva']; 
                }
            }

            $unidades_por_empaque = 0;
            $id_presentacion_actual = null; 
            $info_empaque = $this->resolverEmpaque($empaque_actual, $prod_id);
            
            if ($info_empaque === null) {
                $errores[] = "Fila $n_fila: El tipo de empaque es inválido o no pertenece a este producto.";
                $empaque_actual = null; 
            } else {
                $unidades_por_empaque = $info_empaque['unidades'];
                $empaque_actual = $info_empaque['tipo_empaque'];
                $id_presentacion_actual = $info_empaque['id_presentacion']; 
            }

            $cant = validarEntero($cant, 1, 10000);
            if ($cant === null) {
                $errores[] = "Fila $n_fila: La cantidad debe ser un número entero entre 1 y 10000.";
            }

            $costo = validarDecimal($costo, 8, 2, false);
            if ($costo === null) {
                $errores[] = "Fila $n_fila: El costo debe ser un número mayor a cero y con máximo 2 decimales.";
            }

            $firma_fila = $prod_id . '-' . $empaque_actual . '-' . $costo;
            if (in_array($firma_fila, $filas_procesadas_unicas)) {
                $errores[] = "Fila $n_fila: Estás repitiendo exactamente el mismo producto, empaque y costo.";
            } else {
                $filas_procesadas_unicas[] = $firma_fila;
            }

            if ($prod_id !== null && $empaque_actual !== null && $cant !== null && $costo !== null) {
                
                $cantidad_unidades_reales = $cant * $unidades_por_empaque;
                $costo_unitario = ($unidades_por_empaque > 0) ? round(($costo / $unidades_por_empaque), 4) : 0;
                $subtotal_linea = round(($cant * $costo), 2);
                $subtotal_factura += $subtotal_linea;
                
                if ($aplica_iva == 1) {
                    $iva_linea = round(($subtotal_linea * (TASA_IVA / 100)), 2);
                    $monto_iva_factura += $iva_linea;
                }

                $detalles_validos[] = [
                    'id_producto' => $prod_id,
                    'id_presentacion' => $id_presentacion_actual, 
                    'tipo_empaque' => $empaque_actual,
                    'cantidad_empaques' => $cant,
                    'unidades_por_empaque' => $unidades_por_empaque,
                    'cantidad_unidades' => $cantidad_unidades_reales,
                    'costo_por_empaque' => $costo,
                    'costo_unitario' => $costo_unitario,
                    'aplica_iva' => $aplica_iva,
                    'subtotal_linea' => $subtotal_linea
                ];
            }

            $filas_validas++;
        }

        if ($filas_validas === 0 && empty($errores)) {
            $errores[] = "Debes incluir al menos un producto en la factura.";
        }

        if (!empty($errores)) {
            guardarFormulario($_POST); 
            setFlash('error', $errores); 
            redirigir(urlAccion('movimiento', 'crearcompra')); 
        }

        // Proceder a guardar
        renovarCsrf();

        if (isset($_SESSION['user_id'])) {
            $id_usuario = (int) $_SESSION['user_id'];
        } else {
            $id_usuario = 1; 
        }

        $subtotal_factura = round($subtotal_factura, 2);
        $monto_iva_factura = round($monto_iva_factura, 2);
        $total_factura = round($subtotal_factura + $monto_iva_factura, 2);

        $datos_cabecera = [
            'id_proveedor' => $id_proveedor,
            'numero_factura' => $numero_factura,
            'fecha_factura' => $fecha_factura,
            'subtotal' => $subtotal_factura,
            'tasa_iva' => TASA_IVA, 
            'monto_iva' => $monto_iva_factura,
            'total' => $total_factura,
            'id_usuario' => $id_usuario
        ];

        $resultado = $this->movimientoModel->registrarCompraCompleta($datos_cabecera, $detalles_validos);

        if ($resultado['ok'] === true) {
            setFlash('exito', '¡Factura registrada exitosamente en el inventario!');
            redirigir(urlAccion('movimiento', 'tablaMovimientos'));
        } else {
            if ($resultado['motivo'] === 'factura_duplicada') {
                setFlash('error', 'Esa factura ya está registrada para este proveedor. Verifica el número.');
            } else {
                setFlash('error', 'Error en la base de datos. Intente nuevamente.');
            }
            guardarFormulario($_POST);
            redirigir(urlAccion('movimiento', 'crearcompra'));
        }
    }

    // ============================================================================================================
    // MÉTODO PRIVADO PARA RESOLVER EMPAQUES
    // ============================================================================================================
    private function resolverEmpaque($idPresentacion, $idProducto) {
        if (empty($idPresentacion) || !is_numeric($idPresentacion)) {
            return null;
        }

        $presentacion = $this->productoModel->obtenerPresentacionValida($idPresentacion, $idProducto);

        if ($presentacion) {
            return [
                'id_presentacion' => $presentacion['id_presentacion'],
                'tipo_empaque'    => $presentacion['etiqueta'],
                'unidades'        => (int) $presentacion['unidades']
            ];
        }

        return null;
    }

    // ============================================================================================================
    // PETICIÓN AJAX: GUARDAR PRESENTACIÓN DESDE MODAL
    // ============================================================================================================
    public function guardarPresentacionAjax() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
            return;
        }

        if (!validarCsrf($_POST['csrf_token'] ?? null)) {
            echo json_encode(['ok' => false, 'error' => 'Token de seguridad inválido o expirado.']);
            return;
        }

        $id_producto = !empty($_POST['id_producto']) ? validarEntero($_POST['id_producto'], 1) : null;
        $etiqueta = validarTexto($_POST['nombre'] ?? null, 1, 30);
        $unidades = validarEntero($_POST['unidades'] ?? null, 1, 1000);

        if (!$etiqueta || !$unidades) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos. Verifica que seleccionaste un producto, un nombre válido y unidades entre 1 y 1000.']);
            return;
        }

        $id_usuario = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
        $resultado = $this->productoModel->crearPresentacion($id_producto, $etiqueta, $unidades, $id_usuario);

        if ($resultado['ok']) {
            echo json_encode([
                'ok' => true,
                'id_presentacion' => $resultado['id_presentacion'],
                'nombre' => $etiqueta,
                'unidades' => $unidades,
                'id_producto' => $id_producto
            ]);
        } else {
            $msjError = ($resultado['motivo'] === 'duplicado') 
                ? 'Este producto ya tiene una presentación registrada con ese nombre.' 
                : 'Error interno en la base de datos.';
            echo json_encode(['ok' => false, 'error' => $msjError]);
        }
    }
}