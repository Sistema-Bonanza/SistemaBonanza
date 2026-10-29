<?php
/**
 * public/index.php  -  Front Controller de Sistema Bonanza
 *
 * Es el ÚNICO punto de entrada. Cada petición atraviesa estas "puertas" en
 * orden. Si una falla, la petición se detiene ahí y el controlador NUNCA
 * se ejecuta:
 *
 *   Puerta 1  La ruta existe en la lista blanca             (404)
 *   Puerta 2  El método HTTP es el permitido (GET / POST)   (405)
 *   Puerta 3  En POST, la petición viene de nuestro sitio   (403)
 *   Puerta 4  Hay sesión válida, no expirada                (login)
 *   Puerta 5  El usuario sigue activo y su rol es vigente   (login)
 *   Puerta 6  El rol tiene permiso para esa acción          (403)
 *   Puerta 7  En POST, el token CSRF es válido              (403)
 *   Puerta 8  Recién entonces se ejecuta el controlador
 *
 * Los controladores conservan sus propias guardias (exigirRol, CSRF...).
 * Es a propósito: si alguien cambia este archivo mañana, cada controlador
 * sigue protegido ("defensa en profundidad").
 *
 * Secciones:
 *   1. Archivos estáticos      5. Helpers
 *   2. Configuración y errores 6. Tabla de rutas
 *   3. Cabeceras de seguridad  7. Funciones del router
 *   4. Sesión endurecida       8. Despacho
 */


// ============================================================
// 1. ARCHIVOS ESTÁTICOS (solo con el servidor interno: php -S)
// Bajo Apache/XAMPP no hace nada: Apache sirve el CSS por su cuenta.
// ============================================================
if (php_sapi_name() === 'cli-server') {
    $rutaUrl = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $archivoEstatico = is_string($rutaUrl) ? realpath(__DIR__ . $rutaUrl) : false;
    // Solo archivos que estén DENTRO de public/ y que no sean .php
    if ($archivoEstatico !== false
        && is_file($archivoEstatico)
        && strpos($archivoEstatico, realpath(__DIR__) . DIRECTORY_SEPARATOR) === 0
        && strtolower(pathinfo($archivoEstatico, PATHINFO_EXTENSION)) !== 'php') {
        return false;
    }
}


// ============================================================
// 2. CONFIGURACIÓN Y ERRORES
// ============================================================
const MODO_DESARROLLO = true;    // ponlo en false cuando publiques el sistema
const INACTIVIDAD_MAX = 1800;    // segundos sin actividad antes de cerrar la sesión (30 min)
const SESION_MAX      = 28800;   // duración máxima absoluta de una sesión (8 horas)
const REVALIDAR_CADA  = 60;      // cada cuántos segundos se consulta la BD (0 = en cada petición)

// Conjuntos de roles (siempre en minúsculas)
const ROLES_ADMIN        = ['admin'];
const ROLES_TODOS        = ['admin', 'operador'];             // los que operan el sistema
const ROLES_AUTENTICADOS = ['admin', 'operador', 'usuario'];  // cualquier usuario con login

date_default_timezone_set('America/Caracas');   // ajústala a tu zona horaria

error_reporting(E_ALL);
// En desarrollo ves los errores en pantalla; en producción NO (revelan rutas y
// datos internos) y solo se guardan en el log del servidor.
ini_set('display_errors', MODO_DESARROLLO ? '1' : '0');
ini_set('log_errors', '1');


// ============================================================
// 3. CABECERAS DE SEGURIDAD (antes de cualquier salida)
// ============================================================
$esHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

header_remove('X-Powered-By');                 // no anunciar la versión de PHP
header('X-Frame-Options: DENY');               // nadie puede meter el sistema en un <iframe>
header('X-Content-Type-Options: nosniff');     // el navegador no "adivina" tipos de archivo
header('Referrer-Policy: same-origin');        // no filtra tus URLs a otros sitios
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if ($esHttps) {
    header('Strict-Transport-Security: max-age=31536000');   // solo cuando el sitio use HTTPS
}


// ============================================================
// 4. SESIÓN ENDURECIDA (antes de session_start)
// ============================================================
ini_set('session.use_strict_mode', '1');   // rechaza ids de sesión que el servidor no creó
ini_set('session.use_only_cookies', '1');  // el id nunca viaja en la URL
ini_set('session.use_trans_sid', '0');
session_name('BONANZA_SID');               // no usar el nombre por defecto PHPSESSID
session_set_cookie_params([
    'lifetime' => 0,                       // cookie de sesión: se borra al cerrar el navegador
    'path'     => '/',
    'domain'   => '',
    'secure'   => $esHttps,                // solo por HTTPS cuando el sitio lo use
    'httponly' => true,                    // JavaScript no puede leer la cookie
    'samesite' => 'Strict',                // no se envía desde otros sitios
]);
session_start();


