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
$empresaNombreEtiqueta = trim((string) (($empresa['nombre_comercial'] ?? '') !== '' ? $empresa['nombre_comercial'] : ($empresa['razon_social'] ?? 'Mi empresa')));
$logoEmpresaSrc = !empty($empresa['logo']) ? (url('/app/logo-empresa') . '?v=' . urlencode((string) $empresa['logo'])) : null;
$colorPrimarioEtiqueta = (string) ($sliderCatalogo['catalogo_color_primario'] ?? '#1d4ed8');
$colorAcentoEtiqueta = (string) ($sliderCatalogo['catalogo_color_acento'] ?? '#2563eb');
if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colorPrimarioEtiqueta) !== 1) {
    $colorPrimarioEtiqueta = '#1d4ed8';
}
if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colorAcentoEtiqueta) !== 1) {
    $colorAcentoEtiqueta = '#2563eb';
}
$rubroEtiqueta = (string) ($empresa['etiqueta_rubro'] ?? 'general');
$frasesPorRubro = [
    'tenis_mesa' => [
        'En tenis de mesa, la precisión transforma cada punto en una victoria.',
        'La constancia en cada saque te acerca al siguiente triunfo.',
        'Cada rally es una oportunidad para demostrar control y carácter.',
        'La velocidad impresiona, pero la estrategia gana partidos.',
        'Mantén la mirada en la pelota y la mente en el objetivo.',
        'Disciplina, reflejos y actitud: la fórmula del campeón de mesa.',
        'El mejor punto siempre empieza con una buena preparación.',
        'Juega cada bola con convicción, como si fuera la del partido.',
    ],
    'fitness' => [
        'La constancia diaria construye resultados extraordinarios.',
        'Entrena con propósito, compite contigo mismo cada día.',
        'El progreso nace de la disciplina, no de la prisa.',
        'Tu esfuerzo de hoy es tu ventaja de mañana.',
    ],
    'tecnologia' => [
        'Innovar es convertir ideas en soluciones reales.',
        'La tecnología avanza, tu negocio también.',
        'Precisión en cada proceso, excelencia en cada entrega.',
        'Optimizar hoy es crecer mañana.',
    ],
    'moda' => [
        'El estilo se expresa en cada detalle.',
        'Diseño, identidad y actitud en cada elección.',
        'La elegancia comienza con autenticidad.',
        'Cada prenda cuenta una historia única.',
    ],
    'hogar' => [
        'Cada espacio bien pensado mejora la vida diaria.',
        'Comodidad y diseño para disfrutar cada momento.',
        'Un gran hogar se construye con pequeños detalles.',
        'Bienestar para tu casa, todos los días.',
    ],
    'general' => [
        'Excelencia en cada entrega, confianza en cada compra.',
        'Compromiso y calidad de principio a fin.',
        'Gracias por confiar en nosotros.',
        'Tu satisfacción es nuestra mejor referencia.',
    ],
];
$frasesPersonalizadasRaw = (string) ($empresa['etiqueta_frases'] ?? '');
$frasesPersonalizadas = array_values(array_filter(array_map(
    static fn(string $linea): string => trim($linea),
    preg_split('/\r\n|\r|\n/', $frasesPersonalizadasRaw) ?: []
), static fn(string $linea): bool => $linea !== ''));
$frasesEtiqueta = $frasesPersonalizadas !== [] ? $frasesPersonalizadas : ($frasesPorRubro[$rubroEtiqueta] ?? $frasesPorRubro['general']);
?>
<style>
  .shipping-label-template { display: none; }
