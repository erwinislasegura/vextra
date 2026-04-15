<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$action = url('/catalogo/' . (int) ($empresa['id'] ?? 0) . '/checkout');
?>
<div class="container py-4" style="max-width: 1024px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Formulario de compra</h1>
    <a href="<?= e(url('/catalogo/' . (int) $empresa['id'])) ?>" class="btn btn-outline-secondary btn-sm">Volver al catálogo</a>
  </div>

  <form method="POST" action="<?= e($action) ?>" class="row g-3">
    <?= csrf_campo() ?>
    <input type="hidden" name="carrito_json" value='<?= e(json_encode(array_map(static fn($i) => ['producto_id' => (int) ($i['id'] ?? 0), 'cantidad' => (int) ($i['cantidad'] ?? 1)], $resumen), JSON_UNESCAPED_UNICODE)) ?>'>

    <div class="col-lg-8">
      <div class="card mb-3"><div class="card-body">
        <h2 class="h6 mb-3">1) Datos personales</h2>
        <div class="row g-2">
          <div class="col-md-6"><label class="form-label">Nombre y apellido *</label><input class="form-control" name="nombre" required></div>
          <div class="col-md-6"><label class="form-label">Correo electrónico *</label><input type="email" class="form-control" name="correo" required></div>
          <div class="col-md-6"><label class="form-label">Teléfono *</label><input class="form-control" name="telefono" placeholder="+56912345678" required></div>
          <div class="col-md-6"><label class="form-label">RUT o documento</label><input class="form-control" name="documento"></div>
          <div class="col-12"><label class="form-label">Empresa (opcional)</label><input class="form-control" name="empresa"></div>
        </div>
      </div></div>

      <div class="card"><div class="card-body">
        <h2 class="h6 mb-3">2) Envío</h2>
        <div class="row g-2">
          <div class="col-md-6"><label class="form-label">Método de envío *</label>
            <select class="form-select" name="envio_metodo" required>
              <?php foreach ($metodosEnvio as $codigo => $label): ?>
                <option value="<?= e($codigo) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><label class="form-label">Dirección *</label><input class="form-control" name="direccion" required></div>
          <div class="col-md-6"><label class="form-label">Comuna *</label><input class="form-control" name="comuna" required></div>
          <div class="col-md-6"><label class="form-label">Ciudad *</label><input class="form-control" name="ciudad" required></div>
          <div class="col-md-6"><label class="form-label">Región *</label><input class="form-control" name="region" required></div>
          <div class="col-md-6"><label class="form-label">Referencia</label><input class="form-control" name="referencia"></div>
        </div>
      </div></div>
    </div>

    <div class="col-lg-4">
      <div class="card sticky-top" style="top: 18px;"><div class="card-body">
        <h2 class="h6 mb-3">Resumen del pedido</h2>
        <div class="small text-muted mb-2">Carrito de compra</div>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($resumen as $item): ?>
            <li class="list-group-item px-0 d-flex justify-content-between">
              <span><?= e((string) $item['nombre']) ?> x <?= (int) $item['cantidad'] ?></span>
              <strong><?= e($fmon((float) $item['subtotal'])) ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex justify-content-between mb-3"><span>Total</span><strong><?= e($fmon((float) $total)) ?></strong></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="acepta_terminos" id="acepta_terminos" value="1" required><label class="form-check-label small" for="acepta_terminos">Acepto los términos y confirmo mis datos.</label></div>
        <button class="btn btn-primary w-100" type="submit">Proceder a pagar</button>
      </div></div>
    </div>
  </form>
</div>
