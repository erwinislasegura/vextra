<?php
$estadosCotizacion = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'];
$estadosComerciales = ['abierto', 'contactado', 'en negociacion', 'pendiente cliente', 'seguimiento programado', 'cerrado ganado', 'cerrado perdido'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Seguimiento de cotizaciones</h1>
</div>

<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Guía rápida para el equipo comercial</div>
    <ul class="mb-0 small ps-3">
        <li>Selecciona una cotización para autocompletar cliente y responsable.</li>
        <li>Actualiza el <strong>estado comercial</strong> en cada contacto para mantener trazabilidad del embudo.</li>
        <li>Usa <strong>próxima acción</strong> y <strong>fecha de seguimiento</strong> para no perder oportunidades.</li>
        <li>Si cambias el estado de la cotización, el sistema registra historial automáticamente.</li>
    </ul>
</div>

<div class="card mb-3">
    <div class="card-header">Nuevo seguimiento</div>
    <div class="card-body">
        <form method="POST" action="<?= e(url('/app/seguimiento')) ?>" class="row g-2">
            <?= csrf_campo() ?>

            <div class="col-12">
                <div id="resumen_cotizacion" class="alert alert-info d-none mb-0 py-2">
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
                <select id="cotizacion_id" name="cotizacion_id" class="form-select" required>
                    <option value="">Selecciona una cotización</option>
                    <?php foreach ($cotizaciones as $cotizacion): ?>
                        <option
                            value="<?= (int) $cotizacion['id'] ?>"
                            data-cliente-id="<?= (int) ($cotizacion['cliente_id'] ?? 0) ?>"
                            data-cliente="<?= e((string) ($cotizacion['cliente'] ?? 'Sin cliente')) ?>"
                            data-estado="<?= e((string) ($cotizacion['estado'] ?? '')) ?>"
                            data-total="<?= e(number_format((float) ($cotizacion['total'] ?? 0), 2)) ?>"
                            data-emision="<?= e((string) ($cotizacion['fecha_emision'] ?? '')) ?>"
                            data-vencimiento="<?= e((string) ($cotizacion['fecha_vencimiento'] ?? '')) ?>"
                            data-responsable="<?= e((string) ($cotizacion['vendedor'] ?? '')) ?>"
                        >
                            <?= e((string) $cotizacion['numero']) ?> · <?= e((string) ($cotizacion['cliente'] ?? 'Sin cliente')) ?> · <?= e((string) ($cotizacion['estado'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Cliente (opcional)</label>
                <select id="cliente_id" name="cliente_id" class="form-select">
                    <option value="">Se tomará desde la cotización</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente['id'] ?>"><?= e((string) ($cliente['nombre'] ?? $cliente['razon_social'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Responsable</label>
                <input id="responsable" name="responsable" class="form-control" placeholder="Ej: Juan Pérez">
            </div>

            <div class="col-md-2">
                <label class="form-label">Fecha seguimiento</label>
                <input type="date" name="fecha_seguimiento" class="form-control" value="<?= e(date('Y-m-d')) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Próxima acción</label>
                <input name="proxima_accion" class="form-control" placeholder="Llamar cliente / enviar ajuste...">
            </div>

            <div class="col-md-2">
                <label class="form-label">Estado comercial</label>
                <select id="estado_comercial" name="estado_comercial" class="form-select">
                    <?php foreach ($estadosComerciales as $estadoComercial): ?>
                        <option value="<?= e($estadoComercial) ?>" <?= $estadoComercial === 'abierto' ? 'selected' : '' ?>>
                            <?= e(ucfirst($estadoComercial)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Selecciona el estado más representativo de la gestión actual.</div>
            </div>

            <div class="col-md-2">
                <label class="form-label">Probabilidad %</label>
                <input type="number" min="0" max="100" name="probabilidad_cierre" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label class="form-label">Cambiar estado de cotización</label>
                <select name="nuevo_estado_cotizacion" class="form-select">
                    <option value="">No cambiar estado</option>
                    <?php foreach ($estadosCotizacion as $estado): ?>
                        <option value="<?= e($estado) ?>"><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Comentarios</label>
                <textarea name="comentarios" class="form-control" rows="2" placeholder="Notas de la gestión comercial"></textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary btn-sm">Guardar seguimiento</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const cotizacionSelect = document.getElementById('cotizacion_id');
    const clienteSelect = document.getElementById('cliente_id');
    const responsableInput = document.getElementById('responsable');
    const estadoComercialSelect = document.getElementById('estado_comercial');
    const resumen = document.getElementById('resumen_cotizacion');

    if (!cotizacionSelect || !clienteSelect || !responsableInput || !estadoComercialSelect || !resumen) {
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

        const clienteId = opcion.dataset.clienteId || '';
        const clienteNombre = opcion.dataset.cliente || '—';
        const estado = opcion.dataset.estado || '—';
        const total = opcion.dataset.total || '0.00';
        const emision = opcion.dataset.emision || '—';
        const vencimiento = opcion.dataset.vencimiento || '—';
        const responsable = opcion.dataset.responsable || '';

        if (clienteId !== '') {
            clienteSelect.value = clienteId;
        }

        if (responsableInput.value.trim() === '' && responsable !== '') {
            responsableInput.value = responsable;
        }

        if (estado === 'aprobada') {
            estadoComercialSelect.value = 'cerrado ganado';
        } else if (estado === 'rechazada' || estado === 'anulada') {
            estadoComercialSelect.value = 'cerrado perdido';
        } else if (estado === 'enviada') {
            estadoComercialSelect.value = 'en negociacion';
        } else {
            estadoComercialSelect.value = 'seguimiento programado';
        }

        campos.numero.textContent = opcion.textContent.split('·')[0]?.trim() || '—';
        campos.cliente.textContent = clienteNombre;
        campos.estado.textContent = estado;
        campos.total.textContent = total;
        campos.emision.textContent = emision;
        campos.vencimiento.textContent = vencimiento;

        resumen.classList.remove('d-none');
    };

    cotizacionSelect.addEventListener('change', actualizar);
})();
</script>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong>Seguimientos registrados</strong>
            <div class="small text-muted">Registros encontrados: <?= count($registros) ?></div>
        </div>

        <div class="d-flex flex-nowrap gap-2 align-items-center ms-auto" style="overflow-x:auto;">
            <a href="<?= e(url('/app/seguimiento/exportar/excel?' . http_build_query(['q' => (string) ($buscar ?? ''), 'estado_cotizacion' => (string) ($estadoCotizacion ?? '')]))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
            <form method="GET" class="d-flex flex-nowrap gap-2 align-items-center mb-0">
                <input name="q" class="form-control form-control-sm" style="width: 220px" value="<?= e($buscar ?? '') ?>" placeholder="Buscar">
                <select name="estado_cotizacion" class="form-select form-select-sm" style="width: 180px">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estadosCotizacion as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= ($estadoCotizacion ?? '') === $estado ? 'selected' : '' ?>><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
                <?php if (($buscar ?? '') !== '' || ($estadoCotizacion ?? '') !== ''): ?>
                    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/seguimiento')) ?>">Limpiar</a>
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
                    <th>Estado comercial</th>
                    <th>Prob. %</th>
                    <th>Responsable</th>
                    <th>Próxima acción</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No hay seguimientos para mostrar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $fila): ?>
                        <tr>
                            <td><?= e((string) ($fila['fecha_seguimiento'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['cotizacion_numero'] ?? '—')) ?></td>
                            <td><?= e((string) ($fila['cliente_nombre'] ?? '—')) ?></td>
                            <td><span class="badge text-bg-light"><?= e((string) ($fila['cotizacion_estado'] ?? 'sin estado')) ?></span></td>
                            <td><?= e((string) ($fila['estado_comercial'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['probabilidad_cierre'] ?? 0)) ?></td>
                            <td><?= e((string) ($fila['responsable'] ?? '')) ?></td>
                            <td><?= e((string) ($fila['proxima_accion'] ?? '')) ?></td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= e(url('/app/seguimiento/ver/' . (int) $fila['id'])) ?>">Ver</a></li>
                                        <li><a class="dropdown-item" href="<?= e(url('/app/seguimiento/editar/' . (int) $fila['id'])) ?>">Editar</a></li>
                                        <li>
                                            <form method="POST" action="<?= e(url('/app/seguimiento/eliminar/' . (int) $fila['id'])) ?>" onsubmit="return confirm('¿Eliminar registro?')">
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
