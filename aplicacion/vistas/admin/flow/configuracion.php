<h1 class="h5 mb-3">Configuración Flow</h1>
<form method="POST" action="<?= e(url('/admin/flow/configuracion')) ?>" class="card p-3">
  <?= csrf_campo() ?>
  <div class="row g-2">
    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" id="activo" name="activo" <?= !empty($config['activo'])?'checked':'' ?>><label class="form-check-label" for="activo">Activar integración</label></div>
    <div class="col-md-3"><label class="form-label">Entorno</label><select class="form-select" name="entorno"><option value="sandbox" <?= ($config['entorno']??'sandbox')==='sandbox'?'selected':'' ?>>Sandbox</option><option value="produccion" <?= ($config['entorno']??'')==='produccion'?'selected':'' ?>>Producción</option></select></div>
    <div class="col-md-6"><label class="form-label">Base URL API (opcional)</label><input class="form-control" name="base_url" value="<?= e($config['base_url'] ?? '') ?>" placeholder="<?= e(($config['entorno'] ?? 'produccion') === 'produccion' ? 'https://www.flow.cl/api' : 'https://sandbox.flow.cl/api') ?>"></div>
    <div class="col-md-6"><label class="form-label">API Key</label><input class="form-control" name="api_key" value="<?= e($config['api_key'] ?? '484DFD4D-0A41-424D-A573-95BDAF374LD4') ?>" required></div>
    <div class="col-md-6"><label class="form-label">Secret Key <?= !empty($config['secret_key_masked']) ? '(' . e($config['secret_key_masked']) . ')' : '' ?></label><input class="form-control" name="secret_key" type="password" placeholder="Dejar vacío para mantener"></div>
    <div class="col-md-4"><label class="form-label">URL confirmación pago</label><input class="form-control" name="url_confirmacion" value="<?= e($config['url_confirmacion'] ?? url('/flow/webhook/payment-confirmation')) ?>"></div>
    <div class="col-md-4"><label class="form-label">URL retorno pago</label><input class="form-control" name="url_retorno" value="<?= e($config['url_retorno'] ?? url('/flow/retorno/pago')) ?>"></div>
    <div class="col-md-4"><label class="form-label">URL webhook suscripción</label><input class="form-control" name="url_webhook_suscripcion" value="<?= e($config['url_webhook_suscripcion'] ?? url('/flow/webhook/subscription')) ?>"></div>
    <div class="col-md-6"><label class="form-label">URL webhook pago</label><input class="form-control" name="url_webhook_pago" value="<?= e($config['url_webhook_pago'] ?? url('/flow/webhook/payment-confirmation')) ?>"></div>
    <div class="col-md-6"><label class="form-label">URL retorno registro medio de pago</label><input class="form-control" name="url_retorno_registro" value="<?= e($config['url_retorno_registro'] ?? url('/flow/retorno/registro')) ?>"></div>
    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" id="pagos_unicos" name="habilitar_pagos_unicos" <?= !isset($config['habilitar_pagos_unicos']) || (int)$config['habilitar_pagos_unicos']===1?'checked':'' ?>><label class="form-check-label" for="pagos_unicos">Pagos únicos</label></div>
    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" id="suscripciones" name="habilitar_suscripciones" <?= !isset($config['habilitar_suscripciones']) || (int)$config['habilitar_suscripciones']===1?'checked':'' ?>><label class="form-check-label" for="suscripciones">Suscripciones</label></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary btn-sm">Guardar configuración</button></div>
</form>
