<?php
$decimalesMonto = max(0, min(6, (int) ($configuracion['cantidad_decimales'] ?? 2)));
$monedaPos = (string) ($configuracion['moneda'] ?? 'CLP');
$simboloMoneda = match ($monedaPos) {
  'USD' => 'US$',
  'EU' => '€',
  default => '$',
};
$fmon = static fn(float $monto): string => $simboloMoneda . ' ' . number_format($monto, $decimalesMonto);
$nombreTipoMovimiento = static function (string $tipo): string {
  return match ($tipo) {
    'ingreso_manual' => 'Ingreso manual',
    'egreso_manual' => 'Egreso manual',
    'ingreso_venta' => 'Ingreso por venta',
    default => ucwords(str_replace('_', ' ', $tipo)),
  };
};
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Movimientos de caja</h1><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta')) ?>">Volver</a></div>
<div class="card mb-3">
  <div class="card-body">
    <h2 class="h6 mb-3">Registrar ingreso o egreso</h2>
    <?php if (!empty($apertura)): ?>
      <p class="text-muted small mb-3">Caja abierta: <strong><?= e($apertura['caja_nombre'] ?? '') ?></strong> (<?= e($apertura['caja_codigo'] ?? '') ?>)</p>
      <form method="POST" action="<?= e(url('/app/punto-venta/movimientos')) ?>" class="row g-2">
        <?= csrf_campo() ?>
        <input type="hidden" name="retorno" value="<?= e($_SERVER['REQUEST_URI'] ?? '/app/punto-venta/movimientos') ?>">
        <div class="col-md-3">
          <label class="form-label">Tipo</label>
          <select class="form-select" name="tipo_movimiento" required>
            <option value="ingreso">Ingreso</option>
            <option value="egreso">Egreso</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Monto</label>
          <input type="number" class="form-control" name="monto" min="0.01" step="0.01" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Comentario</label>
          <input type="text" class="form-control" name="comentario" maxlength="255" placeholder="Ej: Pago de proveedor, retiro de caja, etc." required>
        </div>
        <div class="col-12">
          <button class="btn btn-primary btn-sm">Guardar movimiento</button>
        </div>
      </form>
    <?php else: ?>
      <div class="alert alert-warning mb-0">No tienes una caja abierta. Abre caja para registrar ingresos o egresos manuales.</div>
    <?php endif; ?>
  </div>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Tabla: Movimientos de caja POS</span>
    <a href="<?= e(url('/app/punto-venta/movimientos/exportar/excel')) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
  </div>
  <div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Caja</th><th>Nombre</th><th>Tipo</th><th>Concepto</th><th>Monto</th><th>Usuario</th></tr></thead><tbody><?php foreach ($movimientos as $mov): ?><tr><td><?= e($mov['fecha_movimiento']) ?></td><td><?= e($mov['caja_nombre']) ?></td><td><?= e($mov['concepto']) ?></td><td><?= e($nombreTipoMovimiento((string) ($mov['tipo_movimiento'] ?? ''))) ?></td><td><?= e($mov['concepto']) ?></td><td><?= e($fmon((float) $mov['monto'])) ?></td><td><?= e($mov['usuario_nombre'] ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
