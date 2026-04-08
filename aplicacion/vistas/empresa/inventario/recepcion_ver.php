<h1 class="h4 mb-3">Detalle recepción #<?= (int)$recepcion['id'] ?></h1>
<div class="card mb-3"><div class="card-body row g-2">
<div class="col-md-3"><strong>Proveedor:</strong><div><?= e($recepcion['proveedor_nombre'] ?? '-') ?></div></div>
<div class="col-md-3"><strong>Documento:</strong><div><?= e($recepcion['tipo_documento']) ?> #<?= e($recepcion['numero_documento']) ?></div></div>
<div class="col-md-2"><strong>Fecha doc:</strong><div><?= e($recepcion['fecha_documento']) ?></div></div>
<div class="col-md-2"><strong>Usuario:</strong><div><?= e($recepcion['usuario_nombre'] ?? '-') ?></div></div>
<div class="col-md-2"><strong>Registro:</strong><div><?= e($recepcion['fecha_creacion']) ?></div></div>
<div class="col-12"><strong>Referencia:</strong> <?= e($recepcion['referencia_interna'] ?? '-') ?></div>
<div class="col-12"><strong>Observación:</strong> <?= e($recepcion['observacion'] ?? '-') ?></div>
</div></div>
<div class="card"><div class="card-header">Productos recepcionados</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Producto</th><th>Cantidad</th><th>Costo</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach($recepcion['detalles'] as $d): ?><tr><td><?= e(($d['codigo'] ?? '') . ' · ' . ($d['nombre'] ?? '')) ?></td><td><?= number_format((float)$d['cantidad'],2) ?></td><td>$<?= number_format((float)$d['costo_unitario'],2) ?></td><td>$<?= number_format((float)$d['subtotal'],2) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="mt-3 d-flex gap-2 flex-wrap">
  <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/inventario/recepciones/editar/' . $recepcion['id'])) ?>">Editar</a>
  <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/inventario/recepciones/pdf/' . $recepcion['id'])) ?>">Descargar PDF</a>
  <form method="POST" action="<?= e(url('/app/inventario/recepciones/enviar/' . $recepcion['id'])) ?>" class="d-inline"><?= csrf_campo() ?><button class="btn btn-outline-success btn-sm" onclick="return confirm('¿Enviar recepción por correo al proveedor?')">Enviar por correo</button></form>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/inventario/recepciones')) ?>">Volver</a>
</div>
