// =====================================================================
// 1. FUNCIONES GLOBALES (Deben estar afuera para que el HTML las vea)
// =====================================================================
function confirmarDesactivacion(formulario) {
    Swal.fire({
        title: '¿Desactivar producto?',
        text: "El artículo dejará de estar visible en el catálogo principal.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#eab308',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.submit();
        }
    });
}

// =====================================================================
// 2. LÓGICA DEL DOM (Se ejecuta al cargar la página)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {

    // --- LÓGICA DE ALERTAS PHP ---
    if (typeof window.sweetErrorStock !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: '¡Operación Denegada!',
            text: window.sweetErrorStock,
            confirmButtonColor: '#004ac6'
        });
    }

    // --- LÓGICA DE INTERFAZ (Buscador y pestañas de categoría) ---
    const searchInput = document.getElementById('product-search-input');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const rows = document.querySelectorAll('.product-row');
    const counterBadge = document.getElementById('product-counter');
    const paginationInfo = document.getElementById('pagination-info');
    let currentCategory = 'all';

    function applyFilters() {
        const term = (searchInput?.value || '').toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const category = row.getAttribute('data-category') || '';

            const matchesSearch = name.includes(term) || code.includes(term);
            const matchesCategory = currentCategory === 'all' || category.toLowerCase() === currentCategory.toLowerCase();

            if (matchesSearch && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (counterBadge) {
            counterBadge.textContent = visibleCount + (visibleCount === 1 ? ' registro' : ' registros');
        }
        if (paginationInfo) {
            paginationInfo.innerHTML = `Mostrando <strong class="has-text-dark">${visibleCount > 0 ? '1 - ' + visibleCount : '0'}</strong> de <strong class="has-text-dark">${visibleCount}</strong> productos encontrados`;
        }
    }

    searchInput?.addEventListener('input', applyFilters);

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => {
                t.classList.remove('is-link');
                t.classList.add('is-light');
            });
            this.classList.remove('is-light');
            this.classList.add('is-link');

            currentCategory = this.getAttribute('data-filter') || 'all';
            applyFilters();
        });
    });

    // --- MODAL DE CONFIRMACIÓN ---
    const modal = document.getElementById('delete-modal');
    const modalBg = document.getElementById('modal-bg');
    const cancelModal = document.getElementById('btn-cancel-modal');
    const confirmDelete = document.getElementById('btn-confirm-delete');
    let rowToDelete = null;

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            rowToDelete = this.closest('tr');
            modal.classList.add('is-active');
        });
    });

    function closeModal() {
        modal.classList.remove('is-active');
        rowToDelete = null;
    }

    cancelModal?.addEventListener('click', closeModal);
    modalBg?.addEventListener('click', closeModal);

    confirmDelete?.addEventListener('click', function() {
        if (rowToDelete) {
            rowToDelete.remove();
            applyFilters();
        }
        closeModal();
    });

    // --- ALERTA DE STOCK MÍNIMO (SweetAlert) ---
    const bajo = Array.isArray(window.productosBajoMinimo) ? window.productosBajoMinimo : [];
    if (bajo.length > 0 && typeof Swal !== 'undefined') {
        const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const visibles = bajo.slice(0, 8).map(p =>
            '<li><strong>' + esc(p.nombre) + '</strong> <span style="color:#64748b">(' + esc(p.codigo) + ')</span>: ' +
            '<span style="color:#dc2626;font-weight:700">' + p.stock + '</span> de mín. ' + p.minimo + '</li>'
        ).join('');
        const resto = bajo.length > 8 ? '<p style="margin-top:8px;color:#64748b">y ' + (bajo.length - 8) + ' más (marcados en rojo en la tabla)</p>' : '';
        Swal.fire({
            icon: 'warning',
            title: bajo.length === 1 ? '1 producto bajo el stock mínimo' : bajo.length + ' productos bajo el stock mínimo',
            html: '<ul style="text-align:left;margin:0 0 0 1.2rem;list-style:disc;">' + visibles + '</ul>' + resto,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc2626'
        });
    }
});