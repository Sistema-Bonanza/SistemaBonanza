document.addEventListener('DOMContentLoaded', () => {
    // 1. Botones de impresión manual
    document.querySelectorAll('[data-imprimir]').forEach(btn => {
        btn.addEventListener('click', () => window.print());
    });

    // 2. Abrir modal de anulación
    document.querySelectorAll('[data-modal-abrir]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalAbrir);
            if (!modal) return;
            modal.classList.add('is-active');
            const campo = modal.querySelector('textarea, input:not([type="hidden"])');
            if (campo) campo.focus();
        });
    });

    // 3. Cerrar modal (fondo oscuro, botón X o Cancelar)
    document.querySelectorAll('[data-modal-cerrar]').forEach(el => {
        el.addEventListener('click', () => {
            const modal = el.closest('.modal');
            if (modal) modal.classList.remove('is-active');
        });
    });

    // 4. Cerrar modal con la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.is-active').forEach(m => m.classList.remove('is-active'));
        }
    });

    // 5. Disparar impresión / Guardar como PDF si viene con ?imprimir=1
    const paramsUrl = new URLSearchParams(window.location.search);
    if (document.body.hasAttribute('data-autoimprimir') || paramsUrl.get('imprimir') === '1') {
        setTimeout(() => window.print(), 400);
    }
});