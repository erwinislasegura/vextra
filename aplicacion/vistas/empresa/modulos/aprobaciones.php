<?php
$estadosAprobacion = ['pendiente', 'aprobada', 'rechazada'];
$estadosCotizacion = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Aprobaciones de cotizaciones</h1>
</div>

<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Guía rápida para aprobaciones</div>
    <ul class="mb-0 small ps-3">
        <li>Selecciona la cotización para autocompletar monto y solicitante.</li>
        <li>Usa <strong>pendiente</strong> mientras se revisa internamente.</li>
        <li>Al registrar <strong>aprobada</strong> o <strong>rechazada</strong>, el estado de la cotización se actualiza automáticamente.</li>
        <li>Documenta el motivo y observaciones para auditoría comercial.</li>
    </ul>
</div>

<div class="card mb-3">
    <div class="card-header">Nueva aprobación</div>
    <div class="card-body">
        <form method="POST" action="<?= e(url('/app/aprobaciones')) ?>" class="row g-2">
            <?= csrf_campo() ?>

            <div class="col-12">
                <div id="resumen_cotizacion_aprobacion" class="alert alert-info d-none mb-0 py-2">
                    <div class="small text-uppercase fw-semibold">Resumen cotización seleccionada</div>
                    <div class="row row-cols-1 row-cols-md-3 g-2 mt-1">
                        <div><strong>Número:</strong> <span data-campo="numero">—</span></div>
                        <div><strong>Cliente:</strong> <span data-campo="cliente">—</span></div>
                        <div><strong>Estado:</strong> <span data-campo="estado">—</span></div>
                        <div><strong>Total:</strong> <span data-campo="total">—</span></div>
                        <div><strong>Emisión:</strong> <span data-campo="emision">—</span></div>
                        <div><strong>Vencimiento:</strong> <span data-campo="vencimiento">—</span></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Cotización</label>
                <select id="cotizacion_id_aprobacion" name="cotizacion_id" class="form-select" required>
                    <option value="">Selecciona una cotización</option>
                    <?php foreach ($cotizaciones as $cotizacion): ?>
                        <option
                            value="<?= (int) $cotizacion['id'] ?>"
                            data-numero="<?= e((string) ($cotizacion['numero'] ?? '')) ?>"
                            data-cliente="<?= e((string) ($cotizacion['cliente'] ?? 'Sin cliente')) ?>"
                            data-estado="<?= e((string) ($cotizacion['estado'] ?? '')) ?>"
                            data-total="<?= e(number_format((float) ($cotizacion['total'] ?? 0), 2)) ?>"
                            data-emision="<?= e((string) ($cotizacion['fecha_emision'] ?? '')) ?>"
                            data-vencimiento="<?= e((string) ($cotizacion['fecha_vencimiento'] ?? '')) ?>"
                            data-vendedor="<?= e((string) ($cotizacion['vendedor'] ?? '')) ?>"
                        >
                            <?= e((string) ($cotizacion['numero'] ?? '')) ?> · <?= e((string) ($cotizacion['cliente'] ?? 'Sin cliente')) ?> · <?= e((string) ($cotizacion['estado'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Monto</label>
                <input id="monto_aprobacion" type="number" step="0.01" min="0" name="monto" class="form-control" placeholder="0.00">
            </div>

            <div class="col-md-3">
                <label class="form-label">Motivo</label>
                <input name="motivo" class="form-control" placeholder="Descuento especial, excepción, etc.">
            </div>

            <div class="col-md-3">
                <label class="form-label">Solicitante</label>
                <input id="solicitante_aprobacion" name="solicitante" class="form-control" placeholder="Nombre del solicitante">
            </div>

            <div class="col-md-3">
                <label class="form-label">Aprobador</label>
                <input name="aprobador" class="form-control" placeholder="Gerente / supervisor">
            </div>

            <div class="col-md-2">
                <label class="form-label">Estado aprobación</label>
                <select id="estado_aprobacion" name="estado" class="form-select">
                    <?php foreach ($estadosAprobacion as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= $estado === 'pendiente' ? 'selected' : '' ?>><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Fecha aprobación</label>
                <input type="date" name="fecha_aprobacion" class="form-control" value="<?= e(date('Y-m-d')) ?>">
            </div>

            <div class="col-md-5">
                <label class="form-label">Observaciones</label>
                <input name="observaciones" class="form-control" placeholder="Detalle para auditoría interna">
            </div>

            <div class="col-12">
                <button class="btn btn-primary btn-sm">Guardar aprobación</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong>Aprobaciones registradas</strong>
            <div class="small text-muted">Registros encontrados: <?= count($registros) ?></div>
        </div>

        <div class="d-flex flex-nowrap gap-2 align-items-center ms-auto" style="overflow-x:auto;">
            <a href="<?= e(url('/app/aprobaciones/exportar/excel?' . http_build_query(['q' => (string) ($buscar ?? ''), 'estado_aprobacion' => (string) ($estadoAprobacion ?? '')]))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
            <form method="GET" class="d-flex flex-nowrap gap-2 align-items-center mb-0">
                <input name="q" class="form-control form-control-sm" style="width: 220px" value="<?= e($buscar ?? '') ?>" placeholder="Buscar">
                <select name="estado_aprobacion" class="form-select form-select-sm" style="width: 180px">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estadosAprobacion as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= ($estadoAprobacion ?? '') === $estado ? 'selected' : '' ?>><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
                <?php if (($buscar ?? '') !== '' || ($estadoAprobacion ?? '') !== ''): ?>
                    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/aprobaciones')) ?>">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 tabla-admin align-middle">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Cotización</th>
                    <th>Cliente</th>
                    <th>Estado cotización</th>
                    <th>Monto</th>
                    <th>Estado aprobación</th>
                    <th>Solicitante</th>
                    <th>Aprobador</th>
                    <th>Motivo</th>
                    <th>Observaciones</th>
                    <th>Firma cliente</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="12" class="text-center text-muted py-4">No hay aprobaciones para mostrar.</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $fila): ?>
                        <tr>
                            <td><?= e((string) ($fila['fecha_aprobacion'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['cotizacion_numero'] ?? '—')) ?></td>
                            <td><?= e((string) ($fila['cliente_nombre'] ?? '—')) ?></td>
                            <td><span class="badge text-bg-light"><?= e((string) ($fila['cotizacion_estado'] ?? 'sin estado')) ?></span></td>
                            <td><?= e(number_format((float) ($fila['monto'] ?? 0), 2)) ?></td>
                            <td><?= e((string) ($fila['estado'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['solicitante'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['aprobador'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['motivo'] ?? '')) ?></td>
                            <td class="small"><?= e((string) ($fila['observaciones'] ?? '')) ?></td>
                            <td>
                                <?php if (!empty($fila['cotizacion_firma_cliente'])): ?>
                                    <div class="small text-muted mb-1"><?= e((string) ($fila['cotizacion_nombre_firmante'] ?? 'Cliente')) ?></div>
                                    <img
                                        src="<?= e((string) $fila['cotizacion_firma_cliente']) ?>"
                                        alt="Firma cliente"
                                        style="max-width: 180px; width: 100%; border: 1px solid #dee2e6; border-radius: .35rem; background: #fff;"
                                    >
                                <?php else: ?>
                                    <span class="text-muted small">Sin firma registrada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= e(url('/app/aprobaciones/ver/' . (int) $fila['id'])) ?>">Ver</a></li>
                                        <li><a class="dropdown-item" href="<?= e(url('/app/aprobaciones/editar/' . (int) $fila['id'])) ?>">Editar</a></li>
                                        <li>
                                            <form method="POST" action="<?= e(url('/app/aprobaciones/eliminar/' . (int) $fila['id'])) ?>" onsubmit="return confirm('¿Eliminar registro?')">
                                                <?= csrf_campo() ?>
                                                <button type="submit" class="dropdown-item text-danger">Eliminar</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const cotizacionSelect = document.getElementById('cotizacion_id_aprobacion');
    const montoInput = document.getElementById('monto_aprobacion');
    const solicitanteInput = document.getElementById('solicitante_aprobacion');
    const estadoAprobacionSelect = document.getElementById('estado_aprobacion');
    const resumen = document.getElementById('resumen_cotizacion_aprobacion');

    if (!cotizacionSelect || !montoInput || !solicitanteInput || !estadoAprobacionSelect || !resumen) {
        return;
    }

    const campos = {
        numero: resumen.querySelector('[data-campo="numero"]'),
        cliente: resumen.querySelector('[data-campo="cliente"]'),
        estado: resumen.querySelector('[data-campo="estado"]'),
        total: resumen.querySelector('[data-campo="total"]'),
        emision: resumen.querySelector('[data-campo="emision"]'),
        vencimiento: resumen.querySelector('[data-campo="vencimiento"]'),
    };

    const actualizar = () => {
        const opcion = cotizacionSelect.options[cotizacionSelect.selectedIndex];
        if (!opcion || !opcion.value) {
            resumen.classList.add('d-none');
            return;
        }

        const total = opcion.dataset.total || '0.00';
        const estado = opcion.dataset.estado || '';
        const vendedor = opcion.dataset.vendedor || '';

        if (montoInput.value.trim() === '') {
            montoInput.value = total;
        }

        if (solicitanteInput.value.trim() === '' && vendedor !== '') {
            solicitanteInput.value = vendedor;
        }

        if (estado === 'aprobada') {
            estadoAprobacionSelect.value = 'aprobada';
        } else if (estado === 'rechazada' || estado === 'anulada') {
            estadoAprobacionSelect.value = 'rechazada';
        } else {
            estadoAprobacionSelect.value = 'pendiente';
        }

        campos.numero.textContent = opcion.dataset.numero || '—';
        campos.cliente.textContent = opcion.dataset.cliente || '—';
        campos.estado.textContent = estado || '—';
        campos.total.textContent = total;
        campos.emision.textContent = opcion.dataset.emision || '—';
        campos.vencimiento.textContent = opcion.dataset.vencimiento || '—';
        resumen.classList.remove('d-none');
    };

    cotizacionSelect.addEventListener('change', actualizar);
})();
</script>
