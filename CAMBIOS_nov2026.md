# Cambios – noviembre 2026

## Instalación (en este orden)
1. Respalda la base de datos.
2. Ejecuta `migracion_mejoras_nov2026.sql` una sola vez (es repetible; usa IF NOT EXISTS). Sin esto, la tabla de productos, precios, compras y ventas fallarán porque usan columnas nuevas.
3. Copia los archivos de este proyecto sobre el sistema.
4. El servidor necesita salida a internet (cURL o allow_url_fopen) para consultar la tasa BCV. Si no la tiene, el administrador puede escribirla con el botón "Fijar manual".

## Qué se hizo
1. **Banners**: todos los banners azules usan el patrón de Categorías (mismo degradado, icono de 60 px, título + etiqueta, descripción y botones). Se añadió banner a Ventas, Presentaciones y Nueva Presentación. Reportes ahora carga Material Symbols.
2. **Stock mínimo**: editable en Editar producto (y en Crear producto). 0 = sin alerta.
3. **Tasa BCV**: `TasaController` / `tasaModel.php` / `tasaBcv.js`. Una consulta al día, guardada en `tasas_cambio`; cada compra y venta guarda la tasa usada. Montos en USD y Bs en Compras, Ventas, detalle de venta, detalle de compra y Movimientos.
4. **Precio por cantidad**: tarjeta nueva en Precios > Actualizar precio (guarda por AJAX). Se aplica en Ventas (descuento al precio unitario, línea y total), se ve en la tabla maestra, SweetAlert al alcanzar una escala. Tabla maestra: filas en rojo y alerta SweetAlert cuando stock < mínimo.
7. **Unidades del producto**: Editar producto permite cambiar "Unidad de manejo" y "Unidades por empaque" (antes el panel decía siempre "Paquete 12 Unidades").
8. **Registrar compra**: botón "Crear producto nuevo" (modal por AJAX; el producto queda seleccionado).
9. **Movimientos**: arreglado el enlace "Ver detalle" de compras (la acción no existía) con la nueva Nota de Recepción; indicadores del mes corregidos (entradas solo por compras y a costo, salidas solo de facturas vigentes, stock crítico con el mínimo real).
10. **Crédito eliminado**: ventas, constantes, reportes de cierre de caja e historial de cierres.
11. **Ajax**: `assets/js/ajax.js` (formularios POST con `data-ajax`, listados con filtros `data-ajax-lista`). Aplicado a Categorías, Clientes, Proveedores, Usuarios, Productos, Presentaciones, Precios, Ventas y Reportes. `redirigir()` responde JSON a las peticiones AJAX.

## Errores previos corregidos al pasar
- Crear producto enviaba a `action=guardar` (ruta inexistente) → ahora `guardarProducto`.
- Inactivar categoría y cambiar estado de proveedor eran enlaces GET sin ruta → ahora POST + token + AJAX.
- Botón "Limpiar" de Ventas fallaba (`tbody` no definido).
- "Solo imprimir" de Ventas decía que no afectaba el Kardex, pero registra la venta; el mensaje se corrigió.
- Enlaces rotos del Dashboard (Nueva Entrada, Nuevo producto, Editar).