// ============================================================
// 5. HELPERS
// ============================================================
require_once __DIR__ . '/../app/helpers/seguridad.php';
require_once __DIR__ . '/../config/constantes.php';


// ============================================================
// 6. TABLA DE RUTAS (LISTA BLANCA)
//
// Solo lo que aparece aquí se puede ejecutar. Todo en MINÚSCULAS: el router
// compara en minúsculas, así tus enlaces actuales (TablaProveedores,
// tablaClientes...) siguen funcionando.
//
// ruta( método , roles , csrf , json )
//   método : 'GET' o 'POST'
//   roles  : lista de roles permitidos, o null = ruta pública (sin login)
//   csrf   : en POST, ¿el router exige el token? (true por defecto)
//            false = formulario LEGADO que todavía no lleva token
//   json   : true = si falla algo, responde JSON en vez de HTML (para fetch)
//
// 'archivo' es el nombre REAL del archivo (en Linux importa la mayúscula).
// Para añadir un módulo nuevo, agrega un bloque aquí.
//
// ⚠ PERMISOS PROPUESTOS: ajusta ROLES_ADMIN / ROLES_TODOS en cada línea
//   según lo que decidas con tu tutor y con el negocio.
// ============================================================
function ruta(string $metodo, ?array $roles, bool $csrf = true, bool $json = false): array
{
    return ['metodo' => $metodo, 'roles' => $roles, 'csrf' => $csrf, 'json' => $json];
}

