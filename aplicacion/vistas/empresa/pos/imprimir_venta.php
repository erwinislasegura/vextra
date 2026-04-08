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

<div class="ticket-wrap">
  <div class="ticket" id="boucher_pago">
    <div class="ticket-head">
      <strong>COMPROBANTE POS</strong>
      <span>N° <?= e($venta['numero_venta']) ?></span>
      <span><?= e($venta['fecha_venta']) ?></span>
    </div>

    <div class="ticket-row"><span>Caja</span><strong><?= e($venta['caja_nombre']) ?></strong></div>
    <div class="ticket-row"><span>Cajero</span><strong><?= e($venta['cajero'] ?? '') ?></strong></div>
    <div class="ticket-row"><span>Cliente</span><strong><?= e($venta['cliente_nombre']) ?></strong></div>

    <hr>

    <?php foreach ($venta['items'] as $item): ?>
      <div class="ticket-linea">
        <span><?= e($item['nombre_producto']) ?> x <?= number_format((float) $item['cantidad'], $decimalesMonto) ?></span>
        <strong><?= e($fmon((float) $item['total'])) ?></strong>
      </div>
    <?php endforeach; ?>

    <hr>

    <div class="ticket-row"><span>Subtotal</span><strong><?= e($fmon((float) $venta['subtotal'])) ?></strong></div>
    <div class="ticket-row"><span>Descuento</span><strong><?= e($fmon((float) $venta['descuento'])) ?></strong></div>
    <div class="ticket-row"><span>Impuesto</span><strong><?= e($fmon((float) $venta['impuesto'])) ?></strong></div>
    <div class="ticket-row total"><span>Total</span><strong><?= e($fmon((float) $venta['total'])) ?></strong></div>

    <hr>

    <?php foreach ($venta['pagos'] as $pago): ?>
      <div class="ticket-row"><span><?= e(ucfirst($pago['metodo_pago'])) ?></span><strong><?= e($fmon((float) $pago['monto'])) ?></strong></div>
    <?php endforeach; ?>
    <div class="ticket-row"><span>Efectivo recibido</span><strong><?= e($fmon((float) $venta['monto_recibido'])) ?></strong></div>
    <div class="ticket-row"><span>Vuelto</span><strong><?= e($fmon((float) $venta['vuelto'])) ?></strong></div>

    <p class="ticket-gracias">Gracias por su compra</p>
  </div>
</div>

<style>
  @page { size: 80mm auto; margin: 4mm; }
  body { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; }
  .ticket-wrap { display: flex; justify-content: center; }
  .ticket { width: 80mm; font-size: 12px; }
  .ticket-head { text-align: center; display: flex; flex-direction: column; gap: 2px; margin-bottom: 8px; }
  .ticket-row, .ticket-linea { display: flex; justify-content: space-between; gap: 10px; margin: 2px 0; }
  .ticket-linea span { max-width: 60%; }
  .ticket-row.total { font-size: 14px; margin-top: 4px; }
  .ticket-gracias { text-align: center; margin: 12px 0 0; }
</style>

<script>
  window.addEventListener('load', () => {
    const volverPos = <?= isset($_GET['retorno_pos']) && $_GET['retorno_pos'] === '1' ? 'true' : 'false' ?>;
    if (volverPos) {
      window.addEventListener('afterprint', () => {
        window.location.href = '<?= e(url('/app/punto-venta')) ?>';
      }, { once: true });
    }
    window.print();
  });
</script>
