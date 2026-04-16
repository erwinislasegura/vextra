<?php $comprador = is_array($orden['comprador'] ?? null) ? $orden['comprador'] : []; ?>
<section class="py-5">
  <div class="container" style="max-width:760px;">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-2">Estado de tu pago</h1>
        <p class="text-muted mb-3">Empresa: <strong><?= e((string) ($empresa['nombre_comercial'] ?? '')) ?></strong></p>
        <?php if ($estado === 'aprobado'): ?>
          <div class="alert alert-success">✅ Pago aprobado. Tu pedido fue recibido correctamente.</div>
        <?php elseif ($estado === 'rechazado' || $estado === 'anulado'): ?>
          <div class="alert alert-danger">⚠️ El pago no fue aprobado. Puedes volver al catálogo e intentarlo nuevamente.</div>
        <?php else: ?>
          <div class="alert alert-warning">⏳ Tu pago aún está en estado pendiente de confirmación.</div>
        <?php endif; ?>

        <?php if (is_array($orden) && !empty($orden['items'])): ?>
          <h2 class="h6 mt-4">Resumen</h2>
          <ul class="list-group mb-3">
            <?php foreach ($orden['items'] as $item): ?>
              <?php
                $metadata = is_string($item['metadata'] ?? null) ? json_decode((string) $item['metadata'], true) : [];
                if (!is_array($metadata)) {
                    $metadata = [];
                }
                $proximo = (int) ($item['proximo_catalogo'] ?? $metadata['proximo_catalogo'] ?? 0) === 1;
                $diasLlegada = max(0, (int) ($item['proximo_dias_catalogo'] ?? $metadata['proximo_dias_catalogo'] ?? 0));
              ?>
              <li class="list-group-item d-flex justify-content-between">
                <span>
                  <?= e((string) ($item['nombre'] ?? $item['producto_nombre'] ?? 'Producto')) ?> x<?= (int) ($item['cantidad'] ?? 1) ?>
                  <?php if ($proximo): ?><br><small class="text-warning-emphasis">Reserva · llegada estimada en <?= $diasLlegada ?> día(s).</small><?php endif; ?>
                </span>
                <strong>$<?= number_format((float) ($item['subtotal'] ?? 0), 0, ',', '.') ?></strong>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="text-end fw-bold mb-3">Total: $<?= number_format((float) ($orden['total'] ?? 0), 0, ',', '.') ?></div>
        <?php endif; ?>

        <?php if ($comprador !== []): ?>
          <h2 class="h6 mt-4">Datos del comprador</h2>
          <div class="table-responsive mb-3">
            <table class="table table-sm align-middle mb-0">
              <tbody>
                <tr><th class="text-muted" style="width:34%;">Nombre</th><td><?= e((string) ($comprador['nombre'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Correo</th><td><?= e((string) ($comprador['correo'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Teléfono</th><td><?= e((string) ($comprador['telefono'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Documento</th><td><?= e((string) ($comprador['documento'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Empresa</th><td><?= e((string) ($comprador['empresa'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Dirección</th><td><?= e((string) ($comprador['direccion'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Comuna / Ciudad</th><td><?= e(trim((string) (($comprador['comuna'] ?? '') . ' / ' . ($comprador['ciudad'] ?? '')), ' /')) ?></td></tr>
                <tr><th class="text-muted">Región</th><td><?= e((string) ($comprador['region'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted">Referencia</th><td><?= e((string) ($comprador['referencia'] ?? '-')) ?></td></tr>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <?php if ($token !== ''): ?><div class="small text-muted mb-3">Token Flow: <?= e($token) ?></div><?php endif; ?>
        <a class="btn btn-primary" href="<?= e(url('/catalogo/' . (int) ($empresa['id'] ?? 0))) ?>">Volver al catálogo</a>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  try {
    localStorage.removeItem('vextra_catalogo_carrito_<?= (int) ($empresa['id'] ?? 0) ?>');
  } catch (e) {}
})();
</script>
