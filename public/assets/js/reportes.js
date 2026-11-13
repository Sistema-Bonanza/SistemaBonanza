/**
 * reportes.js - Módulo de Reportes
 *
 * El botón "Imprimir" (cualquier elemento con data-imprimir) abre el diálogo de impresión.
 * Los filtros de los reportes se envían por AJAX (assets/js/ajax.js, atributo data-ajax-filtro):
 * como el contenido del reporte se reemplaza sin recargar la página, el clic se escucha en el
 * documento (delegación) y así los botones nuevos también funcionan.
 */
document.addEventListener('click', function (evento) {
    var boton = evento.target.closest ? evento.target.closest('[data-imprimir]') : null;
    if (boton) {
        window.print();
    }
});
