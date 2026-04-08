<?php
$edicionActiva = isset($proveedorEdicion) && is_array($proveedorEdicion);
$tituloFormulario = $edicionActiva ? 'Editar proveedor' : 'Nuevo proveedor';
$descripcionFormulario = $edicionActiva
    ? 'Actualiza los datos del proveedor seleccionado usando el mismo formulario de creación.'
    : 'Completa la información principal para registrarlo rápido.';
$botonFormulario = $edicionActiva ? 'Actualizar proveedor' : 'Guardar proveedor';
$filtrosListado = $filtros ?? [];
$queryFiltros = http_build_query([
    'q' => (string) ($filtrosListado['q'] ?? ''),
    'estado' => (string) ($filtrosListado['estado'] ?? ''),
]);
?>

<section class="modulo-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h4 mb-0">Proveedores</h1>
  <a class="btn btn-outline-success btn-sm" href="<?= e(url('/app/inventario/proveedores/exportar/excel')) ?>">
    <i class="bi bi-file-earmark-excel"></i>
    <span>Exportar Excel</span>
  </a>
</section>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Información clave para gestionar proveedores</div>
  <ul class="mb-0 small ps-3">
    <li>Registra razón social y datos fiscales para mantener trazabilidad documental.</li>
    <li>Mantén contacto, correo y teléfono actualizados para recepciones y reposición.</li>
    <li>Usa observaciones para indicar condiciones de entrega o pago.</li>
  </ul>
</div>

<?php if (!empty($proveedorVer)): ?>
  <div class="card mb-3 border-info-subtle">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Detalle de proveedor</strong>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/inventario/proveedores?editar=' . (int) $proveedorVer['id'])) ?>">Editar</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/inventario/proveedores')) ?>">Cerrar</a>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3 small">
        <div class="col-md-4"><div class="text-muted">Razón social</div><div class="fw-semibold"><?= e($proveedorVer['nombre'] ?? '') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Fiscal</div><div><?= e($proveedorVer['identificador_fiscal'] ?? '-') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Contacto</div><div><?= e($proveedorVer['contacto'] ?? '-') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Correo</div><div><?= e($proveedorVer['correo'] ?? '-') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Teléfono</div><div><?= e($proveedorVer['telefono'] ?? '-') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Ciudad</div><div><?= e($proveedorVer['ciudad'] ?? '-') ?></div></div>
        <div class="col-md-8"><div class="text-muted">Dirección</div><div><?= e($proveedorVer['direccion'] ?? '-') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Estado</div><div><?= e($proveedorVer['estado'] ?? 'activo') ?></div></div>
        <div class="col-12"><div class="text-muted">Observación</div><div><?= e($proveedorVer['observacion'] ?? '-') ?></div></div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= e($tituloFormulario) ?></span>
    <span class="small text-muted"><?= e($descripcionFormulario) ?></span>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= e(url('/app/inventario/proveedores')) ?>" class="row g-3">
      <?= csrf_campo() ?>
      <?php if ($edicionActiva): ?>
        <input type="hidden" name="proveedor_id" value="<?= (int) $proveedorEdicion['id'] ?>">
      <?php endif; ?>

      <div class="col-md-4">
        <label class="form-label">Razón social</label>
        <input name="razon_social" class="form-control" required maxlength="180" placeholder="Ej: Proveedora Industrial SpA" value="<?= e($proveedorEdicion['nombre'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre comercial</label>
        <input name="nombre_comercial" class="form-control" maxlength="180" placeholder="Ej: Proveedora Industrial">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre de contacto</label>
        <input name="nombre_contacto" class="form-control" maxlength="140" placeholder="Ej: Laura Pérez" value="<?= e($proveedorEdicion['contacto'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">RUT/NIT</label>
        <input name="identificador_fiscal" class="form-control" maxlength="80" placeholder="Ej: 76.123.456-7" value="<?= e($proveedorEdicion['identificador_fiscal'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="160" placeholder="proveedor@empresa.com" value="<?= e($proveedorEdicion['correo'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" maxlength="80" placeholder="+56 9 1234 5678" value="<?= e($proveedorEdicion['telefono'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Ciudad</label>
        <input name="ciudad" class="form-control" maxlength="120" placeholder="Santiago" value="<?= e($proveedorEdicion['ciudad'] ?? '') ?>">
      </div>

      <div class="col-md-9">
        <label class="form-label">Dirección</label>
        <input name="direccion" class="form-control" maxlength="200" placeholder="Calle, número, comuna" value="<?= e($proveedorEdicion['direccion'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <option value="activo" <?= ($proveedorEdicion['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= ($proveedorEdicion['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Observación</label>
        <textarea name="observacion" class="form-control" rows="2" placeholder="Notas internas del proveedor"><?= e($proveedorEdicion['observacion'] ?? '') ?></textarea>
      </div>

      <div class="col-12 d-flex justify-content-end gap-2">
        <?php if ($edicionActiva): ?>
          <a class="btn btn-outline-secondary btn-sm px-4" href="<?= e(url('/app/inventario/proveedores')) ?>">Cancelar</a>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm px-4"><?= e($botonFormulario) ?></button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <strong>Listado de proveedores</strong>
      <div class="small text-muted">Registros encontrados: <?= count($proveedores) ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <a href="<?= e(url('/app/inventario/proveedores/exportar/excel?' . $queryFiltros)) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" value="<?= e((string) ($filtrosListado['q'] ?? '')) ?>" class="form-control form-control-sm" placeholder="Buscar proveedor..." style="min-width: 220px;">
        <select name="estado" class="form-select form-select-sm" style="min-width: 170px;">
          <option value="">Todos</option>
          <option value="activo" <?= (($filtrosListado['estado'] ?? '') === 'activo') ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= (($filtrosListado['estado'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
      </form>
    </div>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-hover align-middle table-sm mb-0 tabla-admin">
      <thead class="table-light">
        <tr>
          <th>Razón social</th>
          <th>Fiscal</th>
          <th>Contacto</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Ciudad</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($proveedores)): ?>
        <tr><td colspan="8" class="text-center py-4 text-muted">No hay proveedores registrados.</td></tr>
      <?php else: foreach ($proveedores as $p): ?>
        <tr>
          <td><?= e($p['nombre']) ?></td>
          <td><?= e($p['identificador_fiscal'] ?? '') ?></td>
          <td><?= e($p['contacto'] ?? '') ?></td>
          <td><?= e($p['correo'] ?? '') ?></td>
          <td><?= e($p['telefono'] ?? '') ?></td>
          <td><?= e($p['ciudad'] ?? '') ?></td>
          <td><span class="badge <?= ($p['estado'] ?? 'activo') === 'activo' ? 'badge-estado-activo' : 'badge-estado-inactivo' ?>"><?= e($p['estado'] ?? 'activo') ?></span></td>
          <td class="text-end">
            <div class="dropdown dropup">
              <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= e(url('/app/inventario/proveedores?ver=' . (int) $p['id'])) ?>">Ver</a></li>
                <li><a class="dropdown-item" href="<?= e(url('/app/inventario/proveedores?editar=' . (int) $p['id'])) ?>">Editar</a></li>
                <li>
                  <form method="POST" action="<?= e(url('/app/inventario/proveedores/eliminar/' . (int) $p['id'])) ?>" onsubmit="return confirm('¿Eliminar este proveedor? Esta acción no se puede deshacer.');">
                    <?= csrf_campo() ?>
                    <button class="dropdown-item text-danger" type="submit">Eliminar</button>
                  </form>
                </li>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
