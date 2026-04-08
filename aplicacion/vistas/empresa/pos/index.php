<?php
$aperturaTexto = $apertura ? ($apertura['caja_nombre'] . ' · Apertura #' . $apertura['id']) : 'Caja cerrada';
$esperado = $apertura
  ? (float) $apertura['monto_inicial']
    + (float) ($resumenCierre['total_ventas'] ?? 0)
    + (float) ($resumenCierre['ingresos_manuales'] ?? 0)
    - (float) ($resumenCierre['egresos_manuales'] ?? 0)
  : 0;
$usarDecimales = (int) ($configuracion['usar_decimales'] ?? 1) === 1;
$cantidadDecimales = (int) ($configuracion['cantidad_decimales'] ?? 2);
$decimalesMonto = max(0, min(6, (int) ($configuracion['cantidad_decimales'] ?? 2)));
$monedaPos = (string) ($configuracion['moneda'] ?? 'CLP');
$simboloMoneda = match ($monedaPos) {
  'USD' => 'US$',
  'EU' => '€',
  default => '$',
};
$codigoMonedaJs = match ($monedaPos) {
  'USD' => 'USD',
  'EU' => 'EUR',
  default => 'CLP',
};
$fmon = static fn(float $monto): string => $simboloMoneda . ' ' . number_format($monto, $decimalesMonto);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h4 mb-0">Punto de venta</h1>
    <small class="text-muted">Estado de caja: <?= e($aperturaTexto) ?> · Usuario: <?= e(usuario_actual()['nombre'] ?? '') ?></small>
  </div>
  <div class="d-flex gap-2">
    <?php if ($apertura): ?>
      <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMovimientoCaja">Ingreso / egreso</button>
      <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCierreCaja">Cerrar caja</button>
    <?php else: ?>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAperturaCaja">Abrir caja</button>
    <?php endif; ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/punto-venta/ventas')) ?>">Historial</a>
  </div>
</div>

<?php if (!$apertura): ?>
  <div class="alert alert-warning">Para vender debes abrir una caja desde el botón <strong>Abrir caja</strong>.</div>
<?php endif; ?>

