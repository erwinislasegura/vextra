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
    <div class="d-flex flex-wrap gap-2 justify-content-lg-end w-100 w-lg-auto">
      <a href="<?= e(url('/app/productos/exportar/excel?q=' . urlencode($buscar))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
      <form class="d-flex flex-wrap gap-2 align-items-center" method="GET">
        <input class="form-control form-control-sm flex-grow-1" style="min-width: min(260px, 100%);" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por nombre/código/SKU">
        <button class="btn btn-outline-secondary btn-sm">Buscar</button>
        <?php if ($buscar !== ''): ?>
          <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/productos')) ?>">Limpiar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-sm mb-0 tabla-admin tabla-admin--stack tabla-admin--stack-xl">
      <thead class="table-light">
        <tr>
          <th>Código</th><th>Nombre</th><th>Tipo</th><th>Categoría</th><th>Precio</th><th>Oferta</th><th>Destacado</th><th>Próximamente</th><th>Días llegada</th><th>Stock actual</th><th>Estado stock</th><th>Estado</th><th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($productos)): ?>
          <tr><td colspan="13" class="text-center py-4 text-muted">No hay productos registrados con este criterio.</td></tr>
        <?php else: ?>
          <?php foreach($productos as $p): ?>
            <tr>
              <td data-label="Código"><?= e($p['codigo']) ?></td>
              <td data-label="Nombre"><?= e($p['nombre']) ?></td>
              <td data-label="Tipo"><?= e($p['tipo'] ?? 'producto') ?></td>
              <td data-label="Categoría"><?= e($p['categoria'] ?? '-') ?></td>
              <td data-label="Precio">$<?= number_format((float)$p['precio'],2) ?></td>
              <td data-label="Oferta"><?php if ((float) ($p['precio_oferta'] ?? 0) > 0): ?>$<?= number_format((float) $p['precio_oferta'], 2) ?><?php else: ?>-<?php endif; ?></td>
              <td data-label="Destacado"><?php if ((int) ($p['destacado_catalogo'] ?? 0) === 1): ?><span class="badge text-bg-primary">Sí</span><?php else: ?><span class="text-muted">No</span><?php endif; ?></td>
              <td data-label="Próximamente"><?php if ((int) ($p['proximo_catalogo'] ?? 0) === 1): ?><span class="badge text-bg-warning">Sí</span><?php else: ?><span class="text-muted">No</span><?php endif; ?></td>
              <td data-label="Días llegada"><?= (int) ($p['proximo_dias_catalogo'] ?? 0) ?></td>
              <?php $stockActual = (float)($p['stock_actual'] ?? 0); $stockMin = (float)($p['stock_minimo'] ?? 0); $stockCrit = (float)($p['stock_critico'] ?? 0); if ($stockCrit <= 0) { $stockCrit = (float)($p['stock_aviso'] ?? 0); } $estadoStock = $stockActual <= $stockCrit ? 'crítico' : ($stockActual <= $stockMin ? 'bajo' : 'normal'); $badgeStock = $estadoStock === 'crítico' ? 'text-bg-danger' : ($estadoStock === 'bajo' ? 'text-bg-warning' : 'text-bg-success'); ?>
              <td data-label="Stock actual"><strong><?= number_format($stockActual, 2) ?></strong></td>
              <td data-label="Estado stock"><span class="badge <?= e($badgeStock) ?>"><?= e($estadoStock) ?></span></td>
              <td data-label="Estado"><span class="badge <?= ($p['estado'] === 'activo') ? 'badge-estado-activo' : 'badge-estado-inactivo' ?>"><?= e($p['estado']) ?></span></td>
              <td data-label="Acciones" class="text-end"><div class="dropdown dropup"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button><ul class="dropdown-menu dropdown-menu-end"><li><button type="button" class="dropdown-item js-ver-detalle-producto" data-producto='<?= e(json_encode(['id' => (int) ($p['id'] ?? 0), 'codigo' => (string) ($p['codigo'] ?? ''), 'sku' => (string) ($p['sku'] ?? ''), 'nombre' => (string) ($p['nombre'] ?? ''), 'tipo' => (string) ($p['tipo'] ?? 'producto'), 'categoria' => (string) ($p['categoria'] ?? '-'), 'precio' => (float) ($p['precio'] ?? 0), 'precio_oferta' => (float) ($p['precio_oferta'] ?? 0), 'stock_actual' => (float) ($p['stock_actual'] ?? 0), 'stock_minimo' => (float) ($p['stock_minimo'] ?? 0), 'stock_critico' => (float) ($p['stock_critico'] ?? 0), 'estado' => (string) ($p['estado'] ?? 'activo'), 'descripcion' => (string) ($p['descripcion'] ?? '-')], JSON_UNESCAPED_UNICODE)) ?>' data-bs-toggle="modal" data-bs-target="#modalDetalleProducto">Ver detalle</button></li><li><button type="button" class="dropdown-item js-ver-trazabilidad" data-url="<?= e(url('/app/productos/movimientos/' . $p['id'])) ?>" data-producto="<?= e(($p['codigo'] ?? '') . ' · ' . ($p['nombre'] ?? '')) ?>" data-bs-toggle="modal" data-bs-target="#modalTrazabilidadProducto">Ver movimientos</button></li><li><a class="dropdown-item" href="<?= e(url('/app/productos/editar/' . $p['id'])) ?>">Editar</a></li><li><form method="POST" action="<?= e(url('/app/productos/eliminar/' . $p['id'])) ?>" onsubmit="return confirm('¿Confirmas eliminar este producto?')"><?= csrf_campo() ?><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li></ul></div></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalDetalleProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <tbody id="detalleProductoBody">
              <tr><td colspan="2" class="text-center text-muted py-3">Selecciona un producto para ver su detalle.</td></tr>
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
  const detalleBodyEl = document.getElementById('detalleProductoBody');
  const productoEl = document.getElementById('trazabilidad_producto');
  const bodyEl = document.getElementById('trazabilidad_body');
  if (!modalEl || !productoEl || !bodyEl || !detalleBodyEl) return;

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

  const renderDetalle = (productoRaw) => {
    let producto = {};
    try {
      producto = JSON.parse(productoRaw || '{}');
    } catch (error) {
      producto = {};
    }
    const precio = Number(producto.precio || 0);
    const precioOferta = Number(producto.precio_oferta || 0);
    const filas = [
      ['Código', producto.codigo || '-'],
      ['SKU', producto.sku || '-'],
      ['Nombre', producto.nombre || '-'],
      ['Tipo', producto.tipo || '-'],
      ['Categoría', producto.categoria || '-'],
      ['Precio normal', `$${formatearNumero(precio)}`],
      ['Precio oferta', precioOferta > 0 ? `$${formatearNumero(precioOferta)}` : '-'],
      ['Stock actual', formatearNumero(producto.stock_actual)],
      ['Stock mínimo', formatearNumero(producto.stock_minimo)],
      ['Stock crítico', formatearNumero(producto.stock_critico)],
      ['Estado', producto.estado || '-'],
      ['Descripción', producto.descripcion || '-'],
    ];
    detalleBodyEl.innerHTML = filas.map((fila) => `
      <tr>
        <th class="text-muted" style="width:34%;">${escapeHtml(fila[0])}</th>
        <td>${escapeHtml(fila[1])}</td>
      </tr>
    `).join('');
  };

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

  document.querySelectorAll('.js-ver-detalle-producto').forEach((btn) => {
    btn.addEventListener('click', () => {
      renderDetalle(btn.dataset.producto || '{}');
    });
  });
})();
</script>
