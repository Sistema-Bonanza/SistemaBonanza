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

        const params = new URLSearchParams({
            busqueda: termino,
            filtro: filtroActual,
            pagina: paginaActual
        });

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
});