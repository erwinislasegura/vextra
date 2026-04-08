<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Editar caja</h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta/cajas')) ?>">Volver</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="<?= e(url('/app/punto-venta/cajas/editar/' . (int) ($caja['id'] ?? 0))) ?>" class="row g-2">
      <?= csrf_campo() ?>
      <div class="col-md-5">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" value="<?= e($caja['nombre'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Código</label>
        <input class="form-control" name="codigo" value="<?= e($caja['codigo'] ?? '') ?>" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <select class="form-select" name="estado">
          <option value="activa" <?= (($caja['estado'] ?? '') === 'activa') ? 'selected' : '' ?>>Activa</option>
          <option value="inactiva" <?= (($caja['estado'] ?? '') === 'inactiva') ? 'selected' : '' ?>>Inactiva</option>
        </select>
      </div>
      <div class="col-md-2 d-grid align-items-end">
        <button class="btn btn-primary mt-4">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
