<?php

class UsuarioModel{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function obtenerUsername($username){
        try{
            //buscamos al usuario
            // ¡IMPORTANTE! Agregamos "AND is_active = 1" para aplicar tu regla de Soft Delete.
            $sql = "SELECT * FROM usuarios WHERE username = :username AND is_active = 1";

            $stmt = $this->pdo->prepare($sql);
        

        //Vinculamos el Parametro de forma segura contra inyecciones SQL
        $stmt->execute([
            ':username' => $username
        ]);

        // Usamos fetch() y no fetchAll() porque solo esperamos UN usuario, no una lista.
            // Si lo encuentra, devuelve un array con los datos. Si no, devuelve false.
            return $stmt->fetch();

    } catch(PDOException $e){
        //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
        echo "Error en la Base de Datos " . $e->getMessage();
        return false;

        }
    }
}
?>