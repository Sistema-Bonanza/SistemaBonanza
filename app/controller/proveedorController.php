<?php
// Requerimos el modelo para poder instanciarlo
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/proveedorModel.php';

class ProveedorController {
    private $proveedorModel;

    public function __construct() {
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();

        $this->proveedorModel = new proveedorModel($pdo);
    }

    // Acción por defecto: Carga la tabla con todos los proveedores
    public function tablaProveedores() {
        exigirSesion();
        evitarCache();

        // 1. Pedimos al modelo que nos traiga todos los proveedores
        $proveedores = $this->proveedorModel->getAll();

        // 2. Cargamos la vista. La variable $proveedores estará disponible allí.
        require_once __DIR__ . '/../views/proveedor/proveedor.php';
    }

    // Acción para mostrar el formulario de creación
    public function formcrearproveedor() {
        exigirSesion();
        evitarCache();
        // Aquí simplemente cargamos la vista del formulario HTML
        require_once __DIR__ . '/../views/proveedor/crearProveedor.php';
    }

    public function formEdit(){
        exigirSesion();
        evitarCache();

        //VERIFICAMOS QUE LLEGUE UN ID VÁLIDO POR URL (ej: index.php?controller=proveedor&action=formEdit&id=5)
        $id = validarEntero($_GET['id'] ?? null, 1);

        //BUSCAMOS LOS DATOS ACTUALES DE ESE PROVEEDOR.
        $proveedor = ($id !== null) ? $this->proveedorModel->obtenerPorId($id) : false;

        //SI EL PROVEEDOR EXISTE, CARGAMOS LA VISTA DE EDITAR.
        if ($proveedor) {
            require_once __DIR__ . '/../views/proveedor/editarProveedor.php';
            return;
        }

        //SI NO HAY ID O ES UN ID QUE NO EXISTE, LO DEVOLVEMOS A LA TABLA DE PROVEEDORES.
        setFlash('error', 'El proveedor no existe.');
        redirigir(urlAccion('proveedor', 'tablaProveedores'));
    }

    // Acción para recibir los datos del formulario por POST y guardarlos
    public function guardar() {
        exigirSesion();
        $urlFormulario = urlAccion('proveedor', 'formcrearproveedor');
        exigirPost($urlFormulario);

        $errores = [];

        // rif: VARCHAR(20) NOT NULL UNIQUE. Se guarda normalizado: J-12345678-9
        $rif = validarRif($_POST['rif'] ?? null);
        if ($rif === null) {
            $errores[] = 'El RIF no es válido. Formato: letra (J, G, V, E, P o C) + 8 dígitos + 1 dígito verificador. Ejemplo: J-12345678-9.';
        } elseif ($this->proveedorModel->existeRif($rif)) {
            $errores[] = 'Ya existe un proveedor registrado con el RIF ' . $rif . '.';
        }

        // razon_social: VARCHAR(100) NOT NULL
        $razon_social = validarNombreComercial($_POST['razon_social'] ?? null, 2, 100);
        if ($razon_social === null) {
            $errores[] = 'La razón social es obligatoria: de 2 a 100 caracteres (no se permite < ni >).';
        }

        // Datos opcionales: si se escriben, deben ser válidos. Vacío se guarda como NULL.
        list($contacto, $tel, $correo, $dir) = $this->validarOpcionales($errores, true);

        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // Enviamos los datos ya validados y normalizados al modelo para hacer el INSERT
        $exito = $this->proveedorModel->crearProveedor($rif, $razon_social, $contacto, $tel, $correo, $dir);

        if ($exito) {
            setFlash('exito', 'Proveedor «' . $razon_social . '» registrado correctamente.');
            redirigir(urlAccion('proveedor', 'tablaProveedores'));
        }

        setFlash('error', 'Hubo un error al registrar el proveedor.');
        redirigir($urlFormulario);
    }


    public function actualizar(){
        exigirSesion();
        //COMPROBAMOS QUE LOS DATOS VENGAN POR EL METODO POST.
        exigirPost(urlAccion('proveedor', 'tablaProveedores'));

        $id = validarEntero($_POST['id_proveedor'] ?? null, 1);
        if ($id === null || !$this->proveedorModel->obtenerPorId($id)) {
            setFlash('error', 'El proveedor que intentas editar no existe.');
            redirigir(urlAccion('proveedor', 'tablaProveedores'));
        }

        $urlFormulario = urlAccion('proveedor', 'formEdit', ['id' => $id]);

        //SOLO SE LEEN LOS DATOS QUE SE PUEDEN EDITAR (CONTACTO, TELEFONO, EMAIL).
        //RIF, razón social y dirección son "readonly" en el HTML, pero eso lo salta cualquiera:
        //aquí el servidor ni siquiera los lee, así que no hay forma de alterarlos.
        $errores = [];
        list($contacto, $tel, $correo, $dir) = $this->validarOpcionales($errores, false);

        if (!empty($errores)) {
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // USAMOS EL MODELO DEL CONSTRUCTOR PARA ACTUALIZAR
        $resultado = $this->proveedorModel->actualizarProveedor($id, $contacto, $tel, $correo);

        if($resultado){
            setFlash('exito', 'Datos de contacto actualizados correctamente.');
            redirigir(urlAccion('proveedor', 'tablaProveedores'));
        }

        setFlash('error', 'Hubo un error al actualizar el proveedor.');
        redirigir($urlFormulario);
    }

    /**
     * Valida contacto, teléfono, email y (opcionalmente) dirección.
     * Agrega los problemas encontrados a $errores (por referencia).
     * Devuelve [contacto, teléfono, email, dirección] ya limpios (o null si vinieron vacíos).
     */
    private function validarOpcionales(array &$errores, bool $incluirDireccion): array {
        // nombre_contacto: VARCHAR(100)
        $contacto = opcional($_POST['nombre_contacto'] ?? '', function ($v) {
            return validarNombrePersona($v, 2, 100);
        });
        if (!$contacto['ok']) {
            $errores[] = 'El nombre de contacto solo puede tener letras, espacios, punto, apóstrofo y guion (2 a 100 caracteres).';
        }

        // telefono: VARCHAR(20). Se guarda solo con dígitos.
        $tel = opcional($_POST['telefono'] ?? '', 'validarTelefono');
        if (!$tel['ok']) {
            $errores[] = 'El teléfono debe tener entre 7 y 15 dígitos. Ejemplo: 0412-1234567.';
        }

        // email: VARCHAR(100)
        $correo = opcional($_POST['email'] ?? '', 'validarEmail');
        if (!$correo['ok']) {
            $errores[] = 'El correo electrónico no es válido (máximo 100 caracteres).';
        }

        // direccion: TEXT. Se limita a 500 para que nadie guarde textos gigantes.
        $direccion = ['ok' => true, 'valor' => null];
        if ($incluirDireccion) {
            $direccion = opcional($_POST['direccion'] ?? '', function ($v) {
                return validarTextoLibre($v, 3, 500);
            });
            if (!$direccion['ok']) {
                $errores[] = 'La dirección debe tener entre 3 y 500 caracteres y no puede contener < ni >.';
            }
        }

        return [$contacto['valor'], $tel['valor'], $correo['valor'], $direccion['valor']];
    }
}
?>
