<?php

class UsuarioModel{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
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
}
?>