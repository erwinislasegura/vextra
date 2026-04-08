<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Ver caja</h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta/cajas')) ?>">Volver</a>
</div>

<div class="card">
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">ID</dt><dd class="col-sm-9"><?= (int) ($caja['id'] ?? 0) ?></dd>
      <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9"><?= e($caja['nombre'] ?? '') ?></dd>
      <dt class="col-sm-3">Código</dt><dd class="col-sm-9"><?= e($caja['codigo'] ?? '') ?></dd>
      <dt class="col-sm-3">Estado</dt><dd class="col-sm-9"><?= e($caja['estado'] ?? '') ?></dd>
      <dt class="col-sm-3">Fecha creación</dt><dd class="col-sm-9"><?= e($caja['fecha_creacion'] ?? '') ?></dd>
    </dl>
  </div>
</div>
