document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        Swal.fire({
            icon: 'success',
            title: 'Información',
            text: mensaje,
            confirmButtonColor: '#2563eb',
            timer: 3500
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. CRONÓMETRO Y RELOJ EN VIVO
       ======================================================= */
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaStr = now.toLocaleDateString('es-ES', options);
        const horaStr = now.toLocaleTimeString('es-ES');
        
        const fechaEl = document.getElementById('live-fecha');
        const chronometerEl = document.getElementById('live-chronometer');
        
        if (fechaEl) fechaEl.textContent = fechaStr.charAt(0).toUpperCase() + fechaStr.slice(1);
        if (chronometerEl) chronometerEl.textContent = horaStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

});

/* =======================================================
   3. FUNCIONES DEL CARRITO DE DESPACHO INTERACTIVO
   ======================================================= */
function recalculateTotals() {
    let qCasco = parseInt(document.getElementById('qty-casco')?.innerText || '0');
    let qCamisa = parseInt(document.getElementById('qty-camisa')?.innerText || '0');

    let subtotal = (qCasco * 18.50) + (qCamisa * 22.00);
    let tax = subtotal * 0.16;
    let total = subtotal + tax;
    let totalItems = qCasco + qCamisa;

    const subEl = document.getElementById('cart-subtotal');
    const taxEl = document.getElementById('cart-tax');
    const totEl = document.getElementById('cart-total');
    const countEl = document.getElementById('cart-item-count');

    if (subEl) subEl.innerText = '$' + subtotal.toFixed(2);
    if (taxEl) taxEl.innerText = '$' + tax.toFixed(2);
    if (totEl) totEl.innerText = '$' + total.toFixed(2);
    if (countEl) countEl.innerText = totalItems + ' ítems';
}

function updateQty(itemKey, delta, price) {
    const qtyEl = document.getElementById('qty-' + itemKey);
    const totEl = document.getElementById('total-' + itemKey);
    if (!qtyEl || !totEl) return;

    let current = parseInt(qtyEl.innerText) + delta;
    if (current < 1) current = 1;
    qtyEl.innerText = current;
    totEl.innerText = '$' + (current * price).toFixed(2);

    recalculateTotals();
}

function removeItem(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.style.opacity = '0';
        setTimeout(() => {
            row.remove();
            recalculateTotals();
        }, 200);
    }
}

function resetCart() {
    const qCasco = document.getElementById('qty-casco');
    const qCamisa = document.getElementById('qty-camisa');
    if (qCasco) {
        qCasco.innerText = '0';
        document.getElementById('total-casco').innerText = '$0.00';
    }
    if (qCamisa) {
        qCamisa.innerText = '0';
        document.getElementById('total-camisa').innerText = '$0.00';
    }
    recalculateTotals();
    Swal.fire({
        icon: 'info',
        title: 'Carrito reiniciado',
        text: 'Se han vaciado los ítems del despacho actual.',
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 2000
    });
}

function addItemToCart(name, price) {
    Swal.fire({
        icon: 'success',
        title: 'Ítem agregado',
        text: `${name} añadido al carrito de despacho.`,
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 2000
    });
}

function triggerCheckout() {
    Swal.fire({
        title: '¿Confirmar orden de despacho?',
        text: 'Se procesará la Orden ORD-2026-089 y se descontarán existencias del inventario general.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, cobrar y facturar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: '¡Despacho Exitoso!',
                text: 'La orden ha sido enviada al módulo de Facturación correctamente.',
                confirmButtonColor: '#2563eb'
            });
        }
    });
}