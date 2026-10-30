document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const esError = mensaje.toLowerCase().includes('error') || 
                        mensaje.toLowerCase().includes('obligatorio') || 
                        mensaje.toLowerCase().includes('existe') ||
                        mensaje.toLowerCase().includes('falló');

        Swal.fire({
            icon: esError ? 'error' : 'success',
            title: esError ? 'Atención' : '¡Éxito!',
            text: mensaje,
            confirmButtonColor: esError ? '#ef4444' : '#2563eb',
            timer: esError ? undefined : 3000,
            timerProgressBar: !esError
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. VALIDACIÓN PREVIA DEL FORMULARIO
       ======================================================= */
    const formCrearCliente = document.getElementById('formCrearCliente');
    if (formCrearCliente) {
        formCrearCliente.addEventListener('submit', function (e) {
            const numDocumento = document.getElementById('num_documento');
            const nombreRazonSocial = document.getElementById('nombre_razon_social');

            if (numDocumento && numDocumento.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Debe ingresar el número de documento.',
                    confirmButtonColor: '#2563eb'
                });
                numDocumento.focus();
                return;
            }

            if (nombreRazonSocial && nombreRazonSocial.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Debe ingresar el nombre o razón social.',
                    confirmButtonColor: '#2563eb'
                });
                nombreRazonSocial.focus();
                return;
            }
        });
    }

});