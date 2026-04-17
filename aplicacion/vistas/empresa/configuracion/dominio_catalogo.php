<h1 class="h4 mb-3">Dominio personalizado del catálogo</h1>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Configura tu dominio para mostrar el catálogo con tu marca</div>
  <ul class="mb-0 small ps-3">
    <li>Ingresa solo el host, sin <code>https://</code> ni rutas (ej: <code>catalogo.tuempresa.com</code>).</li>
    <li>Debes apuntar DNS (A o CNAME) al servidor de Vextra.</li>
    <li>El certificado SSL del dominio debe estar activo para usar HTTPS.</li>
  </ul>
</div>

<div class="alert alert-secondary mb-3">
  <div class="fw-semibold mb-1">DocumentRoot recomendado para cPanel / hosting</div>
  <div class="small mb-1">El dominio del cliente debe apuntar al mismo directorio público de Vextra.</div>
  <code><?= e($documentRootActual !== '' ? $documentRootActual : '(no disponible en este entorno)') ?></code>
</div>

<div class="card mb-3">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span>Estado de funcionalidad en tu plan</span>
    <?php if ($incluyeDominioCatalogo): ?>
      <span class="badge bg-success-subtle text-success-emphasis">Incluido</span>
    <?php else: ?>
      <span class="badge bg-warning-subtle text-warning-emphasis">No incluido</span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <?php if (!$incluyeDominioCatalogo): ?>
      <div class="alert alert-warning mb-0">
        Tu plan actual no incluye dominio personalizado para catálogo. Puedes dejar este valor como referencia, pero no podrás editarlo hasta cambiar de plan.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (is_array($diagnosticoDns ?? null)): ?>
  <div class="alert <?= !empty($diagnosticoDns['coincide']) ? 'alert-success' : 'alert-warning' ?> mb-3">
    <div class="fw-semibold mb-1">Estado automático DNS</div>
    <div class="small">Dominio: <code><?= e((string) ($diagnosticoDns['dominio'] ?? '')) ?></code></div>
    <div class="small">IPs del dominio: <code><?= e(implode(', ', $diagnosticoDns['ips_dominio'] ?? []) ?: 'sin A') ?></code></div>
    <div class="small">IPs esperadas servidor: <code><?= e(implode(', ', $diagnosticoDns['ips_esperadas'] ?? []) ?: 'sin IP esperada') ?></code></div>
  </div>
<?php endif; ?>

<form method="POST" action="<?= e(url('/app/configuracion/dominio-catalogo')) ?>" class="card" id="formDominioCatalogo">
  <div class="card-header">Configuración de dominio</div>
  <div class="card-body row g-3">
    <?= csrf_campo() ?>

    <div class="col-md-8">
      <label class="form-label">Dominio personalizado</label>
      <input
        name="catalogo_dominio"
        class="form-control"
        value="<?= e($catalogoDominio) ?>"
        placeholder="catalogo.tuempresa.com"
        autocomplete="off"
        <?= $incluyeDominioCatalogo ? '' : 'readonly' ?>
      >
      <div class="form-text">Si lo dejas vacío, el catálogo seguirá funcionando con la URL por ID.</div>
    </div>

    <div class="col-md-4">
      <label class="form-label">URL actual de respaldo</label>
      <input class="form-control" value="<?= e(url('/catalogo/' . (int) ($empresa['id'] ?? 0))) ?>" readonly>
      <div class="form-text">Esta URL no se desactiva.</div>
    </div>

    <div class="col-12">
      <div class="alert alert-light border mb-0">
        <div class="fw-semibold mb-1">Pasos recomendados en hosting</div>
        <ol class="mb-0 small ps-3">
          <li>Crea DNS A/CNAME para tu dominio hacia este servidor.</li>
          <li>Activa SSL del dominio.</li>
          <li>Si quieres ocultar <code>/catalogo/{id}</code>, configura rewrite/proxy interno en tu hosting.</li>
        </ol>
      </div>
    </div>
  </div>

  <div class="card-footer">
    <button class="btn btn-outline-secondary btn-sm" type="submit" name="accion" value="verificar_dns">Verificar DNS</button>
    <button class="btn btn-primary btn-sm" type="submit" name="accion" value="guardar" <?= $incluyeDominioCatalogo ? '' : 'disabled' ?>>Guardar dominio</button>
  </div>
</form>
