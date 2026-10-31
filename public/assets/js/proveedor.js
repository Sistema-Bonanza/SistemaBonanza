document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const esError = mensaje.toLowerCase().includes('error') || 
                        mensaje.toLowerCase().includes('obligatorio') || 
                        mensaje.toLowerCase().includes('falló');

        Swal.fire({
            icon: esError ? 'error' : 'success',
            title: esError ? 'Atención' : '¡Éxito!',
            text: mensaje,
            confirmButtonColor: esError ? '#ef4444' : '#2563eb',
            timer: esError ? undefined : 3000,
            timerProgressBar: !esError
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. BÚSQUEDA Y FILTRADO EN TIEMPO REAL
       ======================================================= */
    const buscador = document.getElementById('buscadorProveedores');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const filas = document.querySelectorAll('.fila-busqueda');
    const contadorFilas = document.getElementById('contadorFilas');

    let filtroEstadoActual = 'todos';

    function aplicarFiltros() {
        const textoBusqueda = buscador ? buscador.value.toLowerCase().trim() : '';
        let visibles = 0;

        filas.forEach(fila => {
            const estado = fila.getAttribute('data-estado');
            const razonSocial = fila.querySelector('.nombre-proveedor')?.textContent.toLowerCase() || '';
            const rif = fila.querySelector('.doc-proveedor')?.textContent.toLowerCase() || '';
            const contacto = fila.querySelector('.contacto-proveedor')?.textContent.toLowerCase() || '';
            const telefono = fila.querySelector('.tlf-proveedor')?.textContent.toLowerCase() || '';

            const rifLimpio = rif.replace(/[^a-z0-9]/gi, '');
            const busquedaLimpia = textoBusqueda.replace(/[^a-z0-9]/gi, '');

            const coincideTexto = razonSocial.includes(textoBusqueda) || 
                                  rif.includes(textoBusqueda) || 
                                  (busquedaLimpia !== '' && rifLimpio.includes(busquedaLimpia)) ||
                                  contacto.includes(textoBusqueda) ||
                                  telefono.includes(textoBusqueda);

            let coincideEstado = false;
            if (filtroEstadoActual === 'todos') {
                coincideEstado = true;
            } else if (filtroEstadoActual === 'activos' && estado === 'activo') {
                coincideEstado = true;
            } else if (filtroEstadoActual === 'inactivos' && estado === 'inactivo') {
                coincideEstado = true;
            }

            if (coincideTexto && coincideEstado) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        if (contadorFilas) {
            contadorFilas.textContent = `${visibles} registros`;
        }
    }

    if (buscador) {
        buscador.addEventListener('input', aplicarFiltros);
    }

    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', function () {
            botonesFiltro.forEach(b => {
                b.classList.remove('is-selected', 'is-link');
                b.classList.add('is-light');
            });

            this.classList.remove('is-light');
            this.classList.add('is-selected', 'is-link');

            filtroEstadoActual = this.getAttribute('data-filtro');
            aplicarFiltros();
        });
    });

    /* =======================================================
       3. CAMBIO DE ESTADO / SOFT DELETE RÁPIDO
       ======================================================= */
    document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            const nombreProv = this.getAttribute('data-nombre');
            const esActivo = this.getAttribute('data-activo') === '1';
            const urlDestino = this.getAttribute('data-url');

            const accion = esActivo ? 'inactivar' : 'reactivar';
            const color = esActivo ? '#ef4444' : '#10b981';

            Swal.fire({
                title: `¿Desea ${accion} al proveedor?`,
                text: `Proveedor: "${nombreProv}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#64748b',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && urlDestino) {
                    window.location.href = urlDestino;
                }
            });
        });
    });

});