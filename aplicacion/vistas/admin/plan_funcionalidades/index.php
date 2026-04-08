<h1 class="h5 mb-3">Funcionalidades del plan: <?= e($plan['nombre'] ?? 'Plan') ?></h1>
<form method="POST">
  <?= csrf_campo() ?>

  <?php
  $titulos = [
      'limites' => 'Límites',
      'menu' => 'Menú (funciones)',
      'adicionales' => 'Funcionalidades adicionales',
  ];
  ?>

  <?php foreach ($titulos as $grupo => $titulo): ?>
    <?php $funcionalidades = $funcionalidadesAgrupadas[$grupo] ?? []; ?>
    <?php if ($funcionalidades === []): ?>
      <?php continue; ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-light fw-semibold"><?= e($titulo) ?></div>
      <div class="table-responsive">
        <table class="table table-sm tabla-admin align-middle mb-0">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Código</th>
              <th>Descripción para administrador</th>
              <th>Activo</th>
              <th>Límite</th>
              <th>Ilimitado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($funcionalidades as $f): ?>
              <?php $a = $mapa[$f['id']] ?? null; ?>
              <tr>
                <td class="fw-semibold">
                  <?= e($f['nombre']) ?>
                  <?php if (in_array($f['codigo_interno'], $codigosNuevos ?? [], true)): ?>
                    <span class="badge text-bg-success ms-1">Nueva</span>
                  <?php endif; ?>
                </td>
                <td><code><?= e($f['codigo_interno']) ?></code></td>
                <td class="small text-muted">
                  <?= e($f['descripcion'] ?: 'Sin descripción comercial') ?>
                  <?php if (!empty($dependencias[$f['codigo_interno']])): ?>
                    <div class="mt-1 text-warning-emphasis">
                      Depende de: <?= e(implode(', ', $dependencias[$f['codigo_interno']])) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (($f['codigo_interno'] ?? '') === 'maximo_usuarios'): ?>
                    <div class="mt-1 text-info-emphasis">
                      Sincronizado con el campo "Máximo usuarios" de crear/editar plan.
                    </div>
                  <?php endif; ?>
                </td>
                <td><input type="checkbox" name="funcionalidades[<?= $f['id'] ?>][activo]" <?= ($a['activo'] ?? 0) ? 'checked' : '' ?>></td>
                <td><input class="form-control form-control-sm" type="number" min="0" name="funcionalidades[<?= $f['id'] ?>][valor_numerico]" value="<?= e((string) ($a['valor_numerico'] ?? 0)) ?>"></td>
                <td><input type="checkbox" name="funcionalidades[<?= $f['id'] ?>][es_ilimitado]" <?= ($a['es_ilimitado'] ?? 0) ? 'checked' : '' ?>></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <button class="btn btn-primary btn-sm">Guardar asignaciones</button>
</form>
