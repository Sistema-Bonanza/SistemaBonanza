document.addEventListener('DOMContentLoaded', () => {
    // 1. Limpiar el historial para evitar el reenvío del formulario con F5
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // 1b. Interceptar cualquier aviso flash (cierre de caja, inactividad, errores de sesión)
    const flashEl = document.querySelector('#flash-wrapper .mensaje, .mensaje');
    if (flashEl && typeof Swal !== 'undefined') {
        const textoFlash = flashEl.textContent.trim();
        flashEl.remove();

        if (textoFlash !== '') {
            let icono = 'warning';
            let titulo = 'Atención';

            if (flashEl.classList.contains('mensaje-exito') || flashEl.classList.contains('is-success')) {
                icono = 'success';
                titulo = 'Cierre Exitoso';
            } else if (flashEl.classList.contains('mensaje-error') || flashEl.classList.contains('is-danger')) {
                icono = 'error';
                titulo = 'Acceso Denegado';
            } else if (/inactividad|expir/i.test(textoFlash)) {
                icono = 'warning';
                titulo = 'Sesión Cerrada por Inactividad';
            }

            Swal.fire({
                icon: icono,
                title: titulo,
                text: textoFlash,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'rounded-xl shadow-lg' }
            });
            return;
        }
    }

    // 2. Mostrar la alerta de SweetAlert2 si existe un mensaje de error global
    if (typeof errorMessage !== 'undefined' && errorMessage.trim() !== '' && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Acceso Denegado',
            text: errorMessage,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Intentar de nuevo',
            customClass: {
                popup: 'rounded-xl shadow-lg'
            }
        });
    }
});

// 3. Función para alternar la visibilidad de la contraseña
function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    if (!pwdInput || !toggleIcon) return;

    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        pwdInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// 4. Animación de carga en el botón al enviar el formulario
function handleLogin() {
    const btn = document.getElementById('submit-btn');
    if (btn) {
        btn.classList.add('is-loading');
    }
    return true;
}