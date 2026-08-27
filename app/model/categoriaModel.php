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

        public function getActivos(){ //funcion para obtener todas las categorias activas.
            $stmt = $this->pdo->query("SELECT id_categoria, nombre FROM categorias WHERE estado = 1 ORDER BY nombre ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
    

        public function crearCategoria($nombre, $descripcion){

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
}

?>