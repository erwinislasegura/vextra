<h1 class="h4 mb-3">Crear cliente</h1>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Guía rápida de registro</div>
  <ul class="mb-0 small ps-3">
    <li>Completa al menos razón social y datos de contacto para iniciar cotizaciones.</li>
    <li>Asocia listas de precios cuando el cliente tenga condiciones comerciales específicas.</li>
  </ul>
</div>
<div class="card">
  <div class="card-body">
    <form method="POST" action="<?= e(url('/app/clientes/crear')) ?>" class="row g-3">
      <?= csrf_campo() ?>
      <div class="col-md-4">
        <label class="form-label" for="crear_cliente_razon_social">Razón social</label>
        <input id="crear_cliente_razon_social" class="form-control" name="razon_social" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre comercial</label>
        <input class="form-control" name="nombre_comercial">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre de contacto</label>
        <input class="form-control" name="nombre">
      </div>
      <div class="col-md-3">
        <label class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo">
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input class="form-control" name="telefono">
      </div>
      <div class="col-md-3">
        <label class="form-label">Ciudad</label>
        <input class="form-control" name="ciudad">
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>
      <?php if (($permiteGestionListasPrecios ?? false)): ?>
      <div class="col-md-4">
        <label class="form-label">Listas de precios</label>
        <select name="lista_precio_ids[]" class="form-select" multiple size="4">
          <?php foreach (($listasPrecios ?? []) as $lp): ?>
            <option value="<?= (int) $lp['id'] ?>"><?= e($lp['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Puedes asociar una o más listas. Si no seleccionas ninguna, se cotiza sin lista.</div>
      </div>
      <?php endif; ?>
      <div class="col-md-8">
        <label class="form-label">Dirección</label>
        <input class="form-control" name="direccion">
      </div>
      <div class="col-md-4">
        <label class="form-label">RUT/ID fiscal</label>
        <input class="form-control" name="identificador_fiscal">
      </div>
      <div class="col-12">
        <label class="form-label">Notas</label>
        <textarea class="form-control" name="notas" rows="2"></textarea>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/app/clientes')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
        <button class="btn btn-primary btn-sm">Guardar</button>
      </div>
    </form>
  </div>
</div>
