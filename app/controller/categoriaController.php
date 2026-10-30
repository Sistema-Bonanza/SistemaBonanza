<?php
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../model/categoriaModel.php';
require_once __DIR__ . '/../../config/database.php';

class CategoriaController {
    private $categoriaModel;

    public function __construct() {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->categoriaModel = new CategoriaModel($pdo);
    }

    public function formcategoria() {
        exigirSesion();
        evitarCache();
        
        // 1. Extraemos las categorías con el conteo de artículos
        $categorias = $this->categoriaModel->getAll();
        
        // 2. Extraemos los KPIs en tiempo real
        $kpis = $this->categoriaModel->getKpis();
        
        // 3. Declaramos las variables exactas que espera la vista
        $kpiTotalProductos = $kpis['total_productos'];
        $kpiHuerfanos      = $kpis['huerfanos'];
        $kpiMayorRotacion  = $kpis['mayor_rotacion'];

        // 4. Renderizamos la vista
        require_once __DIR__ . '/../../app/views/categorias/categorias.php';
    }

    public function fromcrearCategoria() {
        exigirSesion();
        evitarCache();

        $categorias = $this->categoriaModel->getAll();
        require_once __DIR__ . '/../../app/views/categorias/crearCategoria.php';
    }

    public function fromeditarCategoria() {
        exigirSesion();
        evitarCache();

        $id = validarEntero($_GET['id'] ?? null, 1);
        if ($id !== null) {
            $categoria = $this->categoriaModel->getById($id);
            if ($categoria) {
                // 1. Extraemos los productos reales de esta categoría
                $productosAsociados = $this->categoriaModel->getProductosByCategoria($id);
                
                // 2. Calculamos los totales dinámicos (Cambiamos 'stock' a 'stock_actual')
                $totalArticulos = count($productosAsociados);
                $stockTotal = array_sum(array_column($productosAsociados, 'stock_actual'));

                require_once __DIR__ . '/../../app/views/categorias/editarCategoria.php';
                return;
            }
        }

        setFlash('error', 'La categoría no existe.');
        redirigir(urlAccion('categoria', 'formcategoria'));
    }

    public function guardarCategoria() {
        exigirSesion();
        $urlFormulario = urlAccion('categoria', 'fromcrearCategoria');
        exigirPost($urlFormulario);

        list($datos, $errores) = $this->validarDatos(null);
        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        if ($this->categoriaModel->createCategoria($datos['nombre'], $datos['descripcion'])) {
            setFlash('exito', 'Categoría «' . $datos['nombre'] . '» creada correctamente.');
            redirigir(urlAccion('categoria', 'formcategoria'));
        }

        setFlash('error', 'No se pudo guardar la categoría. Inténtalo de nuevo.');
        redirigir($urlFormulario);
    }

    public function actualizarCategoria() {
        exigirSesion();
        exigirPost(urlAccion('categoria', 'formcategoria'));

        $id = validarEntero($_POST['id'] ?? null, 1);
        if ($id === null || !$this->categoriaModel->getById($id)) {
            setFlash('error', 'La categoría que intentas editar no existe.');
            redirigir(urlAccion('categoria', 'formcategoria'));
        }

        $urlFormulario = urlAccion('categoria', 'fromeditarCategoria', ['id' => $id]);

        list($datos, $errores) = $this->validarDatos($id);
        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        if ($this->categoriaModel->updateCategoria($id, $datos['nombre'], $datos['descripcion'])) {
            setFlash('exito', 'Categoría «' . $datos['nombre'] . '» actualizada correctamente.');
            redirigir(urlAccion('categoria', 'formcategoria'));
        }

        setFlash('error', 'No se pudo actualizar la categoría. Inténtalo de nuevo.');
        redirigir($urlFormulario);
    }

    /**
     * Validación común de crear y editar.
     * $idExcluir: al editar, la propia categoría no cuenta como nombre repetido.
     * Devuelve [datos limpios, lista de errores].
     */
    private function validarDatos(?int $idExcluir): array {
        $errores = [];

        // Columna nombre: VARCHAR(50) NOT NULL
        $nombre = validarNombreComercial($_POST['nombre'] ?? null, 2, 50);
        if ($nombre === null) {
            $errores[] = 'El nombre es obligatorio: de 2 a 50 caracteres, empezando con letra o número (no se permite < ni >).';
        }

        // Columna descripcion: VARCHAR(255) NULL (opcional)
        $desc = opcional($_POST['descripcion'] ?? '', function ($v) {
            return validarTextoLibre($v, 1, 255);
        });
        if (!$desc['ok']) {
            $errores[] = 'La descripción no es válida: máximo 255 caracteres y sin < ni >.';
        }

        // La BD no tiene UNIQUE en este campo, así que sin esta revisión se acumularían duplicados.
        if ($nombre !== null && $this->categoriaModel->existeNombre($nombre, $idExcluir)) {
            $errores[] = 'Ya existe una categoría con ese nombre.';
        }

        return [['nombre' => $nombre, 'descripcion' => $desc['valor']], $errores];
    }

    public function eliminarCategoria() {
        exigirSesion();
        
        $id = validarEntero($_GET['id'] ?? null, 1);
        if ($id === null || !$this->categoriaModel->getById($id)) {
            setFlash('error', 'La categoría no existe o ya fue procesada.');
            redirigir(urlAccion('categoria', 'formcategoria'));
        }

        // Ejecutamos el borrado lógico
        if ($this->categoriaModel->inactivarCategoria($id)) {
            setFlash('exito', 'Categoría inactivada correctamente. Se conservará en el historial de Kardex.');
        } else {
            setFlash('error', 'Error al intentar inactivar la categoría.');
        }
        
        redirigir(urlAccion('categoria', 'formcategoria'));
    }
}
?>
