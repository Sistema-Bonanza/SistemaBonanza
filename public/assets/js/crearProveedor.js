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
    const formCrearProveedor = document.getElementById('formCrearProveedor');
    if (formCrearProveedor) {
        formCrearProveedor.addEventListener('submit', function (e) {
            const rifInput = document.getElementById('rif');
            const razonSocialInput = document.getElementById('razon_social');

            if (rifInput && rifInput.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Debe ingresar el RIF o Identificación Fiscal del proveedor.',
                    confirmButtonColor: '#2563eb'
                });
                rifInput.focus();
                return;
            }

            if (razonSocialInput && razonSocialInput.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Debe ingresar la Razón Social del proveedor.',
                    confirmButtonColor: '#2563eb'
                });
                razonSocialInput.focus();
                return;
            }
        });
    }

});