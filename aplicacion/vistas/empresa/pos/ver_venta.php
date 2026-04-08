<?php
$autoImprimir = isset($_GET['imprimir']) && $_GET['imprimir'] === '1';
$decimalesMonto = max(0, min(6, (int) ($configuracion['cantidad_decimales'] ?? 2)));
$monedaPos = (string) ($configuracion['moneda'] ?? 'CLP');
$simboloMoneda = match ($monedaPos) {
  'USD' => 'US$',
  'EU' => '€',
  default => '$',
};
$fmon = static fn(float $monto): string => $simboloMoneda . ' ' . number_format($monto, $decimalesMonto);
?>
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <h1 class="h5 mb-0">Boucher de pago <?= e($venta['numero_venta']) ?></h1>
  <div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta/ventas')) ?>">Volver</a>
    <a class="btn btn-primary btn-sm" target="_blank" href="<?= e(url('/app/punto-venta/ventas/imprimir/' . (int) $venta['id'])) ?>">Imprimir</a>
  </div>
</div>

<div class="card mx-auto" style="max-width: 420px;">
  <div class="card-body small" id="boucher_pago">
    <div class="text-center mb-2">
      <strong>COMPROBANTE POS</strong><br>
      <span><?= e($venta['numero_venta']) ?></span><br>
      <span><?= e($venta['fecha_venta']) ?></span>
    </div>
    <div>Caja: <strong><?= e($venta['caja_nombre']) ?></strong></div>
    <div>Cajero: <strong><?= e($venta['cajero'] ?? '') ?></strong></div>
    <div>Cliente: <strong><?= e($venta['cliente_nombre']) ?></strong></div>
    <hr>
    <?php foreach ($venta['items'] as $item): ?>
      <div class="d-flex justify-content-between">
        <span><?= e($item['nombre_producto']) ?> x <?= number_format((float) $item['cantidad'], $decimalesMonto) ?></span>
        <strong><?= e($fmon((float) $item['total'])) ?></strong>
      </div>
    <?php endforeach; ?>
    <hr>
    <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= e($fmon((float) $venta['subtotal'])) ?></strong></div>
    <div class="d-flex justify-content-between"><span>Descuento</span><strong><?= e($fmon((float) $venta['descuento'])) ?></strong></div>
    <div class="d-flex justify-content-between"><span>Impuesto</span><strong><?= e($fmon((float) $venta['impuesto'])) ?></strong></div>
    <div class="d-flex justify-content-between fs-6"><span>Total</span><strong><?= e($fmon((float) $venta['total'])) ?></strong></div>
    <hr>
    <?php foreach ($venta['pagos'] as $pago): ?>
      <div class="d-flex justify-content-between"><span><?= e(ucfirst($pago['metodo_pago'])) ?></span><strong><?= e($fmon((float) $pago['monto'])) ?></strong></div>
    <?php endforeach; ?>
    <div class="d-flex justify-content-between"><span>Efectivo recibido</span><strong><?= e($fmon((float) $venta['monto_recibido'])) ?></strong></div>
    <div class="d-flex justify-content-between"><span>Vuelto</span><strong><?= e($fmon((float) $venta['vuelto'])) ?></strong></div>
    <div class="text-center mt-3">Gracias por su compra</div>
  </div>
</div>

<style>
@media print {
  .no-print { display: none !important; }
  body { background: #fff; }
  #boucher_pago { font-size: 12px; }
}
</style>

<?php if ($autoImprimir): ?>
<script>
  window.addEventListener('load', () => window.print());
</script>
<?php endif; ?>
