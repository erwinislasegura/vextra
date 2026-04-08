<h1 class="h5 mb-3">Administradores de empresas</h1>
<form class="row g-2 mb-3">
  <div class="col-md-5"><input class="form-control" name="q" placeholder="Buscar por empresa, nombre o correo" value="<?= e($filtros['busqueda']) ?>"></div>
  <div class="col-md-5"><select class="form-select" name="empresa_id"><option value="">Todas las empresas</option><?php foreach($empresas as $e): ?><option value="<?= $e['id'] ?>" <?= (string)$filtros['empresa_id']===(string)$e['id']?'selected':'' ?>><?= e($e['nombre_comercial']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>
<div class="card"><div class="card-header">Credenciales administrativas por empresa</div><div class="table-responsive" style="overflow: visible;"><table class="table table-sm tabla-admin mb-0"><thead><tr><th>Empresa</th><th>Administrador</th><th>Acceso</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
<?php foreach($administradores as $a): ?>
<tr>
  <td><?= e($a['empresa_nombre']) ?></td>
  <td><?= e($a['nombre']) ?></td>
  <td><div><?= e($a['correo']) ?></div><div class="small text-muted">Últ. cambio clave: <?= e($a['password_actualizado_en'] ?: 'No registrado') ?></div></td>
  <td><span class="badge text-bg-light"><?= e($a['estado']) ?></span></td>
  <td class="text-end"><div class="dropdown dropup"><button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 330px;">
    <form method="POST" action="/admin/administradores-empresa/actualizar/<?= $a['id'] ?>" class="mb-2"><?= csrf_campo() ?><input name="nombre" class="form-control form-control-sm mb-1" value="<?= e($a['nombre']) ?>"><input name="correo" class="form-control form-control-sm mb-1" value="<?= e($a['correo']) ?>"><button class="btn btn-sm btn-outline-primary w-100">Guardar datos</button></form>
    <form method="POST" action="/admin/administradores-empresa/estado/<?= $a['id'] ?>" class="d-flex gap-1 mb-2"><?= csrf_campo() ?><select class="form-select form-select-sm" name="estado"><option value="activo">activo</option><option value="inactivo">inactivo</option></select><button class="btn btn-sm btn-outline-secondary">Estado</button></form>
    <form method="POST" action="/admin/administradores-empresa/reset-password/<?= $a['id'] ?>"><?= csrf_campo() ?><input name="password_nueva" class="form-control form-control-sm mb-1" placeholder="Nueva contraseña (opcional)"><label class="small d-block mb-1"><input type="checkbox" name="generar_temporal" checked> Generar temporal si vacío</label><button class="btn btn-sm btn-outline-danger w-100">Resetear contraseña</button></form>
    <form method="POST" action="/admin/administradores-empresa/acceder/<?= $a['id'] ?>" class="mt-2" target="_blank"><?= csrf_campo() ?><button class="btn btn-sm btn-outline-success w-100">Abrir panel de la empresa</button></form>
  </div></div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
