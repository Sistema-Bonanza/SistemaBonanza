/**
 * public/assets/js/tasaBcv.js
 *
 * Tasa BCV automática (Bs por 1 USD) para Compras y Ventas.
 *  - Pide la tasa del día al servidor con fetch (index.php?controller=tasa&action=obtener).
 *    El servidor la consulta a un servicio público una vez al día y la guarda.
 *  - Pinta el panel #panelTasaBcv y avisa a la pantalla con el evento 'tasa:cambio'.
 *  - Expone window.BonanzaTasa:
 *        BonanzaTasa.tasa            número (Bs por USD) o null si no hay
 *        BonanzaTasa.aBs(usd)        dólares -> bolívares (número) o null
 *        BonanzaTasa.formatoBs(usd)  "Bs 1.234,56" o "—"
 *        BonanzaTasa.alCambiar(fn)   ejecuta fn cada vez que cambia la tasa (y una vez al registrarla si ya hay)
 *        BonanzaTasa.recargar()      vuelve a consultar
 */
(function () {
    'use strict';

    var panel = document.getElementById('panelTasaBcv');
    var estado = { tasa: null, fecha: null, fuente: '', estado: 'sin_datos', mensaje: '', puedeFijar: false };
    var escuchas = [];

    function miles(textoEntero) {
        return textoEntero.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // 1234.5 -> "1.234,50"
    function numeroVe(n, decimales) {
        var partes = Math.abs(n).toFixed(decimales).split('.');
        return (n < 0 ? '-' : '') + miles(partes[0]) + (partes[1] ? ',' + partes[1] : '');
    }

    function aBs(usd) {
        if (estado.tasa === null || isNaN(parseFloat(usd))) { return null; }
        return Math.round(parseFloat(usd) * estado.tasa * 100) / 100;
    }

    function formatoBs(usd) {
        var bs = aBs(usd);
        return bs === null ? '—' : 'Bs ' + numeroVe(bs, 2);
    }

    function avisar() {
        escuchas.forEach(function (fn) { try { fn(estado); } catch (e) { /* una pantalla no rompe a otra */ } });
        document.dispatchEvent(new CustomEvent('tasa:cambio', { detail: estado }));
    }

    function pintar() {
        if (!panel) { return; }
        var valor = document.getElementById('bcvTasaValor');
        var etiqueta = document.getElementById('bcvTasaEstado');

        if (estado.tasa === null) {
            valor.textContent = 'Sin tasa';
            etiqueta.className = 'tag is-danger is-light is-rounded';
            etiqueta.textContent = 'No disponible';
            etiqueta.title = estado.mensaje || '';
            return;
        }
        valor.textContent = numeroVe(estado.tasa, 4);

        if (estado.estado === 'hoy') {
            etiqueta.className = 'tag is-success is-light is-rounded';
            etiqueta.textContent = (estado.manual ? 'Manual' : 'Automática') + ' · ' + estado.fuente;
            etiqueta.title = 'Tasa del ' + estado.fecha;
        } else {
            etiqueta.className = 'tag is-warning is-light is-rounded';
            etiqueta.textContent = 'Última guardada (' + estado.fecha + ')';
            etiqueta.title = estado.mensaje || '';
        }
    }

    function aplicar(datos) {
        estado.tasa = (datos && datos.ok && datos.tasa) ? parseFloat(datos.tasa) : null;
        estado.fecha = datos ? datos.fecha : null;
        estado.fuente = datos ? (datos.fuente || '') : '';
        estado.manual = !!(datos && datos.manual);
        estado.estado = datos ? (datos.estado || 'sin_datos') : 'sin_datos';
        estado.mensaje = datos ? (datos.mensaje || '') : '';
        estado.puedeFijar = !!(datos && datos.puedeFijar);
        pintar();
        avisar();
    }

    function recargar() {
        if (!panel) { return Promise.resolve(); }
        var boton = document.getElementById('bcvRefrescar');
        if (boton) { boton.classList.add('is-loading'); }

        return fetch(panel.getAttribute('data-url-obtener'), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(aplicar)
            .catch(function () {
                aplicar({ ok: false, estado: 'sin_datos', mensaje: 'No se pudo consultar la tasa.' });
            })
            .then(function () { if (boton) { boton.classList.remove('is-loading'); } });
    }

    function fijarManual() {
        if (typeof Swal === 'undefined') { return; }
        Swal.fire({
            title: 'Tasa BCV manual',
            text: 'Escribe cuántos bolívares vale 1 USD hoy.',
            input: 'text',
            inputValue: estado.tasa !== null ? estado.tasa.toFixed(4) : '',
            inputAttributes: { inputmode: 'decimal', autocomplete: 'off' },
            showCancelButton: true,
            confirmButtonText: 'Guardar tasa',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            inputValidator: function (v) {
                var t = String(v).trim().replace(',', '.');
                if (!/^\d{1,6}(\.\d{1,4})?$/.test(t) || parseFloat(t) <= 0) {
                    return 'Escribe un número mayor que 0 (máximo 4 decimales).';
                }
            },
            showLoaderOnConfirm: true,
            preConfirm: function (v) {
                var cuerpo = new FormData();
                cuerpo.append('tasa', String(v).trim().replace(',', '.'));
                cuerpo.append('csrf_token', panel.getAttribute('data-csrf'));
                return fetch(panel.getAttribute('data-url-fijar'), {
                    method: 'POST', body: cuerpo, credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d.ok) { throw new Error(d.error || 'No se pudo guardar la tasa.'); }
                        return d;
                    })
                    .catch(function (e) { Swal.showValidationMessage(e.message); });
            }
        }).then(function (r) {
            if (r.isConfirmed && r.value) {
                aplicar(r.value);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tasa actualizada', showConfirmButton: false, timer: 2000 });
            }
        });
    }

    window.BonanzaTasa = {
        get tasa() { return estado.tasa; },
        get estado() { return estado; },
        aBs: aBs,
        formatoBs: formatoBs,
        numeroVe: numeroVe,
        recargar: recargar,
        alCambiar: function (fn) {
            escuchas.push(fn);
            if (estado.tasa !== null) { fn(estado); }
        }
    };

    if (panel) {
        var bRef = document.getElementById('bcvRefrescar');
        var bFij = document.getElementById('bcvFijar');
        if (bRef) { bRef.addEventListener('click', recargar); }
        if (bFij) { bFij.addEventListener('click', fijarManual); }
        recargar();
    }
})();
