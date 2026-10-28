<?php
/**
 * app/controller/VentaController.php
 *
 * Controlador del módulo de Ventas (facturas).
 * URL base: index.php?controller=venta&action=...
 *
 * Acciones públicas (las que el router puede llamar):
 *   tablaVentas       listado, indicadores y filtros          (cualquier usuario con sesión)
 *   verDetalleVenta   factura, imprimible                      (cualquier usuario con sesión)
 *   crearVenta        formulario de nueva venta               (solo ROLES_VENDER)
 *   guardarVenta      POST: registra la venta y baja stock    (solo ROLES_VENDER)
 *   anularVenta       POST: anula y devuelve el stock         (solo ROLES_ANULAR)
 *
 * El nombre del archivo lleva la V mayúscula a propósito: el router arma
 * ucfirst('venta') . 'Controller' = 'VentaController', y así también funciona
 * en servidores Linux (que distinguen mayúsculas en los nombres de archivo).
 *
 * Reglas de oro (las mismas de Compras):
 *  - El precio, el IVA y el id del usuario NUNCA vienen del formulario: salen de la BD y de la sesión.
 *  - Los montos los calcula el servidor; lo que muestra el JavaScript es solo una vista previa.
 *  - Las ventas no se editan ni se borran: se anulan.
 */

require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../helpers/formato.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';
require_once __DIR__ . '/../model/ventaModel.php';
require_once __DIR__ . '/../model/clienteModel.php';
require_once __DIR__ . '/../model/productoModel.php';

class VentaController
{
    /**
     * Roles (en MINÚSCULAS). El front controller (public/index.php) normaliza el rol de la
     * sesión a minúsculas en cada petición ('Operador' -> 'operador', 'Administrador' -> 'admin'),
     * así que aquí se compara contra esos mismos valores. El rol 'usuario' solo consulta.
     * Estas guardias repiten las de la tabla de rutas a propósito (defensa en profundidad).
     */
    private const ROLES_VENDER = ['admin', 'operador'];

    /** Roles que pueden ANULAR una venta (devuelve stock): solo administración. */
    private const ROLES_ANULAR = ['admin'];

    /**
     * NO es un límite de negocio: una venta puede llevar tantos productos como existan (la BD
     * admite un producto una sola vez por venta). Es solo un tope técnico para que nadie envíe
     * miles de filas falsas al servidor. Ojo: PHP también limita los campos de un envío con
     * max_input_vars (1000 por defecto = unas 490 líneas); si algún día necesitas más, sube ese valor.
     */
    private const MAX_LINEAS_SEGURIDAD = 500;

    private const FILAS_POR_PAGINA = 15;

