document.addEventListener('DOMContentLoaded', () => {
    // ============================================================
    // 1. INTERCEPTOR DE ALERTAS FLASH (SweetAlert2)
    // ============================================================
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '' && typeof Swal !== 'undefined') {
        const textoMensaje = cajaFlash.innerText.trim();
        const aviso = cajaFlash.querySelector('.mensaje');

        let tipo = 'success';
        if (aviso && aviso.classList.contains('mensaje-error')) tipo = 'error';
        else if (aviso && aviso.classList.contains('mensaje-aviso')) tipo = 'warning';

        const titulos = {
            success: '¡Operación exitosa!',
            error: 'No se pudo completar',
            warning: 'Aviso'
        };

        Swal.fire({
            icon: tipo,
            title: titulos[tipo],
            text: textoMensaje,
            confirmButtonColor: '#2563eb',
            timer: tipo === 'error' ? undefined : 4000,
            timerProgressBar: tipo !== 'error'
        });

        cajaFlash.innerHTML = '';
    }

    // ============================================================
    // 2. TABLA DINÁMICA, BUSCADOR, FILTROS Y PAGINACIÓN
    // ============================================================
    const inputBusqueda = document.getElementById('input-busqueda');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const tbody         = document.getElementById('tbody-movimientos');
    const paginacion    = document.getElementById('contenedor-paginacion');

    let filtroActual = 'todas';
    let paginaActual = 1;
    let temporizadorBuscador;

    const cargarMovimientos = async () => {
        const termino = inputBusqueda ? inputBusqueda.value.trim() : '';

        // Capturamos los parámetros actuales de la URL (proveedor y cliente si existen)
        const urlParamsActuales = new URLSearchParams(window.location.search);
        const proveedorUrl = urlParamsActuales.get('proveedor');
        const clienteUrl = urlParamsActuales.get('cliente');

        const params = new URLSearchParams({
            busqueda: termino,
            filtro: filtroActual,
            pagina: paginaActual
        });

        // Si el usuario seleccionó un proveedor o cliente, lo agregamos a la petición AJAX
        if (proveedorUrl) params.append('proveedor', proveedorUrl);
        if (clienteUrl) params.append('cliente', clienteUrl);

        try {
            const respuesta = await fetch(`${URL_BASE}/movimiento/buscarajax?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const textoRespuesta = await respuesta.text();
            let json;

            try {
                json = JSON.parse(textoRespuesta);
            } catch (e) {
                throw new Error('Respuesta no válida del servidor. Código HTTP: ' + respuesta.status);
            }

            if (!respuesta.ok) {
                throw new Error(json.error || json.message || 'Error interno del servidor');
            }

            if (tbody) tbody.innerHTML = json.filasHTML;
            if (paginacion) paginacion.innerHTML = json.paginacionHTML;

            const totalLabel = document.querySelector('#total-registros-label');
            if (totalLabel) totalLabel.textContent = json.totalRegistros;

        } catch (error) {
            console.error('Error capturado:', error);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="7" class="has-text-centered has-text-danger py-5">
                    <span class="material-symbols-outlined mb-2 is-size-3">error</span><br>
                    <strong>Error en la búsqueda:</strong> ${error.message}
                </td></tr>`;
            }
        }
    };

    if (inputBusqueda) {
        inputBusqueda.addEventListener('keyup', () => {
            clearTimeout(temporizadorBuscador);
            paginaActual = 1;
            temporizadorBuscador = setTimeout(cargarMovimientos, 400);
        });
    }

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', (e) => {
            botonesFiltro.forEach(b => b.classList.remove('is-link', 'is-selected'));
            const botonPresionado = e.currentTarget;
            botonPresionado.classList.add('is-link', 'is-selected');

            filtroActual = botonPresionado.getAttribute('data-filtro');
            paginaActual = 1;
            cargarMovimientos();
        });
    });

    if (paginacion) {
        paginacion.addEventListener('click', (e) => {
            const link = e.target.closest('.pagination-link, .pagination-previous, .pagination-next');
            if (link && !link.hasAttribute('disabled')) {
                paginaActual = link.getAttribute('data-pagina');
                cargarMovimientos();
            }
        });
    }

    // Carga inicial al entrar
    cargarMovimientos();

    // ============================================================
    // 3. MODAL EXPORTAR REPORTE PDF CON RANGO DE FECHAS
    // ============================================================
    const btnAbrirPdf       = document.getElementById('btn-abrir-exportar-pdf');
    const modalPdf          = document.getElementById('modalExportarPdf');
    const btnGenerarPdf     = document.getElementById('btn-generar-pdf-movimientos');
    const inputFechaDesde   = document.getElementById('pdf_fecha_desde');
    const inputFechaHasta   = document.getElementById('pdf_fecha_hasta');
    const btnMesActual      = document.getElementById('btn-pdf-mes-actual');
    const btnTodoHistorial  = document.getElementById('btn-pdf-todo-historial');

    if (btnAbrirPdf && modalPdf) {
        btnAbrirPdf.addEventListener('click', () => {
            modalPdf.classList.add('is-active');
        });

        modalPdf.querySelectorAll('[data-cerrar-modal-pdf]').forEach(el => {
            el.addEventListener('click', () => modalPdf.classList.remove('is-active'));
        });
    }

    if (btnTodoHistorial && inputFechaDesde && inputFechaHasta) {
        btnTodoHistorial.addEventListener('click', () => {
            inputFechaDesde.value = '';
            inputFechaHasta.value = '';
        });
    }

    if (btnMesActual && inputFechaDesde && inputFechaHasta) {
        btnMesActual.addEventListener('click', () => {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            inputFechaDesde.value = `${yyyy}-${mm}-01`;
            inputFechaHasta.value = `${yyyy}-${mm}-${dd}`;
        });
    }

    if (btnGenerarPdf) {
        btnGenerarPdf.addEventListener('click', () => {
            const desde = inputFechaDesde ? inputFechaDesde.value : '';
            const hasta = inputFechaHasta ? inputFechaHasta.value : '';

            if (desde && hasta && desde > hasta) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rango inválido',
                        text: 'La fecha "Desde" no puede ser mayor que la fecha "Hasta".',
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta".');
                }
                return;
            }

            const urlParamsActuales = new URLSearchParams(window.location.search);
            const paramsReporte = new URLSearchParams();

            const termino = inputBusqueda ? inputBusqueda.value.trim() : '';
            if (termino) paramsReporte.append('busqueda', termino);
            if (filtroActual && filtroActual !== 'todas') paramsReporte.append('filtro', filtroActual);

            const prov = urlParamsActuales.get('proveedor');
            const cli  = urlParamsActuales.get('cliente');
            if (prov) paramsReporte.append('proveedor', prov);
            if (cli)  paramsReporte.append('cliente', cli);

            if (desde) paramsReporte.append('desde', desde);
            if (hasta) paramsReporte.append('hasta', hasta);

            modalPdf.classList.remove('is-active');
            window.open(`${URL_BASE}/movimiento/exportarpdf?${paramsReporte.toString()}`, '_blank');
        });
    }
});