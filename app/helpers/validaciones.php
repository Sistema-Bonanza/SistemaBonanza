<?php
/**
 * app/helpers/validaciones.php
 *
 * Validadores de datos para TODOS los controladores. Complementa a
 * seguridad.php (que ya trae validarEntero, validarDecimal, validarTexto,
 * validarFecha y validarClave). Se carga con require_once.
 *
 * Regla común (la misma de seguridad.php):
 *   - Cada validador devuelve el valor LIMPIO y válido, o null si no pasa.
 *   - Aceptan cualquier tipo de entrada: un atacante puede enviar arreglos
 *     (campo[]=x) en lugar de texto, y eso nunca debe llegar a la BD.
 *   - Lo que se guarda ya viene normalizado (teléfono solo dígitos, RIF con
 *     guiones, email en minúsculas), así la BD no acumula el mismo dato
 *     escrito de cinco formas distintas.
 *
 * Las expresiones regulares terminan en \z (no en $): con $ se colaría un
 * salto de línea final. La bandera /u hace que \p{L} reconozca letras con
 * tilde y la ñ.
 */

require_once __DIR__ . '/seguridad.php';


// ============================================================
// 1. UTILIDADES PARA CAMPOS OPCIONALES Y MÉTODO HTTP
// ============================================================

/** ¿El campo llegó vacío (o ni siquiera llegó)? Un arreglo NO cuenta como vacío. */
function esVacio($valor): bool
{
    return $valor === null || (is_string($valor) && trim($valor) === '');
}

/**
 * Valida un campo OPCIONAL: vacío es válido (se guarda como NULL);
 * si trae algo, debe pasar el validador.
 *
 *   $r = opcional($_POST['email'] ?? '', 'validarEmail');
 *   if (!$r['ok']) { $errores[] = 'El email no es válido.'; }
 *   $email = $r['valor'];          // texto limpio o null
 *
 * @return array ['ok' => bool, 'valor' => ?string]
 */
function opcional($valor, callable $validador): array
{
    if (esVacio($valor)) {
        return ['ok' => true, 'valor' => null];
    }
    $limpio = $validador($valor);
    return ['ok' => $limpio !== null, 'valor' => $limpio];
}

/**
 * Las acciones que modifican datos solo se aceptan por POST.
 * Con GET cualquiera podría provocar un cambio con solo abrir un enlace.
 *
 * PENDIENTE (cuando agregues campoCsrf() a los formularios): cambia esta
 * llamada por exigirPostConCsrf($url), que ya existe en seguridad.php.
 */
function exigirPost(string $urlSiFalla): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        setFlash('error', 'Solicitud no válida.');
        redirigir($urlSiFalla);
    }
}


// ============================================================
// 2. TEXTOS
// ============================================================

/**
 * Nombre de cosa: razón social, producto, categoría.
 * Debe empezar con letra o número. Permite  . , & ' ( ) / # + % : -
 * Rechaza < y > (así no se guarda HTML) y los caracteres de control.
 */
function validarNombreComercial($valor, int $min = 2, int $max = 150): ?string
{
    return validarTexto($valor, $min, $max, '/^[\p{L}\p{N}][\p{L}\p{N} .,&\'()\/#+%:\-]*\z/u');
}

/** Nombre de persona: solo letras, espacios, punto, apóstrofo y guion. */
function validarNombrePersona($valor, int $min = 2, int $max = 100): ?string
{
    return validarTexto($valor, $min, $max, '/^\p{L}[\p{L} .\'\-]*\z/u');
}

/** Texto libre (descripción, dirección). Acepta casi todo, pero nunca < ni >. */
function validarTextoLibre($valor, int $min, int $max): ?string
{
    return validarTexto($valor, $min, $max, '/^[^<>]+\z/u');
}

/** Código interno (producto): letras, números, punto, guion y guion bajo. */
function validarCodigo($valor, int $max = 50): ?string
{
    return validarTexto($valor, 1, $max, '/^[A-Za-z0-9][A-Za-z0-9._\-]*\z/');
}

/** Número de factura: letras, números, punto, barra y guion. Sin espacios. */
function validarNumeroFactura($valor, int $max = 30): ?string
{
    return validarTexto($valor, 1, $max, '/^[A-Za-z0-9][A-Za-z0-9._\/\-]*\z/');
}


// ============================================================
// 3. DATOS DE CONTACTO E IDENTIDAD (formatos venezolanos)
// ============================================================

