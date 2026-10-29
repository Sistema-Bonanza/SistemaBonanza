document.addEventListener('DOMContentLoaded', () => {
    const formCrearCompra = document.getElementById('formCrearCompra');
    const tbodyDetalles = document.getElementById('tbodyDetalles');
    const btnResetForm = document.getElementById('btnResetForm');
    const btnAgregarFila = document.getElementById('btnAgregarFila'); // Botón de nueva fila
    
    // Labels de Resumen
    const totalEmpaquesLabel = document.getElementById('totalEmpaquesLabel');
    const subtotalLabel = document.getElementById('subtotalLabel');
    const ivaLabel = document.getElementById('ivaLabel');
    const grandTotalLabel = document.getElementById('grandTotalLabel');

    const tasaIvaPorcentaje = typeof window.tasaIva !== 'undefined' ? window.tasaIva : 16.0;

    // Formateador Numérico en Dólares ($)
    const formatearMoneda = (monto) => {
        return '$ ' + parseFloat(monto || 0).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    // ==========================================
    // CÁLCULO EN TIEMPO REAL DE SUBTOTALES
    // ==========================================
    const calcularTotales = () => {
        let subtotalAcumulado = 0;
        let totalEmpaques = 0;

        const filas = tbodyDetalles.querySelectorAll('.fila-detalle');

        filas.forEach((fila) => {
            const qtyInput = fila.querySelector('.input-cantidad');
            const costInput = fila.querySelector('.input-costo');
            const subtotalText = fila.querySelector('.text-subtotal');

            const cant = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const costo = parseFloat(costInput ? costInput.value : 0) || 0;

            const subtotalLinea = cant * costo;
            
            if (subtotalText) {
                subtotalText.textContent = formatearMoneda(subtotalLinea);
                if (subtotalLinea > 0) {
                    subtotalText.classList.add('has-text-link');
                } else {
                    subtotalText.classList.remove('has-text-link');
                }
            }

            subtotalAcumulado += subtotalLinea;
            totalEmpaques += cant;
        });

        const montoIva = subtotalAcumulado * (tasaIvaPorcentaje / 100);
        const grandTotal = subtotalAcumulado + montoIva;

        if (totalEmpaquesLabel) totalEmpaquesLabel.textContent = totalEmpaques.toLocaleString('es-VE') + ' unidades';
        if (subtotalLabel) subtotalLabel.textContent = formatearMoneda(subtotalAcumulado);
        if (ivaLabel) ivaLabel.textContent = formatearMoneda(montoIva);
        if (grandTotalLabel) grandTotalLabel.textContent = formatearMoneda(grandTotal);
    };

    // ==========================================
    // ESCUCHADORES DE LA TABLA (ELIMINAR Y CALCULAR)
    // ==========================================
    if (tbodyDetalles) {
        // Recalcular montos al tipear
        tbodyDetalles.addEventListener('input', (e) => {
            if (e.target.classList.contains('input-cantidad') || e.target.classList.contains('input-costo')) {
                calcularTotales();
            }
        });

        // Eliminar fila físicamente
        tbodyDetalles.addEventListener('click', (e) => {
            const btnEliminar = e.target.closest('.btn-eliminar-fila');
            if (btnEliminar) {
                const fila = btnEliminar.closest('.fila-detalle');
                const totalFilas = tbodyDetalles.querySelectorAll('.fila-detalle').length;

                if (totalFilas > 1) {
                    fila.remove(); // Desaparece la fila del HTML
                    
                    // Re-enumerar la columna "#" y arreglar IDs de las que quedaron
                    const filasRestantes = tbodyDetalles.querySelectorAll('.fila-detalle');
                    filasRestantes.forEach((f, idx) => {
                        f.querySelector('.numero-fila').textContent = idx + 1;
                        f.dataset.index = idx;
                        f.querySelector('.btn-modal-emp').setAttribute('onclick', `abrirModalPresentacion(${idx})`);
                        
                        f.querySelector('.select-producto').id = `id_producto_${idx}`;
                        f.querySelector('.select-empaque').id = `tipo_empaque_${idx}`;
                        f.querySelector('.input-cantidad').id = `cantidad_empaques_${idx}`;
                        f.querySelector('.input-costo').id = `costo_por_empaque_${idx}`;
                    });
                } else {
                    // Si es la última fila que queda, solo limpiamos los campos
                    fila.querySelector('.select-producto').value = '';
                    fila.querySelector('.select-empaque').value = '';
                    fila.querySelector('.input-cantidad').value = '';
                    fila.querySelector('.input-costo').value = '';
                }
                
                calcularTotales();
            }
        });
    }

    // ==========================================
    // AGREGAR NUEVAS FILAS INFINITAS
    // ==========================================
    if (btnAgregarFila) {
        btnAgregarFila.addEventListener('click', () => {
            const filas = tbodyDetalles.querySelectorAll('.fila-detalle');
            const ultimaFila = filas[filas.length - 1];
            
            // Clonamos la última fila 
            const nuevaFila = ultimaFila.cloneNode(true);
            const nuevoIndex = filas.length; // Índice para la nueva (arranca desde 0)

            // Actualizar identificadores
            nuevaFila.dataset.index = nuevoIndex;
            nuevaFila.querySelector('.numero-fila').textContent = nuevoIndex + 1;

            // Limpiar los inputs y selects de la fila clonada y actualizar sus IDs
            const selectProd = nuevaFila.querySelector('.select-producto');
            selectProd.id = `id_producto_${nuevoIndex}`;
            selectProd.value = '';

            const selectEmp = nuevaFila.querySelector('.select-empaque');
            selectEmp.id = `tipo_empaque_${nuevoIndex}`;
            selectEmp.value = '';

            const btnModal = nuevaFila.querySelector('.btn-modal-emp');
            btnModal.setAttribute('onclick', `abrirModalPresentacion(${nuevoIndex})`);

            const inputCant = nuevaFila.querySelector('.input-cantidad');
            inputCant.id = `cantidad_empaques_${nuevoIndex}`;
            inputCant.value = '';

            const inputCosto = nuevaFila.querySelector('.input-costo');
            inputCosto.id = `costo_por_empaque_${nuevoIndex}`;
            inputCosto.value = '';

            const subtotalText = nuevaFila.querySelector('.text-subtotal');
            subtotalText.textContent = '$ 0,00';
            subtotalText.classList.remove('has-text-link');

            // Pegamos la nueva fila al final de la tabla
            tbodyDetalles.appendChild(nuevaFila);
        });
    }

    // ==========================================
    // CONFIRMACIÓN DE ENVÍO CON SWEETALERT2
    // ==========================================
    if (formCrearCompra) {
        formCrearCompra.addEventListener('submit', (e) => {
            e.preventDefault();

            const idProveedor = document.getElementById('id_proveedor').value;
            const numFactura = document.getElementById('numero_factura').value.trim();
            const fechaFactura = document.getElementById('fecha_factura').value;

            // Validación de Cabecera
            if (!idProveedor || !numFactura || !fechaFactura) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos Incompletos',
                    text: 'Por favor completa el proveedor, número de factura y fecha de emisión.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            // Validar que al menos exista 1 fila llena
            let filasLlenas = 0;
            const filas = tbodyDetalles.querySelectorAll('.fila-detalle');
            filas.forEach(f => {
                const p = f.querySelector('.select-producto').value;
                const c = parseFloat(f.querySelector('.input-cantidad').value || 0);
                const cost = parseFloat(f.querySelector('.input-costo').value || 0);

                if (p && c > 0 && cost > 0) {
                    filasLlenas++;
                }
            });

            if (filasLlenas === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin Productos',
                    text: 'Debes incluir al menos un producto completo (Producto, Cantidad y Costo) en la factura.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            Swal.fire({
                title: '¿Procesar Entrada de Inventario?',
                text: 'Verifica los montos. Al asentar la compra, los precios y stock del Kardex se actualizarán de inmediato.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#4a4a4a',
                confirmButtonText: 'Sí, Procesar Kardex',
                cancelButtonText: 'Revisar'
            }).then((result) => {
                if (result.isConfirmed) {
                    formCrearCompra.submit();
                }
            });
        });
    }

    // ==========================================
    // BOTÓN DE LIMPIAR FORMULARIO
    // ==========================================
    if (btnResetForm) {
        btnResetForm.addEventListener('click', () => {
            Swal.fire({
                title: '¿Reiniciar Formulario?',
                text: 'Se borrarán todos los datos ingresados en la pantalla.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f14668',
                cancelButtonColor: '#4a4a4a',
                confirmButtonText: 'Sí, Limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    formCrearCompra.reset();
                    // Al limpiar, removemos todas las filas extra y dejamos solo la primera
                    const filas = tbodyDetalles.querySelectorAll('.fila-detalle');
                    if (filas.length > 1) {
                        for (let i = 1; i < filas.length; i++) {
                            filas[i].remove();
                        }
                    }
                    calcularTotales();
                }
            });
        });
    }

    // Inicializar Totales al Cargar
    calcularTotales();
});

