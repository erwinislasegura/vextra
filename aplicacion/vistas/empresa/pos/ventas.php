<?php
$decimalesMonto = max(0, min(6, (int) ($configuracion['cantidad_decimales'] ?? 2)));
$monedaPos = (string) ($configuracion['moneda'] ?? 'CLP');
$simboloMoneda = match ($monedaPos) {
  'USD' => 'US$',
  'EU' => '€',
  default => '$',
};
$fmon = static fn(float $monto): string => $simboloMoneda . ' ' . number_format($monto, $decimalesMonto);
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Historial de ventas POS</h1><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta')) ?>">Nueva venta</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Venta</th><th>Fecha</th><th>Caja</th><th>Cliente</th><th>Total</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($ventas as $venta): ?>
<tr><td><?= e($venta['numero_venta']) ?></td><td><?= e($venta['fecha_venta']) ?></td><td><?= e($venta['caja_nombre']) ?></td><td><?= e($venta['cliente_nombre']) ?></td><td><?= e($fmon((float) $venta['total'])) ?></td><td><a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/punto-venta/ventas/ver/' . $venta['id'])) ?>">Ver ticket</a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
