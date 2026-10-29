/**
 * public/assets/js/venta.js
 *
 * Comportamiento de las vistas de Ventas:
 *   1. Formulario de venta: buscador de productos, tabla dinámica (sin límite de filas),
 *      totales en vivo y forma de pago (con desglose para el pago mixto).
 *   2. Factura: modal de anulación, botón de imprimir, impresión automática y confirmaciones.
 *
 * IMPORTANTE: lo del formulario es solo una VISTA PREVIA. El servidor vuelve a calcular todo
 * con los precios de la base de datos (VentaController::guardarVenta) y revisa que el pago
 * cuadre; nada de lo que calcula este archivo se guarda tal cual.
 *
 * Los montos se calculan en centavos enteros y el IVA POR LÍNEA, igual que
 * calcularTotalesDocumento() en app/helpers/formato.php, para que la vista previa
 * coincida con el total que se guarda.
 */
(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------

    /** "12.35" => 1235 */
    function aCentavos(valor) {
        var n = parseFloat(valor);
        return isNaN(n) ? 0 : Math.round(n * 100);
    }

    /** 123456 => "$1.234,56" (mismo formato que dinero() en PHP) */
    function dinero(centavos) {
        var texto = String(Math.abs(centavos));
        while (texto.length < 3) { texto = '0' + texto; }
        var enteros   = texto.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        var decimales = texto.slice(-2);
        return (centavos < 0 ? '-' : '') + '$' + enteros + ',' + decimales;
    }

    /**
     * Totales de una lista de líneas [{ subtotal: centavos, aplicaIva: bool }].
     * Devuelve { subtotal, iva, total } en centavos.
     */
    function calcularTotales(lineas, tasaIva) {
        var subtotal = 0;
        var iva = 0;
        lineas.forEach(function (linea) {
            subtotal += linea.subtotal;
            if (linea.aplicaIva) {
                iva += Math.round(linea.subtotal * tasaIva / 100);
            }
        });
        return { subtotal: subtotal, iva: iva, total: subtotal + iva };
    }

    /** Minúsculas y sin tildes: "Café" y "cafe" se encuentran entre sí. */
    function normalizar(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function vaciar(elemento) {
        while (elemento.firstChild) { elemento.removeChild(elemento.firstChild); }
    }

    function crear(etiqueta, clase, texto) {
        var el = document.createElement(etiqueta);
        if (clase) { el.className = clase; }
        if (texto !== undefined) { el.textContent = texto; }
        return el;
    }

    var ETIQUETAS_PAGO = {
        efectivo: 'Efectivo', tarjeta: 'Tarjeta', transferencia: 'Transferencia',
        divisa: 'Divisa', credito: 'Crédito'
    };

    // ------------------------------------------------------------------
    // 1. Formulario de venta
    // ------------------------------------------------------------------

    function iniciarFormularioVenta(form) {
        var tasaIva = parseFloat(form.getAttribute('data-tasa-iva')) || 0;

        // La validación nativa del navegador no puede avisar de campos dentro de un modal cerrado
        // (bloquea el envío en silencio). Por eso se valida aquí, con mensajes propios, y el servidor
        // vuelve a revisar todo.
        form.noValidate = true;

        // ---- catálogo de productos ----
        var catalogo = [];
        try {
            catalogo = JSON.parse(document.getElementById('catalogoProductos').textContent || '[]');
        } catch (e) {
            catalogo = [];
        }
        var porId = {};
        catalogo.forEach(function (p) { porId[p.id] = p; });

        var cuerpo      = document.getElementById('lineasVenta');
        var filaVacia   = document.getElementById('filaVacia');
        var plantilla   = document.getElementById('plantillaLinea');
        var buscador    = document.getElementById('buscarProducto');
        var panel       = document.getElementById('resultadosProducto');
        var aviso       = document.getElementById('avisoBuscador');
        var contador    = document.getElementById('contadorLineas');
        var textoAviso0 = aviso ? aviso.textContent : '';

        var elSubtotal = document.getElementById('totalSubtotal');
        var elIva      = document.getElementById('totalIva');
        var elTotal    = document.getElementById('totalGeneral');

        var resultados = [];   // lo que muestra el buscador ahora
        var activo     = -1;   // posición resaltada con el teclado

        function disponible(p) {
            return p.stock > 0 && parseFloat(p.precio) > 0;
        }

        function mensaje(texto, esError) {
            if (!aviso) { return; }
            aviso.textContent = texto;
            aviso.classList.toggle('has-text-danger', !!esError);
            aviso.classList.toggle('has-text-success', !esError && texto !== textoAviso0);
        }

        // ---------------- buscador ----------------

        function buscar(texto) {
            var q = normalizar(texto).trim();
            if (q === '') { return []; }
            var tokens = q.split(/\s+/);
            var lista = [];

            catalogo.forEach(function (p) {
                var codigo = normalizar(p.codigo);
                var nombre = normalizar(p.nombre);
                var pajar  = codigo + ' ' + nombre + ' ' + normalizar(p.categoria);
                for (var i = 0; i < tokens.length; i++) {
                    if (pajar.indexOf(tokens[i]) === -1) { return; }
                }
                // Mejor puntaje = más arriba: código exacto, código que empieza igual, nombre que empieza igual, resto.
                var puntos = codigo === q ? 0 : (codigo.indexOf(q) === 0 ? 1 : (nombre.indexOf(q) === 0 ? 2 : 3));
                lista.push({ p: p, puntos: puntos, nombre: nombre });
            });

            lista.sort(function (a, b) {
                return a.puntos - b.puntos || (a.nombre < b.nombre ? -1 : (a.nombre > b.nombre ? 1 : 0));
            });
            return lista.slice(0, 8).map(function (x) { return x.p; });
        }

        function cerrarPanel() {
            panel.hidden = true;
            buscador.setAttribute('aria-expanded', 'false');
            buscador.removeAttribute('aria-activedescendant');
        }

        function resaltar(indice) {
            activo = indice;
            Array.prototype.forEach.call(panel.querySelectorAll('.resultado-producto'), function (el, i) {
                var esActivo = (i === indice);
                el.classList.toggle('activo', esActivo);
                if (esActivo) {
                    buscador.setAttribute('aria-activedescendant', el.id);
                    if (el.scrollIntoView) { el.scrollIntoView({ block: 'nearest' }); }
                }
            });
        }

        function pintarResultados() {
            vaciar(panel);

            if (resultados.length === 0) {
                panel.appendChild(crear('div', 'sin-resultados', 'No hay productos que coincidan con «' + buscador.value.trim() + '».'));
            }

            resultados.forEach(function (p, i) {
                var ok   = disponible(p);
                var item = crear('div', 'resultado-producto' + (ok ? '' : ' deshabilitado'));
                item.id = 'opcionProducto' + i;
                item.setAttribute('role', 'option');
                if (!ok) { item.setAttribute('aria-disabled', 'true'); }

                var izq = crear('div');
                izq.appendChild(crear('div', 'has-text-weight-semibold', p.nombre));
                izq.appendChild(crear('div', 'is-size-7 has-text-grey', p.codigo + (p.categoria ? ' · ' + p.categoria : '')));

                var der = crear('div', 'has-text-right');
                der.appendChild(crear('div', 'has-text-weight-semibold', dinero(aCentavos(p.precio))));
                var estado = p.stock <= 0 ? 'Agotado' : (parseFloat(p.precio) <= 0 ? 'Sin precio' : 'Stock: ' + p.stock);
                der.appendChild(crear('div', 'is-size-7 ' + (ok ? 'has-text-grey' : 'has-text-danger'), estado));

                item.appendChild(izq);
                item.appendChild(der);

                // mousedown (no click): se dispara antes de que el buscador pierda el foco.
                item.addEventListener('mousedown', function (evento) {
                    evento.preventDefault();
                    agregarDesdeBuscador(p);
                });
                item.addEventListener('mousemove', function () { if (activo !== i) { resaltar(i); } });

                panel.appendChild(item);
            });

            panel.hidden = false;
            buscador.setAttribute('aria-expanded', 'true');

            // Resalta el primero que se pueda agregar: Enter lo agrega de una vez.
            var primero = -1;
            for (var j = 0; j < resultados.length; j++) {
                if (disponible(resultados[j])) { primero = j; break; }
            }
            resaltar(primero);
        }

        function agregarDesdeBuscador(p) {
            if (!disponible(p)) {
                mensaje('«' + p.nombre + '» ' + (p.stock <= 0 ? 'está agotado.' : 'no tiene precio de venta. Defínelo en Control de Precios.'), true);
                return;
            }
            agregarProducto(p, 1);
            buscador.value = '';
            resultados = [];
            cerrarPanel();
            buscador.focus();
        }

        // ---------------- tabla de productos ----------------

        function filas() {
            return Array.prototype.slice.call(cuerpo.querySelectorAll('tr.fila-venta'));
        }

        function filaDe(id) {
            return cuerpo.querySelector('tr.fila-venta[data-id="' + id + '"]');
        }

        function resaltarFila(fila) {
            fila.classList.add('fila-resaltada');
            setTimeout(function () { fila.classList.remove('fila-resaltada'); }, 900);
        }

        /** Agrega el producto, o suma a la fila existente. Devuelve la fila. */
        function agregarProducto(p, cantidad) {
            var existente = filaDe(p.id);

            if (existente) {
                var input = existente.querySelector('input[name="cantidad[]"]');
                var actual = parseInt(input.value, 10) || 0;
                if (actual >= p.stock) {
                    mensaje('Ya agregaste todo el stock disponible de «' + p.nombre + '» (' + p.stock + ').', true);
                } else {
                    input.value = Math.min(actual + cantidad, p.stock);
                    mensaje('«' + p.nombre + '»: ahora son ' + input.value + ' unidades.', false);
                }
                resaltarFila(existente);
                recalcular();
                return existente;
            }

            var fila = plantilla.content.firstElementChild.cloneNode(true);
            fila.setAttribute('data-id', p.id);
            fila.querySelector('.linea-nombre').textContent = p.nombre;
            fila.querySelector('.linea-codigo').textContent = p.codigo;
            fila.querySelector('input[name="id_producto[]"]').value = p.id;
            fila.querySelector('input[name="cantidad[]"]').value = cantidad;
            cuerpo.appendChild(fila);

            mensaje('Agregado: ' + p.nombre, false);
            resaltarFila(fila);
            recalcular();
            return fila;
        }

        // ---------------- forma de pago ----------------

        var radios      = Array.prototype.slice.call(form.querySelectorAll('input[name="tipo_pago"]'));
        var modalMixto  = document.getElementById('modalMixto');
        var resumenBox  = document.getElementById('resumenMixto');
        var resumenTxt  = document.getElementById('textoResumenMixto');
        var saldoTxt    = document.getElementById('saldoMixto');
        var saldoCaja   = document.getElementById('cajaSaldoMixto');
        var mixtoTotal  = document.getElementById('mixtoTotal');
        var campos      = {};
        Object.keys(ETIQUETAS_PAGO).forEach(function (clave) {
            campos[clave] = document.getElementById('pago_' + clave);
        });

        function tipoPago() {
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) { return radios[i].value; }
            }
            return '';
        }

        /** Lee el desglose escrito en el modal. */
        function leerMixto(totalCentavos) {
            var suma = 0, usados = [], invalido = false;
            Object.keys(campos).forEach(function (clave) {
                var valor = campos[clave].value.trim();
                if (valor === '') { return; }
                if (!/^\d+(\.\d{1,2})?$/.test(valor)) { invalido = true; return; }
                var centavos = aCentavos(valor);
                if (centavos > 0) {
                    suma += centavos;
                    usados.push({ clave: clave, centavos: centavos });
                }
            });
            return { suma: suma, usados: usados, invalido: invalido, saldo: totalCentavos - suma };
        }

        /**
         * Pinta el saldo del modal y el resumen bajo la barra de pago.
         * Devuelve { ok, mensaje }: ok = el desglose es válido para guardar.
         */
        function actualizarMixto(totalCentavos) {
            var m = leerMixto(totalCentavos);
            var estado, texto, ok = false;

            if (m.invalido) {
                estado = 'excede';
                texto = 'Hay un monto no válido: usa números positivos con hasta 2 decimales.';
            } else if (totalCentavos === 0) {
                estado = 'falta';
                texto = 'Agrega productos a la venta para conocer el total.';
            } else if (m.saldo > 0) {
                estado = 'falta';
                texto = 'Falta por pagar: ' + dinero(m.saldo);
            } else if (m.saldo < 0) {
                estado = 'excede';
                texto = 'Excede el total en: ' + dinero(-m.saldo);
            } else if (m.usados.length < 2) {
                estado = 'falta';
                texto = 'Un pago mixto necesita al menos dos formas de pago.';
            } else {
                estado = 'completo';
                texto = 'Pago completo: ' + dinero(m.suma);
                ok = true;
            }

            if (saldoTxt)  { saldoTxt.textContent = texto; }
            if (saldoCaja) {
                saldoCaja.classList.toggle('es-falta', estado === 'falta');
                saldoCaja.classList.toggle('es-excede', estado === 'excede');
                saldoCaja.classList.toggle('es-completo', estado === 'completo');
            }

            if (resumenTxt) {
                var partes = m.usados.map(function (u) { return ETIQUETAS_PAGO[u.clave] + ' ' + dinero(u.centavos); });
                resumenTxt.textContent = '';
                resumenTxt.appendChild(crear('strong', null, 'Pago mixto: '));
                resumenTxt.appendChild(document.createTextNode((partes.length ? partes.join(' + ') : 'sin desglose todavía') + ' — '));
                resumenTxt.appendChild(crear('span', ok ? 'has-text-success' : 'has-text-danger', texto));
            }
            return { ok: ok, mensaje: texto };
        }

        function abrirModalMixto() {
            if (!modalMixto) { return; }
            modalMixto.classList.add('is-active');
            var primero = form.querySelector('#modalMixto input[type="number"]');
            if (primero) { primero.focus(); }
        }

        // ---------------- recálculo general ----------------

        function recalcular() {
            var lineas = [];
            var unidades = 0, hayError = false, incompletas = 0;
            var lista = filas();

            lista.forEach(function (fila, i) {
                var p = porId[fila.getAttribute('data-id')];
                var input = fila.querySelector('input[name="cantidad[]"]');
                var cantidad = parseInt(input.value, 10);
                if (isNaN(cantidad) || cantidad < 0) { cantidad = 0; }

                var precio = aCentavos(p.precio);
                var subtotal = cantidad * precio;
                var excede = cantidad > p.stock;

                fila.querySelector('.celda-num').textContent = i + 1;
                fila.querySelector('.celda-stock').textContent = p.stock;
                fila.querySelector('.celda-precio').textContent = dinero(precio);
                fila.querySelector('.celda-iva').textContent = p.iva ? 'Con IVA' : 'Exento';
                fila.querySelector('.celda-subtotal').textContent = cantidad > 0 ? dinero(subtotal) : '';

                input.classList.toggle('is-danger', excede);
                if (excede) { input.title = 'Supera el stock disponible (' + p.stock + ').'; } else { input.removeAttribute('title'); }

                if (excede) { hayError = true; }
                if (cantidad <= 0) { incompletas++; } else {
                    unidades += cantidad;
                    lineas.push({ subtotal: subtotal, aplicaIva: p.iva === 1 });
                }
            });

            if (filaVacia) { filaVacia.style.display = lista.length ? 'none' : ''; }
            if (contador) {
                contador.textContent = lista.length + (lista.length === 1 ? ' producto' : ' productos') +
                    (unidades ? ' · ' + unidades + (unidades === 1 ? ' unidad' : ' unidades') : '');
            }

            var totales = calcularTotales(lineas, tasaIva);
            if (elSubtotal) { elSubtotal.textContent = dinero(totales.subtotal); }
            if (elIva)      { elIva.textContent      = dinero(totales.iva); }
            if (elTotal)    { elTotal.textContent    = dinero(totales.total); }
            if (mixtoTotal) { mixtoTotal.textContent = dinero(totales.total); }

            var esMixto = tipoPago() === 'mixto';
            if (resumenBox) { resumenBox.hidden = !esMixto; }
            if (esMixto) { actualizarMixto(totales.total); }

            return { filas: lista.length, lineas: lineas.length, hayError: hayError, incompletas: incompletas, total: totales.total };
        }

        // ---------------- eventos ----------------

        buscador.addEventListener('input', function () {
            resultados = buscar(buscador.value);
            if (buscador.value.trim() === '') {
                cerrarPanel();
                mensaje(textoAviso0, false);
                return;
            }
            pintarResultados();
        });

        buscador.addEventListener('focus', function () {
            if (buscador.value.trim() !== '' && resultados.length) { pintarResultados(); }
        });
        buscador.addEventListener('blur', cerrarPanel);
        panel.addEventListener('mousedown', function (evento) { evento.preventDefault(); });

        buscador.addEventListener('keydown', function (evento) {
            if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
                if (!resultados.length) { return; }
                evento.preventDefault();
                if (panel.hidden) { pintarResultados(); return; }
                var paso = evento.key === 'ArrowDown' ? 1 : -1;
                var siguiente = activo;
                for (var n = 0; n < resultados.length; n++) {
                    siguiente = (siguiente + paso + resultados.length) % resultados.length;
                    if (disponible(resultados[siguiente])) { break; }
                }
                resaltar(siguiente);
            } else if (evento.key === 'Enter') {
                // Nunca debe enviar el formulario: Enter aquí significa "agregar".
                evento.preventDefault();
                if (!resultados.length) {
                    if (buscador.value.trim() !== '') { mensaje('No encontré ese producto. Revisa el código o el nombre.', true); }
                    return;
                }
                var elegido = activo >= 0 ? resultados[activo] : resultados[0];
                agregarDesdeBuscador(elegido);
            } else if (evento.key === 'Escape') {
                cerrarPanel();
            }
        });

        cuerpo.addEventListener('input', recalcular);
        cuerpo.addEventListener('click', function (evento) {
            var boton = evento.target.closest ? evento.target.closest('[data-quitar]') : null;
            if (!boton) { return; }
            var fila = boton.closest('tr.fila-venta');
            if (fila) {
                cuerpo.removeChild(fila);
                recalcular();
                buscador.focus();
            }
        });

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                var r = recalcular();
                if (tipoPago() === 'mixto') {
                    if (r.total > 0) { abrirModalMixto(); }
                }
            });
        });

        Object.keys(campos).forEach(function (clave) {
            campos[clave].addEventListener('input', recalcular);
            // Enter en el modal acepta el desglose; nunca envía la venta.
            campos[clave].addEventListener('keydown', function (evento) {
                if (evento.key === 'Enter') {
                    evento.preventDefault();
                    document.getElementById('aceptarMixto').click();
                }
            });
        });

        var botonAceptar = document.getElementById('aceptarMixto');
        if (botonAceptar) {
            botonAceptar.addEventListener('click', function () {
                var r = recalcular();
                var estado = actualizarMixto(r.total);
                if (estado.ok) {
                    modalMixto.classList.remove('is-active');
                } else if (saldoCaja) {
                    saldoCaja.classList.remove('sacudir');
                    void saldoCaja.offsetWidth;   // reinicia la animación
                    saldoCaja.classList.add('sacudir');
                }
            });
        }

        // ---------------- envío ----------------

        var botonesEnvio = Array.prototype.slice.call(form.querySelectorAll('button[type="submit"]'));

        form.addEventListener('submit', function (evento) {
            var r = recalcular();
            var cliente = form.elements['id_cliente'];

            function detener(texto, campo) {
                window.alert(texto);
                evento.preventDefault();
                if (campo && campo.focus) { campo.focus(); }
            }

            if (!cliente.value) { return detener('Selecciona un cliente.', cliente); }
            if (r.filas === 0) { return detener('Agrega al menos un producto con el buscador.', buscador); }
            if (r.incompletas > 0) { return detener('Hay productos sin cantidad. Escríbela o quita la fila con la ×.'); }
            if (r.hayError) { return detener('Hay cantidades que superan el stock disponible (marcadas en rojo).'); }
            if (!tipoPago()) { return detener('Elige la forma de pago.'); }

            if (tipoPago() === 'mixto') {
                var estado = actualizarMixto(r.total);
                if (!estado.ok) {
                    evento.preventDefault();
                    window.alert('El pago mixto no está completo. ' + estado.mensaje);
                    abrirModalMixto();
                    return;
                }
            }

            var etiqueta = tipoPago() === 'mixto' ? 'Pago mixto' : ETIQUETAS_PAGO[tipoPago()];
            if (!window.confirm('Se registrará la factura por ' + dinero(r.total) + ' (' + etiqueta +
                ') y se descontará el stock. No se puede editar, solo anular. ¿Continuar?')) {
                evento.preventDefault();
                return;
            }

            // Evita el doble clic: dos envíos seguidos crearían dos ventas. Se desactiva DESPUÉS de que el
            // navegador lea el botón pulsado (su "name/value" indica si se pidió imprimir).
            setTimeout(function () {
                botonesEnvio.forEach(function (b) { b.disabled = true; });
            }, 0);
        });

        // Si el usuario vuelve con "atrás", los botones no deben quedar bloqueados.
        window.addEventListener('pageshow', function (evento) {
            if (evento.persisted) {
                botonesEnvio.forEach(function (b) { b.disabled = false; });
            }
            recalcular();
        });

        // ---------------- arranque ----------------

        // Si el servidor devolvió el formulario con un error, se vuelven a dibujar las líneas.
        var previas = [];
        try { previas = JSON.parse(cuerpo.getAttribute('data-lineas-previas') || '[]'); } catch (e) { previas = []; }
        var omitidos = 0;
        previas.forEach(function (linea) {
            var p = porId[linea.id];
            if (p && disponible(p) && !filaDe(p.id)) {
                agregarProducto(p, linea.cantidad);
            } else {
                omitidos++;
            }
        });

        if (!radios.some(function (r) { return r.checked; }) && radios.length) { radios[0].checked = true; }

        recalcular();
        mensaje(textoAviso0, false);
        if (omitidos > 0) {
            mensaje('Algunos productos de tu envío anterior ya no están disponibles y no se cargaron.', true);
        }
    }

    // ------------------------------------------------------------------
    // 2. Factura: modales, imprimir y confirmaciones (se activan con atributos data-*)
    // ------------------------------------------------------------------

    function iniciarComportamientosComunes() {
        // <button data-modal-abrir="idDelModal">  /  <button data-modal-cerrar>
        Array.prototype.forEach.call(document.querySelectorAll('[data-modal-abrir]'), function (boton) {
            boton.addEventListener('click', function () {
                var modal = document.getElementById(boton.getAttribute('data-modal-abrir'));
                if (!modal) { return; }
                modal.classList.add('is-active');
                var campo = modal.querySelector('textarea, input[type="text"], input[type="number"]');
                if (campo) { campo.focus(); }
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-modal-cerrar]'), function (elemento) {
            elemento.addEventListener('click', function () {
                var modal = elemento.closest('.modal');
                if (modal) { modal.classList.remove('is-active'); }
            });
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Escape') { return; }
            Array.prototype.forEach.call(document.querySelectorAll('.modal.is-active'), function (modal) {
                modal.classList.remove('is-active');
            });
        });

        // <button data-imprimir>
        Array.prototype.forEach.call(document.querySelectorAll('[data-imprimir]'), function (boton) {
            boton.addEventListener('click', function () { window.print(); });
        });

        // <form data-confirmar="¿Seguro?">
        Array.prototype.forEach.call(document.querySelectorAll('form[data-confirmar]'), function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                if (!window.confirm(formulario.getAttribute('data-confirmar'))) {
                    evento.preventDefault();
                }
            });
        });

        // <body data-autoimprimir>: abre el diálogo de impresión al cargar ("Registrar e imprimir").
        if (document.body && document.body.hasAttribute('data-autoimprimir')) {
            window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
        }
    }

    // ------------------------------------------------------------------
    // Arranque
    // ------------------------------------------------------------------

    var formVenta = document.getElementById('formVenta');
    if (formVenta) { iniciarFormularioVenta(formVenta); }
    iniciarComportamientosComunes();

    // Solo para las pruebas con Node; en el navegador no existe "module".
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { aCentavos: aCentavos, dinero: dinero, calcularTotales: calcularTotales, normalizar: normalizar };
    }
})();
