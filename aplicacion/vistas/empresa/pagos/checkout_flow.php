<?php
$estadoBadge = static function (string $estado): string {
    return match ($estado) {
        'aprobado' => 'success',
        'rechazado', 'anulado' => 'danger',
        default => 'warning',
    };
};
?>

<section class="container-fluid px-0">
  <?php $configFlowEmpresa = $configFlowEmpresa ?? []; ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h1 class="h4 mb-1">Checkout Flow</h1>
      <p class="text-muted mb-0">Crea links de pago de Flow.cl para cobrar a tus clientes desde el panel.</p>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <strong>Configuración API Flow (tu empresa)</strong>
    </div>
    <div class="card-body">
      <form method="POST" action="<?= e(url('/app/pagos/checkout-flow/configuracion')) ?>" class="row g-3">
        <?= csrf_campo() ?>
        <div class="col-md-4">
          <label class="form-label">API Key</label>
          <input type="text" name="api_key" class="form-control" required value="<?= e((string) ($configFlowEmpresa['api_key'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Secret Key</label>
          <input type="password" name="secret_key" class="form-control" placeholder="<?= !empty($configFlowEmpresa['secret_key_enc']) ? '•••••••• (dejar vacío para mantener)' : '' ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Entorno</label>
          <select name="entorno" class="form-select">
            <?php $entorno = (string) ($configFlowEmpresa['entorno'] ?? 'sandbox'); ?>
            <option value="sandbox" <?= $entorno === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
            <option value="produccion" <?= $entorno === 'produccion' ? 'selected' : '' ?>>Producción</option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="flowActivoEmpresa" name="activo" value="1" <?= (int) ($configFlowEmpresa['activo'] ?? 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="flowActivoEmpresa">Activo</label>
          </div>
        </div>
        <div class="col-12 d-flex justify-content-end">
          <button class="btn btn-outline-primary" type="submit">Guardar configuración</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <strong>Nuevo checkout</strong>
    </div>
    <div class="card-body">
      <form method="POST" action="<?= e(url('/app/pagos/checkout-flow/crear')) ?>" class="row g-3">
        <?= csrf_campo() ?>
        <div class="col-md-3">
          <label class="form-label">Monto (CLP)</label>
          <input type="number" min="1" step="1" name="monto" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Descripción</label>
          <input type="text" name="descripcion" class="form-control" maxlength="255" required placeholder="Ej: Cobro de anticipo OC-1001">
        </div>
        <div class="col-md-3">
          <label class="form-label">Correo pagador (opcional)</label>
          <input type="email" name="correo" class="form-control" maxlength="180" placeholder="cliente@empresa.cl">
        </div>
        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Crear link de pago</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <strong>Checkouts recientes</strong>
      <span class="small text-muted">Se guardan en tu sesión actual</span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Descripción</th>
              <th class="text-end">Monto</th>
              <th>Estado</th>
              <th>Link de pago</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($checkoutItems)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Aún no has creado links de pago.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($checkoutItems as $item): ?>
                <?php $estado = (string) ($item['estado'] ?? 'pendiente'); ?>
                <tr>
                  <td><?= e((string) ($item['fecha'] ?? '')) ?></td>
                  <td><?= e((string) ($item['descripcion'] ?? '')) ?></td>
                  <td class="text-end">$<?= number_format((float) ($item['monto'] ?? 0), 0, ',', '.') ?></td>
                  <td><span class="badge text-bg-<?= $estadoBadge($estado) ?>"><?= e(ucfirst($estado)) ?></span></td>
                  <td>
                    <a href="<?= e((string) ($item['url_pago'] ?? '#')) ?>" target="_blank" rel="noopener">
                      Abrir checkout
                    </a>
                  </td>
                  <td class="text-end">
                    <form method="POST" action="<?= e(url('/app/pagos/checkout-flow/sincronizar')) ?>" class="d-inline">
                      <?= csrf_campo() ?>
                      <input type="hidden" name="token" value="<?= e((string) ($item['token'] ?? '')) ?>">
                      <button type="submit" class="btn btn-outline-secondary btn-sm">Actualizar estado</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