$rutas = [

    'sistema' => [
        'archivo'  => 'SistemaController.php',
        'clase'    => 'SistemaController',
        'acciones' => [
            'index'         => ruta('GET',  null),                    // pantalla de login
            'procesarlogin' => ruta('POST', null, false),             // login: aún sin token (mejora pendiente)
            'dashboard'     => ruta('GET',  ROLES_AUTENTICADOS),
            'logout'        => ruta('GET',  ROLES_AUTENTICADOS),      // ideal: pasarlo a POST con token
        ],
    ],

    'usuario' => [
        'archivo'  => 'usuarioController.php',
        'clase'    => 'usuarioController',
        'acciones' => [
            'formusuario'       => ruta('GET',  ROLES_ADMIN),
            'formusuariocrear'  => ruta('GET',  ROLES_ADMIN),
            'formusuarioeditar' => ruta('GET',  ROLES_ADMIN),
            'guardar'           => ruta('POST', ROLES_ADMIN, false),  // LEGADO: sin token
            'editar'            => ruta('POST', ROLES_ADMIN, false),  // LEGADO: sin token
            'eliminar'          => ruta('POST', ROLES_ADMIN),         // LEGADO: enlace GET (vulnerable a CSRF). Convertir a POST + token
        ],
    ],

    'producto' => [
        'archivo'  => 'productoController.php',
        'clase'    => 'productoController',
        'acciones' => [
            'tablaproductos'     => ruta('GET',  ROLES_TODOS),        // consulta de stock: también el operador
            'formproduct'        => ruta('GET',  ROLES_ADMIN),
            'formcategoria'      => ruta('GET',  ROLES_ADMIN),
            'formproveedor'      => ruta('GET',  ROLES_ADMIN),
            'formcrear'          => ruta('GET',  ROLES_ADMIN),
            'formeditar'         => ruta('GET',  ROLES_ADMIN),
            'guardarproducto'    => ruta('POST', ROLES_ADMIN, false), // LEGADO: sin token
            'actualizarproducto' => ruta('POST', ROLES_ADMIN, false), // LEGADO: sin token
            'eliminarproducto'   => ruta('POST', ROLES_ADMIN),        // LEGADO: enlace GET. Convertir a POST + token
        ],
    ],

    'categoria' => [
        'archivo'  => 'categoriaController.php',
        'clase'    => 'CategoriaController',
        'acciones' => [
            'formcategoria'       => ruta('GET',  ROLES_ADMIN),
            'fromcrearcategoria'  => ruta('GET',  ROLES_ADMIN),
            'fromeditarcategoria' => ruta('GET',  ROLES_ADMIN),
            'guardarcategoria'    => ruta('POST', ROLES_ADMIN, false),  // LEGADO: sin token
            'actualizarcategoria' => ruta('POST', ROLES_ADMIN, false),  // LEGADO: sin token
        ],
    ],

    'proveedor' => [
        'archivo'  => 'proveedorController.php',
        'clase'    => 'ProveedorController',
        'acciones' => [
            'tablaproveedores'   => ruta('GET',  ROLES_ADMIN),
            'formcrearproveedor' => ruta('GET',  ROLES_ADMIN),
            'formedit'           => ruta('GET',  ROLES_ADMIN),
            'guardar'            => ruta('POST', ROLES_ADMIN, false), // LEGADO: sin token
            'actualizar'         => ruta('POST', ROLES_ADMIN, false), // LEGADO: sin token
            // La vista proveedor.php enlaza a 'eliminar', pero el controlador no tiene ese método.
        ],
    ],

    'cliente' => [
        'archivo'  => 'clienteController.php',
        'clase'    => 'clienteController',
        'acciones' => [
            'tablaclientes'     => ruta('GET',  ROLES_TODOS),
            'formcrearcliente'  => ruta('GET',  ROLES_TODOS),
            'formeditarcliente' => ruta('GET',  ROLES_TODOS),
            'guardar'           => ruta('POST', ROLES_TODOS, false),  // LEGADO: sin token
            'actualizar'        => ruta('POST', ROLES_TODOS, false),  // LEGADO: sin token
        ],
    ],

    'movimiento' => [
        'archivo'  => 'movimientoController.php',
        'clase'    => 'MovimientoController',
        'acciones' => [
            'tablamovimientos'        => ruta('GET',  ROLES_TODOS),
            'crearcompra'             => ruta('GET',  ROLES_TODOS),
            'guardarcompra'           => ruta('POST', ROLES_TODOS),                 // ya lleva token
            // Decisión pendiente: ¿puede el operador crear presentaciones desde el modal?
            // Si sí, cambia ROLES_ADMIN por ROLES_TODOS.
            'guardarpresentacionajax' => ruta('POST', ROLES_TODOS, true, true),     // fetch: errores en JSON
            'buscarajax' => ruta('GET', ROLES_TODOS, true, true),
            // Descomenta cuando exista el método en el controlador (la vista ya lo enlaza):
            // 'verdetallecompra'     => ruta('GET',  ROLES_TODOS),
        ],
    ],

    'presentacion' => [
        'archivo'  => 'presentacionController.php',
        'clase'    => 'PresentacionController',
        'acciones' => [
            'menuconfig' => ruta('GET',  ROLES_ADMIN),
            'index'      => ruta('GET',  ROLES_ADMIN),
            'crear'      => ruta('GET',  ROLES_ADMIN),
            'guardar'    => ruta('POST', ROLES_ADMIN),
            'desactivar' => ruta('POST', ROLES_ADMIN),
        ],
    ],

    'precio' => [
        'archivo'  => 'PrecioController.php',
        'clase'    => 'PrecioController',
        'acciones' => [
            'tablaprecios'      => ruta('GET',  ROLES_ADMIN),
            'formeditar'        => ruta('GET',  ROLES_ADMIN),
            'guardarprecio'     => ruta('POST', ROLES_ADMIN),
            'historial'         => ruta('GET',  ROLES_ADMIN),
            'formmasivo'        => ruta('GET',  ROLES_ADMIN),
            'previsualizarmasivo' => ruta('POST', ROLES_ADMIN),
            'aplicarmasivo'     => ruta('POST', ROLES_ADMIN),
        ],
    ],

    // Descomenta cuando construyamos el módulo de Ventas:
    // 'venta' => [
    //     'archivo'  => 'ventaController.php',
    //     'clase'    => 'VentaController',
    //     'acciones' => [
    //         'index'   => ruta('GET',  ROLES_TODOS),
    //         'crear'   => ruta('GET',  ROLES_TODOS),
    //         'guardar' => ruta('POST', ROLES_TODOS),
    //         'ver'     => ruta('GET',  ROLES_TODOS),
    //         'anular'  => ruta('POST', ROLES_ADMIN),
    //     ],
    // ],
];


// ============================================================
// 7. FUNCIONES DEL ROUTER
// ============================================================

