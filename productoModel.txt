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
                    prod.stock_actual,
                    prod.precio_compra,
                    prod.precio_venta,
                    prod.aplica_iva
                    FROM productos prod
                    INNER JOIN categorias c ON prod.id_categoria = c.id_categoria
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
        
    public function crearProducto($codigo, $nombre, $id_categoria, $aplica_iva, $stock_minimo = 0, $stock_actual = 0, $precio_compra = 0.00, $precio_venta = 0.00){
        try{
                $sql = "INSERT INTO productos (codigo, nombre, id_categoria, aplica_iva, stock_minimo, stock_actual, precio_compra, precio_venta) 
                        VALUES 
                        (:codigo, :nombre, :id_categoria, :aplica_iva, :stock_minimo, :stock_actual, :precio_compra, :precio_venta)";

                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([
                            ':codigo' => $codigo,
                            ':nombre' => $nombre,
                            ':id_categoria' => $id_categoria,
                            ':aplica_iva' => $aplica_iva,
                            ':stock_minimo' => $stock_minimo,
                            ':stock_actual' => $stock_actual,
                            ':precio_compra' => $precio_compra,
                            ':precio_venta' => $precio_venta,
                            
                                
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
    public function actualizarProducto($id_producto, $codigo, $nombre, $id_categoria,$aplica_iva){
        try{
            $sql = "UPDATE productos SET
                        codigo = :codigo, 
                        nombre = :nombre, 
                        id_categoria = :id_categoria, 
                        aplica_iva = :aplica_iva 
                    WHERE id_producto = :id_producto";
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_producto'          => $id_producto,
                ':codigo'               => $codigo,
                ':nombre'               => $nombre,
                ':id_categoria'         => $id_categoria,
                ':aplica_iva'           => $aplica_iva
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

    //==================================================================================
    // ¿YA EXISTE UN PRODUCTO CON ESE CÓDIGO? (LA COLUMNA TIENE UNIQUE)
    // $excluirId SIRVE AL EDITAR: EL PROPIO PRODUCTO NO CUENTA COMO DUPLICADO.
    public function existeCodigo($codigo, $excluirId = null){
        $sql = "SELECT COUNT(*) FROM productos WHERE codigo = :codigo";
        $params = [':codigo' => $codigo];
        if ($excluirId !== null) {
            $sql .= " AND id_producto <> :id";
            $params[':id'] = (int) $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
    //==================================================================================


    // ============================================================================================================
    // REGISTRO DE PRESENTACIONES (EMPAQUES)
    // ============================================================================================================
    public function crearPresentacion($id_producto, $etiqueta, $unidades, $id_usuario) {
        try {
            $sql = "INSERT INTO presentaciones (id_producto, etiqueta, unidades, id_usuario) 
                    VALUES (:id_producto, :etiqueta, :unidades, :id_usuario)";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Forzamos la etiqueta a mayúsculas (y quitamos espacios extra) para evitar duplicados por errores de tipeo
            $stmt->execute([
                ':id_producto' => $id_producto,
                ':etiqueta'    => strtoupper(trim($etiqueta)), 
                ':unidades'    => $unidades,
                ':id_usuario'  => $id_usuario
            ]);

            // Devolvemos el éxito y el ID autogenerado
            return [
                'ok' => true,
                'id_presentacion' => $this->pdo->lastInsertId()
            ];

        } catch (PDOException $e) {
            // El código 1062 en MySQL identifica la violación de una restricción UNIQUE 
            // Esto se activa automáticamente por tu CONSTRAINT uq_present_producto_etiqueta
            if ($e->errorInfo[1] == 1062) {
                return ['ok' => false, 'motivo' => 'duplicado'];
            }
            
            // Cualquier otro error de base de datos se registra silenciosamente en el log
            error_log("Error al crear presentación: " . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error_interno'];
        }
    }

    // ============================================================================================================
    // OBTENER TODAS LAS PRESENTACIONES (PARA LLENAR EL FORMULARIO)
    // ============================================================================================================
    public function obtenerTodasLasPresentacionesActivas() {
        // Usamos LEFT JOIN y COALESCE: Si no tiene producto, dice "ESTÁNDAR GLOBAL"
        $sql = "SELECT p.id_presentacion, p.etiqueta, p.unidades, 
                COALESCE(prod.nombre, '★ ESTÁNDAR GLOBAL ★') as nombre_producto 
                FROM presentaciones p
                LEFT JOIN productos prod ON p.id_producto = prod.id_producto
                WHERE p.estado = 1
                ORDER BY p.id_producto IS NULL DESC, p.etiqueta ASC";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================================================================
    // VALIDAR QUE UNA PRESENTACIÓN EXISTE Y PERTENECE AL PRODUCTO SELECCIONADO
    // ============================================================================================================
    public function obtenerPresentacionValida($id_presentacion, $id_producto) {
        // Ahora es válida si es Específica (id_producto = ?) O si es Global (id_producto IS NULL)
        $sql = "SELECT id_presentacion, etiqueta, unidades 
                FROM presentaciones 
                WHERE id_presentacion = ? AND (id_producto = ? OR id_producto IS NULL) AND estado = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_presentacion, $id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
    ?>
