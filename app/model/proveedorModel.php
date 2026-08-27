<?php

    class ProveedorModel{
        private $pdo;

        public function __construct($pdo){
            $this->pdo = $pdo;
    }

    public function getAll(){  //Funcion para obtener todos los proveedores..
        $stmt = $this->pdo->query("SELECT * FROM proveedores ORDER BY razon_social ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getActivos(){ //funcion para obtener todos los proveedores activos.
        $stmt = $this->pdo->query("SELECT id_proveedor, razon_social FROM proveedores WHERE estado = 1 ORDER BY razon_social ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function crearProveedor($rif, $razon_social, $nombre_contacto, $telefono, $email, $direccion){

            try{
                //sentencia para crear el nuevo proveedor.
                $sql = "INSERT INTO proveedores (rif,razon_social,nombre_contacto,telefono,email,direccion) VALUES (:rif, :razon_social, :nombre_contacto, :telefono, :email, :direccion)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':rif' => $rif,
                    ':razon_social'=> $razon_social,
                    ':nombre_contacto' => $nombre_contacto,
                    ':telefono' => $telefono,
                    ':email' => $email,
                    ':direccion' => $direccion,
                ]);

                return true; //proveedor creado exitosamente.
            
            } catch(PDOException $e){
                //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
                echo "error en la Base de Datos " . $e->getMessage();
                return false; //Error al crear el proveedor.
            }
        }
    }

?>