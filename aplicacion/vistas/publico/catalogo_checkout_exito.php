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
              <li class="list-group-item d-flex justify-content-between">
                <span><?= e((string) ($item['nombre'] ?? 'Producto')) ?> x<?= (int) ($item['cantidad'] ?? 1) ?></span>
                <strong>$<?= number_format((float) ($item['subtotal'] ?? 0), 0, ',', '.') ?></strong>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="text-end fw-bold mb-3">Total: $<?= number_format((float) ($orden['total'] ?? 0), 0, ',', '.') ?></div>
        <?php endif; ?>

        <?php if ($token !== ''): ?><div class="small text-muted mb-3">Token Flow: <?= e($token) ?></div><?php endif; ?>
        <a class="btn btn-primary" href="<?= e(url('/catalogo/' . (int) ($empresa['id'] ?? 0))) ?>">Volver al catálogo</a>
      </div>
    </div>
  </div>
</section>
