<h1 class="h4 mb-3">Órdenes de compra</h1>
<?php
$filtrosListado = $filtros ?? [];
$queryFiltros = http_build_query([
  'q' => (string) ($filtrosListado['q'] ?? ''),
  'estado' => (string) ($filtrosListado['estado'] ?? ''),
]);
?>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Flujo comercial integrado con inventario</div>
  <ul class="mb-0 small ps-3">
    <li>Crea órdenes de compra para formalizar reposiciones a proveedor.</li>
    <li>Luego recepciona desde la orden para mantener trazabilidad entre compra e inventario.</li>
    <li>El estado de la orden se actualiza automáticamente según lo recepcionado.</li>
  </ul>
</div>

<form method="POST" action="<?= e(url('/app/inventario/ordenes-compra')) ?>" class="d-grid gap-3">
  <?= csrf_campo() ?>
  <div class="card">
    <div class="card-header">Nueva orden de compra</div>
    <div class="card-body row g-2">
      <div class="col-md-3"><label class="small">Número</label><input name="numero" class="form-control" value="<?= e($siguienteNumero) ?>"></div>
      <div class="col-md-4"><label class="small">Proveedor</label><select name="proveedor_id" class="form-select" required><option value="">Seleccionar...</option><?php foreach($proveedores as $pr): ?><option value="<?= (int)$pr['id'] ?>"><?= e($pr['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><label class="small">Fecha emisión</label><input type="date" name="fecha_emision" class="form-control" value="<?= e(date('Y-m-d')) ?>"></div>
      <div class="col-md-3"><label class="small">Entrega estimada</label><input type="date" name="fecha_entrega_estimada" class="form-control" value="<?= e(date('Y-m-d', strtotime('+7 days'))) ?>"></div>
      <div class="col-md-4"><label class="small">Referencia</label><input name="referencia" class="form-control" placeholder="OC interna / contrato"></div>
      <div class="col-md-8"><label class="small">Observación</label><input name="observacion" class="form-control"></div>
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
    <button class="btn btn-primary btn-sm" name="accion" value="guardar_salir">Guardar</button>
    <button class="btn btn-outline-success btn-sm" type="button" onclick="alert('Guarda la orden para poder enviarla por correo al proveedor.')">Enviar por correo</button>
    <button class="btn btn-outline-dark btn-sm" type="button" onclick="alert('Guarda la orden para descargar el PDF.')">Descargar PDF</button>
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

<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Historial de órdenes</span>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <a href="<?= e(url('/app/inventario/ordenes-compra/exportar/excel?' . $queryFiltros)) ?>"
         class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>"
         style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>">
        <?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?>
      </a>
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" value="<?= e((string) ($filtrosListado['q'] ?? '')) ?>" class="form-control form-control-sm" placeholder="Buscar orden..." style="min-width: 360px;">
        <select name="estado" class="form-select form-select-sm" style="min-width: 220px;">
          <option value="">Todos los estados</option>
          <option value="emitida" <?= ($filtrosListado['estado'] ?? '') === 'emitida' ? 'selected' : '' ?>>Emitida</option>
          <option value="parcial" <?= ($filtrosListado['estado'] ?? '') === 'parcial' ? 'selected' : '' ?>>Parcial</option>
          <option value="recepcionada" <?= ($filtrosListado['estado'] ?? '') === 'recepcionada' ? 'selected' : '' ?>>Recepcionada</option>
          <option value="cancelada" <?= ($filtrosListado['estado'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
      </form>
    </div>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-sm mb-0">
      <thead>
        <tr>
          <th>Número</th><th>Proveedor</th><th>Emisión</th><th>Entrega est.</th><th>Estado</th><th>Usuario</th><th class="text-end">Acción</th>
        </tr>
      </thead>
      <tbody>
<?php if(empty($ordenes)): ?><tr><td colspan="7" class="text-center text-muted py-3">Sin órdenes de compra registradas.</td></tr><?php else: foreach($ordenes as $o): ?><tr><td><?= e($o['numero']) ?></td><td><?= e($o['proveedor_nombre'] ?? '-') ?></td><td><?= e($o['fecha_emision']) ?></td><td><?= e($o['fecha_entrega_estimada']) ?></td><td><?= e($o['estado']) ?></td><td><?= e($o['usuario_nombre'] ?? '-') ?></td><td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= e(url('/app/inventario/ordenes-compra/ver/' . $o['id'])) ?>">Ver</a></li><li><a class="dropdown-item" href="<?= e(url('/app/inventario/ordenes-compra/editar/' . $o['id'])) ?>">Editar</a></li><li><a class="dropdown-item" href="<?= e(url('/app/inventario/ordenes-compra/pdf/' . $o['id'])) ?>">PDF</a></li></ul></div></td></tr><?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  const body = document.getElementById('cuerpo-detalle-oc');
  const tpl = document.getElementById('template-linea-oc');
  const btn = document.getElementById('btn-agregar-linea-oc');
  const totalEl = document.getElementById('total-oc');
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

  function add(){
    const frag = tpl.content.cloneNode(true);
    const tr = frag.querySelector('tr');
    tr.querySelectorAll('input,select').forEach((el) => el.addEventListener('input', calc));
    tr.querySelector('.js-del').addEventListener('click', () => { tr.remove(); calc(); });
    body.appendChild(tr);
    calc();
  }

  btn.addEventListener('click', add);
  add();
})();
</script>
