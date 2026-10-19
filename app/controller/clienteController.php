<?php
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../model/clienteModel.php';
require_once __DIR__ . '/../../config/database.php';

Class clienteController{
private $clienteModel;

/** Valores que acepta la columna tipo_documento (ENUM de la BD). */
private const TIPOS_DOCUMENTO = ['V', 'E', 'J', 'G'];

public function __construct(){
    $database = new Database();
    $pdo = $database->getConnection();
    $this->clienteModel = new clienteModel($pdo);
}

public function tablaClientes(){
    exigirSesion();
    evitarCache();
    $clientes = $this->clienteModel->getAll();
    require_once __DIR__ . '/../views/clientes/clientes.php';
}
public function formcrearcliente(){
    exigirSesion();
    evitarCache();
    require_once __DIR__ . '/../views/clientes/crearcliente.php';

}

public function formeditarcliente(){
    exigirSesion();
    evitarCache();

    $id = validarEntero($_GET['id'] ?? null, 1);
    if ($id !== null) {
        $clientes = $this->clienteModel->getById($id);
        if ($clientes) {
            require_once __DIR__ . '/../../app/views/clientes/editarcliente.php';
            return;
        }
    }
    // Si no se encuentra el cliente, redirigir a la lista de clientes
    setFlash('error', 'El cliente no existe.');
    redirigir(urlAccion('cliente', 'tablaClientes'));
}


public function guardar(){
    exigirSesion();
    $urlFormulario = urlAccion('cliente', 'formcrearcliente');
    exigirPost($urlFormulario);

    list($datos, $errores) = $this->validarDatos(null);
    if (!empty($errores)) {
        setFlash('error', $errores);
        redirigir($urlFormulario);
    }

    // Enviamos los datos ya validados y normalizados al modelo para hacer el INSERT
    $exito = $this->clienteModel->createcliente(
        $datos['tipo_documento'], $datos['num_documento'], $datos['nombre_razon_social'],
        $datos['telefono'], $datos['email'], $datos['direccion']
    );

    if ($exito) {
        setFlash('exito', 'Cliente «' . $datos['nombre_razon_social'] . '» registrado correctamente.');
        redirigir(urlAccion('cliente', 'tablaClientes'));
    }

    setFlash('error', 'Hubo un error al registrar el cliente.');
    redirigir($urlFormulario);
}

public function actualizar(){
    exigirSesion();
    exigirPost(urlAccion('cliente', 'tablaClientes'));

    $id = validarEntero($_POST['id'] ?? null, 1);
    $actual = ($id !== null) ? $this->clienteModel->getById($id) : false;
    if (!$actual) {
        setFlash('error', 'El cliente que intentas editar no existe.');
        redirigir(urlAccion('cliente', 'tablaClientes'));
    }

    $urlFormulario = urlAccion('cliente', 'formeditarcliente', ['id' => $id]);

    list($datos, $errores) = $this->validarDatos($id);
    if (!empty($errores)) {
        setFlash('error', $errores);
        redirigir($urlFormulario);
    }

    // 'estado' y 'creado_en' NO se toman del formulario: viajaban como campos ocultos y
    // cualquiera podía alterarlos (reactivar un cliente o falsear su fecha de registro).
    // Se conserva lo que ya hay en la BD.
    $exito = $this->clienteModel->updatecliente(
        $id,
        $datos['tipo_documento'],
        $datos['nombre_razon_social'],
        $datos['num_documento'],
        $datos['telefono'],
        $datos['email'],
        $datos['direccion'],
        $actual['estado'],
        $actual['creado_en']
    );

    if ($exito) {
        setFlash('exito', 'Cliente «' . $datos['nombre_razon_social'] . '» actualizado correctamente.');
        redirigir(urlAccion('cliente', 'tablaClientes'));
    }

    setFlash('error', 'No se pudo actualizar el cliente. Inténtalo de nuevo.');
    redirigir($urlFormulario);
}

/**
 * Validación común de crear y editar.
 * $idExcluir: al editar, el propio cliente no cuenta como documento repetido.
 * Devuelve [datos limpios, lista de errores].
 */
private function validarDatos(?int $idExcluir): array {
    $errores = [];

    // tipo_documento es un ENUM: solo V, E, J o G. Cualquier otro valor se cuela vacío en MySQL no estricto.
    $tipo = validarClave($_POST['tipo_documento'] ?? null, array_flip(self::TIPOS_DOCUMENTO));
    if ($tipo === null) {
        $errores[] = 'Elige un tipo de documento válido (V, E, J o G).';
    }

    // num_documento: formato según el tipo (cédula o RIF) y sin repetirse (columna UNIQUE).
    $documento = null;
    if ($tipo !== null) {
        $documento = validarDocumentoIdentidad($tipo, $_POST['num_documento'] ?? null);
        if ($documento === null) {
            $errores[] = ($tipo === 'V' || $tipo === 'E')
                ? 'La cédula debe tener entre 6 y 9 dígitos (solo números).'
                : 'El RIF debe tener 8 dígitos más el dígito verificador. Ejemplo: 12345678-9.';
        } elseif ($this->clienteModel->existeDocumento($documento, $idExcluir)) {
            $errores[] = 'Ya existe un cliente registrado con el documento ' . $documento . '.';
        }
    }

    // nombre_razon_social: VARCHAR(150) NOT NULL
    $nombre = validarNombreComercial($_POST['nombre_razon_social'] ?? null, 2, 150);
    if ($nombre === null) {
        $errores[] = 'El nombre o razón social es obligatorio: de 2 a 150 caracteres (no se permite < ni >).';
    }

    // Contacto: opcional, pero si se escribe debe ser válido.
    $tel = opcional($_POST['telefono'] ?? '', 'validarTelefono');
    if (!$tel['ok']) {
        $errores[] = 'El teléfono debe tener entre 7 y 15 dígitos. Ejemplo: 0412-1234567.';
    }

    $correo = opcional($_POST['email'] ?? '', 'validarEmail');
    if (!$correo['ok']) {
        $errores[] = 'El correo electrónico no es válido (máximo 100 caracteres).';
    }

    // direccion: VARCHAR(255) NULL
    $dir = opcional($_POST['direccion'] ?? '', function ($v) {
        return validarTextoLibre($v, 3, 255);
    });
    if (!$dir['ok']) {
        $errores[] = 'La dirección debe tener entre 3 y 255 caracteres y no puede contener < ni >.';
    }

    return [[
        'tipo_documento'      => $tipo,
        'num_documento'       => $documento,
        'nombre_razon_social' => $nombre,
        'telefono'            => $tel['valor'],
        'email'               => $correo['valor'],
        'direccion'           => $dir['valor'],
    ], $errores];
}

}
?>
