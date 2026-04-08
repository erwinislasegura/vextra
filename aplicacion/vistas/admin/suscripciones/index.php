<div class="d-flex justify-content-between mb-3"><h1 class="h5 mb-0">Suscripciones</h1></div>
<form class="row g-2 mb-2" method="GET" action="<?= e(url('/admin/suscripciones')) ?>">
  <div class="col-md-4">
    <select name="estado" class="form-select">
      <option value="">Todos los estados</option>
      <?php foreach (['activa','pendiente','por_vencer','vencida','suspendida','cancelada'] as $est): ?>
        <option value="<?= e($est) ?>" <?= $filtros['estado'] === $est ? 'selected' : '' ?>><?= e($est) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <select name="plan_id" class="form-select">
      <option value="">Todos los planes</option>
      <?php foreach ($planes as $p): ?>
        <option value="<?= (int) $p['id'] ?>" <?= (string) $filtros['plan_id'] === (string) $p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>

<div class="card">
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-sm table-hover mb-0 tabla-admin">
      <thead>
        <tr><th>Empresa</th><th>Plan</th><th>Inicio</th><th>Vence</th><th>Días</th><th>Monto</th><th>Estado</th><th>Gestión</th></tr>
      </thead>
      <tbody>
        <?php foreach ($suscripciones as $s): ?>
          <tr>
            <td><?= e($s['empresa']) ?></td>
            <td><?= e($s['plan']) ?></td>
            <td><?= e($s['fecha_inicio']) ?></td>
            <td><?= e($s['fecha_vencimiento']) ?></td>
            <td><?= e((string) $s['dias_restantes']) ?></td>
            <td>$<?= number_format((float) $s['precio_mensual'], 0, ',', '.') ?></td>
            <td><?= e($s['estado']) ?></td>
            <td>
              <form method="POST" action="<?= e(url('/admin/suscripciones/editar/' . (int) $s['id'])) ?>" class="d-flex gap-1 align-items-center">
                <?= csrf_campo() ?>
                <input type="date" name="fecha_vencimiento" class="form-control form-control-sm" value="<?= e($s['fecha_vencimiento']) ?>" required>
                <select class="form-select form-select-sm" name="estado">
                  <?php foreach (['activa','por_vencer','vencida','suspendida','cancelada'] as $es): ?>
                    <option value="<?= e($es) ?>" <?= $s['estado'] === $es ? 'selected' : '' ?>><?= e($es) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="plan_id" value="<?= (int) $s['plan_id'] ?>">
                <input type="hidden" name="fecha_inicio" value="<?= e($s['fecha_inicio']) ?>">
                <button class="btn btn-sm btn-outline-primary">Guardar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
