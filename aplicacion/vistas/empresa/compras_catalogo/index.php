<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$estadoActual = (string) ($estado ?? '');
$estados = ['' => 'Todas', 'pendiente' => 'Pendientes', 'aprobado' => 'Aprobadas', 'rechazado' => 'Rechazadas', 'anulado' => 'Anuladas'];
$formatearEnvio = static function (string $metodo): string {
    return match ($metodo) {
        'blue_express' => 'Blue Express',
        'chile_express' => 'Chile Express',
        default => 'Starken',
    };
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Compras por catálogo</h1>
</div>

<form class="row g-2 mb-3" method="GET">
  <div class="col-auto">
    <select name="estado" class="form-select" onchange="this.form.submit()">
      <?php foreach ($estados as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= $estadoActual === $key ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead><tr><th>#</th><th>Datos personales</th><th>Datos de envío</th><th>Total</th><th>Estado pago</th><th>Fecha</th><th>Detalle</th></tr></thead>
      <tbody>
      <?php if ($compras === []): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Sin compras en este estado.</td></tr>
      <?php endif; ?>
      <?php foreach ($compras as $compra): ?>
        <tr>
          <td><?= (int) $compra['id'] ?></td>
          <td>
            <strong><?= e((string) ($compra['comprador_nombre'] ?? '')) ?></strong>
            <small class="text-muted d-block">Correo: <?= e((string) ($compra['comprador_correo'] ?? '-')) ?></small>
            <small class="text-muted d-block">Teléfono: <?= e((string) ($compra['comprador_telefono'] ?? '-')) ?></small>
            <small class="text-muted d-block">Documento: <?= e((string) (($compra['comprador_documento'] ?? '') !== '' ? $compra['comprador_documento'] : '-')) ?></small>
            <small class="text-muted d-block">Empresa: <?= e((string) (($compra['comprador_empresa'] ?? '') !== '' ? $compra['comprador_empresa'] : '-')) ?></small>
          </td>
          <td>
            <small class="d-block"><strong>Método:</strong> <?= e($formatearEnvio((string) ($compra['envio_metodo'] ?? 'starken'))) ?></small>
            <small class="d-block"><strong>Dirección:</strong> <?= e((string) ($compra['envio_direccion'] ?? '-')) ?></small>
            <small class="d-block"><strong>Referencia:</strong> <?= e((string) (($compra['envio_referencia'] ?? '') !== '' ? $compra['envio_referencia'] : '-')) ?></small>
            <small class="d-block"><strong>Comuna / Ciudad:</strong> <?= e((string) ($compra['envio_comuna'] ?? '-')) ?> / <?= e((string) ($compra['envio_ciudad'] ?? '-')) ?></small>
            <small class="d-block"><strong>Región:</strong> <?= e((string) ($compra['envio_region'] ?? '-')) ?></small>
          </td>
          <td><?= e($fmon((float) ($compra['total'] ?? 0))) ?></td>
          <td><span class="badge text-bg-<?= ($compra['estado_pago'] ?? '') === 'aprobado' ? 'success' : 'warning' ?>"><?= e((string) ($compra['estado_pago'] ?? 'pendiente')) ?></span></td>
          <td><small><?= e((string) ($compra['fecha_creacion'] ?? '')) ?></small></td>
          <td>
            <details>
              <summary>Ver items (<?= (int) ($compra['total_items'] ?? 0) ?>)</summary>
              <ul class="mb-0 mt-2 small">
                <?php foreach (($compra['items'] ?? []) as $item): ?>
                  <li><?= e((string) ($item['producto_nombre'] ?? 'Producto')) ?> x <?= (int) ($item['cantidad'] ?? 1) ?></li>
                <?php endforeach; ?>
              </ul>
            </details>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
