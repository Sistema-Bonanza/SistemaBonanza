// Editar producto: validación rápida, resumen lateral en vivo y desactivación por AJAX.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('editProductForm');
    if (!form) { return; }

    // Validación rápida antes de enviar (el servidor valida de nuevo todo).
    form.addEventListener('submit', (e) => {
        const codigo = document.getElementById('codigo').value.trim();
        const nombre = document.getElementById('nombre').value.trim();
        if (!codigo || !nombre) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Completa el código y el nombre del producto.', confirmButtonColor: '#2563eb' });
        }
    });

    // El panel lateral refleja lo que se está escribiendo.
    const minimo = document.getElementById('stock_minimo');
    const unidad = document.getElementById('unidad_manejo');
    const porEmpaque = document.getElementById('unidades_por_empaque');
    const resMin = document.getElementById('resumenStockMinimo');
    const resUnidad = document.getElementById('resumenUnidadManejo');

    function refrescarResumen() {
        if (resMin && minimo) { resMin.textContent = (parseInt(minimo.value, 10) || 0) + ' UND'; }
        if (resUnidad && unidad && porEmpaque) {
            const n = parseInt(porEmpaque.value, 10) || 1;
            resUnidad.textContent = (unidad.value.trim().toUpperCase() || 'UNIDAD') + ' · ' + n + (n === 1 ? ' unidad' : ' unidades');
        }
    }
    [minimo, unidad, porEmpaque].forEach((el) => el && el.addEventListener('input', refrescarResumen));

    // Desactivar el producto (solo si no tiene existencias; el servidor lo comprueba).
    const btnDesactivar = document.getElementById('btnDeactivate');
    if (btnDesactivar) {
        btnDesactivar.addEventListener('click', () => {
            Swal.fire({
                title: '¿Desactivar producto?',
                text: 'El artículo dejará de estar visible en el catálogo. Solo se puede si no tiene unidades en almacén.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eab308',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((r) => {
                if (!r.isConfirmed) { return; }
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = 'index.php?controller=producto&action=eliminarproducto';
                f.setAttribute('data-ajax', '');
                f.style.display = 'none';
                const campos = { id: form.elements['id_producto'].value, csrf_token: btnDesactivar.getAttribute('data-csrf') || '' };
                Object.keys(campos).forEach((k) => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = k; i.value = campos[k];
                    f.appendChild(i);
                });
                document.body.appendChild(f);
                f.submit();   // ajax.js lo envía con fetch
            });
        });
    }
});
