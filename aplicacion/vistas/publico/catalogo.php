<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
?>
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
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCheckout">Finalizar compra</button>
    </div>
  </div>
</section>

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
          <article class="card border-0 shadow-sm h-100">
            <?php if (!empty($producto['imagen_catalogo_url'])): ?>
              <img src="<?= e((string) $producto['imagen_catalogo_url']) ?>" alt="<?= e((string) ($producto['nombre'] ?? 'Producto')) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <div class="small text-muted mb-1"><?= e((string) ($producto['categoria'] ?? 'Sin categoría')) ?></div>
              <h2 class="h6 mb-1"><?= e((string) ($producto['nombre'] ?? '')) ?></h2>
              <p class="text-muted small flex-grow-1"><?= e((string) ($producto['descripcion'] ?? 'Sin descripción')) ?></p>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <strong><?= e($fmon((float) ($producto['precio'] ?? 0))) ?></strong>
                <button class="btn btn-sm btn-outline-primary" type="button" data-add-cart data-id="<?= (int) $producto['id'] ?>" data-name="<?= e((string) $producto['nombre']) ?>" data-price="<?= (float) ($producto['precio'] ?? 0) ?>">Agregar</button>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

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
          <div id="carritoVista" class="small mb-3 text-muted">Tu carrito está vacío.</div>
          <input type="hidden" name="carrito_json" id="carrito_json" value="[]">
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre" required></div>
            <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="correo" required></div>
            <div class="col-12"><label class="form-label">Dirección de envío</label><input class="form-control" name="direccion" required></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir comprando</button>
          <button class="btn btn-primary" type="submit">Pagar con Flow</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(() => {
  const storageKey = 'vextra_catalogo_carrito_<?= (int) $empresa['id'] ?>';
  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { cart = []; }
  const carritoVista = document.getElementById('carritoVista');
  const carritoJson = document.getElementById('carrito_json');

  const render = () => {
    if (!Array.isArray(cart) || !cart.length) {
      carritoVista.innerHTML = 'Tu carrito está vacío.';
      carritoJson.value = '[]';
      localStorage.setItem(storageKey, '[]');
      return;
    }
    const total = cart.reduce((acc, i) => acc + (Number(i.precio || 0) * Number(i.cantidad || 0)), 0);
    carritoVista.innerHTML = cart.map((i, idx) => `
      <div class="d-flex justify-content-between border-bottom py-2">
        <div>${i.nombre} <span class="text-muted">x${i.cantidad}</span></div>
        <div>
          $${Math.round(i.precio * i.cantidad).toLocaleString('es-CL')}
          <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-2" data-remove="${idx}">Quitar</button>
        </div>
      </div>`).join('') + `<div class="fw-bold text-end mt-2">Total: $${Math.round(total).toLocaleString('es-CL')}</div>`;
    carritoJson.value = JSON.stringify(cart.map((i) => ({ producto_id: Number(i.producto_id), cantidad: Number(i.cantidad) })));
    localStorage.setItem(storageKey, JSON.stringify(cart));
  };

  document.querySelectorAll('[data-add-cart]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id || 0);
      const precio = Number(btn.dataset.price || 0);
      const nombre = String(btn.dataset.name || 'Producto');
      const ex = cart.find((x) => Number(x.producto_id) === id);
      if (ex) ex.cantidad += 1;
      else cart.push({ producto_id: id, nombre, precio, cantidad: 1 });
      render();
    });
  });

  carritoVista.addEventListener('click', (e) => {
    const idx = Number(e.target.dataset.remove);
    if (Number.isNaN(idx)) return;
    cart.splice(idx, 1);
    render();
  });

  render();
})();
</script>
