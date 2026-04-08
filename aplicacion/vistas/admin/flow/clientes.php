<h1 class="h5 mb-3">Clientes Flow</h1>
<div class="card p-3 mb-3"><form method="POST" action="<?= e(url('/admin/flow/clientes/crear')) ?>" class="row g-2 align-items-end"><?= csrf_campo() ?>
  <div class="col-md-8"><label class="form-label">Empresa</label><select class="form-select" name="empresa_id" required><?php foreach($empresas as $e): ?><option value="<?= (int)$e['id'] ?>"><?= e($e['nombre_comercial']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><button class="btn btn-primary btn-sm w-100">Crear cliente Flow</button></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Empresa</th><th>Flow customer</th><th>Correo</th><th>Estado</th><th>Registro tarjeta</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($clientes as $c): ?><tr><td><?= e($c['empresa']) ?></td><td><?= e($c['flow_customer_id']) ?></td><td><?= e($c['correo']) ?></td><td><?= e($c['estado_local']) ?></td><td><?= (int)$c['medio_pago_registrado']===1?'Registrado':'Pendiente' ?></td><td><form method="POST" action="<?= e(url('/admin/flow/clientes/registro/' . (int)$c['empresa_id'])) ?>"><?= csrf_campo() ?><button class="btn btn-sm btn-outline-primary">Iniciar/Reintentar registro</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
