<?php
/**
 * app/controller/PrecioController.php
 *
 * Controlador del módulo de Control de Precios.
 * URL base: index.php?controller=precio&action=...
 *
 * Acciones públicas (las que el router puede llamar):
 *   tablaPrecios        listado, resumen y filtros          (cualquier usuario con sesión)
 *   historial           historial de cambios de precio      (cualquier usuario con sesión)
 *   formEditar          formulario de un producto           (solo roles editores)
 *   guardarPrecio       POST: guarda costo y precio         (solo roles editores)
 *   formMasivo          formulario de ajuste por porcentaje (solo roles editores)
 *   previsualizarMasivo POST: muestra el efecto sin guardar (solo roles editores)
 *   aplicarMasivo       POST: aplica el ajuste              (solo roles editores)
 *
 * El nombre del archivo lleva la P mayúscula a propósito: el router arma
 * ucfirst('precio') . 'Controller' = 'PrecioController', y así también
 * funciona en servidores Linux (que distinguen mayúsculas en los archivos).
 */

require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/formato.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/precioModel.php';
require_once __DIR__ . '/../model/categoriaModel.php';

class PrecioController
{
    /**
     * Roles que pueden CAMBIAR precios. Ver precios y el historial lo puede
     * hacer cualquier usuario con sesión. Los valores deben coincidir con lo
     * que guarda la columna usuarios.rol ('admin' es el usuario semilla;
     * 'Administrador' es lo que guarda el formulario de usuarios).
     */
    private const ROLES_EDITAR = ['admin', 'Administra'];

    /** IVA solo para mostrar el precio final referencial (mismo valor fijo que usa movimientoController). */
    private const TASA_IVA = 16.00;

    private const FILAS_POR_PAGINA = 15;

    /** Opciones del filtro "Situación" del listado. */
    private const ESTADOS = [
        'todos'       => 'Todas las situaciones',
        'sin_precio'  => 'Sin precio de venta',
        'sin_costo'   => 'Sin costo',
        'bajo_costo'  => 'Vendiendo bajo el costo',
        'margen_bajo' => 'Margen bajo',
        'ok'          => 'Margen saludable',
    ];

