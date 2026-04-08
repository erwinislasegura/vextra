<h1 class="h4 mb-3">Ver cliente</h1>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-3">Razón social</dt><dd class="col-sm-9"><?= e($cliente['razon_social'] ?: $cliente['nombre']) ?></dd>
  <dt class="col-sm-3">Nombre comercial</dt><dd class="col-sm-9"><?= e($cliente['nombre_comercial'] ?: '') ?></dd>
  <dt class="col-sm-3">Correo</dt><dd class="col-sm-9"><?= e($cliente['correo']) ?></dd>
  <dt class="col-sm-3">Teléfono</dt><dd class="col-sm-9"><?= e($cliente['telefono']) ?></dd>
  <dt class="col-sm-3">Estado</dt><dd class="col-sm-9"><?= e($cliente['estado']) ?></dd>
</dl></div></div>
<div class="mt-3"><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/clientes')) ?>">Volver</a> <a class="btn btn-primary btn-sm" href="<?= e(url('/app/clientes/editar/' . $cliente['id'])) ?>">Editar</a></div>
