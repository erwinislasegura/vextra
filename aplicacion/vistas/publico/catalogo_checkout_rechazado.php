<?php $comprador = is_array($orden['comprador'] ?? null) ? $orden['comprador'] : []; ?>
<section class="py-5">
  <div class="container" style="max-width:760px;">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-2">Pago no aprobado</h1>
        <p class="text-muted mb-3">Empresa: <strong><?= e((string) ($empresa['nombre_comercial'] ?? '')) ?></strong></p>
        <div class="alert alert-danger">⚠️ Tu pago fue rechazado o anulado. Puedes volver al catálogo e intentarlo nuevamente.</div>

        <?php if (is_array($orden) && !empty($orden['items'])): ?>
          <h2 class="h6 mt-4">Detalle de compra</h2>
          <ul class="list-group mb-3">
            <?php foreach ($orden['items'] as $item): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span><?= e((string) ($item['nombre'] ?? $item['producto_nombre'] ?? 'Producto')) ?> x<?= (int) ($item['cantidad'] ?? 1) ?></span>
                <strong>$<?= number_format((float) ($item['subtotal'] ?? 0), 0, ',', '.') ?></strong>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="text-end fw-bold mb-3">Total: $<?= number_format((float) ($orden['total'] ?? 0), 0, ',', '.') ?></div>
        <?php endif; ?>

        <?php if ($comprador !== []): ?>
          <h2 class="h6 mt-4">Datos personales y de envío</h2>
          <div class="table-responsive mb-3">
            <table class="table table-sm align-middle mb-0">
              <tbody>
                <tr><th class="text-muted" style="width:34%;">Nombre</th><td><?= e((string) ($comprador['nombre'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Correo</th><td><?= e((string) ($comprador['correo'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Teléfono</th><td><?= e((string) ($comprador['telefono'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Método de envío</th><td><?= e((string) ($comprador['envio_metodo'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Dirección</th><td><?= e((string) ($comprador['direccion'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Comuna / Ciudad</th><td><?= e(trim((string) (($comprador['comuna'] ?? '') . ' / ' . ($comprador['ciudad'] ?? '')), ' /')) ?></td></tr>
                <tr><th class="text-muted">Región</th><td><?= e((string) ($comprador['region'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Referencia</th><td><?= e((string) ($comprador['referencia'] ?? '-')) ?></td></tr>
              </tbody>
            </table>
          </div>
          <div class="alert alert-info small">Condiciones de envío: envío por pagar con plazo máximo de 48 horas hábiles desde la confirmación del pago.</div>
        <?php endif; ?>

        <?php if ($token !== ''): ?><div class="small text-muted mb-3">Token Flow: <?= e($token) ?></div><?php endif; ?>
        <a class="btn btn-primary" href="<?= e(url('/catalogo/' . (int) ($empresa['id'] ?? 0))) ?>">Volver al catálogo</a>
      </div>
    </div>
  </div>
</section>