    private $ventaModel;
    private $clienteModel;
    private $productoModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $this->ventaModel    = new VentaModel($pdo);
        $this->clienteModel  = new clienteModel($pdo);
        $this->productoModel = new productoModel($pdo);
    }

    // =====================================================================
    // LISTADO
    // =====================================================================

    public function tablaVentas(): void
    {
        exigirSesion();
        evitarCache();

        $filtros = $this->leerFiltrosListado();

        $total        = $this->ventaModel->contarVentas($filtros);
        $totalPaginas = max(1, (int) ceil($total / self::FILAS_POR_PAGINA));
        $pagina       = validarEntero($_GET['pagina'] ?? null, 1, 100000) ?? 1;
        $pagina       = min($pagina, $totalPaginas);

        $ventas = $this->ventaModel->obtenerVentas(
            $filtros,
            self::FILAS_POR_PAGINA,
            ($pagina - 1) * self::FILAS_POR_PAGINA
        );

        // Número de operación legible, igual que 'COMP-000015' en Compras.
        foreach ($ventas as &$venta) {
            $venta['numero_operacion'] = numeroOperacion('VEN', $venta['id_venta']);
        }
        unset($venta);

        $resumen     = $this->ventaModel->resumen();
        $puedeVender = tieneRol(self::ROLES_VENDER);

        // Filtros activos como parámetros de URL (para los enlaces de paginación).
        $filtrosUrl = array_filter($filtros, function ($v) {
            return $v !== null && $v !== '';
        });

        require __DIR__ . '/../views/ventas/ventas.php';
    }

    // =====================================================================
    // NOTA DE ENTREGA (detalle de una venta)
    // =====================================================================

    public function verDetalleVenta(): void
    {
        exigirSesion();
        evitarCache();

        $id    = validarEntero($_GET['id'] ?? null, 1);
        $venta = ($id !== null) ? $this->ventaModel->obtenerVenta($id) : null;

        if ($venta === null) {
            setFlash('error', 'La venta que buscas no existe.');
            redirigir(urlAccion('venta', 'tablaVentas'));
        }

        $detalles        = $this->ventaModel->obtenerDetalle($id);
        $pagos           = $this->ventaModel->obtenerPagos($id);
        $numeroOperacion = numeroOperacion('VEN', $venta['id_venta']);
        // Ventas anteriores a las facturas no tienen número guardado: se deriva igual que al crearlas.
        $numeroFactura   = !empty($venta['numero_factura']) ? $venta['numero_factura'] : numeroOperacion('FAC', $venta['id_venta']);
        $puedeAnular     = tieneRol(self::ROLES_ANULAR) && (int) $venta['estado'] === 1;
        // ?imprimir=1 (botón "Registrar e imprimir"): abre el diálogo de impresión al cargar. Solo si no está anulada.
        $autoImprimir    = (($_GET['imprimir'] ?? '') === '1') && (int) $venta['estado'] === 1;

        require __DIR__ . '/../views/ventas/detalleVenta.php';
    }

    // =====================================================================
    // FORMULARIO DE NUEVA VENTA
    // =====================================================================

    public function crearVenta(): void
    {
        exigirRol(self::ROLES_VENDER);
        evitarCache();

        $clientes  = $this->clienteModel->getAll();   // solo activos, por nombre
        $productos = $this->productoModel->obtenerProductos();   // solo activos

        // Catálogo para el buscador del formulario (se filtra en el navegador, sin recargar).
        // Los productos agotados o sin precio van incluidos: el buscador los muestra, pero no deja agregarlos.
        $catalogo = array_map(function ($p) {
            return [
                'id'        => (int) $p['id_producto'],
                'codigo'    => $p['codigo'],
                'nombre'    => $p['nombre'],
                'categoria' => $p['nombre_categoria'],
                'precio'    => numeroInput($p['precio_venta']),
                'stock'     => (int) $p['stock_actual'],
                'iva'       => (int) $p['aplica_iva'],
            ];
        }, $productos);

        // Lo que el usuario escribió si el servidor rechazó el envío anterior (se lee UNA vez).
        $previo   = recuperarFormulario();
        $tasaIva  = (float) TASA_IVA;

        require __DIR__ . '/../views/ventas/crearVenta.php';
    }

    // =====================================================================
    // GUARDAR UNA VENTA
    // =====================================================================

    public function guardarVenta(): void
    {
        // Sesión + rol, luego método POST + token CSRF. Si algo falla, redirige solo.
        exigirRol(self::ROLES_VENDER);
        $urlFormulario = urlAccion('venta', 'crearVenta');
        exigirPostConCsrf($urlFormulario);

        $errores = [];

        // ---------------------------------------------------------------
        // CABECERA: cliente y observaciones
        // ---------------------------------------------------------------
        $idCliente = validarEntero($_POST['id_cliente'] ?? null, 1);
        if ($idCliente === null) {
            $errores[] = 'Selecciona un cliente.';
        } else {
            $cliente = $this->clienteModel->getById($idCliente);
            if (!$cliente || (int) $cliente['estado'] !== 1) {
                $errores[] = 'El cliente seleccionado no existe o está inactivo.';
                $idCliente = null;
            }
        }

        $observaciones = opcional($_POST['observaciones'] ?? '', function ($v) {
            return validarTextoLibre($v, 1, 255);
        });
        if (!$observaciones['ok']) {
            $errores[] = 'Las observaciones no pueden superar 255 caracteres ni contener < o >.';
        }

        // ---------------------------------------------------------------
        // LÍNEAS: producto + cantidad. El precio NO se lee del formulario.
        // ---------------------------------------------------------------
        $idsProducto = postArray('id_producto');
        $cantidades  = postArray('cantidad');

        $detalles = [];
        $vistos   = [];   // id_producto => número de fila donde apareció

        $totalFilas = max(count($idsProducto), count($cantidades));
        if ($totalFilas > self::MAX_LINEAS_SEGURIDAD) {
            $errores[]  = 'La venta tiene demasiadas líneas. Divídela en dos facturas.';
            $totalFilas = 0;   // no se procesa nada más de las líneas
        }

        for ($i = 0; $i < $totalFilas; $i++) {
            $n = $i + 1;

            // Un valor que no sea texto (un atacante puede enviar arreglos) se trata como vacío.
            $crudoProducto = (isset($idsProducto[$i]) && is_string($idsProducto[$i])) ? trim($idsProducto[$i]) : '';
            $crudoCantidad = (isset($cantidades[$i]) && is_string($cantidades[$i])) ? trim($cantidades[$i]) : '';

            // Fila vacía: se ignora.
            if ($crudoProducto === '' && $crudoCantidad === '') {
                continue;
            }
            // Fila a medias: error.
            if ($crudoProducto === '' || $crudoCantidad === '') {
                $errores[] = "Fila $n: elige el producto y escribe la cantidad (o deja la fila vacía).";
                continue;
            }

            $idProducto = validarEntero($crudoProducto, 1);
            if ($idProducto === null) {
                $errores[] = "Fila $n: el producto seleccionado no es válido.";
                continue;
            }

            $cantidad = validarEntero($crudoCantidad, 1, 100000);
            if ($cantidad === null) {
                $errores[] = "Fila $n: la cantidad debe ser un número entero entre 1 y 100000.";
                continue;
            }

            // La BD tiene UNIQUE (id_venta, id_producto): un producto va en una sola fila.
            if (isset($vistos[$idProducto])) {
                $errores[] = "Fila $n: ese producto ya está en la fila {$vistos[$idProducto]}. Suma las cantidades en una sola fila.";
                continue;
            }
            $vistos[$idProducto] = $n;

            $producto = $this->productoModel->obtenerProductoPorId($idProducto);
            if (!$producto || (int) $producto['estado'] !== 1) {
                $errores[] = "Fila $n: el producto no existe o está inactivo.";
                continue;
            }

            $precio = (float) $producto['precio_venta'];
            if ($precio <= 0) {
                $errores[] = "Fila $n: «{$producto['nombre']}» no tiene precio de venta. Defínelo en Control de Precios y vuelve a intentarlo.";
                continue;
            }

            $stock = (int) $producto['stock_actual'];
            if ($stock < $cantidad) {
                $errores[] = "Fila $n: no hay stock suficiente de «{$producto['nombre']}» (disponible: $stock, pedido: $cantidad).";
                continue;
            }

            $precioTexto = number_format($precio, 2, '.', '');

            $detalles[] = [
                'id_producto'     => $idProducto,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioTexto,
                'aplica_iva'      => (int) $producto['aplica_iva'],
                'subtotal_linea'  => subtotalLinea($cantidad, $precioTexto),
            ];
        }

        if (empty($detalles) && empty($errores)) {
            $errores[] = 'Agrega al menos un producto a la venta.';
        }

        // ---------------------------------------------------------------
        // FORMA DE PAGO (necesita el total, por eso va después de las líneas)
        // ---------------------------------------------------------------
        $totales = !empty($detalles) ? calcularTotalesDocumento($detalles, (float) TASA_IVA) : null;

        $tipoPago = validarClave($_POST['tipo_pago'] ?? null, TIPOS_PAGO_VENTA);
        $pagos    = $this->armarPagos($tipoPago, $totales['total'] ?? null, $errores);

        // ---------------------------------------------------------------
        // Si algo falló: se devuelve al formulario SIN perder lo escrito.
        // ---------------------------------------------------------------
        if (!empty($errores)) {
            guardarFormulario($_POST);
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // ---------------------------------------------------------------
        // GUARDADO (los totales ya los calculó el servidor más arriba)
        // ---------------------------------------------------------------
        $cabecera = [
            'id_cliente'    => $idCliente,
            'subtotal'      => $totales['subtotal'],
            'tasa_iva'      => number_format((float) TASA_IVA, 2, '.', ''),
            'monto_iva'     => $totales['monto_iva'],
            'total'         => $totales['total'],
            'tipo_pago'     => $tipoPago,
            'observaciones' => $observaciones['valor'],
            'id_usuario'    => usuarioActualId(),   // de la sesión, nunca del formulario
        ];

        $resultado = $this->ventaModel->registrarVentaCompleta($cabecera, $detalles, $pagos);

        if ($resultado['ok'] === true) {
            // Token nuevo: un reenvío del mismo formulario (F5, doble clic) ya no será válido.
            renovarCsrf();
            setFlash(
                'exito',
                'Factura ' . $resultado['numero_factura'] . ' registrada. El stock ya fue descontado y '
                . 'la salida quedó anotada en el kardex.'
            );
            // Cada venta genera su factura: se abre de una vez. Con "Registrar e imprimir" se lanza la impresión.
            $parametros = ['id' => $resultado['id']];
            if (($_POST['accion'] ?? '') === 'imprimir') {
                $parametros['imprimir'] = 1;
            }
            redirigir(urlAccion('venta', 'verDetalleVenta', $parametros));
        }

        setFlash('error', $this->mensajeErrorRegistro($resultado));
        guardarFormulario($_POST);
        redirigir($urlFormulario);
    }

    // =====================================================================
    // ANULAR UNA VENTA
    // =====================================================================

    public function anularVenta(): void
    {
        exigirRol(self::ROLES_ANULAR);

        $urlListado = urlAccion('venta', 'tablaVentas');
        $id         = validarEntero($_POST['id_venta'] ?? null, 1);
        $urlDetalle = ($id !== null) ? urlAccion('venta', 'verDetalleVenta', ['id' => $id]) : $urlListado;

        exigirPostConCsrf($urlDetalle);

        if ($id === null) {
            setFlash('error', 'La venta que intentas anular no es válida.');
            redirigir($urlListado);
        }

        $motivo = validarTextoLibre($_POST['motivo_anulacion'] ?? null, 5, 255);
        if ($motivo === null) {
            setFlash('error', 'Escribe el motivo de la anulación (de 5 a 255 caracteres, sin < ni >).');
            redirigir($urlDetalle);
        }

        $resultado = $this->ventaModel->anularVenta($id, usuarioActualId(), $motivo);

        if ($resultado['ok'] === true) {
            renovarCsrf();
            setFlash(
                'exito',
                'La factura ' . numeroOperacion('FAC', $id) . ' fue anulada y el stock volvió al inventario.'
            );
            redirigir($urlDetalle);
        }

        switch ($resultado['motivo']) {
            case 'no_existe':
                setFlash('error', 'La venta que intentas anular no existe.');
                redirigir($urlListado);
                break;
            case 'ya_anulada':
                setFlash('aviso', 'Esa venta ya estaba anulada; no se hizo ningún cambio.');
                break;
            case 'ocupado':
                setFlash('aviso', 'El sistema estaba procesando otra operación sobre estos productos. Inténtalo de nuevo.');
                break;
            default:
                setFlash('error', 'No se pudo anular la venta por un error en la base de datos. Inténtalo de nuevo.');
        }
        redirigir($urlDetalle);
    }

    // =====================================================================
    // UTILIDADES PRIVADAS
    // =====================================================================

    /** Traduce el código que devuelve el modelo a un mensaje que el usuario sepa resolver. */
    private function mensajeErrorRegistro(array $resultado): string
    {
        switch ($resultado['motivo'] ?? 'error') {
            case 'stock_insuficiente':
                return 'Mientras registrabas la venta, el stock de «' . $resultado['producto']
                    . '» cambió: ahora solo hay ' . $resultado['disponible'] . ' unidades. Ajusta la cantidad.';
            case 'conflicto':
                return 'El precio o el IVA de «' . $resultado['producto']
                    . '» cambió mientras armabas la venta. Revisa los montos y vuelve a procesarla.';
            case 'pagos_invalidos':
                return 'Los pagos no suman el total de la venta. Revisa la forma de pago.';
            case 'producto_invalido':
                return 'Uno de los productos ya no está disponible. Actualiza el formulario e inténtalo de nuevo.';
            case 'ocupado':
                return 'El sistema estaba procesando otra venta con los mismos productos. Inténtalo de nuevo.';
            default:
                return 'Error en la base de datos: la venta no se guardó. Inténtalo de nuevo.';
        }
    }

    /**
     * Convierte la forma de pago elegida en la lista de pagos que se guardará.
     *  - Una sola forma de pago: un pago por el total.
     *  - Pago mixto: lee pago_efectivo, pago_tarjeta... del formulario; deben ser al menos dos
     *    y sumar EXACTAMENTE el total (en pagos mixtos no hay vuelto).
     * Agrega al arreglo $errores lo que esté mal y devuelve la lista (vacía si hay errores).
     */
    private function armarPagos(?string $tipoPago, ?string $total, array &$errores): array
    {
        if ($tipoPago === null) {
            $errores[] = 'Selecciona la forma de pago.';
            return [];
        }
        if ($total === null) {
            return [];   // no hay líneas válidas: ese error ya está anotado
        }
        if ($tipoPago !== 'mixto') {
            return [['metodo' => $tipoPago, 'monto' => $total]];
        }

        $pagos    = [];
        $suma     = 0;
        $montoMal = false;

        foreach (METODOS_PAGO as $clave => $etiqueta) {
            $crudo = $_POST['pago_' . $clave] ?? '';
            $crudo = is_string($crudo) ? trim($crudo) : null;   // un arreglo no es un monto
            if ($crudo === '') {
                continue;   // campo vacío = esa forma de pago no se usó
            }
            $monto = validarDecimal($crudo, 10, 2, true);
            if ($monto === null) {
                $errores[] = "Pago mixto: el monto de $etiqueta no es válido.";
                $montoMal  = true;
                continue;
            }
            if (aCentavos($monto) === 0) {
                continue;
            }
            $pagos[] = ['metodo' => $clave, 'monto' => $monto];
            $suma   += aCentavos($monto);
        }

        if ($montoMal) {
            return [];
        }
        if (count($pagos) < 2) {
            $errores[] = 'El pago mixto necesita al menos dos formas de pago con monto. Si pagó con una sola, elige esa forma de pago.';
            return [];
        }
        if ($suma !== aCentavos($total)) {
            $dif = abs(aCentavos($total) - $suma);
            $errores[] = 'En el pago mixto no hay vuelto: la suma debe ser exactamente ' . dinero($total)
                . ' (' . ($suma < aCentavos($total) ? 'faltan ' : 'sobran ') . dinero($dif / 100) . ').';
            return [];
        }

        return $pagos;
    }

    /** Filtros del listado desde la URL: texto, rango de fechas y estado. */
    private function leerFiltrosListado(): array
    {
        $q = validarTexto($_GET['q'] ?? null, 1, 60) ?? '';

        // Las fechas pueden ser pasadas (hasta 50 años) pero no futuras.
        $desdeCrudo = $_GET['desde'] ?? '';
        $hastaCrudo = $_GET['hasta'] ?? '';
        $desde = ($desdeCrudo !== '') ? validarFecha($desdeCrudo, false, 50) : null;
        $hasta = ($hastaCrudo !== '') ? validarFecha($hastaCrudo, false, 50) : null;

        if (($desdeCrudo !== '' && $desde === null) || ($hastaCrudo !== '' && $hasta === null)) {
            setFlash('aviso', 'Se ignoró una fecha no válida (no puede ser futura).');
        }

        // Si vienen al revés, se intercambian.
        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            $tmp   = $desde;
            $desde = $hasta;
            $hasta = $tmp;
        }

        $estado = validarClave($_GET['estado'] ?? null, ['1' => true, '0' => true]);

        return ['q' => $q, 'desde' => $desde, 'hasta' => $hasta, 'estado' => $estado];
    }
}
