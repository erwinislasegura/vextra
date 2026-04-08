<?php
$estado = (string) ($orden['estado'] ?? 'emitida');
$estadoBadge = [
    'emitida' => 'warning',
    'parcial' => 'info',
    'recibida' => 'success',
    'anulada' => 'danger',
][$estado] ?? 'secondary';
?>

<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h1 class="h4 mb-0">Orden de compra pública <?= e($orden['numero'] ?? '') ?></h1>
      <small class="text-muted">Documento compartido por proveedor</small>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/orden-compra/publica/' . $token . '/imprimir')) ?>" target="_blank" rel="noopener">Ver / imprimir orden</a>
      <a class="btn btn-primary btn-sm" href="<?= e(url('/orden-compra/publica/' . $token . '/imprimir?modo=pdf')) ?>" target="_blank" rel="noopener">Descargar PDF</a>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6"><strong>Proveedor:</strong> <?= e($orden['proveedor_nombre'] ?? '') ?></div>
        <div class="col-md-3"><strong>Emisión:</strong> <?= e($orden['fecha_emision'] ?? '') ?></div>
        <div class="col-md-3"><strong>Entrega:</strong> <?= e($orden['fecha_entrega_estimada'] ?? '') ?></div>
        <div class="col-md-6"><strong>Correo proveedor:</strong> <?= e($orden['proveedor_correo'] ?? '') ?></div>
        <div class="col-md-6"><strong>Estado:</strong> <span class="badge text-bg-<?= e($estadoBadge) ?>"><?= e(ucfirst($estado)) ?></span></div>
        <div class="col-md-12"><strong>Referencia:</strong> <?= e($orden['referencia'] ?? '—') ?></div>
      </div>

      <div class="table-responsive mb-3">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Producto</th>
              <th class="text-end">Cantidad</th>
              <th class="text-end">Costo</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($orden['detalles'] ?? []) as $item): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= e($item['nombre'] ?? '') ?></div>
                  <small class="text-muted">Código: <?= e($item['codigo'] ?? '') ?></small>
                </td>
                <td class="text-end"><?= e(number_format((float) ($item['cantidad'] ?? 0), 2)) ?></td>
                <td class="text-end">$<?= e(number_format((float) ($item['costo_unitario'] ?? 0), 2)) ?></td>
                <td class="text-end">$<?= e(number_format((float) ($item['subtotal'] ?? 0), 2)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="border rounded p-3 bg-light-subtle">
        <div class="d-flex justify-content-between h5 mb-0"><span>Total orden</span><strong>$<?= e(number_format((float) ($orden['total'] ?? 0), 2)) ?></strong></div>
      </div>

      <div class="mt-3">
        <div><strong>Observaciones:</strong><br><?= nl2br(e((string) ($orden['observacion'] ?? 'Sin observaciones'))) ?></div>
      </div>
    </div>
  </div>
</div>