</style>
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
  <?php
    $fraseEtiqueta = $frasesEtiqueta[$compraId % count($frasesEtiqueta)];
    $codigoEtiqueta = 'CP-' . str_pad((string) $compraId, 6, '0', STR_PAD_LEFT);
    $fechaEtiqueta = (string) ($compra['fecha_creacion'] ?? '');
    try {
        $fechaObj = new DateTime($fechaEtiqueta);
        $fechaEtiqueta = $fechaObj->format('d/m/Y H:i');
    } catch (Throwable $e) {
        $fechaEtiqueta = (string) ($compra['fecha_creacion'] ?? '-');
    }
  ?>
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
          <button type="button" class="btn btn-primary" onclick="imprimirEtiquetaCompra(<?= $compraId ?>)">
            <i class="bi bi-printer me-1"></i>Imprimir etiqueta
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <template id="shippingLabelTemplate<?= $compraId ?>" class="shipping-label-template">
    <div class="shipping-label-card">
      <div class="shipping-label-header">
        <div class="shipping-label-brand">
          <?php if ($logoEmpresaSrc): ?>
            <img src="<?= e($logoEmpresaSrc) ?>" alt="Logo empresa" class="shipping-label-logo">
          <?php else: ?>
            <div class="shipping-label-logo-placeholder"><?= e(strtoupper(substr($empresaNombreEtiqueta, 0, 1))) ?></div>
          <?php endif; ?>
          <div>
            <div class="shipping-label-company"><?= e($empresaNombreEtiqueta) ?></div>
            <div class="shipping-label-date">Fecha etiqueta: <?= e($fechaEtiqueta) ?></div>
          </div>
        </div>
        <div class="shipping-label-code"><?= e($codigoEtiqueta) ?></div>
      </div>

      <div class="shipping-label-section">
        <h3>Datos personales</h3>
        <div class="shipping-label-grid">
          <div><strong>Nombre:</strong> <?= e((string) ($compra['comprador_nombre'] ?? '-')) ?></div>
          <div><strong>Documento:</strong> <?= e((string) (($compra['comprador_documento'] ?? '') !== '' ? $compra['comprador_documento'] : '-')) ?></div>
          <div><strong>Correo:</strong> <?= e((string) ($compra['comprador_correo'] ?? '-')) ?></div>
          <div><strong>Teléfono:</strong> <?= e((string) ($compra['comprador_telefono'] ?? '-')) ?></div>
          <div><strong>Empresa:</strong> <?= e((string) (($compra['comprador_empresa'] ?? '') !== '' ? $compra['comprador_empresa'] : '-')) ?></div>
          <div><strong>Método envío:</strong> <?= e($formatearEnvio((string) ($compra['envio_metodo'] ?? 'starken'))) ?></div>
        </div>
      </div>

      <div class="shipping-label-section">
        <h3>Datos de envío</h3>
        <div><strong>Dirección:</strong> <?= e((string) ($compra['envio_direccion'] ?? '-')) ?></div>
        <div><strong>Referencia:</strong> <?= e((string) (($compra['envio_referencia'] ?? '') !== '' ? $compra['envio_referencia'] : '-')) ?></div>
        <div><strong>Comuna / Ciudad:</strong> <?= e((string) ($compra['envio_comuna'] ?? '-')) ?> / <?= e((string) ($compra['envio_ciudad'] ?? '-')) ?></div>
        <div><strong>Región:</strong> <?= e((string) ($compra['envio_region'] ?? '-')) ?></div>
      </div>

      <div class="shipping-label-footer">
        <span class="shipping-label-motto">“<?= e($fraseEtiqueta) ?>”</span>
        <div class="shipping-label-signature">Creado con tecnología Vextra.cl</div>
      </div>
    </div>
  </template>
<?php endforeach; ?>

<script>
  function imprimirEtiquetaCompra(compraId) {
    const template = document.getElementById('shippingLabelTemplate' + compraId);
    if (!template) {
      return;
    }

    const labelHtml = template.innerHTML.trim();
    const colorPrimario = <?= json_encode($colorPrimarioEtiqueta, JSON_UNESCAPED_UNICODE) ?>;
    const colorAcento = <?= json_encode($colorAcentoEtiqueta, JSON_UNESCAPED_UNICODE) ?>;
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    if (!printWindow) {
      alert('No fue posible abrir la ventana de impresión. Revisa si tu navegador bloqueó la ventana emergente.');
      return;
    }

    printWindow.document.write(`
      <!doctype html>
      <html lang="es">
      <head>
        <meta charset="utf-8">
        <title>Etiqueta de envío</title>
        <style>
          :root {
            --label-primary: ${colorPrimario};
            --label-accent: ${colorAcento};
          }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: #0f172a;
          }
          .shipping-label-card {
            background: #fff;
            border: 2px solid var(--label-primary);
            border-radius: 18px;
            padding: 20px;
            max-width: 760px;
            margin: 0 auto;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
          }
          .shipping-label-header {
            border-bottom: 2px solid rgba(15, 23, 42, 0.12);
            padding-bottom: 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
          }
          .shipping-label-brand {
            display: flex;
            align-items: center;
            gap: 12px;
          }
          .shipping-label-logo {
            width: 82px;
            height: 56px;
            object-fit: contain;
          }
          .shipping-label-logo-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: var(--label-primary);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
          }
          .shipping-label-company {
            font-size: 18px;
            font-weight: 800;
            color: var(--label-primary);
          }
          .shipping-label-date {
            font-size: 12px;
            color: #475569;
          }
          .shipping-label-code {
            border: 2px solid var(--label-accent);
            border-radius: 999px;
            padding: 6px 14px;
            font-weight: 700;
            color: var(--label-primary);
            white-space: nowrap;
            font-size: 13px;
          }
          .shipping-label-section {
            border: 1px solid #dbeafe;
            border-left: 6px solid var(--label-accent);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 10px;
            font-size: 14px;
          }
          .shipping-label-section h3 {
            margin: 0 0 8px 0;
            color: var(--label-primary);
            font-size: 15px;
          }
          .shipping-label-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
          }
          .shipping-label-footer {
            margin-top: 14px;
            border-top: 1px dashed #94a3b8;
            padding-top: 12px;
            text-align: center;
          }
          .shipping-label-motto {
            font-size: 14px;
            color: #0f172a;
            font-style: italic;
            font-weight: 500;
            display: block;
            margin-bottom: 6px;
          }
          .shipping-label-signature {
            font-size: 11px;
            color: #64748b;
            letter-spacing: .2px;
          }
          @media print {
            body { background: #fff; padding: 0; }
            .shipping-label-card {
              box-shadow: none;
              max-width: none;
              page-break-inside: avoid;
            }
          }
        </style>
      </head>
      <body>${labelHtml}</body>
      </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
    }, 220);
  }
</script>
