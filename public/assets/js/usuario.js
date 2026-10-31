document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
        if (cajaFlash && cajaFlash.innerText.trim() !== '') {
            const mensaje = cajaFlash.innerText.trim();
            const mensajeLower = mensaje.toLowerCase();

            // Detección ampliada de mensajes de error o advertencia
            const esError = mensajeLower.includes('error') || 
                            mensajeLower.includes('obligatorio') || 
                            mensajeLower.includes('falló') || 
                            mensajeLower.includes('no se pudo') || 
                            mensajeLower.includes('no puedes') || 
                            mensajeLower.includes('expiró') || 
                            mensajeLower.includes('inválido') ||
                            cajaFlash.querySelector('.is-danger, .notification-danger, .alert-danger') !== null;

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
    const buscador = document.getElementById('buscadorUsuarios');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const filas = document.querySelectorAll('.fila-busqueda');
    const contadorFilas = document.getElementById('contadorFilas');

    let filtroRolActual = 'todos';

    function aplicarFiltros() {
        const textoBusqueda = buscador ? buscador.value.toLowerCase().trim() : '';
        let visibles = 0;

        filas.forEach(fila => {
            const rol = fila.getAttribute('data-rol');
            const nombre = fila.querySelector('.nombre-user')?.textContent.toLowerCase() || '';
            const username = fila.querySelector('.username-user')?.textContent.toLowerCase() || '';

            const coincideTexto = nombre.includes(textoBusqueda) || username.includes(textoBusqueda);

            let coincideRol = false;
            if (filtroRolActual === 'todos') {
                coincideRol = true;
            } else if (filtroRolActual === 'admin' && rol === 'admin') {
                coincideRol = true;
            } else if (filtroRolActual === 'operador' && rol === 'operador') {
                coincideRol = true;
            }

            if (coincideTexto && coincideRol) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        if (contadorFilas) {
            contadorFilas.textContent = `${visibles} Cuentas`;
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

            filtroRolActual = this.getAttribute('data-filtro');
            aplicarFiltros();
        });
    });

    /* =======================================================
       3. CAMBIO DE ESTADO / SOFT DELETE EN 1 CLIC
       ======================================================= */
    document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            const nombreUser = this.getAttribute('data-nombre');
            const esActivo = this.getAttribute('data-activo') === '1';
            const urlDestino = this.getAttribute('data-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const accion = esActivo ? 'desactivar el acceso a' : 'reactivar el acceso a';
            const color = esActivo ? '#ef4444' : '#10b981';

            Swal.fire({
                title: `¿Confirma ${esActivo ? 'desactivar' : 'reactivar'} cuenta?`,
                text: `Usuario: "${nombreUser}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#64748b',
                confirmButtonText: esActivo ? 'Sí, desactivar' : 'Sí, reactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && urlDestino) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = urlDestino;

                    if (csrfToken) {
                        const inputToken = document.createElement('input');
                        inputToken.type = 'hidden';
                        inputToken.name = 'csrf_token';
                        inputToken.value = csrfToken;
                        form.appendChild(inputToken);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});