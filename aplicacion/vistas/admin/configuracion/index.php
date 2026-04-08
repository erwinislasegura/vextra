<h1 class="h4 mb-3">Configuración general</h1>

<div class="card">
  <div class="card-header">Parámetros globales del SaaS</div>
  <div class="card-body">
    <form method="POST" action="<?= e(url('/admin/configuracion')) ?>" class="row g-2">
      <?= csrf_campo() ?>

      <div class="col-md-3">
        <label class="form-label">Nombre del sistema</label>
        <input class="form-control" name="nombre_plataforma" value="<?= e($config['nombre_plataforma'] ?? 'Vextra') ?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Correo soporte</label>
        <input class="form-control" type="email" name="correo_soporte" value="<?= e($config['correo_soporte'] ?? '') ?>" placeholder="soporte@dominio.com">
      </div>

      <div class="col-md-2">
        <label class="form-label">Moneda por defecto</label>
        <select class="form-select" name="moneda_defecto">
          <?php $moneda = (string) ($config['moneda_defecto'] ?? 'CLP'); ?>
          <option value="CLP" <?= $moneda === 'CLP' ? 'selected' : '' ?>>CLP</option>
          <option value="USD" <?= $moneda === 'USD' ? 'selected' : '' ?>>USD</option>
          <option value="EUR" <?= $moneda === 'EUR' ? 'selected' : '' ?>>EUR</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Zona horaria</label>
        <input class="form-control" name="zona_horaria" value="<?= e($config['zona_horaria'] ?? 'America/Santiago') ?>" placeholder="America/Santiago">
      </div>

      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <?php $estado = (string) ($config['estado_plataforma'] ?? 'activo'); ?>
        <select class="form-select" name="estado_plataforma">
          <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>activo</option>
          <option value="mantenimiento" <?= $estado === 'mantenimiento' ? 'selected' : '' ?>>mantenimiento</option>
        </select>
      </div>

      <div class="col-12 mt-2">
        <h2 class="h6 mb-1">Google reCAPTCHA</h2>
        <p class="text-muted small mb-2">Define aquí las credenciales para aplicar validación anti-bot en landing pages, formulario de contacto y registro.</p>
      </div>

      <div class="col-md-3">
        <label class="form-label d-block">Habilitar reCAPTCHA</label>
        <?php $recaptchaHabilitado = (string) ($config['recaptcha_habilitado'] ?? '0'); ?>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="recaptcha_habilitado" name="recaptcha_habilitado" value="1" <?= $recaptchaHabilitado === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="recaptcha_habilitado">Activar validación anti-bot en formularios públicos</label>
        </div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Site key</label>
        <input class="form-control" name="recaptcha_site_key" value="<?= e($config['recaptcha_site_key'] ?? '') ?>" placeholder="6Lc...">
      </div>

      <div class="col-md-5">
        <label class="form-label">Secret key</label>
        <input class="form-control" name="recaptcha_secret_key" value="<?= e($config['recaptcha_secret_key'] ?? '') ?>" placeholder="6Lc...">
      </div>

      <div class="col-12" id="imap-smtp-admin">
        <h2 class="h6 mb-1 mt-2">Configuración IMAP/POP3/SMTP (Administrador)</h2>
        <p class="text-muted small mb-2">Credenciales globales para notificaciones automáticas a clientes (cuenta creada y pago confirmado).</p>
      </div>

      <div class="col-md-3">
        <label class="form-label">Servidor SMTP</label>
        <input class="form-control" name="smtp_notif_host" value="<?= e($config['smtp_notif_host'] ?? 'mail.vextra.cl') ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Puerto</label>
        <input class="form-control" name="smtp_notif_port" value="<?= e($config['smtp_notif_port'] ?? '465') ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Seguridad</label>
        <?php $smtpEncryption = (string) ($config['smtp_notif_encryption'] ?? 'ssl'); ?>
        <select class="form-select" name="smtp_notif_encryption">
          <option value="ssl" <?= $smtpEncryption === 'ssl' ? 'selected' : '' ?>>SSL/TLS</option>
          <option value="tls" <?= $smtpEncryption === 'tls' ? 'selected' : '' ?>>TLS</option>
          <option value="" <?= $smtpEncryption === '' ? 'selected' : '' ?>>Ninguna</option>
        </select>
      </div>

      <div class="col-md-5">
        <label class="form-label">Usuario SMTP</label>
        <input class="form-control" name="smtp_notif_usuario" value="<?= e($config['smtp_notif_usuario'] ?? 'noresponder@vextra.cl') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Puerto IMAP</label>
        <input class="form-control" name="imap_notif_port" value="<?= e($config['imap_notif_port'] ?? '993') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Puerto POP3</label>
        <input class="form-control" name="pop3_notif_port" value="<?= e($config['pop3_notif_port'] ?? '995') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Contraseña SMTP</label>
        <input type="password" class="form-control" name="smtp_notif_password" placeholder="••••••••">
      </div>

      <div class="col-md-4">
        <label class="form-label">Remitente correo</label>
        <input class="form-control" name="smtp_notif_remitente_correo" value="<?= e($config['smtp_notif_remitente_correo'] ?? 'noresponder@vextra.cl') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Remitente nombre</label>
        <input class="form-control" name="smtp_notif_remitente_nombre" value="<?= e($config['smtp_notif_remitente_nombre'] ?? 'Vextra Notificaciones') ?>">
      </div>

      <div class="col-12">
        <button class="btn btn-primary btn-sm">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
