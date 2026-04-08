<section class="modulo-head d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Servicios / Productos</h1>
</section>

<div class="card mb-3">
  <div class="card-header">Nuevo ítem comercial</div>
  <div class="card-body">
    <?php
    $accion = url('/app/productos');
    $textoBoton = 'Guardar ítem';
    $mostrarCancelar = false;
    $mostrarModalCategoria = true;
    $modalId = 'modalNuevaCategoriaProductoIndex';
    $redirigirA = '/app/productos';
    require __DIR__ . '/_formulario.php';
    ?>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong>Listado de productos</strong>
      <div class="small text-muted">Registros encontrados: <?= count($productos) ?></div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= e(url('/app/productos/exportar/excel?q=' . urlencode($buscar))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
      <form class="d-flex gap-2" method="GET">
        <input class="form-control form-control-sm" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por nombre/código/SKU">
        <button class="btn btn-outline-secondary btn-sm">Buscar</button>
        <?php if ($buscar !== ''): ?>
          <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/productos')) ?>">Limpiar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-hover table-sm mb-0 tabla-admin">
      <thead class="table-light">
        <tr>
          <th>Código</th><th>SKU</th><th>Nombre</th><th>Tipo</th><th>Categoría</th><th>Precio</th><th>Stock actual</th><th>Stock mín.</th><th>Stock crítico</th><th>Estado stock</th><th>Estado</th><th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($productos)): ?>
          <tr><td colspan="12" class="text-center py-4 text-muted">No hay productos registrados con este criterio.</td></tr>
        <?php else: ?>
          <?php foreach($productos as $p): ?>
            <tr>
              <td><?= e($p['codigo']) ?></td>
              <td><?= e($p['sku'] ?: '-') ?></td>
              <td><?= e($p['nombre']) ?></td>
              <td><?= e($p['tipo'] ?? 'producto') ?></td>
              <td><?= e($p['categoria'] ?? '-') ?></td>
              <td>$<?= number_format((float)$p['precio'],2) ?></td>
              <?php $stockActual = (float)($p['stock_actual'] ?? 0); $stockMin = (float)($p['stock_minimo'] ?? 0); $stockCrit = (float)($p['stock_critico'] ?? $p['stock_aviso'] ?? 0); $estadoStock = $stockActual <= $stockCrit ? 'crítico' : ($stockActual <= $stockMin ? 'bajo' : 'normal'); $badgeStock = $estadoStock === 'crítico' ? 'text-bg-danger' : ($estadoStock === 'bajo' ? 'text-bg-warning' : 'text-bg-success'); ?>
              <td><strong><?= number_format($stockActual, 2) ?></strong></td>
              <td><?= number_format($stockMin, 2) ?></td>
              <td><?= number_format($stockCrit, 2) ?></td>
              <td>
                <button
                  type="button"
                  class="badge border-0 js-ver-trazabilidad <?= e($badgeStock) ?>"
                  data-url="<?= e(url('/app/productos/movimientos/' . $p['id'])) ?>"
                  data-producto="<?= e(($p['codigo'] ?? '') . ' · ' . ($p['nombre'] ?? '')) ?>"
                  data-bs-toggle="modal"
                  data-bs-target="#modalTrazabilidadProducto"
                  style="cursor:pointer;"
                >
                  <?= e($estadoStock) ?>
                </button>
              </td>
              <td><span class="badge <?= ($p['estado'] === 'activo') ? 'badge-estado-activo' : 'badge-estado-inactivo' ?>"><?= e($p['estado']) ?></span></td>
              <td class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= e(url('/app/productos/ver/' . $p['id'])) ?>">Ver</a></li><li><a class="dropdown-item" href="<?= e(url('/app/productos/editar/' . $p['id'])) ?>">Editar</a></li><li><form method="POST" action="<?= e(url('/app/productos/eliminar/' . $p['id'])) ?>" onsubmit="return confirm('¿Confirmas eliminar este producto?')"><?= csrf_campo() ?><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li></ul></div></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalTrazabilidadProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Trazabilidad de stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="small text-muted mb-2" id="trazabilidad_producto">Producto: -</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Origen</th>
                <th class="text-end">Entrada</th>
                <th class="text-end">Salida</th>
                <th class="text-end">Saldo</th>
                <th>Usuario</th>
                <th>Observación</th>
              </tr>
            </thead>
            <tbody id="trazabilidad_body">
              <tr><td colspan="8" class="text-center text-muted py-3">Selecciona un producto para ver su trazabilidad.</td></tr>
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

<script>
(() => {
  const modalEl = document.getElementById('modalTrazabilidadProducto');
  const productoEl = document.getElementById('trazabilidad_producto');
  const bodyEl = document.getElementById('trazabilidad_body');
  if (!modalEl || !productoEl || !bodyEl) return;

  const formatearNumero = (valor) => {
    const numero = Number(valor || 0);
    return Number.isFinite(numero) ? numero.toLocaleString('es-CL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
  };

  const escapeHtml = (valor) => String(valor ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const setCargando = () => {
    bodyEl.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Cargando movimientos...</td></tr>';
  };

  const setError = (mensaje) => {
    bodyEl.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">${escapeHtml(mensaje)}</td></tr>`;
  };

  const setSinDatos = () => {
    bodyEl.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Sin movimientos registrados para este producto.</td></tr>';
  };

  document.querySelectorAll('.js-ver-trazabilidad').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const endpoint = btn.dataset.url || '';
      productoEl.textContent = `Producto: ${btn.dataset.producto || '-'}`;
      setCargando();

      if (!endpoint) {
        setError('No se pudo resolver la ruta de trazabilidad.');
        return;
      }

      try {
        const respuesta = await fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await respuesta.json();
        if (!respuesta.ok || !data.ok) {
          setError(data.mensaje || 'No se pudo cargar la trazabilidad del producto.');
          return;
        }

        const movimientos = Array.isArray(data.movimientos) ? data.movimientos : [];
        if (!movimientos.length) {
          setSinDatos();
          return;
        }

        bodyEl.innerHTML = movimientos.map((mov) => `
          <tr>
            <td>${escapeHtml(mov.fecha_creacion || '-')}</td>
            <td>${escapeHtml(mov.tipo_movimiento || '-')}</td>
            <td>${escapeHtml(mov.modulo_origen || '-')}</td>
            <td class="text-end">${formatearNumero(mov.entrada)}</td>
            <td class="text-end">${formatearNumero(mov.salida)}</td>
            <td class="text-end"><strong>${formatearNumero(mov.saldo_resultante)}</strong></td>
            <td>${escapeHtml(mov.usuario_nombre || '-')}</td>
            <td>${escapeHtml(mov.observacion || '-')}</td>
          </tr>
        `).join('');
      } catch (error) {
        setError('No se pudo cargar la trazabilidad del producto.');
      }
    });
  });
})();
</script>