/** Email válido, en minúsculas, de hasta $max caracteres. */
function validarEmail($valor, int $max = 100): ?string
{
    $texto = validarTexto($valor, 3, $max);
    if ($texto === null || filter_var($texto, FILTER_VALIDATE_EMAIL) === false) {
        return null;
    }
    return mb_strtolower($texto, 'UTF-8');
}

/**
 * Teléfono: acepta espacios, guiones y paréntesis al escribirlo, pero
 * guarda SOLO los dígitos (y un "+" inicial si lo trae). Entre 7 y 15
 * dígitos. "(0412) 076-5743"  =>  "04120765743"
 */
function validarTelefono($valor): ?string
{
    if (!is_string($valor)) {
        return null;
    }
    $texto = trim($valor);
    if (!preg_match('/^\+?[\d(][\d\s\-().]*\z/', $texto)) {
        return null;
    }
    $digitos = preg_replace('/\D/', '', $texto);
    $cantidad = strlen($digitos);
    if ($cantidad < 7 || $cantidad > 15) {
        return null;
    }
    return ($texto[0] === '+' ? '+' : '') . $digitos;
}

/**
 * RIF: letra (J, G, V, E, P, C) + 8 dígitos + 1 dígito verificador.
 * Acepta con o sin guiones y en minúscula; guarda "J-12345678-9".
 * Valida el FORMATO, no el dígito verificador (tus datos de prueba,
 * como J-12345678-7, no lo cumplen).
 */
function validarRif($valor): ?string
{
    if (!is_string($valor)) {
        return null;
    }
    $limpio = strtoupper(preg_replace('/[\s\-]/', '', $valor));
    if (!preg_match('/^([JGVEPC])(\d{8})(\d)\z/', $limpio, $m)) {
        return null;
    }
    return $m[1] . '-' . $m[2] . '-' . $m[3];
}

/**
 * Documento de un cliente según su tipo:
 *   V o E  => cédula: 6 a 9 dígitos. Se guarda solo el número ("12345678").
 *   J o G  => RIF: 8 dígitos + 1 verificador. Se guarda "12345678-9"
 *             (la letra ya está en la columna tipo_documento).
 * Acepta puntos, guiones, espacios y la letra del tipo al escribirlo.
 * Rechaza números que son solo ceros.
 */
function validarDocumentoIdentidad($tipo, $valor): ?string
{
    if (!is_string($valor) || !is_string($tipo) || !in_array($tipo, ['V', 'E', 'J', 'G'], true)) {
        return null;
    }
    $limpio = strtoupper(preg_replace('/[\s.\-]/', '', $valor));

    // $tipo ya fue comprobado contra la lista blanca: es seguro usarlo en el patrón.
    if ($tipo === 'V' || $tipo === 'E') {
        if (!preg_match('/^' . $tipo . '?(\d{6,9})\z/', $limpio, $m) || (int) $m[1] === 0) {
            return null;
        }
        return $m[1];
    }

    if (!preg_match('/^' . $tipo . '?(\d{8})(\d)\z/', $limpio, $m) || (int) $m[1] === 0) {
        return null;
    }
    return $m[1] . '-' . $m[2];
}


// ============================================================
// 4. CUENTAS DE USUARIO
// ============================================================

/** Usuario: 3 a 30 caracteres; letras, números, punto, guion y guion bajo. */
function validarUsername($valor): ?string
{
    return validarTexto($valor, 3, 30, '/^[A-Za-z0-9][A-Za-z0-9._\-]*\z/');
}

/**
 * Política de contraseña. Devuelve la lista de problemas (vacía = válida).
 *  - NO se hace trim: cambiar la contraseña en silencio rompería el login,
 *    que compara el texto exacto. Si empieza o termina con espacio, se rechaza.
 *  - Máximo 72 BYTES: bcrypt ignora todo lo que pase de ahí, así que dos
 *    contraseñas largas distintas podrían terminar siendo la misma.
 */
function erroresPassword($valor): array
{
    if (!is_string($valor)) {
        return ['La contraseña no es válida.'];
    }
    $errores = [];

    if ($valor !== trim($valor)) {
        $errores[] = 'La contraseña no puede empezar ni terminar con espacios.';
    }
    if (mb_strlen($valor, 'UTF-8') < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (strlen($valor) > 72) {
        $errores[] = 'La contraseña no puede superar 72 caracteres (las letras con tilde cuentan doble).';
    }
    if (!preg_match('/\p{L}/u', $valor) || !preg_match('/\d/', $valor)) {
        $errores[] = 'La contraseña debe incluir al menos una letra y un número.';
    }
    if (preg_match('/[\x00-\x1F\x7F]/', $valor)) {
        $errores[] = 'La contraseña contiene caracteres no permitidos.';
    }
    return $errores;
}
