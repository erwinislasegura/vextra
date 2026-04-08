<?php
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = base_path_url();
if ($base !== '' && str_starts_with($rutaActual, $base . '/')) {
    $rutaActual = substr($rutaActual, strlen($base));
}

$coincideRuta = static function (string $rutaMenu, string $rutaActual): bool {
    return $rutaActual === $rutaMenu || str_starts_with($rutaActual, $rutaMenu . '/');
};

$tieneModulo = static fn(string $codigo): bool => plan_tiene_funcionalidad_empresa_actual($codigo);
$esProductos = $coincideRuta('/app/productos', $rutaActual)
    || $coincideRuta('/app/categorias', $rutaActual)
    || $coincideRuta('/app/listas-precios', $rutaActual);
$esPos = $coincideRuta('/app/punto-venta', $rutaActual);
?>
<aside class="sidebar sidebar-app p-3 border-end bg-white">
  <h6 class="sidebar-app__titulo text-uppercase mb-3">Mi Empresa</h6>
  <nav class="nav flex-column small gap-2">
    <a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/panel', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/panel')) ?>">
      <i class="bi bi-house-door mt-1"></i>
      <span>Inicio</span>
    </a>

    <?php if ($tieneModulo('modulo_inventario') || $tieneModulo('modulo_recepciones') || $tieneModulo('modulo_ajustes') || $tieneModulo('modulo_movimientos') || $tieneModulo('modulo_ordenes_compra') || $tieneModulo('modulo_productos') || $tieneModulo('modulo_categorias') || $tieneModulo('modulo_listas_precios')): ?>
      <div class="pt-2 border-top"><div class="text-uppercase text-muted fw-semibold px-2">Flujo de inventario</div></div>
    <?php endif; ?>

    <?php if ($tieneModulo('modulo_inventario')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/inventario/proveedores', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/inventario/proveedores')) ?>"><i class="bi bi-building-add mt-1"></i><span>Proveedores</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_ordenes_compra')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/inventario/ordenes-compra', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/inventario/ordenes-compra')) ?>"><i class="bi bi-file-earmark-text mt-1"></i><span>Órdenes de compra</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_recepciones')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/inventario/recepciones', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/inventario/recepciones')) ?>"><i class="bi bi-truck mt-1"></i><span>Recepciones inventario</span></a><?php endif; ?>

    <?php if ($tieneModulo('modulo_productos') || $tieneModulo('modulo_categorias') || $tieneModulo('modulo_listas_precios')): ?>
      <button class="nav-link btn text-start d-flex align-items-center gap-2 <?= $esProductos ? 'active' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#submenuProductos" aria-expanded="<?= $esProductos ? 'true' : 'false' ?>">
        <i class="bi bi-box-seam"></i><span>Servicios / Productos</span><i class="bi ms-auto <?= $esProductos ? 'bi-chevron-up' : 'bi-chevron-down' ?>"></i>
      </button>
      <div class="collapse <?= $esProductos ? 'show' : '' ?>" id="submenuProductos">
        <?php if ($tieneModulo('modulo_productos')): ?><a class="nav-link submenu <?= $coincideRuta('/app/productos', $rutaActual) && !$coincideRuta('/app/productos/carga-masiva', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/productos')) ?>">Listado productos</a><?php endif; ?>
        <?php if ($tieneModulo('modulo_productos')): ?><a class="nav-link submenu <?= $coincideRuta('/app/productos/carga-masiva', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/productos/carga-masiva')) ?>">Carga masiva</a><?php endif; ?>
        <?php if ($tieneModulo('modulo_categorias')): ?><a class="nav-link submenu <?= $coincideRuta('/app/categorias', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/categorias')) ?>">Categorías</a><?php endif; ?>
        <?php if ($tieneModulo('modulo_listas_precios')): ?><a class="nav-link submenu <?= $coincideRuta('/app/listas-precios', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/listas-precios')) ?>">Listas de precios</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($tieneModulo('modulo_ajustes')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/inventario/ajustes', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/inventario/ajustes')) ?>"><i class="bi bi-sliders mt-1"></i><span>Ajustes inventario</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_movimientos')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/inventario/movimientos', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/inventario/movimientos')) ?>"><i class="bi bi-arrow-left-right mt-1"></i><span>Movimientos inventario</span></a><?php endif; ?>

    <?php if ($tieneModulo('modulo_pos')): ?>
      <button class="nav-link btn text-start d-flex align-items-center gap-2 <?= $esPos ? 'active' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#submenuPos" aria-expanded="<?= $esPos ? 'true' : 'false' ?>">
        <i class="bi bi-cart-check"></i><span>Punto de venta</span><i class="bi ms-auto <?= $esPos ? 'bi-chevron-up' : 'bi-chevron-down' ?>"></i>
      </button>
      <div class="collapse <?= $esPos ? 'show' : '' ?>" id="submenuPos">
        <a class="nav-link submenu <?= $coincideRuta('/app/punto-venta', $rutaActual) && !$coincideRuta('/app/punto-venta/ventas', $rutaActual) && !$coincideRuta('/app/punto-venta/movimientos', $rutaActual) && !$coincideRuta('/app/punto-venta/cajas', $rutaActual) && !$coincideRuta('/app/punto-venta/configuracion', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/punto-venta')) ?>">Nueva venta</a>
        <a class="nav-link submenu <?= $coincideRuta('/app/punto-venta/ventas', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/punto-venta/ventas')) ?>">Historial POS</a>
        <a class="nav-link submenu <?= $coincideRuta('/app/punto-venta/movimientos', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/punto-venta/movimientos')) ?>">Movimientos de caja</a>
        <a class="nav-link submenu <?= $coincideRuta('/app/punto-venta/cajas', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/punto-venta/cajas')) ?>">Cajas / terminales</a>
        <a class="nav-link submenu <?= $coincideRuta('/app/punto-venta/configuracion', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/punto-venta/configuracion')) ?>">Configuración POS</a>
      </div>
    <?php endif; ?>

    <?php if ($tieneModulo('modulo_clientes') || $tieneModulo('modulo_contactos') || $tieneModulo('modulo_vendedores') || $tieneModulo('modulo_cotizaciones') || $tieneModulo('modulo_seguimiento') || $tieneModulo('modulo_aprobaciones')): ?>
      <div class="pt-2 border-top"><div class="text-uppercase text-muted fw-semibold px-2">Gestión comercial</div></div>
    <?php endif; ?>
    <?php if ($tieneModulo('modulo_clientes')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/clientes', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/clientes')) ?>"><i class="bi bi-building mt-1"></i><span>Clientes</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_contactos')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/contactos', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/contactos')) ?>"><i class="bi bi-person-lines-fill mt-1"></i><span>Contactos</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_vendedores')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/vendedores', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/vendedores')) ?>"><i class="bi bi-person-badge mt-1"></i><span>Vendedores</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_cotizaciones')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/cotizaciones', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/cotizaciones')) ?>"><i class="bi bi-file-earmark-text mt-1"></i><span>Cotizaciones</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_seguimiento')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/seguimiento', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/seguimiento')) ?>"><i class="bi bi-graph-up-arrow mt-1"></i><span>Seguimiento comercial</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_aprobaciones')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/aprobaciones', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/aprobaciones')) ?>"><i class="bi bi-check2-square mt-1"></i><span>Aprobaciones</span></a><?php endif; ?>

    <?php if ($tieneModulo('modulo_documentos') || $tieneModulo('modulo_correos_stock') || $tieneModulo('modulo_configuracion') || $tieneModulo('modulo_usuarios') || $tieneModulo('modulo_notificaciones') || $tieneModulo('modulo_historial') || $tieneModulo('modulo_reportes')): ?>
      <div class="pt-2 border-top"><div class="text-uppercase text-muted fw-semibold px-2">Configuración</div></div>
    <?php endif; ?>
    <?php if ($tieneModulo('modulo_documentos')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/documentos', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/documentos')) ?>"><i class="bi bi-code-square mt-1"></i><span>Plantilla correo cotización</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_documentos')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/configuracion/envio-oc-html', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/configuracion/envio-oc-html')) ?>"><i class="bi bi-envelope mt-1"></i><span>Envío OC HTML</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_configuracion')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/configuracion', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/configuracion')) ?>"><i class="bi bi-gear mt-1"></i><span>Configuración empresa</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_correos_stock')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/configuracion/correos-stock', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/configuracion/correos-stock')) ?>"><i class="bi bi-envelope-paper mt-1"></i><span>Correos de stock</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_usuarios')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/usuarios', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/usuarios')) ?>"><i class="bi bi-people mt-1"></i><span>Usuarios y permisos</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_notificaciones')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/notificaciones', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/notificaciones')) ?>"><i class="bi bi-bell mt-1"></i><span>Notificaciones</span></a><?php endif; ?>
    <?php if ($tieneModulo('modulo_historial')): ?><a class="nav-link d-flex gap-2 <?= $coincideRuta('/app/historial', $rutaActual) ? 'active' : '' ?>" href="<?= e(url('/app/historial')) ?>"><i class="bi bi-clock-history mt-1"></i><span>Historial / actividad</span></a><?php endif; ?>
  </nav>
</aside>
