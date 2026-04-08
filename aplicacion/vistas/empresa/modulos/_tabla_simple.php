<?php
function render_modulo_simple(string $titulo, string $url, array $columnas, array $registros, string $htmlFormulario, string $buscar, string $accionesListado = '', ?string $tituloListado = null): void {
    $tituloListado = $tituloListado ?? ('Listado de ' . mb_strtolower($titulo, 'UTF-8') . ' registrados');
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0"><?= e($titulo) ?></h1></div>
<div class="card mb-3"><div class="card-header">Nuevo registro</div><div class="card-body"><form method="POST" action="<?= e(url($url)) ?>" class="row g-2"><?= csrf_campo() ?><?= $htmlFormulario ?><div class="col-12"><button class="btn btn-primary btn-sm">Guardar</button></div></form></div></div>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong><?= e($tituloListado) ?></strong>
            <div class="small text-muted">Registros encontrados: <?= count($registros) ?></div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input name="q" class="form-control form-control-sm" value="<?= e($buscar) ?>" placeholder="Buscar">
                <button class="btn btn-outline-secondary btn-sm">Buscar</button>
                <?php if ($buscar !== ''): ?>
                    <a class="btn btn-outline-dark btn-sm" href="<?= e(url($url)) ?>">Limpiar</a>
                <?php endif; ?>
            </form>
            <?php if ($accionesListado !== ''): ?><div><?= $accionesListado ?></div><?php endif; ?>
        </div>
    </div>
    <div class="table-responsive" style="overflow: visible;"><table class="table table-sm table-hover mb-0 tabla-admin"><thead class="table-light"><tr><?php foreach($columnas as $col): ?><th><?= e(ucwords(str_replace('_', ' ', $col))) ?></th><?php endforeach; ?><th class="text-end">Acciones</th></tr></thead><tbody><?php if (empty($registros)): ?><tr><td colspan="<?= count($columnas) + 1 ?>" class="text-center text-muted py-4">No hay registros para mostrar.</td></tr><?php else: foreach($registros as $fila): ?><tr><?php foreach($columnas as $col): ?><td><?= e((string)($fila[$col] ?? '')) ?></td><?php endforeach; ?><td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= e(url($url . '/ver/' . $fila['id'])) ?>">Ver</a></li><li><a class="dropdown-item" href="<?= e(url($url . '/editar/' . $fila['id'])) ?>">Editar</a></li><li><form method="POST" action="<?= e(url($url . '/eliminar/' . $fila['id'])) ?>" onsubmit="return confirm('¿Eliminar registro?')"><?= csrf_campo() ?><button type="submit" class="dropdown-item text-danger">Eliminar</button></form></li></ul></div></td></tr><?php endforeach; endif; ?></tbody></table></div>
</div>
<?php }
