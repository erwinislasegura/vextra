<?php
/** @var array<string,mixed>|null $vendedor */
/** @var array<int,array<string,mixed>> $usuarios */
$vendedor = $vendedor ?? null;
$usuarios = $usuarios ?? [];
$usuarioSeleccionado = (int) ($vendedor['usuario_id'] ?? 0);
?>
<div class="col-md-4">
    <label class="form-label" for="vendedor_nombre">Nombre completo</label>
    <input id="vendedor_nombre" name="nombre" class="form-control" maxlength="120" required placeholder="Ej. Laura García" value="<?= e((string) ($vendedor['nombre'] ?? '')) ?>">
</div>
<div class="col-md-4">
    <label class="form-label" for="vendedor_correo">Correo corporativo</label>
    <input id="vendedor_correo" name="correo" class="form-control" type="email" maxlength="120" placeholder="vendedor@empresa.com" value="<?= e((string) ($vendedor['correo'] ?? '')) ?>">
</div>
<div class="col-md-2">
    <label class="form-label">Teléfono</label>
    <input name="telefono" class="form-control" maxlength="30" placeholder="3001234567" value="<?= e((string) ($vendedor['telefono'] ?? '')) ?>">
</div>
<div class="col-md-2">
    <label class="form-label">Comisión %</label>
    <input name="comision" class="form-control" type="number" step="0.01" min="0" max="100" value="<?= e((string) ($vendedor['comision'] ?? '0')) ?>">
</div>
<div class="col-md-6">
    <label class="form-label">Usuario asociado (opcional)</label>
    <select name="usuario_id" class="form-select">
        <option value="">Sin usuario asociado</option>
        <?php foreach ($usuarios as $usuario): ?>
            <option value="<?= (int) $usuario['id'] ?>" <?= $usuarioSeleccionado === (int) $usuario['id'] ? 'selected' : '' ?>>
                <?= e(($usuario['nombre'] ?? 'Usuario') . ' · ' . ($usuario['correo'] ?? '')) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
        <?php $estadoActual = (string) ($vendedor['estado'] ?? 'activo'); ?>
        <option value="activo" <?= $estadoActual === 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $estadoActual === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
    </select>
</div>
