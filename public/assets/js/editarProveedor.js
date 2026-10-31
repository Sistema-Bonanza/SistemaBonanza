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
       2. VALIDACIÓN DEL FORMULARIO DE EDICIÓN
       ======================================================= */
    const formEditarProveedor = document.getElementById('formEditarProveedor');
    if (formEditarProveedor) {
        formEditarProveedor.addEventListener('submit', function (e) {
            const nombreContacto = document.getElementById('nombre_contacto');
            const telefono = document.getElementById('telefono');

            if (nombreContacto && nombreContacto.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'El nombre del contacto no puede quedar vacío.',
                    confirmButtonColor: '#2563eb'
                });
                nombreContacto.focus();
                return;
            }

            if (telefono && telefono.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'El teléfono de contacto no puede quedar vacío.',
                    confirmButtonColor: '#2563eb'
                });
                telefono.focus();
                return;
            }
        });
    }

});