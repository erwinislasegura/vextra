<h1 class="h5 mb-3">Detalle de empresa</h1>
<div class="row g-3">
  <div class="col-lg-6"><div class="card card-body small">
    <h2 class="h6 mb-2"><?= e($empresa['nombre_comercial']) ?></h2>
    <div><strong>Razón social:</strong> <?= e($empresa['razon_social']) ?></div>
    <div><strong>Correo:</strong> <?= e($empresa['correo']) ?></div>
    <div><strong>Teléfono:</strong> <?= e($empresa['telefono']) ?></div>
    <div><strong>Identificador fiscal:</strong> <?= e($empresa['identificador_fiscal']) ?></div>
    <div><strong>Plan activo:</strong> <?= e($empresa['plan_nombre'] ?? 'Sin plan') ?></div>
    <div><strong>Estado:</strong> <?= e($empresa['estado']) ?></div>
    <div><strong>Suscripción:</strong> <?= e($empresa['suscripcion_estado'] ?? '-') ?> (vence <?= e($empresa['fecha_vencimiento'] ?? '-') ?>)</div>
    <div><strong>Días restantes del plan:</strong> <?= isset($empresa['dias_restantes']) && $empresa['dias_restantes'] !== null ? (int) $empresa['dias_restantes'] : '-' ?></div>
  </div></div>

  <div class="col-lg-6"><div class="card card-body">
    <h2 class="h6">Acciones comerciales</h2>
    <form method="POST" action="/admin/empresas/estado/<?= $empresa['id'] ?>" class="row g-2 mb-2"><?= csrf_campo() ?>
      <div class="col-8"><select name="estado" class="form-select form-select-sm"><?php foreach(['activa','suspendida','vencida','cancelada'] as $est): ?><option value="<?= $est ?>" <?= $empresa['estado']===$est?'selected':'' ?>><?= $est ?></option><?php endforeach; ?></select></div>
      <div class="col-4"><button class="btn btn-sm btn-outline-primary w-100">Actualizar estado</button></div>
    </form>
    <form method="POST" action="/admin/empresas/plan/<?= $empresa['id'] ?>" class="row g-2 mb-2"><?= csrf_campo() ?>
      <div class="col-7"><select name="plan_id" class="form-select form-select-sm"><?php foreach($planes as $p): ?><option value="<?= $p['id'] ?>" <?= (int)$empresa['plan_id']===(int)$p['id']?'selected':'' ?>><?= e($p['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="col-5"><button class="btn btn-sm btn-outline-primary w-100">Cambiar plan</button></div>
      <div class="col-12"><input class="form-control form-control-sm" name="observaciones_internas" placeholder="Observación interna"></div>
    </form>
    <form method="POST" action="/admin/empresas/extender-vigencia/<?= $empresa['id'] ?>" class="row g-2"><?= csrf_campo() ?>
      <div class="col-7"><input type="number" min="1" name="dias" class="form-control form-control-sm" value="30"></div>
      <div class="col-5"><button class="btn btn-sm btn-outline-primary w-100">Extender vigencia</button></div>
    </form>
  </div></div>

  <div class="col-12"><div class="card"><div class="card-header">Administradores asociados</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Nombre</th><th>Correo</th><th>Estado</th><th>Último acceso</th></tr></thead><tbody><?php foreach($admins as $a): ?><tr><td><?= e($a['nombre']) ?></td><td><?= e($a['correo']) ?></td><td><?= e($a['estado']) ?></td><td><?= e($a['ultimo_acceso'] ?: '-') ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>
