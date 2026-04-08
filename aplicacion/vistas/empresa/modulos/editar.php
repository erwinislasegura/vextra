<h1 class="h4 mb-3">Editar registro - <?= e($titulo) ?></h1>
<div class="card"><div class="card-body"><form method="POST" class="row g-2"><?= csrf_campo() ?>
<?php foreach($registro as $k => $v): if (in_array($k, ['id','empresa_id','fecha_creacion'], true)) { continue; } ?>
<div class="col-md-6"><label class="form-label"><?= e(ucwords(str_replace('_',' ',$k))) ?></label><input name="<?= e($k) ?>" class="form-control" value="<?= e((string)$v) ?>"></div>
<?php endforeach; ?>
<div class="col-12"><button class="btn btn-primary btn-sm">Guardar cambios</button> <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/' . $modulo)) ?>">Cancelar</a></div>
</form></div></div>
