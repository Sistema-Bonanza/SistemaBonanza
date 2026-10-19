<?php
require_once __DIR__.'/../helpers/seguridad.php';
require_once __DIR__.'/../helpers/validaciones.php';
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../model/usuarioModel.php';

    class usuarioController{
        private $model;

        /**
         * Roles que pueden administrar usuarios, y que cuentan como "administrador" al
         * proteger al último. 'admin' es el usuario semilla; 'Administra' es como quedó
         * guardado 'Administrador' en las BD donde la columna rol mide solo 10 caracteres
         * (corrige eso con mejoras_integridad_bd.sql y luego puedes quitar 'Administra').
         */
        private const ROLES_ADMIN = ['admin', 'Administrador', 'Administra'];

        /** Roles que el formulario permite asignar (lista blanca). */
        private const ROLES_ASIGNABLES = ['Administrador', 'Operador', 'Usuario'];

    public function __construct(){
        //crear conexion con la base de datos.
        $database = new Database();
        $pdo = $database->getConnection();
        //inyectar la coneccion en el modelo.
        $this->model = new usuarioModel($pdo);
    }

    public function formusuario(){
        //Este metodo solo sirve para mostrar la vista de usuario.
        exigirRol(self::ROLES_ADMIN);
        evitarCache();
         $usuarios = $this->model->getAll();
        require_once '../app/views/usuario/usuario.php';
    }


    public function formusuariocrear(){
        //Este metodo solo sirve para mostrar la vista de crear usuario.
        exigirRol(self::ROLES_ADMIN);
        evitarCache();
        require_once '../app/views/usuario/crear.php';
    }

   public function formusuarioeditar(){
    exigirRol(self::ROLES_ADMIN);
    evitarCache();

    $id = validarEntero($_GET['id'] ?? null, 1);
    if($id === null){
        redirigir(urlAccion('usuario', 'formusuario'));
    }

    $usuario = $this->model->getById($id);
    if(!$usuario){
        setFlash('error', 'Usuario no encontrado.');
        redirigir(urlAccion('usuario', 'formusuario'));
    }

    require_once '../app/views/usuario/editar.php';
}

    public function guardar(){
        exigirRol(self::ROLES_ADMIN);
        $urlFormulario = urlAccion('usuario', 'formusuariocrear');
        exigirPost($urlFormulario);

        $errores = [];

        //Recolección y validación de los datos de registro del usuario
        // username: VARCHAR(100) NOT NULL UNIQUE
        $username = validarUsername($_POST['username'] ?? null);
        if($username === null){
            $errores[] = 'El usuario debe tener de 3 a 30 caracteres: letras, números, punto, guion o guion bajo, sin espacios.';
        } elseif($this->model->existeUsername($username)){
            $errores[] = 'El nombre de usuario «' . $username . '» ya está en uso.';
        }

        // La contraseña es OBLIGATORIA al crear: antes se aceptaba vacía y se guardaba el hash de ''.
        $password = $_POST['password'] ?? null;
        $erroresPassword = erroresPassword($password);
        $errores = array_merge($errores, $erroresPassword);

        // rol: solo los de la lista blanca (la columna es texto libre, aceptaría cualquier cosa).
        $rol = validarClave($_POST['rol'] ?? null, array_flip(self::ROLES_ASIGNABLES));
        if($rol === null){
            $errores[] = 'Selecciona un rol válido.';
        }

        if(!empty($errores)){
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        //Encriptamiento de la clave de acceso (solo si pasó la política)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if($this->model->createUsuario($username, $password_hash, $rol)){
            setFlash('exito', 'Usuario «' . $username . '» creado correctamente.');
            redirigir(urlAccion('usuario', 'formusuario'));
        }

        setFlash('error', 'No se pudo crear el usuario. Inténtalo de nuevo.');
        redirigir($urlFormulario);
    }

    public function editar(){
        exigirRol(self::ROLES_ADMIN);
        exigirPost(urlAccion('usuario', 'formusuario'));

        $id = validarEntero($_POST['id'] ?? null, 1);
        $actual = ($id !== null) ? $this->model->getById($id) : false;
        if(!$actual){
            setFlash('error', 'El usuario que intentas editar no existe.');
            redirigir(urlAccion('usuario', 'formusuario'));
        }

        $urlFormulario = urlAccion('usuario', 'formusuarioeditar', ['id' => $id]);
        $errores = [];

        $username = validarUsername($_POST['username'] ?? null);
        if($username === null){
            $errores[] = 'El usuario debe tener de 3 a 30 caracteres: letras, números, punto, guion o guion bajo, sin espacios.';
        } elseif($this->model->existeUsername($username, $id)){
            $errores[] = 'El nombre de usuario «' . $username . '» ya está en uso.';
        }

        $rol = validarClave($_POST['rol'] ?? null, array_flip(self::ROLES_ASIGNABLES));
        if($rol === null){
            $errores[] = 'Selecciona un rol válido.';
        }

        // Contraseña OPCIONAL al editar. El formulario dice "Dejar en blanco para mantener la
        // contraseña actual", pero antes se hasheaba el texto vacío y se SOBRESCRIBÍA la clave real.
        // Ahora: vacía => se envía '' y el modelo conserva el hash que ya existe.
        $password_hash = '';
        $password = $_POST['password'] ?? '';
        if(!esVacio($password)){
            $erroresPassword = erroresPassword($password);
            if(empty($erroresPassword)){
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $errores = array_merge($errores, $erroresPassword);
            }
        }

        // No dejar el sistema sin administradores: si este usuario es admin y se le quita el
        // rol, tiene que quedar otro administrador activo.
        if($rol !== null && $this->esAdministrador($actual['rol'] ?? '') && !$this->esAdministrador($rol)){
            if($this->model->contarActivosPorRoles(self::ROLES_ADMIN, $id) < 1){
                $errores[] = 'No puedes quitar el rol de administrador al único administrador activo del sistema.';
            }
        }

        if(!empty($errores)){
            setFlash('error', $errores);
            redirigir($urlFormulario);
        }

        // updateUsuario() no atrapa errores de la BD: se captura aquí para no mostrar un error fatal.
        $guardado = false;
        try {
            $guardado = $this->model->updateUsuario($id, $username, $password_hash, $rol);
        } catch (PDOException $e) {
            error_log('usuarioController::editar -> ' . $e->getMessage());
        }

        if($guardado){
            // Si el administrador se edita a sí mismo, se refresca el nombre guardado en sesión.
            if($id === usuarioActualId()){
                $_SESSION['username'] = $username;
            }
            setFlash('exito', 'Usuario «' . $username . '» actualizado correctamente.');
            redirigir(urlAccion('usuario', 'formusuario'));
        }

        setFlash('error', 'No se pudo actualizar el usuario. Inténtalo de nuevo.');
        redirigir($urlFormulario);
    }

    public function eliminar(){
        exigirRol(self::ROLES_ADMIN);
        $urlLista = urlAccion('usuario', 'formusuario');

        //Recolección del ID del usuario a eliminar
        $id = validarEntero($_GET['id'] ?? null, 1);
        $usuario = ($id !== null) ? $this->model->getById($id) : false;
        if(!$usuario){
            setFlash('error', 'El usuario que intentas eliminar no existe.');
            redirigir($urlLista);
        }

        // Nadie puede eliminar su propia cuenta (se quedaría sin acceso en plena sesión).
        if($id === usuarioActualId()){
            setFlash('error', 'No puedes eliminar tu propia cuenta.');
            redirigir($urlLista);
        }

        // Tampoco al último administrador activo.
        if($this->esAdministrador($usuario['rol'] ?? '') && $this->model->contarActivosPorRoles(self::ROLES_ADMIN, $id) < 1){
            setFlash('error', 'No puedes eliminar al único administrador activo del sistema.');
            redirigir($urlLista);
        }

        // Un usuario con compras, movimientos o cambios de precio registrados no se puede borrar:
        // dejaría esos registros huérfanos (o la BD lo rechazaría con un error fatal).
        if($this->model->tieneActividad($id)){
            setFlash('error', 'El usuario «' . $usuario['username'] . '» tiene actividad registrada (compras, movimientos o cambios de precio) y no se puede eliminar sin romper esa información.');
            redirigir($urlLista);
        }

        try {
            $this->model->deleteUsuario($id);
            setFlash('exito', 'Usuario «' . $usuario['username'] . '» eliminado.');
        } catch (PDOException $e) {
            error_log('usuarioController::eliminar -> ' . $e->getMessage());
            setFlash('error', 'No se pudo eliminar el usuario.');
        }

        redirigir($urlLista);
    }

    /** ¿Este valor de rol cuenta como administrador? */
    private function esAdministrador($rol): bool {
        return is_string($rol) && in_array($rol, self::ROLES_ADMIN, true);
    }

}

?>
