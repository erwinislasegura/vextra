<?php
$flash = obtener_flash();
$estado = (string) ($cotizacion['estado'] ?? 'borrador');
$badge = $estado === 'aprobada' ? 'success' : ($estado === 'rechazada' ? 'danger' : 'warning');
$puedeDecidir = in_array($estado, ['enviada', 'borrador'], true);
?>
<section class="py-4">
  <div class="container" style="max-width: 980px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Cotización pública <?= e($cotizacion['numero'] ?? '') ?></h1>
      <span class="badge text-bg-<?= e($badge) ?> text-uppercase"><?= e($estado) ?></span>
    </div>

    <div class="d-flex gap-2 mb-3">
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/cotizacion/publica/' . $token . '/imprimir')) ?>" target="_blank" rel="noopener">Imprimir cotización</a>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
      <div class="card-body row g-3">
        <div class="col-md-6"><strong>Cliente:</strong> <?= e($cotizacion['cliente'] ?? '') ?></div>
        <div class="col-md-3"><strong>Emisión:</strong> <?= e($cotizacion['fecha_emision'] ?? '') ?></div>
        <div class="col-md-3"><strong>Vencimiento:</strong> <?= e($cotizacion['fecha_vencimiento'] ?? '') ?></div>
        <div class="col-md-6"><strong>Vendedor:</strong> <?= e($cotizacion['vendedor'] ?? '') ?></div>
        <div class="col-md-6"><strong>Correo de contacto:</strong> <?= e($cotizacion['cliente_correo'] ?? '') ?></div>
        <div class="col-md-6"><strong>Teléfono cliente:</strong> <?= e($cotizacion['cliente_telefono'] ?? '') ?></div>
        <div class="col-md-6"><strong>Dirección cliente:</strong> <?= e(trim((string) (($cotizacion['cliente_direccion'] ?? '') . ' ' . ($cotizacion['cliente_ciudad'] ?? '')))) ?></div>
        <div class="col-md-6"><strong>Identificador fiscal:</strong> <?= e($cotizacion['cliente_identificador_fiscal'] ?? '') ?></div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Detalle del producto / servicio</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead>
          <tr>
            <th>Producto</th>
            <th>Detalle</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Total</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach (($cotizacion['items'] ?? []) as $item): ?>
            <?php $detalle = trim((string) ($item['descripcion'] ?? '')) !== '' ? (string) $item['descripcion'] : ((string) ($item['producto_descripcion'] ?? '') !== '' ? (string) $item['producto_descripcion'] : (string) ($item['producto_nombre'] ?? 'Ítem')); ?>
            <tr>
              <td><?= e((string) ($item['producto_nombre'] ?? $item['codigo'] ?? 'Ítem')) ?></td>
              <td><?= e($detalle) ?></td>
              <td class="text-end"><?= e(number_format((float) ($item['cantidad'] ?? 0), 2)) ?></td>
              <td class="text-end">$<?= e(number_format((float) ($item['precio_unitario'] ?? 0), 2)) ?></td>
              <td class="text-end">$<?= e(number_format((float) ($item['total'] ?? 0), 2)) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>$<?= e(number_format((float) ($cotizacion['subtotal'] ?? 0), 2)) ?></strong></div>
        <div class="d-flex justify-content-between"><span>Descuento</span><strong>$<?= e(number_format((float) ($cotizacion['descuento'] ?? 0), 2)) ?></strong></div>
        <div class="d-flex justify-content-between"><span>IVA</span><strong>$<?= e(number_format((float) ($cotizacion['impuesto'] ?? 0), 2)) ?></strong></div>
        <hr>
        <div class="d-flex justify-content-between h5 mb-0"><span>Total</span><strong>$<?= e(number_format((float) ($cotizacion['total'] ?? 0), 2)) ?></strong></div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Condiciones comerciales</div>
      <div class="card-body">
        <div class="mb-2"><strong>Observaciones:</strong><br><?= nl2br(e((string) ($cotizacion['observaciones'] ?? 'Sin observaciones'))) ?></div>
        <div><strong>Términos y condiciones:</strong><br><?= nl2br(e((string) ($cotizacion['terminos_condiciones'] ?? 'Sin términos definidos'))) ?></div>
      </div>
    </div>

    <?php if ($puedeDecidir): ?>
      <div class="card mb-3">
        <div class="card-header">Aprobación del cliente</div>
        <div class="card-body">
          <form method="POST" action="<?= e(url('/cotizacion/publica/' . $token . '/decision')) ?>" id="form_aprobacion_cliente" class="mb-2">
            <input type="hidden" name="decision" value="aprobada">
            <input type="hidden" name="firma_cliente" id="firma_cliente">
            <div class="mb-2">
              <label class="form-label">Nombre de quien aprueba</label>
              <input type="text" class="form-control" name="nombre_firmante_cliente" id="nombre_firmante_cliente" placeholder="Nombre completo" required>
            </div>
            <label class="form-label">Firma del cliente</label>
            <div class="border rounded p-2 bg-light">
              <canvas id="firma_canvas" style="width:100%;height:170px;background:#fff;border:1px dashed #adb5bd;border-radius:.35rem;"></canvas>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" id="limpiar_firma">Limpiar firma</button>
              <button class="btn btn-success btn-sm">Aceptar cotización con firma</button>
            </div>
            <div class="form-text">La firma es obligatoria para aprobar la cotización.</div>
          </form>

          <form method="POST" action="<?= e(url('/cotizacion/publica/' . $token . '/decision')) ?>" onsubmit="return confirm('¿Confirmas rechazar esta cotización?');">
            <input type="hidden" name="decision" value="rechazada">
            <button class="btn btn-outline-danger btn-sm">Rechazar cotización</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($cotizacion['firma_cliente'])): ?>
      <div class="card">
        <div class="card-header">Firma registrada</div>
        <div class="card-body">
          <div><strong>Firmante:</strong> <?= e((string) ($cotizacion['nombre_firmante_cliente'] ?? 'Cliente')) ?></div>
          <div><strong>Fecha:</strong> <?= e((string) ($cotizacion['fecha_aprobacion_cliente'] ?? '')) ?></div>
          <img src="<?= e((string) $cotizacion['firma_cliente']) ?>" alt="Firma del cliente" style="max-width:360px; width:100%; border:1px solid #dee2e6; border-radius:.35rem; margin-top:10px; background:#fff;">
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  const form = document.getElementById('form_aprobacion_cliente');
  const canvas = document.getElementById('firma_canvas');
  const inputFirma = document.getElementById('firma_cliente');
  const limpiarBtn = document.getElementById('limpiar_firma');
  if (!form || !canvas || !inputFirma || !limpiarBtn) {
    return;
  }

  const contexto = canvas.getContext('2d');
  let dibujando = false;
  let huboTrazo = false;

  function ajustarCanvas() {
    const ratio = window.devicePixelRatio || 1;
    const ancho = canvas.clientWidth;
    const alto = canvas.clientHeight;
    canvas.width = Math.floor(ancho * ratio);
    canvas.height = Math.floor(alto * ratio);
    contexto.setTransform(1, 0, 0, 1, 0, 0);
    contexto.scale(ratio, ratio);
    contexto.lineWidth = 2;
    contexto.lineCap = 'round';
    contexto.lineJoin = 'round';
    contexto.strokeStyle = '#212529';
    contexto.fillStyle = '#ffffff';
    contexto.fillRect(0, 0, ancho, alto);
  }

  function posicion(evento) {
    const rect = canvas.getBoundingClientRect();
    const punto = evento.touches ? evento.touches[0] : evento;
    return { x: punto.clientX - rect.left, y: punto.clientY - rect.top };
  }

  function iniciar(evento) {
    dibujando = true;
    const p = posicion(evento);
    contexto.beginPath();
    contexto.moveTo(p.x, p.y);
    evento.preventDefault();
  }

  function mover(evento) {
    if (!dibujando) return;
    const p = posicion(evento);
    contexto.lineTo(p.x, p.y);
    contexto.stroke();
    huboTrazo = true;
    evento.preventDefault();
  }

  function terminar() {
    dibujando = false;
  }

  ajustarCanvas();
  window.addEventListener('resize', ajustarCanvas);
  canvas.addEventListener('mousedown', iniciar);
  canvas.addEventListener('mousemove', mover);
  window.addEventListener('mouseup', terminar);
  canvas.addEventListener('touchstart', iniciar, { passive: false });
  canvas.addEventListener('touchmove', mover, { passive: false });
  canvas.addEventListener('touchend', terminar);

  limpiarBtn.addEventListener('click', function () {
    huboTrazo = false;
    ajustarCanvas();
    inputFirma.value = '';
  });

  form.addEventListener('submit', function (evento) {
    if (!huboTrazo) {
      evento.preventDefault();
      alert('Debes registrar una firma para aprobar la cotización.');
      return;
    }
    inputFirma.value = canvas.toDataURL('image/png');
  });
})();
</script>
