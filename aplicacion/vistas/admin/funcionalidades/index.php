<div class="d-flex justify-content-between mb-3">
  <h1 class="h5 mb-0">Funcionalidades</h1>
  <a href="/admin/funcionalidades/crear" class="btn btn-primary btn-sm">Nueva funcionalidad</a>
</div>

<div class="card mb-3">
  <div class="card-header">
    <strong>Listado de funcionalidades</strong>
    <div class="small text-muted">Registros encontrados: <?= count($funcionalidades) ?></div>
  </div>
</div>

<?php
$titulos = [
    'limites' => 'Límites',
    'menu' => 'Menú (funciones)',
    'adicionales' => 'Funcionalidades adicionales',
];
?>

<?php foreach ($titulos as $grupo => $titulo): ?>
  <?php $lista = $funcionalidadesAgrupadas[$grupo] ?? []; ?>
  <?php if ($lista === []): ?>
    <?php continue; ?>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-header bg-light fw-semibold"><?= e($titulo) ?></div>
    <div class="table-responsive" style="overflow: visible;">
      <table class="table table-sm table-hover mb-0 tabla-admin align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $f): ?>
            <tr>
              <td>
                <?= e($f['nombre']) ?>
                <?php if (in_array($f['codigo_interno'], $codigosNuevos ?? [], true)): ?>
                  <span class="badge text-bg-success ms-1">Nueva</span>
                <?php endif; ?>
              </td>
              <td><code><?= e($f['codigo_interno']) ?></code></td>
              <td class="small text-muted">
                <?= e($f['descripcion'] ?: 'Sin descripción') ?>
                <?php if (!empty($dependencias[$f['codigo_interno']])): ?>
                  <div class="mt-1 text-warning-emphasis">
                    Depende de: <?= e(implode(', ', $dependencias[$f['codigo_interno']])) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= e($f['tipo_valor']) ?></td>
              <td><?= e($f['estado']) ?></td>
              <td class="text-end">
                <div class="dropdown dropup">
                  <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/admin/funcionalidades/editar/<?= $f['id'] ?>">Editar</a></li>
                    <li><a class="dropdown-item" href="#">Ver</a></li>
                  </ul>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
