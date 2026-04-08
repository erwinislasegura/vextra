<h1 class="h4 mb-3">Detalle ajuste #<?= (int)$ajuste['id'] ?></h1>
<div class="card"><div class="card-body row g-2">
<div class="col-md-4"><strong>Producto</strong><div><?= e(($ajuste['codigo'] ?? '') . ' · ' . ($ajuste['producto_nombre'] ?? '')) ?></div></div>
<div class="col-md-2"><strong>Tipo</strong><div><?= e($ajuste['tipo_ajuste']) ?></div></div>
<div class="col-md-2"><strong>Cantidad</strong><div><?= number_format((float)$ajuste['cantidad'],2) ?></div></div>
<div class="col-md-2"><strong>Usuario</strong><div><?= e($ajuste['usuario_nombre'] ?? '-') ?></div></div>
<div class="col-md-2"><strong>Fecha</strong><div><?= e($ajuste['fecha_creacion']) ?></div></div>
<div class="col-md-6"><strong>Motivo</strong><div><?= e($ajuste['motivo']) ?></div></div>
<div class="col-md-6"><strong>Stock actual producto</strong><div><?= number_format((float)$ajuste['stock_actual'],2) ?></div></div>
<div class="col-12"><strong>Observación</strong><div><?= e($ajuste['observacion'] ?? '-') ?></div></div>
</div></div>
