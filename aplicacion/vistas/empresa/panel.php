<?php
$totalCotizaciones = max(1, (int) (($resumen['aprobadas'] ?? 0) + ($resumen['rechazadas'] ?? 0) + ($resumen['pendientes'] ?? 0)));
$porcentajeAprobadas = (int) round(((int) ($resumen['aprobadas'] ?? 0) / $totalCotizaciones) * 100);
$porcentajePendientes = (int) round(((int) ($resumen['pendientes'] ?? 0) / $totalCotizaciones) * 100);
$porcentajeRechazadas = (int) round(((int) ($resumen['rechazadas'] ?? 0) / $totalCotizaciones) * 100);
$stockCritico = (int) ($resumen['stock_critico'] ?? 0);
$aprobacionesPendientes = (int) ($resumen['aprobaciones_pendientes'] ?? 0);
$seguimientosAbiertos = (int) ($resumen['seguimientos_abiertos'] ?? 0);
$notificacionesPendientes = (int) ($resumen['notificaciones_pendientes'] ?? 0);

$meses = [];
$conteosMes = [];
$montosMes = [];
foreach (($resumen['cotizaciones_ultimos_meses'] ?? []) as $fila) {
    $periodo = (string) ($fila['periodo'] ?? '');
    $fecha = \DateTime::createFromFormat('Y-m', $periodo);
    $meses[] = $fecha ? $fecha->format('M y') : $periodo;
    $conteosMes[] = (int) ($fila['total'] ?? 0);
    $montosMes[] = (float) ($fila['monto'] ?? 0);
}

$diasRestantesPlan = isset($resumen['dias_restantes_plan']) && $resumen['dias_restantes_plan'] !== null ? (int) $resumen['dias_restantes_plan'] : null;
$esPeriodoPrueba = ($resumen['estado_suscripcion'] ?? '') === 'pendiente' && $diasRestantesPlan !== null && $diasRestantesPlan >= 0;
?>

