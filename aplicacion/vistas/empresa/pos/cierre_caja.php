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
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Cierre de caja</h1><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta')) ?>">Volver al POS</a></div>

<?php if (!$apertura): ?>
  <div class="alert alert-warning">No tienes caja abierta para cerrar.</div>
<?php else: ?>
  <?php $esperado = (float) $apertura['monto_inicial'] + (float) ($resumen['total_ventas'] ?? 0); ?>
  <div class="card mb-3"><div class="card-body">
    <h2 class="h6">Arqueo básico</h2>
    <ul class="small mb-3">
      <li>Monto inicial: <strong><?= e($fmon((float) $apertura['monto_inicial'])) ?></strong></li>
      <li>Ventas efectivo: <strong><?= e($fmon((float) ($resumen['efectivo'] ?? 0))) ?></strong></li>
      <li>Ventas transferencia: <strong><?= e($fmon((float) ($resumen['transferencia'] ?? 0))) ?></strong></li>
      <li>Ventas tarjeta: <strong><?= e($fmon((float) ($resumen['tarjeta'] ?? 0))) ?></strong></li>
      <li>Total esperado: <strong><?= e($fmon((float) $esperado)) ?></strong></li>
    </ul>
    <form method="POST" class="row g-2" action="<?= e(url('/app/punto-venta/cierre-caja')) ?>"><?= csrf_campo() ?>
      <div class="col-md-4"><label class="form-label">Monto contado</label><input class="form-control" type="number" step="0.01" min="0" name="monto_contado" required></div>
      <div class="col-md-8"><label class="form-label">Observación de cierre</label><input class="form-control" name="observacion"></div>
      <div class="col-12"><button class="btn btn-danger">Cerrar caja</button></div>
    </form>
  </div></div>
<?php endif; ?>

<div class="card"><div class="card-body"><h2 class="h6">Historial de cierres</h2>
<div class="table-responsive"><table class="table table-sm"><thead><tr><th>#</th><th>Caja</th><th>Fecha cierre</th><th>Esperado</th><th>Contado</th><th>Diferencia</th></tr></thead><tbody>
<?php foreach ($historialCierres as $cierre): ?>
<tr><td><?= (int) $cierre['id'] ?></td><td><?= e($cierre['caja_nombre']) ?></td><td><?= e($cierre['fecha_cierre']) ?></td><td><?= e($fmon((float) $cierre['monto_esperado'])) ?></td><td><?= e($fmon((float) $cierre['monto_contado'])) ?></td><td class="<?= (float)$cierre['diferencia'] < 0 ? 'text-danger':'text-success' ?>"><?= e($fmon((float) $cierre['diferencia'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
