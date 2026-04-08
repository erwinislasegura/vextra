<h1 class="h5 mb-3">Reportes del administrador</h1>
<div class="row g-2 mb-3">
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Suscripciones activas</div><div class="value"><?= $reportes['suscripciones_activas'] ?></div></div></div>
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Por vencer</div><div class="value"><?= $reportes['cuentas_por_vencer'] ?></div></div></div>
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Vencidas</div><div class="value"><?= $reportes['cuentas_vencidas'] ?></div></div></div>
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Suspendidas</div><div class="value"><?= $reportes['empresas_suspendidas'] ?></div></div></div>
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Ingreso mensual</div><div class="value">$<?= number_format($reportes['ingresos_mensuales_estimados'],0,',','.') ?></div></div></div>
  <div class="col-md-2"><div class="admin-kpi"><div class="label">Ingreso anual</div><div class="value">$<?= number_format($reportes['ingresos_anuales_estimados'],0,',','.') ?></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-4"><div class="card"><div class="card-header">Empresas por plan</div><div class="card-body small"><?php foreach($reportes['empresas_por_plan'] as $r): ?><div class="d-flex justify-content-between border-bottom py-1"><span><?= e($r['nombre']) ?></span><strong><?= e((string)$r['total']) ?></strong></div><?php endforeach; ?></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-header">Planes más contratados</div><div class="card-body small"><?php foreach($reportes['planes_mas_contratados'] as $r): ?><div class="d-flex justify-content-between border-bottom py-1"><span><?= e($r['nombre']) ?></span><strong><?= e((string)$r['total']) ?></strong></div><?php endforeach; ?></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-header">Próximas renovaciones</div><div class="card-body small"><?php foreach($reportes['renovaciones_proximas'] as $r): ?><div class="border-bottom py-1"><strong><?= e($r['empresa']) ?></strong> · <?= e($r['plan']) ?> <span class="text-muted">(<?= e((string)$r['dias_restantes']) ?> días)</span></div><?php endforeach; ?></div></div></div>
</div>
