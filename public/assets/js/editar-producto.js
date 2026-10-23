document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', (e) => {
            // Validación rápida antes de enviar al controlador
            const codigo = document.getElementById('codigo').value.trim();
            const nombre = document.getElementById('nombre').value.trim();
            
            if (!codigo || !nombre) {
                e.preventDefault();
                alert('Por favor, completa los campos obligatorios.');
            }
        });
    }
});