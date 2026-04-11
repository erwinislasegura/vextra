<h1 class="h5 mb-3">Empresas</h1>
<form class="row g-2 mb-3">
  <div class="col-md-4"><input class="form-control" name="q" placeholder="Buscar por nombre, correo o fiscal" value="<?= e($filtros['busqueda']) ?>"></div>
  <div class="col-md-3"><select class="form-select" name="plan_id"><option value="">Todos los planes</option><?php foreach($planes as $p): ?><option value="<?= $p['id'] ?>" <?= (string)$filtros['plan_id']===(string)$p['id']?'selected':'' ?>><?= e($p['nombre']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select class="form-select" name="estado"><option value="">Todos los estados</option><?php foreach(['activa','suspendida','vencida','cancelada'] as $est): ?><option value="<?= $est ?>" <?= $filtros['estado']===$est?'selected':'' ?>><?= $est ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>

<?php if (!empty($confirmacionEliminacion)): ?>
  <div class="alert alert-warning">
    <h2 class="h6 mb-2">Confirmación de eliminación requerida</h2>
    <p class="mb-2">
      La empresa <strong><?= e($confirmacionEliminacion['empresa_nombre']) ?></strong> tiene datos asociados.
      Si continúas, también se eliminarán estos registros.
    </p>
    <?php if (!empty($confirmacionEliminacion['resumen'])): ?>
      <ul class="mb-3">
        <?php foreach ($confirmacionEliminacion['resumen'] as $fila): ?>
          <li><strong><?= e($fila['tabla']) ?>:</strong> <?= (int) $fila['total'] ?> registro(s)</li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="mb-3">No se detectaron datos asociados activos.</p>
    <?php endif; ?>
    <div class="d-flex gap-2">
      <form method="POST" action="<?= e(url('/admin/empresas/eliminar/' . (int) $confirmacionEliminacion['empresa_id'])) ?>">
        <?= csrf_campo() ?>
        <input type="hidden" name="forzar" value="1">
        <button class="btn btn-danger btn-sm" onclick="return confirm('Se eliminará la empresa y todos sus datos asociados. ¿Deseas continuar?')">Eliminar empresa y datos asociados</button>
      </form>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/empresas')) ?>">Cancelar</a>
    </div>
  </div>
<?php endif; ?>

<div class="card"><div class="card-header">Listado de empresas</div><div class="table-responsive" style="overflow: visible;"><table class="table table-sm table-hover mb-0 tabla-admin"><thead><tr><th>Empresa</th><th>Plan</th><th>Días plan</th><th>Estado</th><th>Usuarios</th><th>Último acceso</th><th class="text-end">Acciones</th></tr></thead><tbody>
<?php foreach($empresas as $e): ?>
<tr>
  <td><div class="fw-semibold"><?= e($e['nombre_comercial']) ?></div><div class="small text-muted"><?= e($e['correo']) ?></div></td>
  <td><?= e($e['plan_nombre'] ?? 'Sin plan') ?></td>
  <td>
    <?php
      $diasRestantesPlan = isset($e['dias_restantes_plan']) && $e['dias_restantes_plan'] !== null
        ? (int) $e['dias_restantes_plan']
        : null;
      $esPeriodoPrueba = ($e['suscripcion_estado'] ?? '') === 'pendiente'
        && $diasRestantesPlan !== null
        && $diasRestantesPlan >= 0;
    ?>
    <?php if (!isset($e['dias_restantes_plan']) || $e['dias_restantes_plan'] === null): ?>
      <span class="text-muted">Sin vigencia</span>
    <?php elseif ($esPeriodoPrueba): ?>
      <span class="badge text-bg-info">
        Periodo de prueba: <?= $diasRestantesPlan ?> día(s)
      </span>
    <?php elseif ((int) $e['dias_restantes_plan'] < 0): ?>
      <span class="badge text-bg-danger">Vencido hace <?= abs((int) $e['dias_restantes_plan']) ?> día(s)</span>
    <?php else: ?>
      <span class="badge <?= (int) $e['dias_restantes_plan'] <= 7 ? 'text-bg-warning' : 'text-bg-success' ?>">
        <?= (int) $e['dias_restantes_plan'] ?> día(s)
      </span>
    <?php endif; ?>
  </td>
  <td><span class="badge text-bg-light"><?= e($e['estado']) ?></span></td>
  <td><?= (int) $e['total_usuarios'] ?></td>
  <td><?= e($e['ultimo_acceso_admin'] ?: '-') ?></td>
  <td class="text-end"><div class="dropdown dropup"><button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="/admin/empresas/ver/<?= $e['id'] ?>">Ver</a></li><li><a class="dropdown-item" href="/admin/administradores-empresa?q=<?= urlencode($e['correo']) ?>">Resetear contraseña</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="<?= e(url('/admin/empresas?confirmar_eliminar=' . (int) $e['id'])) ?>">Eliminar empresa</a></li></ul></div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
