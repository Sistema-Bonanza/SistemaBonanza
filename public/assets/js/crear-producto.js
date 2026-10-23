document.addEventListener('DOMContentLoaded', () => {
    // 1. Generador automático de SKU
    const btnAutoSku = document.getElementById('btn-auto-sku');
    if (btnAutoSku) {
        btnAutoSku.addEventListener('click', () => {
            const prefixes = ['BNZ', 'CHM', 'ALM', 'TAB', 'PKG'];
            const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
            const randomNum = Math.floor(100 + Math.random() * 900);
            const skuInput = document.getElementById('codigo');
            if (skuInput) {
                skuInput.value = `${randomPrefix}-${randomNum}`;
                skuInput.focus();
            }
        });
    }

    // 2. Confirmación al volver atrás
    const btnVolver = document.getElementById('btn-volver');
    if (btnVolver) {
        btnVolver.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Desea salir o descartar los cambios sin guardar?')) {
                window.history.back();
            }
        });
    }
});