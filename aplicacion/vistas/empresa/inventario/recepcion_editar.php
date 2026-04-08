<h1 class="h4 mb-3">Editar recepción #<?= (int) $recepcion['id'] ?></h1>

<div class="alert alert-warning py-2 px-3 small">
  Para proteger la trazabilidad y el stock, aquí solo puedes actualizar los datos documentales de la recepción.
</div>

<form method="POST" action="<?= e(url('/app/inventario/recepciones/editar/' . (int) $recepcion['id'])) ?>" class="d-grid gap-3">
  <?= csrf_campo() ?>
  <div class="card">
    <div class="card-header">Datos de recepción</div>
    <div class="card-body row g-2">
      <div class="col-md-5"><label class="small">Proveedor</label><select name="proveedor_id" class="form-select"><option value="0">Sin proveedor</option><?php foreach($proveedores as $pr): ?><option value="<?= (int)$pr['id'] ?>" <?= (int) ($recepcion['proveedor_id'] ?? 0) === (int) $pr['id'] ? 'selected' : '' ?>><?= e($pr['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><label class="small">Tipo documento</label><select name="tipo_documento" class="form-select"><option value="guia_despacho" <?= ($recepcion['tipo_documento'] ?? '') === 'guia_despacho' ? 'selected' : '' ?>>Guía de despacho</option><option value="factura" <?= ($recepcion['tipo_documento'] ?? '') === 'factura' ? 'selected' : '' ?>>Factura</option></select></div>
      <div class="col-md-2"><label class="small">Número documento</label><input name="numero_documento" class="form-control" value="<?= e($recepcion['numero_documento'] ?? '') ?>" required></div>
      <div class="col-md-3"><label class="small">Fecha documento</label><input type="date" name="fecha_documento" class="form-control" value="<?= e($recepcion['fecha_documento'] ?? date('Y-m-d')) ?>"></div>
      <div class="col-md-4"><label class="small">Referencia interna</label><input name="referencia_interna" class="form-control" value="<?= e($recepcion['referencia_interna'] ?? '') ?>"></div>
      <div class="col-md-8"><label class="small">Observación</label><input name="observacion" class="form-control" value="<?= e($recepcion['observacion'] ?? '') ?>"></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Detalle recepcionado (solo lectura)</div>
    <div class="table-responsive">
      <table class="table table-sm mb-0"><thead><tr><th>Producto</th><th>Cantidad</th><th>Costo</th><th>Subtotal</th></tr></thead><tbody>
      <?php foreach(($recepcion['detalles'] ?? []) as $d): ?><tr><td><?= e(($d['codigo'] ?? '') . ' · ' . ($d['nombre'] ?? '')) ?></td><td><?= number_format((float)$d['cantidad'],2) ?></td><td>$<?= number_format((float)$d['costo_unitario'],2) ?></td><td>$<?= number_format((float)$d['subtotal'],2) ?></td></tr><?php endforeach; ?>
      </tbody></table>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary btn-sm" name="accion" value="guardar">Guardar sin salir</button>
    <button class="btn btn-success btn-sm" name="accion" value="guardar_salir">Guardar y salir</button>
    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/inventario/recepciones/pdf/' . (int) $recepcion['id'])) ?>">Descargar PDF</a>
    <a href="<?= e(url('/app/inventario/recepciones')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
  </div>
</form>
