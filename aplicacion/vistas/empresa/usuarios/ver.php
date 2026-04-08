<h1 class="h4 mb-3">Detalle del usuario</h1>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <dl class="row mb-0 g-2">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">#<?= (int) $usuario['id'] ?></dd>

            <dt class="col-sm-3">Nombre</dt>
            <dd class="col-sm-9"><?= e($usuario['nombre']) ?></dd>

            <dt class="col-sm-3">Correo</dt>
            <dd class="col-sm-9"><?= e($usuario['correo']) ?></dd>

            <dt class="col-sm-3">Cargo</dt>
            <dd class="col-sm-9"><?= e($usuario['cargo'] ?? 'No especificado') ?></dd>

            <dt class="col-sm-3">Teléfono</dt>
            <dd class="col-sm-9"><?= e($usuario['telefono'] ?? 'No especificado') ?></dd>

            <dt class="col-sm-3">Rol</dt>
            <dd class="col-sm-9"><?= e($usuario['rol']) ?></dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9"><span class="badge <?= $usuario['estado'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?> text-uppercase"><?= e($usuario['estado']) ?></span></dd>

            <dt class="col-sm-3">Información</dt>
            <dd class="col-sm-9"><?= nl2br(e($usuario['biografia'] ?? 'Sin información adicional')) ?></dd>

            <dt class="col-sm-3">Fecha de registro</dt>
            <dd class="col-sm-9"><?= e(date('d/m/Y H:i', strtotime($usuario['fecha_creacion'] ?? 'now'))) ?></dd>

            <dt class="col-sm-3">Última actualización</dt>
            <dd class="col-sm-9"><?= !empty($usuario['fecha_actualizacion']) ? e(date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']))) : 'Sin cambios' ?></dd>
        </dl>
    </div>
</div>
<div class="mt-3">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/usuarios')) ?>">Volver</a>
    <a class="btn btn-primary btn-sm" href="<?= e(url('/app/usuarios/editar/' . $usuario['id'])) ?>">Editar</a>
</div>
