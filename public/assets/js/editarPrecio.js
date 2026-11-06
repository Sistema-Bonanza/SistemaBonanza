document.addEventListener('DOMContentLoaded', () => {

    /* =======================================================
       1. INTERCEPTOR DE MENSAJES FLASH (SweetAlert2)
       ======================================================= */
    const cajaFlash = document.getElementById('caja-flash-oculta');
    if (cajaFlash && cajaFlash.innerText.trim() !== '') {
        const mensaje = cajaFlash.innerText.trim();
        const mensajeLower = mensaje.toLowerCase();

        const esError = mensajeLower.includes('error') || 
                        mensajeLower.includes('obligatorio') || 
                        mensajeLower.includes('falló') ||
                        mensajeLower.includes('inválido');

        Swal.fire({
            icon: esError ? 'error' : 'success',
            title: esError ? 'Atención' : '¡Éxito!',
            text: mensaje,
            confirmButtonColor: esError ? '#ef4444' : '#2563eb',
            timer: esError ? undefined : 3000,
            timerProgressBar: !esError
        });
        cajaFlash.innerHTML = '';
    }

    /* =======================================================
       2. CALCULADORA EN VIVO DE MÁRGENES Y COSTOS
       ======================================================= */
    const config = window.bonanzaConfig || { tasaIva: 0, aplicaIva: false, margenMinimo: 10 };
    const tasaIva = Number(config.tasaIva);
    const aplicaIva = Boolean(config.aplicaIva);
    const margenMinimo = Number(config.margenMinimo);

    const costo = document.getElementById('precio_compra');
    const venta = document.getElementById('precio_venta');
    const resGan = document.getElementById('res-ganancia');
    const resMar = document.getElementById('res-margen');
    const resMarNota = document.getElementById('res-margen-nota');
    const resIva = document.getElementById('res-iva');
    const alerta = document.getElementById('alerta-perdida');
    const chk = document.getElementById('confirmar_perdida');

    // Función segura para parsear números locales (soporta comas y puntos)
    function num(campo) {
        if (!campo || !campo.value) return 0;
        // Reemplaza comas por puntos y elimina espacios
        const valStr = campo.value.toString().trim().replace(',', '.');
        const n = parseFloat(valStr);
        return isNaN(n) ? 0 : n;
    }

    // Formateador de moneda local estilo Venezuela (1.234,56)
    function fmt(n) {
        return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function actualizar() {
        if (!costo || !venta) return;

        const c = num(costo);
        const v = num(venta);

        // Ganancia unitaria
        if (resGan) {
            resGan.textContent = v > 0 ? '$' + fmt(v - c) : '—';
        }

        // Margen sobre la venta: ((v - c) / v) * 100
        let margen = 0;
        if (v > 0 && c > 0) {
            margen = ((v - c) / v) * 100;
            if (resMar) resMar.textContent = fmt(margen) + ' %';
            
            // Advertencia visual si el margen es menor al mínimo configurado
            if (resMarNota) {
                if (margen < margenMinimo) {
                    resMarNota.textContent = `(Margen bajo < ${margenMinimo}%)`;
                } else {
                    resMarNota.textContent = '';
                }
            }
        } else {
            if (resMar) resMar.textContent = '—';
            if (resMarNota) resMarNota.textContent = '';
        }

        // Precio referencial con IVA
        if (aplicaIva && resIva) {
            resIva.textContent = v > 0 ? '$' + fmt(v * (1 + (tasaIva / 100))) : '—';
        }

        // Validación de pérdida operativa
        const perdida = v > 0 && c > 0 && v < c;
        if (alerta) {
            if (perdida) {
                alerta.classList.remove('is-hidden');
            } else {
                alerta.classList.add('is-hidden');
                if (chk) chk.checked = false;
            }
        }
    }

    if (costo) costo.addEventListener('input', actualizar);
    if (venta) venta.addEventListener('input', actualizar);

    /* =======================================================
       3. BOTONES DE AYUDA RÁPIDA Y CÁLCULO POR PORCENTAJE
       ======================================================= */
    const btnCosto = document.getElementById('btn-usar-costo');
    if (btnCosto) {
        btnCosto.addEventListener('click', function () {
            if (costo) {
                const valorCrudo = btnCosto.getAttribute('data-costo').replace(',', '.');
                costo.value = parseFloat(valorCrudo).toFixed(2);
                actualizar();
            }
        });
    }

    const btnCalcular = document.getElementById('btn-calcular');
    if (btnCalcular) {
        btnCalcular.addEventListener('click', function () {
            const porcInput = document.getElementById('porc-ganancia');
            
            // Si el input está vacío, toma el placeholder por defecto (25)
            let porcValue = (porcInput && porcInput.value.trim() !== '') ? porcInput.value : (porcInput ? porcInput.placeholder : '25');
            const porc = parseFloat(porcValue.toString().replace(',', '.'));
            
            const c = num(costo);
            
            if (isNaN(porc) || porc < 0 || c <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos incompletos',
                    text: 'Asegúrate de que el costo sea mayor a $0,00 e ingresa un porcentaje de ganancia válido.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            
            if (venta) {
                // Cálculo del precio de venta sumando el porcentaje de ganancia al costo
                const nuevoPrecioVenta = c * (1 + (porc / 100));
                
                // Asignación estricta con punto decimal (requerido por inputs numéricos de HTML5)
                venta.value = nuevoPrecioVenta.toFixed(2);
                
                // Disparar evento para actualizar dinámicamente los KPIs visuales
                const event = new Event('input', { bubbles: true });
                venta.dispatchEvent(event);
            }
        });
    }

    /* =======================================================
       4. PREVENIR DOBLE ENVÍO
       ======================================================= */
    const formPrecio = document.getElementById('form-precio');
    if (formPrecio) {
        formPrecio.addEventListener('submit', function () {
            const boton = this.querySelector('button[type="submit"]');
            if (boton) { 
                boton.classList.add('is-loading');
                boton.disabled = true; 
            }
        });
    }

    // Inicializar cálculos al cargar la página por primera vez
    actualizar();
});