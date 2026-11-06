<?php
// 1. Importar la conexión y los modelos
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/model/proveedorModel.php';
require_once __DIR__ . '/app/model/productoModel.php';

// 2. Iniciar conexión
$database = new Database();
$pdo = $database->getConnection();

// 3. Instanciar los modelos
$proveedorModel = new ProveedorModel($pdo);
$productoModel = new ProductoModel($pdo);

// 4. Imprimir resultados en pantalla usando <pre> para darles formato legible
echo "<h3>Lista de Proveedores Activos:</h3>";
echo "<pre>";
print_r($proveedorModel->getActivos());
echo "</pre>";

echo "<hr>";

echo "<h3>Lista de Productos:</h3>";
echo "<pre>";
print_r($productoModel->obtenerProductos());
echo "</pre>";