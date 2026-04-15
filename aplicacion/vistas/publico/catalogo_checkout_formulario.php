<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$action = url('/catalogo/' . (int) ($empresa['id'] ?? 0) . '/checkout');
$fragmento = static function (string $texto, int $max = 95): string {
    $limpio = trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    if ($limpio === '') {
        return 'Sin descripción disponible.';
    }
    if (mb_strlen($limpio) <= $max) {
        return $limpio;
    }
    return rtrim(mb_substr($limpio, 0, $max - 1)) . '…';
};
?>
<style>
  .checkout-grid{align-items:flex-start}
  .checkout-form-panel{position:sticky;top:16px}
  .checkout-resumen-list{max-height:68vh;overflow:auto}
  .checkout-form-panel .form-label{font-size:.82rem;margin-bottom:.25rem}
  .checkout-form-panel .form-control,.checkout-form-panel .form-select{padding:.38rem .55rem;font-size:.86rem}
  .checkout-form-panel .card-body{padding:1rem}
  .checkout-form-panel .alert{padding:.55rem .7rem;margin-bottom:.75rem}
  @media (max-width:991px){
    .checkout-form-panel{position:static}
    .checkout-resumen-list{max-height:none}
  }
</style>
<div class="container py-3" style="max-width: 1080px;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h1 class="h5 mb-0">Formulario de compra</h1>
    <a href="<?= e(url('/catalogo/' . (int) $empresa['id'])) ?>" class="btn btn-outline-secondary btn-sm">Volver al catálogo</a>
  </div>

  <form method="POST" action="<?= e($action) ?>" class="row g-3 checkout-grid" id="checkoutFormCatalogo">
    <?= csrf_campo() ?>
    <input type="hidden" name="carrito_json" id="carritoJsonInput" value='<?= e(json_encode(array_map(static fn($i) => ['producto_id' => (int) ($i['id'] ?? 0), 'cantidad' => (int) ($i['cantidad'] ?? 1)], $resumen), JSON_UNESCAPED_UNICODE)) ?>'>

    <div class="col-lg-7 order-2 order-lg-1">
      <div class="card"><div class="card-body">
        <h2 class="h6 mb-2">Resumen del pedido</h2>
        <div class="small text-muted mb-2">Puedes ajustar cantidades o eliminar productos.</div>
        <ul class="list-group list-group-flush mb-3 checkout-resumen-list" id="checkoutResumenLista">
          <?php foreach ($resumen as $item): ?>
            <li class="list-group-item px-0 checkout-item" data-id="<?= (int) ($item['id'] ?? 0) ?>" data-cantidad="<?= (int) ($item['cantidad'] ?? 1) ?>" data-precio="<?= (float) ($item['precio'] ?? 0) ?>" data-nombre="<?= e((string) ($item['nombre'] ?? 'Producto')) ?>" data-descripcion="<?= e((string) ($item['descripcion'] ?? '')) ?>" data-imagen="<?= e((string) ($item['imagen'] ?? url('/img/placeholder-producto.svg'))) ?>">
              <div class="d-flex gap-2 align-items-start">
                <img src="<?= e((string) ($item['imagen'] ?? url('/img/placeholder-producto.svg'))) ?>" alt="<?= e((string) ($item['nombre'] ?? 'Producto')) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:8px;background:#f8fafc;">
                <div class="flex-grow-1">
                  <div class="fw-semibold small"><?= e((string) $item['nombre']) ?></div>
                  <div class="text-muted" style="font-size:.78rem;"><?= e($fragmento((string) ($item['descripcion'] ?? ''))) ?></div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <div class="btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-outline-secondary" data-minus="<?= (int) ($item['id'] ?? 0) ?>">−</button>
                      <button type="button" class="btn btn-light disabled" data-qty-label="<?= (int) ($item['id'] ?? 0) ?>"><?= (int) $item['cantidad'] ?></button>
                      <button type="button" class="btn btn-outline-secondary" data-plus="<?= (int) ($item['id'] ?? 0) ?>">+</button>
                    </div>
                    <button type="button" class="btn btn-link btn-sm text-danger p-0" data-remove="<?= (int) ($item['id'] ?? 0) ?>">Eliminar</button>
                  </div>
                  <div class="text-end fw-semibold mt-1" data-subtotal="<?= (int) ($item['id'] ?? 0) ?>"><?= e($fmon((float) $item['subtotal'])) ?></div>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <div id="checkoutResumenVacio" class="alert alert-warning small d-none">No quedan productos en el resumen. Vuelve al catálogo para agregar productos.</div>
      </div></div>
    </div>

    <div class="col-lg-5 order-1 order-lg-2">
      <div class="checkout-form-panel">
        <div class="card mb-2"><div class="card-body">
          <h2 class="h6 mb-2">Datos del comprador</h2>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Nombre y apellido *</label><input class="form-control form-control-sm" name="nombre" required></div>
            <div class="col-md-6"><label class="form-label">Correo *</label><input type="email" class="form-control form-control-sm" name="correo" required></div>
            <div class="col-md-6"><label class="form-label">Teléfono *</label><input class="form-control form-control-sm" name="telefono" placeholder="+56912345678" required></div>
            <div class="col-md-6"><label class="form-label">RUT o documento</label><input class="form-control form-control-sm" name="documento"></div>
            <div class="col-12"><label class="form-label">Empresa (opcional)</label><input class="form-control form-control-sm" name="empresa"></div>
          </div>
        </div></div>

        <div class="card mb-2"><div class="card-body">
          <h2 class="h6 mb-2">Envío</h2>
          <div class="alert alert-info small">
            Envío <strong>por pagar</strong> · plazo máximo <strong>48 horas hábiles</strong> tras confirmar pago.
          </div>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Método *</label>
              <select class="form-select form-select-sm" name="envio_metodo" required>
                <option value="starken">Starken</option>
                <option value="blue_express">Blue Express</option>
                <option value="chile_express">Chile Express</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Referencia</label><input class="form-control form-control-sm" name="referencia"></div>
            <div class="col-12"><label class="form-label">Dirección *</label><input class="form-control form-control-sm" name="direccion" required></div>
            <div class="col-md-4"><label class="form-label">Comuna *</label><input class="form-control form-control-sm" name="comuna" required></div>
            <div class="col-md-4"><label class="form-label">Ciudad *</label><input class="form-control form-control-sm" name="ciudad" required></div>
            <div class="col-md-4"><label class="form-label">Región *</label><input class="form-control form-control-sm" name="region" required></div>
          </div>
        </div></div>

        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between"><span class="small">Total</span><strong id="checkoutTotal"><?= e($fmon((float) $total)) ?></strong></div>
          <div class="form-check my-2"><input class="form-check-input" type="checkbox" name="acepta_terminos" id="acepta_terminos" value="1" required><label class="form-check-label small" for="acepta_terminos">Acepto los términos y confirmo mis datos.</label></div>
          <button class="btn btn-primary btn-sm w-100" type="submit" id="checkoutSubmitBtn">Proceder a pagar</button>
        </div></div>
      </div>
    </div>
  </form>
