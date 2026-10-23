<?php
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/proveedorModel.php';
require_once __DIR__ . '/../model/productoModel.php';
require_once __DIR__ . '/../model/categoriaModel.php';


Class productoController{

    private $proveedorModel;
    private $productoModel;
    private $categoriaModel;

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();

        //Se instancia los 3 modelos porque se necesitan para guardar un producto.
       $this->proveedorModel = new ProveedorModel($pdo);
        $this->categoriaModel = new CategoriaModel($pdo);
        $this->productoModel = new ProductoModel($pdo);

    }


    //Metodos para mostrar la vistas de productos, crear producto, crear categoria y crear proveedor

    public function TablaProductos(){
        exigirSesion();
        evitarCache();
        $productos = $this->productoModel->obtenerProductos();
        require_once __DIR__.'/../views/producto/producto.php';
    }
    public function formproduct(){
        exigirSesion();
        //Este metodo solo sirve para mostrar la vista de crear producto.
        require_once __DIR__.'/../../app/views/producto/producto.php';
    }
    public function formcategoria(){
        exigirSesion();
        //Este metodo solo sirve para mostrar la vista de crear categoria.
        require_once __DIR__.'/../../app/views/crearCategoria.php';
    }
    public function formproveedor(){
        exigirSesion();
        //Este metodo solo sirve para mostrar la vista de crear proveedor.
        require_once __DIR__.'/../../app/views/crearProveedor.php';
    }

    public function formCrear(){
        exigirSesion();
        evitarCache();

        // 1. Se ejecutan los métodos que extraen los datos y los guardas en variables
        $categorias = $this->categoriaModel->getAll();
        $proveedores = $this->proveedorModel->getActivos();

        // Como las variables $categorias y $proveedores ya están definidas aquí,
        // el archivo HTML podrá leerlas sin problema gracias al 'require_once'.
        //ESTE METODO SOLO SIRVE PARA MOSTRAR LA VISTA DE CREAR PRODUCTO.
        require_once __DIR__.'/../../app/views/producto/crearProducto.php';
    }


    // Metodos relacionados a productos
    public function guardarProducto(){
        exigirSesion();
        $urlFormulario = urlAccion('producto', 'formCrear');
        exigirPost($urlFormulario);

        // 1. VALIDACIÓN DE LOS DATOS DEL FORMULARIO
        list($datos, $errores) = $this->validarDatos(null);
        if(!empty($errores)){
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // 2. DATOS QUE NO VIENEN DEL FORMULARIO (valores fijos al crear)
        $precio_compra = 0;
        $precio_venta  = 0; // Se gestiona en el módulo de Control de Precios
        $stock_minimo  = 0;
        $stock_actual  = 0; // El stock solo sube con compras (módulo de Movimientos)

        // 3. GUARDAR
        $exito = $this->productoModel->crearProducto(
            $datos['codigo'],
            $datos['nombre'],
            $datos['id_categoria'],
            $datos['aplica_iva'],
            $stock_minimo,
            $stock_actual,
            $precio_compra,
            $precio_venta
        );

        if($exito){
            setFlash('exito', 'Producto «' . $datos['nombre'] . '» registrado correctamente.');
            redirigir(urlAccion('producto', 'TablaProductos'));
        }

        setFlash('error', 'No se pudo guardar el producto en la base de datos.');
        redirigir($urlFormulario);
    }

    public function formEditar(){
        exigirSesion();
        evitarCache();

        $id = validarEntero($_GET['id'] ?? null, 1);
        if($id !== null){
            // OBTENER LOS DATOS  ACTUALES DEL PRODUCTO
            $producto = $this->productoModel->obtenerProductoPorId($id);

            // VALIDAMOS QUE EL PRODUCTO EXISTA Y ESTÉ ACTIVO (un producto desactivado no se edita)
            if($producto && (int) $producto['estado'] === 1){

                // Obtener todas las categorías para llenar el menú desplegable
                $categorias = $this->categoriaModel->getAll();

                //CARGAR LA VISTA CON LOS DATOS LISTOS
                require_once __DIR__.'/../../app/views/producto/editarProducto.php';
                return;
            }
        }

        // ERROR: sin ID, ID inválido o producto inexistente/desactivado
        setFlash('error', 'El producto no existe o está desactivado.');
        redirigir(urlAccion('producto', 'TablaProductos'));
    }

    public function actualizarProducto(){
        exigirSesion();
        exigirPost(urlAccion('producto', 'TablaProductos'));

        // 1. El producto debe existir y estar activo
        $id_producto = validarEntero($_POST['id_producto'] ?? null, 1);
        $actual = ($id_producto !== null) ? $this->productoModel->obtenerProductoPorId($id_producto) : false;
        if(!$actual || (int) $actual['estado'] !== 1){
            setFlash('error', 'El producto que intentas editar no existe o está desactivado.');
            redirigir(urlAccion('producto', 'TablaProductos'));
        }

        $urlFormulario = urlAccion('producto', 'formEditar', ['id' => $id_producto]);

        // 2. Validar los datos (el propio producto no cuenta como código repetido)
        list($datos, $errores) = $this->validarDatos($id_producto);
        if(!empty($errores)){
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // 3. Ejecutar la actualización en el modelo
        $exito = $this->productoModel->actualizarProducto(
            $id_producto,
            $datos['codigo'],
            $datos['nombre'],
            $datos['id_categoria'],
            $datos['aplica_iva']
        );

        if($exito){
            setFlash('exito', 'Producto «' . $datos['nombre'] . '» actualizado correctamente.');
            redirigir(urlAccion('producto', 'TablaProductos'));
        }

        setFlash('error', 'Ocurrió un problema al actualizar el producto en la base de datos.');
        redirigir($urlFormulario);
    }

    public function eliminarProducto() {
        exigirSesion();
        $urlLista = urlAccion('producto', 'tablaproductos'); // Ajusta a 'TablaProductos' si así se llama tu ruta

        // 1. Capturamos el ID de forma segura
        $idBruto = $_POST['id'] ?? $_POST['id_producto'] ?? null;
        $id = validarEntero($idBruto, 1);
        
        $producto = ($id !== null) ? $this->productoModel->obtenerProductoPorId($id) : false;

        // 2. Validaciones estrictas
        if(!$producto) {
            setFlash('error', 'Fallo de ID: No se recibió un ID válido o el producto no existe.');
            redirigir($urlLista);
        }

        if(!isset($producto['estado'])) {
            setFlash('error', 'Fallo de SQL: La función obtenerProductoPorId() en tu Modelo no está seleccionando la columna "estado".');
            redirigir($urlLista);
        }

        if((int) $producto['estado'] !== 1){
            setFlash('error', 'El producto ya se encuentra desactivado en el sistema.');
            redirigir($urlLista);
        }

        if((int) $producto['stock_actual'] > 0){
            // Guardamos el error en una variable de sesión única para SweetAlert
            $_SESSION['sweet_error_stock'] = 'No se puede desactivar «' . $producto['nombre'] . '»: todavía tiene ' . (int) $producto['stock_actual'] . ' unidad(es) en almacén.';
            redirigir($urlLista);
        }

        // 3. Ejecutamos el borrado lógico (UPDATE) en el modelo
        if($this->productoModel->eliminarProducto($id)){
            setFlash('exito', 'Producto «' . $producto['nombre'] . '» desactivado exitosamente.');
        } else {
            setFlash('error', 'Ocurrió un problema en la base de datos al intentar desactivar el producto.');
        }
        
        // 4. Redirigimos de vuelta a la tabla (Esto es lo que evita la pantalla en blanco)
        redirigir($urlLista);
    }

    // ==================================================================
    // VALIDACIÓN COMÚN DE CREAR Y EDITAR
    // ==================================================================

    /**
     * $idExcluir: al editar, el propio producto no cuenta como código repetido.
     * Devuelve [datos limpios, lista de errores].
     */
    private function validarDatos(?int $idExcluir): array {
        $errores = [];

        // codigo: VARCHAR(50) NOT NULL UNIQUE
        $codigo = validarCodigo($_POST['codigo'] ?? null, 50);
        if($codigo === null){
            $errores[] = 'El código es obligatorio: hasta 50 caracteres con letras, números, punto, guion o guion bajo (sin espacios).';
        } elseif($this->productoModel->existeCodigo($codigo, $idExcluir)){
            $errores[] = 'Ya existe un producto con el código «' . $codigo . '».';
        }

        // nombre: VARCHAR(150) NOT NULL
        $nombre = validarNombreComercial($_POST['nombre'] ?? null, 2, 150);
        if($nombre === null){
            $errores[] = 'El nombre es obligatorio: de 2 a 150 caracteres (no se permite < ni >).';
        }

        // id_categoria: debe ser un número y corresponder a una categoría que EXISTA y esté ACTIVA.
        $id_categoria = validarEntero($_POST['id_categoria'] ?? null, 1);
        if($id_categoria === null || !$this->categoriaActiva($id_categoria)){
            $errores[] = 'Selecciona una categoría válida.';
            $id_categoria = null;
        }

        return [[
            'codigo'       => $codigo,
            'nombre'       => $nombre,
            'id_categoria' => $id_categoria,
            'aplica_iva'   => isset($_POST['aplica_iva']) ? 1 : 0, // Checkbox booleano
        ], $errores];
    }

    /** ¿Existe la categoría y está activa (estado = 1)? */
    private function categoriaActiva(int $id): bool {
        foreach($this->categoriaModel->getAll() as $c){
            if((int) $c['id_categoria'] === $id){
                return (int) $c['estado'] === 1;
            }
        }
        return false;
    }
}


?>
