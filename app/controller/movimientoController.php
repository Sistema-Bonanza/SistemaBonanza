<?php
// Llamamos al helper de seguridad
require_once __DIR__ . '/../helpers/seguridad.php';

// Llamamos a la base de datos y a los modelos
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/movimientoModel.php';
require_once __DIR__ . '/../model/proveedorModel.php';
require_once __DIR__ . '/../model/productoModel.php';
require_once __DIR__ . '/../../config/constantes.php';

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

        //Obtenemos todas las compras desde el modelo
        $compras =$this->movimientoModel->obtenerCompras();

        // 2. FORMATEAMOS EL NÚMERO DE OPERACIÓN INTERNO
        foreach ($compras as &$compra) {
            // Rellena con ceros hasta tener 6 dígitos. Ej: COMP-000015
            $compra['numero_operacion'] = 'COMP-' . str_pad($compra['id_compra'], 6, '0', STR_PAD_LEFT);
        }
        unset($compra); // Rompemos la referencia por seguridad

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

        // 3. Cargamos las presentaciones desde la base de datos (Adiós archivo estático)
        $empaques = $this->productoModel->obtenerTodasLasPresentacionesActivas();
        // 4. redireccionamos a la vista de crear compra
        require_once __DIR__ . '/../views/movimientos/crearCompra.php';
    }


    // =======================================================
    // 2. MÉTODO PARA RECIBIR Y PROCESAR EL FORMULARIO
    // =======================================================
    public function guardarCompra() {
        // 1. Validamos la sesion.

        //Se exige que el usuario tengo el rol de admin o operador De no ser asi
        //la funcion exigirRol() redirecciona al index.php 

        exigirRol(['admin' , 'operador']);

        //2. Exigir metodo POST y validar el token CSRF
        // Si falla (ej: entran por GET o el token expiro), devuelve al formulario.

        exigirPostConCsrf(urlAccion('movimiento', 'crearcompra'));

            

         // ============================================================================================================
        //----------------------------------VALIDAMOS LA CABECERA-------------------------------------------------------
        //==============================================================================================================

        // El "BOUNCER" (Guardia de puerta)
        // Si no es POST, lo sacamos inmediatamente. no sigue procesando el resto del codigo.
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            setFlash('error', 'Acceso denegado. Debes enviar los datos desde el formulario.');
            redirigir(urlAccion('movimiento', 'crearCompra'));
        }        

        // Validamos el CSRF (Antifraude)
        //Si el token es invalido o expiró, lo sacamos
        if(!validarCsrf($_POST['csrf_token'] ?? null)){
            setFlash('error', 'El formulario expiró o es inválido. Intenta de nuevo.');
            redirigir(urlAccion('movimiento', 'crearCompra'));
        }

        // Validamos rol (PERMISOS)
        exigirRol(['admin', 'operador']);


        $errores = []; //aca se guardan los errores que se encuentren en la validacion de los datos del formulario

        //==============================================================================================================
        //Usamos la funcion validarTexto() para la factura: MAX 30 caracteres, solo letras, numero y guiones
        $id_proveedor = validarEntero($_POST['id_proveedor'] ?? null, 1);
        if($id_proveedor === null){
            $errores[] = "Debe seleccionar un proveedor válido.";
        } else {
            // Validamos que el proveedor existe y esta activo.
            $datos_proveedor = $this->proveedorModel->obtenerPorId($id_proveedor);
            if(!$datos_proveedor || $datos_proveedor['estado'] != 1){
                $errores[] = "El proveedor seleccionado no existe o se encuentra inactivo.";
                $id_proveedor = null; //lo invalidamos para que no se guarde en la base de datos
            }
            
        }
        //==============================================================================================================


        //==============================================================================================================
            //Usamos ValidarTexto() para la factura: MAX 30 caracteres, solo letras, numeros y guiones
        $numero_factura = validarTexto($_POST['numero_factura'] ?? null, 1, 30, '/^[A-Za-z0-9-]+\z/');
        if($numero_factura === null){
            $errores[] = "El numero de factura es invalido (solo letras, numero y guiones, maximo 30 caracteres).";
             
        } else {
            $numero_factura = strtoupper($numero_factura); //se ingresan todas las facturas en mayusculas para evitar duplicados por minusculas
        }
        //==============================================================================================================

        //==============================================================================================================
            //Usamos validarFecha(): Verifica que sea real y no futura. Formato: YYYY-MM-DD
            $fecha_factura = validarFecha($_POST['fecha_factura'] ?? null);
            if($fecha_factura === null){
                $errores[] = "La fecha de la factura no tiene un formato valido o es una fecha futura.";
            }
        //==============================================================================================================

        
        //==============================================================================================================
            //--------------------------------VALIDAMOS LAS 8 COLUMNAS DE DETALLES-------------------------------------------------------
        //==============================================================================================================

        // 1. Extraemos los arreglos correctamente (sin pisar variables de cabecera)
        $id_productos = postArray('id_producto');
        $tipos_empaques = postArray('tipo_empaque');
        $cantidades = postArray('cantidad_empaques');
        $costos_empaque = postArray('costo_por_empaque');


        $filas_validas = 0;
        $filas_procesadas_unicas = []; 
        $max_filas = 8; 

        // Variables para ir sumando los totales globales de la factura
        $subtotal_factura = 0;
        $monto_iva_factura = 0;
        $detalles_validos = []; // Aquí guardaremos las líneas perfectas para mandarlas al modelo

        // 2. Procesar, validar y calcular filas
        for ($i = 0; $i < $max_filas; $i++) {
            $n_fila = $i + 1; 
                
            $prod_id = $id_productos[$i] ?? '';
            $empaque_actual = $tipos_empaques[$i] ?? '';
            $cant = $cantidades[$i] ?? '';
            $costo = $costos_empaque[$i] ?? '';

            // Convertimos a valores numéricos para evaluar ceros
            $cant_num = (float) $cant;
            $costo_num = (float) $costo;

            // CASO 1: Fila completamente vacía -> Se ignora (continue)
            // Si no hay producto ni empaque, y la cantidad y costo son 0 o vacíos
            if ($prod_id === '' && $empaque_actual === '' && ($cant === '' || $cant_num == 0) && ($costo === '' || $costo_num == 0)) {
                continue;
            }

            // CASO 2: Fila a medias -> Error
            // Si falta alguno de los 4 datos esenciales en una fila que el usuario intentó llenar
            if ($prod_id === '' || $empaque_actual === '' || $cant === '' || $costo === '' || $cant_num <= 0 || $costo_num <= 0) {
                $errores[] = "Fila $n_fila: Has dejado campos incompletos o con valor cero.";
                continue;
            }

            // CASO 3: Fila completa -> Validaciones
            $prod_id = validarEntero($prod_id, 1);
            $aplica_iva = 0; // Variable para guardar si lleva IVA

            if ($prod_id === null) {
                $errores[] = "Fila $n_fila: El producto seleccionado es inválido.";
            } else {
                // Buscamos el producto en la BD para ver si existe y esta activo.
                $datos_producto = $this->productoModel->obtenerProductoPorId($prod_id);
                if (!$datos_producto || $datos_producto['estado'] != 1){
                    $errores[] = "Fila $n_fila: El producto no existe o se encuentra inactivo.";
                    $produ_id = null; // lo invalidamos.
                } else {
                    $aplica_iva = $datos_producto['aplica_iva']; //guardamos su IVA de una vez
                }
            }

            $unidades_por_empaque = 0;
            $id_presentacion_actual = null; // Declaramos la nueva variable
            $info_empaque = $this->resolverEmpaque($empaque_actual, $prod_id);
            
            if ($info_empaque === null) {
                $errores[] = "Fila $n_fila: El tipo de empaque es inválido o no pertenece a este producto.";
                $empaque_actual = null; // Lo invalidamos
            } else {
                $unidades_por_empaque = $info_empaque['unidades'];
                $empaque_actual = $info_empaque['tipo_empaque'];
                $id_presentacion_actual = $info_empaque['id_presentacion']; // Capturamos el ID real de la BD
            }


            $cant = validarEntero($cant, 1, 10000);
            if ($cant === null) {
                $errores[] = "Fila $n_fila: La cantidad debe ser un número entero entre 1 y 10000.";
            }

            $costo = validarDecimal($costo, 8, 2, false);
            if ($costo === null) {
                $errores[] = "Fila $n_fila: El costo debe ser un número mayor a cero y con máximo 2 decimales.";
            }

            // Validar Duplicados
            $firma_fila = $prod_id . '-' . $empaque_actual . '-' . $costo;
            if (in_array($firma_fila, $filas_procesadas_unicas)) {
                $errores[] = "Fila $n_fila: Estás repitiendo exactamente el mismo producto, empaque y costo.";
            } else {
                $filas_procesadas_unicas[] = $firma_fila;
            }

            // --- SI LA LÍNEA NO TIENE ERRORES HASTA AQUÍ, CALCULAMOS SUS TOTALES ---
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
                    'id_presentacion' => $id_presentacion_actual, // ENVIAMOS EL ID AL MOVIMIENTOMODEL
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

        // Verificación final
        if ($filas_validas === 0 && empty($errores)) {
            $errores[] = "Debes incluir al menos un producto en la factura.";
        }

        // ========================================================
        // MANEJO DE ERRORES (Rebotar al usuario si algo falló)
        // ========================================================
        if (!empty($errores)) {
            guardarFormulario($_POST); // Evita que el usuario pierda lo que escribió
            setFlash('error', $errores); // Envía los errores
            redirigir(urlAccion('movimiento', 'crearcompra')); // Regresa al formulario
        }

        // ========================================================
        // GUARDAR EN BASE DE DATOS
        // ========================================================
        // Renovar token porque el formulario fue validado con éxito
        renovarCsrf();

        // OBTENER EL ID NUMÉRICO DEL USUARIO (Ajustado según repositorio de GitHub)
        // Validamos si la sesión 'user_id' existe. Si existe, la forzamos a número (int).
        // Si no existe (por si se venció la sesión durante las pruebas), le asignamos 1 temporalmente
        // para que MySQL no arroje error 1048.
        if (isset($_SESSION['user_id'])) {
            $id_usuario = (int) $_SESSION['user_id'];
        } else {
            $id_usuario = 1; 
        }

        // A6: Asegurarnos de que las variables globales también estén redondeadas a 2 decimales
        $subtotal_factura = round($subtotal_factura, 2);
        $monto_iva_factura = round($monto_iva_factura, 2);
        
        // A6: El total ES EXACTAMENTE IGUAL al subtotal más IVA ya redondeados (Pasa el CHECK SQL)
        $total_factura = round($subtotal_factura + $monto_iva_factura, 2);

        $datos_cabecera = [
            'id_proveedor' => $id_proveedor,
            'numero_factura' => $numero_factura,
            'fecha_factura' => $fecha_factura,
            'subtotal' => $subtotal_factura,
            'tasa_iva' => TASA_IVA, // A6: Usamos la constante de config/constantes.php
            'monto_iva' => $monto_iva_factura,
            'total' => $total_factura,
            'id_usuario' => $id_usuario // Utiliza la variable correcta del historial
        ];

        // Guardar usando el Modelo
        $resultado = $this->movimientoModel->registrarCompraCompleta($datos_cabecera, $detalles_validos);

        // A8 y A9: El Controlador traduce el nuevo contrato
        if ($resultado['ok'] === true) {
            setFlash('exito', '¡Factura registrada exitosamente en el inventario!');
            redirigir(urlAccion('movimiento', 'tablaMovimientos'));
        } else {
            // A9: Leer el motivo y traducir a un mensaje amigable
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
    //----------------------------------MÉTODO PRIVADO PARA EMPAQUES Y UNIDADES-|----------------------------------------------------
    // ============================================================================================================

    private function resolverEmpaque($idPresentacion, $idProducto) {
        // 1. Validar que estemos recibiendo números válidos
        if (empty($idPresentacion) || !is_numeric($idPresentacion)) {
            return null;
        }

        // 2. Interrogar a la base de datos (Esto verifica automáticamente que la presentación le pertenezca a ese producto)
        $presentacion = $this->productoModel->obtenerPresentacionValida($idPresentacion, $idProducto);

        if ($presentacion) {
            return [
                'id_presentacion' => $presentacion['id_presentacion'],
                'tipo_empaque'    => $presentacion['etiqueta'],
                'unidades'        => (int) $presentacion['unidades']
            ];
        }

        return null; // Si alguien manipula el HTML e intenta meter un ID falso, el sistema lo bloquea aquí
    }

    // ============================================================================================================
    // MÉTODO AJAX PARA GUARDAR PRESENTACIÓN DESDE EL MODAL
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

        // Si viene vacío desde el formulario, lo dejamos como null (Estándar Global)
        $id_producto = !empty($_POST['id_producto']) ? validarEntero($_POST['id_producto'], 1) : null;
        $etiqueta = validarTexto($_POST['nombre'] ?? null, 1, 30);
        $unidades = validarEntero($_POST['unidades'] ?? null, 1, 1000);

        // Ya no exigimos $id_producto como obligatorio
        if (!$etiqueta || !$unidades) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos. Verifica que seleccionaste un producto, un nombre válido y unidades entre 1 y 1000.']);
            return;
        }

        $id_usuario = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

        // Llamar al modelo para guardar en la base de datos
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

    public function buscarAjax() {
    // 1. Recibir variables de JS
    $busqueda = $_POST['busqueda'] ?? '';
    $filtro = $_POST['filtro'] ?? 'todas';
    $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
    $limitePorPagina = 10;
    $offset = ($pagina - 1) * $limitePorPagina;

    // 2. Pedir al modelo que ejecute la consulta SQL
    // (Necesitarás crear esta función en tu Modelo)
    $resultados = $this->movimientoModel->obtenerMovimientosPaginados($busqueda, $filtro, $offset, $limitePorPagina);
    $totalRegistros = $this->movimientoModel->contarMovimientosPaginados($busqueda, $filtro);

    // 3. Construir el HTML de las filas (<tr>)
    $filasHTML = '';
    if(!empty($resultados)) {
        foreach($resultados as $compra) {
            // Usamos un buffer de salida para no ensuciar el controlador con HTML
            ob_start();
            
            // Reutiliza tus cálculos (fecha en español, iniciales, etc)
            $fecha = new DateTime($compra['fecha_registro']);
            // ... (Copia aquí tu lógica de variables para la fila) ...

            // Generamos el HTML exacto de la fila (Copia tu <tr> actual aquí adentro)
            ?>
            <tr>
                <td class="is-vcentered px-4">
                    <p class="has-text-weight-bold has-text-dark mb-0"><?= $fecha->format('d M Y') ?></p>
                </td>
                <!-- ... Resto de tus columnas ... -->
            </tr>
            <?php
            // Guardamos la fila construida y limpiamos el buffer
            $filasHTML .= ob_get_clean(); 
        }
    } else {
        $filasHTML = '<tr><td colspan="7" class="has-text-centered py-6 has-text-grey">No se encontraron resultados para su búsqueda.</td></tr>';
    }

    // 4. Construir HTML de paginación matemáticamente
    $paginacionHTML = ''; 
    $totalPaginas = ceil($totalRegistros / $limitePorPagina);

    if ($totalPaginas > 0) {
        // Botón Anterior
        $prevDisabled = ($pagina <= 1) ? 'disabled' : '';
        $prevPage = $pagina - 1;
        $paginacionHTML .= "<a class='pagination-previous' data-pagina='{$prevPage}' {$prevDisabled}><span class='icon'><span class='material-symbols-outlined'>chevron_left</span></span></a>";

        // Botón Siguiente
        $nextDisabled = ($pagina >= $totalPaginas) ? 'disabled' : '';
        $nextPage = $pagina + 1;
        $paginacionHTML .= "<a class='pagination-next' data-pagina='{$nextPage}' {$nextDisabled}><span class='icon'><span class='material-symbols-outlined'>chevron_right</span></span></a>";

        // Números de Página
        $paginacionHTML .= "<ul class='pagination-list'>";
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $isCurrent = ($i == $pagina) ? 'is-current has-background-link has-border-link' : '';
            $paginacionHTML .= "<li><a class='pagination-link {$isCurrent}' data-pagina='{$i}'>{$i}</a></li>";
        }
        $paginacionHTML .= "</ul>";
    }

    // 5. Responder a JavaScript en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'filasHTML' => $filasHTML,
        'paginacionHTML' => $paginacionHTML,
        'totalRegistros' => $totalRegistros
    ]);
    exit; // Importante para que no se imprima el layout principal
}
}