<?php
$hayClientes = !empty($clientes);
$hayProductos = !empty($productos);
$puedeGuardar = $hayClientes && $hayProductos;
?>
<h1 class="h4 mb-3">Crear cotización</h1>

<style>
    #tabla-items {
        width: 100%;
        table-layout: fixed;
        font-size: 0.78rem;
    }

    #tabla-items th,
    #tabla-items td {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-items .js-lista-ajuste {
        display: block;
        min-width: 0;
        overflow: visible;
    }
</style>

<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Guía rápida para crear cotizaciones</div>
    <ul class="mb-0 small ps-3">
        <li>Selecciona cliente y lista de precios para aplicar ajustes comerciales correctos.</li>
        <li>Completa detalle de productos con cantidades y descuentos para evitar reprocesos.</li>
        <li>Usa observaciones y términos para dejar condiciones claras antes del envío.</li>
    </ul>
</div>

<form method="POST" class="d-grid gap-3" id="form-cotizacion">
    <?= csrf_campo() ?>
    <input type="hidden" name="token_publico" id="token_publico" value="<?= e($tokenPrevisualizacion ?? '') ?>">
    <input type="hidden" name="orden_compra_origen_id" id="orden_compra_origen_id" value="">

    <div class="card">
        <div class="card-header">Datos cotización</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="small">Número</label>
                <input class="form-control" value="<?= e($siguienteNumero) ?>" disabled>
            </div>

            <div class="col-md-5">
                <label class="small">Cliente</label>
                <div class="input-group">
                    <select class="form-select" name="cliente_id" id="cliente_id" required>
                        <?php if (!$hayClientes): ?>
                            <option value="">No hay clientes registrados</option>
                        <?php endif; ?>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['nombre']) ?></option>
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
                    <option>borrador</option>
                    <option>enviada</option>
                    <option>aprobada</option>
                    <option>rechazada</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="small d-block">Cotización automática desde orden aprobada</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switch_oc_aprobada">
                    <label class="form-check-label" for="switch_oc_aprobada">Usar datos de una orden de compra aprobada</label>
                </div>
                <select class="form-select mt-2 d-none" id="orden_compra_aprobada_id">
                    <option value="">Seleccionar orden aprobada...</option>
                    <?php foreach (($ordenesCompraAprobadas ?? []) as $ordenAprobada): ?>
                        <option value="<?= (int) ($ordenAprobada['id'] ?? 0) ?>">
                            <?= e((string) ($ordenAprobada['numero'] ?? '')) ?> · <?= e((string) ($ordenAprobada['proveedor_nombre'] ?? 'Sin proveedor')) ?> · <?= e((string) ($ordenAprobada['fecha_emision'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small">Lista de precios aplicada</label>
                <select class="form-select" name="lista_precio_id" id="lista_precio_id">
                    <option value="">No aplicar lista</option>
                    <?php foreach (($listasPrecios ?? []) as $lista): ?>
                        <option value="<?= (int) $lista['id'] ?>"><?= e($lista['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text" id="indicador_lista_estado">Selecciona cliente y lista para aplicar ajustes.</div>
            </div>

            <div class="col-md-3">
                <label class="small">Fecha emisión</label>
                <input class="form-control" type="date" name="fecha_emision" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-md-3">
                <label class="small">Fecha vencimiento</label>
                <input class="form-control" type="date" name="fecha_vencimiento" value="<?= date('Y-m-d', strtotime('+15 days')) ?>">
            </div>

            <div class="col-12">
                <?php $linkAprobacion = url('/cotizacion/publica/' . ($tokenPrevisualizacion ?? '')); ?>
                <label class="small text-muted">Enlace generado para cliente (previsualización)</label>
                <div class="input-group input-group-sm">
                    <input type="text" readonly class="form-control" id="link_aprobacion" value="<?= e($linkAprobacion) ?>">
                    <button class="btn btn-outline-secondary" type="button" id="copiar_link_aprobacion">Copiar</button>
                </div>
                <div class="form-text">Puedes compartirlo cuando la cotización quede guardada.</div>
            </div>

            <div class="col-md-6">
                <label class="small">Observaciones</label>
                <input class="form-control" name="observaciones">
            </div>

            <div class="col-12">
                <label class="small">Términos</label>
                <input class="form-control" name="terminos_condiciones">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Detalle de cotización</span>
            <small class="text-muted">El precio y descuento se aplican según la lista seleccionada (o sin lista).</small>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-agregar-linea">Agregar línea</button>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x:hidden; overflow-y:visible;">
                <table class="table table-sm align-middle" id="tabla-items">
                    <thead>
                    <tr>
                        <th style="width: 20%;">Producto / Servicio</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th style="width: 18%;">Lista / ajuste</th>
                        <th style="width: 11%;">Descuento</th>
                        <th>IVA %</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">IVA</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="cuerpo-items"></tbody>
                </table>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-5 ms-auto">
                    <label class="small">Descuento total</label>
                    <div class="input-group">
                        <select class="form-select" style="max-width: 75px; flex: 0 0 75px;" name="descuento_tipo_total" id="descuento_tipo_total">
                            <option value="valor">$</option>
                            <option value="porcentaje">%</option>
                        </select>
                        <input class="form-control" type="number" step="0.01" min="0" name="descuento_total" id="descuento_total" value="0">
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
        <div class="alert alert-warning mb-0">
            Debes crear al menos un cliente y un producto antes de guardar una cotización.
        </div>
    <?php endif; ?>

    <div>
        <button class="btn btn-primary btn-sm" name="accion" value="guardar"<?= $puedeGuardar ? '' : ' disabled' ?>>Guardar sin salir</button>
        <button class="btn btn-success btn-sm" name="accion" value="guardar_salir"<?= $puedeGuardar ? '' : ' disabled' ?>>Guardar y salir</button>
        <button class="btn btn-outline-success btn-sm" type="button" id="btn-enviar-cliente-crear" onclick="return confirmarEnvioCotizacionCrear();">Enviar al cliente</button>
        <button class="btn btn-outline-dark btn-sm" type="button" onclick="alert('Guarda la cotización para descargar el PDF.')">Descargar PDF</button>
        <a href="<?= e(url('/app/cotizaciones')) ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>
</form>

<div class="modal fade" id="modalConfirmarEnvioCotizacionCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="mensaje-confirmar-envio-cotizacion-crear">
                ¿Deseas enviar esta cotización al cliente seleccionado?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-confirmar-envio-cotizacion-crear">Sí, enviar</button>
            </div>
        </div>
    </div>
</div>

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
                <select class="form-select js-descuento-tipo" style="max-width: 54px; flex: 0 0 54px;" name="descuento_tipo_item[]">
                    <option value="valor">$</option>
                    <option value="porcentaje">%</option>
                </select>
                <input class="form-control js-descuento-valor" style="min-width: 0;" type="number" step="0.01" min="0" name="descuento_item[]" value="0">
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

<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear cliente (dato fijo)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="<?= e(url('/app/clientes/crear')) ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="redirect_to" value="/app/cotizaciones/crear">
                <div class="modal-body row g-2">
                    <div class="col-md-4"><input class="form-control" name="nombre" placeholder="Nombre" required></div>
                    <div class="col-md-4"><input class="form-control" name="correo" placeholder="Correo"></div>
                    <div class="col-md-4"><input class="form-control" name="telefono" placeholder="Teléfono"></div>
                    <div class="col-md-6"><input class="form-control" name="direccion" placeholder="Dirección"></div>
                    <div class="col-md-6"><input class="form-control" name="notas" placeholder="Notas"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary btn-sm">Guardar cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear producto (dato fijo)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="<?= e(url('/app/productos/crear')) ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="redirect_to" value="/app/cotizaciones/crear">
                <div class="modal-body row g-2">
                    <div class="col-md-3"><input class="form-control" name="codigo" placeholder="Código" required></div>
                    <div class="col-md-4"><input class="form-control" name="nombre" placeholder="Nombre" required></div>
                    <div class="col-md-5"><input class="form-control" name="descripcion" placeholder="Descripción"></div>
                    <div class="col-md-3"><input class="form-control" name="unidad" value="unidad"></div>
                    <div class="col-md-3"><input class="form-control" type="number" step="0.01" name="precio" placeholder="Precio"></div>
                    <div class="col-md-3"><input class="form-control" type="number" step="0.01" name="impuesto" value="19"></div>
                    <div class="col-md-3">
                        <select name="estado" class="form-select">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary btn-sm">Guardar producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarEnvioCotizacionCrear() {
    const selectCliente = document.getElementById('cliente_id');
    if (!selectCliente || String(selectCliente.value || '').trim() === '') {
        alert('No se puede enviar la cotización porque no hay un cliente seleccionado.');
        return false;
    }
    alert('Debes guardar la cotización primero. Luego podrás enviarla al cliente desde "Editar cotización".');
    return false;
}

(function () {
    const bloqueOcAprobada = document.getElementById('switch_oc_aprobada')?.closest('.col-md-6');
    if (bloqueOcAprobada) {
        bloqueOcAprobada.remove();
    }
    const selectorOcAprobada = document.getElementById('orden_compra_aprobada_id');
    if (selectorOcAprobada) {
        selectorOcAprobada.remove();
    }
    const cuerpo = document.getElementById('cuerpo-items');
    const template = document.getElementById('fila-item-template');
    const btnAgregar = document.getElementById('btn-agregar-linea');
    const selectCliente = document.querySelector('[name="cliente_id"]');
    const selectLista = document.getElementById('lista_precio_id');
    const todasLasListas = <?= json_encode($listasPrecios ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const btnCopiarLink = document.getElementById('copiar_link_aprobacion');
    const inputLinkAprobacion = document.getElementById('link_aprobacion');
    const infoProductoNombre = document.getElementById('info_producto_nombre');
    const infoProductoDescripcion = document.getElementById('info_producto_descripcion');
    const infoProductoPrecio = document.getElementById('info_producto_precio');
    const infoProductoImpuesto = document.getElementById('info_producto_impuesto');
    const infoProductoStock = document.getElementById('info_producto_stock');
    const btnVerMovimientos = document.getElementById('btn-ver-movimientos');
    const movimientosProductoTitulo = document.getElementById('movimientos_producto_titulo');
    const movimientosProductoBody = document.getElementById('movimientos_producto_body');
    const btnEnviarClienteCrear = document.getElementById('btn-enviar-cliente-crear');
    const btnConfirmarEnvioCotizacionCrear = document.getElementById('btn-confirmar-envio-cotizacion-crear');
    const modalConfirmarEnvioCotizacionCrearEl = document.getElementById('modalConfirmarEnvioCotizacionCrear');
    const mensajeConfirmarEnvioCotizacionCrear = document.getElementById('mensaje-confirmar-envio-cotizacion-crear');
    const modalConfirmarEnvioCotizacionCrear = (window.bootstrap && modalConfirmarEnvioCotizacionCrearEl)
        ? new bootstrap.Modal(modalConfirmarEnvioCotizacionCrearEl)
        : null;
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

    if (btnEnviarClienteCrear) {
        btnEnviarClienteCrear.addEventListener('click', function () {
            const clienteSeleccionado = String(selectCliente?.value || '').trim();
            if (clienteSeleccionado === '') {
                if (mensajeConfirmarEnvioCotizacionCrear) {
                    mensajeConfirmarEnvioCotizacionCrear.textContent = 'No se puede enviar la cotización porque no hay un cliente seleccionado.';
                }
                if (btnConfirmarEnvioCotizacionCrear) { btnConfirmarEnvioCotizacionCrear.classList.add('d-none'); }
                if (modalConfirmarEnvioCotizacionCrear) {
                    modalConfirmarEnvioCotizacionCrear.show();
                } else {
                    alert('No se puede enviar la cotización porque no hay un cliente seleccionado.');
                }
                return;
            }

            if (mensajeConfirmarEnvioCotizacionCrear) {
                mensajeConfirmarEnvioCotizacionCrear.textContent = 'Debes guardar la cotización antes de enviarla al cliente.';
            }
            if (btnConfirmarEnvioCotizacionCrear) { btnConfirmarEnvioCotizacionCrear.classList.remove('d-none'); }
            if (modalConfirmarEnvioCotizacionCrear) {
                modalConfirmarEnvioCotizacionCrear.show();
                return;
            }
            alert('Debes guardar la cotización antes de enviarla al cliente.');
        });
    }
    if (btnConfirmarEnvioCotizacionCrear) {
        btnConfirmarEnvioCotizacionCrear.addEventListener('click', function () {
            alert('Primero guarda la cotización y luego usa "Enviar al cliente" desde la edición.');
        });
    }

    if (btnCopiarLink && inputLinkAprobacion) {
        btnCopiarLink.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(inputLinkAprobacion.value);
                btnCopiarLink.textContent = 'Copiado';
                setTimeout(function () { btnCopiarLink.textContent = 'Copiar'; }, 1200);
            } catch (error) {
                inputLinkAprobacion.select();
                document.execCommand('copy');
            }
        });
    }
    const listasPorCliente = <?= json_encode($listasPreciosPorCliente ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const clientes = <?= json_encode($clientes ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const ordenesCompraAprobadas = <?= json_encode($ordenesCompraAprobadas ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const switchOcAprobada = document.getElementById('switch_oc_aprobada');
    const selectOcAprobada = document.getElementById('orden_compra_aprobada_id');

    function fmt(valor) {
        return '$' + (Math.round((valor + Number.EPSILON) * 100) / 100).toFixed(2);
    }

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
        const aplicadas = filas.filter((fila) => fila.dataset.listaAplicada === 'si').length;
        if (!selectLista || !selectLista.value) {
            indicador.innerHTML = '<span style="color:#6c757d;">Sin lista de precios aplicada.</span>';
            return;
        }
        if (aplicadas > 0) {
            indicador.innerHTML = `<span style="color:#3f8f62;">Se aplica descuento/lista en ${aplicadas} de ${filas.length} líneas.</span>`;
            return;
        }
        indicador.innerHTML = '<span style="color:#b94a48;">No aplica lista de precios en las líneas actuales.</span>';
    }

    function renderInfoLista(fila, data = null) {
        const celda = fila.querySelector('.js-lista-ajuste');
        if (!celda) { return; }
        if (!data) {
            fila.dataset.listaAplicada = 'no';
            celda.innerHTML = '<span style="color:#b94a48;">No aplica lista.</span>';
            actualizarIndicadorLista();
            return;
        }

        const nombreLista = data.lista_precio_nombre || 'Lista automática';
        const porcentaje = parseFloat(data.ajuste_porcentaje || '0');
        const tipo = data.ajuste_tipo === 'descuento' ? 'descuento' : 'incremento';
        const precioBase = parseFloat(data.precio_base || '0');
        const precioFinal = parseFloat(data.precio_final || '0');
        const montoAjuste = Math.abs(precioFinal - precioBase);

        if (!data.lista_precio_id) {
            fila.dataset.listaAplicada = 'no';
            celda.innerHTML = '<span style="color:#b94a48;">La lista no aplica (cliente, estado o vigencia).</span>';
            actualizarIndicadorLista();
            return;
        }

        if (porcentaje <= 0) {
            fila.dataset.listaAplicada = 'si';
            celda.innerHTML = `<span class="badge text-bg-success">${nombreLista}</span> <span style="color:#3f8f62;">Lista detectada y aplicada (sin ajuste porcentual).</span>`;
            actualizarIndicadorLista();
            return;
        }

        const esDescuento = tipo === 'descuento';
        const colorSuave = esDescuento ? 'style="color:#3f8f62;"' : '';
        const signo = esDescuento ? '-' : '+';
        const etiqueta = esDescuento ? 'Descuento por lista' : 'Incremento por lista';
        fila.dataset.listaAplicada = 'si';
        celda.innerHTML = `<span class="badge ${esDescuento ? 'text-bg-success' : 'text-bg-warning'}">${nombreLista}</span> <span ${colorSuave}><strong>${etiqueta}</strong>: ${signo}${porcentaje.toFixed(2)}% (${signo}${fmt(montoAjuste)}) | Base ${fmt(precioBase)} → Final ${fmt(precioFinal)}</span>`;
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
        const inputCantidad = fila.querySelector('.js-cantidad');
        const clienteId = selectCliente?.value || '';
        const listaPrecioId = selectLista?.value || '';
        const cantidad = parseFloat(inputCantidad?.value || '0');

        if (!selectProducto || !selectProducto.value || (!clienteId && !listaPrecioId)) {
            renderInfoLista(fila, null);
            aplicarPrecioBaseSinLista(fila, forzar);
            return;
        }

        try {
            const params = new URLSearchParams({
                producto_id: selectProducto.value,
                cliente_id: clienteId,
                lista_precio_id: listaPrecioId,
                cantidad: String(Math.max(0, cantidad || 0))
            });
            const resp = await fetch('<?= e(url('/app/listas-precios/precio-producto')) ?>?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
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
                        if (selectDescuento) {
                            selectDescuento.value = 'porcentaje';
                        }
                        if (inputDescuento) {
                            inputDescuento.value = String(ajustePorcentaje);
                        }
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
            // Ignorar para no interrumpir la cotización manual
            renderInfoLista(fila, null);
            aplicarPrecioBaseSinLista(fila, forzar);
        }
    }

    function recalcular() {
        let subtotal = 0;
        let iva = 0;
        let descuentoLineas = 0;

        cuerpo.querySelectorAll('tr').forEach((fila) => {
            const cantidad = parseFloat(fila.querySelector('.js-cantidad').value || '0');
            const precio = parseFloat(fila.querySelector('.js-precio').value || '0');
            const ivaPct = parseFloat(fila.querySelector('.js-iva').value || '0');
            const descuentoTipo = fila.querySelector('.js-descuento-tipo').value;
            const descuentoValor = parseFloat(fila.querySelector('.js-descuento-valor').value || '0');

            const base = Math.max(0, cantidad) * Math.max(0, precio);
            const descuento = descuentoTipo === 'porcentaje'
                ? base * (Math.min(Math.max(descuentoValor, 0), 100) / 100)
                : Math.min(Math.max(descuentoValor, 0), base);

            const subtotalLinea = Math.max(0, base - descuento);
            const ivaLinea = subtotalLinea * (Math.max(0, ivaPct) / 100);
            const totalLinea = subtotalLinea + ivaLinea;

            fila.querySelector('.js-subtotal').textContent = fmt(subtotalLinea);
            fila.querySelector('.js-iva-total').textContent = fmt(ivaLinea);
            fila.querySelector('.js-total').textContent = fmt(totalLinea);

            subtotal += subtotalLinea;
            iva += ivaLinea;
            descuentoLineas += descuento;
        });

        const tipoTotal = document.getElementById('descuento_tipo_total').value;
        const valorTotal = parseFloat(document.getElementById('descuento_total').value || '0');
        const baseTotal = subtotal + iva;
        const descuentoTotal = tipoTotal === 'porcentaje'
            ? baseTotal * (Math.min(Math.max(valorTotal, 0), 100) / 100)
            : Math.min(Math.max(valorTotal, 0), baseTotal);

        document.getElementById('resumen_subtotal').textContent = fmt(subtotal);
        document.getElementById('resumen_iva').textContent = fmt(iva);
        const descuentoGlobal = descuentoTotal;
        const descuentoAcumulado = descuentoLineas + descuentoGlobal;
        document.getElementById('resumen_descuento').textContent = fmt(descuentoAcumulado);
        document.getElementById('resumen_total').textContent = fmt(Math.max(0, baseTotal - descuentoTotal));
        actualizarIndicadorLista();
    }

    function agregarFila() {
        const fila = template.content.firstElementChild.cloneNode(true);
        fila.querySelector('.js-eliminar').addEventListener('click', () => {
            if (cuerpo.querySelectorAll('tr').length > 1) {
                fila.remove();
                recalcular();
            }
        });
        fila.querySelectorAll('input, select').forEach((control) => {
            control.addEventListener('input', recalcular);
            control.addEventListener('change', recalcular);
        });
        const inputCantidad = fila.querySelector('.js-cantidad');
        if (inputCantidad) {
            inputCantidad.addEventListener('change', async () => {
                await autocompletarPrecioDesdeLista(fila, true);
                recalcular();
            });
        }

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
        cuerpo.appendChild(fila);
    }


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
            const option = document.createElement('option');
            option.value = String(idLista);
            const nombreLista = String(lista.nombre || ('Lista #' + idLista));
            option.textContent = permitidas.has(idLista) ? nombreLista : `${nombreLista} (manual)`;
            selectLista.appendChild(option);
        });

        if (valorActual !== '' && todasLasListas.some((lista) => parseInt(lista.id || 0, 10) === parseInt(valorActual, 10))) {
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

    async function cargarDatosDesdeOrdenCompraAprobada() {
        const ordenId = parseInt(selectOcAprobada?.value || '0', 10);
        if (!ordenId) { return; }
        const orden = ordenesCompraAprobadas.find((o) => parseInt(o.id || 0, 10) === ordenId);
        if (!orden) { return; }
        const detalles = Array.isArray(orden.detalles) ? orden.detalles : [];
        if (detalles.length === 0) {
            alert('La orden seleccionada no tiene detalle para cargar.');
            return;
        }
        cuerpo.innerHTML = '';
        detalles.forEach((detalle) => {
            agregarFila();
            const fila = cuerpo.lastElementChild;
            if (!fila) { return; }
            const selectProducto = fila.querySelector('.js-producto');
            const inputCantidad = fila.querySelector('.js-cantidad');
            const inputPrecio = fila.querySelector('.js-precio');
            const inputIva = fila.querySelector('.js-iva');
            const inputDescripcion = fila.querySelector('.js-descripcion');
            const prodId = String(parseInt(detalle.producto_id || 0, 10));
            if (selectProducto) { selectProducto.value = prodId; }
            if (inputCantidad) { inputCantidad.value = String(Number(detalle.cantidad || 0)); }
            if (inputPrecio) { inputPrecio.value = String(Number(detalle.costo_unitario || 0)); }
            if (inputIva) { inputIva.value = '19'; }
            if (inputDescripcion && selectProducto) {
                const opcion = selectProducto.options[selectProducto.selectedIndex];
                inputDescripcion.value = String(opcion?.dataset?.descripcion || opcion?.dataset?.nombre || '');
            }
        });
        const referencia = String(orden.referencia || '').trim();
        const observacion = String(orden.observacion || '').trim();
        const inputObs = document.querySelector('[name=\"observaciones\"]');
        if (inputObs) {
            inputObs.value = [referencia !== '' ? `Orden aprobada ${orden.numero}` : `Orden aprobada ${orden.numero}`, referencia, observacion].filter(Boolean).join(' · ');
        }
        await aplicarListaATodasLineas(false);
    }

    btnAgregar.addEventListener('click', () => {
        agregarFila();
        recalcular();
    });

    agregarFila();
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
    switchOcAprobada?.addEventListener('change', () => {
        if (!selectOcAprobada) { return; }
        selectOcAprobada.classList.toggle('d-none', !switchOcAprobada.checked);
        if (!switchOcAprobada.checked) {
            selectOcAprobada.value = '';
            const inputOrdenOrigen = document.getElementById('orden_compra_origen_id');
            if (inputOrdenOrigen) {
                inputOrdenOrigen.value = '';
            }
        }
    });
    selectOcAprobada?.addEventListener('change', cargarDatosDesdeOrdenCompraAprobada);
    selectOcAprobada?.addEventListener('change', () => {
        const inputOrdenOrigen = document.getElementById('orden_compra_origen_id');
        if (inputOrdenOrigen) {
            inputOrdenOrigen.value = String(selectOcAprobada?.value || '');
        }
    });
    aplicarListaATodasLineas(true);
})();
</script>
