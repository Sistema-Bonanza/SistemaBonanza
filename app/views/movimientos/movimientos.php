<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Operaciones · Bonanza</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    
    <!-- Ruta absoluta a tu CSS centralizado -->
     <link rel="stylesheet" href="assets/css/sidebar.css" />
</head>

    <body>

            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content" style="width: 100%; flex-grow: 1; max-width: 100%; overflow-x: hidden; padding: 30px;">
                
                <!-- CABECERA -->
                <div class="level mb-5">
                    <div class="level-left">
                        <div>
                            <h1 class="title is-4 mb-1">Módulo de Movimientos</h1>
                            <p class="has-text-grey mt-1">Auditoría general de ingresos y salidas del almacén.</p>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="buttons">
                            <a href="index.php?controller=movimiento&action=crearcompra" class="button is-link has-text-weight-bold">
                                <span class="icon is-small"><i class="fas fa-truck-ramp-box"></i></span>
                                <span>Registrar Compra</span>
                            </a>
                            <a href="#" class="button is-success has-text-weight-bold">
                                <span class="icon is-small"><i class="fas fa-cart-shopping"></i></span>
                                <span>Registrar Venta</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- TARJETAS DE INDICADORES (KPIs) -->
                <div class="columns is-multiline mb-5">
                    <div class="column is-4">
                        <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #3e8ed0; height: 100%;">
                            <div>
                                <p class="heading has-text-grey has-text-weight-bold mb-1">Entradas (Mes)</p>
                                <p class="title is-3 mb-0">1,250 <span class="is-size-6 has-text-grey has-text-weight-normal">unds.</span></p>
                            </div>
                            <div class="has-background-info-light has-text-info is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-4">
                        <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #48c774; height: 100%;">
                            <div>
                                <p class="heading has-text-grey has-text-weight-bold mb-1">Salidas (Mes)</p>
                                <p class="title is-3 mb-0">840 <span class="is-size-6 has-text-grey has-text-weight-normal">unds.</span></p>
                            </div>
                            <div class="has-background-success-light has-text-success is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="box is-flex is-align-items-center is-justify-content-space-between" style="border-left: 4px solid #f14668; height: 100%;">
                            <div>
                                <p class="heading has-text-grey has-text-weight-bold mb-1">Stock Crítico</p>
                                <p class="title is-3 has-text-danger mb-0">8 <span class="is-size-6 has-text-danger-light has-text-weight-normal">SKUs</span></p>
                                <p class="is-size-7 has-text-danger mt-1"><i class="fas fa-triangle-exclamation"></i> Requieren reabastecimiento</p>
                            </div>
                            <div class="has-background-danger-light has-text-danger is-flex is-align-items-center is-justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                                <i class="fas fa-battery-quarter"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA DE BÚSQUEDA Y FILTROS -->
                <div class="box mb-5">
                    <div class="columns is-vcentered mb-0">
                        <div class="column is-7 pb-0 pt-0">
                            <div class="control has-icons-left">
                                <input class="input" placeholder="Buscar N° de Documento o Usuario..." type="text">
                                <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="column is-5 pb-0 pt-0">
                            <div class="control has-icons-left is-expanded">
                                <div class="select is-fullwidth">
                                    <select>
                                        <option selected>Rango: Últimos 30 días</option>
                                        <option>Hoy</option>
                                        <option>Esta Semana</option>
                                    </select>
                                </div>
                                <span class="icon is-small is-left has-text-grey-light"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA PRINCIPAL -->
                <div class="box p-0" style="overflow: hidden;">
                    <div class="tabs is-boxed mb-0 pt-3 px-4 has-background-white-ter">
                        <ul>
                            <li class="is-active"><a><span class="icon is-small"><i class="fas fa-list"></i></span><span>Todas</span></a></li>
                            <li><a class="has-text-info"><span class="icon is-small"><i class="fas fa-arrow-down"></i></span><span>Entradas</span></a></li>
                            <li><a class="has-text-success"><span class="icon is-small"><i class="fas fa-arrow-up"></i></span><span>Salidas</span></a></li>
                        </ul>
                    </div>

                    <div class="level px-4 py-3 mb-0" style="border-bottom: 1px solid #ededed;">
                        <div class="level-left">
                            <h2 class="has-text-weight-bold is-size-5">Resumen de Operaciones</h2>
                        </div>
                        <div class="level-right">
                            <button class="button is-small is-light">
                                <span class="icon is-small has-text-danger"><i class="fas fa-file-pdf"></i></span>
                                <span>Exportar PDF</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-container mb-0">
                        <table class="table is-fullwidth is-hoverable is-vcentered mb-0">
                            <thead class="has-background-white-ter">
                                <tr>
                                    <th>Fecha / Hora</th>
                                    <th>Operación</th>
                                    <th>Documento / Ref.</th>
                                    <th>Entidad (Prov. / Cliente)</th>
                                    <th class="has-text-centered">Monto Total</th>
                                    <th class="has-text-right">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ciclo Foreach que recorre las compras -->
                                <?php if(!empty($compras)): ?>
                                    <?php foreach ($compras as $compra): ?>
                                    <tr>
                                        <!-- Columna 1: Fecha y Hora -->
                                         <td class="has-text-weight-bold">
                                            <?php 
                                                //FORMATEAMOS LA FECHA DE REGISTRO (EJ: 08 OCT 2026)
                                                $fecha = new DateTime($compra['fecha_registro']);
                                               echo $fecha->format('d M Y');
                                            ?>
                                            <br> 

                                            <!--FORMATO DE HORA EN GRIS Y PEQUEÑO (EJ: 11:50 AM)-->
                                            <span class= "has-text-grey is-size-7 has-text-weight-normal">
                                                <?php  $fecha->format ('h:i A')?>
                                            </span>
                                         </td>


                                        <!--COLUMNA 2: OPERACION -->
                                        <td>
                                            <span class= tag is-info is-light has-text-weight-bold>COMPRA</span>
                                        </td> 

                                        <!--COLUMNA 3 DOCUMENTO / REF -->
                                            <td class="has-text-weight-bold">
                                                <a href=#>#FACTURA-<?= esc($compra['numero_factura']) ?></a>
                                            </td>


                                        <!--COLUMNA 4: ENTIDAD (PROVEEDOR / CLIENTE) -->
                                        <td class="has-text-weight-bold">
                                            <?= esc($compra['nombre_proveedor']) ?>
                                            <br>
                                            <!--RIF EN GRIS Y PEQUEÑO-->
                                            <span class="has-text-grey is-size-7 has-text-weight-normal">
                                                <?= esc($compra['rif_proveedor'] ?? 'SIN RIF') ?>
                                            </span>
                                        </td>


                                        <!--COLUMNA 5: MONTO-->
                                        <td class="has-text-weight-bold is-size">
                                            $<?= number_format($compra['total'], 2, ',', '.') ?>
                                        </td>

                                        <!--COLUMNA 6 DETALLES (BOTON DE VER FACTURA-->
                                        <td> 
                                            <!-- Aquí luego pondremos el enlace para ver el detalle de la factura -->
                                            <a href="<?= urlAccion('movimiento', 'verDetalleCompra') ?>&id=<?= $compra['id_compra'] ?>" class="button is-small is-light">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="has-text-centered py-5 has-text-grey">
                                            No hay operaciones registradas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </body>
</html>