<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - Bonanza</title>
    <!-- Incluyendo Bootstrap por CDN (si ya lo tienes local, ignora esta línea) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registro de Nuevo Producto</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Mostrar mensaje de error si faltan datos -->
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'faltan_datos'): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Atención!</strong> Por favor llene todos los datos obligatorios marcados con asterisco (*).
                        </div>
                    <?php endif; ?>

                    <!-- Formulario apuntando al controlador -->
                    <form action="index.php?controller=producto&action=guardarProducto" method="POST">
                        
                        <h5 class="text-secondary mb-3">Datos Indispensables</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código de Producto *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required autofocus placeholder="Ej. PROD-001">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej. Harina Pan 1Kg">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_categoria" class="form-label">Categoría *</label>
                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                    <option value="">-- Seleccione una Categoría --</option>
                                    <!-- Iteramos las categorías traídas por el controlador -->
                                    <?php if (!empty($categorias)): ?>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['id_categoria']) ?>">
                                                <?= htmlspecialchars($cat['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay categorías registradas</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="id_proveedor" class="form-label">Proveedor *</label>
                                <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                                    <option value="">-- Seleccione un Proveedor --</option>
                                    <!-- Iteramos los proveedores traídos por el controlador -->
                                    <?php if (!empty($proveedores)): ?>
                                        <?php foreach ($proveedores as $prov): ?>
                                            <!-- Nota: Asumo que en la tabla proveedores el nombre está en 'razon_social' -->
                                            <option value="<?= htmlspecialchars($prov['id_proveedor']) ?>">
                                                <?= htmlspecialchars($prov['razon_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay proveedores registrados</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h5 class="text-secondary mb-3">Datos Adicionales</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="unidad_medida" class="form-label">Unidad de Medida</label>
                                <select class="form-select" id="unidad_medida" name="unidad_medida">
                                    <option value="UNIDAD">Unidad</option>
                                    <option value="KG">Kilogramos (KG)</option>
                                    <option value="BULTOS">Bultos</option>
                                    <option value="LITROS">Litros (L)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="unidades_por_empaque" class="form-label">Unidades por Empaque</label>
                                <input type="number" class="form-control" id="unidades_por_empaque" name="unidades_por_empaque" min="1" placeholder="Ej. 24">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" placeholder="Ej. 10">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="precio_compra" class="form-label">Precio de Compra ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" placeholder="0.00">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="precio_venta" class="form-label">Precio de Venta ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" placeholder="0.00">
                            </div>

                            <div class="col-md-4 mb-3 d-flex align-items-end pb-2">
                                <div class="form-check">
                                    <!-- Si se marca, enviará 'on'. El controlador validará con isset() -->
                                    <input class="form-check-input" type="checkbox" id="aplica_iva" name="aplica_iva" checked>
                                    <label class="form-check-label" for="aplica_iva">
                                        ¿Aplica IVA?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="index.php?controller=producto&action=TablaProductos" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Producto</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>