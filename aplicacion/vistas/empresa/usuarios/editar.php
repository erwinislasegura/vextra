<h1 class="h4 mb-3">Editar usuario</h1>
<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Recomendaciones para edición de usuarios</div>
    <ul class="mb-0 small ps-3">
        <li>Completa cargo, teléfono e información interna para mejorar coordinación del equipo.</li>
        <li>Si vas a cambiar contraseña, confirma primero la contraseña actual para proteger la cuenta.</li>
    </ul>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="fw-semibold mb-1">Información del usuario</div>
                <div class="small text-muted">ID #<?= (int) $usuario['id'] ?> · Registrado: <?= e(date('d/m/Y', strtotime($usuario['fecha_creacion'] ?? 'now'))) ?></div>
            </div>
            <span class="badge <?= $usuario['estado'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary' ?> text-uppercase"><?= e($usuario['estado']) ?></span>
        </div>

        <form method="POST" class="row g-3">
            <?= csrf_campo() ?>

            <div class="col-md-6">
                <label class="form-label">Nombre completo</label>
                <input name="nombre" class="form-control" value="<?= e($usuario['nombre']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Correo electrónico</label>
                <input name="correo" type="email" class="form-control" value="<?= e($usuario['correo']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Cargo / puesto</label>
                <input name="cargo" class="form-control" value="<?= e($usuario['cargo'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input name="telefono" class="form-control" value="<?= e($usuario['telefono'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Rol</label>
                <select name="rol_id" class="form-select" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= (int) $rol['id'] ?>" <?= (int) $usuario['rol_id'] === (int) $rol['id'] ? 'selected' : '' ?>>
                            <?= e($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select" required>
                    <option value="activo" <?= $usuario['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Información del usuario</label>
                <textarea name="biografia" class="form-control" rows="2" placeholder="Notas internas del usuario."><?= e($usuario['biografia'] ?? '') ?></textarea>
            </div>

            <?php if (!empty($esUsuarioLogueado)): ?>
                <div class="col-12"><hr class="my-1"></div>
                <div class="col-12">
                    <h2 class="h6 mb-2">Cambiar contraseña</h2>
                    <p class="text-muted small mb-0">Completa estos campos solo si deseas actualizar la contraseña de tu cuenta actual.</p>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contraseña actual</label>
                    <input name="password_actual" type="password" class="form-control" autocomplete="current-password">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nueva contraseña</label>
                    <input name="nueva_password" type="password" class="form-control" minlength="8" autocomplete="new-password">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input name="confirmar_password" type="password" class="form-control" minlength="8" autocomplete="new-password">
                </div>
            <?php endif; ?>

            <div class="col-12 d-flex gap-2 pt-1">
                <button class="btn btn-primary btn-sm">Guardar cambios</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/usuarios')) ?>">Cancelar</a>
            </div>
        </form>
    </div>
</div>
