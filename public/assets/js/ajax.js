/**
 * public/assets/js/ajax.js
 *
 * Comunicación asíncrona (AJAX) compartida por todos los módulos.
 * Se carga después de SweetAlert2 en cada vista. No necesita configuración: se activa con
 * atributos HTML.
 *
 * 1) FORMULARIOS POST  ->  <form method="POST" data-ajax>
 *    El formulario se envía con fetch (sin recargar la página). El servidor responde JSON
 *    (ver redirigir() en app/helpers/seguridad.php):
 *        { ok, tipo: 'exito'|'error'|'aviso', mensajes: [...], redirect, login }
 *    - Éxito/aviso: muestra el mensaje con SweetAlert2 y navega a "redirect".
 *    - Error: muestra los errores y deja al usuario en el formulario con lo que escribió.
 *    - Sesión vencida: lleva al login.
 *    También funciona cuando otro script llama form.submit() (por ejemplo tras una confirmación).
 *    Opcional:  data-ajax-sin-navegar  => en éxito NO navega, solo muestra el mensaje y ejecuta
 *               el evento 'ajax:exito' del formulario (detail = respuesta JSON).
 *
 * 2) LISTADOS CON FILTROS  ->  contenedor <div id="x" data-ajax-lista> + filtros
 *    - <form method="GET" data-ajax-filtro="#x">  : al enviarlo (o al cambiar un <select>
 *      / escribir en un input con data-ajax-vivo) pide la lista con fetch y reemplaza SOLO el
 *      contenido de #x. La URL de la barra se actualiza (se puede copiar y volver atrás).
 *    - Los enlaces de paginación dentro de #x también se cargan sin recargar la página.
 *    - Un enlace fuera del contenedor puede usar  <a href="..." data-ajax-link="#x">.
 */
