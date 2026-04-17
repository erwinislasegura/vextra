<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$estadoActual = (string) ($estado ?? '');
$estados = ['' => 'Todas', 'pendiente' => 'Pendientes', 'aprobado' => 'Aprobadas', 'rechazado' => 'Rechazadas', 'anulado' => 'Anuladas'];
$formatearEnvio = static function (string $metodo): string {
    return match ($metodo) {
        'blue_express' => 'Blue Express',
        'chile_express' => 'Chile Express',
        default => 'Starken',
    };
};
$formatearEstado = static function (string $estadoPago): array {
    return match ($estadoPago) {
        'aprobado' => ['clase' => 'success', 'texto' => 'Aprobado'],
        'rechazado' => ['clase' => 'danger', 'texto' => 'Rechazado'],
        'anulado' => ['clase' => 'secondary', 'texto' => 'Anulado'],
        default => ['clase' => 'warning', 'texto' => 'Pendiente'],
    };
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Compras por catálogo</h1>
</div>

<form class="row g-2 mb-3" method="GET">
  <div class="col-auto">
    <select name="estado" class="form-select" onchange="this.form.submit()">
      <?php foreach ($estados as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= $estadoActual === $key ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
      <tr>
        <th>#</th>
        <th>Comprador</th>
        <th>Total</th>
        <th>Estado pago</th>
        <th>Fecha</th>
        <th class="text-end">Detalle</th>
      </tr>
      </thead>
      <tbody>
      <?php if ($compras === []): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Sin compras en este estado.</td></tr>
      <?php endif; ?>
      <?php foreach ($compras as $compra): ?>
        <?php
        $compraId = (int) ($compra['id'] ?? 0);
        $estadoPagoInfo = $formatearEstado((string) ($compra['estado_pago'] ?? 'pendiente'));
        $modalId = 'compraDetalleModal' . $compraId;
        ?>
        <tr>
          <td><?= $compraId ?></td>
          <td>
            <strong><?= e((string) ($compra['comprador_nombre'] ?? '')) ?></strong>
            <small class="text-muted d-block"><?= e((string) ($compra['comprador_correo'] ?? '-')) ?></small>
            <small class="text-muted d-block"><?= e((string) ($compra['comprador_telefono'] ?? '-')) ?></small>
          </td>
          <td><?= e($fmon((float) ($compra['total'] ?? 0))) ?></td>
          <td>
            <span class="badge text-bg-<?= e($estadoPagoInfo['clase']) ?>"><?= e($estadoPagoInfo['texto']) ?></span>
          </td>
          <td><small><?= e((string) ($compra['fecha_creacion'] ?? '')) ?></small></td>
          <td class="text-end">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>">
              Ver detalle
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($compras as $compra): ?>
  <?php $compraId = (int) ($compra['id'] ?? 0); $modalId = 'compraDetalleModal' . $compraId; ?>
  <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-labelledby="<?= e($modalId) ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="<?= e($modalId) ?>Label">Compra #<?= $compraId ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-2">Datos del comprador</h6>
                <div><strong>Nombre:</strong> <?= e((string) ($compra['comprador_nombre'] ?? '-')) ?></div>
                <div><strong>Correo:</strong> <?= e((string) ($compra['comprador_correo'] ?? '-')) ?></div>
                <div><strong>Teléfono:</strong> <?= e((string) ($compra['comprador_telefono'] ?? '-')) ?></div>
                <div><strong>Documento:</strong> <?= e((string) (($compra['comprador_documento'] ?? '') !== '' ? $compra['comprador_documento'] : '-')) ?></div>
                <div><strong>Empresa:</strong> <?= e((string) (($compra['comprador_empresa'] ?? '') !== '' ? $compra['comprador_empresa'] : '-')) ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-2">Datos de envío</h6>
                <div><strong>Método:</strong> <?= e($formatearEnvio((string) ($compra['envio_metodo'] ?? 'starken'))) ?></div>
                <div><strong>Dirección:</strong> <?= e((string) ($compra['envio_direccion'] ?? '-')) ?></div>
                <div><strong>Referencia:</strong> <?= e((string) (($compra['envio_referencia'] ?? '') !== '' ? $compra['envio_referencia'] : '-')) ?></div>
                <div><strong>Comuna / Ciudad:</strong> <?= e((string) ($compra['envio_comuna'] ?? '-')) ?> / <?= e((string) ($compra['envio_ciudad'] ?? '-')) ?></div>
                <div><strong>Región:</strong> <?= e((string) ($compra['envio_region'] ?? '-')) ?></div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Detalle de productos</h6>
            <span class="text-muted small">Total: <?= e($fmon((float) ($compra['total'] ?? 0))) ?> · <?= (int) ($compra['total_items'] ?? 0) ?> item(s)</span>
          </div>

          <div class="table-responsive border rounded">
            <table class="table table-sm align-middle mb-0">
              <thead>
              <tr>
                <th style="width:70px;">Foto</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Descripción</th>
                <th class="text-end">Cant.</th>
                <th class="text-end">P. unit.</th>
                <th class="text-end">Subtotal</th>
              </tr>
              </thead>
              <tbody>
              <?php if (($compra['items'] ?? []) === []): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Sin ítems registrados para esta compra.</td></tr>
              <?php endif; ?>
              <?php foreach (($compra['items'] ?? []) as $item): ?>
                <?php
                $meta = [];
                if (isset($item['metadata'])) {
                    $metaDecode = json_decode((string) $item['metadata'], true);
                    if (is_array($metaDecode)) {
                        $meta = $metaDecode;
                    }
                }
                $imagen = trim((string) ($item['imagen'] ?? $item['producto_imagen'] ?? $meta['imagen'] ?? ''));
                if ($imagen === '') {
                    $imagen = url('/img/placeholder-producto.svg');
                } elseif (preg_match('/^https?:\/\//i', $imagen) !== 1) {
                    $imagen = url('/' . ltrim($imagen, '/'));
                }
                $codigo = trim((string) ($item['codigo'] ?? $item['producto_codigo'] ?? $meta['codigo'] ?? '-'));
                $descripcion = trim((string) ($item['descripcion'] ?? $item['detalle'] ?? $meta['descripcion'] ?? '-'));
                if ($descripcion === '') {
                    $descripcion = '-';
                }
                ?>
                <tr>
                  <td>
                    <img src="<?= e($imagen) ?>" alt="<?= e((string) ($item['producto_nombre'] ?? 'Producto')) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:8px;background:#f3f4f6;">
                  </td>
                  <td><code><?= e($codigo) ?></code></td>
                  <td><?= e((string) ($item['producto_nombre'] ?? 'Producto')) ?></td>
                  <td class="text-muted"><?= e($descripcion) ?></td>
                  <td class="text-end"><?= (int) ($item['cantidad'] ?? 1) ?></td>
                  <td class="text-end"><?= e($fmon((float) ($item['precio_unitario'] ?? $item['precio'] ?? 0))) ?></td>
                  <td class="text-end"><?= e($fmon((float) ($item['subtotal'] ?? 0))) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
