<style>
    body {
        font-family: Calibri, "Segoe UI", Arial, sans-serif;
        font-size: 11pt;
        color: #1e293b;
    }
    table {
        border-collapse: collapse;
    }
    /* Banner Corporativo Azul del Sistema */
    .banner-empresa {
        background-color: #1e3a8a;
        color: #ffffff;
        font-size: 15pt;
        font-weight: bold;
        padding: 10px 12px;
        border: 1px solid #1e3a8a;
    }
    .banner-titulo {
        background-color: #2563eb;
        color: #ffffff;
        font-size: 11.5pt;
        font-weight: bold;
        padding: 7px 12px;
        border: 1px solid #2563eb;
    }
    .meta-info {
        background-color: #f8fafc;
        color: #334155;
        font-size: 10pt;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
    }
    /* Títulos de Sección */
    .seccion-titulo {
        background-color: #dbeafe;
        color: #1e3a8a;
        font-size: 10.5pt;
        font-weight: bold;
        text-transform: uppercase;
        padding: 7px 10px;
        border: 1px solid #93c5fd;
    }
    /* Encabezados de Tabla */
    .th-tabla {
        background-color: #1e40af;
        color: #ffffff;
        font-weight: bold;
        font-size: 10pt;
        text-align: center;
        padding: 8px 6px;
        border: 1px solid #1e3a8a;
        vertical-align: middle;
    }
    .th-grupo {
        background-color: #172554;
        color: #dbeafe;
        font-weight: bold;
        font-size: 9.5pt;
        text-align: center;
        padding: 5px 6px;
        border: 1px solid #1e3a8a;
        text-transform: uppercase;
    }
    /* Celdas de Datos */
    .td-dato {
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        vertical-align: middle;
    }
    .fila-par {
        background-color: #f8fafc;
    }
    /* Estados y Alertas (Paleta Bulma) */
    .badge-ok {
        background-color: #effaf5;
        color: #257953;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
    }
    .badge-info {
        background-color: #eff5fb;
        color: #1d4ed8;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
    }
    .badge-alerta {
        background-color: #fffaeb;
        color: #946c00;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
    }
    .badge-peligro {
        background-color: #feecf0;
        color: #cc0f35;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
    }
    .badge-neutro {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
    }
    .fila-peligro {
        background-color: #fef2f2;
    }
    .fila-alerta {
        background-color: #fffbeb;
    }
    .tachado {
        text-decoration: line-through;
        color: #991b1b;
    }
    /* Filas de Subtotales y Totales */
    .fila-subtotal td {
        background-color: #f1f5f9;
        font-weight: bold;
        color: #0f172a;
        border: 1px solid #94a3b8;
        border-top: 2px solid #94a3b8;
        padding: 7px 8px;
    }
    .fila-total td {
        background-color: #eff6ff;
        font-weight: bold;
        font-size: 11pt;
        color: #1e3a8a;
        border: 1px solid #93c5fd;
        border-top: 2px solid #2563eb;
        border-bottom: 2px solid #2563eb;
        padding: 8px;
    }
    /* Formatos numéricos nativos de Excel */
    .texto      { mso-number-format: "\@"; }
    .entero     { mso-number-format: "#,##0"; text-align: right; }
    .entero-dif { mso-number-format: "+#,##0;-#,##0;0"; text-align: right; }
    .moneda     { mso-number-format: "\$#,##0.00"; text-align: right; }
    .moneda-dif { mso-number-format: "+\$#,##0.00;-\$#,##0.00;\$0.00"; text-align: right; }
    .porcentaje { mso-number-format: "0.00\%"; text-align: right; }
    .centro     { text-align: center; }
    .izq        { text-align: left; }
    .der        { text-align: right; }
    .negrita    { font-weight: bold; }
    .txt-verde  { color: #15803d; }
    .txt-rojo   { color: #b91c1c; }

    /* Firmas */
    .caja-firma {
        border-top: 1px solid #334155;
        text-align: center;
        font-size: 9.5pt;
        color: #475569;
        padding-top: 4px;
    }
</style>