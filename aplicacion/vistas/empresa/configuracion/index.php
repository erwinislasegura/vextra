<h1 class="h4 mb-3">Configuración de empresa</h1>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Recomendaciones de configuración</div>
  <ul class="mb-0 small ps-3">
    <li>Mantén actualizados los datos fiscales y de contacto para documentos comerciales.</li>
    <li>Configura IMAP correctamente para envío y trazabilidad de notificaciones.</li>
    <li>Sube un logo optimizado para mejorar presentación de cotizaciones y PDFs.</li>
  </ul>
</div>

<form method="POST" action="<?= e(url('/app/configuracion')) ?>" enctype="multipart/form-data" class="row g-3">
  <?= csrf_campo() ?>

  <div class="col-12">
    <div class="card">
      <div class="card-header">Datos generales de la empresa</div>
      <div class="card-body row g-3">
        <div class="col-md-4">
          <label class="form-label">Razón social</label>
          <input name="razon_social" class="form-control" value="<?= e($empresa['razon_social'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Nombre comercial</label>
          <input name="nombre_comercial" class="form-control" value="<?= e($empresa['nombre_comercial'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Identificador fiscal</label>
          <input name="identificador_fiscal" class="form-control" value="<?= e($empresa['identificador_fiscal'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Correo principal</label>
          <input type="email" name="correo" class="form-control" value="<?= e($empresa['correo'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono</label>
          <input name="telefono" class="form-control" value="<?= e($empresa['telefono'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Ciudad</label>
          <input name="ciudad" class="form-control" value="<?= e($empresa['ciudad'] ?? '') ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label">Dirección</label>
          <input name="direccion" class="form-control" value="<?= e($empresa['direccion'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">País</label>
          <input name="pais" class="form-control" value="<?= e($empresa['pais'] ?? '') ?>">
        </div>
        <div class="col-12">
          <hr class="my-1">
          <div class="fw-semibold small text-uppercase text-muted">Administrador de la cuenta</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nombre completo administrador</label>
          <input
            name="nombre_admin"
            class="form-control"
            value="<?= e((string) ($adminEmpresa['nombre'] ?? '')) ?>"
            maxlength="150"
            placeholder="Nombre del administrador principal"
          >
        </div>
        <div class="col-md-6">
          <label class="form-label">Correo del administrador</label>
          <input
            type="email"
            name="correo_admin"
            class="form-control"
            value="<?= e((string) ($adminEmpresa['correo'] ?? '')) ?>"
            maxlength="150"
            placeholder="admin@empresa.com"
          >
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">Logo de la empresa</div>
      <div class="card-body">
        <?php if (!empty($empresa['logo'])): ?>
          <div class="mb-3">
            <img src="<?= e(url($empresa['logo'])) ?>" alt="Logo de empresa" style="max-width: 220px; max-height: 120px;" class="img-thumbnail">
          </div>
        <?php endif; ?>
        <label class="form-label">Subir nuevo logo</label>
        <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control">
        <small class="text-muted">Formatos permitidos: PNG, JPG, WEBP o SVG. Tamaño máximo: 2MB.</small>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header">Correo IMAP para envíos y notificaciones</div>
      <div class="card-body row g-3">
        <div class="col-md-6">
          <label class="form-label">Servidor IMAP</label>
          <input name="imap_host" class="form-control" placeholder="imap.tudominio.com" value="<?= e($empresa['imap_host'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Puerto</label>
          <input type="number" name="imap_port" class="form-control" value="<?= e((string) ($empresa['imap_port'] ?? '993')) ?>" min="1">
        </div>
        <div class="col-md-3">
          <label class="form-label">Encriptación</label>
          <select name="imap_encryption" class="form-select">
            <?php $encryption = $empresa['imap_encryption'] ?? 'tls'; ?>
            <option value="ssl" <?= $encryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value="tls" <?= $encryption === 'tls' ? 'selected' : '' ?>>TLS</option>
            <option value="none" <?= $encryption === 'none' ? 'selected' : '' ?>>Ninguna</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Usuario IMAP</label>
          <input name="imap_usuario" class="form-control" value="<?= e($empresa['imap_usuario'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contraseña IMAP</label>
          <input type="password" name="imap_password" class="form-control" placeholder="••••••••">
          <small class="text-muted">Déjalo en blanco para conservar la contraseña actual.</small>
        </div>
        <div class="col-md-6">
          <label class="form-label">Correo remitente</label>
          <input type="email" name="imap_remitente_correo" class="form-control" value="<?= e($empresa['imap_remitente_correo'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nombre remitente</label>
          <input name="imap_remitente_nombre" class="form-control" value="<?= e($empresa['imap_remitente_nombre'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-primary btn-sm">Guardar configuración</button>
  </div>
</form>
