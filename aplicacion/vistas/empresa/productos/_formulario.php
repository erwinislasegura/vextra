<?php
$producto = $producto ?? null;
$categorias = $categorias ?? [];
$accion = $accion ?? url('/app/productos');
$textoBoton = $textoBoton ?? 'Guardar ítem';
$mostrarCancelar = $mostrarCancelar ?? false;
$rutaCancelar = $rutaCancelar ?? url('/app/productos');
$mostrarModalCategoria = $mostrarModalCategoria ?? true;
$modalId = $modalId ?? 'modalNuevaCategoria';
$redirigirA = $redirigirA ?? '/app/productos';
$unidadActual = $producto['unidad'] ?? 'unidad';
?>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Uso y buenas prácticas para productos</div>
  <ul class="mb-0 small ps-3">
    <li>Incluye datos completos para que la cotización sea más clara para el cliente.</li>
    <li>Configura <strong>stock mínimo</strong> y <strong>stock crítico</strong> para alertas automáticas.</li>
    <li>Si el ítem no se ofrece temporalmente, cambia estado a <strong>inactivo</strong>.</li>
  </ul>
</div>
<form method="POST" action="<?= e($accion) ?>" class="row g-2">
  <?= csrf_campo() ?>
  <?php if (!$producto): ?>
    <input type="hidden" name="redirect_to" value="<?= e($redirigirA) ?>">
  <?php endif; ?>
  <div class="col-md-2">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select">
      <option value="producto" <?= ($producto['tipo'] ?? 'producto') === 'producto' ? 'selected' : '' ?>>Producto</option>
      <option value="servicio" <?= ($producto['tipo'] ?? '') === 'servicio' ? 'selected' : '' ?>>Servicio</option>
    </select>
  </div>
  <div class="col-md-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <label class="form-label mb-0">Categoría</label>
      <?php if ($mostrarModalCategoria): ?>
        <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>">+ Nueva</button>
      <?php endif; ?>
    </div>
    <select name="categoria_id" class="form-select">
      <option value="">Sin categoría</option>
      <?php foreach($categorias as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>" <?= (int)($producto['categoria_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Agrupa productos para encontrarlos más rápido.</div>
  </div>
  <div class="col-md-2">
    <label class="form-label" for="producto_codigo">Código interno</label>
    <input id="producto_codigo" name="codigo" class="form-control" value="<?= e($producto['codigo'] ?? '') ?>" required>
    <div class="form-text">Código corto para tu operación diaria.</div>
  </div>
  <div class="col-md-2">
    <label class="form-label">SKU</label>
    <input name="sku" class="form-control" value="<?= e($producto['sku'] ?? '') ?>" placeholder="SKU-0001">
  </div>
  <div class="col-md-3">
    <label class="form-label">Código de barras</label>
    <input name="codigo_barras" class="form-control" value="<?= e($producto['codigo_barras'] ?? '') ?>" placeholder="EAN/UPC">
  </div>
  <div class="col-md-3">
    <label class="form-label" for="producto_nombre">Nombre</label>
    <input id="producto_nombre" name="nombre" class="form-control" value="<?= e($producto['nombre'] ?? '') ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Descripción</label>
    <input name="descripcion" class="form-control" value="<?= e($producto['descripcion'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Unidad de medida</label>
    <select name="unidad" class="form-select">
      <?php foreach (['unidad','kg','g','lb','litro','ml','metro','cm','caja','paquete','servicio','hora'] as $unidad): ?>
        <option value="<?= e($unidad) ?>" <?= $unidadActual === $unidad ? 'selected' : '' ?>><?= e(ucfirst($unidad)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Precio</label>
    <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= e((string)($producto['precio'] ?? '')) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Costo</label>
    <input type="number" step="0.01" min="0" name="costo" class="form-control" value="<?= e((string)($producto['costo'] ?? '')) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Impuesto %</label>
    <input type="number" step="0.01" min="0" name="impuesto" class="form-control" value="<?= e((string)($producto['impuesto'] ?? 19)) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Desc. máximo %</label>
    <input type="number" step="0.01" min="0" name="descuento_maximo" class="form-control" value="<?= e((string)($producto['descuento_maximo'] ?? 0)) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Stock mínimo</label>
    <input type="number" step="0.01" min="0" name="stock_minimo" class="form-control" value="<?= e((string)($producto['stock_minimo'] ?? 0)) ?>">
    <div class="form-text">Nivel base para reposición.</div>
  </div>
  <div class="col-md-2">
    <label class="form-label">Stock crítico</label>
    <input type="number" step="0.01" min="0" name="stock_critico" class="form-control" value="<?= e((string)($producto['stock_critico'] ?? $producto['stock_aviso'] ?? 0)) ?>">
    <div class="form-text">Umbral para alerta crítica.</div>
  </div>
  <div class="col-md-2">
    <label class="form-label">Stock actual</label>
    <input type="number" step="0.01" min="0" name="stock_actual" class="form-control" value="<?= e((string)($producto['stock_actual'] ?? 0)) ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
      <option value="activo" <?= ($producto['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
      <option value="inactivo" <?= ($producto['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
    </select>
  </div>
  <div class="col-12">
    <button class="btn btn-primary btn-sm"><?= e($textoBoton) ?></button>
    <?php if ($mostrarCancelar): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e($rutaCancelar) ?>">Cancelar</a>
    <?php endif; ?>
  </div>
</form>

<?php if ($mostrarModalCategoria): ?>
<div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-labelledby="<?= e($modalId) ?>Label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= e(url('/app/categorias')) ?>">
        <?= csrf_campo() ?>
        <input type="hidden" name="redirect_to" value="<?= e($redirigirA) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="<?= e($modalId) ?>Label">Nueva categoría</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body row g-2">
          <div class="col-12"><label class="form-label" for="nueva_categoria_nombre_<?= e($modalId) ?>">Nombre</label><input id="nueva_categoria_nombre_<?= e($modalId) ?>" name="nombre" class="form-control" required></div>
          <div class="col-12"><label class="form-label">Descripción</label><input name="descripcion" class="form-control"></div>
          <div class="col-12"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm">Guardar categoría</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
