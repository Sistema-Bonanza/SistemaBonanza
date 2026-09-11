<?php

    class CategoriaModel{
        private $pdo;

        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        public function getAll(){   //Funcion para obtener todas las categorias.
            $stmt = $this->pdo->query("SELECT * FROM categorias  ORDER BY nombre ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getById($id){ //funcion para obtener una categoria por su id.
            $sql = "SELECT id_categoria as id, nombre, descripcion FROM categorias WHERE id_categoria = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function createCategoria($nombre, $descripcion){

        try{
            //sentencia para crear la nueva categoria..
            $sql = "INSERT INTO categorias (nombre, descripcion) Values (:nombre, :descripcion)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ]);

                return true; //Categoria Creada exitosamente.

            } catch(PDOException $e){
            //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
                echo "error en la Base de Datos " . $e->getMessage();
                return false; //Error al crear la categoria.
        }
    }

    public function updateCategoria($id, $nombre, $descripcion){
        //sentencia para actualizar la categoria.
        $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id_categoria";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_categoria' => $id,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
        ]);
    }
}

?>