<form method="GET" class="row g-2 mb-3" id="form_busqueda_pos">
  <div class="col-md-6"><input class="form-control form-control-sm" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por nombre, código, SKU o barras"></div>
  <div class="col-md-4">
    <select class="form-select form-select-sm" name="categoria_id">
      <option value="">Todas las categorías</option>
      <?php foreach ($categorias as $categoria): ?>
        <option value="<?= (int) $categoria['id'] ?>" <?= (int) ($categoriaId ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre'] ?? '') ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-grid"><button class="btn btn-sm btn-outline-primary">Filtrar</button></div>
</form>

<form method="POST" action="<?= e(url('/app/punto-venta/venta/guardar')) ?>" id="form_pos" target="_blank">
  <?= csrf_campo() ?>
  <input type="hidden" name="tipo_venta" id="tipo_venta" value="rapida">
  <input type="hidden" name="cliente_id" id="cliente_id" value="">
  <input type="hidden" name="subtotal" id="input_subtotal" value="0">
  <input type="hidden" name="descuento" id="input_descuento" value="0">
  <input type="hidden" name="impuesto" id="input_impuesto" value="0">
  <input type="hidden" name="total" id="input_total" value="0">
  <input type="hidden" name="monto_recibido" id="input_recibido" value="0">
  <input type="hidden" name="vuelto" id="input_vuelto" value="0">
  <input type="hidden" name="items_json" id="items_json" value="[]">
  <input type="hidden" name="pagos_json" id="pagos_json" value="[]">

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6">Detalle de compra</h2>
          <div class="row g-2 mb-2">
            <div class="col-6"><select class="form-select form-select-sm" id="selector_tipo_venta"><option value="rapida">Venta rápida</option><option value="registrada">Cliente registrado</option></select></div>
            <div class="col-6"><select class="form-select form-select-sm" id="selector_cliente"><option value="">Consumidor final</option><?php foreach ($clientes as $cliente): ?><option value="<?= (int) $cliente['id'] ?>"><?= e(($cliente['razon_social'] ?: $cliente['nombre_comercial'] ?: $cliente['nombre'])) ?></option><?php endforeach; ?></select></div>
          </div>

          <div class="table-responsive" style="max-height: 340px; overflow:auto;">
            <table class="table table-sm align-middle">
              <thead><tr><th>Producto</th><th style="width:90px;">Cant.</th><th style="width:120px;">Precio</th><th style="width:190px;">Desc. línea</th><th style="width:140px;">Total línea</th><th style="width:50px;"></th></tr></thead>
              <tbody id="carrito_body"><tr><td colspan="6" class="text-muted">Sin productos agregados.</td></tr></tbody>
            </table>
          </div>

          <div class="border rounded p-2 bg-light small">
            <div class="row g-2 mb-2">
              <div class="col-md-4">
                <label class="form-label small mb-1">Descuento total</label>
                <select class="form-select form-select-sm" id="descuento_total_tipo">
                  <option value="monto">$</option>
                  <option value="porcentaje">%</option>
                </select>
              </div>
              <div class="col-md-8">
                <label class="form-label small mb-1">Valor descuento total</label>
                <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="descuento_total_valor" value="0">
              </div>
            </div>
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="txt_subtotal">$0.00</strong></div>
            <div class="d-flex justify-content-between"><span>Descuento</span><strong id="txt_descuento">$0.00</strong></div>
            <div class="d-flex justify-content-between"><span>Impuestos</span><strong id="txt_impuesto">$0.00</strong></div>
            <div class="d-flex justify-content-between fs-5"><span>Total</span><strong id="txt_total">$0.00</strong></div>
          </div>

          <div class="row g-2 mt-2">
            <div class="col-md-4"><select class="form-select form-select-sm" id="metodo_pago"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option></select></div>
            <div class="col-md-3"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="monto_pago" placeholder="Monto"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" id="referencia_pago" placeholder="Referencia"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary btn-sm w-100" type="button" id="agregar_pago">Agregar</button></div>
          </div>

          <div class="small mt-2" id="listado_pagos"></div>

          <div class="row g-2 mt-2">
            <div class="col-md-6">
              <label class="form-label small">Efectivo recibido (pagos)</label>
              <input class="form-control form-control-sm" id="monto_efectivo_recibido" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Vuelto</label>
              <input class="form-control form-control-sm" id="monto_vuelto" readonly>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-success" type="submit" <?= $apertura ? '' : 'disabled' ?>>Cobrar y emitir boucher</button>
            <button class="btn btn-outline-danger" type="button" id="cancelar_venta">Cancelar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6">Productos</h2>
          <div class="d-grid gap-2" style="max-height: 560px; overflow:auto;">
            <?php foreach ($productos as $producto): ?>
              <button type="button" class="btn btn-light border text-start py-2 js-producto" <?= $apertura ? '' : 'disabled' ?> data-id="<?= (int) $producto['id'] ?>" data-nombre="<?= e($producto['nombre']) ?>" data-codigo="<?= e($producto['codigo'] ?? '') ?>" data-precio="<?= e((string) ($producto['precio'] ?? 0)) ?>" data-impuesto="<?= e((string) ($producto['impuesto'] ?? 0)) ?>" data-stock="<?= e((string) ($producto['stock_actual'] ?? 0)) ?>" data-tipo="<?= e((string) ($producto['tipo'] ?? 'producto')) ?>">
                <div class="fw-semibold small"><?= e($producto['nombre']) ?></div>
                <div class="small text-muted">Cod: <?= e($producto['codigo'] ?? '') ?> · Stock: <?= e(number_format((float) ($producto['stock_actual'] ?? 0), 2)) ?></div>
                <div class="text-primary fw-bold small"><?= e($fmon((float) ($producto['precio'] ?? 0))) ?></div>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" id="modalAperturaCaja" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="<?= e(url('/app/punto-venta/apertura-caja')) ?>"><?= csrf_campo() ?><div class="modal-header"><h5 class="modal-title">Apertura de caja</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-12"><label class="form-label">Caja / terminal</label><select name="caja_id" class="form-select" required><option value="">Selecciona...</option><?php foreach ($cajas as $caja): ?><option value="<?= (int) $caja['id'] ?>"><?= e($caja['nombre']) ?> (<?= e($caja['codigo']) ?>)</option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Monto inicial</label><input class="form-control" type="number" step="0.01" min="0" name="monto_inicial" required></div><div class="col-12"><label class="form-label">Observación</label><input class="form-control" name="observacion" placeholder="Opcional"></div></div><div class="modal-footer"><button class="btn btn-primary">Abrir caja</button></div></form></div></div></div>

<div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="<?= e(url('/app/punto-venta/cierre-caja')) ?>"><?= csrf_campo() ?><div class="modal-header"><h5 class="modal-title">Cierre de caja</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if ($apertura): ?><ul class="small"><li>Monto inicial: <strong><?= e($fmon((float) $apertura['monto_inicial'])) ?></strong></li><li>Total ventas: <strong><?= e($fmon((float) ($resumenCierre['total_ventas'] ?? 0))) ?></strong></li><li>Ingresos manuales: <strong><?= e($fmon((float) ($resumenCierre['ingresos_manuales'] ?? 0))) ?></strong></li><li>Egresos manuales: <strong><?= e($fmon((float) ($resumenCierre['egresos_manuales'] ?? 0))) ?></strong></li><li>Ventas efectivo: <strong><?= e($fmon((float) ($resumenCierre['efectivo'] ?? 0))) ?></strong></li><li>Ventas transferencia: <strong><?= e($fmon((float) ($resumenCierre['transferencia'] ?? 0))) ?></strong></li><li>Ventas tarjeta: <strong><?= e($fmon((float) ($resumenCierre['tarjeta'] ?? 0))) ?></strong></li><li>Total esperado: <strong><?= e($fmon((float) $esperado)) ?></strong></li></ul><label class="form-label">Monto contado</label><input class="form-control mb-2" type="number" step="0.01" min="0" name="monto_contado" required><label class="form-label">Observación de cierre</label><input class="form-control" name="observacion"><?php else: ?><p class="text-muted">No hay caja abierta.</p><?php endif; ?></div><div class="modal-footer"><button class="btn btn-danger" <?= $apertura ? '' : 'disabled' ?>>Cerrar caja</button></div></form></div></div></div>
<div class="modal fade" id="modalMovimientoCaja" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="<?= e(url('/app/punto-venta/movimientos')) ?>"><?= csrf_campo() ?><input type="hidden" name="retorno" value="<?= e($_SERVER['REQUEST_URI'] ?? '/app/punto-venta') ?>"><div class="modal-header"><h5 class="modal-title">Registrar ingreso / egreso</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if ($apertura): ?><div class="mb-2 small text-muted">Caja actual: <?= e($apertura['caja_nombre'] ?? '') ?> (<?= e($apertura['caja_codigo'] ?? '') ?>)</div><label class="form-label">Tipo de movimiento</label><select class="form-select mb-2" name="tipo_movimiento" required><option value="ingreso">Ingreso</option><option value="egreso">Egreso</option></select><label class="form-label">Monto</label><input class="form-control mb-2" type="number" step="0.01" min="0.01" name="monto" required><label class="form-label">Comentario</label><input class="form-control" name="comentario" maxlength="255" required><?php else: ?><p class="text-muted">No hay caja abierta.</p><?php endif; ?></div><div class="modal-footer"><button class="btn btn-primary" <?= $apertura ? '' : 'disabled' ?>>Guardar movimiento</button></div></form></div></div></div>

<script>
(() => {
  const carrito = [];
  const pagos = [];
  const body = document.getElementById('carrito_body');
  const estadoKey = 'cotiza_pos_estado_v2';
  const impuestoDefault = Number('<?= e((string) ($configuracion['impuesto_por_defecto'] ?? 0)) ?>') || 0;
  const usarDecimales = Number('<?= $usarDecimales ? '1' : '0' ?>') === 1;
  const decRaw = parseInt('<?= e((string) $cantidadDecimales) ?>', 10);
  const decimales = usarDecimales ? (Number.isFinite(decRaw) ? Math.min(6, Math.max(0, decRaw)) : 2) : 0;
  const money = new Intl.NumberFormat('es-CL', { style: 'currency', currency: '<?= e($codigoMonedaJs) ?>', minimumFractionDigits: decimales, maximumFractionDigits: decimales });

  function storageSet(key, value) {
    try {
      sessionStorage.setItem(key, value);
      return;
    } catch (_) {}
    try {
      localStorage.setItem(key, value);
    } catch (_) {}
  }

  function storageGet(key) {
    try {
      const valor = sessionStorage.getItem(key);
      if (valor !== null) return valor;
    } catch (_) {}
    try {
      return localStorage.getItem(key);
    } catch (_) {
      return null;
    }
  }

  function storageRemove(key) {
    try { sessionStorage.removeItem(key); } catch (_) {}
    try { localStorage.removeItem(key); } catch (_) {}
  }

  function fmtNum(n) { return Number(n || 0).toFixed(decimales); }
  function fmtMoney(n) { return money.format(Number(n || 0)); }
  function pasoCantidad() { return usarDecimales ? '0.01' : '1'; }
  function guardarEstadoLocal() {
    const tipoVenta = document.getElementById('selector_tipo_venta').value || 'rapida';
    const clienteId = document.getElementById('selector_cliente').value || '';
    const descuentoTotalTipo = document.getElementById('descuento_total_tipo').value || 'monto';
    const descuentoTotalValor = Number(document.getElementById('descuento_total_valor').value || 0);
    const estado = {
      carrito,
      pagos,
      tipo_venta: tipoVenta,
      cliente_id: clienteId,
      descuento_total_tipo: descuentoTotalTipo,
      descuento_total_valor: descuentoTotalValor,
    };
    storageSet(estadoKey, JSON.stringify(estado));
  }

  function limpiarEstadoLocal() {
    storageRemove(estadoKey);
  }

  function restaurarEstadoLocal() {
    const raw = storageGet(estadoKey);
    if (!raw) return;
    try {
      const estado = JSON.parse(raw);
      if (Array.isArray(estado.carrito)) {
        carrito.push(...estado.carrito.map((item) => ({
          id: Number(item.id || 0),
          nombre: String(item.nombre || ''),
          codigo: String(item.codigo || ''),
          precio: Number(item.precio || 0),
          impuestoPct: Number(item.impuestoPct || impuestoDefault || 0),
          descuento: Number(item.descuento || 0),
          descuentoTipo: String(item.descuentoTipo || 'monto'),
          descuentoValor: Number(item.descuentoValor || 0),
          cantidad: Math.max(1, Number(item.cantidad || 1)),
          stock: Number(item.stock || 0),
          tipo: String(item.tipo || 'producto'),
        })).filter((item) => item.id > 0));
      }
      if (Array.isArray(estado.pagos)) {
        pagos.push(...estado.pagos.map((pago) => ({
          metodo_pago: String(pago.metodo_pago || 'efectivo'),
          monto: Math.max(0, Number(pago.monto || 0)),
          referencia: String(pago.referencia || ''),
        })).filter((pago) => pago.monto > 0));
      }
      const tipoVenta = estado.tipo_venta === 'registrada' ? 'registrada' : 'rapida';
      const selectorTipo = document.getElementById('selector_tipo_venta');
      selectorTipo.value = tipoVenta;
      document.getElementById('tipo_venta').value = tipoVenta;
      const selectorCliente = document.getElementById('selector_cliente');
      selectorCliente.value = String(estado.cliente_id || '');
      document.getElementById('cliente_id').value = selectorCliente.value;
      const descuentoTotalTipo = estado.descuento_total_tipo === 'porcentaje' ? 'porcentaje' : 'monto';
      document.getElementById('descuento_total_tipo').value = descuentoTotalTipo;
      document.getElementById('descuento_total_valor').value = Math.max(0, Number(estado.descuento_total_valor || 0)).toFixed(2);
    } catch (_) {
      limpiarEstadoLocal();
    }
  }

  function totalPagadoEfectivo() {
    return pagos.filter((p) => p.metodo_pago === 'efectivo').reduce((acc, p) => acc + Number(p.monto || 0), 0);
  }
  function totalPagadoNoEfectivo() {
    return pagos.filter((p) => p.metodo_pago !== 'efectivo').reduce((acc, p) => acc + Number(p.monto || 0), 0);
  }

  function calcularTotales() {
    let subtotal = 0;
    let impuesto = 0;
    let descuento = 0;

    carrito.forEach((i) => {
      const bruto = i.cantidad * i.precio;
      const descuentoLinea = i.descuentoTipo === 'porcentaje'
        ? (bruto * Math.max(0, Math.min(100, Number(i.descuentoValor || 0)))) / 100
        : Math.max(0, Number(i.descuentoValor || 0));
      const descuentoLineaAplicado = Math.min(bruto, descuentoLinea);
      const neto = Math.max(0, bruto - descuentoLineaAplicado);
      subtotal += bruto;
      impuesto += neto * (i.impuestoPct / 100);
      descuento += descuentoLineaAplicado;
      i.descuento = descuentoLineaAplicado;
    });

    const baseAntesDescuentoTotal = Math.max(0, subtotal - descuento);
    const descuentoTotalTipo = document.getElementById('descuento_total_tipo').value || 'monto';
    const descuentoTotalValor = Math.max(0, Number(document.getElementById('descuento_total_valor').value || 0));
    const descuentoTotal = descuentoTotalTipo === 'porcentaje'
      ? (baseAntesDescuentoTotal * Math.min(100, descuentoTotalValor)) / 100
      : descuentoTotalValor;
    const descuentoTotalAplicado = Math.min(baseAntesDescuentoTotal, descuentoTotal);
    const factorImpuesto = baseAntesDescuentoTotal > 0 ? (baseAntesDescuentoTotal - descuentoTotalAplicado) / baseAntesDescuentoTotal : 1;
    impuesto *= Math.max(0, factorImpuesto);
    descuento += descuentoTotalAplicado;

    const total = Math.max(0, subtotal - descuento + impuesto);

    document.getElementById('txt_subtotal').textContent = fmtMoney(subtotal);
    document.getElementById('txt_descuento').textContent = fmtMoney(descuento);
    document.getElementById('txt_impuesto').textContent = fmtMoney(impuesto);
    document.getElementById('txt_total').textContent = fmtMoney(total);

    document.getElementById('input_subtotal').value = subtotal.toFixed(2);
    document.getElementById('input_descuento').value = descuento.toFixed(2);
    document.getElementById('input_impuesto').value = impuesto.toFixed(2);
    document.getElementById('input_total').value = total.toFixed(2);

    const recibidoEfectivo = totalPagadoEfectivo();
    const pagadoNoEfectivo = totalPagadoNoEfectivo();
    const saldoPorEfectivo = Math.max(0, total - pagadoNoEfectivo);
    const vuelto = Math.max(0, recibidoEfectivo - saldoPorEfectivo);

    document.getElementById('monto_vuelto').value = fmtMoney(vuelto);
    document.getElementById('monto_efectivo_recibido').value = fmtMoney(recibidoEfectivo);
    document.getElementById('input_recibido').value = recibidoEfectivo.toFixed(2);
    document.getElementById('input_vuelto').value = vuelto.toFixed(2);

    document.getElementById('items_json').value = JSON.stringify(carrito.map((i) => ({
      producto_id: i.id,
      codigo_producto: i.codigo,
      nombre_producto: i.nombre,
      cantidad: Number(i.cantidad),
      precio_unitario: Number(i.precio),
      descuento: Number(i.descuento),
      descuento_tipo: i.descuentoTipo,
      descuento_valor: Number(i.descuentoValor),
      impuesto: ((i.cantidad * i.precio - i.descuento) * (i.impuestoPct / 100)),
      subtotal: i.cantidad * i.precio,
      total: (i.cantidad * i.precio - i.descuento) + ((i.cantidad * i.precio - i.descuento) * (i.impuestoPct / 100)),
    })));
    guardarEstadoLocal();
  }

  function pintarPagos() {
    const div = document.getElementById('listado_pagos');
    if (!pagos.length) {
      div.innerHTML = '<span class="text-muted">Sin pagos registrados.</span>';
      document.getElementById('pagos_json').value = '[]';
      calcularTotales();
      return;
    }

    div.innerHTML = pagos.map((p, idx) => `
      <div class="d-flex justify-content-between border rounded p-1 mb-1">
        <span>${p.metodo_pago}${p.referencia ? ' - ' + p.referencia : ''}</span>
        <span>
          <strong>${fmtMoney(p.monto)}</strong>
          <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" data-pago-idx="${idx}">Quitar</button>
        </span>
      </div>`).join('');

    document.getElementById('pagos_json').value = JSON.stringify(pagos);
    calcularTotales();
  }

  function render() {
    if (!carrito.length) {
      body.innerHTML = '<tr><td colspan="6" class="text-muted">Sin productos agregados.</td></tr>';
      calcularTotales();
      return;
    }

    body.innerHTML = '';
    carrito.forEach((item, idx) => {
      const row = document.createElement('tr');
      const netoLinea = calcularNetoLinea(item);
      const requiereStock = item.tipo !== 'servicio';
      const sinStock = requiereStock && Number(item.cantidad) > Number(item.stock);
      if (sinStock) row.classList.add('table-danger');
      row.innerHTML = `<td><div class="fw-semibold text-break">${item.nombre}</div><small class="${sinStock ? 'text-danger' : 'text-muted'} text-break">${item.codigo}${sinStock ? ' · Stock insuficiente' : ''}</small></td>
        <td><input class="form-control form-control-sm" type="number" min="1" step="${pasoCantidad()}" value="${fmtNum(item.cantidad)}" data-idx="${idx}" data-tipo="cantidad"></td>
        <td><input class="form-control form-control-sm" type="number" min="0" step="0.01" value="${Number(item.precio).toFixed(2)}" data-idx="${idx}" data-tipo="precio"></td>
        <td><div class="input-group input-group-sm" style="max-width:185px;"><select class="form-select px-1" style="max-width:62px;" data-idx="${idx}" data-tipo="descuento_tipo"><option value="monto" ${item.descuentoTipo === 'monto' ? 'selected' : ''}>$</option><option value="porcentaje" ${item.descuentoTipo === 'porcentaje' ? 'selected' : ''}>%</option></select><input class="form-control" type="number" min="0" step="0.01" value="${Number(item.descuentoValor || 0).toFixed(2)}" data-idx="${idx}" data-tipo="descuento_valor"></div></td>
        <td class="fw-bold" data-total-linea="${idx}">${fmtMoney(netoLinea)}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-idx="${idx}" data-tipo="eliminar">✕</button></td>`;
      body.appendChild(row);
    });

    calcularTotales();
  }

  function calcularNetoLinea(item) {
    const linea = Number(item.cantidad || 0) * Number(item.precio || 0);
    const descuentoLinea = item.descuentoTipo === 'porcentaje'
      ? (linea * Math.max(0, Math.min(100, Number(item.descuentoValor || 0)))) / 100
      : Math.max(0, Number(item.descuentoValor || 0));
    const descuentoLineaAplicado = Math.min(linea, descuentoLinea);
    return Math.max(0, linea - descuentoLineaAplicado);
  }

  function actualizarTotalLinea(idx) {
    if (!carrito[idx]) return;
    const celda = body.querySelector(`[data-total-linea="${idx}"]`);
    if (!celda) return;
    celda.textContent = fmtMoney(calcularNetoLinea(carrito[idx]));
  }

  document.querySelectorAll('.js-producto').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id);
      const existing = carrito.find((i) => i.id === id);
      if (existing) {
        existing.cantidad += 1;
      } else {
        carrito.push({
          id,
          nombre: btn.dataset.nombre,
          codigo: btn.dataset.codigo,
          precio: Number(btn.dataset.precio || 0),
          impuestoPct: Number(btn.dataset.impuesto || impuestoDefault || 0),
          descuento: 0,
          descuentoTipo: 'monto',
          descuentoValor: 0,
          cantidad: 1,
          stock: Number(btn.dataset.stock || 0),
          tipo: btn.dataset.tipo || 'producto',
        });
      }
      render();
    });
  });

  body.addEventListener('input', (e) => {
    const el = e.target;
    const idx = Number(el.dataset.idx);
    if (Number.isNaN(idx) || !carrito[idx]) return;
    if (el.dataset.tipo === 'cantidad') carrito[idx].cantidad = Math.max(1, Number(el.value || 1));
    if (el.dataset.tipo === 'precio') carrito[idx].precio = Math.max(0, Number(el.value || 0));
    if (el.dataset.tipo === 'descuento_valor') carrito[idx].descuentoValor = Math.max(0, Number(el.value || 0));
    actualizarTotalLinea(idx);
    calcularTotales();
  });

  body.addEventListener('change', (e) => {
    const el = e.target;
    const idx = Number(el.dataset.idx);
    if (Number.isNaN(idx) || !carrito[idx]) return;
    if (el.dataset.tipo === 'descuento_tipo') {
      carrito[idx].descuentoTipo = el.value === 'porcentaje' ? 'porcentaje' : 'monto';
      actualizarTotalLinea(idx);
      calcularTotales();
    }
  });

  body.addEventListener('click', (e) => {
    const idx = Number(e.target.dataset.idx);
    if (e.target.dataset.tipo === 'eliminar' && !Number.isNaN(idx)) {
      carrito.splice(idx, 1);
      render();
    }
  });

  document.getElementById('agregar_pago').addEventListener('click', () => {
    const monto = Number(document.getElementById('monto_pago').value || 0);
    if (monto <= 0) {
      alert('Ingresa un monto de pago mayor a 0.');
      return;
    }

    pagos.push({
      metodo_pago: document.getElementById('metodo_pago').value,
      monto,
      referencia: document.getElementById('referencia_pago').value.trim(),
    });

    document.getElementById('monto_pago').value = '';
    document.getElementById('referencia_pago').value = '';
    pintarPagos();
  });

  document.getElementById('listado_pagos').addEventListener('click', (e) => {
    const idx = Number(e.target.dataset.pagoIdx);
    if (Number.isNaN(idx)) return;
    pagos.splice(idx, 1);
    pintarPagos();
  });

  document.getElementById('selector_tipo_venta').addEventListener('change', (e) => { document.getElementById('tipo_venta').value = e.target.value; });
  document.getElementById('selector_tipo_venta').addEventListener('change', () => { guardarEstadoLocal(); });
  document.getElementById('selector_cliente').addEventListener('change', (e) => { document.getElementById('cliente_id').value = e.target.value; });
  document.getElementById('selector_cliente').addEventListener('change', () => { guardarEstadoLocal(); });
  document.getElementById('descuento_total_tipo').addEventListener('change', () => { calcularTotales(); });
  document.getElementById('descuento_total_valor').addEventListener('input', () => { calcularTotales(); });
  document.getElementById('form_busqueda_pos').addEventListener('submit', () => { guardarEstadoLocal(); });
  window.addEventListener('beforeunload', () => {
    if (carrito.length || pagos.length) guardarEstadoLocal();
  });

  document.getElementById('cancelar_venta').addEventListener('click', () => {
    carrito.splice(0, carrito.length);
    pagos.splice(0, pagos.length);
    limpiarEstadoLocal();
    document.getElementById('descuento_total_tipo').value = 'monto';
    document.getElementById('descuento_total_valor').value = '0';
    render();
    pintarPagos();
  });

  document.getElementById('form_pos').addEventListener('submit', (e) => {
    calcularTotales();
    if (!carrito.length) {
      e.preventDefault();
      alert('Debes cargar productos para finalizar la venta.');
      return;
    }
    if (!pagos.length) {
      e.preventDefault();
      alert('Debes registrar al menos un pago.');
      return;
    }
    document.getElementById('pagos_json').value = JSON.stringify(pagos);

    // Deja el formulario listo para registrar una nueva venta
    // mientras el boucher se abre/imprime en la nueva pestaña.
    setTimeout(() => {
      carrito.splice(0, carrito.length);
      pagos.splice(0, pagos.length);
      limpiarEstadoLocal();
      document.getElementById('selector_tipo_venta').value = 'rapida';
      document.getElementById('selector_cliente').value = '';
      document.getElementById('descuento_total_tipo').value = 'monto';
      document.getElementById('descuento_total_valor').value = '0';
      document.getElementById('tipo_venta').value = 'rapida';
      document.getElementById('cliente_id').value = '';
      document.getElementById('monto_pago').value = '';
      document.getElementById('referencia_pago').value = '';
      render();
      pintarPagos();
    }, 0);
  });

  restaurarEstadoLocal();
  render();
  pintarPagos();
})();
</script>
