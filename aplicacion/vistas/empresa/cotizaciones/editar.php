<?php
$hayClientes = !empty($clientes);
$hayProductos = !empty($productos);
$puedeGuardar = $hayClientes;
$itemsExistentes = $cotizacion['items'] ?? [];
$descuentoTipoTotal = $cotizacion['descuento_tipo'] ?? 'valor';
$descuentoTotalValor = $cotizacion['descuento_valor'] ?? $cotizacion['descuento'] ?? 0;
$listaPrecioIdSeleccionada = (int) ($listaPrecioSeleccionada['id'] ?? 0);
$listaPrecioCotizacionId = (int) ($cotizacion['lista_precio_id'] ?? 0);
if ($listaPrecioCotizacionId > 0) {
    $listaPrecioIdSeleccionada = $listaPrecioCotizacionId;
}
?>
<h1 class="h4 mb-3">Editar cotización</h1>

<div class="small text-muted mb-3">
    Link generado para cliente:
    <a href="<?= e($linkAprobacionCliente ?? '') ?>" target="_blank" rel="noopener" class="text-decoration-none">ver enlace de aprobación</a>
    <button type="button" class="btn btn-link btn-sm p-0 ms-2 align-baseline" id="copiar_link_aprobacion_cliente">copiar</button>
    <input type="hidden" id="link_aprobacion_cliente" value="<?= e($linkAprobacionCliente ?? '') ?>">
</div>

