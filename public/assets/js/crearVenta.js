/**
 * public/assets/js/crearVenta.js
 *
 * Comportamiento de las vistas de Ventas adaptado a Bulma CSS + SweetAlert2.
 * Mantiene la base lógica original (cálculo en centavos, validación estricta y buscador nativo).
 */
(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Utilidades (Originales)
    // ------------------------------------------------------------------

    function aCentavos(valor) {
        var n = parseFloat(valor);
        return isNaN(n) ? 0 : Math.round(n * 100);
    }

    function dinero(centavos) {
        var texto = String(Math.abs(centavos));
        while (texto.length < 3) { texto = '0' + texto; }
        var enteros   = texto.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        var decimales = texto.slice(-2);
        return (centavos < 0 ? '-' : '') + '$' + enteros + ',' + decimales;
    }

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
        divisa: 'Divisa', credito: 'Crédito', mixto: 'Pago Mixto'
    };

    // ------------------------------------------------------------------
    // 1. Formulario de venta
    // ------------------------------------------------------------------

    function iniciarFormularioVenta(form) {
        
        // --- Interceptor de Alertas PHP a SweetAlert2 ---
        var cajaFlash = document.getElementById('caja-flash-oculta');
        if (cajaFlash && cajaFlash.innerText.trim() !== '') {
            Swal.fire({
                icon: 'success',
                title: '¡Operación Exitosa!',
                text: cajaFlash.innerText.trim(),
                confirmButtonColor: '#2563eb',
                timer: 4000,
                timerProgressBar: true
            });
            cajaFlash.innerHTML = '';
        }

        var tasaIva = parseFloat(form.getAttribute('data-tasa-iva')) || 0;
        form.noValidate = true;

        // ---- catálogo de productos ----
        var catalogo = [];
        try {
            catalogo = JSON.parse(document.getElementById('catalogoProductos').textContent || '[]');
        } catch (e) {
            catalogo = [];
        }
        var porId = {};
        catalogo.forEach(function (p) { porId[p.id_producto || p.id] = p; }); // Soporta ambas nomenclaturas

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

        var resultados = [];   
        var activo     = -1;   

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
                var puntos = codigo === q ? 0 : (codigo.indexOf(q) === 0 ? 1 : (nombre.indexOf(q) === 0 ? 2 : 3));
                lista.push({ p: p, puntos: puntos, nombre: nombre });
            });

            lista.sort(function (a, b) {
                return a.puntos - b.puntos || (a.nombre < b.nombre ? -1 : (a.nombre > b.nombre ? 1 : 0));
            });
            return lista.slice(0, 8).map(function (x) { return x.p; });
        }

        function cerrarPanel() {
            panel.style.display = 'none';
            buscador.setAttribute('aria-expanded', 'false');
            buscador.removeAttribute('aria-activedescendant');
        }

        function resaltar(indice) {
            activo = indice;
            Array.prototype.forEach.call(panel.querySelectorAll('.resultado-producto'), function (el, i) {
                var esActivo = (i === indice);
                // Estilos Bulma para hover/activo
                if (esActivo) {
                    el.style.backgroundColor = '#f1f5f9';
                    buscador.setAttribute('aria-activedescendant', el.id);
                    if (el.scrollIntoView) { el.scrollIntoView({ block: 'nearest' }); }
                } else {
                    el.style.backgroundColor = 'transparent';
                }
            });
        }

        function pintarResultados() {
            vaciar(panel);

            if (resultados.length === 0) {
                panel.appendChild(crear('div', 'p-3 has-text-grey is-size-7', 'No hay productos que coincidan con «' + buscador.value.trim() + '».'));
            }

            resultados.forEach(function (p, i) {
                var ok   = disponible(p);
                var idProd = p.id_producto || p.id;
                var item = crear('div', 'resultado-producto p-3 is-clickable is-flex is-justify-content-space-between is-align-items-center' + (ok ? '' : ' has-background-danger-light'));
                item.id = 'opcionProducto' + i;
                item.style.borderBottom = '1px solid #e2e8f0';
                item.style.transition = 'background-color 0.2s';
                item.setAttribute('role', 'option');
                if (!ok) { item.setAttribute('aria-disabled', 'true'); }

                var izq = crear('div');
                izq.appendChild(crear('div', 'has-text-weight-semibold is-size-6', p.nombre));
                izq.appendChild(crear('div', 'is-size-7 has-text-grey', p.codigo + (p.categoria ? ' · ' + p.categoria : '')));

                var der = crear('div', 'has-text-right');
                der.appendChild(crear('div', 'has-text-weight-semibold has-text-link', dinero(aCentavos(p.precio))));
                var estado = p.stock <= 0 ? 'Agotado' : (parseFloat(p.precio) <= 0 ? 'Sin precio' : 'Stock: ' + p.stock);
                der.appendChild(crear('div', 'is-size-7 ' + (ok ? 'has-text-grey' : 'has-text-danger has-text-weight-bold'), estado));

                item.appendChild(izq);
                item.appendChild(der);

                item.addEventListener('mousedown', function (evento) {
                    evento.preventDefault();
                    agregarDesdeBuscador(p);
                });
                item.addEventListener('mousemove', function () { if (activo !== i) { resaltar(i); } });

                panel.appendChild(item);
            });

            panel.style.display = 'block';
            panel.style.backgroundColor = 'white';
            panel.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1)';
            buscador.setAttribute('aria-expanded', 'true');

            var primero = -1;
            for (var j = 0; j < resultados.length; j++) {
                if (disponible(resultados[j])) { primero = j; break; }
            }
            resaltar(primero);
        }

        function agregarDesdeBuscador(p) {
            if (!disponible(p)) {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'error', 
                    title: '«' + p.nombre + '» ' + (p.stock <= 0 ? 'está agotado.' : 'no tiene precio de venta.'), 
                    showConfirmButton: false, timer: 3000
                });
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

        function agregarProducto(p, cantidad) {
            var idProd = p.id_producto || p.id;
            var existente = filaDe(idProd);

            if (existente) {
                var input = existente.querySelector('input[name="cantidad[]"]');
                var actual = parseInt(input.value, 10) || 0;
                if (actual >= p.stock) {
                    Swal.fire({toast: true, position: 'top-end', icon: 'warning', title: 'Límite de stock alcanzado (' + p.stock + ').', showConfirmButton: false, timer: 2500});
                } else {
                    input.value = Math.min(actual + cantidad, p.stock);
                }
                recalcular();
                return existente;
            }

            var fila = plantilla.content.firstElementChild.cloneNode(true);
            fila.setAttribute('data-id', idProd);
            fila.querySelector('.linea-nombre').textContent = p.nombre;
            fila.querySelector('.linea-codigo').textContent = p.codigo;
            fila.querySelector('input[name="id_producto[]"]').value = idProd;
            
            var inCant = fila.querySelector('input[name="cantidad[]"]');
            inCant.value = cantidad;
            inCant.max = p.stock;

            cuerpo.appendChild(fila);
            recalcular();
            return fila;
        }

        // ---------------- forma de pago (Sincronizado con UI Bulma) ----------------

        var radios      = Array.prototype.slice.call(form.querySelectorAll('#radiosFormaPago input[name="tipo_pago"]'));
        var botonesUI   = Array.prototype.slice.call(document.querySelectorAll('.btn-pago-ui'));
        
        var modalMixto  = document.getElementById('modalMixto');
        var resumenBox  = document.getElementById('resumenMixto');
        var resumenTxt  = document.getElementById('textoResumenMixto');
        var saldoTxt    = document.getElementById('saldoMixto');
        var saldoCaja   = document.getElementById('cajaSaldoMixto');
        var mixtoTotal  = document.getElementById('mixtoTotal');
        var campos      = {};
        
        ['efectivo', 'tarjeta', 'transferencia', 'divisa', 'credito'].forEach(function (clave) {
            campos[clave] = document.getElementById('pago_' + clave);
        });

        function tipoPago() {
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) { return radios[i].value; }
            }
            return '';
        }

        function actualizarEstiloPago(pagoSeleccionado) {
            botonesUI.forEach(function(btn) {
                if (btn.getAttribute('data-pago') === pagoSeleccionado) {
                    btn.style.border = '2px solid #10b981';
                    btn.style.backgroundColor = '#ecfdf5';
                    btn.querySelector('.material-symbols-outlined').classList.replace('has-text-grey', 'has-text-success');
                } else {
                    btn.style.border = '1px solid #e2e8f0';
                    btn.style.backgroundColor = 'transparent';
                    btn.querySelector('.material-symbols-outlined').classList.replace('has-text-success', 'has-text-grey');
                }
            });
        }

        botonesUI.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var pagoValue = btn.getAttribute('data-pago');
                var radioAsociado = form.querySelector('#radiosFormaPago input[value="' + pagoValue + '"]');
                if(radioAsociado) {
                    radioAsociado.checked = true;
                    radioAsociado.dispatchEvent(new Event('change'));
                }
                actualizarEstiloPago(pagoValue);
            });
        });

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

            if (saldoTxt)  { 
                saldoTxt.textContent = texto;
                saldoTxt.className = 'is-size-7 has-text-centered has-text-weight-bold ' + (ok ? 'has-text-success' : 'has-text-danger');
            }

            if (resumenTxt) {
                var partes = m.usados.map(function (u) { return ETIQUETAS_PAGO[u.clave] + ' ' + dinero(u.centavos); });
                resumenTxt.textContent = '';
                resumenTxt.appendChild(crear('strong', null, 'Desglose: '));
                resumenTxt.appendChild(document.createTextNode((partes.length ? partes.join(' + ') : 'Incompleto') + ' — '));
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
                fila.querySelector('.celda-iva').textContent = p.iva || p.aplica_iva ? 'Con IVA' : 'Exento';
                fila.querySelector('.celda-subtotal').textContent = cantidad > 0 ? dinero(subtotal) : '';

                input.classList.toggle('is-danger', excede);
                if (excede) { input.title = 'Supera el stock disponible (' + p.stock + ').'; } else { input.removeAttribute('title'); }

                if (excede) { hayError = true; }
                if (cantidad <= 0) { incompletas++; } else {
                    unidades += cantidad;
                    lineas.push({ subtotal: subtotal, aplicaIva: (p.iva === 1 || p.aplica_iva === 1) });
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
            if (resumenBox) { 
                if (esMixto) { resumenBox.classList.remove('is-hidden'); }
                else { resumenBox.classList.add('is-hidden'); }
            }
            if (esMixto) { actualizarMixto(totales.total); }

            return { filas: lista.length, lineas: lineas.length, hayError: hayError, incompletas: incompletas, total: totales.total };
        }

        // ---------------- eventos ----------------

        buscador.addEventListener('input', function () {
            resultados = buscar(buscador.value);
            if (buscador.value.trim() === '') {
                cerrarPanel();
                return;
            }
            pintarResultados();
        });

        buscador.addEventListener('focus', function () {
            if (buscador.value.trim() !== '' && resultados.length) { pintarResultados(); }
        });
        
        // Timeout para permitir el click antes de cerrar
        buscador.addEventListener('blur', function() {
            setTimeout(cerrarPanel, 150);
        });

        buscador.addEventListener('keydown', function (evento) {
            if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
                if (!resultados.length) { return; }
                evento.preventDefault();
                if (panel.style.display === 'none') { pintarResultados(); return; }
                var paso = evento.key === 'ArrowDown' ? 1 : -1;
                var siguiente = activo;
                for (var n = 0; n < resultados.length; n++) {
                    siguiente = (siguiente + paso + resultados.length) % resultados.length;
                    if (disponible(resultados[siguiente])) { break; }
                }
                resaltar(siguiente);
            } else if (evento.key === 'Enter') {
                evento.preventDefault();
                if (!resultados.length) {
                    if (buscador.value.trim() !== '') { 
                        Swal.fire({toast: true, position: 'top', icon: 'error', title: 'Producto no encontrado.', showConfirmButton: false, timer: 2000}); 
                    }
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
                    void saldoCaja.offsetWidth;
                    saldoCaja.classList.add('sacudir');
                }
            });
        }

        // ---------------- envío (SweetAlert Intercept) ----------------
        
        var botonesEnvio = Array.prototype.slice.call(form.querySelectorAll('button[type="submit"]'));
        
        function detenerConSweet(texto, campo) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: texto,
                confirmButtonColor: '#2563eb'
            }).then(function() {
                if (campo && campo.focus) { campo.focus(); }
            });
        }

        form.addEventListener('submit', function (evento) {
            evento.preventDefault(); 

            var r = recalcular();
            var cliente = form.elements['id_cliente'];
            var btnPulsado = evento.submitter;

            if (!cliente.value) { return detenerConSweet('Selecciona un cliente.', cliente); }
            if (r.filas === 0) { return detenerConSweet('Agrega al menos un producto con el buscador.', buscador); }
            if (r.incompletas > 0) { return detenerConSweet('Hay productos sin cantidad. Escríbela o quita la fila con la papelera.'); }
            if (r.hayError) { return detenerConSweet('Hay cantidades que superan el stock disponible (marcadas en rojo).'); }
            if (!tipoPago()) { return detenerConSweet('Elige la forma de pago.'); }

            if (tipoPago() === 'mixto') {
                var estado = actualizarMixto(r.total);
                if (!estado.ok) {
                    Swal.fire({
                        icon: 'error', title: 'Desglose Incompleto', text: 'El pago mixto no está completo. ' + estado.mensaje
                    }).then(abrirModalMixto);
                    return;
                }
            }

            var etiqueta = tipoPago() === 'mixto' ? 'Pago mixto' : ETIQUETAS_PAGO[tipoPago()];
            var accionMsg = (btnPulsado && btnPulsado.value === 'imprimir') ? '¿Desea solo imprimir la cotización (sin afectar Kardex)?' : 'Se registrará la factura por ' + dinero(r.total) + ' (' + etiqueta + ') y se descontará el stock. No se puede editar, solo anular. ¿Continuar?';

            Swal.fire({
                title: '¿Confirmar Operación?',
                text: accionMsg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Revisar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    if (btnPulsado) {
                        var inputOculto = document.createElement('input');
                        inputOculto.type = 'hidden';
                        inputOculto.name = btnPulsado.name;
                        inputOculto.value = btnPulsado.value;
                        form.appendChild(inputOculto);
                    }
                    botonesEnvio.forEach(function (b) { b.disabled = true; });
                    form.submit();
                }
            });
        });

        // Limpiar form custom (Botón Limpiar de Bulma)
        var btnReset = document.getElementById('btnResetVenta');
        if (btnReset) {
            btnReset.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Limpiar Factura?',
                    text: 'Se borrarán los productos y el cliente seleccionado.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        form.reset();
                        tbody.innerHTML = '<tr id="filaVacia"><td colspan="8" class="has-text-centered has-text-grey py-5">Aún no hay productos. Búscalos arriba.</td></tr>';
                        var radEf = form.querySelector('input[name="tipo_pago"][value="efectivo"]');
                        if (radEf) { 
                            radEf.checked = true; 
                            radEf.dispatchEvent(new Event('change')); 
                            actualizarEstiloPago('efectivo');
                        }
                        recalcular();
                    }
                });
            });
        }

        window.addEventListener('pageshow', function (evento) {
            if (evento.persisted) {
                botonesEnvio.forEach(function (b) { b.disabled = false; });
            }
            recalcular();
        });

        // ---------------- arranque ----------------

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

        var checkIni = form.querySelector('input[name="tipo_pago"]:checked');
        if (checkIni) { actualizarEstiloPago(checkIni.value); } 
        else if (radios.length) { 
            radios[0].checked = true; 
            actualizarEstiloPago(radios[0].value);
        }

        recalcular();
        if (omitidos > 0) {
            Swal.fire({toast: true, position: 'top-end', icon: 'info', title: 'Algunos productos del envío anterior ya no están disponibles.', showConfirmButton: false, timer: 3000});
        }
    }

    // ------------------------------------------------------------------
    // 2. Factura: modales, imprimir y confirmaciones
    // ------------------------------------------------------------------

    function iniciarComportamientosComunes() {
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

        Array.prototype.forEach.call(document.querySelectorAll('[data-imprimir]'), function (boton) {
            boton.addEventListener('click', function () { window.print(); });
        });
    }

    var formVenta = document.getElementById('formVenta');
    if (formVenta) { iniciarFormularioVenta(formVenta); }
    iniciarComportamientosComunes();

})();