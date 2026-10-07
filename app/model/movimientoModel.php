<?php 

Class movimientoModel {
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

//==================================================================================
        //OBTENER TODOS LOS PRODUCTOS
    public function obtenerCompras(){
        try{

            $sql = "SELECT
                        c.id_compra, 
                        c.numero_factura, 
                        c.fecha_factura, 
                        c.total, 
                        c.estado,
                        p.nombre AS nombre_proveedor, 
                        u.usuario AS nombre_usuario
                    FROM compras c
                    INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                    INNER JOIN usuarios u ON c.id_usuario = u.id
                    ORDER BY c.fecha_registro DESC";
            
            $stmt =$this->pdo->prepare($sql);$stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // RETORNA UN ARREGLO ASOCIATIVO CON TODOS LOS PRODUCTOS OBTENIDOS DE LA CONSULTA.

        } catch(PDOException $e){
            error_log("Error en obtenerCompras: " . $e->getMessage());
            return [];
        }
    } 
    //==================================================================================

    public function registrarCompraCompleta($cabecera, $detalles) {
    try {
        $this->pdo->beginTransaction();

        // 1. Insertar Cabecera
        $sqlCompra = "INSERT INTO compras 
            (id_proveedor, numero_factura, fecha_factura, subtotal, tasa_iva, monto_iva, total, id_usuario, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmtCompra = $this->pdo->prepare($sqlCompra);
        $stmtCompra->execute([
            $cabecera['id_proveedor'],
            $cabecera['numero_factura'],
            $cabecera['fecha_factura'],
            $cabecera['subtotal'],
            $cabecera['tasa_iva'],
            $cabecera['monto_iva'],
            $cabecera['total'],
            $cabecera['id_usuario']
        ]);
        
        $id_compra = $this->pdo->lastInsertId();

        // 2. Preparar consultas para el ciclo
        $sqlDetalle = "INSERT INTO detalle_compras 
            (id_compra, id_producto, tipo_empaque, cantidad_empaques, unidades_por_empaque, cantidad_unidades, costo_por_empaque, costo_unitario, aplica_iva, subtotal_linea) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtDetalle = $this->pdo->prepare($sqlDetalle);

        $sqlStockActual = "SELECT stock FROM productos WHERE id_producto = ? FOR UPDATE";
        $stmtStockActual = $this->pdo->prepare($sqlStockActual);

        $sqlKardex = "INSERT INTO movimientos 
            (id_producto, tipo, cantidad, stock_antes, stock_despues, motivo, id_compra, id_usuario) 
            VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?)";
        $stmtKardex = $this->pdo->prepare($sqlKardex);

        $sqlActualizarStock = "UPDATE productos SET stock = ? WHERE id_producto = ?";
        $stmtActualizarStock = $this->pdo->prepare($sqlActualizarStock);

        // 3. Ejecutar ciclo por cada producto válido
        foreach ($detalles as $det) {
            
            $stmtDetalle->execute([
                $id_compra,
                $det['id_producto'],
                $det['tipo_empaque'],
                $det['cantidad_empaques'],
                $det['unidades_por_empaque'],
                $det['cantidad_unidades'],
                $det['costo_por_empaque'],
                $det['costo_unitario'],
                $det['aplica_iva'],
                $det['subtotal_linea']
            ]);

            $stmtStockActual->execute([$det['id_producto']]);
            $filaProd = $stmtStockActual->fetch(PDO::FETCH_ASSOC);
            $stock_antes = $filaProd['stock'] ?? 0;
            $stock_despues = $stock_antes + $det['cantidad_unidades'];

            $motivo = "Compra Factura #" . $cabecera['numero_factura'];
            $stmtKardex->execute([
                $det['id_producto'],
                $det['cantidad_unidades'],
                $stock_antes,
                $stock_despues,
                $motivo,
                $id_compra,
                $cabecera['id_usuario']
            ]);

            $stmtActualizarStock->execute([$stock_despues, $det['id_producto']]);
        }

        $this->pdo->commit();
        return true;

    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Fallo en registrarCompraCompleta: " . $e->getMessage());
        return false;
    }
}

}
