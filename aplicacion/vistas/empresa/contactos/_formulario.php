<?php
$contactoActual = $contacto ?? [];
$accionFormulario = $accionFormulario ?? url('/app/contactos');
$textoBoton = $textoBoton ?? 'Guardar contacto';
$mostrarCancelar = $mostrarCancelar ?? false;
?>
<form method="POST" action="<?= e($accionFormulario) ?>" class="row g-3">
    <?= csrf_campo() ?>
    <div class="col-md-4">
        <label class="form-label">Cliente registrado</label>
        <select name="cliente_id" class="form-select" required>
            <option value="">Selecciona un cliente</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) ($contactoActual['cliente_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['nombre_comercial'] ?: $c['razon_social'] ?: $c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nombre del contacto</label>
        <input name="nombre" class="form-control" required maxlength="120" placeholder="Ej: María Pérez" value="<?= e($contactoActual['nombre'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Cargo</label>
        <input name="cargo" class="form-control" maxlength="120" placeholder="Ej: Jefe de compras" value="<?= e($contactoActual['cargo'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="150" placeholder="correo@empresa.com" value="<?= e($contactoActual['correo'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" maxlength="30" placeholder="Ej: +51 1 555 1234" value="<?= e($contactoActual['telefono'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Celular</label>
        <input name="celular" class="form-control" maxlength="30" placeholder="Ej: +51 999 888 777" value="<?= e($contactoActual['celular'] ?? '') ?>">
    </div>
    <div class="col-md-8">
        <label class="form-label">Observaciones</label>
        <input name="observaciones" class="form-control" maxlength="255" placeholder="Detalles relevantes del contacto" value="<?= e($contactoActual['observaciones'] ?? '') ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="es_principal" name="es_principal" value="1" <?= !empty($contactoActual['es_principal']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="es_principal">Marcar como contacto principal</label>
        </div>
    </div>
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><?= e($textoBoton) ?></button>
        <?php if ($mostrarCancelar): ?>
            <a href="<?= e(url('/app/contactos')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
        <?php endif; ?>
    </div>
</form>
