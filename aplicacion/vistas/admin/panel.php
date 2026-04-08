<h1 class="h5 mb-3">Dashboard administrativo</h1>

<div class="row g-2 mb-3">
  <?php $kpis = [
    ['Total empresas', $resumen['empresas_total']],
    ['Empresas activas', $resumen['empresas_activas']],
    ['Vencidas', $resumen['empresas_vencidas']],
    ['Por vencer (10 días)', $resumen['empresas_por_vencer']],
    ['Planes activos', $resumen['planes_activos']],
    ['Usuarios empresas', $resumen['total_usuarios_empresas']],
    ['Nuevas empresas (7 días)', $resumen['nuevas_empresas_7_dias']],
    ['Renovaciones hoy', $resumen['renovaciones_hoy']],
    ['MRR estimado', '$' . number_format($resumen['ingresos_mensuales_estimados'], 0, ',', '.')],
    ['MRR en riesgo', '$' . number_format($resumen['mrr_en_riesgo'], 0, ',', '.')],
    ['Flow pagos hoy', $resumen['flow_pagos_hoy'] ?? 0],
    ['Flow suscripciones activas', $resumen['flow_suscripciones_activas'] ?? 0],
  ]; ?>
  <?php foreach ($kpis as [$label, $valor]): ?>
    <div class="col-md-4 col-lg-3"><div class="admin-kpi"><div class="label"><?= e($label) ?></div><div class="value"><?= e((string) $valor) ?></div></div></div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-8">
    <div class="card border-danger-subtle">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Prioridades operativas</span>
        <a href="<?= e(url('/admin/suscripciones')) ?>" class="btn btn-sm btn-outline-danger">Gestionar suscripciones</a>
      </div>
      <div class="card-body small">
        <?php if (empty($alertas)): ?>
          <div class="text-success">No hay alertas críticas pendientes hoy.</div>
        <?php else: ?>
          <ul class="mb-0 ps-3 d-grid gap-1">
            <?php foreach ($alertas as $a): ?><li><?= e($a) ?></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Acciones rápidas</div>
      <div class="card-body d-grid gap-2">
        <a href="<?= e(url('/admin/planes')) ?>" class="btn btn-sm btn-outline-primary">Gestionar planes</a>
        <a href="<?= e(url('/admin/funcionalidades')) ?>" class="btn btn-sm btn-outline-primary">Matriz de funcionalidades</a>
        <a href="<?= e(url('/admin/empresas')) ?>" class="btn btn-sm btn-outline-primary">Gestionar empresas</a>
        <a href="<?= e(url('/admin/administradores-empresa')) ?>" class="btn btn-sm btn-outline-primary">Credenciales empresas</a>
        <a href="<?= e(url('/admin/flow')) ?>" class="btn btn-sm btn-outline-primary">Dashboard Flow</a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card"><div class="card-header">Últimas empresas registradas</div><div class="table-responsive"><table class="table table-sm tabla-admin mb-0"><thead><tr><th>Empresa</th><th>Correo</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($ultimasEmpresas as $e): ?><tr><td><?= e($e['nombre_comercial']) ?></td><td><?= e($e['correo']) ?></td><td><?= e($e['estado']) ?></td><td><?= e(substr($e['fecha_creacion'],0,10)) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-header">Suscripciones recientes</div><div class="table-responsive"><table class="table table-sm tabla-admin mb-0"><thead><tr><th>Empresa</th><th>Plan</th><th>Estado</th><th>Movimiento</th></tr></thead><tbody><?php foreach($ultimasSuscripciones as $s): ?><tr><td><?= e($s['empresa']) ?></td><td><?= e($s['plan']) ?></td><td><?= e($s['estado']) ?></td><td><?= e(substr($s['fecha_movimiento'],0,16)) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-header">Empresas por plan</div><div class="card-body small"><?php foreach($empresasPorPlan as $r): ?><div class="d-flex justify-content-between border-bottom py-1"><span><?= e($r['nombre']) ?></span><strong><?= e((string)$r['total']) ?></strong></div><?php endforeach; ?></div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-header">Próximos vencimientos</div><div class="table-responsive"><table class="table table-sm tabla-admin mb-0"><thead><tr><th>Empresa</th><th>Plan</th><th>Vence</th><th>Días</th></tr></thead><tbody><?php foreach($proximosVencimientos as $v): ?><tr><td><?= e($v['empresa']) ?></td><td><?= e($v['plan']) ?></td><td><?= e($v['fecha_vencimiento']) ?></td><td><?= e((string)$v['dias_restantes']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  </div>
</div>
