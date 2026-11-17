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
                    ORDER BY id_cliente ASC";
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

    // ¿Ya existe un cliente con ese número de documento? (la columna tiene UNIQUE)
    // $excluirId sirve al EDITAR: el propio cliente no cuenta como duplicado.
    public function existeDocumento($num_documento, $excluirId = null){
        $sql = "SELECT COUNT(*) FROM clientes WHERE num_documento = :num";
        $params = [':num' => $num_documento];
        if ($excluirId !== null) {
            $sql .= " AND id_cliente <> :id";
            $params[':id'] = (int) $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function cambiarEstado(int $id): bool {
            $sql = "UPDATE clientes 
                    SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END 
                    WHERE id_cliente = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        }

        public function obtenerResumenVentas(int $id): array {
    $sql = "SELECT COUNT(v.id_venta) AS total_compras,
                   MAX(v.fecha_venta) AS ultima_compra,
                   COALESCE(SUM(v.total), 0) AS total_facturado
            FROM clientes c
            LEFT JOIN ventas v
                   ON c.id_cliente = v.id_cliente
                  AND v.estado = 1
            WHERE c.id_cliente = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'total_compras'   => (int) ($resumen['total_compras'] ?? 0),
        'ultima_compra'   => $resumen['ultima_compra'] ?? null,
        'total_facturado' => (float) ($resumen['total_facturado'] ?? 0),
    ];
}
}
?>