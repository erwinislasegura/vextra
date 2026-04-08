<div class="d-flex justify-content-between mb-3"><h1 class="h5 mb-0">Planes</h1><a href="/admin/planes/crear" class="btn btn-primary btn-sm">Nuevo plan</a></div>
<div class="card"><div class="card-header"><strong>Listado de planes</strong></div>
<div class="table-responsive" style="overflow: visible;"><table class="table table-sm table-hover mb-0 tabla-admin"><thead><tr><th>Plan</th><th>Mensual</th><th>Anual</th><th>Usuarios</th><th>Landing</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
<?php foreach($planes as $plan): ?>
<tr>
<td><div class="fw-semibold"><?= e($plan['nombre']) ?></div><div class="small text-muted"><?= e($plan['slug']) ?></div></td>
<td>$<?= number_format((float)$plan['precio_mensual'],0,',','.') ?></td>
<td>$<?= number_format((float)$plan['precio_anual'],0,',','.') ?></td>
<td><?= ($plan['usuarios_ilimitados'] ?? 0) ? 'Ilimitado' : (int)($plan['maximo_usuarios'] ?? 0) ?></td>
<td><?= ($plan['visible'] ?? 0) ? 'Sí' : 'No' ?> <?= ($plan['recomendado'] ?? 0) ? ' / Recomendado' : '' ?></td>
<td><span class="badge text-bg-light"><?= e($plan['estado']) ?></span></td>
<td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="/admin/planes/editar/<?= $plan['id'] ?>">Editar</a></li><li><a class="dropdown-item" href="/admin/plan-funcionalidades/<?= $plan['id'] ?>">Funciones del plan</a></li><li><form method="POST" action="/admin/planes/estado/<?= $plan['id'] ?>"><?= csrf_campo() ?><button class="dropdown-item"><?= $plan['estado'] === 'activo' ? 'Deshabilitar' : 'Habilitar' ?></button></form></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="/admin/planes/eliminar/<?= $plan['id'] ?>" onsubmit="return confirm('¿Eliminar este plan? Esta acción es irreversible.');"><?= csrf_campo() ?><button class="dropdown-item text-danger">Eliminar</button></form></li></ul></div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
