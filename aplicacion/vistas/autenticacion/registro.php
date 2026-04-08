<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-lg-5">
          <h1 class="h3 mb-2">Crear cuenta empresarial</h1>
          <p class="text-secondary mb-4">
            Registra tu empresa en menos de 2 minutos. Te enviaremos acceso inmediato para iniciar la gestión comercial.
          </p>

          <form method="POST" class="row g-3" data-recaptcha-form="1" data-recaptcha-action="registro_empresa">
            <?= csrf_campo() ?>
            <input type="hidden" name="g-recaptcha-response" value="">

            <div class="col-12">
              <h2 class="h6 text-uppercase text-muted mb-2">Datos de la empresa</h2>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="razon_social">Razón social <span class="text-danger">*</span></label>
              <input id="razon_social" class="form-control" name="razon_social" maxlength="150" value="<?= e((string) ($datosFormulario['razon_social'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nombre_comercial">Nombre comercial <span class="text-danger">*</span></label>
              <input id="nombre_comercial" class="form-control" name="nombre_comercial" maxlength="150" value="<?= e((string) ($datosFormulario['nombre_comercial'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="identificador_fiscal">RUT / NIT <span class="text-danger">*</span></label>
              <input id="identificador_fiscal" class="form-control" name="identificador_fiscal" maxlength="80" value="<?= e((string) ($datosFormulario['identificador_fiscal'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="correo_empresa">Correo corporativo <span class="text-danger">*</span></label>
              <input id="correo_empresa" type="email" class="form-control" name="correo_empresa" maxlength="150" value="<?= e((string) ($datosFormulario['correo_empresa'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="telefono">Teléfono</label>
              <input id="telefono" class="form-control" name="telefono" maxlength="60" value="<?= e((string) ($datosFormulario['telefono'] ?? '')) ?>" placeholder="+57 300 123 4567">
            </div>
            <div class="col-md-5">
              <label class="form-label" for="direccion">Dirección</label>
              <input id="direccion" class="form-control" name="direccion" maxlength="200" value="<?= e((string) ($datosFormulario['direccion'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="ciudad">Ciudad</label>
              <input id="ciudad" class="form-control" name="ciudad" maxlength="120" value="<?= e((string) ($datosFormulario['ciudad'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="pais">País</label>
              <input id="pais" class="form-control" name="pais" value="<?= e((string) ($datosFormulario['pais'] ?? 'Colombia')) ?>" maxlength="120">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="plan_id">Plan <span class="text-danger">*</span></label>
              <select id="plan_id" class="form-select" name="plan_id" required>
                <option value="">Selecciona un plan</option>
                <?php foreach ($planes as $plan): ?>
                  <option value="<?= (int) $plan['id'] ?>" <?= ((int) ($planPreseleccionado ?? 0) === (int) $plan['id']) ? 'selected' : '' ?>>
                    <?= e($plan['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tipo_cobro">Tipo de cobro <span class="text-danger">*</span></label>
              <select id="tipo_cobro" class="form-select" name="tipo_cobro" required>
                <option value="mensual" <?= (($tipoCobroPreseleccionado ?? 'mensual') === 'mensual') ? 'selected' : '' ?>>Mensual</option>
                <option value="anual" <?= (($tipoCobroPreseleccionado ?? 'mensual') === 'anual') ? 'selected' : '' ?>>Anual (ahorra con precio preferente)</option>
              </select>
            </div>

            <div class="col-12 mt-2">
              <h2 class="h6 text-uppercase text-muted mb-2">Administrador de la cuenta</h2>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="nombre_admin">Nombre completo <span class="text-danger">*</span></label>
              <input id="nombre_admin" class="form-control" name="nombre_admin" maxlength="150" value="<?= e((string) ($datosFormulario['nombre_admin'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="correo_admin">Correo del administrador <span class="text-danger">*</span></label>
              <input id="correo_admin" type="email" class="form-control" name="correo_admin" maxlength="150" value="<?= e((string) ($datosFormulario['correo_admin'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="password">Contraseña <span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="password" type="password" class="form-control" name="password" minlength="8" value="<?= e((string) ($datosFormulario['password'] ?? '')) ?>" required>
                <button type="button" class="btn btn-outline-secondary" id="toggle_password">Mostrar</button>
              </div>
              <div class="form-text">Mínimo 8 caracteres.</div>
            </div>

            <div class="col-12">
              <div class="alert alert-info mb-2">
                Al completar tu registro se iniciará el <strong>pago del plan en Flow Ecommerce</strong>. La cuenta se activará automáticamente cuando el pago sea aprobado.
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2 small text-secondary">
                <span class="fw-semibold text-dark">Medios de pago Flow:</span>
                <span class="badge text-bg-light border">VISA</span>
                <span class="badge text-bg-light border">Mastercard</span>
                <span class="badge text-bg-light border">AMEX</span>
                <span class="badge text-bg-light border">Redcompra</span>
                <span class="badge text-bg-light border">Webpay</span>
              </div>
            </div>

            <div class="col-12">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="acepta_terminos"
                  name="acepta_terminos"
                  value="1"
                  <?= ((string) ($datosFormulario['acepta_terminos'] ?? '0') === '1') ? 'checked' : '' ?>
                  required
                >
                <label class="form-check-label" for="acepta_terminos">
                  Acepto los términos y condiciones del servicio y autorizo el proceso de cobro del plan mediante Flow.
                </label>
              </div>
            </div>

            <div class="col-12 d-grid d-md-flex justify-content-md-end pt-2">
              <button type="submit" class="btn btn-primary px-4">Crear empresa y continuar a pago</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
(() => {
  const input = document.getElementById('password');
  const boton = document.getElementById('toggle_password');
  if (!input || !boton) return;
  boton.addEventListener('click', () => {
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    boton.textContent = visible ? 'Mostrar' : 'Ocultar';
  });
})();
</script>