</div>

<script>
(() => {
  const money = (v) => `$${Math.round(Number(v || 0)).toLocaleString('es-CL')}`;
  const form = document.getElementById('checkoutFormCatalogo');
  const lista = document.getElementById('checkoutResumenLista');
  const totalEl = document.getElementById('checkoutTotal');
  const vacioEl = document.getElementById('checkoutResumenVacio');
  const carritoJsonInput = document.getElementById('carritoJsonInput');
  const submitBtn = document.getElementById('checkoutSubmitBtn');
  if (!form || !lista || !totalEl || !carritoJsonInput || !submitBtn) return;

  const items = Array.from(lista.querySelectorAll('.checkout-item')).map((el) => ({
    id: Number(el.dataset.id || 0),
    precio: Number(el.dataset.precio || 0),
    cantidad: Math.max(1, Number(el.dataset.cantidad || 1)),
  }));

  const render = () => {
    const total = items.reduce((sum, i) => sum + (i.precio * i.cantidad), 0);
    totalEl.textContent = money(total);
    carritoJsonInput.value = JSON.stringify(items.map((i) => ({ producto_id: i.id, cantidad: i.cantidad })));

    items.forEach((item) => {
      const qty = lista.querySelector(`[data-qty-label="${item.id}"]`);
      const subtotal = lista.querySelector(`[data-subtotal="${item.id}"]`);
      if (qty) qty.textContent = String(item.cantidad);
      if (subtotal) subtotal.textContent = money(item.cantidad * item.precio);
    });

    const vacio = items.length === 0;
    if (vacioEl) vacioEl.classList.toggle('d-none', !vacio);
    submitBtn.disabled = vacio;
  };

  lista.addEventListener('click', (ev) => {
    const minus = ev.target.closest('[data-minus]');
    const plus = ev.target.closest('[data-plus]');
    const remove = ev.target.closest('[data-remove]');

    if (minus) {
      const id = Number(minus.getAttribute('data-minus') || 0);
      const item = items.find((i) => i.id === id);
      if (item) {
        item.cantidad = Math.max(1, item.cantidad - 1);
        render();
      }
      return;
    }

    if (plus) {
      const id = Number(plus.getAttribute('data-plus') || 0);
      const item = items.find((i) => i.id === id);
      if (item) {
        item.cantidad += 1;
        render();
      }
      return;
    }

    if (remove) {
      const id = Number(remove.getAttribute('data-remove') || 0);
      const idx = items.findIndex((i) => i.id === id);
      if (idx >= 0) {
        items.splice(idx, 1);
      }
      const li = remove.closest('.checkout-item');
      if (li) li.remove();
      render();
    }
  });

  render();
})();
</script>