<form method="POST" class="d-grid gap-3" id="form-cotizacion-editar">
    <?= csrf_campo() ?>

    <div class="card">
        <div class="card-header">Datos cotización</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="small">Número</label>
                <input class="form-control" value="<?= e($cotizacion['numero']) ?>" disabled>
            </div>

            <div class="col-md-5">
                <label class="small">Cliente</label>
                <div class="input-group">
                    <select class="form-select" name="cliente_id" id="cliente_id" required>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int) $cotizacion['cliente_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalCliente">Dato fijo cliente</button>
                </div>
            </div>
            <div class="col-12">
                <div class="border rounded p-2 bg-light" id="resumen_cliente">
                    <div class="small text-muted">Selecciona un cliente para ver su información.</div>
                </div>
            </div>

            <div class="col-md-2">
                <label class="small">Estado</label>
                <select class="form-select" name="estado">
                    <?php foreach (['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'] as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= $cotizacion['estado'] === $estado ? 'selected' : '' ?>><?= e($estado) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small">Lista de precios aplicada</label>
                <select class="form-select" name="lista_precio_id" id="lista_precio_id">
                    <option value="">No aplicar lista</option>
                    <?php foreach (($listasPrecios ?? []) as $lista): ?>
                        <option value="<?= (int) $lista['id'] ?>" <?= $listaPrecioIdSeleccionada === (int) $lista['id'] ? 'selected' : '' ?>><?= e($lista['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text" id="indicador_lista_estado">Selecciona cliente y lista para aplicar ajustes.</div>
            </div>

            <div class="col-md-3">
                <label class="small">Fecha emisión</label>
                <input class="form-control" type="date" name="fecha_emision" value="<?= e($cotizacion['fecha_emision']) ?>">
            </div>

            <div class="col-md-3">
                <label class="small">Fecha vencimiento</label>
                <input class="form-control" type="date" name="fecha_vencimiento" value="<?= e($cotizacion['fecha_vencimiento']) ?>">
            </div>

            <div class="col-md-6">
                <label class="small">Observaciones</label>
                <input class="form-control" name="observaciones" value="<?= e($cotizacion['observaciones'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label class="small">Términos</label>
                <input class="form-control" name="terminos_condiciones" value="<?= e($cotizacion['terminos_condiciones'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Detalle de cotización</span>
            <small class="text-muted">Precio y descuento se recalculan con la lista seleccionada (o sin lista).</small>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-agregar-linea">Agregar línea</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle" id="tabla-items">
                    <thead>
                    <tr>
                        <th style="min-width: 220px;">Producto / Servicio</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th style="min-width: 230px;">Lista / ajuste</th>
                        <th>Descuento</th>
                        <th>IVA %</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">IVA</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="cuerpo-items">
                    <?php foreach ($itemsExistentes as $item): ?>
                        <tr>
                            <td>
                                <div class="input-group input-group-sm">
                                    <select class="form-select js-producto" name="producto_id[]">
                                        <option value="">Seleccionar</option>
                                        <?php foreach ($productos as $p): ?>
                                            <option value="<?= $p['id'] ?>" data-nombre="<?= e($p['nombre']) ?>" data-descripcion="<?= e($p['descripcion'] ?? '') ?>" data-precio="<?= e((string) ($p['precio'] ?? 0)) ?>" data-impuesto="<?= e((string) ($p['impuesto'] ?? 0)) ?>" data-stock="<?= e((string) ($p['stock_actual'] ?? 0)) ?>" <?= (int) ($item['producto_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline-secondary js-editar-descripcion" type="button" title="Descripción" data-bs-toggle="modal" data-bs-target="#modalDescripcionItem">+</button>
                                </div>
                                <input type="hidden" class="js-descripcion" name="descripcion_item[]" value="<?= e($item['descripcion'] ?? '') ?>">
                            </td>
                            <td><input class="form-control form-control-sm js-cantidad" type="number" step="0.01" min="0" name="cantidad[]" value="<?= e((string) ($item['cantidad'] ?? 1)) ?>"></td>
                            <td><input class="form-control form-control-sm js-precio" type="number" step="0.01" min="0" name="precio_unitario[]" value="<?= e((string) ($item['precio_unitario'] ?? 0)) ?>"></td>
                            <td class="small text-muted js-lista-ajuste">Sin validar lista</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <select class="form-select js-descuento-tipo" name="descuento_tipo_item[]">
                                        <option value="valor" <?= ($item['descuento_tipo'] ?? 'valor') === 'valor' ? 'selected' : '' ?>>$</option>
                                        <option value="porcentaje" <?= ($item['descuento_tipo'] ?? '') === 'porcentaje' ? 'selected' : '' ?>>%</option>
                                    </select>
                                    <input class="form-control js-descuento-valor" type="number" step="0.01" min="0" name="descuento_item[]" value="<?= e((string) ($item['descuento_valor'] ?? 0)) ?>">
                                </div>
                            </td>
                            <td><input class="form-control form-control-sm js-iva" type="number" step="0.01" min="0" name="impuesto_item[]" value="<?= e((string) ($item['porcentaje_impuesto'] ?? 19)) ?>"></td>
                            <td class="text-end js-subtotal">$0.00</td>
                            <td class="text-end js-iva-total">$0.00</td>
                            <td class="text-end js-total">$0.00</td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm js-eliminar">×</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-4 ms-auto">
                    <label class="small">Descuento total</label>
                    <div class="input-group">
                        <select class="form-select" name="descuento_tipo_total" id="descuento_tipo_total">
                            <option value="valor" <?= $descuentoTipoTotal === 'valor' ? 'selected' : '' ?>>$</option>
                            <option value="porcentaje" <?= $descuentoTipoTotal === 'porcentaje' ? 'selected' : '' ?>>%</option>
                        </select>
                        <input class="form-control" type="number" step="0.01" min="0" name="descuento_total" id="descuento_total" value="<?= e((string) $descuentoTotalValor) ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 ms-auto">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong id="resumen_subtotal">$0.00</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>IVA</span><strong id="resumen_iva">$0.00</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Descuento total</span><strong id="resumen_descuento">$0.00</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Total</span><strong id="resumen_total">$0.00</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$puedeGuardar): ?>
        <div class="alert alert-warning mb-0">Debes crear al menos un cliente antes de guardar cambios.</div>
    <?php endif; ?>

    <div>
        <button class="btn btn-primary btn-sm" name="accion" value="guardar"<?= $puedeGuardar ? '' : ' disabled' ?>>Guardar sin salir</button>
        <button class="btn btn-success btn-sm" name="accion" value="guardar_salir"<?= $puedeGuardar ? '' : ' disabled' ?>>Guardar y salir</button>
        <?php if (plan_tiene_funcionalidad_empresa_actual('cotizacion_pdf')): ?>
          <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/cotizaciones/pdf/' . $cotizacion['id'])) ?>">Descargar PDF</a>
        <?php endif; ?>
        <?php if (plan_tiene_funcionalidad_empresa_actual('cotizacion_correo') && plan_tiene_funcionalidad_empresa_actual('cotizacion_pdf')): ?>
        <form method="POST" action="<?= e(url('/app/cotizaciones/enviar/' . $cotizacion['id'])) ?>" class="d-inline">
            <?= csrf_campo() ?>
            <button class="btn btn-warning btn-sm" type="submit">Enviar al cliente</button>
        </form>
        <?php endif; ?>
        <a href="<?= e(url('/app/cotizaciones')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>
</form>

<template id="fila-item-template">
    <tr>
        <td>
            <div class="input-group input-group-sm">
                <select class="form-select js-producto" name="producto_id[]">
                    <option value="">Seleccionar</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id'] ?>" data-nombre="<?= e($p['nombre']) ?>" data-descripcion="<?= e($p['descripcion'] ?? '') ?>" data-precio="<?= e((string) ($p['precio'] ?? 0)) ?>" data-impuesto="<?= e((string) ($p['impuesto'] ?? 0)) ?>" data-stock="<?= e((string) ($p['stock_actual'] ?? 0)) ?>"><?= e($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-secondary js-editar-descripcion" type="button" title="Descripción" data-bs-toggle="modal" data-bs-target="#modalDescripcionItem">+</button>
            </div>
            <input type="hidden" class="js-descripcion" name="descripcion_item[]" value="">
        </td>
        <td><input class="form-control form-control-sm js-cantidad" type="number" step="0.01" min="0" name="cantidad[]" value="1"></td>
        <td><input class="form-control form-control-sm js-precio" type="number" step="0.01" min="0" name="precio_unitario[]" value="0"></td>
        <td class="small text-muted js-lista-ajuste">Sin validar lista</td>
        <td>
            <div class="input-group input-group-sm">
                <select class="form-select js-descuento-tipo" name="descuento_tipo_item[]">
                    <option value="valor">$</option>
                    <option value="porcentaje">%</option>
                </select>
                <input class="form-control js-descuento-valor" type="number" step="0.01" min="0" name="descuento_item[]" value="0">
            </div>
        </td>
        <td><input class="form-control form-control-sm js-iva" type="number" step="0.01" min="0" name="impuesto_item[]" value="19"></td>
        <td class="text-end js-subtotal">$0.00</td>
        <td class="text-end js-iva-total">$0.00</td>
        <td class="text-end js-total">$0.00</td>
        <td><button type="button" class="btn btn-outline-danger btn-sm js-eliminar">×</button></td>
    </tr>
</template>

<div class="modal fade" id="modalDescripcionItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información del producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><span class="small text-muted">Nombre</span><div class="fw-semibold" id="info_producto_nombre">—</div></div>
                <div class="mb-2"><span class="small text-muted">Descripción</span><div id="info_producto_descripcion">—</div></div>
                <div class="row g-2">
                    <div class="col-6"><span class="small text-muted">Precio</span><div id="info_producto_precio">—</div></div>
                    <div class="col-6"><span class="small text-muted">Impuesto (%)</span><div id="info_producto_impuesto">—</div></div>
                </div>
                <div class="mt-2"><span class="small text-muted">Cantidad existente</span><div id="info_producto_stock">—</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-normal" id="btn-ver-movimientos" data-bs-toggle="modal" data-bs-target="#modalMovimientosProducto">Ver movimientos</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMovimientosProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Movimientos de inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-2" id="movimientos_producto_titulo">Selecciona un producto.</div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th><th>Tipo</th><th>Documento</th><th class="text-end">Entrada</th><th class="text-end">Salida</th><th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="movimientos_producto_body">
                            <tr><td colspan="6" class="text-center text-muted">Sin datos.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Crear cliente (dato fijo)</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><form method="POST" action="<?= e(url('/app/clientes/crear')) ?>"><?= csrf_campo() ?><input type="hidden" name="redirect_to" value="/app/cotizaciones/editar/<?= e((string) $cotizacion['id']) ?>"><div class="modal-body row g-2"><div class="col-md-4"><input class="form-control" name="nombre" placeholder="Nombre" required></div><div class="col-md-4"><input class="form-control" name="correo" placeholder="Correo"></div><div class="col-md-4"><input class="form-control" name="telefono" placeholder="Teléfono"></div><div class="col-md-6"><input class="form-control" name="direccion" placeholder="Dirección"></div><div class="col-md-6"><input class="form-control" name="notas" placeholder="Notas"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button><button class="btn btn-primary btn-sm">Guardar cliente</button></div></form></div></div></div>
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Crear producto (dato fijo)</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><form method="POST" action="<?= e(url('/app/productos/crear')) ?>"><?= csrf_campo() ?><input type="hidden" name="redirect_to" value="/app/cotizaciones/editar/<?= e((string) $cotizacion['id']) ?>"><div class="modal-body row g-2"><div class="col-md-3"><input class="form-control" name="codigo" placeholder="Código" required></div><div class="col-md-4"><input class="form-control" name="nombre" placeholder="Nombre" required></div><div class="col-md-5"><input class="form-control" name="descripcion" placeholder="Descripción"></div><div class="col-md-3"><input class="form-control" name="unidad" value="unidad"></div><div class="col-md-3"><input class="form-control" type="number" step="0.01" name="precio" placeholder="Precio"></div><div class="col-md-3"><input class="form-control" type="number" step="0.01" name="impuesto" value="19"></div><div class="col-md-3"><select name="estado" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button><button class="btn btn-primary btn-sm">Guardar producto</button></div></form></div></div></div>

<script>
(function () {
    const cuerpo = document.getElementById('cuerpo-items');
    const template = document.getElementById('fila-item-template');
    const btnAgregar = document.getElementById('btn-agregar-linea');
    const selectCliente = document.querySelector('[name="cliente_id"]');
    const selectLista = document.getElementById('lista_precio_id');
    const todasLasListas = <?= json_encode($listasPrecios ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const listasPorCliente = <?= json_encode($listasPreciosPorCliente ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const clientes = <?= json_encode($clientes ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const infoProductoNombre = document.getElementById('info_producto_nombre');
    const infoProductoDescripcion = document.getElementById('info_producto_descripcion');
    const infoProductoPrecio = document.getElementById('info_producto_precio');
    const infoProductoImpuesto = document.getElementById('info_producto_impuesto');
    const infoProductoStock = document.getElementById('info_producto_stock');
    const btnVerMovimientos = document.getElementById('btn-ver-movimientos');
    const movimientosProductoTitulo = document.getElementById('movimientos_producto_titulo');
    const movimientosProductoBody = document.getElementById('movimientos_producto_body');
    const etiquetasTipoMovimiento = {
        recepcion_proveedor: 'Recepción de proveedor',
        ajuste_entrada: 'Ajuste de entrada',
        ajuste_salida: 'Ajuste de salida'
    };

    function nombreTipoMovimiento(tipo) {
        const clave = String(tipo || '').trim();
        if (clave === '') { return '-'; }
        if (Object.prototype.hasOwnProperty.call(etiquetasTipoMovimiento, clave)) {
            return etiquetasTipoMovimiento[clave];
        }
        return clave.replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase());
    }

    function fmt(v) { return '$' + (Math.round((v + Number.EPSILON) * 100) / 100).toFixed(2); }
    function esc(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[c] || c));
    }
    document.addEventListener('click', (event) => {
        const boton = event.target.closest('.js-editar-descripcion');
        if (!boton) { return; }
        const fila = boton.closest('tr');
        const selectProductoFila = fila ? fila.querySelector('.js-producto') : null;
        const inputDescripcionFila = fila ? fila.querySelector('.js-descripcion') : null;
        const opcion = selectProductoFila ? selectProductoFila.options[selectProductoFila.selectedIndex] : null;
        if (!opcion || !opcion.value) {
            event.preventDefault();
            event.stopPropagation();
            alert('Selecciona un producto para ver su información.');
            return;
        }
        const nombre = (opcion?.dataset?.nombre || opcion?.textContent || '').trim();
        const descripcion = (opcion?.dataset?.descripcion || inputDescripcionFila?.value || '').trim();
        const precioRaw = (opcion?.dataset?.precio ?? fila?.querySelector('.js-precio')?.value ?? '0');
        const impuestoRaw = (opcion?.dataset?.impuesto ?? fila?.querySelector('.js-iva')?.value ?? '0');
        if (infoProductoNombre) { infoProductoNombre.textContent = nombre !== '' ? nombre : '—'; }
        if (infoProductoDescripcion) { infoProductoDescripcion.textContent = descripcion !== '' ? descripcion : '—'; }
        if (infoProductoPrecio) { infoProductoPrecio.textContent = fmt(parseFloat(precioRaw || '0')); }
        if (infoProductoImpuesto) { infoProductoImpuesto.textContent = String(impuestoRaw || '0') + '%'; }
        if (infoProductoStock) { infoProductoStock.textContent = String(opcion?.dataset?.stock || '0'); }
        if (btnVerMovimientos) { btnVerMovimientos.dataset.productoId = String(opcion.value || ''); btnVerMovimientos.dataset.productoNombre = nombre; }
    });
    if (btnVerMovimientos) {
        btnVerMovimientos.addEventListener('click', async function () {
            const productoId = parseInt(this.dataset.productoId || '0', 10);
            const productoNombre = this.dataset.productoNombre || 'Producto';
            if (!productoId || !movimientosProductoBody) { return; }
            movimientosProductoTitulo.textContent = 'Cargando movimientos de ' + productoNombre + '...';
            movimientosProductoBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>';
            try {
                const resp = await fetch('<?= e(url('/app/cotizaciones/producto-movimientos/')) ?>' + productoId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                if (!data.ok) { throw new Error(data.mensaje || 'No se pudo cargar'); }
                const stock = Number(data?.data?.producto?.stock_actual || 0);
                movimientosProductoTitulo.textContent = productoNombre + ' · Existencia actual: ' + stock.toFixed(2);
                const movimientos = Array.isArray(data?.data?.movimientos) ? data.data.movimientos : [];
                if (movimientos.length === 0) {
                    movimientosProductoBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Este producto no tiene movimientos.</td></tr>';
                    return;
                }
                movimientosProductoBody.innerHTML = movimientos.map((m) => '<tr>' +
                    '<td>' + esc(m.fecha_creacion || '-') + '</td>' +
                    '<td>' + esc(nombreTipoMovimiento(m.tipo_movimiento)) + '</td>' +
                    '<td>' + esc(m.documento_origen || '-') + '</td>' +
                    '<td class="text-end">' + Number(m.entrada || 0).toFixed(2) + '</td>' +
                    '<td class="text-end">' + Number(m.salida || 0).toFixed(2) + '</td>' +
                    '<td class="text-end">' + Number(m.saldo_resultante || 0).toFixed(2) + '</td>' +
                '</tr>').join('');
            } catch (error) {
                movimientosProductoTitulo.textContent = 'No se pudieron cargar los movimientos.';
                movimientosProductoBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar movimientos.</td></tr>';
            }
        });
    }

    function actualizarIndicadorLista() {
        const indicador = document.getElementById('indicador_lista_estado');
        if (!indicador) { return; }
        const filas = Array.from(cuerpo.querySelectorAll('tr'));
        if (filas.length === 0) {
            indicador.innerHTML = 'Sin líneas para validar lista de precios.';
            return;
        }
        if (!selectLista || !selectLista.value) {
            indicador.innerHTML = '<span style="color:#6c757d;">Sin lista de precios aplicada.</span>';
            return;
        }
        const aplicadas = filas.filter((fila) => fila.dataset.listaAplicada === 'si').length;
        if (aplicadas > 0) {
            indicador.innerHTML = `<span style="color:#3f8f62;">Lista aplicada en ${aplicadas} de ${filas.length} líneas.</span>`;
            return;
        }
        indicador.innerHTML = '<span style="color:#b94a48;">No aplica lista de precios en las líneas actuales.</span>';
    }
    function renderInfoLista(fila, data = null) {
        const celda = fila.querySelector('.js-lista-ajuste');
        if (!celda) { return; }
        if (!data) {
            fila.dataset.listaAplicada = 'no';
            celda.innerHTML = '<span style="color:#b94a48;">No aplica lista de precios.</span>';
            actualizarIndicadorLista();
            return;
        }

        const nombreLista = data.lista_precio_nombre || 'Lista';
        const tieneLista = !!data.lista_precio_id;
        const porcentaje = parseFloat(data.ajuste_porcentaje || '0');
        const tipo = data.ajuste_tipo === 'descuento' ? 'descuento' : 'incremento';
        const precioBase = parseFloat(data.precio_base || '0');
        const precioFinal = parseFloat(data.precio_final || '0');
        const montoAjuste = Math.abs(precioFinal - precioBase);

        if (!tieneLista) {
            fila.dataset.listaAplicada = 'no';
            celda.innerHTML = '<span style="color:#b94a48;">Sin lista para este cliente.</span>';
            actualizarIndicadorLista();
            return;
        }

        if (porcentaje <= 0) {
            fila.dataset.listaAplicada = 'si';
            celda.innerHTML = `<span class="badge text-bg-success mb-1">${nombreLista}</span><div style="color:#3f8f62;">Lista detectada y aplicada (sin ajuste porcentual).</div>`;
            actualizarIndicadorLista();
            return;
        }

        const esDescuento = tipo === 'descuento';
        const tipoBadge = esDescuento ? 'text-bg-success' : 'text-bg-warning';
        const signo = esDescuento ? '-' : '+';
        const colorSuave = esDescuento ? 'style="color:#3f8f62;"' : '';
        const etiqueta = esDescuento ? 'Descuento aplicado' : 'Incremento aplicado';
        fila.dataset.listaAplicada = 'si';
        celda.innerHTML = `<span class="badge ${tipoBadge} mb-1">${nombreLista}</span><div ${colorSuave}><strong>${etiqueta}</strong>: ${signo}${porcentaje.toFixed(2)}% (${signo}${fmt(montoAjuste)})</div><div>Base ${fmt(precioBase)} → Final ${fmt(precioFinal)}</div>`;
        actualizarIndicadorLista();
    }

    function aplicarPrecioBaseSinLista(fila, forzar = false) {
        const selectProducto = fila.querySelector('.js-producto');
        const inputPrecio = fila.querySelector('.js-precio');
        const selectDescuento = fila.querySelector('.js-descuento-tipo');
        const inputDescuento = fila.querySelector('.js-descuento-valor');
        const opcion = selectProducto ? selectProducto.options[selectProducto.selectedIndex] : null;
        const precioBase = parseFloat(opcion?.dataset?.precio || '0');

        if (inputPrecio && (forzar || parseFloat(inputPrecio.value || '0') <= 0)) {
            inputPrecio.value = String(Number.isFinite(precioBase) ? precioBase : 0);
        }
        if (forzar && selectDescuento && inputDescuento) {
            selectDescuento.value = 'valor';
            inputDescuento.value = '0';
        }
    }

    async function autocompletarPrecioDesdeLista(fila, forzar = false) {
        const selectProducto = fila.querySelector('.js-producto');
        const clienteId = selectCliente?.value || '';
        const listaPrecioId = selectLista?.value || '';
        if (!selectProducto || !selectProducto.value || !clienteId) {
            renderInfoLista(fila, null);
            aplicarPrecioBaseSinLista(fila, forzar);
            return;
        }
        try {
            const params = new URLSearchParams({ producto_id: selectProducto.value, cliente_id: clienteId, lista_precio_id: listaPrecioId });
            const resp = await fetch('<?= e(url('/app/listas-precios/precio-producto')) ?>?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await resp.json();
            if (data.ok && data.data && typeof data.data.precio_final !== 'undefined') {
                renderInfoLista(fila, data.data);
                const inputPrecio = fila.querySelector('.js-precio');
                const selectDescuento = fila.querySelector('.js-descuento-tipo');
                const inputDescuento = fila.querySelector('.js-descuento-valor');
                if (inputPrecio && (forzar || parseFloat(inputPrecio.value || '0') <= 0)) {
                    const ajusteTipo = data.data.ajuste_tipo || '';
                    const ajustePorcentaje = parseFloat(data.data.ajuste_porcentaje || '0');
                    if (ajusteTipo === 'descuento' && ajustePorcentaje > 0) {
                        inputPrecio.value = String(data.data.precio_base);
                        if (selectDescuento) { selectDescuento.value = 'porcentaje'; }
                        if (inputDescuento) { inputDescuento.value = String(ajustePorcentaje); }
                    } else {
                        inputPrecio.value = String(data.data.precio_final);
                        if (forzar && selectDescuento && inputDescuento) {
                            selectDescuento.value = 'valor';
                            inputDescuento.value = '0';
                        }
                    }
                }
            } else {
                renderInfoLista(fila, null);
                aplicarPrecioBaseSinLista(fila, forzar);
            }
        } catch (e) {
            renderInfoLista(fila, null);
            aplicarPrecioBaseSinLista(fila, forzar);
        }
    }
    function bindFila(fila) {
        fila.querySelector('.js-eliminar').addEventListener('click', () => {
            if (cuerpo.querySelectorAll('tr').length > 1) { fila.remove(); recalcular(); }
        });
        fila.querySelectorAll('input, select').forEach((c) => { c.addEventListener('input', recalcular); c.addEventListener('change', recalcular); });
        const selectProducto = fila.querySelector('.js-producto');
        const inputDescripcion = fila.querySelector('.js-descripcion');
        const btnEditarDescripcion = fila.querySelector('.js-editar-descripcion');
        const sincronizarBotonInfo = () => {
            const opcion = selectProducto ? selectProducto.options[selectProducto.selectedIndex] : null;
            const seleccionado = !!(opcion && opcion.value);
            if (!btnEditarDescripcion) { return; }
            btnEditarDescripcion.dataset.productoSeleccionado = seleccionado ? '1' : '0';
            btnEditarDescripcion.dataset.productoNombre = seleccionado ? (opcion.dataset.nombre || '—') : '—';
            btnEditarDescripcion.dataset.productoDescripcion = seleccionado ? (opcion.dataset.descripcion || '—') : '—';
            btnEditarDescripcion.dataset.productoPrecio = seleccionado ? fmt(parseFloat(opcion.dataset.precio || '0')) : '—';
            btnEditarDescripcion.dataset.productoImpuesto = seleccionado ? ((opcion.dataset.impuesto || '0') + '%') : '—';
        };
        if (selectProducto) {
            selectProducto.addEventListener('change', async () => {
                const opcion = selectProducto.options[selectProducto.selectedIndex];
                const detalleProducto = opcion?.dataset?.descripcion || opcion?.dataset?.nombre || '';
                if (inputDescripcion && inputDescripcion.value.trim() === '') {
                    inputDescripcion.value = detalleProducto;
                }
                sincronizarBotonInfo();
                await autocompletarPrecioDesdeLista(fila, true);
                recalcular();
            });
        }
        sincronizarBotonInfo();
    }
    function recalcular() {
        let subtotal = 0; let iva = 0;
        cuerpo.querySelectorAll('tr').forEach((fila) => {
            const cantidad = parseFloat(fila.querySelector('.js-cantidad').value || '0');
            const precio = parseFloat(fila.querySelector('.js-precio').value || '0');
            const ivaPct = parseFloat(fila.querySelector('.js-iva').value || '0');
            const tipo = fila.querySelector('.js-descuento-tipo').value;
            const valor = parseFloat(fila.querySelector('.js-descuento-valor').value || '0');
            const base = Math.max(0, cantidad) * Math.max(0, precio);
            const descuento = tipo === 'porcentaje' ? base * (Math.min(Math.max(valor, 0), 100) / 100) : Math.min(Math.max(valor, 0), base);
            const sub = Math.max(0, base - descuento);
            const ivaLinea = sub * (Math.max(0, ivaPct) / 100);
            const total = sub + ivaLinea;
            fila.querySelector('.js-subtotal').textContent = fmt(sub);
            fila.querySelector('.js-iva-total').textContent = fmt(ivaLinea);
            fila.querySelector('.js-total').textContent = fmt(total);
            subtotal += sub; iva += ivaLinea;
        });
        const tipoTotal = document.getElementById('descuento_tipo_total').value;
        const valorTotal = parseFloat(document.getElementById('descuento_total').value || '0');
        const baseTotal = subtotal + iva;
        const descTotal = tipoTotal === 'porcentaje' ? baseTotal * (Math.min(Math.max(valorTotal, 0), 100) / 100) : Math.min(Math.max(valorTotal, 0), baseTotal);
        document.getElementById('resumen_subtotal').textContent = fmt(subtotal);
        document.getElementById('resumen_iva').textContent = fmt(iva);
        document.getElementById('resumen_descuento').textContent = fmt(descTotal);
        document.getElementById('resumen_total').textContent = fmt(Math.max(0, baseTotal - descTotal));
        actualizarIndicadorLista();
    }
    function agregarFila() { const fila = template.content.firstElementChild.cloneNode(true); bindFila(fila); cuerpo.appendChild(fila); }
    function renderResumenCliente() {
        const contenedor = document.getElementById('resumen_cliente');
        if (!contenedor) { return; }

        const clienteId = parseInt(selectCliente?.value || '0', 10);
        const cliente = clientes.find((c) => parseInt(c.id || 0, 10) === clienteId);
        if (!cliente) {
            contenedor.innerHTML = '<div class="small text-muted">Selecciona un cliente para ver su información.</div>';
            return;
        }

        const razon = (cliente.razon_social || cliente.nombre || '').trim();
        const nombreComercial = (cliente.nombre_comercial || '').trim();
        const correo = (cliente.correo || '').trim() || '—';
        const telefono = (cliente.telefono || '').trim() || '—';
        const ciudad = (cliente.ciudad || '').trim() || '—';
        const direccion = (cliente.direccion || '').trim() || '—';

        contenedor.innerHTML = `
            <div class="row g-2 small">
                <div class="col-md-4"><strong>Cliente:</strong> ${esc(razon || '—')}</div>
                <div class="col-md-4"><strong>Nombre comercial:</strong> ${esc(nombreComercial || '—')}</div>
                <div class="col-md-4"><strong>Correo:</strong> ${esc(correo)}</div>
                <div class="col-md-4"><strong>Teléfono:</strong> ${esc(telefono)}</div>
                <div class="col-md-4"><strong>Ciudad:</strong> ${esc(ciudad)}</div>
                <div class="col-md-4"><strong>Dirección:</strong> ${esc(direccion)}</div>
            </div>`;
    }
    function actualizarOpcionesListaCliente() {
        if (!selectLista) { return; }
        const clienteId = parseInt(selectCliente?.value || '0', 10);
        const permitidas = new Set((listasPorCliente[String(clienteId)] || []).map((id) => parseInt(id, 10)));
        const valorActual = selectLista.value;

        selectLista.innerHTML = '';
        const opcionNinguna = document.createElement('option');
        opcionNinguna.value = '';
        opcionNinguna.textContent = 'No aplicar lista';
        selectLista.appendChild(opcionNinguna);

        todasLasListas.forEach((lista) => {
            const idLista = parseInt(lista.id || 0, 10);
            if (!permitidas.has(idLista)) { return; }
            const option = document.createElement('option');
            option.value = String(idLista);
            option.textContent = String(lista.nombre || ('Lista #' + idLista));
            selectLista.appendChild(option);
        });

        if (valorActual !== '' && permitidas.has(parseInt(valorActual, 10))) {
            selectLista.value = valorActual;
        } else {
            selectLista.value = '';
        }
    }
    async function aplicarListaATodasLineas(forzar = true) {
        const filas = Array.from(cuerpo.querySelectorAll('tr'));
        await Promise.all(filas.map((fila) => autocompletarPrecioDesdeLista(fila, forzar)));
        recalcular();
        actualizarIndicadorLista();
    }
    if (cuerpo.querySelectorAll('tr').length === 0) { agregarFila(); }
    cuerpo.querySelectorAll('tr').forEach((fila) => { bindFila(fila); });
    btnAgregar.addEventListener('click', () => { agregarFila(); recalcular(); });
    document.getElementById('descuento_tipo_total').addEventListener('change', recalcular);
    document.getElementById('descuento_total').addEventListener('input', recalcular);
    actualizarOpcionesListaCliente();
    renderResumenCliente();
    document.querySelector('[name="cliente_id"]')?.addEventListener('change', () => {
        renderResumenCliente();
        actualizarOpcionesListaCliente();
        aplicarListaATodasLineas(true);
    });
    document.getElementById('lista_precio_id')?.addEventListener('change', () => { aplicarListaATodasLineas(true); });
    aplicarListaATodasLineas(true);
})();

    const btnCopiarLinkAprobacion = document.getElementById('copiar_link_aprobacion_cliente');
    const inputLinkAprobacionCliente = document.getElementById('link_aprobacion_cliente');
    if (btnCopiarLinkAprobacion && inputLinkAprobacionCliente) {
        btnCopiarLinkAprobacion.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(inputLinkAprobacionCliente.value);
                btnCopiarLinkAprobacion.textContent = 'copiado';
                setTimeout(function () {
                    btnCopiarLinkAprobacion.textContent = 'copiar';
                }, 1200);
            } catch (error) {
                inputLinkAprobacionCliente.type = 'text';
                inputLinkAprobacionCliente.select();
                document.execCommand('copy');
                inputLinkAprobacionCliente.type = 'hidden';
            }
        });
    }
</script>
