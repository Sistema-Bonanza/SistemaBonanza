
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard · POS & Inventario</title>
    <!-- 1. Cargar Bulma CSS PRIMERO (Usa el CDN si no lo tienes local) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <!-- CSS externo -->
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/dashboard.css" /> 
    

    </head>
    <body>

        <!-- ========== AQUÍ CONECTAMOS EL SIDEBAR (LAYOUT) ========== -->
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

            <!-- CONTENEDOR PRINCIPAL DEL DASHBOARD -->
        <div class="is-flex-grow-1 is-flex is-flex-direction-column bg-dot-grid" style="height: 100vh; overflow: hidden;">
            
            <!-- TOPBAR / CABECERA -->
            <header class="has-background-white p-4" style="border-bottom: 1px solid #e2e8f0;">
                <div class="is-flex is-align-items-center is-justify-content-space-between">
                    
                    <!-- Título y Estado -->
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <div>
                            <h2 class="title is-5 mb-0 has-text-weight-bold">Panel de Control General</h2>
                            <p class="is-size-7 has-text-grey mt-1">Planta Central • Almacén General de Dotaciones</p>
                        </div>
                        <span class="has-text-grey-light">|</span>
                        <span class="tag is-success is-light is-rounded has-text-weight-bold">
                            <span style="width:8px; height:8px; background:#48c774; border-radius:50%; display:inline-block; margin-right:6px; animation: pulse 2s infinite;"></span>
                            Despacho Operativo
                        </span>
                    </div>

                    <!-- Buscador Central -->
                    <div style="width: 400px;">
                        <div class="field mb-0">
                            <div class="control has-icons-left has-icons-right">
                                <input class="input is-small is-rounded has-background-white-ter" type="text" placeholder="Buscar por código SKU, producto o proveedor...">
                                <span class="icon is-small is-left has-text-grey"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                                <span class="icon is-small is-right"><kbd class="has-background-white border has-text-grey px-1 rounded text-xxs">Ctrl+K</kbd></span>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones y Fecha -->
                    <div class="is-flex is-align-items-center" style="gap: 1.5rem;">
                        <div class="has-text-right is-hidden-touch">
                            <p class="is-size-7 has-text-weight-bold mb-0">Miércoles, 24 Octubre</p>
                            <p class="text-xxs has-text-grey">10:42:15 AM</p>
                        </div>
                        <button class="button is-white is-small" style="position:relative;">
                            <span class="icon has-text-grey"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg></span>
                            <span style="position:absolute; top:2px; right:2px; width:8px; height:8px; background:#f14668; border-radius:50%; border: 2px solid white;"></span>
                        </button>
                        <button class="button is-link is-small has-text-weight-bold is-rounded">
                            <span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"></path></svg></span>
                            <span>Nueva Entrada</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- ÁREA DE SCROLL (CONTENIDO) -->
            <main class="p-5 custom-scrollbar" style="overflow-y: auto; flex: 1;">
                
                <!-- SECCIÓN 1: TARJETAS KPI -->
                <div class="columns is-multiline mb-4">
                    <!-- KPI 1 -->
                    <div class="column is-3-desktop is-6-tablet">
                        <div class="box dashboard-box p-4">
                            <div class="is-flex is-justify-content-space-between is-align-items-center">
                                <span class="text-xxs has-text-weight-bold has-text-grey is-uppercase">Productos en Total</span>
                                <span class="icon has-text-link has-background-info-light rounded p-4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></span>
                            </div>
                            <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                                <span class="title is-3 mb-0">124</span>
                                <span class="tag is-success is-light is-small has-text-weight-bold">+12%</span>
                            </div>
                            <p class="is-size-7 has-text-grey mt-1">Registrados en catálogo</p>
                        </div>
                    </div>
                    <!-- KPI 2 -->
                    <div class="column is-3-desktop is-6-tablet">
                        <div class="box dashboard-box p-4">
                            <div class="is-flex is-justify-content-space-between is-align-items-center">
                                <span class="text-xxs has-text-weight-bold has-text-grey is-uppercase">Productos Disponibles</span>
                                <span class="icon has-text-success has-background-success-light rounded p-4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                            </div>
                            <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                                <span class="title is-3 mb-0">98</span>
                                <span class="tag is-light is-small has-text-weight-bold">79% aptos</span>
                            </div>
                            <p class="is-size-7 has-text-grey mt-1">Listos para salida inmediata</p>
                        </div>
                    </div>
                    <!-- KPI 3 -->
                    <div class="column is-3-desktop is-6-tablet">
                        <div class="box dashboard-box p-4">
                            <div class="is-flex is-justify-content-space-between is-align-items-center">
                                <span class="text-xxs has-text-weight-bold has-text-grey is-uppercase">Stock Total</span>
                                <span class="icon has-text-info has-background-info-light rounded p-4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg></span>
                            </div>
                            <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                                <span class="title is-3 mb-0">1,450</span>
                                <span class="is-size-7 has-text-weight-semibold has-text-grey">unidades</span>
                            </div>
                            <p class="is-size-7 has-text-grey mt-1">Valor aprox: $42,850.00</p>
                        </div>
                    </div>
                    <!-- KPI 4 -->
                    <div class="column is-3-desktop is-6-tablet">
                        <div class="box dashboard-box p-4">
                            <div class="is-flex is-justify-content-space-between is-align-items-center">
                                <span class="text-xxs has-text-weight-bold has-text-grey is-uppercase">Proveedores</span>
                                <span class="icon has-text-warning-dark has-background-warning-light rounded p-4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></span>
                            </div>
                            <div class="is-flex is-align-items-baseline mt-3" style="gap: 0.5rem;">
                                <span class="title is-3 mb-0">18</span>
                                <span class="tag is-success is-light is-small has-text-weight-bold">100% activos</span>
                            </div>
                            <p class="is-size-7 has-text-grey mt-1">Industriales certificados</p>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: CATÁLOGO Y CARRITO -->
                <div class="columns mb-4">
                    
                    <!-- Izquierda: Catálogo -->
                    <div class="column is-8">
                        <div class="box dashboard-box p-5 h-100">
                            <!-- Cabecera Catálogo -->
                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-4" style="border-bottom: 1px solid #f5f5f5; padding-bottom: 1rem;">
                                <div>
                                    <h3 class="title is-6 mb-1">Catálogo Rápido de Productos</h3>
                                    <p class="is-size-7 has-text-grey">Haz clic en <strong class="has-text-link">+</strong> para agregar ítems a la orden</p>
                                </div>
                                <div class="tags mb-0">
                                    <span class="tag is-link is-rounded has-text-weight-bold">Todos</span>
                                    <span class="tag is-light is-rounded">Seguridad</span>
                                    <span class="tag is-light is-rounded">Calzado</span>
                                    <span class="tag is-light is-rounded">Textil</span>
                                </div>
                            </div>

                            <!-- Grilla de Productos -->
                            <div class="columns is-multiline">
                                <!-- Producto 1 -->
                                <div class="column is-6">
                                    <div class="box dashboard-box p-3 has-background-white-bis is-shadowless" style="border: 1px solid #e2e8f0;">
                                        <div class="is-flex is-justify-content-space-between mb-2">
                                            <span class="tag is-info is-light text-xxs has-text-weight-bold">SKU-BON-0101</span>
                                            <span class="tag is-success is-light text-xxs has-text-weight-bold is-rounded">Stock: 48 unids</span>
                                        </div>
                                        <p class="is-size-7 has-text-weight-bold mb-1">Casco Dieléctrico Tipo I Clase E</p>
                                        <p class="text-xxs has-text-grey mb-3">Talla: <strong>Ajustable</strong> • Prov: <strong>Segurind</strong></p>
                                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end pt-2" style="border-top: 1px solid #edf2f7;">
                                            <div>
                                                <span class="text-xxs has-text-grey is-uppercase has-text-weight-bold">Precio Unit.</span>
                                                <p class="is-size-5 has-text-weight-bold mb-0">$18.50</p>
                                            </div>
                                            <button class="button is-link is-small has-text-weight-bold">+</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Producto 2 -->
                                <div class="column is-6">
                                    <div class="box dashboard-box p-3 has-background-white-bis is-shadowless" style="border: 1px solid #e2e8f0;">
                                        <div class="is-flex is-justify-content-space-between mb-2">
                                            <span class="tag is-info is-light text-xxs has-text-weight-bold">SKU-BON-0340</span>
                                            <span class="tag is-success is-light text-xxs has-text-weight-bold is-rounded">Stock: 65 piezas</span>
                                        </div>
                                        <p class="is-size-7 has-text-weight-bold mb-1">Camisa Drill Reforzada Manga Larga</p>
                                        <p class="text-xxs has-text-grey mb-3">Talla: <strong>L (40-42)</strong> • Prov: <strong>Textil Ind.</strong></p>
                                        <div class="is-flex is-justify-content-space-between is-align-items-flex-end pt-2" style="border-top: 1px solid #edf2f7;">
                                            <div>
                                                <span class="text-xxs has-text-grey is-uppercase has-text-weight-bold">Precio Unit.</span>
                                                <p class="is-size-5 has-text-weight-bold mb-0">$22.00</p>
                                            </div>
                                            <button class="button is-link is-small has-text-weight-bold">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Derecha: Carrito / POS -->
                    <div class="column is-4">
                        <div class="box dashboard-box p-5 h-100 is-flex is-flex-direction-column">
                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-4" style="border-bottom: 1px solid #f5f5f5; padding-bottom: 1rem;">
                                <div class="is-flex is-align-items-center" style="gap: 0.5rem;">
                                    <span class="icon has-text-link has-background-info-light rounded p-3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></span>
                                    <div>
                                        <h3 class="title is-6 mb-0">Carrito Despacho</h3>
                                        <p class="text-xxs has-text-grey font-mono">ORD-2023-089</p>
                                    </div>
                                </div>
                                <span class="tag is-info is-light is-rounded has-text-weight-bold text-xxs">2 Ítems</span>
                            </div>

                            <!-- Lista de Ítems (Scrollable) -->
                            <div class="custom-scrollbar mb-4" style="max-height: 200px; overflow-y: auto; flex-grow: 1;">
                                <!-- Item 1 -->
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
                                    <div style="flex: 1; min-width: 0;">
                                        <p class="is-size-7 has-text-weight-bold is-truncated">Casco Dieléctrico</p>
                                        <p class="text-xxs has-text-grey">$18.50 c/u</p>
                                    </div>
                                    <div class="is-flex is-align-items-center has-background-white-bis rounded border mx-2">
                                        <button class="button is-small is-ghost has-text-grey px-2">-</button>
                                        <span class="is-size-7 has-text-weight-bold px-2">2</span>
                                        <button class="button is-small is-ghost has-text-grey px-2">+</button>
                                    </div>
                                    <div class="has-text-right">
                                        <p class="is-size-7 has-text-weight-bold">$37.00</p>
                                        <a href="#" class="has-text-danger text-xxs">Quitar</a>
                                    </div>
                                </div>
                                <!-- Item 2 -->
                                <div class="is-flex is-justify-content-space-between is-align-items-center">
                                    <div style="flex: 1; min-width: 0;">
                                        <p class="is-size-7 has-text-weight-bold is-truncated">Camisa Drill (L)</p>
                                        <p class="text-xxs has-text-grey">$22.00 c/u</p>
                                    </div>
                                    <div class="is-flex is-align-items-center has-background-white-bis rounded border mx-2">
                                        <button class="button is-small is-ghost has-text-grey px-2">-</button>
                                        <span class="is-size-7 has-text-weight-bold px-2">1</span>
                                        <button class="button is-small is-ghost has-text-grey px-2">+</button>
                                    </div>
                                    <div class="has-text-right">
                                        <p class="is-size-7 has-text-weight-bold">$22.00</p>
                                        <a href="#" class="has-text-danger text-xxs">Quitar</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Totales y Botones -->
                            <div class="pt-3" style="border-top: 1px solid #edf2f7;">
                                <div class="is-flex is-justify-content-space-between mb-1">
                                    <span class="is-size-7 has-text-grey">Subtotal</span>
                                    <span class="is-size-7 has-text-weight-bold">$59.00</span>
                                </div>
                                <div class="is-flex is-justify-content-space-between mb-3">
                                    <span class="is-size-7 has-text-grey">IVA (16%)</span>
                                    <span class="is-size-7 has-text-weight-bold">$9.44</span>
                                </div>
                                <div class="is-flex is-justify-content-space-between is-align-items-baseline mb-4 pt-2" style="border-top: 2px solid #e2e8f0;">
                                    <span class="has-text-weight-bold">Total</span>
                                    <span class="title is-4 has-text-link mb-0">$68.44</span>
                                </div>
                                <div class="columns is-mobile is-gapless mb-0">
                                    <div class="column pr-1">
                                        <button class="button is-danger is-light is-fullwidth is-small has-text-weight-bold">Reset</button>
                                    </div>
                                    <div class="column pl-1">
                                        <button class="button is-link is-fullwidth is-small has-text-weight-bold">Cobrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: TABLA DE INVENTARIO COMPLETO -->
                <div class="box dashboard-box p-0 overflow-hidden">
                    <div class="p-4 is-flex is-justify-content-space-between is-align-items-center has-background-white">
                        <div>
                            <h3 class="title is-6 mb-1">Inventario Completo <span class="tag is-light ml-2 text-xxs">5 ítems visualizados</span></h3>
                            <p class="is-size-7 has-text-grey">Listado general de existencias registradas</p>
                        </div>
                        <button class="button is-link is-small has-text-weight-bold">Añadir Producto</button>
                    </div>
                    
                    <div class="table-container mb-0">
                        <table class="table is-fullwidth is-hoverable is-striped mb-0 text-xxs">
                            <thead class="has-background-light">
                                <tr>
                                    <th class="has-text-grey is-uppercase">SKU</th>
                                    <th class="has-text-grey is-uppercase">Producto</th>
                                    <th class="has-text-grey is-uppercase text-center">Talla</th>
                                    <th class="has-text-grey is-uppercase has-text-right">Stock</th>
                                    <th class="has-text-grey is-uppercase has-text-right">Precio</th>
                                    <th class="has-text-grey is-uppercase text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-mono has-text-weight-bold">SKU-00101</td>
                                    <td class="has-text-weight-bold">Casco Dieléctrico Tipo I</td>
                                    <td class="text-center">Única</td>
                                    <td class="has-text-right has-text-weight-bold">48</td>
                                    <td class="has-text-right">$18.50</td>
                                    <td class="text-center"><span class="tag is-success is-light is-rounded text-xxs">En Stock</span></td>
                                </tr>
                                <tr>
                                    <td class="font-mono has-text-weight-bold">SKU-00488</td>
                                    <td class="has-text-weight-bold">Guantes de Nitrilo</td>
                                    <td class="text-center">M</td>
                                    <td class="has-text-right has-text-weight-bold has-text-warning-dark">6</td>
                                    <td class="has-text-right">$8.20</td>
                                    <td class="text-center"><span class="tag is-warning is-light is-rounded text-xxs">Bajo</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </main>
        </div>
    </body>
</html>