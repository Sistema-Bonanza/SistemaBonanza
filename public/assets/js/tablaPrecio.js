/**
 * public/assets/js/tablaPrecios.js
 * Interactividad asíncrona para Control de Precios (tablaprecios.php).
 * Trabaja en conjunto con ajax.js y SweetAlert2.
 */
document.addEventListener('DOMContentLoaded', () => {
    const contenedorAjax = document.getElementById('ajax-precios');
    const contadorBadge  = document.getElementById('contador-precios');
    let temporizadorBusqueda = null;
    let restaurarFocoBuscador = false;
    let posicionCursor = 0;

    // ------------------------------------------------------------
    // 1. Interceptor de mensajes Flash (SweetAlert2)
    // ------------------------------------------------------------
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '' && typeof Swal !== 'undefined') {
        const mensaje = cajaFlash.innerText.trim();
        const esError = mensaje.toLowerCase().includes('error') || mensaje.toLowerCase().includes('falló');
        Swal.fire({
            icon: esError ? 'error' : 'success',
            title: esError ? 'Atención' : '¡Éxito!',
            text: mensaje,
            confirmButtonColor: esError ? '#ef4444' : '#2563eb',
            timer: esError ? undefined : 3000,
            timerProgressBar: !esError
        });
    }

    if (!contenedorAjax) return;

    // Helper para disparar el envío asíncrono que intercepta ajax.js
    function dispararFiltroAsync() {
        const form = contenedorAjax.querySelector('form[data-ajax-filtro]');
        if (!form) return;

        if (document.activeElement && document.activeElement.id === 'q') {
            restaurarFocoBuscador = true;
            posicionCursor = document.activeElement.selectionStart || 0;
        }

        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    }

    // ------------------------------------------------------------
    // 2. Filtrado automático al cambiar Categoría, Situación o Buscar
    // ------------------------------------------------------------
    contenedorAjax.addEventListener('change', (e) => {
        if (e.target.matches('#id_categoria, #estado')) {
            dispararFiltroAsync();
        }
    });

    contenedorAjax.addEventListener('input', (e) => {
        if (e.target.matches('#q')) {
            clearTimeout(temporizadorBusqueda);
            temporizadorBusqueda = setTimeout(() => {
                dispararFiltroAsync();
            }, 350);
        }
    });

    // ------------------------------------------------------------
    // 3. Botón "Limpiar" asíncrono (sin recargar toda la página)
    // ------------------------------------------------------------
    contenedorAjax.addEventListener('click', (e) => {
        const btnLimpiar = e.target.closest('a[data-limpiar-filtros]');
        if (!btnLimpiar) return;

        e.preventDefault();
        const inputQ    = contenedorAjax.querySelector('#q');
        const selectCat = contenedorAjax.querySelector('#id_categoria');
        const selectEst = contenedorAjax.querySelector('#estado');

        if (inputQ) inputQ.value = '';
        if (selectCat) selectCat.value = '';
        if (selectEst) selectEst.value = 'todos';

        marcarTarjetaResumen('todos');
        dispararFiltroAsync();
    });

    // ------------------------------------------------------------
    // 4. Tarjetas del panel "Resumen" (columna izquierda) en AJAX
    // ------------------------------------------------------------
    const enlacesResumen = document.querySelectorAll('.column.is-4 a[href*="tablaprecios"]');

    function marcarTarjetaResumen(estadoActivo) {
        enlacesResumen.forEach((enlace) => {
            const url = new URL(enlace.href, window.location.origin);
            const estadoEnlace = url.searchParams.get('estado') || 'todos';
            const coincide = estadoEnlace === estadoActivo;

            enlace.style.borderColor = coincide ? '#2563eb' : '#f1f5f9';
            enlace.style.background  = coincide ? '#eff6ff' : '#f8fafc';
        });
    }

    enlacesResumen.forEach((enlace) => {
        enlace.addEventListener('click', (e) => {
            const selectEst = contenedorAjax.querySelector('#estado');
            if (!selectEst) return; // Si no existe el select, deja navegar normal

            e.preventDefault();
            const url = new URL(enlace.href, window.location.origin);
            const estadoObjetivo = url.searchParams.get('estado') || 'todos';

            selectEst.value = estadoObjetivo;
            marcarTarjetaResumen(estadoObjetivo);
            dispararFiltroAsync();
        });
    });

    // ------------------------------------------------------------
    // 5. Actualizar contador "X listados" y foco tras cada recarga AJAX
    // ------------------------------------------------------------
    const observador = new MutationObserver(() => {
        // Sincronizar contador del encabezado
        if (contadorBadge) {
            const filas = contenedorAjax.querySelectorAll('tbody tr');
            const sinResultados = filas.length === 1 && filas[0].querySelector('td[colspan]');
            const total = sinResultados ? 0 : filas.length;
            contadorBadge.textContent = `${total} listados`;
        }

        // Sincronizar tarjeta activa del resumen
        const selectEst = contenedorAjax.querySelector('#estado');
        if (selectEst) {
            marcarTarjetaResumen(selectEst.value || 'todos');
        }

        // Mantener el cursor en el buscador si el usuario estaba escribiendo
        if (restaurarFocoBuscador) {
            const nuevoInputQ = contenedorAjax.querySelector('#q');
            if (nuevoInputQ) {
                nuevoInputQ.focus();
                nuevoInputQ.setSelectionRange(posicionCursor, posicionCursor);
            }
            restaurarFocoBuscador = false;
        }
    });

    observador.observe(contenedorAjax, { childList: true, subtree: true });
});