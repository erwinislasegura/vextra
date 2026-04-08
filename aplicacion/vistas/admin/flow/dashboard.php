<h1 class="h5 mb-3">Dashboard Flow</h1>
<div class="row g-2 mb-3">
  <?php $kpis = [
    ['Clientes Flow', $resumen['clientes']],
    ['Suscripciones activas', $resumen['suscripciones_activas']],
    ['Suscripciones canceladas/vencidas', $resumen['suscripciones_canceladas']],
    ['Pagos del día', $resumen['pagos_hoy']],
    ['Ingresos mensuales estimados', '$' . number_format($resumen['ingresos_mensuales_estimados'], 0, ',', '.')],
    ['Ingresos anuales estimados', '$' . number_format($resumen['ingresos_anuales_estimados'], 0, ',', '.')],
  ]; ?>
  <?php foreach ($kpis as [$label, $valor]): ?>
    <div class="col-md-4 col-lg-3"><div class="admin-kpi"><div class="label"><?= e($label) ?></div><div class="value"><?= e((string) $valor) ?></div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <div class="col-lg-4"><div class="card"><div class="card-header">Accesos rápidos</div><div class="card-body d-grid gap-2">
    <a href="<?= e(url('/admin/flow/configuracion')) ?>" class="btn btn-sm btn-outline-primary">Configuración Flow</a>
    <a href="<?= e(url('/admin/flow/planes')) ?>" class="btn btn-sm btn-outline-primary">Planes Flow</a>
    <a href="<?= e(url('/admin/flow/suscripciones')) ?>" class="btn btn-sm btn-outline-primary">Suscripciones Flow</a>
    <a href="<?= e(url('/admin/flow/pagos')) ?>" class="btn btn-sm btn-outline-primary">Pagos Flow</a>
    <a href="<?= e(url('/admin/flow/logs')) ?>" class="btn btn-sm btn-outline-primary">Logs/Webhooks</a>
  </div></div></div>
  <div class="col-lg-8"><div class="card"><div class="card-header">Pagos recientes</div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Empresa</th><th>Orden</th><th>Monto</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($pagosRecientes as $p): ?><tr><td><?= e($p['empresa']) ?></td><td><?= e($p['commerce_order']) ?></td><td>$<?= number_format((float)$p['monto'],0,',','.') ?></td><td><?= e($p['estado_local']) ?></td><td><?= e($p['fecha_creacion']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header">Próximas renovaciones</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Empresa</th><th>Plan</th><th>Renueva</th><th>Estado</th></tr></thead><tbody><?php foreach($renovaciones as $r): ?><tr><td><?= e($r['empresa']) ?></td><td><?= e($r['plan']) ?></td><td><?= e($r['proxima_renovacion']) ?></td><td><?= e($r['estado_local']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header">Alertas de pago/suscripción</div><div class="card-body small"><?php if(empty($problemas)): ?>Sin alertas activas.<?php else: ?><ul class="mb-0 ps-3"><?php foreach($problemas as $pb): ?><li><?= e($pb['nombre_comercial']) ?> - <?= e($pb['estado_local']) ?> (<?= e($pb['flow_subscription_id']) ?>)</li><?php endforeach; ?></ul><?php endif; ?></div></div></div>
  <div class="col-lg-12"><div class="card"><div class="card-header">Empresas activas por plan</div><div class="card-body"><?php foreach($empresasPorPlan as $epp): ?><div class="d-flex justify-content-between border-bottom py-1"><span><?= e($epp['nombre']) ?></span><strong><?= e((string)$epp['total']) ?></strong></div><?php endforeach; ?></div></div></div>
</div>
