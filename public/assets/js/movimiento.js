document.addEventListener('DOMContentLoaded', () => {
    const inputBusqueda = document.getElementById('input-busqueda');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const tbody = document.getElementById('tbody-movimientos');
    const paginacion = document.getElementById('contenedor-paginacion');
    
    let filtroActual = 'todas';
    let paginaActual = 1;
    let temporizadorBuscador;

    // 1. Función principal que hace la petición AJAX
    const cargarMovimientos = async () => {
        const termino = inputBusqueda.value.trim();
        
        // Creamos un FormData para enviar por POST
        const data = new FormData();
        data.append('busqueda', termino);
        data.append('filtro', filtroActual);
        data.append('pagina', paginaActual);
        
        // ¡NUEVO! Enviamos el token de seguridad que pide tu enrutador
        if (window.csrfToken) {
            data.append('csrf_token', window.csrfToken); 
        }

        try {
            // Ajusta la URL según tu enrutador (index.php?controller=...)
            const respuesta = await fetch('index.php?controller=movimiento&action=buscarAjax', {
                method: 'POST',
                body: data
            });
            
            const json = await respuesta.json();
            
            // Inyectamos el HTML que nos devuelva PHP
            tbody.innerHTML = json.filasHTML;
            paginacion.innerHTML = json.paginacionHTML;
            
            // Actualizamos el contador total si existe
            const totalLabel = document.querySelector('#total-registros-label');
            if(totalLabel) totalLabel.textContent = json.totalRegistros;

        } catch (error) {
            console.error("Error en AJAX:", error);
            tbody.innerHTML = '<tr><td colspan="7" class="has-text-centered has-text-danger">Error de conexión. Intente nuevamente.</td></tr>';
        }
    };

    // 2. Evento: Escribir en el buscador (con "Debounce" para no saturar la base de datos por cada letra)
    inputBusqueda.addEventListener('keyup', () => {
        clearTimeout(temporizadorBuscador);
        paginaActual = 1; // Volver a la página 1 al buscar
        temporizadorBuscador = setTimeout(cargarMovimientos, 400); // Espera 400ms después de dejar de escribir
    });

    // 3. Evento: Clic en los botones de filtro
    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Quitar clase activa a todos los botones
            botonesFiltro.forEach(b => {
                b.classList.remove('is-link', 'is-selected');
                // Devolver el color original al icono según tu diseño
            });
            
            // Agregar clase activa al botón presionado
            const botonPresionado = e.currentTarget;
            botonPresionado.classList.add('is-link', 'is-selected');
            
            filtroActual = botonPresionado.getAttribute('data-filtro');
            paginaActual = 1;
            cargarMovimientos();
        });
    });

    // 4. Evento: Clic en botones de paginación (Delegación de eventos porque los botones se recrean dinámicamente)
    paginacion.addEventListener('click', (e) => {
        // Buscar si hicimos clic en un enlace de paginación
        const link = e.target.closest('.pagination-link');
        if (link && !link.hasAttribute('disabled')) {
            paginaActual = link.getAttribute('data-pagina');
            cargarMovimientos();
        }
    });
});