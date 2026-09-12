<?php
Class clienteModel{
private $pdo;
public function __construct($pdo){
    $this->pdo = $pdo;
}

public function getAll(){   //Funcion para obtener todos los clientes.
    $stmt = $this->pdo->query("SELECT * FROM clientes  ORDER BY nombre ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getById($id){ //funcion para obtener un cliente por su id.
    $sql = "SELECT id_cliente as id, nombre, cedula, telefono, direccion FROM clientes WHERE id_cliente = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function createcliente($nombre, $cedula, $telefono, $direccion){

            try{
                //sentencia para crear el nuevo proveedor.
                $sql = "INSERT INTO clientes (nombre, cedula, telefono, direccion) VALUES (:nombre, :cedula, :telefono, :direccion)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':cedula' => $cedula,
                    ':telefono' => $telefono,
                    ':direccion' => $direccion,
                ]);

                return true; //cliente creado exitosamente.
            
            }catch(PDOException $e){
                //EN PRODUCCION ESTO IRIA A UN ARCHIVO DE TEXTO (LOG)
                echo "error en la Base de Datos " . $e->getMessage();
                return false; //Error al crear el cliente.
            }
        }


public function updatecliente($id, $nombre, $cedula, $telefono, $direccion){
    try{
        //sentencia para actualizar el cliente.
        $sql = "UPDATE clientes SET nombre = :nombre, cedula = :cedula, telefono = :telefono, direccion = :direccion WHERE id_cliente = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':cedula' => $cedula,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
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