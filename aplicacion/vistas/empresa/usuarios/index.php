<h1 class="h4 mb-3">Usuarios y permisos</h1>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Gestión de acceso de tu equipo</div>
  <ul class="mb-0 small ps-3">
    <li>Usa correos corporativos para mantener trazabilidad y seguridad.</li>
    <li>Define cargo y rol para facilitar aprobaciones, seguimiento y soporte interno.</li>
  </ul>
</div>

<div class="card shadow-sm border-0 mb-3">
  <div class="card-header bg-white border-0 pt-3">
    <strong>Crear usuario de empresa</strong>
  </div>
  <div class="card-body pt-0">
    <form method="POST" action="<?= e(url('/app/usuarios')) ?>" class="row g-3">
      <?= csrf_campo() ?>
      <div class="col-md-4">
        <label class="form-label">Nombre completo</label>
        <input name="nombre" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Correo electrónico</label>
        <input name="correo" type="email" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Contraseña temporal</label>
        <input name="password" type="text" class="form-control" value="123456" minlength="6" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Cargo / puesto</label>
        <input name="cargo" class="form-control" placeholder="Ej. Ejecutivo comercial">
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" placeholder="Ej. +1 555 010 2244">
      </div>
      <div class="col-md-2">
        <label class="form-label">Rol</label>
        <select name="rol_id" class="form-select" required>
          <?php foreach($roles as $rol): ?>
            <option value="<?= (int)$rol['id'] ?>"><?= e($rol['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select" required>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Información del usuario</label>
        <textarea name="biografia" class="form-control" rows="2" placeholder="Notas internas, alcance comercial, responsabilidades, etc."></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary btn-sm">Guardar usuario</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong>Listado de usuarios</strong>
      <div class="small text-muted">Registros encontrados: <?= count($usuarios) ?></div>
    </div>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-sm table-hover mb-0 tabla-admin">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Cargo</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
        <?php else: foreach($usuarios as $u): ?>
          <tr>
            <td><?= e($u['nombre']) ?></td>
            <td><?= e($u['cargo'] ?? '—') ?></td>
            <td><?= e($u['correo']) ?></td>
            <td><?= e($u['rol']) ?></td>
            <td><span class="badge <?= $u['estado']==='activo' ? 'badge-estado-activo':'badge-estado-inactivo' ?>"><?= e($u['estado']) ?></span></td>
            <td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= e(url('/app/usuarios/ver/' . $u['id'])) ?>">Ver</a></li><li><a class="dropdown-item" href="<?= e(url('/app/usuarios/editar/' . $u['id'])) ?>">Editar</a></li><li><a class="dropdown-item" href="<?= e(url('/app/usuarios/editar/' . $u['id'])) ?>">Restablecer contraseña</a></li><li><a class="dropdown-item text-danger" href="<?= e(url('/app/usuarios/editar/' . $u['id'])) ?>">Desactivar</a></li></ul></div></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
