<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Cotizaciones</h1>
  <div class="d-flex gap-2">
    <a href="<?= e(url('/app/cotizaciones/crear')) ?>" class="btn btn-primary btn-sm">Nueva cotización</a>
  </div>
</div>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Buenas prácticas de gestión de cotizaciones</div>
  <ul class="mb-0 small ps-3">
    <li>Usa filtros por estado y fechas para priorizar seguimiento comercial diario.</li>
    <li>Revisa cotizaciones próximas a vencer para evitar pérdida de oportunidades.</li>
    <li>Mantén estados actualizados para reportes comerciales más confiables.</li>
  </ul>
</div>

<div class="card mb-3">
  <div class="card-header"><strong>Filtros</strong></div>
  <div class="card-body">
    <form method="GET" class="row g-2">
      <div class="col-md-3">
        <label class="small">Buscar</label>
        <input class="form-control form-control-sm" name="q" value="<?= e($buscar ?? '') ?>" placeholder="Número, cliente o vendedor">
      </div>
      <div class="col-md-2">
        <label class="small">Estado</label>
        <select class="form-select form-select-sm" name="estado">
          <option value="">Todos</option>
          <?php foreach (['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'] as $estadoItem): ?>
            <option value="<?= e($estadoItem) ?>" <?= ($estado ?? '') === $estadoItem ? 'selected' : '' ?>><?= e(ucfirst($estadoItem)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="small">Cliente</label>
        <select class="form-select form-select-sm" name="cliente_id">
          <option value="">Todos</option>
          <?php foreach (($clientes ?? []) as $cli): ?>
            <option value="<?= (int) $cli['id'] ?>" <?= (int) ($clienteId ?? 0) === (int) $cli['id'] ? 'selected' : '' ?>><?= e($cli['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="small">Desde</label>
        <input type="date" class="form-control form-control-sm" name="fecha_desde" value="<?= e($fechaDesde ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="small">Hasta</label>
        <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="<?= e($fechaHasta ?? '') ?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
        <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/cotizaciones')) ?>">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <strong>Listado de cotizaciones</strong>
      <div class="small text-muted">Registros encontrados: <?= count($cotizaciones) ?></div>
    </div>
    <?php if (plan_tiene_funcionalidad_empresa_actual('modulo_cotizaciones')): ?>
      <a href="<?= e(url('/app/cotizaciones/exportar-excel?' . http_build_query($_GET))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
    <?php endif; ?>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-hover table-sm mb-0 tabla-admin">
      <thead class="table-light">
        <tr>
          <th>Número</th>
          <th>Cliente</th>
          <th>Emisión</th>
          <th>Vencimiento</th>
          <th>Vendedor</th>
          <th class="text-end">Subtotal</th>
          <th class="text-end">Impuesto</th>
          <th class="text-end">Total</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($cotizaciones)): ?>
        <tr><td colspan="10" class="text-center py-4 text-muted">No hay cotizaciones registradas con este criterio.</td></tr>
      <?php else: ?>
        <?php foreach($cotizaciones as $c): ?>
          <?php $estadoFila = (string) ($c['estado'] ?? ''); ?>
          <tr>
            <td><?= e($c['numero']) ?></td>
            <td><?= e($c['cliente']) ?></td>
            <td><?= e($c['fecha_emision']) ?></td>
            <td><?= e($c['fecha_vencimiento']) ?></td>
            <td><?= e($c['vendedor']) ?></td>
            <td class="text-end">$<?= number_format((float) $c['subtotal'], 2) ?></td>
            <td class="text-end">$<?= number_format((float) $c['impuesto'], 2) ?></td>
            <td class="text-end">$<?= number_format((float) $c['total'], 2) ?></td>
            <td>
              <span class="badge <?= $estadoFila === 'aprobada' ? 'badge-estado-aprobada' : ($estadoFila === 'rechazada' ? 'badge-estado-rechazada' : 'badge-estado-pendiente') ?>">
                <?= e($estadoFila) ?>
              </span>
            </td>
            <td class="text-end">
              <div class="dropdown dropup">
                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="<?= e(url('/app/cotizaciones/ver/' . $c['id'])) ?>">Ver</a></li>
                  <li><a class="dropdown-item" href="<?= e(url('/app/cotizaciones/editar/' . $c['id'])) ?>">Editar</a></li>
                  <?php if (plan_tiene_funcionalidad_empresa_actual('cotizacion_pdf')): ?>
                    <li><a class="dropdown-item" href="<?= e(url('/app/cotizaciones/pdf/' . $c['id'])) ?>">Descargar PDF</a></li>
                  <?php endif; ?>
                  <li>
                    <form method="POST" action="<?= e(url('/app/cotizaciones/eliminar/' . $c['id'])) ?>" onsubmit="return confirm('¿Confirmas eliminar esta cotización?')">
                      <?= csrf_campo() ?>
                      <button class="dropdown-item text-danger" type="submit">Eliminar</button>
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
