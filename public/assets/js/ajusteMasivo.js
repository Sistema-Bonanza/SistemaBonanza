document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const mensajeLower = mensaje.toLowerCase();

        const esError = mensajeLower.includes('error') || 
                        mensajeLower.includes('aviso') || 
                        mensajeLower.includes('inválido') ||
                        mensajeLower.includes('ningún producto');

        Swal.fire({
            icon: esError ? 'warning' : 'success',
            title: esError ? 'Atención' : '¡Éxito!',
            text: mensaje,
            confirmButtonColor: '#2563eb',
            timer: esError ? undefined : 4000,
            timerProgressBar: !esError
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. CONFIRMACIÓN PROFESIONAL CON SWEETALERT2
       ======================================================= */
    const formAplicar = document.getElementById('form-aplicar');
    if (formAplicar) {
        formAplicar.addEventListener('submit', function (e) {
            // Previene el envío inmediato para mostrar la alerta interactiva
            e.preventDefault();

            const botonSubmit = this.querySelector('button[type="submit"]');

            Swal.fire({
                title: '¿Confirmar revalorización masiva?',
                text: 'Se modificará el precio de venta en el lote de productos seleccionados. Esta acción quedará registrada en el historial de auditoría.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981', // Verde éxito
                cancelButtonColor: '#64748b',  // Gris secundario
                confirmButtonText: 'Sí, aplicar cambios',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Deshabilitar botón para evitar doble envío
                    if (botonSubmit) {
                        botonSubmit.classList.add('is-loading');
                        botonSubmit.disabled = true;
                    }
                    // Envía el formulario formalmente
                    formAplicar.submit();
                }
            });
        });
    }

});