    private $precioModel;
    private $categoriaModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $this->precioModel    = new PrecioModel($pdo);
        $this->categoriaModel = new CategoriaModel($pdo);
    }

    // =====================================================================
    // LISTADO
    // =====================================================================

    public function tablaPrecios(): void
    {
        exigirSesion();
        evitarCache();

        $filtros  = $this->leerFiltrosListado();
        $catalogo = $this->precioModel->obtenerCatalogo();

        $resumen    = $this->precioModel->resumir($catalogo);          // siempre sobre TODOS los activos
        $productos  = $this->precioModel->filtrar($catalogo, $filtros);
        $categorias = $this->categoriaModel->getAll();
        $estados    = self::ESTADOS;
        $puedeEditar = tieneRol(self::ROLES_EDITAR);
        $margenMinimo = PrecioModel::MARGEN_MINIMO;

        require __DIR__ . '/../views/precios/precios.php';
    }

    // =====================================================================
    // EDITAR PRECIO DE UN PRODUCTO
    // =====================================================================

    public function formEditar(): void
    {
        $this->exigirEditor();
        evitarCache();

        $id       = validarEntero($_GET['id'] ?? null, 1);
        $producto = ($id !== null) ? $this->precioModel->obtenerProducto($id) : null;

        if ($producto === null) {
            setFlash('error', 'El producto no existe o está inactivo.');
            redirigir(urlAccion('precio', 'tablaPrecios'));
        }

        // Si el servidor rechazó un envío anterior, se recuperan los valores escritos.
        $previo = recuperarFormulario();
        if ((int) ($previo['id_producto'] ?? 0) !== $id) {
            $previo = [];
        }

        $recientes = $this->precioModel->historial(['id_producto' => $id], 5, 0);
        $tasaIva   = self::TASA_IVA;

        require __DIR__ . '/../views/precios/editarPrecio.php';
    }

    public function guardarPrecio(): void
    {
        $this->exigirEditor();

        $id = validarEntero($_POST['id_producto'] ?? null, 1);
        $urlFormulario = ($id !== null)
            ? urlAccion('precio', 'formEditar', ['id' => $id])
            : urlAccion('precio', 'tablaPrecios');

        // Exige POST + token CSRF; si falla, avisa y redirige solo.
        exigirPostConCsrf($urlFormulario);

        if ($id === null) {
            setFlash('error', 'Solicitud no válida.');
            redirigir(urlAccion('precio', 'tablaPrecios'));
        }

        // --- Validación del lado del servidor (el HTML solo es comodidad) ---
        $errores = [];

        $costo = validarDecimal($_POST['precio_compra'] ?? null, 8, 2, true);   // el costo puede ser 0
        $venta = validarDecimal($_POST['precio_venta'] ?? null, 8, 2, false);   // la venta debe ser > 0
        $costoEsperado = validarDecimal($_POST['precio_compra_actual'] ?? null, 8, 2, true);
        $ventaEsperado = validarDecimal($_POST['precio_venta_actual'] ?? null, 8, 2, true);

        if ($costoEsperado === null || $ventaEsperado === null) {
            setFlash('error', 'Solicitud no válida. Vuelve a abrir el formulario.');
            redirigir($urlFormulario);
        }
        if ($costo === null) {
            $errores[] = 'El costo debe ser un número válido (usa punto decimal, máximo 2 decimales). Puede ser 0.';
        }
        if ($venta === null) {
            $errores[] = 'El precio de venta debe ser un número mayor que 0 (usa punto decimal, máximo 2 decimales).';
        }

        $confirmaPerdida = (($_POST['confirmar_perdida'] ?? '') === '1');
        if (empty($errores) && (float) $costo > 0 && (float) $venta < (float) $costo && !$confirmaPerdida) {
            $errores[] = 'El precio de venta (' . dinero($venta) . ') es menor que el costo (' . dinero($costo)
                       . '). Si es intencional, marca la casilla de confirmación.';
        }

        if (!empty($errores)) {
            guardarFormulario($_POST);
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        $producto = $this->precioModel->obtenerProducto($id);
        if ($producto === null) {
            setFlash('error', 'El producto no existe o está inactivo.');
            redirigir(urlAccion('precio', 'tablaPrecios'));
        }

        // El id del usuario SIEMPRE sale de la sesión, nunca del formulario.
        $resultado = $this->precioModel->actualizarPrecio(
            $id, $costo, $venta, $costoEsperado, $ventaEsperado, usuarioActualId()
        );

        switch ($resultado) {
            case 'ok':
                renovarCsrf();   // un reenvío (F5) ya no tendrá token válido
                setFlash('exito', 'Precios de «' . $producto['nombre'] . '» actualizados: costo '
                    . dinero($costo) . ' · venta ' . dinero($venta) . '.');
                redirigir(urlAccion('precio', 'tablaPrecios'));
                break;

            case 'sin_cambios':
                setFlash('aviso', 'No hay cambios: los valores son iguales a los actuales.');
                redirigir(urlAccion('precio', 'tablaPrecios'));
                break;

            case 'conflicto':
                setFlash('error', 'Otro usuario modificó los precios de este producto mientras editabas. '
                    . 'Se cargaron los valores vigentes; revisa y vuelve a intentarlo.');
                redirigir($urlFormulario);
                break;

            case 'no_existe':
                setFlash('error', 'El producto no existe o está inactivo.');
                redirigir(urlAccion('precio', 'tablaPrecios'));
                break;

            default:
                guardarFormulario($_POST);
                setFlash('error', 'No se pudo guardar el cambio. Inténtalo de nuevo.');
                redirigir($urlFormulario);
        }
    }

    // =====================================================================
    // HISTORIAL
    // =====================================================================

    public function historial(): void
    {
        exigirSesion();
        evitarCache();

        $filtros = $this->leerFiltrosHistorial();

        $total         = $this->precioModel->contarHistorial($filtros);
        $totalPaginas  = max(1, (int) ceil($total / self::FILAS_POR_PAGINA));
        $pagina        = validarEntero($_GET['pagina'] ?? null, 1, 100000) ?? 1;
        $pagina        = min($pagina, $totalPaginas);

        $registros = $this->precioModel->historial(
            $filtros,
            self::FILAS_POR_PAGINA,
            ($pagina - 1) * self::FILAS_POR_PAGINA
        );

        $catalogo = $this->precioModel->obtenerCatalogo();   // para el <select> de productos

        // Filtros activos como parámetros de URL (para los enlaces de paginación).
        $filtrosUrl = array_filter($filtros, function ($v) {
            return $v !== null && $v !== '';
        });

        require __DIR__ . '/../views/precios/historial.php';
    }

    // =====================================================================
    // AJUSTE MASIVO POR PORCENTAJE
    // =====================================================================

    public function formMasivo(): void
    {
        $this->exigirEditor();
        evitarCache();

        $this->mostrarMasivo(['id_categoria' => '', 'tipo' => 'aumento', 'porcentaje' => ''], null);
    }

    public function previsualizarMasivo(): void
    {
        $this->exigirEditor();
        $urlFormulario = urlAccion('precio', 'formMasivo');
        exigirPostConCsrf($urlFormulario);

        list($entrada, $errores) = $this->leerAjusteMasivo();
        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        $catalogo = $this->precioModel->obtenerCatalogo();
        $filas    = $this->precioModel->filtrar($catalogo, ['id_categoria' => $entrada['id_categoria']]);
        $preview  = $this->precioModel->simularAjusteMasivo($filas, $entrada['tipo'], (float) $entrada['porcentaje']);

        $this->mostrarMasivo($entrada, $preview);
    }

    public function aplicarMasivo(): void
    {
        $this->exigirEditor();
        $urlFormulario = urlAccion('precio', 'formMasivo');
        exigirPostConCsrf($urlFormulario);

        list($entrada, $errores) = $this->leerAjusteMasivo();
        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        $resultado = $this->precioModel->aplicarAjusteMasivo(
            $entrada['id_categoria'],
            $entrada['tipo'],
            (float) $entrada['porcentaje'],
            usuarioActualId()
        );

        if ($resultado['estado'] !== 'ok') {
            setFlash('error', 'No se pudo aplicar el ajuste. No se modificó ningún precio.');
            redirigir($urlFormulario);
        }

        renovarCsrf();
        if ($resultado['actualizados'] === 0) {
            setFlash('aviso', 'Ningún producto cambió de precio (todos fueron omitidos).');
        } else {
            setFlash('exito', 'Ajuste aplicado: ' . $resultado['actualizados'] . ' producto(s) actualizado(s)'
                . ($resultado['omitidos'] > 0 ? ' y ' . $resultado['omitidos'] . ' omitido(s) (sin precio de venta o sin variación).' : '.'));
        }
        redirigir(urlAccion('precio', 'tablaPrecios'));
    }

    // =====================================================================
    // PRIVADOS
    // =====================================================================

    /** Exige sesión y rol editor. Si no cumple, vuelve al listado con un aviso. */
    private function exigirEditor(): void
    {
        exigirSesion();
        if (!tieneRol(self::ROLES_EDITAR)) {
            error_log('Acceso denegado a edición de precios: usuario ' . usuarioActualId());
            setFlash('error', 'Solo los administradores pueden modificar precios.');
            redirigir(urlAccion('precio', 'tablaPrecios'));
        }
    }

    private function mostrarMasivo(array $entrada, ?array $preview): void
    {
        $categorias = $this->categoriaModel->getAll();
        require __DIR__ . '/../views/precios/ajusteMasivo.php';
    }

    /** Filtros del listado desde la URL. Todo valor inválido se ignora. */
    private function leerFiltrosListado(): array
    {
        return [
            'q'            => isset($_GET['q']) ? validarTexto($_GET['q'], 1, 100) : null,
            'id_categoria' => isset($_GET['id_categoria']) ? validarEntero($_GET['id_categoria'], 1) : null,
            'estado'       => validarClave($_GET['estado'] ?? 'todos', self::ESTADOS) ?? 'todos',
        ];
    }

    /** Filtros del historial desde la URL: producto y rango de fechas. */
    private function leerFiltrosHistorial(): array
    {
        $idProducto = isset($_GET['id_producto']) ? validarEntero($_GET['id_producto'], 1) : null;

        // Las fechas pueden ser pasadas (hasta 50 años) pero no futuras.
        $desde = (isset($_GET['desde']) && $_GET['desde'] !== '') ? validarFecha($_GET['desde'], false, 50) : null;
        $hasta = (isset($_GET['hasta']) && $_GET['hasta'] !== '') ? validarFecha($_GET['hasta'], false, 50) : null;

        if ((isset($_GET['desde']) && $_GET['desde'] !== '' && $desde === null)
            || (isset($_GET['hasta']) && $_GET['hasta'] !== '' && $hasta === null)) {
            setFlash('aviso', 'Se ignoró una fecha no válida (no puede ser futura).');
        }

        // Si vienen al revés, se intercambian.
        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            $tmp = $desde;
            $desde = $hasta;
            $hasta = $tmp;
        }

        return ['id_producto' => $idProducto, 'desde' => $desde, 'hasta' => $hasta];
    }

    /**
     * Lee y valida el formulario de ajuste masivo (previsualizar y aplicar
     * usan exactamente la misma validación).
     * Devuelve [$entrada, $errores].
     */
    private function leerAjusteMasivo(): array
    {
        $errores = [];

        $tipo = validarClave($_POST['tipo'] ?? null, ['aumento' => true, 'descuento' => true]);
        if ($tipo === null) {
            $errores[] = 'Elige si el ajuste es un aumento o un descuento.';
        }

        $porcentaje = validarDecimal($_POST['porcentaje'] ?? null, 3, 2, false);
        if ($porcentaje === null || (float) $porcentaje > 100) {
            $errores[] = 'El porcentaje debe ser mayor que 0 y no superar 100 (máximo 2 decimales).';
        } elseif ($tipo === 'descuento' && (float) $porcentaje >= 100) {
            $errores[] = 'Un descuento debe ser menor que 100 %.';
        }

        $idCategoria = null;
        $crudo = $_POST['id_categoria'] ?? '';
        if ($crudo !== '') {
            $idCategoria = validarEntero($crudo, 1);
            if ($idCategoria === null) {
                $errores[] = 'La categoría elegida no es válida.';
            }
        }

        $entrada = [
            'id_categoria' => $idCategoria,
            'tipo'         => $tipo,
            'porcentaje'   => $porcentaje,
        ];
        return [$entrada, $errores];
    }
}