// ==========================================
// FUNCIONES PARA EL MODAL DE PRESENTACIÓN AJAX
// ==========================================
let filaIniciadoraModal = null;

function abrirModalPresentacion(indexFila) {
    filaIniciadoraModal = indexFila;
    const modal = document.getElementById('modalPresentacion');
    if (modal) modal.classList.add('is-active');
}

function cerrarModalPresentacion() {
    const modal = document.getElementById('modalPresentacion');
    if (modal) modal.classList.remove('is-active');

    const form = document.getElementById('formNuevaPresentacion');
    if (form) form.reset();

    const err = document.getElementById('mensajeErrorModal');
    if (err) {
        err.classList.add('is-hidden');
        err.innerHTML = '';
    }
}

async function guardarPresentacionAjax() {
    const btnGuardar = document.getElementById('btnGuardarPresentacion');
    const mensajeError = document.getElementById('mensajeErrorModal');

    const idProducto = document.getElementById('producto_presentacion').value;
    const nombre = document.getElementById('nombre_presentacion').value.trim();
    const unidades = document.getElementById('unidades_presentacion').value;
    const csrfToken = document.getElementById('csrf_token_modal').value;

    if (!nombre || !unidades || unidades <= 0) {
        mensajeError.innerHTML = "Por favor ingresa un nombre y cantidad de unidades válidas.";
        mensajeError.classList.remove('is-hidden');
        return;
    }

    btnGuardar.classList.add('is-loading');
    mensajeError.classList.add('is-hidden');

    try {
        const formData = new FormData();
        formData.append('id_producto', idProducto);
        formData.append('nombre', nombre);
        formData.append('unidades', unidades);
        formData.append('csrf_token', csrfToken);

        const response = await fetch(window.urlGuardarPresentacion, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.ok) {
            // Inyectar la opción en todos los selects de la tabla
            const selectsEmpaques = document.querySelectorAll('select[name="tipo_empaque[]"]');
            selectsEmpaques.forEach((select, idx) => {
                const nuevaOpcion = document.createElement('option');
                nuevaOpcion.value = result.id_presentacion;
                nuevaOpcion.textContent = `${result.nombre} (${result.unidades} uds)`;

                select.appendChild(nuevaOpcion);

                // Si es la fila desde donde se presionó el botón +, se autoselecciona
                if (filaIniciadoraModal !== null && idx === filaIniciadoraModal) {
                    select.value = result.id_presentacion;
                }
            });

            cerrarModalPresentacion();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '¡Presentación creada e inyectada con éxito!',
                showConfirmButton: false,
                timer: 3000
            });

        } else {
            mensajeError.innerHTML = result.error || "Error al procesar la solicitud.";
            mensajeError.classList.remove('is-hidden');
        }

    } catch (error) {
        mensajeError.innerHTML = "Error de conexión con el servidor.";
        mensajeError.classList.remove('is-hidden');
    } finally {
        btnGuardar.classList.remove('is-loading');
    }
}