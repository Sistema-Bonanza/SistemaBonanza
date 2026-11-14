document.addEventListener('DOMContentLoaded', () => {
    const inputBuscador = document.getElementById('buscadorClientes');
    const botonesFiltro = document.querySelectorAll('#filtrosEstado .btn-filtro');
    const filas         = document.querySelectorAll('#tablaClientes .fila-busqueda');
    const contadorTag   = document.getElementById('contadorFilas');

    let estadoActual = 'todos';

    function filtrarTabla() {
        const texto = (inputBuscador?.value || '').toLowerCase().trim();
        let visibles = 0;

        filas.forEach(fila => {
            const contenido  = fila.textContent.toLowerCase();
            const estadoFila = fila.dataset.estado || 'activo';

            const coincideTexto = texto === '' || contenido.includes(texto);
            const coincideEstado =
                estadoActual === 'todos' ||
                (estadoActual === 'activos' && estadoFila === 'activo') ||
                (estadoActual === 'inactivos' && estadoFila === 'inactivo');

            if (coincideTexto && coincideEstado) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        if (contadorTag) {
            contadorTag.textContent = `${visibles} ${visibles === 1 ? 'registro' : 'registros'}`;
        }
    }

    if (inputBuscador) {
        inputBuscador.addEventListener('keyup', filtrarTabla);
    }

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', () => {
            botonesFiltro.forEach(b => {
                b.classList.remove('is-link', 'is-selected');
                b.classList.add('is-light');
            });
            btn.classList.remove('is-light');
            btn.classList.add('is-link', 'is-selected');

            estadoActual = btn.dataset.filtro || 'todos';
            filtrarTabla();
        });
    });
});