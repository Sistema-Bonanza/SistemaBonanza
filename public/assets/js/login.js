document.addEventListener('DOMContentLoaded', () => {
    // 1. Limpiar el historial para evitar el reenvío del formulario con F5
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // 1b. Aviso de éxito que llega por flash (por ejemplo "Cierre exitoso" al terminar un cierre de caja).
    // mostrarFlash() imprime <div class="mensaje mensaje-exito">; se muestra como alerta y se oculta el div.
    var avisoExito = document.querySelector('.mensaje.mensaje-exito');
    if (avisoExito && typeof Swal !== 'undefined') {
        var textoExito = avisoExito.textContent.trim();
        avisoExito.style.display = 'none';
        Swal.fire({
            icon: 'success',
            title: 'Cierre Exitoso',
            text: textoExito,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Entendido',
            customClass: { popup: 'rounded-xl shadow-lg' }
        });
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