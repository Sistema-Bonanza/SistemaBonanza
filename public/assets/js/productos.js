console.log("¡El archivo productos.js está vivo y conectado!");
// =====================================================================
// 1. FUNCIONES GLOBALES (Deben estar afuera para que el HTML las vea)
// =====================================================================
function confirmarDesactivacion(formulario) {
    console.log("¡Botón presionado! Llamando a SweetAlert..."); // <-- Agrega est
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
    // Verificamos si la variable global fue creada por el puente PHP
    if (typeof window.sweetErrorStock !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: '¡Operación Denegada!',
            text: window.sweetErrorStock,
            confirmButtonColor: '#004ac6'
        });
    }

    // --- LÓGICA DE INTERFAZ ---
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

    document.getElementById('btn-export-csv')?.addEventListener('click', function() {
        const headers = ['Producto', 'Codigo', 'Costo', 'Stock', 'Estado'];
        const rowsData = [
            ['Chimo Tapa Amarilla', '006', '$0.00', '120', 'Activo'],
            ['Tabaco Kijih', '004', '$0.00', '650', 'Activo'],
            ['Cigarro Belmont', '002', '$0.00', '200', 'Activo']
        ];
        
        let csvContent = 'data:text/csv;charset=utf-8,' + headers.join(',') + '\n' + rowsData.map(e => e.join(',')).join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'catalogo_productos_bonanza.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});