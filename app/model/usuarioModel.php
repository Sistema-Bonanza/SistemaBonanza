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

    public function crearUsuario($username, $password_hash, $rol){
        
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

    public function actualizarUsuario($id, $username, $password_hash='null', $rol){
            
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

    public function eliminarUsuario($id){
            $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true; //usuario eliminado exitosamente.
    }
}
?>