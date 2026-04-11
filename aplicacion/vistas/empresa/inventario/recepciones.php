<?php
$hayProveedores = !empty($proveedores);
$hayProductos = !empty($productos);
$ordenCompraSeleccionada = $ordenCompraSeleccionada ?? null;
$filtrosListadoRecepciones = $filtrosRecepciones ?? [];
$queryFiltrosRecepciones = http_build_query([
  'q' => (string) ($filtrosListadoRecepciones['q'] ?? ''),
  'tipo_documento' => (string) ($filtrosListadoRecepciones['tipo_documento'] ?? ''),
]);
$etiquetasDocumento = [
  'guia_despacho' => 'Guía de despacho',
  'factura' => 'Factura',
];
?>
<h1 class="h4 mb-3">Recepciones de inventario</h1>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Guía rápida de recepción</div>
  <ul class="mb-0 small ps-3">
    <li>Selecciona proveedor y documento de respaldo para mantener trazabilidad.</li>
    <li>Agrega múltiples productos y cantidades en el detalle inferior.</li>
    <li>Al guardar, el sistema incrementa stock y registra movimientos automáticamente.</li>
  </ul>
</div>

<form method="POST" action="<?= e(url('/app/inventario/recepciones')) ?>" class="d-grid gap-3" id="form-recepcion">
  <?= csrf_campo() ?>
  <input type="hidden" name="orden_compra_id" id="orden_compra_id" value="<?= (int) ($ordenCompraSeleccionada['id'] ?? 0) ?>">
  <?php if ($ordenCompraSeleccionada): ?>
    <div class="alert alert-warning mb-0">
      Recepción asociada a orden de compra <strong><?= e($ordenCompraSeleccionada['numero'] ?? '') ?></strong>.
      Esta recepción actualizará el estado de la orden automáticamente.
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">Datos del documento</div>
    <div class="card-body row g-2">
      <div class="col-md-5">
        <label class="small">Proveedor</label>
        <div class="input-group">
          <select name="proveedor_id" class="form-select" id="proveedor_id">
            <option value="0"><?= $hayProveedores ? 'Seleccionar proveedor' : 'Sin proveedores registrados' ?></option>
            <?php foreach($proveedores as $pr): ?>
              <option value="<?= (int)$pr['id'] ?>" <?= (int)($ordenCompraSeleccionada['proveedor_id'] ?? 0) === (int)$pr['id'] ? 'selected' : '' ?>><?= e($pr['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalProveedor">+ Proveedor</button>
        </div>
      </div>
      <div class="col-md-2"><label class="small">Tipo documento</label><select name="tipo_documento" class="form-select"><option value="guia_despacho">Guía de despacho</option><option value="factura">Factura</option></select></div>
      <div class="col-md-2"><label class="small">Número documento</label><input name="numero_documento" class="form-control" value="<?= e((string)($ordenCompraSeleccionada['numero'] ?? '')) ?>" required></div>
      <div class="col-md-3"><label class="small">Fecha documento</label><input type="date" name="fecha_documento" class="form-control" value="<?= e(date('Y-m-d')) ?>" required></div>
      <div class="col-md-4"><label class="small">Referencia interna</label><input name="referencia_interna" class="form-control" placeholder="OC / Folio interno"></div>
      <div class="col-md-8"><label class="small">Observación</label><input name="observacion" class="form-control" placeholder="Observaciones de recepción"></div>
      <div class="col-12">
        <label class="small d-block">Datos de proveedor y productos desde orden de compra aprobada</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="switch-oc-aprobada" <?= $ordenCompraSeleccionada ? 'checked' : '' ?>>
          <label class="form-check-label" for="switch-oc-aprobada">Usar orden de compra aprobada</label>
        </div>
        <select id="selector-oc-aprobada" class="form-select mt-2 <?= $ordenCompraSeleccionada ? '' : 'd-none' ?>">
          <option value="">Seleccionar orden aprobada...</option>
          <?php foreach (($ordenesCompraAprobadas ?? []) as $ordenAprobada): ?>
            <option value="<?= (int) ($ordenAprobada['id'] ?? 0) ?>" <?= (int) ($ordenCompraSeleccionada['id'] ?? 0) === (int) ($ordenAprobada['id'] ?? 0) ? 'selected' : '' ?>>
              <?= e((string) ($ordenAprobada['numero'] ?? '')) ?> · <?= e((string) ($ordenAprobada['proveedor_nombre'] ?? 'Sin proveedor')) ?> · <?= e((string) ($ordenAprobada['fecha_emision'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 border-top pt-2">
        <div class="small fw-semibold mb-2">Proveedor rápido (si no seleccionas uno existente)</div>
      </div>
      <div class="col-md-3"><input name="proveedor_nuevo" class="form-control" placeholder="Nombre proveedor"></div>
      <div class="col-md-2"><input name="proveedor_identificador_fiscal" class="form-control" placeholder="RUT/NIT"></div>
      <div class="col-md-2"><input name="proveedor_contacto" class="form-control" placeholder="Contacto"></div>
      <div class="col-md-3"><input type="email" name="proveedor_correo" class="form-control" placeholder="correo@proveedor.com"></div>
      <div class="col-md-2"><input name="proveedor_telefono" class="form-control" placeholder="Teléfono"></div>
      <div class="col-md-4"><input name="proveedor_direccion" class="form-control" placeholder="Dirección"></div>
      <div class="col-md-3"><input name="proveedor_ciudad" class="form-control" placeholder="Ciudad"></div>
      <div class="col-md-5"><input name="proveedor_observacion" class="form-control" placeholder="Observación proveedor"></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Detalle de recepción</span>
      <button type="button" class="btn btn-outline-primary btn-sm" id="btn-agregar-linea">Agregar línea</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle" id="tabla-detalle">
          <thead>
            <tr><th style="min-width:260px;">Producto</th><th style="width:160px;">Cantidad</th><th style="width:180px;">Costo unitario</th><th class="text-end" style="width:180px;">Subtotal</th><th style="width:60px;"></th></tr>
          </thead>
          <tbody id="cuerpo-detalle"></tbody>
        </table>
      </div>
      <div class="row mt-2">
        <div class="col-md-4 ms-auto">
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between"><span>Total líneas</span><strong id="resumen-lineas">0</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Total recepción</span><strong id="resumen-total">$0.00</strong></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div>
    <button class="btn btn-primary btn-sm" name="accion" value="guardar_salir" <?= $hayProductos ? '' : 'disabled' ?>>Guardar</button>
    <button class="btn btn-outline-dark btn-sm" type="button" onclick="alert('Guarda la recepción para descargar el PDF.')">Descargar PDF</button>
    <a href="<?= e(url('/app/inventario/recepciones')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    <?php if (!$hayProductos): ?><span class="text-muted small ms-2">Debes tener productos activos para recepcionar.</span><?php endif; ?>
  </div>
</form>

<template id="fila-detalle-template">
  <tr>
    <td>
      <select name="producto_id[]" class="form-select form-select-sm js-producto">
        <option value="">Seleccionar producto...</option>
        <?php foreach($productos as $p): ?>
          <option value="<?= (int)$p['id'] ?>"><?= e(($p['codigo'] ?? '') . ' · ' . ($p['nombre'] ?? '')) ?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><input type="number" step="0.01" min="0" name="cantidad[]" class="form-control form-control-sm js-cantidad" value="1"></td>
    <td><input type="number" step="0.01" min="0" name="costo_unitario[]" class="form-control form-control-sm js-costo" value="0"></td>
    <td class="text-end js-subtotal">$0.00</td>
    <td><button type="button" class="btn btn-outline-danger btn-sm js-eliminar">×</button></td>
  </tr>
</template>

<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Historial de recepciones</span>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <a href="<?= e(url('/app/inventario/recepciones/exportar/excel?' . $queryFiltrosRecepciones)) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" value="<?= e((string) ($filtrosListadoRecepciones['q'] ?? '')) ?>" class="form-control form-control-sm" placeholder="Buscar recepción..." style="min-width: 360px;">
        <select name="tipo_documento" class="form-select form-select-sm" style="min-width: 220px;">
          <option value="">Todos los documentos</option>
          <option value="guia_despacho" <?= ($filtrosListadoRecepciones['tipo_documento'] ?? '') === 'guia_despacho' ? 'selected' : '' ?>>Guía de despacho</option>
          <option value="factura" <?= ($filtrosListadoRecepciones['tipo_documento'] ?? '') === 'factura' ? 'selected' : '' ?>>Factura</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
      </form>
    </div>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-sm mb-0">
      <thead>
        <tr><th>Fecha</th><th>Proveedor</th><th>Documento</th><th>Número</th><th>Usuario</th><th class="text-end">Acción</th></tr>
      </thead>
      <tbody>
<?php if(empty($recepciones)): ?><tr><td colspan="6" class="text-center text-muted py-3">Sin recepciones registradas.</td></tr><?php else: foreach($recepciones as $r): ?><tr><td><?= e($r['fecha_creacion']) ?></td><td><?= e($r['proveedor_nombre'] ?? 'Sin proveedor') ?></td><td><?= e($etiquetasDocumento[$r['tipo_documento'] ?? ''] ?? ($r['tipo_documento'] ?? '-')) ?></td><td><?= e($r['numero_documento']) ?></td><td><?= e($r['usuario_nombre'] ?? '-') ?></td><td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= e(url('/app/inventario/recepciones/ver/' . $r['id'])) ?>">Ver</a></li><li><a class="dropdown-item" href="<?= e(url('/app/inventario/recepciones/editar/' . $r['id'])) ?>">Editar</a></li><li><a class="dropdown-item" href="<?= e(url('/app/inventario/recepciones/pdf/' . $r['id'])) ?>">PDF</a></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="<?= e(url('/app/inventario/recepciones/eliminar/' . $r['id'])) ?>" onsubmit="return confirm('¿Eliminar completamente esta recepción y su detalle?')"><?= csrf_campo() ?><button type="submit" class="dropdown-item text-danger">Eliminar</button></form></li></ul></div></td></tr><?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Crear proveedor</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <form method="POST" action="<?= e(url('/app/inventario/proveedores')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="redirect_to" value="/app/inventario/recepciones">
      <div class="modal-body row g-2">
        <div class="col-md-4"><label class="form-label">Razón social</label><input class="form-control" name="razon_social" placeholder="Razón social" required></div>
        <div class="col-md-4"><label class="form-label">Nombre comercial</label><input class="form-control" name="nombre_comercial" placeholder="Nombre comercial"></div>
        <div class="col-md-4"><label class="form-label">Nombre contacto</label><input class="form-control" name="nombre_contacto" placeholder="Contacto"></div>
        <div class="col-md-3"><label class="form-label">RUT/NIT</label><input class="form-control" name="identificador_fiscal" placeholder="RUT/NIT"></div>
        <div class="col-md-3"><label class="form-label">Correo</label><input type="email" class="form-control" name="correo" placeholder="Correo"></div>
        <div class="col-md-3"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" placeholder="Teléfono"></div>
        <div class="col-md-3"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad" placeholder="Ciudad"></div>
        <div class="col-md-9"><label class="form-label">Dirección</label><input class="form-control" name="direccion" placeholder="Dirección"></div>
        <div class="col-md-3"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
        <div class="col-12"><label class="form-label">Observación</label><input class="form-control" name="observacion" placeholder="Observación"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary btn-sm">Guardar proveedor</button></div>
    </form>
  </div></div>
</div>

<script>
(function(){
  const ordenesCompraAprobadas = <?= json_encode($ordenesCompraAprobadas ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const cuerpo = document.getElementById('cuerpo-detalle');
  const template = document.getElementById('fila-detalle-template');
  const btn = document.getElementById('btn-agregar-linea');
  const resumenLineas = document.getElementById('resumen-lineas');
  const resumenTotal = document.getElementById('resumen-total');
  const switchOcAprobada = document.getElementById('switch-oc-aprobada');
  const selectorOcAprobada = document.getElementById('selector-oc-aprobada');
  const inputOrdenCompraId = document.getElementById('orden_compra_id');
  const proveedorSelect = document.getElementById('proveedor_id');
  const inputNumeroDocumento = document.querySelector('[name="numero_documento"]');
  const inputReferencia = document.querySelector('[name="referencia_interna"]');
  const inputObservacion = document.querySelector('[name="observacion"]');

  const money = (n) => '$' + Number(n || 0).toLocaleString('es-CL', {minimumFractionDigits:2, maximumFractionDigits:2});

  function recalcular() {
    let total = 0;
    let lineas = 0;
    cuerpo.querySelectorAll('tr').forEach((tr) => {
      const prod = tr.querySelector('.js-producto').value;
      const cant = Number(tr.querySelector('.js-cantidad').value || 0);
      const costo = Number(tr.querySelector('.js-costo').value || 0);
      const sub = cant * costo;
      tr.querySelector('.js-subtotal').textContent = money(sub);
      if (prod && cant > 0) {
        total += sub;
        lineas += 1;
      }
    });
    resumenLineas.textContent = String(lineas);
    resumenTotal.textContent = money(total);
  }

  function agregarFila(data = null) {
    const frag = template.content.cloneNode(true);
    const tr = frag.querySelector('tr');
    if (data) {
      tr.querySelector('.js-producto').value = String(data.producto_id || '');
      tr.querySelector('.js-cantidad').value = String(data.cantidad || 0);
      tr.querySelector('.js-costo').value = String(data.costo_unitario || 0);
    }
    tr.querySelectorAll('input,select').forEach((el) => el.addEventListener('input', recalcular));
    tr.querySelector('.js-eliminar').addEventListener('click', () => { tr.remove(); recalcular(); });
    cuerpo.appendChild(tr);
    recalcular();
  }

  function cargarOrdenAprobadaSeleccionada() {
    const ordenId = parseInt(selectorOcAprobada?.value || '0', 10);
    if (!ordenId) {
      if (inputOrdenCompraId) { inputOrdenCompraId.value = ''; }
      return;
    }
    const orden = ordenesCompraAprobadas.find((item) => parseInt(item.id || 0, 10) === ordenId);
    if (!orden) { return; }
    if (inputOrdenCompraId) { inputOrdenCompraId.value = String(ordenId); }
    if (proveedorSelect) { proveedorSelect.value = String(parseInt(orden.proveedor_id || 0, 10)); }
    if (inputNumeroDocumento && String(inputNumeroDocumento.value || '').trim() === '') {
      inputNumeroDocumento.value = String(orden.numero || '');
    }
    if (inputReferencia) { inputReferencia.value = String(orden.referencia || ''); }
    if (inputObservacion) { inputObservacion.value = String(orden.observacion || ''); }

    const detalles = Array.isArray(orden.detalles) ? orden.detalles : [];
    if (detalles.length > 0) {
      cuerpo.innerHTML = '';
      detalles.forEach((detalle) => agregarFila(detalle));
    }
  }

  btn.addEventListener('click', () => agregarFila());

  switchOcAprobada?.addEventListener('change', () => {
    if (!selectorOcAprobada) { return; }
    selectorOcAprobada.classList.toggle('d-none', !switchOcAprobada.checked);
    if (!switchOcAprobada.checked) {
      selectorOcAprobada.value = '';
      if (inputOrdenCompraId) { inputOrdenCompraId.value = ''; }
    }
  });
  selectorOcAprobada?.addEventListener('change', cargarOrdenAprobadaSeleccionada);

  const ordenInicial = parseInt(inputOrdenCompraId?.value || '0', 10);
  if (ordenInicial > 0) {
    const orden = ordenesCompraAprobadas.find((item) => parseInt(item.id || 0, 10) === ordenInicial);
    if (orden) {
      cuerpo.innerHTML = '';
      (Array.isArray(orden.detalles) ? orden.detalles : []).forEach((d) => agregarFila(d));
    } else {
      agregarFila();
    }
  } else {
    agregarFila();
  }
})();
</script>
