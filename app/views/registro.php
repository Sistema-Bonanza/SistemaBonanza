<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - Productos</title>
</head>
<link rel="stylesheet" href="/../../Xampp/htdocs/refactorizar/app/Vista/style.css">
<body>

    <h1>Gestión de Inventario (Módulo Productos)</h1>

    <!-- FORMULARIO DE REGISTRO -->
    <h3>Registrar Nuevo Producto</h3>
    <form action="index.php?controller=tienda&action=almacenar" method="POST">
        <input type="text" name="nombre" placeholder="Nombre del producto" required>
        <br><br>
        <textarea name="descripcion" placeholder="Descripción brevedad" rows="3" required></textarea>
        <br><br>
        <input type="number" step="0.01" name="precio" placeholder="Precio (ej: 19.99)" required>
        <input type="number" name="stock" placeholder="Cantidad en Stock" required>
        <br><br>
        <button type="submit" name="guardar_producto">Guardar Producto</button>
    </form>

    </body>
</html>
