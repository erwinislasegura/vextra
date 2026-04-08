<h1 class="h5 mb-3">Planes SaaS conectados con Flow</h1>
<div class="card"><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Plan</th><th>Mensual</th><th>Anual</th><th>Flow mensual</th><th>Flow anual</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($planes as $p): ?>
<tr>
  <td><strong><?= e($p['nombre']) ?></strong><div class="small text-muted"><?= e($p['slug']) ?></div></td>
  <td>$<?= number_format((float)$p['precio_mensual'],0,',','.') ?></td>
  <td>$<?= number_format((float)$p['precio_anual'],0,',','.') ?></td>
  <td>
    <div><?= e($p['flow_plan_id_mensual'] ?? '-') ?></div>
    <div class="small text-muted">Prueba: <?= (int) ($p['flow_dias_prueba'] ?? 0) ?> días / Cobro: <?= (int) ($p['flow_dias_cobro'] ?? 3) ?> días</div>
  </td>
  <td>
    <div><?= e($p['flow_plan_id_anual'] ?? '-') ?></div>
    <div class="small text-muted">Prueba: <?= (int) ($p['flow_dias_prueba'] ?? 0) ?> días / Cobro: <?= (int) ($p['flow_dias_cobro'] ?? 3) ?> días</div>
  </td>
  <td class="d-grid gap-1">
    <form method="POST" action="<?= e(url('/admin/flow/planes/sincronizar/' . (int)$p['id'] . '/mensual')) ?>" class="d-flex gap-1 align-items-center">
      <?= csrf_campo() ?>
      <button class="btn btn-sm btn-outline-primary">Sincronizar mensual</button>
    </form>
    <form method="POST" action="<?= e(url('/admin/flow/planes/sincronizar/' . (int)$p['id'] . '/anual')) ?>" class="d-flex gap-1 align-items-center">
      <?= csrf_campo() ?>
      <button class="btn btn-sm btn-outline-secondary">Sincronizar anual</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
