<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$resolverImagenProducto = static function (?string $ruta): string {
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return url('/img/placeholder-producto.svg');
    }
    if (preg_match('/^https?:\/\//i', $ruta) === 1) {
        return $ruta;
    }

    $normalizada = str_replace('\\', '/', $ruta);
    $normalizada = preg_replace('#^https?://[^/]+#i', '', $normalizada) ?? $normalizada;
    $normalizada = preg_replace('#^/?public/#i', '/', $normalizada) ?? $normalizada;
    if (str_contains($normalizada, '/uploads/')) {
        $partes = explode('/uploads/', $normalizada, 2);
        $normalizada = '/uploads/' . ($partes[1] ?? '');
    } elseif (str_contains($normalizada, 'productos_catalogo/')) {
        $partes = explode('productos_catalogo/', $normalizada, 2);
        $normalizada = '/uploads/productos_catalogo/' . ($partes[1] ?? '');
    }
    $normalizada = '/' . ltrim($normalizada, '/');

    if (str_starts_with($normalizada, '/uploads/') || str_starts_with($normalizada, '/img/')) {
        return url('/media/archivo?ruta=' . rawurlencode($normalizada));
    }

    return url('/' . ltrim($normalizada, '/'));
};
?>
<style>
  :root{--primary:#0f172a;--primary-soft:#1e293b;--accent:#2563eb;--accent-hover:#1d4ed8;--danger:#dc2626;--bg:#eef2f7;--card:#ffffff;--text:#0f172a;--muted:#64748b;--border:#dbe3ee;--shadow:0 10px 25px rgba(15,23,42,.08);--radius:18px}
  .catalogo-page{background:var(--bg)}
  .catalogo-container{width:min(1280px,92%);margin:0 auto}
  .catalogo-topbar{background:var(--primary);color:#fff;padding:10px 0;font-size:14px}
  .catalogo-topbar__content{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .catalogo-header{position:sticky;top:0;z-index:45;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
  .catalogo-navbar{display:grid;grid-template-columns:220px 1fr auto auto;gap:16px;align-items:center;padding:16px 0}
  .catalogo-logo{display:flex;align-items:center;gap:.6rem;color:var(--text);font-size:40px;font-weight:800;text-decoration:none;line-height:1}
  .catalogo-logo img{width:52px;height:52px;object-fit:contain;border-radius:12px;border:1px solid var(--border);background:#fff}
  .catalogo-logo small{display:block;font-size:13px;font-weight:600;color:var(--muted)}
  .catalogo-logo span{color:var(--accent)}
  .search-box{display:flex;align-items:center;background:#fff;border:1px solid var(--border);border-radius:999px;overflow:hidden;box-shadow:var(--shadow)}
  .search-box input{width:100%;padding:14px 18px;border:none;outline:none;background:transparent;font-size:15px}
  .search-box button{background:var(--accent);color:#fff;padding:14px 20px;font-weight:700;border:none}
  .nav-actions{display:flex;gap:10px;align-items:center}
  .btn-outline,.btn-primary-custom,.btn-soft,.btn-danger-soft{padding:12px 16px;border-radius:12px;font-weight:700;border:1px solid var(--border);background:#fff;color:var(--text)}
  .btn-primary-custom{background:var(--accent);border-color:var(--accent);color:#fff}
  .btn-soft{background:#eff6ff;color:var(--accent)}
  .btn-danger-soft{background:#fff1f2;color:var(--danger);border-color:#fde2e2}
  .hero{padding:26px 0 22px}
  .hero-grid{display:grid;grid-template-columns:1.4fr .8fr;gap:22px}
  .slider{position:relative;min-height:420px;border-radius:26px;overflow:hidden;box-shadow:var(--shadow);background:linear-gradient(135deg,#0f172a,#1d4ed8)}
  .slide{position:absolute;inset:0;opacity:0;visibility:hidden;transition:opacity .45s ease;display:grid;grid-template-columns:1.1fr .9fr;align-items:center;padding:40px;color:#fff}
  .slide.active{opacity:1;visibility:visible}
  .slide h2{font-size:52px;line-height:1.1;margin-bottom:14px;font-weight:800}
  .slide p{color:rgba(255,255,255,.92);margin-bottom:20px;max-width:520px}
  .slide img{width:100%;height:250px;object-fit:cover;border-radius:18px}
  .slide-actions{display:flex;gap:12px;flex-wrap:wrap}
  .hero-card{background:#fff;border-radius:26px;padding:24px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:16px;border:1px solid var(--border)}
  .hero-card h3,.section-head h2,.sidebar h3{font-size:24px;color:var(--primary);font-weight:800}
  .promo-box{padding:16px;border-radius:18px;background:#f8fafc;border:1px solid var(--border)}
  .promo-box p{color:var(--muted);margin:6px 0 0}
  .filters-section{padding:8px 0 24px}
  .filters-wrap{background:#fff;border:1px solid var(--border);border-radius:22px;padding:16px;box-shadow:var(--shadow);display:grid;grid-template-columns:1.2fr repeat(3,1fr) auto;gap:12px;align-items:end}
  .field{display:flex;flex-direction:column;gap:8px}.field label{font-size:13px;font-weight:700;color:var(--muted)}
  .field input,.field select{border:1px solid var(--border);background:#fff;padding:13px 14px;border-radius:14px;outline:none;font-size:14px}
  .main-content{padding:0 0 46px}
  .content-grid{display:grid;grid-template-columns:280px 1fr;gap:22px}
  .sidebar{background:#fff;border-radius:22px;box-shadow:var(--shadow);padding:20px;border:1px solid var(--border);height:fit-content;position:sticky;top:106px}
  .category-list,.feature-list{list-style:none;display:grid;gap:10px;margin-top:14px}
  .category-list button,.feature-list button{width:100%;text-align:left;background:#f8fafc;border:1px solid var(--border);border-radius:14px;padding:12px 14px;font-weight:700;color:var(--primary-soft)}
  .category-list button.active{background:#eff6ff;color:var(--accent);border-color:#bfdbfe}
  .section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap}
  .section-head p{color:var(--muted)}
  .products-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
  .product-card{background:#fff;border:1px solid var(--border);border-radius:22px;overflow:hidden;box-shadow:var(--shadow);display:flex;flex-direction:column;transition:transform .2s ease,box-shadow .2s ease}
  .product-card:hover{transform:translateY(-4px);box-shadow:0 16px 30px rgba(15,23,42,.12)}
  .product-image{position:relative;height:220px;background:#dce3ee;overflow:hidden}.product-image img{width:100%;height:100%;object-fit:cover}
  .badge-mini{position:absolute;top:14px;left:14px;background:#fff;color:var(--primary);font-size:12px;font-weight:700;padding:7px 10px;border-radius:999px;box-shadow:var(--shadow)}
  .badge-mini.sale{background:#fff7ed;color:#ea580c}
  .product-body{padding:16px;display:flex;flex-direction:column;gap:10px;flex:1}
  .category-tag{font-size:12px;font-weight:800;color:var(--accent);letter-spacing:.3px;text-transform:uppercase}
  .product-title{font-size:34px;font-weight:800;color:var(--primary);line-height:1.15;margin:0}
  .product-desc{color:var(--muted);font-size:14px;min-height:42px}
  .rating-stock{display:flex;justify-content:space-between;font-size:13px;color:var(--muted)}
  .price-wrap{display:flex;align-items:baseline;gap:8px}.price{font-size:42px;font-weight:800;color:var(--primary)}.old-price{color:#94a3b8;text-decoration:line-through;font-weight:700}
  .card-actions{display:grid;grid-template-columns:1fr auto;gap:10px;margin-top:auto}
  .icon-btn{width:48px;border-radius:14px;background:#f8fafc;border:1px solid var(--border);font-size:20px}
  .cart-toggle{position:fixed;right:20px;bottom:20px;z-index:70;background:var(--primary);color:#fff;border-radius:999px;padding:14px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:10px;font-weight:700;border:none}
  .cart-count{background:var(--accent);min-width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:13px}
  .cart-panel{position:fixed;top:0;right:-420px;width:400px;max-width:100%;height:100vh;background:#fff;border-left:1px solid var(--border);box-shadow:-10px 0 30px rgba(15,23,42,.12);z-index:90;transition:right .3s ease;display:flex;flex-direction:column}
  .cart-panel.open{right:0}.cart-header,.cart-footer{padding:20px}.cart-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)}
  .cart-items{flex:1;overflow:auto;padding:18px;display:grid;gap:12px}.cart-item{display:grid;grid-template-columns:74px 1fr auto;gap:10px;align-items:center;border:1px solid var(--border);border-radius:14px;padding:10px}
  .cart-item img{width:74px;height:74px;object-fit:cover;border-radius:10px}.qty-controls{display:flex;align-items:center;gap:8px;margin-top:8px}
  .qty-controls button{width:28px;height:28px;border-radius:8px;background:#f1f5f9;border:1px solid var(--border);font-weight:700}
  .cart-footer{border-top:1px solid var(--border);display:grid;gap:12px}.summary-row{display:flex;justify-content:space-between;font-weight:700}
  .empty-state{text-align:center;color:var(--muted);padding:40px 10px}.overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);opacity:0;visibility:hidden;transition:.25s;z-index:80}.overlay.show{opacity:1;visibility:visible}
  .footer{background:var(--primary);color:#fff;padding:28px 0;margin-top:20px}.footer-content{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap}
  .catalogo-checkout .form-control,.catalogo-checkout .form-select{border-radius:.65rem}
  .catalogo-checkout__block{border:1px solid #edf1f5;border-radius:.95rem;padding:1rem;background:#fff}
  .catalogo-checkout__title{font-weight:700;font-size:.95rem;margin-bottom:.75rem}
  @media (max-width:1100px){.catalogo-navbar,.hero-grid,.content-grid,.filters-wrap,.slide{grid-template-columns:1fr}.sidebar{position:static}.products-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media (max-width:720px){.products-grid{grid-template-columns:1fr}.catalogo-topbar__content,.section-head,.footer-content,.catalogo-navbar{display:flex;flex-direction:column;align-items:stretch}.slide{padding:24px}.slide h2{font-size:32px}.cart-panel{width:100%}}
</style>

<div class="catalogo-page">
  <div class="catalogo-topbar">
    <div class="catalogo-container catalogo-topbar__content">
      <div>Envíos a todo el país • Garantía en todos los productos</div>
      <div>Soporte comercial • Compras seguras • Atención premium</div>
    </div>
  </div>

  <header class="catalogo-header">
    <div class="catalogo-container catalogo-navbar">
      <a class="catalogo-logo" href="#catalogoProductos">
        <?php if (!empty($logoCatalogo)): ?>
          <img src="<?= e((string) $logoCatalogo) ?>" alt="Logo empresa">
        <?php endif; ?>
        <div>
          <?= e((string) ($empresa['nombre_comercial'] ?? 'Catálogo')) ?><span>Pro</span>
          <small>Catálogo profesional</small>
        </div>
      </a>
      <div class="search-box">
        <input type="text" id="globalSearch" placeholder="Buscar productos, categorías o marcas...">
        <button type="button" id="searchBtn">Buscar</button>
      </div>
      <div class="nav-actions">
        <button class="btn-outline" type="button">Favoritos</button>
        <button class="btn-outline" type="button">Mi cuenta</button>
      </div>
      <button class="btn-primary-custom" type="button" id="openCartHeader">Ver carrito</button>
    </div>
  </header>

  <section class="hero">
    <div class="catalogo-container hero-grid">
      <div class="slider" id="slider">
        <article class="slide active">
          <div>
            <h2><?= e((string) ($sliderCatalogo['titulo'] ?? 'Catálogo online moderno y profesional')) ?></h2>
            <p><?= e((string) ($sliderCatalogo['bajada'] ?? 'Presenta tus productos con una experiencia de compra elegante, rápida y totalmente ordenada para tus clientes.')) ?></p>
            <div class="slide-actions">
              <a href="#catalogoProductos" class="btn-primary-custom">Ver catálogo</a>
              <?php if (!empty($sliderCatalogo['boton_url']) && !empty($sliderCatalogo['boton_texto'])): ?>
                <a href="<?= e((string) $sliderCatalogo['boton_url']) ?>" class="btn-outline" target="_blank" rel="noopener noreferrer"><?= e((string) $sliderCatalogo['boton_texto']) ?></a>
              <?php else: ?>
                <button class="btn-soft" type="button" id="showOffersBtnTop">Nuevos ingresos</button>
              <?php endif; ?>
            </div>
          </div>
          <div><img src="<?= e((string) ($sliderCatalogo['imagen'] ?: url('/img/placeholder-producto.svg'))) ?>" alt="Promoción principal"></div>
        </article>
        <article class="slide">
          <div>
            <h2>Experiencia de compra fluida</h2>
            <p>Incluye tarjetas atractivas, filtros por categoría, búsqueda, ordenamiento y carrito lateral para cerrar ventas más rápido.</p>
            <div class="slide-actions"><a href="#catalogoProductos" class="btn-primary-custom">Comprar ahora</a><button class="btn-soft" type="button" id="showStockBtnTop">Solo stock</button></div>
          </div>
          <div><img src="<?= e((string) ($sliderCatalogo['imagen'] ?: url('/img/placeholder-producto.svg'))) ?>" alt="Colección destacada"></div>
        </article>
      </div>

      <aside class="hero-card">
        <h3>Beneficios del catálogo</h3>
        <div class="promo-box"><strong>Diseño premium</strong><p>Interfaz limpia, moderna y pensada para vender más.</p></div>
        <div class="promo-box"><strong>Carrito funcional</strong><p>Agrega productos, calcula subtotales y visualiza el total.</p></div>
        <div class="promo-box"><strong>Filtros inteligentes</strong><p>Busca por nombre, categoría, precio y stock disponible.</p></div>
        <a href="<?= e(url('/app/catalogo-en-linea')) ?>" class="btn-primary-custom text-center">Configurar contenido</a>
      </aside>
    </div>
  </section>

  <section class="filters-section" id="catalogoProductos">
    <div class="catalogo-container filters-wrap">
      <div class="field"><label for="searchInput">Buscar producto</label><input type="text" id="searchInput" placeholder="Ej: audífonos, reloj, mochila..."></div>
      <div class="field"><label for="categoryFilter">Categoría</label><select id="categoryFilter"><option value="all">Todas</option></select></div>
      <div class="field"><label for="priceFilter">Rango de precio</label><select id="priceFilter"><option value="all">Todos</option><option value="0-50000">Hasta $50.000</option><option value="50001-100000">$50.001 a $100.000</option><option value="100001-200000">$100.001 a $200.000</option><option value="200001-99999999">Más de $200.000</option></select></div>
      <div class="field"><label for="sortFilter">Ordenar por</label><select id="sortFilter"><option value="featured">Destacados</option><option value="price-asc">Precio: menor a mayor</option><option value="price-desc">Precio: mayor a menor</option><option value="name-asc">Nombre A-Z</option></select></div>
      <button class="btn-danger-soft" type="button" id="clearFilters">Limpiar</button>
    </div>
  </section>

  <main class="main-content">
    <div class="catalogo-container content-grid">
      <aside class="sidebar">
        <h3>Categorías</h3>
        <div class="category-list" id="categoryButtons"></div>
        <h3 style="margin-top:24px;">Acciones rápidas</h3>
        <div class="feature-list">
          <button type="button" id="showAllBtn">Ver todos los productos</button>
          <button type="button" id="showOffersBtn">Ver ofertas</button>
          <button type="button" id="showStockBtn">Solo con stock</button>
        </div>
      </aside>

      <section class="products-area">
        <div class="section-head">
          <div><h2>Nuestro catálogo</h2><p id="resultsInfo">Explora nuestros productos destacados.</p></div>
          <button class="btn-soft" type="button" id="scrollTopBtn">Ir arriba</button>
        </div>

        <div class="products-grid" id="productsGrid">
          <?php foreach ($productos as $producto): ?>
            <?php
              $imagenProducto = (string) ($producto['imagen_catalogo'] ?? $producto['imagen_catalogo_url'] ?? '');
              $precio = (float) ($producto['precio'] ?? 0);
              $categoria = (string) ($producto['categoria'] ?? 'Sin categoría');
              $nombreProducto = (string) ($producto['nombre'] ?? 'Producto');
              $descripcionProducto = (string) ($producto['descripcion'] ?? 'Sin descripción');
              $stock = (int) ($producto['stock_actual'] ?? rand(3, 40));
              $rating = number_format(4 + (($producto['id'] ?? 1) % 10) / 10, 1);
              $onSale = ((int) ($producto['id'] ?? 0) % 2) === 0;
              $oldPrice = $onSale ? ($precio * 1.18) : 0;
            ?>
            <article class="product-card" data-producto-card data-id="<?= (int) $producto['id'] ?>" data-name="<?= e($nombreProducto) ?>" data-price="<?= $precio ?>" data-category="<?= e($categoria) ?>" data-description="<?= e($descripcionProducto) ?>" data-image="<?= e($resolverImagenProducto($imagenProducto)) ?>" data-stock="<?= $stock ?>" data-rating="<?= e((string) $rating) ?>" data-onsale="<?= $onSale ? '1' : '0' ?>" data-oldprice="<?= $oldPrice ?>">
              <div class="product-image">
                <img src="<?= e($resolverImagenProducto($imagenProducto)) ?>" alt="<?= e($nombreProducto) ?>" loading="lazy">
                <span class="badge-mini <?= $onSale ? 'sale' : '' ?>"><?= $onSale ? 'Oferta' : 'Destacado' ?></span>
              </div>
              <div class="product-body">
                <span class="category-tag"><?= e($categoria) ?></span>
                <h3 class="product-title"><?= e($nombreProducto) ?></h3>
                <p class="product-desc"><?= e($descripcionProducto) ?></p>
                <div class="rating-stock"><span>⭐<?= e((string) $rating) ?></span><span>Stock: <?= $stock ?></span></div>
                <div class="price-wrap"><div class="price"><?= e($fmon($precio)) ?></div><?php if ($oldPrice > 0): ?><div class="old-price"><?= e($fmon($oldPrice)) ?></div><?php endif; ?></div>
                <div class="card-actions">
                  <button class="btn-primary-custom" type="button" data-add-cart data-id="<?= (int) $producto['id'] ?>" data-name="<?= e($nombreProducto) ?>" data-price="<?= $precio ?>">Agregar al carrito</button>
                  <button class="icon-btn" type="button" data-view-product aria-label="Ver producto">👁</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>

  <div class="overlay" id="overlay"></div>
  <button class="cart-toggle" id="cartToggle">🛒 Carrito <span class="cart-count" id="cartCount">0</span></button>

  <aside class="cart-panel" id="cartPanel">
    <div class="cart-header"><h3>Tu carrito</h3><button class="btn-outline" type="button" id="closeCart">Cerrar</button></div>
    <div class="cart-items" id="cartItems"><div class="empty-state">Aún no has agregado productos.</div></div>
    <div class="cart-footer">
      <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">$0</span></div>
      <div class="summary-row"><span>Total</span><span id="cartTotal">$0</span></div>
      <button class="btn-primary-custom" type="button" id="openCheckout">Finalizar compra</button>
      <button class="btn-outline" type="button" id="clearCart">Vaciar carrito</button>
    </div>
  </aside>

  <footer class="footer"><div class="catalogo-container footer-content"><div><strong><?= e((string) ($empresa['nombre_comercial'] ?? 'CatálogoPro')) ?></strong><p class="mb-0">Diseño profesional para mostrar y vender productos online.</p></div><div><p class="mb-0">© <?= date('Y') ?> • Todos los derechos reservados</p></div></div></footer>
</div>

<div class="modal fade" id="modalCheckout" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form method="POST" action="<?= e(url('/catalogo/' . (int) $empresa['id'] . '/checkout')) ?>" class="catalogo-checkout">
      <?= csrf_campo() ?>
      <div class="modal-header"><h5 class="modal-title">Checkout seguro</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body">
        <div class="catalogo-checkout__block mb-3 bg-light-subtle"><div class="catalogo-checkout__title">Resumen de compra</div><div id="carritoVistaCheckout" class="small text-muted">Tu carrito está vacío.</div></div>
        <input type="hidden" name="carrito_json" id="carrito_json" value="[]">
        <div class="catalogo-checkout__block mb-3"><div class="catalogo-checkout__title">Datos del comprador</div><div class="row g-2">
          <div class="col-md-6"><label class="form-label">Nombre y apellido *</label><input class="form-control" name="nombre" required></div>
          <div class="col-md-6"><label class="form-label">Correo electrónico *</label><input type="email" class="form-control" name="correo" required></div>
          <div class="col-md-6"><label class="form-label">Teléfono / WhatsApp *</label><input class="form-control" name="telefono" placeholder="+56912345678" required></div>
          <div class="col-md-6"><label class="form-label">RUT o documento</label><input class="form-control" name="documento" placeholder="12.345.678-9"></div>
          <div class="col-12"><label class="form-label">Empresa (opcional)</label><input class="form-control" name="empresa"></div>
        </div></div>
        <div class="catalogo-checkout__block"><div class="catalogo-checkout__title">Dirección de envío y facturación</div><div class="row g-2">
          <div class="col-12"><label class="form-label">Dirección *</label><input class="form-control" name="direccion" required></div>
          <div class="col-md-6"><label class="form-label">Comuna *</label><input class="form-control" name="comuna" required></div>
          <div class="col-md-6"><label class="form-label">Ciudad *</label><input class="form-control" name="ciudad" required></div>
          <div class="col-md-6"><label class="form-label">Región *</label><input class="form-control" name="region" required></div>
          <div class="col-md-6"><label class="form-label">Referencia de entrega</label><input class="form-control" name="referencia"></div>
        </div></div>
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="acepta_terminos" id="acepta_terminos" value="1" required><label class="form-check-label small" for="acepta_terminos">Confirmo que los datos ingresados son correctos y acepto continuar con el pago.</label></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir comprando</button><button class="btn btn-primary" type="submit">Pagar ahora</button></div>
    </form>
  </div></div>
</div>

<div class="modal fade" id="modalProductoDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 shadow">
    <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <div class="modal-body pt-1"><div class="row g-3 g-lg-4 align-items-start"><div class="col-lg-6"><img id="detalleProductoImagen" src="" alt="Producto" class="w-100 rounded-3" style="max-height:360px;object-fit:cover;background:#f8fafc"></div><div class="col-lg-6"><div class="small text-uppercase text-muted mb-2" id="detalleProductoCategoria"></div><h3 class="h4 mb-2" id="detalleProductoNombre"></h3><p class="text-muted mb-3" id="detalleProductoDescripcion"></p><div class="d-flex justify-content-between align-items-center border rounded-3 p-3 bg-light"><div><div class="small text-muted">Precio</div><div class="h4 mb-0" id="detalleProductoPrecio"></div></div><button type="button" class="btn btn-primary px-4" id="detalleAgregarCarrito">Comprar</button></div></div></div></div>
  </div></div>
</div>

<script>
(() => {
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const money = (v) => `$${Math.round(Number(v || 0)).toLocaleString('es-CL')}`;
  const storageKey = 'vextra_catalogo_carrito_<?= (int) $empresa['id'] ?>';

  const cards = $$('.product-card');
  const products = cards.map((card) => ({
    id: Number(card.dataset.id || 0),
    name: String(card.dataset.name || ''),
    price: Number(card.dataset.price || 0),
    category: String(card.dataset.category || 'Sin categoría'),
    description: String(card.dataset.description || ''),
    image: String(card.dataset.image || ''),
    stock: Number(card.dataset.stock || 0),
    rating: Number(card.dataset.rating || 0),
    onSale: String(card.dataset.onsale || '0') === '1',
    featured: String(card.dataset.onsale || '0') !== '1',
    oldPrice: Number(card.dataset.oldprice || 0),
    el: card,
  }));

  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { cart = []; }

  let selectedCategory = 'all';
  let onlyOffers = false;
  let onlyStock = false;

  const categoryFilter = $('#categoryFilter');
  const categoryButtons = $('#categoryButtons');
  const searchInput = $('#searchInput');
  const globalSearch = $('#globalSearch');
  const priceFilter = $('#priceFilter');
  const sortFilter = $('#sortFilter');
  const resultsInfo = $('#resultsInfo');
  const cartPanel = $('#cartPanel');
  const overlay = $('#overlay');
  const cartItems = $('#cartItems');
  const cartCount = $('#cartCount');
  const cartSubtotal = $('#cartSubtotal');
  const cartTotal = $('#cartTotal');
  const carritoVistaCheckout = $('#carritoVistaCheckout');
  const carritoJson = $('#carrito_json');

  const modalCheckout = window.bootstrap ? new bootstrap.Modal($('#modalCheckout')) : null;
  const modalProductoDetalle = window.bootstrap ? new bootstrap.Modal($('#modalProductoDetalle')) : null;
  const detalleNombre = $('#detalleProductoNombre');
  const detalleDescripcion = $('#detalleProductoDescripcion');
  const detalleCategoria = $('#detalleProductoCategoria');
  const detallePrecio = $('#detalleProductoPrecio');
  const detalleImagen = $('#detalleProductoImagen');
  let productoSeleccionado = null;

  const categories = ['all', ...new Set(products.map((p) => p.category))];
  const renderCategories = () => {
    categoryFilter.innerHTML = categories.map((c) => `<option value="${c}">${c === 'all' ? 'Todas' : c}</option>`).join('');
    categoryFilter.value = selectedCategory;
    categoryButtons.innerHTML = categories.map((c) => `<button type="button" class="${selectedCategory === c ? 'active' : ''}" data-category="${c}">${c === 'all' ? 'Todas las categorías' : c}</button>`).join('');
    $$('button[data-category]', categoryButtons).forEach((btn) => {
      btn.addEventListener('click', () => {
        selectedCategory = btn.dataset.category || 'all';
        renderCategories();
        applyFilters();
      });
    });
  };

  const applyFilters = () => {
    const term = (searchInput.value.trim() || globalSearch.value.trim()).toLowerCase();
    const priceRange = priceFilter.value;
    let visible = products.filter((p) => {
      const matchTerm = p.name.toLowerCase().includes(term) || p.category.toLowerCase().includes(term) || p.description.toLowerCase().includes(term);
      const matchCategory = selectedCategory === 'all' || p.category === selectedCategory;
      let matchPrice = true;
      if (priceRange !== 'all') {
        const [min, max] = priceRange.split('-').map(Number);
        matchPrice = p.price >= min && p.price <= max;
      }
      const matchOffers = !onlyOffers || p.onSale;
      const matchStock = !onlyStock || p.stock > 0;
      return matchTerm && matchCategory && matchPrice && matchOffers && matchStock;
    });

    const sort = sortFilter.value;
    if (sort === 'price-asc') visible.sort((a, b) => a.price - b.price);
    else if (sort === 'price-desc') visible.sort((a, b) => b.price - a.price);
    else if (sort === 'name-asc') visible.sort((a, b) => a.name.localeCompare(b.name));
    else visible.sort((a, b) => Number(b.featured) - Number(a.featured));

    products.forEach((p) => { p.el.style.display = 'none'; });
    visible.forEach((p) => { p.el.style.display = ''; p.el.parentElement && (p.el.parentElement.style.display = ''); });
    resultsInfo.textContent = visible.length ? `Mostrando ${visible.length} producto(s) disponibles.` : 'No hay resultados con los filtros actuales.';
  };

  const saveCart = () => localStorage.setItem(storageKey, JSON.stringify(cart));
  const renderCart = () => {
    const totalItems = cart.reduce((sum, i) => sum + Number(i.quantity || 0), 0);
    const subtotal = cart.reduce((sum, i) => sum + Number(i.price || 0) * Number(i.quantity || 0), 0);
    cartCount.textContent = String(totalItems);
    cartSubtotal.textContent = money(subtotal);
    cartTotal.textContent = money(subtotal);

    if (!cart.length) {
      cartItems.innerHTML = '<div class="empty-state">Aún no has agregado productos.</div>';
      carritoVistaCheckout.innerHTML = 'Tu carrito está vacío.';
      carritoJson.value = '[]';
      saveCart();
      return;
    }

    cartItems.innerHTML = cart.map((item) => `
      <div class="cart-item">
        <img src="${item.image}" alt="${item.name}">
        <div>
          <h4>${item.name}</h4>
          <p>${money(item.price)} c/u</p>
          <div class="qty-controls">
            <button type="button" data-cart-minus="${item.id}">-</button>
            <span>${item.quantity}</span>
            <button type="button" data-cart-plus="${item.id}">+</button>
          </div>
        </div>
        <button class="btn-danger-soft" type="button" data-cart-remove="${item.id}">X</button>
      </div>
    `).join('');

    carritoVistaCheckout.innerHTML = cart.map((item) => `<div class="d-flex justify-content-between border-bottom py-2"><span>${item.name} x${item.quantity}</span><strong>${money(item.price * item.quantity)}</strong></div>`).join('') + `<div class="fw-bold text-end mt-2">Total: ${money(subtotal)}</div>`;
    carritoJson.value = JSON.stringify(cart.map((i) => ({ producto_id: Number(i.id), cantidad: Number(i.quantity) })));
    saveCart();
  };

  const openCart = () => { cartPanel.classList.add('open'); overlay.classList.add('show'); };
  const closeCart = () => { cartPanel.classList.remove('open'); overlay.classList.remove('show'); };

  const addToCart = (id) => {
    const product = products.find((p) => p.id === id);
    if (!product) return;
    const ex = cart.find((i) => i.id === id);
    if (ex) ex.quantity += 1;
    else cart.push({ id: product.id, name: product.name, image: product.image, price: product.price, quantity: 1 });
    renderCart();
    openCart();
  };

  const initSlider = () => {
    const slides = $$('.slide', $('#slider'));
    if (slides.length < 2) return;
    let idx = 0;
    window.setInterval(() => {
      slides[idx].classList.remove('active');
      idx = (idx + 1) % slides.length;
      slides[idx].classList.add('active');
    }, 5000);
  };

  $$('.product-card').forEach((card) => {
    const addBtn = $('[data-add-cart]', card);
    const viewBtn = $('[data-view-product]', card);
    addBtn && addBtn.addEventListener('click', (e) => { e.stopPropagation(); addToCart(Number(card.dataset.id || 0)); });
    const openDetail = () => {
      productoSeleccionado = products.find((p) => p.id === Number(card.dataset.id || 0)) || null;
      if (!productoSeleccionado || !modalProductoDetalle) return;
      detalleNombre.textContent = productoSeleccionado.name;
      detalleDescripcion.textContent = productoSeleccionado.description;
      detalleCategoria.textContent = productoSeleccionado.category;
      detallePrecio.textContent = money(productoSeleccionado.price);
      detalleImagen.src = productoSeleccionado.image;
      detalleImagen.alt = productoSeleccionado.name;
      modalProductoDetalle.show();
    };
    card.addEventListener('click', openDetail);
    viewBtn && viewBtn.addEventListener('click', (e) => { e.stopPropagation(); openDetail(); });
  });

  $('#detalleAgregarCarrito').addEventListener('click', () => {
    if (!productoSeleccionado) return;
    addToCart(Number(productoSeleccionado.id));
    modalProductoDetalle && modalProductoDetalle.hide();
  });

  document.addEventListener('click', (e) => {
    const minus = e.target.closest('[data-cart-minus]');
    const plus = e.target.closest('[data-cart-plus]');
    const remove = e.target.closest('[data-cart-remove]');
    if (minus) {
      const id = Number(minus.dataset.cartMinus || 0);
      cart = cart.map((i) => i.id === id ? { ...i, quantity: Math.max(1, i.quantity - 1) } : i);
      renderCart();
    }
    if (plus) {
      const id = Number(plus.dataset.cartPlus || 0);
      cart = cart.map((i) => i.id === id ? { ...i, quantity: i.quantity + 1 } : i);
      renderCart();
    }
    if (remove) {
      const id = Number(remove.dataset.cartRemove || 0);
      cart = cart.filter((i) => i.id !== id);
      renderCart();
    }
  });

  $('#showAllBtn').addEventListener('click', () => { onlyOffers = false; onlyStock = false; applyFilters(); });
  $('#showOffersBtn').addEventListener('click', () => { onlyOffers = true; onlyStock = false; applyFilters(); });
  $('#showStockBtn').addEventListener('click', () => { onlyStock = true; onlyOffers = false; applyFilters(); });
  const offersTop = $('#showOffersBtnTop'); if (offersTop) offersTop.addEventListener('click', () => { onlyOffers = true; applyFilters(); });
  const stockTop = $('#showStockBtnTop'); if (stockTop) stockTop.addEventListener('click', () => { onlyStock = true; applyFilters(); });

  searchInput.addEventListener('input', applyFilters);
  globalSearch.addEventListener('input', applyFilters);
  categoryFilter.addEventListener('change', (e) => { selectedCategory = e.target.value || 'all'; renderCategories(); applyFilters(); });
  priceFilter.addEventListener('change', applyFilters);
  sortFilter.addEventListener('change', applyFilters);
  $('#clearFilters').addEventListener('click', () => {
    searchInput.value = ''; globalSearch.value = ''; selectedCategory = 'all'; onlyOffers = false; onlyStock = false;
    categoryFilter.value = 'all'; priceFilter.value = 'all'; sortFilter.value = 'featured'; renderCategories(); applyFilters();
  });

  $('#searchBtn').addEventListener('click', applyFilters);
  $('#cartToggle').addEventListener('click', openCart);
  $('#openCartHeader').addEventListener('click', openCart);
  $('#closeCart').addEventListener('click', closeCart);
  overlay.addEventListener('click', closeCart);
  $('#clearCart').addEventListener('click', () => { cart = []; renderCart(); });
  $('#scrollTopBtn').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  $('#openCheckout').addEventListener('click', () => {
    if (!cart.length) return;
    if (!modalCheckout) {
      alert('No fue posible abrir el checkout en este navegador.');
      return;
    }
    modalCheckout.show();
  });

  renderCategories();
  applyFilters();
  renderCart();
  initSlider();
})();
</script>
