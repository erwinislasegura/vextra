<h1 class="h4 mb-3">Configuración de correos de stock</h1>
<div class="card mb-3"><div class="card-header">Parámetros de alerta automática</div><div class="card-body">
<form method="POST" class="row g-2"><?= csrf_campo() ?>
<div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="activar_alerta_stock_bajo" id="alerta_bajo" <?= ($configuracion['activar_alerta_stock_bajo'] ?? '0') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="alerta_bajo">Activar stock bajo</label></div></div>
<div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="activar_alerta_stock_critico" id="alerta_critico" <?= ($configuracion['activar_alerta_stock_critico'] ?? '0') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="alerta_critico">Activar stock crítico</label></div></div>
<div class="col-md-6"><label class="form-label">Destinatarios (separados por coma)</label><input name="destinatarios_alerta_stock" class="form-control" value="<?= e($configuracion['destinatarios_alerta_stock'] ?? '') ?>"></div>
<div class="col-12"><div class="alert alert-info small mb-0"><strong>Variables disponibles:</strong> <?= e(implode(', ', $variables)) ?></div></div>

<div class="col-md-6"><label class="form-label">Asunto stock bajo</label><input name="asunto_stock_bajo" class="form-control" value="<?= e($configuracion['asunto_stock_bajo'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Asunto stock crítico</label><input name="asunto_stock_critico" class="form-control" value="<?= e($configuracion['asunto_stock_critico'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Plantilla HTML stock bajo</label><textarea name="plantilla_html_stock_bajo" class="form-control" rows="12" style="font-family: ui-monospace,monospace;"><?= e($configuracion['plantilla_html_stock_bajo'] ?? '') ?></textarea></div>
<div class="col-md-6"><label class="form-label">Plantilla HTML stock crítico</label><textarea name="plantilla_html_stock_critico" class="form-control" rows="12" style="font-family: ui-monospace,monospace;"><?= e($configuracion['plantilla_html_stock_critico'] ?? '') ?></textarea></div>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary btn-sm" name="accion" value="guardar">Guardar configuración</button><input type="email" name="correo_prueba" class="form-control form-control-sm" style="max-width:280px;" placeholder="correo@empresa.com"><button class="btn btn-outline-secondary btn-sm" name="accion" value="enviar_prueba">Enviar prueba</button></div>
</form></div></div>

<div class="row g-3">
<div class="col-md-6"><div class="card"><div class="card-header">Vista previa stock bajo</div><div class="card-body"><div class="small text-muted mb-2"><strong>Asunto:</strong> <?= e($asuntoPreviaBajo) ?></div><div class="border rounded p-2 bg-light"><?= $vistaPreviaBajo ?></div></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Vista previa stock crítico</div><div class="card-body"><div class="small text-muted mb-2"><strong>Asunto:</strong> <?= e($asuntoPreviaCritico) ?></div><div class="border rounded p-2 bg-light"><?= $vistaPreviaCritico ?></div></div></div></div>
</div>
