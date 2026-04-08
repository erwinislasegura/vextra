<h1 class="h4 mb-3">Plantilla HTML de correo para cotizaciones</h1>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Editor de correo de cotización</div>
  <ul class="mb-0 small ps-3">
    <li>Selecciona una cotización para precargar el correo de destino real y los datos de ejemplo.</li>
    <li>Edita el HTML usando variables y revisa la vista previa antes de aplicarlo.</li>
  </ul>
</div>

<div class="card shadow-sm border-0 mb-3">
  <div class="card-body">
    <form method="POST" class="row g-3 align-items-end">
      <?= csrf_campo() ?>
      <div class="col-md-7">
        <label class="form-label">Cotización de referencia</label>
        <select name="cotizacion_id" class="form-select" required>
          <option value="">Selecciona una cotización</option>
          <?php foreach ($cotizaciones as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $cotizacionId === (int) $c['id'] ? 'selected' : '' ?>>
              <?= e(($c['numero'] ?? 'Sin número') . ' · ' . ($c['cliente'] ?? 'Sin cliente')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-5">
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary btn-sm" name="accion" value="preview">Generar vista previa</button>
          <button class="btn btn-primary btn-sm" name="accion" value="guardar">Guardar plantilla</button>
          <button class="btn btn-outline-secondary btn-sm" name="accion" value="restaurar">Restaurar original</button>
        </div>
      </div>

      <div class="col-md-6">
        <div class="border rounded p-3 bg-light-subtle h-100">
          <div class="small text-muted text-uppercase">Correo de destino</div>
          <div class="fw-semibold"><?= e($variables['{{correo_destino}}'] ?? 'sin-correo@cliente.com') ?></div>
          <div class="small text-muted mt-2">Cotización: <?= e($variables['{{numero_cotizacion}}'] ?? 'COT-0000') ?></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="border rounded p-3 bg-light-subtle h-100">
          <div class="small text-muted text-uppercase">Remitente</div>
          <div class="fw-semibold"><?= e($variables['{{remitente_nombre}}'] ?? 'Tu empresa') ?></div>
          <div class="small text-muted"><?= e($variables['{{remitente_correo}}'] ?? '') ?></div>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label">Asunto del correo</label>
        <input name="asunto_correo" class="form-control" value="<?= e($asuntoCorreo ?? '') ?>" placeholder="Cotización {{numero_cotizacion}} - {{empresa_nombre}}">
        <div class="form-text">Vista previa asunto: <strong><?= e($asuntoPrevia ?? '') ?></strong></div>
      </div>

      <div class="col-12">
        <label class="form-label">HTML del correo</label>
        <textarea name="plantilla_html" class="form-control" rows="18" style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;"><?= e($plantillaHtml) ?></textarea>
      </div>

      <div class="col-12">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h2 class="h6 mb-2">Variables disponibles</h2>
            <div class="row row-cols-1 row-cols-md-2 g-2 small">
              <?php foreach ($variables as $clave => $valor): ?>
                <div class="col">
                  <div class="border rounded p-2 h-100 bg-white">
                    <code><?= e($clave) ?></code>
                    <div class="text-muted mt-1">Ejemplo: <?= e((string) $valor) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white">
    <strong>Vista previa del correo</strong>
  </div>
  <div class="card-body bg-white">
    <?= $vistaPrevia ?>
  </div>
</div>