<section class="panel-cliente panel-cliente--pro">
  <?php if ($esPeriodoPrueba): ?>
    <div class="alert alert-info border-0 shadow-sm d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3" role="alert">
      <div>
        <div class="fw-bold mb-1">🎁 Periodo de prueba activado</div>
        <div class="mb-1">Te quedan <strong><?= $diasRestantesPlan ?> día(s)</strong> para disfrutar Vextra con acceso completo.</div>
        <div class="small text-secondary">Activa tu plan hoy y evita interrupciones para tu equipo comercial.</div>
      </div>
      <form method="POST" action="<?= e(url('/app/panel/iniciar-pago-trial')) ?>" class="d-grid">
        <?= csrf_campo() ?>
        <button type="submit" class="btn btn-primary btn-sm px-3">Paga acá y mantén tu operación sin pausas</button>
      </form>
    </div>
  <?php endif; ?>

  <?php if (isset($resumen['dias_restantes_plan']) && $resumen['dias_restantes_plan'] !== null && (int) $resumen['dias_restantes_plan'] <= 0): ?>
    <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2" role="alert">
      <div><strong>Tu plan finalizó.</strong> Para mantener el acceso completo al sistema, renueva tu plan desde el panel comercial.</div>
      <a class="btn btn-sm btn-warning" href="<?= e(url('/planes')) ?>">Renovar plan</a>
    </div>
  <?php endif; ?>

  <div class="panel-cliente__hero card mb-3">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
      <div>
        <h1 class="h4 mb-1">Panel comercial</h1>
        <p class="text-muted mb-0">Vista ordenada para vender, controlar inventario y tomar decisiones con claridad.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= e(url('/app/notificaciones')) ?>"><i class="bi bi-bell me-1"></i> Alertas<?php if ($notificacionesPendientes > 0): ?><span class="badge text-bg-danger ms-1"><?= $notificacionesPendientes ?></span><?php endif; ?></a>
        <a class="btn btn-primary" href="<?= e(url('/app/cotizaciones/crear')) ?>"><i class="bi bi-plus-circle me-1"></i> Nueva cotización</a>
      </div>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-sm-6 col-xl-3"><article class="metric-card metric-card-sky"><div class="metric-card__icon"><i class="bi bi-file-earmark-bar-graph"></i></div><div class="metric-card__meta">Cotizaciones del mes</div><div class="metric-card__value"><?= (int) ($resumen['cotizaciones_mes'] ?? 0) ?></div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card metric-card-red"><div class="metric-card__icon"><i class="bi bi-currency-dollar"></i></div><div class="metric-card__meta">Monto cotizado</div><div class="metric-card__value">$<?= number_format((float) ($resumen['monto_mes'] ?? 0), 2) ?></div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card metric-card-green"><div class="metric-card__icon"><i class="bi bi-graph-up-arrow"></i></div><div class="metric-card__meta">Tasa de aprobación</div><div class="metric-card__value"><?= $porcentajeAprobadas ?>%</div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card metric-card-amber"><div class="metric-card__icon"><i class="bi bi-hourglass-split"></i></div><div class="metric-card__meta">Por vencer (7 días)</div><div class="metric-card__value"><?= (int) ($resumen['por_vencer'] ?? 0) ?></div></article></div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-xl-8">
      <div class="card card-dashboard h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Atajos del flujo diario</span>
          <span class="small text-muted">Acciones frecuentes</span>
        </div>
        <div class="card-body panel-quick-grid">
          <a class="btn btn-light border text-start" href="<?= e(url('/app/punto-venta')) ?>"><i class="bi bi-receipt me-2 text-primary"></i>Registrar venta POS</a>
          <a class="btn btn-light border text-start" href="<?= e(url('/app/inventario/recepciones')) ?>"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Registrar recepción</a>
          <a class="btn btn-light border text-start" href="<?= e(url('/app/clientes/crear')) ?>"><i class="bi bi-person-plus me-2 text-info"></i>Nuevo cliente</a>
          <a class="btn btn-light border text-start" href="<?= e(url('/app/seguimiento')) ?>"><i class="bi bi-clipboard-check me-2 text-warning"></i>Gestionar seguimientos</a>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card card-dashboard h-100">
        <div class="card-header">Semáforo operativo</div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center py-1"><span>Riesgo de inventario</span><span class="badge <?= $stockCritico > 0 ? 'text-bg-danger' : 'text-bg-success' ?>"><?= $stockCritico > 0 ? 'Atención' : 'Estable' ?></span></div>
          <div class="d-flex justify-content-between align-items-center py-1"><span>Cola de aprobaciones</span><span class="badge <?= $aprobacionesPendientes > 0 ? 'text-bg-warning' : 'text-bg-success' ?>"><?= $aprobacionesPendientes > 0 ? 'Pendiente' : 'Al día' ?></span></div>
          <div class="d-flex justify-content-between align-items-center py-1"><span>Seguimiento comercial</span><span class="badge <?= $seguimientosAbiertos > 15 ? 'text-bg-warning' : 'text-bg-success' ?>"><?= $seguimientosAbiertos > 15 ? 'Carga alta' : 'Controlado' ?></span></div>
          <div class="d-flex justify-content-between align-items-center py-1"><span>Notificaciones</span><span class="badge <?= $notificacionesPendientes > 0 ? 'text-bg-info' : 'text-bg-success' ?>"><?= $notificacionesPendientes > 0 ? 'Por revisar' : 'Sin pendientes' ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-xl-8">
      <div class="card card-dashboard h-100">
        <div class="card-header d-flex justify-content-between align-items-center"><span>Tendencia comercial (últimos 6 meses)</span><span class="badge text-bg-light border">Actualizado</span></div>
        <div class="card-body chart-area"><canvas id="graficoCotizacionesMes"></canvas></div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card card-dashboard h-100">
        <div class="card-header">Objetivos por estado</div>
        <div class="card-body">
          <div class="kpi-progress mb-3"><div class="d-flex justify-content-between small mb-1"><span>Aprobadas</span><strong><?= $porcentajeAprobadas ?>%</strong></div><div class="progress" role="progressbar"><div class="progress-bar bg-success" style="width: <?= $porcentajeAprobadas ?>%"></div></div></div>
          <div class="kpi-progress mb-3"><div class="d-flex justify-content-between small mb-1"><span>Pendientes</span><strong><?= $porcentajePendientes ?>%</strong></div><div class="progress" role="progressbar"><div class="progress-bar bg-warning" style="width: <?= $porcentajePendientes ?>%"></div></div></div>
          <div class="kpi-progress mb-3"><div class="d-flex justify-content-between small mb-1"><span>Rechazadas</span><strong><?= $porcentajeRechazadas ?>%</strong></div><div class="progress" role="progressbar"><div class="progress-bar bg-danger" style="width: <?= $porcentajeRechazadas ?>%"></div></div></div>
          <hr>
          <div class="small text-muted">Distribución sobre <?= $totalCotizaciones ?> cotizaciones registradas.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-dashboard mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>KPIs de módulos integrados</span>
      <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar módulos KPI">
        <button type="button" class="btn btn-outline-secondary active" data-kpi-filter="all">Todos</button>
        <button type="button" class="btn btn-outline-secondary" data-kpi-filter="inventario">Inventario</button>
        <button type="button" class="btn btn-outline-secondary" data-kpi-filter="pos">POS</button>
        <button type="button" class="btn btn-outline-secondary" data-kpi-filter="comercial">Comercial</button>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-sm-6 col-xl-3" data-kpi-module="inventario"><article class="metric-card metric-card-amber"><div class="metric-card__icon"><i class="bi bi-box-seam"></i></div><div class="metric-card__meta">Stock bajo</div><div class="metric-card__value"><?= (int) ($resumen['stock_bajo'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3" data-kpi-module="inventario"><article class="metric-card metric-card-red"><div class="metric-card__icon"><i class="bi bi-exclamation-octagon"></i></div><div class="metric-card__meta">Stock crítico</div><div class="metric-card__value"><?= (int) ($resumen['stock_critico'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3" data-kpi-module="inventario"><article class="metric-card metric-card-green"><div class="metric-card__icon"><i class="bi bi-check-circle"></i></div><div class="metric-card__meta">Stock normal</div><div class="metric-card__value"><?= (int) ($resumen['stock_normal'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3" data-kpi-module="pos"><article class="metric-card metric-card-sky"><div class="metric-card__icon"><i class="bi bi-receipt"></i></div><div class="metric-card__meta">Ventas POS hoy</div><div class="metric-card__value"><?= (int) ($resumen['ventas_hoy'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3" data-kpi-module="pos"><article class="metric-card metric-card-green"><div class="metric-card__icon"><i class="bi bi-cash-coin"></i></div><div class="metric-card__meta">Ingresos POS hoy</div><div class="metric-card__value">$<?= number_format((float) ($resumen['monto_ventas_hoy'] ?? 0), 2) ?></div></article></div>
      </div>
      <div class="row g-2 mt-1">
        <div class="col-md-3" data-kpi-module="inventario"><div class="panel-inline-stat"><div class="text-muted small">Órdenes de compra pendientes</div><div class="h4 mb-0"><?= (int) ($resumen['ordenes_compra_pendientes'] ?? 0) ?></div></div></div>
        <div class="col-md-3" data-kpi-module="comercial"><div class="panel-inline-stat"><div class="text-muted small">Seguimientos abiertos</div><div class="h4 mb-0"><?= (int) ($resumen['seguimientos_abiertos'] ?? 0) ?></div></div></div>
        <div class="col-md-3" data-kpi-module="comercial"><div class="panel-inline-stat"><div class="text-muted small">Aprobaciones pendientes</div><div class="h4 mb-0"><?= (int) ($resumen['aprobaciones_pendientes'] ?? 0) ?></div></div></div>
        <div class="col-md-3" data-kpi-module="comercial"><div class="panel-inline-stat"><div class="text-muted small">Notificaciones por revisar</div><div class="h4 mb-0"><?= (int) ($resumen['notificaciones_pendientes'] ?? 0) ?></div></div></div>
      </div>
    </div>
  </div>

  <div class="card card-dashboard mb-3">
    <div class="card-header">Resumen comercial rápido</div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-sm-6 col-xl-3"><div class="panel-inline-stat"><div class="small text-muted">Clientes</div><div class="h5 mb-0"><?= (int) ($resumen['total_clientes'] ?? 0) ?></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="panel-inline-stat"><div class="small text-muted">Productos/Servicios</div><div class="h5 mb-0"><?= (int) ($resumen['total_productos'] ?? 0) ?></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="panel-inline-stat"><div class="small text-muted">Cotizaciones</div><div class="h5 mb-0"><?= (int) ($resumen['total_cotizaciones'] ?? 0) ?></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="panel-inline-stat"><div class="small text-muted">Plan activo</div><div class="h5 mb-0"><?= e((string) (($resumen['plan_actual_nombre'] ?? null) ?: ($resumen['plan_actual'] ?? 'N/A'))) ?></div></div></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="card card-dashboard h-100"><div class="card-header">Clientes recientes</div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Cliente</th><th>Correo</th><th>Alta</th></tr></thead><tbody><?php if (!empty($resumen['clientes_recientes'])): ?><?php foreach ($resumen['clientes_recientes'] as $c): ?><tr><td class="fw-semibold"><?= e($c['nombre']) ?></td><td><?= e($c['correo']) ?></td><td><?= e($c['fecha_creacion']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="3" class="text-center text-muted py-3">Sin clientes recientes.</td></tr><?php endif; ?></tbody></table></div></div></div>
    <div class="col-lg-6"><div class="card card-dashboard h-100"><div class="card-header">Productos/Servicios más cotizados</div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Producto/Servicio</th><th class="text-end">Cantidad</th></tr></thead><tbody><?php if (!empty($resumen['productos_top'])): ?><?php foreach ($resumen['productos_top'] as $item): ?><tr><td><?= e($item['nombre']) ?></td><td class="text-end fw-semibold"><?= (int) $item['total'] ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="2" class="text-center text-muted py-3">Sin productos cotizados.</td></tr><?php endif; ?></tbody></table></div></div></div>
  </div>

  <div class="card card-dashboard mb-3"><div class="card-header">Actividad reciente del negocio</div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Fecha</th></tr></thead><tbody><?php if (!empty($resumen['historial_reciente'])): ?><?php foreach ($resumen['historial_reciente'] as $h): ?><tr><td><?= e($h['usuario_nombre']) ?></td><td><?= e($h['modulo']) ?></td><td><?= e($h['accion']) ?></td><td><?= e($h['fecha_creacion']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="text-center text-muted py-3">Sin actividad reciente.</td></tr><?php endif; ?></tbody></table></div></div>

  <div class="card card-dashboard"><div class="card-header">Vendedores destacados</div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Vendedor</th><th class="text-end">Cotizaciones</th></tr></thead><tbody><?php if (!empty($resumen['vendedores_top'])): ?><?php foreach ($resumen['vendedores_top'] as $v): ?><tr><td><?= e($v['nombre']) ?></td><td class="text-end fw-semibold"><?= (int) $v['total'] ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="2" class="text-center text-muted py-3">Sin vendedores registrados.</td></tr><?php endif; ?></tbody></table></div></div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(() => {
  const labels = <?= json_encode($meses) ?>;
  const seriesConteo = <?= json_encode($conteosMes) ?>;
  const seriesMontos = <?= json_encode($montosMes) ?>;
  const canvas = document.getElementById('graficoCotizacionesMes');
  if (!canvas || !labels.length || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Cotizaciones',
          data: seriesConteo,
          backgroundColor: 'rgba(70, 50, 168, 0.35)',
          borderColor: '#4632a8',
          borderWidth: 1,
          borderRadius: 6
        },
        {
          type: 'line',
          label: 'Monto cotizado',
          data: seriesMontos,
          yAxisID: 'y1',
          borderColor: '#22b36d',
          backgroundColor: 'rgba(34, 179, 109, 0.18)',
          tension: 0.35,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { position: 'bottom' } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        y1: {
          beginAtZero: true,
          position: 'right',
          grid: { drawOnChartArea: false }
        }
      }
    }
  });

  const botonesFiltro = document.querySelectorAll('[data-kpi-filter]');
  const itemsKpi = document.querySelectorAll('[data-kpi-module]');
  if (botonesFiltro.length && itemsKpi.length) {
    botonesFiltro.forEach((boton) => {
      boton.addEventListener('click', () => {
        const modulo = boton.getAttribute('data-kpi-filter');
        botonesFiltro.forEach((b) => b.classList.remove('active'));
        boton.classList.add('active');

        itemsKpi.forEach((item) => {
          const itemModulo = item.getAttribute('data-kpi-module');
          item.classList.toggle('d-none', modulo !== 'all' && itemModulo !== modulo);
        });
      });
    });
  }
})();
</script>