/** Deja una línea JSON en el log del servidor (el JSON escapa saltos de línea: nadie falsea el log). */
function registrarEvento(string $tipo, string $detalle = ''): void
{
    error_log(json_encode([
        'evento'  => $tipo,
        'usuario' => isset($_SESSION[SESION_ID]) ? (int) $_SESSION[SESION_ID] : 0,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '?',
        'detalle' => substr($detalle, 0, 200),
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
}

/**
 * Responde un error y termina. Siempre un mensaje genérico: el detalle va al log.
 * Con $json = true responde JSON (para las peticiones fetch).
 */
function responderError(int $codigo, string $mensaje, bool $json = false): void
{
    http_response_code($codigo);
    if ($json) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => false, 'error' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error ' . $codigo . '</title></head>'
       . '<body style="font-family:sans-serif;text-align:center;margin-top:12vh">'
       . '<h1>' . $codigo . '</h1><p>' . esc($mensaje) . '</p>'
       . '<p><a href="' . esc(URL_DASHBOARD) . '">Volver al inicio</a></p></body></html>';
    exit;
}

/**
 * Unifica los nombres de rol. Tu BD guarda 'admin' (el usuario semilla) pero
 * también 'Administrador' y 'Operador' (valores del formulario de usuarios);
 * el código compara contra 'admin' y 'operador'.
 */
function normalizarRol($rol): string
{
    $rol = is_string($rol) ? strtolower(trim($rol)) : '';
    return $rol === 'administrador' ? 'admin' : $rol;
}

/** Destruye la sesión por completo y manda al login con un aviso. */
function cerrarSesionYRedirigir(string $mensaje, bool $json = false): void
{
    $p = session_get_cookie_params();
    $_SESSION = [];
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'] ?: 'Strict',
    ]);
    session_destroy();
    session_start();                 // sesión nueva, vacía, solo para llevar el aviso
    session_regenerate_id(true);
    if ($json) {
        responderError(401, $mensaje, true);
    }
    setFlash('aviso', $mensaje);
    redirigir(URL_LOGIN);
}

/** Cierra la sesión por inactividad o por superar su duración máxima. */
function controlarTiempos(bool $json): void
{
    $ahora  = time();
    $inicio = $_SESSION['inicio_sesion']     ?? $ahora;
    $ultima = $_SESSION['ultima_actividad']  ?? $ahora;

    if (($ahora - $ultima) > INACTIVIDAD_MAX) {
        registrarEvento('sesion_expirada', 'inactividad');
        cerrarSesionYRedirigir('Tu sesión expiró por inactividad. Inicia sesión de nuevo.', $json);
    }
    if (($ahora - $inicio) > SESION_MAX) {
        registrarEvento('sesion_expirada', 'duracion maxima');
        cerrarSesionYRedirigir('Tu sesión alcanzó su duración máxima. Inicia sesión de nuevo.', $json);
    }
    $_SESSION['inicio_sesion']    = $inicio;
    $_SESSION['ultima_actividad'] = $ahora;
}

/**
 * Consulta la BD para confirmar que el usuario SIGUE activo y cuál es su rol HOY.
 * Sin esto, un usuario desactivado conservaría su acceso hasta cerrar el navegador,
 * y un cambio de rol no tendría efecto hasta volver a iniciar sesión.
 */
function revalidarUsuario(bool $json): void
{
    $ahora = time();
    if (isset($_SESSION['revalidado_en']) && ($ahora - $_SESSION['revalidado_en']) < REVALIDAR_CADA) {
        return;
    }

    require_once __DIR__ . '/../config/database.php';
    $pdo  = (new Database())->getConnection();
    $stmt = $pdo->prepare('SELECT rol, is_active FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => usuarioActualId()]);
    $fila = $stmt->fetch();

    if (!$fila || (int) $fila['is_active'] !== 1) {
        registrarEvento('sesion_invalida', 'usuario inexistente o desactivado');
        cerrarSesionYRedirigir('Tu cuenta no está disponible. Contacta al administrador.', $json);
    }

    $_SESSION[SESION_ROL]      = normalizarRol($fila['rol']);   // el rol de la BD manda
    $_SESSION['revalidado_en'] = $ahora;
}

/**
 * En POST: ¿la petición viene de nuestro propio sitio? Compara el host de las
 * cabeceras Origin / Referer con el host al que se pidió. Es una defensa extra
 * contra CSRF; la principal sigue siendo el token.
 */
function origenValido(): bool
{
    $hostPropio = strtolower($_SERVER['HTTP_HOST'] ?? '');
    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $cabecera) {
        if (!empty($_SERVER[$cabecera])) {
            $partes = parse_url((string) $_SERVER[$cabecera]);
            if ($partes === false || empty($partes['host'])) {
                return false;
            }
            $hostOrigen = strtolower($partes['host'] . (isset($partes['port']) ? ':' . $partes['port'] : ''));
            return hash_equals($hostPropio, $hostOrigen);
        }
    }
    return true;   // sin cabeceras (privacidad del navegador): decide el token CSRF
}


// ============================================================
// 8. DESPACHO
// ============================================================
evitarCache();   // las páginas del sistema no se guardan en caché (botón "atrás" tras cerrar sesión)

// --- Puerta 1: la ruta existe en la lista blanca -----------------------------
$controlador = $_GET['controller'] ?? 'sistema';
$accion      = $_GET['action']     ?? 'index';
// Si llega un arreglo (?controller[]=x) se descarta; si es texto, a minúsculas.
$controlador = is_string($controlador) ? strtolower($controlador) : '';
$accion      = is_string($accion)      ? strtolower($accion)      : '';

if (!preg_match('/^[a-z0-9]{1,40}\z/', $controlador)
    || !preg_match('/^[a-z0-9]{1,40}\z/', $accion)
    || !isset($rutas[$controlador]['acciones'][$accion])) {
    registrarEvento('ruta_inexistente', $controlador . '/' . $accion);
    responderError(404, 'La página que buscas no existe.');
}

$definicion = $rutas[$controlador];
$ruta       = $definicion['acciones'][$accion];
$json       = $ruta['json'];

// --- Puerta 2: método HTTP permitido ------------------------------------------
$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($metodo === 'HEAD') {
    $metodo = 'GET';
}
if ($metodo !== $ruta['metodo']) {
    header('Allow: ' . $ruta['metodo']);
    registrarEvento('metodo_no_permitido', $controlador . '/' . $accion . ' ' . $metodo);
    responderError(405, 'Método no permitido.', $json);
}

// --- Puerta 3: en POST, mismo origen --------------------------------------------
if ($metodo === 'POST' && !origenValido()) {
    registrarEvento('origen_invalido', $controlador . '/' . $accion);
    responderError(403, 'Solicitud no válida.', $json);
}

// --- Puertas 4, 5 y 6: sesión, usuario vigente y rol --------------------------
$esPublica   = ($ruta['roles'] === null);
$autenticado = usuarioActualId() > 0;

if (!$esPublica) {
    if (!$autenticado) {
        if ($json) {
            responderError(401, 'Tu sesión expiró. Inicia sesión de nuevo.', true);
        }
        redirigir(URL_LOGIN);
    }
    controlarTiempos($json);      // Puerta 4: inactividad y duración máxima
    revalidarUsuario($json);      // Puerta 5: ¿sigue activo? ¿qué rol tiene hoy?

    if (!tieneRol($ruta['roles'])) {   // Puerta 6: autorización por rol
        registrarEvento('acceso_denegado', $controlador . '/' . $accion);
        responderError(403, 'No tienes permiso para realizar esta acción.', $json);
    }
} elseif ($autenticado && $accion !== 'logout') {
    // Ya hay sesión y pide una ruta pública (el login): al dashboard.
    redirigir(URL_DASHBOARD);
}

// --- Puerta 7: token CSRF en POST ---------------------------------------------
if ($metodo === 'POST' && $ruta['csrf'] && !validarCsrf($_POST['csrf_token'] ?? null)) {
    registrarEvento('csrf_invalido', $controlador . '/' . $accion);
    responderError(403, 'El formulario expiró o no es válido. Recarga la página e inténtalo de nuevo.', $json);
}

// --- Puerta 8: ejecución ----------------------------------------------------------
// El archivo y la clase salen de TU tabla, nunca de lo que escribió el usuario.
try {
    $archivo = __DIR__ . '/../app/controller/' . $definicion['archivo'];
    if (!is_file($archivo)) {
        throw new RuntimeException('No existe el archivo del controlador: ' . $definicion['archivo']);
    }
    require_once $archivo;

    $clase = $definicion['clase'];
    if (!class_exists($clase, false)) {
        throw new RuntimeException('La clase ' . $clase . ' no está definida en su archivo.');
    }

    $instancia = new $clase();
    if (!is_callable([$instancia, $accion])) {
        throw new RuntimeException('El método ' . $accion . ' no es invocable en ' . $clase . '.');
    }
    $instancia->$accion();   // PHP no distingue mayúsculas en métodos: tablaproductos() = TablaProductos()
} catch (Throwable $e) {
    registrarEvento('error_interno', $controlador . '/' . $accion . ': ' . $e->getMessage());
    responderError(
        500,
        MODO_DESARROLLO ? $e->getMessage() : 'Ocurrió un error interno. Inténtalo de nuevo más tarde.',
        $json
    );
}