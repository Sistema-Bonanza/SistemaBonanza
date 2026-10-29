document.addEventListener('DOMContentLoaded', () => {
    const inputBusqueda = document.getElementById('input-busqueda');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const tbody = document.getElementById('tbody-movimientos');
    const paginacion = document.getElementById('contenedor-paginacion');
    
    let filtroActual = 'todas';
    let paginaActual = 1;
    let temporizadorBuscador;

    const cargarMovimientos = async () => {
        const termino = inputBusqueda ? inputBusqueda.value.trim() : '';
        
        // 1. Construimos los parámetros para enviarlos por la URL (Método GET)
        const params = new URLSearchParams({
            busqueda: termino,
            filtro: filtroActual,
            pagina: paginaActual
        });

        try {
            // 2. Enviamos la petición por GET con la firma oficial de AJAX
            const respuesta = await fetch(`index.php?controller=movimiento&action=buscarajax&${params.toString()}`, {
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
                throw new Error("Respuesta no válida del servidor. Código HTTP: " + respuesta.status);
            }
            
            if (!respuesta.ok) {
                throw new Error(json.message || "Error interno del servidor");
            }
            
            // 3. Pintamos los datos si todo salió bien
            if (tbody) tbody.innerHTML = json.filasHTML;
            if (paginacion) paginacion.innerHTML = json.paginacionHTML;
            
            const totalLabel = document.querySelector('#total-registros-label');
            if (totalLabel) totalLabel.textContent = json.totalRegistros;

        } catch (error) {
            console.error("Error capturado:", error);
            // Mostrará el error real en la tabla en lugar de quedarse en blanco
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

    // Disparamos la carga inicial al entrar
    cargarMovimientos();
});