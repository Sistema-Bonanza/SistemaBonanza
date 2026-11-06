document.addEventListener('DOMContentLoaded', () => {
    /* =======================================================
       INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const mensajeLower = mensaje.toLowerCase();

        const esError = mensajeLower.includes('error') || 
                        mensajeLower.includes('aviso') || 
                        mensajeLower.includes('inválido');

        Swal.fire({
            icon: esError ? 'warning' : 'success',
            title: esError ? 'Atención' : 'Información',
            text: mensaje,
            confirmButtonColor: '#2563eb',
            timer: esError ? undefined : 3000
        });
        cajaFlash.innerHTML = '';
    }
});