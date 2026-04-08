<h1 class="h4 mb-3">Detalle orden de compra <?= e($orden['numero']) ?></h1>
<div class="card mb-3"><div class="card-body row g-2">
  <div class="col-md-3"><strong>Proveedor</strong><div><?= e($orden['proveedor_nombre'] ?? '-') ?></div></div>
  <div class="col-md-2"><strong>Estado</strong><div><?= e($orden['estado']) ?></div></div>
  <div class="col-md-2"><strong>Emisión</strong><div><?= e($orden['fecha_emision']) ?></div></div>
  <div class="col-md-2"><strong>Entrega estimada</strong><div><?= e($orden['fecha_entrega_estimada']) ?></div></div>
  <div class="col-md-3"><strong>Usuario</strong><div><?= e($orden['usuario_nombre'] ?? '-') ?></div></div>
  <div class="col-md-4"><strong>Referencia</strong><div><?= e($orden['referencia'] ?? '-') ?></div></div>
  <div class="col-md-8"><strong>Observación</strong><div><?= e($orden['observacion'] ?? '-') ?></div></div>
</div></div>

<div class="card mb-3"><div class="card-header">Detalle solicitado</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Producto</th><th>Cantidad</th><th>Costo</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach($orden['detalles'] as $d): ?><tr><td><?= e(($d['codigo'] ?? '') . ' · ' . ($d['nombre'] ?? '')) ?></td><td><?= number_format((float)$d['cantidad'],2) ?></td><td>$<?= number_format((float)$d['costo_unitario'],2) ?></td><td>$<?= number_format((float)$d['subtotal'],2) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>

<div class="d-flex gap-2">
  <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/inventario/ordenes-compra/editar/' . $orden['id'])) ?>">Editar</a>
  <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/inventario/ordenes-compra/pdf/' . $orden['id'])) ?>">Descargar PDF</a>
  <form method="POST" action="<?= e(url('/app/inventario/ordenes-compra/enviar/' . $orden['id'])) ?>" class="d-inline"><?= csrf_campo() ?><button class="btn btn-outline-success btn-sm" onclick="return confirm('¿Enviar orden por correo al proveedor?')">Enviar por correo</button></form>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/app/inventario/recepciones?orden_compra_id=' . $orden['id'])) ?>">Recepcionar esta orden</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/inventario/ordenes-compra')) ?>">Volver</a>
</div>
