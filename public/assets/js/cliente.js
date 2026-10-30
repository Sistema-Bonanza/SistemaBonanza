document.addEventListener('DOMContentLoaded', () => {
    
    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const esError = mensaje.toLowerCase().includes('error') || mensaje.toLowerCase().includes('obligatorio') || mensaje.toLowerCase().includes('falló');
        
        Swal.fire({
            icon: esError ? 'error' : 'success',
            title: esError ? 'Atención' : '¡Acción exitosa!',
            text: mensaje,
            confirmButtonColor: esError ? '#ef4444' : '#2563eb',
            timer: esError ? undefined : 3000,
            timerProgressBar: !esError
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. FILTRADO DINÁMICO (Buscador + Botones de Estado)
       ======================================================= */
    let filtroActivo = 'todos';
    const inputBuscador = document.getElementById('buscadorClientes');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');

    function aplicarFiltros() {
        const textoBusqueda = inputBuscador ? inputBuscador.value.toLowerCase() : '';
        const filas = document.querySelectorAll('.fila-busqueda');
        let registrosVisibles = 0;
        
        filas.forEach(fila => {
            const nombre = fila.querySelector('.nombre-cliente')?.textContent.toLowerCase() || '';
            const documento = fila.querySelector('.doc-cliente')?.textContent.toLowerCase() || '';
            const telefono = fila.querySelector('.tlf-cliente')?.textContent.toLowerCase() || '';

            // Limpieza de guiones y espacios para búsqueda inteligente de cédula/RIF
            const docLimpio = documento.replace(/[^a-z0-9]/gi, '');
            const busquedaLimpia = textoBusqueda.replace(/[^a-z0-9]/gi, '');

            // Coincidencia por Nombre, Documento (con o sin guión) o Teléfono
            const coincideTexto = nombre.includes(textoBusqueda) || 
                                documento.includes(textoBusqueda) || 
                                (busquedaLimpia !== '' && docLimpio.includes(busquedaLimpia)) ||
                                telefono.includes(textoBusqueda);
            
            // Validación de botones
            let coincideBoton = false;
            if (filtroActivo === 'todos') coincideBoton = true;
            if (filtroActivo === 'activos' && estadoFila === 'activo') coincideBoton = true;
            if (filtroActivo === 'inactivos' && estadoFila === 'inactivo') coincideBoton = true;
            
            if (coincideTexto && coincideBoton) {
                fila.style.display = '';
                registrosVisibles++;
            } else {
                fila.style.display = 'none';
            }
        });
        
        const contadorTag = document.getElementById('contadorFilas');
        if (contadorTag) contadorTag.textContent = `${registrosVisibles} registros`;
    }

    if (inputBuscador) {
        inputBuscador.addEventListener('keyup', aplicarFiltros);
    }

    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', function() {
            botonesFiltro.forEach(b => {
                b.classList.remove('is-link', 'is-selected');
                b.classList.add('is-light');
                const tag = b.querySelector('.tag');
                if (tag) { tag.classList.remove('is-white'); tag.classList.add('is-info', 'is-light'); }
            });
            
            this.classList.remove('is-light');
            this.classList.add('is-link', 'is-selected');
            const tag = this.querySelector('.tag');
            if (tag) { tag.classList.remove('is-info', 'is-light'); tag.classList.add('is-white'); }
            
            filtroActivo = this.getAttribute('data-filtro');
            aplicarFiltros();
        });
    });

    /* =======================================================
       3. PREPARACIÓN PARA AJAX (Botón Inactivar)
       ======================================================= */
    const botonesEliminar = document.querySelectorAll('.btn-eliminar-ajax');
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const idCliente = this.getAttribute('data-id');
            const nombreCliente = this.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Estás seguro?',
                html: `Vas a inactivar al cliente <strong>${nombreCliente}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, inactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Función en construcción',
                        text: `El ID ${idCliente} está listo para enviarse por AJAX.`
                    });
                }
            });
        });
    });

});