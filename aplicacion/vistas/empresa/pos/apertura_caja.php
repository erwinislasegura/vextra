<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Apertura de caja</h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta')) ?>">Ir al POS</a>
</div>

<?php if ($apertura): ?>
  <div class="alert alert-info">Ya tienes la caja <strong><?= e($apertura['caja_nombre']) ?></strong> abierta desde <?= e($apertura['fecha_apertura']) ?>.</div>
<?php else: ?>
  <div class="card">
    <div class="card-body">
      <form method="POST" class="row g-2" action="<?= e(url('/app/punto-venta/apertura-caja')) ?>">
        <?= csrf_campo() ?>
        <div class="col-md-4">
          <label class="form-label">Caja / terminal</label>
          <select name="caja_id" class="form-select" required>
            <option value="">Selecciona...</option>
            <?php foreach ($cajas as $caja): ?>
              <option value="<?= (int) $caja['id'] ?>"><?= e($caja['nombre']) ?> (<?= e($caja['codigo']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Monto inicial</label><input class="form-control" type="number" step="0.01" min="0" name="monto_inicial" required></div>
        <div class="col-md-5"><label class="form-label">Observación</label><input class="form-control" name="observacion" placeholder="Opcional"></div>
        <div class="col-12"><button class="btn btn-primary">Abrir caja</button></div>
      </form>
    </div>
  </div>
<?php endif; ?>
