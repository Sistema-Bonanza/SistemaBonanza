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

    /* =======================================================
       3. FILTRADO DINÁMICO DE PRODUCTOS POR CATEGORÍA
       ======================================================= */
    const categoryButtons = document.querySelectorAll('#category-filters .filter-btn');
    const productCards = document.querySelectorAll('.tarjeta-producto-item');

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Cambiar estilos de los botones seleccionados
            categoryButtons.forEach(b => {
                b.classList.remove('is-link', 'active');
                b.classList.add('is-light');
            });
            btn.classList.remove('is-light');
            btn.classList.add('is-link', 'active');

            const selectedCat = btn.getAttribute('data-categoria').toLowerCase().trim();

            // Ocultar / Mostrar tarjetas según categoría
            productCards.forEach(card => {
                const cardCat = card.getAttribute('data-categoria').toLowerCase().trim();
                if (selectedCat === 'todas' || cardCat === selectedCat) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    /* =======================================================
       4. DELEGACIÓN DE EVENTOS PARA BOTONES DE AGREGAR (+)
       ======================================================= */
    document.addEventListener('click', (e) => {
        const btnAdd = e.target.closest('.btn-add-cart');
        if (btnAdd) {
            const id = btnAdd.getAttribute('data-id');
            const nombre = btnAdd.getAttribute('data-nombre');
            const precio = parseFloat(btnAdd.getAttribute('data-precio')) || 0;

            agregarAlCarrito(id, nombre, precio);
        }
    });

});

/* =======================================================
   5. LÓGICA Y ESTADO DEL CARRITO DE PRESUPUESTO
   ======================================================= */
let carritoPresupuesto = [];

function agregarAlCarrito(id, nombre, precio) {
    const existe = carritoPresupuesto.find(item => item.id === id);

    if (existe) {
        existe.cantidad += 1;
    } else {
        carritoPresupuesto.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }

    renderizarCarrito();

    Swal.fire({
        icon: 'success',
        title: 'Agregado al Presupuesto',
        text: `${nombre} se ha añadido a la cotización.`,
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 1800
    });
}

function renderizarCarrito() {
    const container = document.getElementById('cart-items-container');
    if (!container) return;

    container.innerHTML = '';

    if (carritoPresupuesto.length === 0) {
        container.innerHTML = `
            <div class="has-text-centered py-5 has-text-grey">
                <span class="material-symbols-outlined is-size-3">shopping_basket</span>
                <p class="is-size-7 mt-1 font-weight-semibold">El carrito de cotización está vacío.</p>
            </div>
        `;
        recalcularTotales();
        return;
    }

    carritoPresupuesto.forEach((item, index) => {
        const subtotalItem = item.precio * item.cantidad;
        const row = document.createElement('div');
        row.className = 'is-flex is-justify-content-space-between is-align-items-center mb-3 pb-2';
        row.style.borderBottom = '1px solid #f1f5f9';

        row.innerHTML = `
            <div style="flex: 1; min-width: 0;">
                <p class="is-size-7 has-text-weight-bold has-text-dark is-truncated">${item.nombre}</p>
                <p class="is-size-7 has-text-grey">$${item.precio.toFixed(2)} c/u</p>
            </div>
            <div class="is-flex is-align-items-center has-background-light rounded border mx-2 px-1">
                <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="cambiarCantidad(${index}, -1)">-</button>
                <span class="is-size-7 has-text-weight-bold px-2">${item.cantidad}</span>
                <button type="button" class="button is-small is-ghost has-text-grey px-1" onclick="cambiarCantidad(${index}, 1)">+</button>
            </div>
            <div class="has-text-right">
                <p class="is-size-7 has-text-weight-bold font-mono has-text-dark">$${subtotalItem.toFixed(2)}</p>
                <a href="#" class="has-text-danger is-size-7" onclick="eliminarDelCarrito(${index}); return false;">Quitar</a>
            </div>
        `;

        container.appendChild(row);
    });

    recalcularTotales();
}

function cambiarCantidad(index, cambio) {
    if (carritoPresupuesto[index]) {
        carritoPresupuesto[index].cantidad += cambio;
        if (carritoPresupuesto[index].cantidad <= 0) {
            carritoPresupuesto.splice(index, 1);
        }
        renderizarCarrito();
    }
}

function eliminarDelCarrito(index) {
    if (carritoPresupuesto[index]) {
        carritoPresupuesto.splice(index, 1);
        renderizarCarrito();
    }
}

function recalcularTotales() {
    let subtotal = 0;
    let totalItems = 0;

    carritoPresupuesto.forEach(item => {
        subtotal += item.precio * item.cantidad;
        totalItems += item.cantidad;
    });

    let tax = subtotal * 0.16;
    let total = subtotal + tax;

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