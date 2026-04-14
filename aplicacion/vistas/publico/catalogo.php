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
    $normalizada = '/' . ltrim($normalizada, '/');

    if (str_starts_with($normalizada, '/uploads/') || str_starts_with($normalizada, '/img/')) {
        return url($normalizada);
    }

    return url('/' . ltrim($normalizada, '/'));
};
?>
<style>
  .catalogo-card{border:1px solid #edf0f3;border-radius:1rem;overflow:hidden;transition:all .2s ease;cursor:pointer}
  .catalogo-card:hover{transform:translateY(-2px);box-shadow:0 .75rem 1.5rem rgba(17,24,39,.10)!important}
  .catalogo-card__img{height:220px;object-fit:cover;background:#f8fafc}
  .catalogo-card__desc{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;min-height:60px}
  .catalogo-card__cta{border-radius:.75rem}
  .modal-producto__img{max-height:360px;object-fit:cover;border-radius:.9rem;background:#f8fafc}
  .catalogo-slider{position:relative;overflow:hidden;border:1px solid #e5e7eb;border-radius:1rem;min-height:220px;background:#0f172a}
  .catalogo-slider__image{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.55}
  .catalogo-slider__overlay{position:relative;z-index:1;padding:2rem;color:#fff;max-width:640px}
  .catalogo-slider__title{font-size:clamp(1.2rem,2.5vw,2rem);font-weight:700;line-height:1.2}
  .catalogo-slider__desc{font-size:1rem;opacity:.92}
</style>
<section class="py-4 bg-white border-bottom">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 justify-content-between">
      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($logoCatalogo)): ?>
          <img src="<?= e((string) $logoCatalogo) ?>" alt="Logo empresa" style="width:64px;height:64px;object-fit:contain;border-radius:12px;border:1px solid #e5e7eb;background:#fff;">
        <?php endif; ?>
        <div>
          <h1 class="h3 mb-1"><?= e((string) ($empresa['nombre_comercial'] ?? 'Catálogo')) ?></h1>
          <div class="text-muted">Catálogo en línea con compra y pago inmediato por Flow</div>
        </div>
      </div>
      <button class="btn btn-primary position-relative" type="button" id="abrirResumenCarrito">
        Ver carrito
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="carritoContador">0</span>
      </button>
    </div>
  </div>
</section>

<?php if (!empty($sliderCatalogo['imagen'])): ?>
<section class="py-3 bg-white">
  <div class="container">
    <article class="catalogo-slider shadow-sm">
      <img src="<?= e((string) $sliderCatalogo['imagen']) ?>" alt="Slider catálogo" class="catalogo-slider__image" loading="lazy">
      <div class="catalogo-slider__overlay">
        <?php if (!empty($sliderCatalogo['titulo'])): ?>
          <h2 class="catalogo-slider__title mb-2"><?= e((string) $sliderCatalogo['titulo']) ?></h2>
        <?php endif; ?>
        <?php if (!empty($sliderCatalogo['bajada'])): ?>
          <p class="catalogo-slider__desc mb-3"><?= e((string) $sliderCatalogo['bajada']) ?></p>
        <?php endif; ?>
        <?php if (!empty($sliderCatalogo['boton_texto']) && !empty($sliderCatalogo['boton_url'])): ?>
          <a href="<?= e((string) $sliderCatalogo['boton_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-sm px-3">
            <?= e((string) $sliderCatalogo['boton_texto']) ?>
          </a>
        <?php endif; ?>
      </div>
    </article>
  </div>
</section>
<?php endif; ?>

<section class="py-4">
  <div class="container">
    <form class="row g-2 mb-3" method="GET">
      <div class="col-md-6"><input class="form-control" name="q" value="<?= e($buscar) ?>" placeholder="Buscar producto o servicio"></div>
      <div class="col-md-4">
        <select class="form-select" name="categoria">
          <option value="0">Todas las categorías</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (int) $categoriaId === (int) $cat['id'] ? 'selected' : '' ?>><?= e((string) ($cat['nombre'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filtrar</button></div>
    </form>

    <div class="row g-3">
      <?php if (empty($productos)): ?>
        <div class="col-12"><div class="alert alert-light border text-muted mb-0">No hay productos publicados para los filtros seleccionados.</div></div>
      <?php endif; ?>
      <?php foreach ($productos as $producto): ?>
        <div class="col-md-6 col-lg-4">
          <article class="card catalogo-card shadow-sm h-100" data-producto-card
            data-id="<?= (int) $producto['id'] ?>"
            data-name="<?= e((string) ($producto['nombre'] ?? 'Producto')) ?>"
            data-price="<?= (float) ($producto['precio'] ?? 0) ?>"
            data-category="<?= e((string) ($producto['categoria'] ?? 'Sin categoría')) ?>"
            data-description="<?= e((string) ($producto['descripcion'] ?? 'Sin descripción')) ?>"
            data-image="<?= e($resolverImagenProducto((string) ($producto['imagen_catalogo'] ?? $producto['imagen_catalogo_url'] ?? ''))) ?>">
            <?php $imagenProducto = (string) ($producto['imagen_catalogo'] ?? $producto['imagen_catalogo_url'] ?? ''); ?>
            <img src="<?= e($resolverImagenProducto($imagenProducto)) ?>" alt="<?= e((string) ($producto['nombre'] ?? 'Producto')) ?>" class="card-img-top catalogo-card__img" loading="lazy">
            <div class="card-body d-flex flex-column">
              <div class="small text-muted mb-1"><?= e((string) ($producto['categoria'] ?? 'Sin categoría')) ?></div>
              <h2 class="h6 mb-1"><?= e((string) ($producto['nombre'] ?? '')) ?></h2>
              <p class="text-muted small flex-grow-1 catalogo-card__desc"><?= e((string) ($producto['descripcion'] ?? 'Sin descripción')) ?></p>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <strong><?= e($fmon((float) ($producto['precio'] ?? 0))) ?></strong>
                <button class="btn btn-sm btn-outline-primary catalogo-card__cta" type="button" data-add-cart data-id="<?= (int) $producto['id'] ?>" data-name="<?= e((string) $producto['nombre']) ?>" data-price="<?= (float) ($producto['precio'] ?? 0) ?>">Agregar</button>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="modal fade" id="modalResumenCarrito" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Resumen del carrito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="carritoVista" class="small mb-0 text-muted">Tu carrito está vacío.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir comprando</button>
        <button type="button" class="btn btn-primary" id="btnIrFormularioPago">Pagar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCheckout" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="<?= e(url('/catalogo/' . (int) $empresa['id'] . '/checkout')) ?>">
        <?= csrf_campo() ?>
        <div class="modal-header">
          <h5 class="modal-title">Carrito y datos de envío</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="border rounded p-2 mb-3 bg-light">
            <div class="small fw-semibold mb-1">Resumen de compra</div>
            <div id="carritoVistaCheckout" class="small text-muted">Tu carrito está vacío.</div>
          </div>
          <input type="hidden" name="carrito_json" id="carrito_json" value="[]">
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre" required></div>
            <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="correo" required></div>
            <div class="col-12"><label class="form-label">Dirección de envío</label><input class="form-control" name="direccion" required></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir comprando</button>
          <button class="btn btn-primary" type="submit">Pagar ahora</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalProductoDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body pt-1">
        <div class="row g-3 g-lg-4 align-items-start">
          <div class="col-lg-6">
            <img id="detalleProductoImagen" src="" alt="Producto" class="w-100 modal-producto__img">
          </div>
          <div class="col-lg-6">
            <div class="small text-uppercase text-muted mb-2" id="detalleProductoCategoria"></div>
            <h3 class="h4 mb-2" id="detalleProductoNombre"></h3>
            <p class="text-muted mb-3" id="detalleProductoDescripcion"></p>
            <div class="d-flex justify-content-between align-items-center border rounded-3 p-3 bg-light">
              <div>
                <div class="small text-muted">Precio</div>
                <div class="h4 mb-0" id="detalleProductoPrecio"></div>
              </div>
              <button type="button" class="btn btn-primary px-4" id="detalleAgregarCarrito">Agregar al carro</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const storageKey = 'vextra_catalogo_carrito_<?= (int) $empresa['id'] ?>';
  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { cart = []; }
  const carritoVista = document.getElementById('carritoVista');
  const carritoVistaCheckout = document.getElementById('carritoVistaCheckout');
  const carritoContador = document.getElementById('carritoContador');
  const abrirResumenBtn = document.getElementById('abrirResumenCarrito');
  const carritoJson = document.getElementById('carrito_json');
  const modalResumen = new bootstrap.Modal(document.getElementById('modalResumenCarrito'));
  const modalCheckout = new bootstrap.Modal(document.getElementById('modalCheckout'));
  const modalProductoDetalle = new bootstrap.Modal(document.getElementById('modalProductoDetalle'));
  const detalleNombre = document.getElementById('detalleProductoNombre');
  const detalleDescripcion = document.getElementById('detalleProductoDescripcion');
  const detalleCategoria = document.getElementById('detalleProductoCategoria');
  const detallePrecio = document.getElementById('detalleProductoPrecio');
  const detalleImagen = document.getElementById('detalleProductoImagen');
  const detalleAgregarBtn = document.getElementById('detalleAgregarCarrito');
  let productoSeleccionado = null;

  const money = (value) => `$${Math.round(Number(value || 0)).toLocaleString('es-CL')}`;

  const agregarProducto = (id, precio, nombre) => {
    if (!id || Number.isNaN(id)) return;
    const ex = cart.find((x) => Number(x.producto_id) === Number(id));
    if (ex) ex.cantidad += 1;
    else cart.push({ producto_id: Number(id), nombre: String(nombre || 'Producto'), precio: Number(precio || 0), cantidad: 1 });
    render();
  };

  const render = () => {
    if (!Array.isArray(cart) || !cart.length) {
      carritoVista.innerHTML = 'Tu carrito está vacío.';
      carritoVistaCheckout.innerHTML = 'Tu carrito está vacío.';
      carritoJson.value = '[]';
      carritoContador.textContent = '0';
      localStorage.setItem(storageKey, '[]');
      return;
    }
    const total = cart.reduce((acc, i) => acc + (Number(i.precio || 0) * Number(i.cantidad || 0)), 0);
    const resumenHtml = cart.map((i, idx) => `
      <div class="d-flex justify-content-between border-bottom py-2">
        <div>${i.nombre} <span class="text-muted">x${i.cantidad}</span></div>
        <div>
          ${money(i.precio * i.cantidad)}
          <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-2" data-remove="${idx}">Quitar</button>
        </div>
      </div>`).join('') + `<div class="fw-bold text-end mt-2">Total: ${money(total)}</div>`;
    carritoVista.innerHTML = resumenHtml;
    carritoVistaCheckout.innerHTML = resumenHtml;
    carritoJson.value = JSON.stringify(cart.map((i) => ({ producto_id: Number(i.producto_id), cantidad: Number(i.cantidad) })));
    carritoContador.textContent = String(cart.reduce((acc, i) => acc + Number(i.cantidad || 0), 0));
    localStorage.setItem(storageKey, JSON.stringify(cart));
  };

  document.querySelectorAll('[data-add-cart]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.stopPropagation();
      agregarProducto(Number(btn.dataset.id || 0), Number(btn.dataset.price || 0), String(btn.dataset.name || 'Producto'));
      modalResumen.show();
    });
  });

  document.querySelectorAll('[data-producto-card]').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (event.target.closest('[data-add-cart]')) return;
      productoSeleccionado = {
        id: Number(card.dataset.id || 0),
        nombre: String(card.dataset.name || 'Producto'),
        precio: Number(card.dataset.price || 0),
        categoria: String(card.dataset.category || 'Sin categoría'),
        descripcion: String(card.dataset.description || 'Sin descripción'),
        imagen: String(card.dataset.image || ''),
      };

      detalleNombre.textContent = productoSeleccionado.nombre;
      detalleDescripcion.textContent = productoSeleccionado.descripcion;
      detalleCategoria.textContent = productoSeleccionado.categoria;
      detallePrecio.textContent = money(productoSeleccionado.precio);
      detalleImagen.src = productoSeleccionado.imagen;
      detalleImagen.alt = productoSeleccionado.nombre;
      modalProductoDetalle.show();
    });
  });

  detalleAgregarBtn.addEventListener('click', () => {
    if (!productoSeleccionado) return;
    agregarProducto(productoSeleccionado.id, productoSeleccionado.precio, productoSeleccionado.nombre);
    modalProductoDetalle.hide();
    modalResumen.show();
  });

  carritoVista.addEventListener('click', (e) => {
    const idx = Number(e.target.dataset.remove);
    if (Number.isNaN(idx)) return;
    cart.splice(idx, 1);
    render();
  });

  abrirResumenBtn.addEventListener('click', () => {
    modalResumen.show();
  });

  document.getElementById('btnIrFormularioPago').addEventListener('click', () => {
    if (!cart.length) return;
    modalResumen.hide();
    modalCheckout.show();
  });

  render();
})();
</script>
