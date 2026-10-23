document.addEventListener('DOMContentLoaded', () => {
    // 1. Limpiar el historial para evitar el reenvío del formulario con F5
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // 2. Mostrar la alerta de SweetAlert2 si existe un mensaje de error global
    if (typeof errorMessage !== 'undefined' && errorMessage.trim() !== '') {
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