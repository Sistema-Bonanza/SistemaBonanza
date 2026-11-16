/**
 * public/assets/js/venta.js
 *
 * Interactividad de la vista de detalle de factura / venta:
 * - Impresión manual ([data-imprimir]) y automática ([data-autoimprimir]).
 * - Apertura y cierre de modales Bulma ([data-modal-abrir], [data-modal-cerrar], tecla Escape).
 * - Validación y confirmación con SweetAlert2 para <form data-confirmar data-ajax>
 *   en coordinación con ajax.js.
 */
(function () {
    'use strict';

    // ------------------------------------------------------------------
    // 1. IMPRESIÓN AUTOMÁTICA Y MANUAL
    // ------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        if (document.body && document.body.hasAttribute('data-autoimprimir')) {
            setTimeout(function () {
                window.print();
            }, 300);
        }
    });

    document.addEventListener('click', function (evento) {
        var btnImprimir = evento.target.closest ? evento.target.closest('[data-imprimir]') : null;
        if (btnImprimir) {
            evento.preventDefault();
            window.print();
        }
    });

    // ------------------------------------------------------------------
    // 2. MODALES BULMA (ABRIR / CERRAR)
    // ------------------------------------------------------------------
    function abrirModal(idModal) {
        var modal = document.getElementById(idModal);
        if (!modal) { return; }
        modal.classList.add('is-active');
        document.documentElement.classList.add('is-clipped');

        var campoFoco = modal.querySelector('textarea, input:not([type="hidden"]), select');
        if (campoFoco) {
            setTimeout(function () { campoFoco.focus(); }, 50);
        }
    }

    function cerrarModal(modal) {
        if (!modal) { return; }
        modal.classList.remove('is-active');
        if (!document.querySelector('.modal.is-active')) {
            document.documentElement.classList.remove('is-clipped');
        }
    }

    document.addEventListener('click', function (evento) {
        var disparadorAbrir = evento.target.closest ? evento.target.closest('[data-modal-abrir]') : null;
        if (disparadorAbrir) {
            evento.preventDefault();
            abrirModal(disparadorAbrir.getAttribute('data-modal-abrir'));
            return;
        }

        var disparadorCerrar = evento.target.closest ? evento.target.closest('[data-modal-cerrar]') : null;
        if (disparadorCerrar) {
            evento.preventDefault();
            cerrarModal(disparadorCerrar.closest('.modal'));
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            var modalActivo = document.querySelector('.modal.is-active');
            if (modalActivo) {
                cerrarModal(modalActivo);
            }
        }
    });

    // ------------------------------------------------------------------
    // 3. CONFIRMACIÓN SWEETALERT2 EN FORMULARIOS (<form data-confirmar>)
    // ------------------------------------------------------------------
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-confirmar')) { return; }

        if (form.__confirmado) {
            form.__confirmado = false;
            return;
        }

        // Detiene el envío inmediato (también evita que ajax.js lo dispare antes de confirmar)
        evento.preventDefault();

        // Evita que se envíen solo espacios en blanco en el motivo de anulación
        var campoMotivo = form.querySelector('#motivo_anulacion');
        if (campoMotivo && campoMotivo.value.trim().length < 5) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Motivo requerido',
                    text: 'Debes escribir un motivo válido de al menos 5 caracteres (sin contar espacios).',
                    confirmButtonColor: '#2563eb'
                }).then(function () {
                    campoMotivo.focus();
                });
            } else {
                window.alert('Debes escribir un motivo válido de al menos 5 caracteres.');
                campoMotivo.focus();
            }
            return;
        }

        var mensaje = form.getAttribute('data-confirmar') || '¿Seguro que deseas realizar esta acción?';
        var submitter = evento.submitter || null;

        function ejecutarEnvio() {
            if (form.hasAttribute('data-ajax') && window.BonanzaAjax && typeof window.BonanzaAjax.enviarFormulario === 'function') {
                window.BonanzaAjax.enviarFormulario(form, submitter);
            } else {
                form.__confirmado = true;
                form.submit();
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: '¿Confirmar anulación?',
                text: mensaje,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, anular factura',
                cancelButtonText: 'Cancelar'
            }).then(function (resultado) {
                if (resultado && resultado.isConfirmed) {
                    ejecutarEnvio();
                }
            });
        } else if (window.confirm(mensaje)) {
            ejecutarEnvio();
        }
    });
})();