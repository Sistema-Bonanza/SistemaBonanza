<?php
Class productoModel{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }
    //==================================================================================
        //OBTENER TODOS LOS PRODUCTOS


    public function obtenerProductos(){

        //USAMOS APODOS (C = CATEGORIA,PROD = PRODUCTO Y PROV = PROVEEDORES ) PARA SIMPLIFICAR LA CONSULTA Y HACERLA MAS LEGIBLE Y AS PARA
        //CAMBIAR NOMBRES DE COLUMNAS CON NOMBRES IGUALES EN DIFERENTES TABLAS.

        $sql = "SELECT
                    prod.id_producto,
                    prod.codigo,
                    prod.nombre,
                    c.nombre AS nombre_categoria,
                    prov.razon_social AS razon_social_proveedor,
                    prod.stock_actual,
                    prod.precio_venta
                    FROM productos prod
                    INNER JOIN categorias c ON prod.id_categoria = c.id_categoria
                    INNER JOIN proveedores prov ON prod.id_proveedor = prov.id_proveedor
                    WHERE prod.estado = 1 
                    ORDER BY prod.id_producto DESC";
                    // SE UTILIZA 1 PARA ESPECIFICAR QUE SOLO SE OBTIENEN LOS PRODUCTOS ACTIVOS (ESTADO = 1) Y SE ORDENA POR ID DE PRODUCTO EN ORDEN DESCENDENTE.
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // RETORNA UN ARREGLO ASOCIATIVO CON TODOS LOS PRODUCTOS OBTENIDOS DE LA CONSULTA.
    }

    //==================================================================================


    //==================================================================================
        //OBTENER PRODUCTOS POR ID 

    public function obtenerProductoPorId($id_producto){
        $sql = "SELECT * FROM productos WHERE id_producto = :id_producto"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
    //=================================================================================


    //==================================================================================
        //CREAR PRODUCTOS   
        
    public function crearProducto($codigo, $nombre, $id_categoria, $id_proveedor, $unidad_medida, $unidades_por_empaque, $precio_compra, $precio_venta, $aplica_iva, $stock_minimo){
        try{
                $sql = "INSERT INTO productos (codigo, nombre, id_categoria, id_proveedor, unidad_medida, unidades_por_empaque, precio_compra, precio_venta, aplica_iva, stock_minimo) 
                        VALUES 
                        (:codigo, :nombre, :id_categoria, :id_proveedor, :unidad_medida, :unidades_por_empaque, :precio_compra, :precio_venta, :aplica_iva, :stock_minimo)";

                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([
                            ':codigo' => $codigo,
                            ':nombre' => $nombre,
                            ':id_categoria' => $id_categoria,
                            ':id_proveedor' => $id_proveedor,
                            ':unidad_medida' => $unidad_medida,
                            ':unidades_por_empaque' => $unidades_por_empaque,
                            ':precio_compra' => $precio_compra,
                            ':precio_venta' => $precio_venta,
                            ':aplica_iva' => $aplica_iva,
                            ':stock_minimo' => $stock_minimo
                        ]);

                        return true; //SE GUARDÓ CORRECTAMENTE.
            } catch(PDOException $e){
                //SI OCURRE ERROR EL CATCH ATRAPA EL ERROR Y EVITA EL COLAPSO DEL SISTEMA.
                echo "Error al crear el producto: " . $e->getMessage();
                return false;
            }
    }
    //==================================================================================


    //==================================================================================
    public function actualizarProducto($id_producto, $codigo, $nombre, $id_categoria, $id_proveedor, $unidad_medida, $unidades_por_empaque, $precio_compra, $precio_venta, $aplica_iva, $stock_minimo){
        try{
            $sql = "UPDATE productos SET
                        codigo = :codigo, 
                        nombre = :nombre, 
                        id_categoria = :id_categoria, 
                        id_proveedor = :id_proveedor, 
                        unidad_medida = :unidad_medida, 
                        unidades_por_empaque = :unidades_por_empaque, 
                        precio_compra = :precio_compra, 
                        precio_venta = :precio_venta, 
                        aplica_iva = :aplica_iva, 
                        stock_minimo = :stock_minimo 
                    WHERE id_producto = :id_producto";
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_producto'          => $id_producto,
                ':codigo'               => $codigo,
                ':nombre'               => $nombre,
                ':id_categoria'         => $id_categoria,
                ':id_proveedor'         => $id_proveedor,
                ':unidad_medida'        => $unidad_medida,
                ':unidades_por_empaque' => $unidades_por_empaque,
                ':precio_compra'        => $precio_compra,
                ':precio_venta'         => $precio_venta,
                ':aplica_iva'           => $aplica_iva,
                ':stock_minimo'         => $stock_minimo
            ]);
            
            return true;

        } catch(PDOException $e) {
            echo "Error al actualizar el producto: " . $e->getMessage();
            return false;
        }

    }
    //==================================================================================


    //==================================================================================
    public function eliminarProducto($id_producto){
        try{
            //No se borrara registros de la base de dato, se hara un borrado logico al acualizar el estatus del producto a 0 (inactivo)
            //asi no se borra los datos de facturas antiguas con los productos fuera del disponibilidad.
            $sql = "UPDATE productos SET estado = 0 WHERE id_producto = :id_producto";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            return true;

        } catch (PDOException $e){
            echo "Error al eliminar el producto: " . $e->getMessage();
            return false;
        }
    }
    //==================================================================================
}
    ?>