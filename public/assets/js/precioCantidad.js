/**
 * public/assets/js/precioCantidad.js
 *
 * Pantalla "Actualizar Precio": tabla de precio por cantidad (descuento por volumen).
 *  - Agrega / quita escalas sin recargar.
 *  - Calcula en vivo el precio unitario que resulta de cada escala (misma fórmula entera que el servidor).
 *  - Guarda con fetch (AJAX) en index.php?controller=precio&action=guardarEscalas y avisa con SweetAlert.
 */
(function () {
    'use strict';

    var form = document.getElementById('form-escalas');
    if (!form) { return; }

    var cuerpo   = document.getElementById('escalas-cuerpo');
    var vacio    = document.getElementById('escalas-vacio');
    var contador = document.getElementById('escalas-contador');
    var btnMas   = document.getElementById('btn-agregar-escala');
    var btnSave  = document.getElementById('btn-guardar-escalas');

    var precioVenta = parseFloat(form.getAttribute('data-precio-venta')) || 0;
    var costo       = parseFloat(form.getAttribute('data-costo')) || 0;

    // --- misma regla que precioConDescuento() de app/helpers/formato.php (solo enteros) ---
    function precioConDescuento(precio, pct) {
        var centavos = Math.round(precio * 100);
        var base = Math.round(pct * 100);
        return Math.floor((centavos * (10000 - base) + 5000) / 10000) / 100;
    }

    function dinero(n) {
        var partes = n.toFixed(2).split('.');
        return '$' + partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + partes[1];
    }

    function filas() { return Array.prototype.slice.call(cuerpo.querySelectorAll('tr.fila-escala')); }

    function refrescar() {
        filas().forEach(function (tr) {
            var pct = parseFloat(tr.querySelector('input[name="descuento_pct[]"]').value);
            var celda = tr.querySelector('.celda-precio-escala');
            if (precioVenta <= 0) { celda.textContent = 'Sin precio de venta'; return; }
            if (isNaN(pct) || pct <= 0 || pct >= 100) { celda.textContent = '—'; celda.classList.remove('has-text-danger'); return; }
            var p = precioConDescuento(precioVenta, pct);
            celda.textContent = dinero(p);
            celda.classList.toggle('has-text-danger', costo > 0 && p < costo);
            celda.title = (costo > 0 && p < costo) ? 'Por debajo del costo (' + dinero(costo) + ')' : '';
        });
        var n = filas().length;
        contador.textContent = n + ' escala(s)';
        if (vacio) { vacio.classList.toggle('is-hidden', n > 0); }
    }

    function agregarFila() {
        var tr = document.createElement('tr');
        tr.className = 'fila-escala';
        tr.innerHTML =
            '<td><input type="number" name="cantidad_minima[]" class="input is-small" min="2" max="100000" step="1" placeholder="Ej: 10"></td>' +
            '<td><input type="number" name="descuento_pct[]" class="input is-small" min="0.01" max="99.99" step="0.01" placeholder="Ej: 5"></td>' +
            '<td class="has-text-right has-text-weight-bold has-text-link font-mono celda-precio-escala">—</td>' +
            '<td><button type="button" class="button is-small is-danger is-light" data-quitar-escala title="Quitar escala"><span class="icon"><span class="material-symbols-outlined">delete</span></span></button></td>';
        cuerpo.appendChild(tr);
        tr.querySelector('input').focus();
        refrescar();
    }

    btnMas.addEventListener('click', agregarFila);

    cuerpo.addEventListener('input', refrescar);
    cuerpo.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('[data-quitar-escala]') : null;
        if (!b) { return; }
        var tr = b.closest('tr.fila-escala');
        if (tr) { cuerpo.removeChild(tr); refrescar(); }
    });

    function lista(items) {
        return '<ul style="text-align:left;margin-left:1.2rem;list-style:disc;">' +
            items.map(function (t) { return '<li>' + String(t).replace(/</g, '&lt;') + '</li>'; }).join('') + '</ul>';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSave.classList.add('is-loading');
        btnSave.disabled = true;

        fetch(form.getAttribute('data-url'), {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) {
                    Swal.fire({ icon: 'error', title: 'No se guardó', html: lista(d.errores || [d.error || 'Error desconocido']), confirmButtonColor: '#ef4444' });
                    return;
                }
                if (d.advertencias && d.advertencias.length) {
                    Swal.fire({ icon: 'warning', title: 'Guardado, pero revisa', html: '<p>' + d.mensaje + '</p>' + lista(d.advertencias), confirmButtonColor: '#2563eb' });
                } else {
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: d.mensaje, confirmButtonColor: '#2563eb', timer: 2500, timerProgressBar: true });
                }
                refrescar();
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Sin conexión', text: 'No se pudo comunicar con el servidor. Inténtalo de nuevo.', confirmButtonColor: '#ef4444' });
            })
            .then(function () {
                btnSave.classList.remove('is-loading');
                btnSave.disabled = false;
            });
    });

    refrescar();
})();
