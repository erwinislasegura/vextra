<h1 class="h4 mb-3">Ver producto/servicio</h1>
<div class="card">
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Código</dt><dd class="col-sm-9"><?= e($producto['codigo']) ?></dd>
      <dt class="col-sm-3">SKU</dt><dd class="col-sm-9"><?= e($producto['sku'] ?? '-') ?></dd>
      <dt class="col-sm-3">Código de barras</dt><dd class="col-sm-9"><?= e($producto['codigo_barras'] ?? '-') ?></dd>
      <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9"><?= e($producto['nombre']) ?></dd>
      <dt class="col-sm-3">Tipo</dt><dd class="col-sm-9"><?= e($producto['tipo']) ?></dd>
      <dt class="col-sm-3">Unidad</dt><dd class="col-sm-9"><?= e($producto['unidad'] ?? '-') ?></dd>
      <dt class="col-sm-3">Precio</dt><dd class="col-sm-9">$<?= number_format((float)$producto['precio'],2) ?></dd>
      <dt class="col-sm-3">Stock mínimo</dt><dd class="col-sm-9"><?= e((string)($producto['stock_minimo'] ?? 0)) ?></dd>
      <dt class="col-sm-3">Stock actual</dt><dd class="col-sm-9"><?= e((string)($producto['stock_actual'] ?? 0)) ?></dd>
      <dt class="col-sm-3">Stock crítico</dt><dd class="col-sm-9"><?= e((string)($producto['stock_critico'] ?? $producto['stock_aviso'] ?? 0)) ?></dd>
      <dt class="col-sm-3">Estado</dt><dd class="col-sm-9"><?= e($producto['estado']) ?></dd>
    </dl>
  </div>
</div>
<div class="mt-3"><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/productos')) ?>">Volver</a> <a class="btn btn-primary btn-sm" href="<?= e(url('/app/productos/editar/' . $producto['id'])) ?>">Editar</a></div>
