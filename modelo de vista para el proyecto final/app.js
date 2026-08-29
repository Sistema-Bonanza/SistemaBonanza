// ============================================================
//  DATOS DE EJEMPLO (simulan la base de datos)
// ============================================================
const products = [
    { id: 1, name: 'Mochila Ejecutiva', price: 62.00, stock: 20, size: 'Única', supplier: 'Proveedor A', category: 'Accesorios', available: true },
    { id: 2, name: 'Bolsa de Lona', price: 32.00, stock: 15, size: 'Única', supplier: 'Proveedor B', category: 'Bolsos', available: true },
    { id: 3, name: 'Cinturón de Cuero', price: 38.00, stock: 8, size: 'M', supplier: 'Proveedor A', category: 'Accesorios', available: true },
    { id: 4, name: 'Zapatillas Running', price: 89.00, stock: 5, size: '42', supplier: 'Proveedor C', category: 'Calzado', available: false },
    { id: 5, name: 'Taza Cerámica Blanca', price: 18.00, stock: 30, size: 'Única', supplier: 'Proveedor D', category: 'Hogar', available: true },
    { id: 6, name: 'Pantalón Casual', price: 20.00, stock: 3, size: 'XL', supplier: 'Proveedor 1', category: 'Ropa', available: true },
    { id: 7, name: 'Camisa Formal', price: 20.00, stock: 100, size: 'L', supplier: 'Bondados norte', category: 'Ropa', available: false },
    { id: 8, name: 'Reloj Deportivo', price: 45.00, stock: 12, size: 'Única', supplier: 'Proveedor E', category: 'Electrónica', available: true }
];

// Proveedores únicos para estadística
const suppliers = [...new Set(products.map(p => p.supplier))];

// ============================================================
//  ESTADÍSTICAS
// ============================================================
function updateStats() {
    const total = products.length;
    const available = products.filter(p => p.available).length;
    const stock = products.reduce((sum, p) => sum + p.stock, 0);
    const uniqueSuppliers = suppliers.length;

    document.getElementById('totalProducts').textContent = total;
    document.getElementById('availableProducts').textContent = available;
    document.getElementById('totalStock').textContent = stock;
    document.getElementById('totalSuppliers').textContent = uniqueSuppliers;
}

// ============================================================
//  RENDER PRODUCTOS (para la lista de compras)
// ============================================================
function renderProductList() {
    const container = document.getElementById('productList');
    container.innerHTML = products.map(p => `
        <div class="product-item" data-id="${p.id}">
            <div class="info">
                <span class="name">${p.name}</span>
                <span class="price">$${p.price.toFixed(2)}</span>
            </div>
            <button class="btn-add" data-id="${p.id}">＋</button>
        </div>
    `).join('');

    // Event listeners para botones "＋"
    document.querySelectorAll('.product-item .btn-add').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = parseInt(this.dataset.id);
            const product = products.find(p => p.id === id);
            if (product) addToCart(product);
        });
    });
}

// ============================================================
//  CARRITO (estado y funciones)
// ============================================================
let cart = [];

function addToCart(product) {
    cart.push({ ...product });
    renderCart();
    // Efecto visual simple (opcional)
    const btn = document.querySelector(`.product-item .btn-add[data-id="${product.id}"]`);
    if (btn) {
        btn.style.transform = 'scale(1.2)';
        setTimeout(() => btn.style.transform = '', 200);
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function resetCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const totalSpan = document.getElementById('cartTotal');

    if (cart.length === 0) {
        container.innerHTML = `<div class="empty-cart">El carrito está vacío</div>`;
        totalSpan.textContent = '$0.00';
        return;
    }

    let html = '';
    let total = 0;
    cart.forEach((item, idx) => {
        total += item.price;
        html += `
            <div class="cart-item">
                <span>${item.name}</span>
                <span>
                    $${item.price.toFixed(2)}
                    <button style="margin-left:8px;background:#fee2e2;border-radius:50%;width:20px;height:20px;font-size:0.7rem;line-height:20px;text-align:center;color:#b91c1c;" data-index="${idx}">✕</button>
                </span>
            </div>
        `;
    });

    container.innerHTML = html;
    totalSpan.textContent = `$${total.toFixed(2)}`;

    // Event listeners para eliminar items
    container.querySelectorAll('[data-index]').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            removeFromCart(index);
        });
    });
}

// ============================================================
//  RENDER TABLA DE INVENTARIO
// ============================================================
function renderInventoryTable() {
    const tbody = document.getElementById('inventoryTableBody');
    tbody.innerHTML = products.map(p => `
        <tr>
            <td>${String(p.id).padStart(3, '0')}</td>
            <td>${p.name}</td>
            <td>$${p.price.toFixed(2)}</td>
            <td>${p.stock} unidades</td>
            <td>${p.size}</td>
            <td>${p.supplier}</td>
            <td>${p.category}</td>
            <td>
                <span class="badge ${p.available ? 'success' : 'danger'}">
                    ${p.available ? 'Disponible' : 'No disponible'}
                </span>
            </td>
        </tr>
    `).join('');
}

// ============================================================
//  EVENTOS DE BOTONES GLOBALES
// ============================================================
document.getElementById('resetCartBtn').addEventListener('click', resetCart);
document.getElementById('endShiftBtn').addEventListener('click', function() {
    if (cart.length === 0) {
        alert('El turno ha finalizado. ¡Hasta luego!');
    } else {
        if (confirm('¿Estás seguro de finalizar el turno? El carrito se vaciará.')) {
            resetCart();
            alert('Turno finalizado correctamente.');
        }
    }
});

// ============================================================
//  INICIALIZACIÓN
// ============================================================
function init() {
    updateStats();
    renderProductList();
    renderInventoryTable();
    renderCart();
}

init();