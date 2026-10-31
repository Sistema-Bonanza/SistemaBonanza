/**
 * reportes.js - Módulo de Reportes
 *
 * Solo hace una cosa: el botón "Imprimir" (cualquier elemento con data-imprimir) abre
 * el diálogo de impresión del navegador. Los filtros son formularios GET normales,
 * así que no necesitan JavaScript.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-imprimir]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            window.print();
        });
    });
});