(function () {
    'use strict';

    var CABECERAS_JSON = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };

    function escapar(texto) {
        return String(texto).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function listaHtml(mensajes) {
        if (!mensajes || mensajes.length === 0) { return ''; }
        if (mensajes.length === 1) { return escapar(mensajes[0]); }
        return '<ul style="text-align:left;margin-left:1.2rem;list-style:disc;">' +
            mensajes.map(function (m) { return '<li>' + escapar(m) + '</li>'; }).join('') + '</ul>';
    }

    function alertar(opciones) {
        if (typeof Swal !== 'undefined') { return Swal.fire(opciones); }
        window.alert((opciones.title ? opciones.title + '\n' : '') + (opciones.text || ''));
        return Promise.resolve({ isConfirmed: true });
    }

    // ------------------------------------------------------------------
    // 1. FORMULARIOS POST
    // ------------------------------------------------------------------

    function botonesDe(form) {
        return Array.prototype.slice.call(form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])'));
    }

    function enviarFormulario(form, submitter) {
        if (form.__ajaxEnviando) { return; }
        form.__ajaxEnviando = true;

        var datos = new FormData(form);
        // El botón que se pulsó (ej. name="accion" value="imprimir") no siempre entra en FormData.
        if (submitter && submitter.name && !datos.has(submitter.name)) {
            datos.append(submitter.name, submitter.value);
        }

        var botones = botonesDe(form);
        botones.forEach(function (b) { b.classList.add('is-loading'); b.disabled = true; });

        function liberar() {
            form.__ajaxEnviando = false;
            botones.forEach(function (b) { b.classList.remove('is-loading'); b.disabled = false; });
        }

        var metodo = (form.getAttribute('method') || 'POST').toUpperCase();
        var url = form.getAttribute('action') || window.location.href;

        fetch(url, { method: metodo, body: datos, credentials: 'same-origin', headers: CABECERAS_JSON })
            .then(function (r) {
                return r.text().then(function (texto) {
                    var json = null;
                    try { json = JSON.parse(texto); } catch (e) { json = null; }
                    return { estado: r.status, json: json };
                });
            })
            .then(function (res) {
                var d = res.json;

                if (!d) {
                    liberar();
                    return alertar({
                        icon: 'error', title: 'Respuesta inesperada',
                        text: 'El servidor no respondió como se esperaba. Recarga la página y revisa si la operación quedó registrada.',
                        confirmButtonColor: '#ef4444'
                    });
                }

                if (d.login) {
                    return alertar({ icon: 'warning', title: 'Sesión vencida', text: (d.mensajes || [])[0], confirmButtonColor: '#2563eb' })
                        .then(function () { window.location.href = d.redirect || 'index.php'; });
                }

                if (!d.ok) {
                    liberar();
                    return alertar({
                        icon: 'error', title: 'Atención', html: listaHtml(d.mensajes),
                        confirmButtonColor: '#ef4444'
                    });
                }

                // Éxito o aviso
                var sinNavegar = form.hasAttribute('data-ajax-sin-navegar');
                var icono = d.tipo === 'aviso' ? 'info' : 'success';
                var promesa = alertar({
                    icon: icono,
                    title: d.tipo === 'aviso' ? 'Aviso' : '¡Listo!',
                    html: listaHtml(d.mensajes),
                    confirmButtonColor: '#2563eb',
                    timer: 1800,
                    timerProgressBar: true
                });

                return promesa.then(function () {
                    if (sinNavegar) {
                        liberar();
                        form.dispatchEvent(new CustomEvent('ajax:exito', { detail: d }));
                    } else {
                        // Se deja el formulario bloqueado hasta cambiar de página (evita un doble envío).
                        window.location.href = d.redirect || 'index.php';
                    }
                });
            })
            .catch(function () {
                liberar();
                alertar({ icon: 'error', title: 'Sin conexión', text: 'No se pudo comunicar con el servidor. Inténtalo de nuevo.', confirmButtonColor: '#ef4444' });
            });
    }

    // Envío normal (clic en el botón). Si otro script ya lo detuvo (validación), no se toca.
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-ajax')) { return; }
        if (evento.defaultPrevented) { return; }
        evento.preventDefault();
        enviarFormulario(form, evento.submitter || null);
    });

    // Botón con confirmación:  <button type="button" data-confirmar="¿Seguro?"> dentro de un <form data-ajax>.
    // Pregunta con SweetAlert2 y, si el usuario acepta, envía el formulario por AJAX.
    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest ? evento.target.closest('button[data-confirmar]') : null;
        if (!boton || !boton.form || !boton.form.hasAttribute('data-ajax')) { return; }
        evento.preventDefault();
        var form = boton.form;
        alertar({
            icon: 'warning',
            title: '¿Confirmar?',
            text: boton.getAttribute('data-confirmar'),
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r && r.isConfirmed) { form.submit(); }
        });
    });

    // Envío programático: form.submit() no dispara el evento 'submit', así que se intercepta aquí.
    var envioNativo = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (this.hasAttribute('data-ajax')) {
            enviarFormulario(this, null);
            return;
        }
        return envioNativo.call(this);
    };

    // ------------------------------------------------------------------
    // 2. LISTADOS CON FILTROS Y PAGINACIÓN
    // ------------------------------------------------------------------

    var navegoConAjax = false;

    function cargarLista(url, contenedor, empujar) {
        var activo = document.activeElement;
        var idFoco = (activo && activo.id && contenedor.contains(activo)) ? activo.id : null;
        var posCursor = (idFoco && typeof activo.selectionStart === 'number') ? activo.selectionStart : null;

        contenedor.style.opacity = '0.5';
        contenedor.style.pointerEvents = 'none';

        fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function (r) {
                var tipo = r.headers.get('Content-Type') || '';
                if (tipo.indexOf('application/json') !== -1) {
                    window.location.href = url;
                    return null;
                }
                return r.text();
            })
            .then(function (html) {
                if (html === null) { return; }
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nuevo = doc.getElementById(contenedor.id);
                if (!nuevo) { window.location.href = url; return; }
                contenedor.innerHTML = nuevo.innerHTML;
                if (idFoco) {
                    var nuevoCampo = document.getElementById(idFoco);
                    if (nuevoCampo) {
                        nuevoCampo.focus();
                        if (posCursor !== null && typeof nuevoCampo.setSelectionRange === 'function') {
                            nuevoCampo.setSelectionRange(posCursor, posCursor);
                        }
                    }
                }
                if (empujar) {
                    window.history.pushState({ ajaxLista: true }, '', url);
                    navegoConAjax = true;
                }
                contenedor.dispatchEvent(new CustomEvent('ajax:lista', { bubbles: true }));
            })
            .catch(function () {
                window.location.href = url;
            })
            .then(function () {
                contenedor.style.opacity = '';
                contenedor.style.pointerEvents = '';
            });
    }

    function urlDeFiltro(form) {
        var base = form.getAttribute('action') || window.location.pathname;
        var params = new URLSearchParams();
        new FormData(form).forEach(function (valor, clave) {
            if (typeof valor === 'string' && valor !== '') { params.append(clave, valor); }
        });
        return base + (base.indexOf('?') === -1 ? '?' : '&') + params.toString();
    }

    function contenedorDe(selector) {
        return selector ? document.querySelector(selector) : null;
    }

    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-ajax-filtro')) { return; }
        var cont = contenedorDe(form.getAttribute('data-ajax-filtro'));
        if (!cont) { return; }
        evento.preventDefault();
        cargarLista(urlDeFiltro(form), cont, true);
    });

    // Los <select> del filtro se aplican al cambiar; los inputs con data-ajax-vivo, al escribir (con pausa).
    document.addEventListener('change', function (evento) {
        var campo = evento.target;
        var form = campo && campo.form;
        if (!form || !form.hasAttribute('data-ajax-filtro')) { return; }
        if (campo.tagName === 'SELECT' || campo.type === 'date' || campo.type === 'checkbox') {
        var cont = contenedorDe(form.getAttribute('data-ajax-filtro'));
        if (cont) { cargarLista(urlDeFiltro(form), cont, true); }
        }
    });

    var temporizador = null;
    document.addEventListener('input', function (evento) {
        var campo = evento.target;
        var form = campo && campo.form;
        if (!form || !form.hasAttribute('data-ajax-filtro') || !campo.hasAttribute('data-ajax-vivo')) { return; }
        var cont = contenedorDe(form.getAttribute('data-ajax-filtro'));
        if (!cont) { return; }
        clearTimeout(temporizador);
        temporizador = setTimeout(function () { cargarLista(urlDeFiltro(form), cont, true); }, 350);
    });

    // Paginación dentro del contenedor y enlaces con data-ajax-link.
    document.addEventListener('click', function (evento) {
        if (evento.defaultPrevented || evento.button !== 0 || evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) { return; }
        var enlace = evento.target.closest ? evento.target.closest('a[href]') : null;
        if (!enlace) { return; }

        var cont = null;
        var destino = enlace.getAttribute('data-ajax-link');
        if (destino) {
            cont = contenedorDe(destino);
        } else {
            var dentro = enlace.closest('[data-ajax-lista]');
            if (dentro && enlace.closest('.pagination')) { cont = dentro; }
        }
        if (!cont) { return; }

        evento.preventDefault();
        cargarLista(enlace.getAttribute('href'), cont, true);
    });

    // Botón "atrás" tras haber filtrado con AJAX: se recarga la vista correspondiente.
    window.addEventListener('popstate', function (evento) {
        if (navegoConAjax || (evento.state && evento.state.ajaxLista)) { window.location.reload(); }
    });

    // ------------------------------------------------------------------
    // 3. ALERTAS FLASH GLOBALES (#caja-flash-oculta)
    // ------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        var cajaFlash = document.getElementById('caja-flash-oculta');
        if (cajaFlash && cajaFlash.innerText.trim() !== '') {
            var textoMensaje = cajaFlash.innerText.trim();
            var textoLower = textoMensaje.toLowerCase();
            var aviso = cajaFlash.querySelector('.mensaje');
            var tipo = 'success';

            if ((aviso && aviso.classList.contains('mensaje-error')) ||
                textoLower.includes('error') ||
                textoLower.includes('obligatorio') ||
                textoLower.includes('ya existe') ||
                textoLower.includes('falló')) {
                tipo = 'error';
            } else if (aviso && aviso.classList.contains('mensaje-aviso')) {
                tipo = 'warning';
            }

            var titulos = {
                success: '¡Éxito!',
                error: 'Atención',
                warning: 'Aviso'
            };

            alertar({
                icon: tipo,
                title: titulos[tipo],
                text: textoMensaje,
                confirmButtonColor: tipo === 'error' ? '#ef4444' : '#2563eb',
                timer: tipo === 'error' ? undefined : 3000,
                timerProgressBar: tipo !== 'error'
            });

            cajaFlash.innerHTML = '';
        }

        // ------------------------------------------------------------------
        // 4. BUSCADOR EN VIVO DE CATEGORÍAS (#buscadorCategorias)
        // ------------------------------------------------------------------
        var inputBuscador = document.getElementById('buscadorCategorias');
        if (inputBuscador) {
            inputBuscador.addEventListener('input', function () {
                var texto = this.value.toLowerCase().trim();
                var filas = document.querySelectorAll('.fila-busqueda');
                var count = 0;

                filas.forEach(function (fila) {
                    var celdaNombre = fila.querySelector('.nombre-cat');
                    var nombreCat = celdaNombre ? celdaNombre.textContent.toLowerCase() : '';
                    if (nombreCat.includes(texto)) {
                        fila.style.display = '';
                        count++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                var contadorTag = document.getElementById('contadorFilas');
                if (contadorTag) {
                    contadorTag.textContent = count + ' registros';
                }
            });
        }

        // ------------------------------------------------------------------
        // 5. CONTADOR DE CARACTERES Y RESTAURAR CAMPOS EN FORMULARIOS
        // ------------------------------------------------------------------
        var textarea = document.getElementById('descripcion');
        var counter = document.getElementById('char-counter');
        var resetBtn = document.getElementById('reset-btn');
        var inputName = document.getElementById('nombre');

        var originalName = inputName ? inputName.value : '';
        var originalDesc = textarea ? textarea.value : '';

        function updateCounter() {
            if (textarea && counter) {
                var max = textarea.getAttribute('maxlength') || 250;
                counter.textContent = textarea.value.length + ' / ' + max;
            }
        }

        if (textarea) {
            updateCounter();
            textarea.addEventListener('input', updateCounter);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (inputName) { inputName.value = originalName; }
                if (textarea) {
                    textarea.value = originalDesc;
                    updateCounter();
                }
                alertar({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Campos restaurados',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });

    window.BonanzaAjax = { enviarFormulario: enviarFormulario, cargarLista: cargarLista };
// ------------------------------------------------------------------
    // 6. ARQUEO Y CIERRE DE CAJA (#efectivoContado y #formCierre)
    // ------------------------------------------------------------------
    var formatoArqueo = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var clasesArqueo  = ['has-text-grey-dark', 'has-text-success', 'has-text-warning-dark', 'has-text-danger'];

    function dineroArqueo(centavos) {
        return (centavos < 0 ? '-' : '') + '$' + formatoArqueo.format(Math.abs(centavos) / 100);
    }

    function calcularArqueoCaja() {
        var entrada = document.getElementById('efectivoContado');
        var sistema = document.getElementById('efectivoSistemaTexto');
        var salida  = document.getElementById('diferenciaCaja');
        var estado  = document.getElementById('diferenciaEstado');
        if (!entrada || !sistema || !salida) { return null; }

        var centavosSistema = parseInt(sistema.getAttribute('data-centavos'), 10) || 0;

        function pintar(clase, texto, nota) {
            clasesArqueo.forEach(function (c) { salida.classList.remove(c); });
            salida.classList.add(clase);
            salida.textContent = texto;
            if (estado) {
                estado.textContent = nota;
                estado.className = 'is-size-7 mt-1 mb-0 ' + (clase === 'has-text-grey-dark' ? 'has-text-grey' : clase + ' has-text-weight-bold');
            }
        }

        var texto = entrada.value.trim().replace(',', '.');
        if (texto === '' || isNaN(Number(texto))) {
            pintar('has-text-grey-dark', '$0,00', 'Escribe el efectivo contado para calcularla.');
            return null;
        }

        var diferencia = Math.round(Number(texto) * 100) - centavosSistema;
        if (diferencia === 0) {
            pintar('has-text-success', dineroArqueo(0), 'La caja cuadra');
        } else if (diferencia > 0) {
            pintar('has-text-warning-dark', '+' + dineroArqueo(diferencia), 'Sobrante: hay más efectivo del que registra el sistema');
        } else {
            pintar('has-text-danger', dineroArqueo(diferencia), 'Faltante: hay menos efectivo del que registra el sistema');
        }
        return diferencia;
    }

    document.addEventListener('input', function (evento) {
        if (evento.target && evento.target.id === 'efectivoContado') {
            calcularArqueoCaja();
        }
    });

    document.addEventListener('change', function (evento) {
        if (evento.target && evento.target.id === 'efectivoContado') {
            calcularArqueoCaja();
        }
    });

    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form || form.id !== 'formCierre') { return; }
        if (form.__cierreConfirmado) { return; }
        evento.preventDefault();

        var dif = calcularArqueoCaja();
        var aviso = 'Vas a realizar el cierre de caja de este día. ';
        if (dif !== null && dif !== 0) {
            aviso += 'La caja NO cuadra (' + (dif > 0 ? '+' : '') + dineroArqueo(dif) + '). ';
        }
        aviso += 'El cierre no se podrá modificar y tu sesión se cerrará.';

        alertar({
            icon: 'warning',
            title: '¿Realizar cierre de caja?',
            text: aviso,
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r && r.isConfirmed) {
                form.__cierreConfirmado = true;
                var boton = document.getElementById('btnRealizarCierre');
                if (boton) {
                    boton.classList.add('is-loading');
                    boton.disabled = true;
                }
                envioNativo.call(form);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', calcularArqueoCaja);
    document.addEventListener('ajax:lista', calcularArqueoCaja);
    
     window.BonanzaAjax = { enviarFormulario: enviarFormulario, cargarLista: cargarLista };
})()