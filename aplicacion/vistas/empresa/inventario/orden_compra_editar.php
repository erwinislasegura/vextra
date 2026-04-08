<h1 class="h4 mb-3">Editar orden de compra <?= e($orden['numero'] ?? '') ?></h1>

<form method="POST" action="<?= e(url('/app/inventario/ordenes-compra/editar/' . (int) $orden['id'])) ?>" class="d-grid gap-3">
  <?= csrf_campo() ?>
  <div class="card">
    <div class="card-header">Datos de orden de compra</div>
    <div class="card-body row g-2">
      <div class="col-md-3"><label class="small">Número</label><input name="numero" class="form-control" value="<?= e($orden['numero'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="small">Proveedor</label><select name="proveedor_id" class="form-select" required><option value="">Seleccionar...</option><?php foreach($proveedores as $pr): ?><option value="<?= (int)$pr['id'] ?>" <?= (int) ($orden['proveedor_id'] ?? 0) === (int) $pr['id'] ? 'selected' : '' ?>><?= e($pr['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><label class="small">Fecha emisión</label><input type="date" name="fecha_emision" class="form-control" value="<?= e($orden['fecha_emision'] ?? date('Y-m-d')) ?>"></div>
      <div class="col-md-3"><label class="small">Entrega estimada</label><input type="date" name="fecha_entrega_estimada" class="form-control" value="<?= e($orden['fecha_entrega_estimada'] ?? date('Y-m-d')) ?>"></div>
      <div class="col-md-4"><label class="small">Referencia</label><input name="referencia" class="form-control" value="<?= e($orden['referencia'] ?? '') ?>"></div>
      <div class="col-md-8"><label class="small">Observación</label><input name="observacion" class="form-control" value="<?= e($orden['observacion'] ?? '') ?>"></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"><span>Detalle orden de compra</span><button type="button" class="btn btn-outline-primary btn-sm" id="btn-agregar-linea-oc">Agregar línea</button></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle"><thead><tr><th style="min-width:260px;">Producto</th><th>Cantidad</th><th>Costo unitario</th><th class="text-end">Subtotal</th><th></th></tr></thead><tbody id="cuerpo-detalle-oc"></tbody></table>
      </div>
      <div class="row mt-2"><div class="col-md-4 ms-auto"><ul class="list-group"><li class="list-group-item d-flex justify-content-between"><span>Total OC</span><strong id="total-oc">$0.00</strong></li></ul></div></div>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary btn-sm" name="accion" value="guardar">Guardar sin salir</button>
    <button class="btn btn-success btn-sm" name="accion" value="guardar_salir">Guardar y salir</button>
    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/inventario/ordenes-compra/pdf/' . (int) $orden['id'])) ?>">Descargar PDF</a>
    <button class="btn btn-outline-success btn-sm" formmethod="post" formaction="<?= e(url('/app/inventario/ordenes-compra/enviar/' . (int) $orden['id'])) ?>" onclick="return confirm('¿Enviar orden de compra por correo al proveedor?')">Enviar por correo</button>
    <a href="<?= e(url('/app/inventario/ordenes-compra')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
  </div>
</form>

<template id="template-linea-oc">
  <tr>
    <td><select name="producto_id[]" class="form-select form-select-sm js-prod"><option value="">Seleccionar...</option><?php foreach($productos as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e(($p['codigo'] ?? '') . ' · ' . ($p['nombre'] ?? '')) ?></option><?php endforeach; ?></select></td>
    <td><input type="number" step="0.01" min="0" name="cantidad[]" class="form-control form-control-sm js-cant" value="1"></td>
    <td><input type="number" step="0.01" min="0" name="costo_unitario[]" class="form-control form-control-sm js-cost" value="0"></td>
    <td class="text-end js-sub">$0.00</td>
    <td><button type="button" class="btn btn-outline-danger btn-sm js-del">×</button></td>
  </tr>
</template>

<script>
(function(){
  const body = document.getElementById('cuerpo-detalle-oc');
  const tpl = document.getElementById('template-linea-oc');
  const btn = document.getElementById('btn-agregar-linea-oc');
  const totalEl = document.getElementById('total-oc');
  const detalles = <?= json_encode($orden['detalles'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const money = (n) => '$' + Number(n || 0).toLocaleString('es-CL', {minimumFractionDigits:2, maximumFractionDigits:2});

  function calc(){
    let t = 0;
    body.querySelectorAll('tr').forEach((tr) => {
      const q = Number(tr.querySelector('.js-cant').value || 0);
      const c = Number(tr.querySelector('.js-cost').value || 0);
      const s = q * c;
      tr.querySelector('.js-sub').textContent = money(s);
      if (tr.querySelector('.js-prod').value && q > 0) t += s;
    });
    totalEl.textContent = money(t);
  }

  function add(linea){
    const frag = tpl.content.cloneNode(true);
    const tr = frag.querySelector('tr');
    if (linea) {
      tr.querySelector('.js-prod').value = String(linea.producto_id || '');
      tr.querySelector('.js-cant').value = Number(linea.cantidad || 0).toFixed(2);
      tr.querySelector('.js-cost').value = Number(linea.costo_unitario || 0).toFixed(2);
    }
    tr.querySelectorAll('input,select').forEach((el) => el.addEventListener('input', calc));
    tr.querySelector('.js-del').addEventListener('click', () => { tr.remove(); calc(); });
    body.appendChild(tr);
    calc();
  }

  btn.addEventListener('click', () => add(null));
  if (Array.isArray(detalles) && detalles.length > 0) {
    detalles.forEach((d) => add(d));
  } else {
    add(null);
  }
})();
</script>
