<h1 class="h5 mb-3">Empresas</h1>
<form class="row g-2 mb-3">
  <div class="col-md-4"><input class="form-control" name="q" placeholder="Buscar por nombre, correo o fiscal" value="<?= e($filtros['busqueda']) ?>"></div>
  <div class="col-md-3"><select class="form-select" name="plan_id"><option value="">Todos los planes</option><?php foreach($planes as $p): ?><option value="<?= $p['id'] ?>" <?= (string)$filtros['plan_id']===(string)$p['id']?'selected':'' ?>><?= e($p['nombre']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select class="form-select" name="estado"><option value="">Todos los estados</option><?php foreach(['activa','suspendida','vencida','cancelada'] as $est): ?><option value="<?= $est ?>" <?= $filtros['estado']===$est?'selected':'' ?>><?= $est ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>

<?php if (!empty($confirmacionEliminacion)): ?>
  <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h2 class="h6 mb-1">Confirmación de eliminación requerida</h2>
      <p class="mb-0">La empresa <strong><?= e($confirmacionEliminacion['empresa_nombre']) ?></strong> tiene datos asociados y debe confirmarse desde el modal.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/empresas')) ?>">Cancelar</a>
  </div>

  <div class="modal fade" id="modalEliminarEmpresa" tabindex="-1" aria-labelledby="modalEliminarEmpresaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title h5 mb-0" id="modalEliminarEmpresaLabel">Eliminar empresa y datos asociados</h2>
          <a class="btn-close" href="<?= e(url('/admin/empresas')) ?>" aria-label="Cerrar"></a>
        </div>
        <div class="modal-body">
          <p class="mb-2">
            Estás por eliminar la empresa <strong id="empresaNombreEliminar"><?= e($confirmacionEliminacion['empresa_nombre']) ?></strong>.
            Esta acción eliminará también asociaciones de usuarios y demás registros relacionados.
          </p>

          <?php if (!empty($confirmacionEliminacion['resumen'])): ?>
            <div class="border rounded p-2 mb-3" style="max-height: 220px; overflow: auto;">
              <ul class="mb-0">
                <?php foreach ($confirmacionEliminacion['resumen'] as $fila): ?>
                  <li><strong><?= e($fila['tabla']) ?>:</strong> <?= (int) $fila['total'] ?> registro(s)</li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <p class="text-muted mb-3">No se detectaron datos asociados activos.</p>
          <?php endif; ?>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="confirmacionEliminarCheck">
            <label class="form-check-label" for="confirmacionEliminarCheck">
              Entiendo que esta acción no se puede deshacer.
            </label>
          </div>

          <label class="form-label" for="confirmacionEliminarNombre">
            Para continuar, escribe el nombre exacto de la empresa:
          </label>
          <input
            type="text"
            class="form-control"
            id="confirmacionEliminarNombre"
            autocomplete="off"
            placeholder="<?= e($confirmacionEliminacion['empresa_nombre']) ?>"
          >
          <div class="form-text">
            Debe coincidir exactamente con <strong><?= e($confirmacionEliminacion['empresa_nombre']) ?></strong>.
          </div>
        </div>
        <div class="modal-footer">
          <a class="btn btn-outline-secondary" href="<?= e(url('/admin/empresas')) ?>">Cancelar</a>
          <form method="POST" action="<?= e(url('/admin/empresas/eliminar/' . (int) $confirmacionEliminacion['empresa_id'])) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="forzar" value="1">
            <button class="btn btn-danger" id="btnConfirmarEliminarEmpresa" disabled>Eliminar definitivamente</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function () {
      var nombreEsperado = <?= json_encode((string) $confirmacionEliminacion['empresa_nombre'], JSON_UNESCAPED_UNICODE) ?>;
      var modalEl = document.getElementById('modalEliminarEmpresa');
      var checkEl = document.getElementById('confirmacionEliminarCheck');
      var inputEl = document.getElementById('confirmacionEliminarNombre');
      var botonEl = document.getElementById('btnConfirmarEliminarEmpresa');
      var backdropEl = null;

      function validarConfirmacion() {
        var confirmo = !!checkEl.checked;
        var coincideNombre = inputEl.value.trim() === nombreEsperado.trim();
        botonEl.disabled = !(confirmo && coincideNombre);
      }

      checkEl.addEventListener('change', validarConfirmacion);
      inputEl.addEventListener('input', validarConfirmacion);
      validarConfirmacion();

      function abrirModalFallback() {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        if (!backdropEl) {
          backdropEl = document.createElement('div');
          backdropEl.className = 'modal-backdrop fade show';
          document.body.appendChild(backdropEl);
        }
      }

      function abrirModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
          var modal = new window.bootstrap.Modal(modalEl, {backdrop: 'static', keyboard: false});
          modal.show();
          return;
        }
        abrirModalFallback();
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', abrirModal);
      } else {
        abrirModal();
      }

      window.addEventListener('load', function () {
        if (!modalEl.classList.contains('show')) {
          abrirModal();
        }
      });
    })();
  </script>
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
