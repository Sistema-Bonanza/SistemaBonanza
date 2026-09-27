<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
    <div class="logo">✨ <span>Bonanza</span></div>
    <nav>
        <!-- Rutas apuntando a tu index.php -->
        <a href="index.php?controller=sistema&action=dashboard"><i>📊</i> Dashboard</a>
        <a href="index.php?controller=producto&action=tablaProductos"><i>🛒</i> Productos</a>
        <a href="#"><i>📦</i> Movimientos</a>
        <a href="index.php?controller=categoria&action=formcategoria"><i>🏷️</i> Categorías</a>
        <a href="#"><i>📋</i> Reportes</a>
        <a href="index.php?controller=cliente&action=tablaClientes"><i>👥</i> Clientes</a>
        <a href="index.php?controller=proveedor&action=tablaProveedores"><i>👥</i> Proveedores</a>
        <a href="index.php?controller=usuario&action=formusuario"><i></i> Usuarios</a>
        <a href="#"> <i></i>Control de Precios</a>
        <a href="#"><i>⚙️</i> Configuración</a>
    </nav>
    <div class="sidebar-footer">
        <p><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?> </p>
        
</nav>
        <small>Sistema de Inventario</small>

      <!-- Botón de Cerrar Sesión -->
    <a href="index.php?controller=sistema&action=logout" style="display: block; margin-top: 12px; color: #ef4444; font-weight: bold;">
        <i></i>Salir del sistema
    </a>
    </a>
    </div>
</aside>