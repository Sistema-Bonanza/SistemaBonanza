document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const mensajeLower = mensaje.toLowerCase();

        // Incluimos 'válido' para capturar el error de 'Selecciona un rol válido'
        const esError = mensajeLower.includes('error') || 
                        mensajeLower.includes('obligatorio') || 
                        mensajeLower.includes('existe') || 
                        mensajeLower.includes('inválido') ||
                        mensajeLower.includes('válido') ||
                        mensajeLower.includes('falló');

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
       2. ALTERNAR VISIBILIDAD DE CONTRASEÑA
       ======================================================= */
    const toggleBtn = document.getElementById('togglePassword');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && pwdInput && eyeIcon) {
        toggleBtn.addEventListener('click', () => {
            const esPassword = pwdInput.getAttribute('type') === 'password';
            pwdInput.setAttribute('type', esPassword ? 'text' : 'password');
            eyeIcon.textContent = esPassword ? 'visibility_off' : 'visibility';
        });
    }

    /* =======================================================
       3. VALIDACIÓN DE USUARIO (SIN ESPACIOS)
       ======================================================= */
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function () {
            // Eliminar espacios accidentalmente ingresados
            this.value = this.value.replace(/\s+/g, '');
        });
    }

    /* =======================================================
       4. BOTÓN RESTAURAR VALORES ORIGINALES
       ======================================================= */
    const btnRestaurar = document.getElementById('btnRestaurar');
    const originalUsername = document.getElementById('originalUsername');
    
    if (btnRestaurar && originalUsername) {
        btnRestaurar.addEventListener('click', () => {
            if(usernameInput) usernameInput.value = originalUsername.value;
            if(pwdInput) pwdInput.value = '';
        });
    }

});