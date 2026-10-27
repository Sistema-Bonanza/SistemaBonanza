<?php

class usuarioModel{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getAll(){
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById($id){
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }    

    public function createUsuario($username, $password_hash, $rol){
        
        try{
            // Sentencia para crear al nuevo usuario.
            $sql = "INSERT INTO usuarios (username, password_hash, rol) VALUES (:username, :password_hash, :rol)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => $password_hash,
                ':rol' => $rol,
            ]);

            return true; //usuario creado exitosamente.

        } catch(PDOException $e){
            //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
            echo "error en la Base de Datos " . $e->getMessage();
            return false; //Error al crear el usuario.
        }
    }

    public function updateUsuario($id, $username, $rol, $password_hash=null){
            
          // Si no se proporciona contraseña, mantener la existente
            if(empty($password_hash)){
                $usuario = $this->getById($id);
                if(!$usuario){
                    return false;
                }
                $password_hash = $usuario['password_hash'];
            }
            // Sentencia para actualizar al usuario.
            $sql = "UPDATE usuarios SET username = :username, password_hash = :password_hash, rol = :rol WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':username' => $username,
                ':password_hash' => $password_hash,
                ':rol' => $rol,
            ]);
    }

    public function deleteUsuario($id){
            $stmt = $this->pdo->prepare("UPDATE usuarios SET is_active = 0 WHERE id_usuario = :id");
            $stmt->execute([':id' => $id]);
            return true; //usuario eliminado exitosamente.
    }

    // ¿Ya existe ese nombre de usuario? (la columna tiene UNIQUE; la comparación ignora mayúsculas)
    // $excluirId sirve al EDITAR: el propio usuario no cuenta como duplicado.
    public function existeUsername($username, $excluirId = null){
        $sql = "SELECT COUNT(*) FROM usuarios WHERE username = :username";
        $params = [':username' => $username];
        if ($excluirId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = (int) $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Cuántos usuarios ACTIVOS tienen alguno de esos roles. Sirve para no dejar
    // el sistema sin administradores. $excluirId deja fuera a un usuario del conteo.
    public function contarActivosPorRoles(array $roles, $excluirId = null){
        if (empty($roles)) {
            return 0;
        }
        $marcas = implode(',', array_fill(0, count($roles), '?'));
        $sql = "SELECT COUNT(*) FROM usuarios WHERE is_active = 1 AND rol IN ($marcas)";
        $params = array_values($roles);
        if ($excluirId !== null) {
            $sql .= " AND id <> ?";
            $params[] = (int) $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // ¿El usuario ya hizo algo en el sistema (compras, movimientos, cambios de precio)?
    // Si sí, borrarlo rompería esos registros (claves foráneas) y la auditoría.
    public function tieneActividad($id){
        $sql = "SELECT
                    (SELECT COUNT(*) FROM compras WHERE id_usuario = :a OR id_usuario_anula = :b)
                  + (SELECT COUNT(*) FROM movimientos WHERE id_usuario = :c)
                  + (SELECT COUNT(*) FROM historial_precios WHERE id_usuario = :d)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':a' => (int) $id, ':b' => (int) $id, ':c' => (int) $id, ':d' => (int) $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
?>