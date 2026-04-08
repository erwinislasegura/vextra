<h1 class="h4 mb-3">Plantilla HTML de envío para órdenes de compra</h1>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Editor de correo de orden de compra</div>
  <ul class="mb-0 small ps-3">
    <li>Selecciona una OC para precargar proveedor, asunto y variables reales.</li>
    <li>Edita el HTML a la izquierda y valida la vista previa al lado derecho.</li>
  </ul>
</div>

<form method="POST" class="card shadow-sm border-0 mb-3">
  <div class="card-body row g-3 align-items-end">
    <?= csrf_campo() ?>
    <div class="col-md-7">
      <label class="form-label">Orden de compra de referencia</label>
      <select name="orden_id" class="form-select" required>
        <option value="">Selecciona una orden</option>
        <?php foreach ($ordenes as $o): ?>
          <option value="<?= (int) $o['id'] ?>" <?= (int) $ordenId === (int) $o['id'] ? 'selected' : '' ?>>
            <?= e(($o['numero'] ?? 'Sin número') . ' · ' . ($o['proveedor_nombre'] ?? 'Sin proveedor')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <div class="d-flex gap-2 flex-wrap justify-content-md-end">
        <button class="btn btn-outline-primary btn-sm" name="accion" value="preview">Generar vista previa</button>
        <button class="btn btn-primary btn-sm" name="accion" value="guardar">Guardar plantilla</button>
        <button class="btn btn-outline-secondary btn-sm" name="accion" value="restaurar">Restaurar original</button>
      </div>
    </div>

    <div class="col-md-6">
      <div class="border rounded p-3 bg-light-subtle h-100">
        <div class="small text-muted text-uppercase">Proveedor destino</div>
        <div class="fw-semibold"><?= e($variables['{{proveedor_nombre}}'] ?? 'Proveedor') ?></div>
        <div class="small text-muted"><?= e($variables['{{correo_destino}}'] ?? 'sin-correo@proveedor.com') ?></div>
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
      <input name="asunto_correo" class="form-control" value="<?= e($asuntoCorreo ?? '') ?>" placeholder="Orden de compra {{numero_orden}} - {{empresa_nombre}}">
      <div class="form-text">Vista previa asunto: <strong><?= e($asuntoPrevia ?? '') ?></strong></div>
    </div>

    <div class="col-12">
      <div class="row g-3">
        <div class="col-lg-6">
          <label class="form-label">Envío OC HTML</label>
          <textarea name="plantilla_html" class="form-control" rows="20" style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;"><?= e($plantillaHtml ?? '') ?></textarea>
        </div>
        <div class="col-lg-6">
          <label class="form-label">Vista previa</label>
          <div class="border rounded bg-white p-3" style="min-height: 420px; overflow:auto;">
            <?= $vistaPrevia ?? '' ?>
          </div>
        </div>
      </div>
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
  </div>
</form>
