<?php
$totalEmpresas = max((int) ($resumen['empresas_total'] ?? 0), 1);
$empresasActivas = (int) ($resumen['empresas_activas'] ?? 0);
$empresasVencidas = (int) ($resumen['empresas_vencidas'] ?? 0);
$empresasPorVencer = (int) ($resumen['empresas_por_vencer'] ?? 0);
$pctActivas = min(100, (int) round(($empresasActivas / $totalEmpresas) * 100));
$pctVencidas = min(100, (int) round(($empresasVencidas / $totalEmpresas) * 100));
$pctPorVencer = min(100, (int) round(($empresasPorVencer / $totalEmpresas) * 100));
?>

<section class="admin-dashboard">
  <div class="admin-dashboard__hero card mb-3">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <h1 class="h4 mb-1">Panel administrativo</h1>
        <p class="text-muted mb-0">Vista general del estado comercial, suscripciones y operación diaria de la plataforma.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(url('/admin/empresas')) ?>" class="btn btn-primary"><i class="bi bi-buildings me-1"></i> Gestionar empresas</a>
        <a href="<?= e(url('/admin/suscripciones')) ?>" class="btn btn-outline-primary"><i class="bi bi-card-checklist me-1"></i> Revisar suscripciones</a>
      </div>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <?php $kpis = [
      ['Total empresas', $resumen['empresas_total'], 'bi-buildings'],
      ['Empresas activas', $resumen['empresas_activas'], 'bi-patch-check'],
      ['Vencidas', $resumen['empresas_vencidas'], 'bi-exclamation-triangle'],
      ['Por vencer (10 días)', $resumen['empresas_por_vencer'], 'bi-hourglass-split'],
      ['Planes activos', $resumen['planes_activos'], 'bi-award'],
      ['Usuarios empresas', $resumen['total_usuarios_empresas'], 'bi-people'],
      ['Nuevas empresas (7 días)', $resumen['nuevas_empresas_7_dias'], 'bi-graph-up-arrow'],
      ['Renovaciones hoy', $resumen['renovaciones_hoy'], 'bi-arrow-repeat'],
      ['MRR estimado', '$' . number_format($resumen['ingresos_mensuales_estimados'], 0, ',', '.'), 'bi-currency-dollar'],
      ['MRR en riesgo', '$' . number_format($resumen['mrr_en_riesgo'], 0, ',', '.'), 'bi-shield-exclamation'],
      ['Flow pagos hoy', $resumen['flow_pagos_hoy'] ?? 0, 'bi-wallet2'],
      ['Flow suscripciones activas', $resumen['flow_suscripciones_activas'] ?? 0, 'bi-credit-card-2-front'],
    ]; ?>
    <?php foreach ($kpis as [$label, $valor, $icono]): ?>
      <div class="col-md-4 col-lg-3">
        <div class="admin-kpi">
          <div class="label"><i class="bi <?= e($icono) ?> me-1"></i><?= e($label) ?></div>
          <div class="value"><?= e((string) $valor) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-8">
      <div class="card border-danger-subtle h-100">
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
        <div class="card-header">Salud comercial</div>
        <div class="card-body small d-grid gap-2">
          <div class="admin-progress">
            <div class="d-flex justify-content-between"><span>Empresas activas</span><strong><?= e((string) $pctActivas) ?>%</strong></div>
            <div class="progress"><div class="progress-bar bg-success" style="width: <?= e((string) $pctActivas) ?>%"></div></div>
          </div>
          <div class="admin-progress">
            <div class="d-flex justify-content-between"><span>Empresas por vencer</span><strong><?= e((string) $pctPorVencer) ?>%</strong></div>
            <div class="progress"><div class="progress-bar bg-warning" style="width: <?= e((string) $pctPorVencer) ?>%"></div></div>
          </div>
          <div class="admin-progress">
            <div class="d-flex justify-content-between"><span>Empresas vencidas</span><strong><?= e((string) $pctVencidas) ?>%</strong></div>
            <div class="progress"><div class="progress-bar bg-danger" style="width: <?= e((string) $pctVencidas) ?>%"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Acciones rápidas</div>
    <div class="card-body admin-quick-actions">
      <a href="<?= e(url('/admin/planes')) ?>" class="btn btn-sm btn-outline-primary">Gestionar planes</a>
      <a href="<?= e(url('/admin/funcionalidades')) ?>" class="btn btn-sm btn-outline-primary">Matriz funcionalidades</a>
      <a href="<?= e(url('/admin/empresas')) ?>" class="btn btn-sm btn-outline-primary">Gestionar empresas</a>
      <a href="<?= e(url('/admin/administradores-empresa')) ?>" class="btn btn-sm btn-outline-primary">Credenciales empresas</a>
      <a href="<?= e(url('/admin/flow')) ?>" class="btn btn-sm btn-outline-primary">Dashboard Flow</a>
      <a href="<?= e(url('/admin/reportes')) ?>" class="btn btn-sm btn-outline-primary">Abrir reportes</a>
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
</section>
