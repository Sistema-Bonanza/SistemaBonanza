<?php
Class clienteModel{
private $pdo;
public function __construct($pdo){
    $this->pdo = $pdo;
}

public function getAll(){   //Funcion para obtener todos los clientes.
    $sql = "SELECT id_cliente, tipo_documento, num_documento, 
                       nombre_razon_social, telefono, email, direccion, estado, creado_en
                FROM clientes 
                WHERE estado = 1 
                ORDER BY nombre_razon_social ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getById($id){ //funcion para obtener un cliente por su id.
    $sql = "SELECT id_cliente as id, tipo_documento, num_documento,
     nombre_razon_social, telefono, email, direccion, estado, creado_en
     FROM clientes WHERE id_cliente = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function createcliente($tipo_documento, $num_documento, $nombre_razon_social, $telefono, $email, $direccion){

            try{
                //sentencia para crear el nuevo proveedor.
                $sql = "INSERT INTO clientes (tipo_documento, num_documento, nombre_razon_social, telefono, email, direccion) VALUES (:tipo_documento, :num_documento, :nombre_razon_social, :telefono, :email, :direccion)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':tipo_documento' => $tipo_documento,
                    ':num_documento' => $num_documento,
                    ':nombre_razon_social' => $nombre_razon_social,
                    ':telefono' => $telefono,
                    ':email' => $email,
                    ':direccion' => $direccion,
                ]);

                return true; //cliente creado exitosamente.
            
            }catch(PDOException $e){
                //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
                echo "error en la Base de Datos " . $e->getMessage();
                return false; //Error al crear el cliente.
            }
        }


public function updatecliente($id, $tipo_documento, $nombre_razon_social, $num_documento, $telefono,
 $email, $direccion, $estado, $creado_en){
    try{
        //sentencia para actualizar el cliente.
        $sql = "UPDATE clientes SET tipo_documento = :tipo_documento, nombre_razon_social = :nombre_razon_social, num_documento = :num_documento,
         telefono = :telefono, email = :email, direccion = :direccion, estado = :estado, creado_en = :creado_en
          WHERE id_cliente = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':tipo_documento' => $tipo_documento,
            ':nombre_razon_social' => $nombre_razon_social,
            ':num_documento' => $num_documento,
            ':telefono' => $telefono,
            ':email' => $email,
            ':direccion' => $direccion,
            ':estado' => $estado,
            ':creado_en' => $creado_en
        ]);

        return true; //cliente actualizado exitosamente.
    
    }catch(PDOException $e){
        //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
        echo "error en la Base de Datos " . $e->getMessage();
        return false; //Error al actualizar el cliente.
    }
}

}
?>