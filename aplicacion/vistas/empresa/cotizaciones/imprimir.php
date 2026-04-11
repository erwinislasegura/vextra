<?php
$empresaNombre = trim((string) (($empresa['nombre_comercial'] ?? '') !== '' ? $empresa['nombre_comercial'] : ($empresa['razon_social'] ?? 'Comercial')));
$clienteNombre = trim((string) (($cotizacion['cliente_razon_social'] ?? '') !== '' ? $cotizacion['cliente_razon_social'] : ($cotizacion['cliente'] ?? '')));
$moneda = 'CLP';
$modoPdf = ($_GET['modo'] ?? '') === 'pdf';
$descuentoMonto = (float) ($cotizacion['descuento'] ?? 0);
$descuentoTexto = (($cotizacion['descuento_tipo'] ?? 'valor') === 'porcentaje')
    ? number_format((float) ($cotizacion['descuento_valor'] ?? 0), 2) . '%'
    : '$' . number_format($descuentoMonto, 0, ',', '.');
$descuentoDetalle = (($cotizacion['descuento_tipo'] ?? 'valor') === 'porcentaje')
    ? $descuentoTexto . ' ($' . number_format($descuentoMonto, 0, ',', '.') . ')'
    : '$' . number_format($descuentoMonto, 0, ',', '.');
$neto = max(0, (float) ($cotizacion['subtotal'] ?? 0) - $descuentoMonto);
$fechaEmision = !empty($cotizacion['fecha_emision']) ? date('d-m-Y', strtotime((string) $cotizacion['fecha_emision'])) : '';
$fechaVencimiento = !empty($cotizacion['fecha_vencimiento']) ? date('d-m-Y', strtotime((string) $cotizacion['fecha_vencimiento'])) : '';
$listaNombre = trim((string) ($listaAplicada['nombre'] ?? ''));
$descuentoListaMonto = 0.0;
foreach (($cotizacion['items'] ?? []) as $it) {
    $descuentoListaMonto += (float) ($it['descuento_monto'] ?? 0);
}
$mostrarTarjetaLista = $listaNombre !== '' && $descuentoListaMonto > 0;
$logoEmpresaSrc = !empty($empresa['logo']) ? (url('/app/logo-empresa') . '?v=' . urlencode((string) $empresa['logo'])) : null;
?>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 24px;
    background: #eef2f7;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
    font-size: 12.5px;
    line-height: 1.25;
  }
  .toolbar {
    max-width: 980px;
    margin: 0 auto 12px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .hoja {
    max-width: 980px;
    margin: 0 auto;
    background: #fff;
    padding: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
    border-radius: 8px;
  }
  .encabezado {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    border-bottom: 2px solid #1f4e79;
    padding-bottom: 10px;
    margin-bottom: 10px;
  }
  .empresa, .doc { width: 50%; }
  .empresa-logo { max-width: 180px; max-height: 70px; object-fit: contain; display: block; margin-bottom: 8px; }
  .empresa h1 {
    margin: 0 0 6px;
    color: #1f4e79;
    font-size: 22px;
  }
  .empresa p { margin: 1px 0; font-size: 12px; }
  .doc { text-align: right; }
  .doc-inline {
    margin-top: 14px;
    padding: 2px 0;
    border: none;
    background: transparent;
    display: inline-flex;
    gap: 10px;
    flex-direction: column;
    align-items: flex-end;
    font-size: 11px;
    color: #111827;
    min-width: 230px;
  }
  .doc-inline .doc-titulo { color: #1f4e79; }
  .doc-inline .doc-meta { color: #111827; }
  .lista-precio {
    margin: 0 0 10px;
    background: #f8fafc;
    border: 1px solid #dbe2ea;
    border-left: 4px solid #1f4e79;
    padding: 6px 8px;
    font-size: 11.5px;
  }
  .tarjeta-lista {
    margin: 0 0 10px;
    background: #ecfdf3;
    border: 1px solid #b7efcf;
    border-left: 4px solid #2f9e62;
    padding: 8px 10px;
    font-size: 11.5px;
    color: #1f5137;
  }
  .bloque { margin-bottom: 10px; }
  .bloque h3 {
    margin: 0 0 8px;
    font-size: 13px;
    color: #1f4e79;
    border-bottom: 1px solid #d8dee8;
    padding-bottom: 5px;
  }
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 20px;
  }
  .dato { font-size: 11.5px; }
  .dato strong { color: #111827; }
  table { width: 100%; border-collapse: collapse; }
  .tabla-items th {
    background: #1f4e79;
    color: #fff;
    font-weight: 600;
    font-size: 10.5px;
    padding: 5px 5px;
    text-align: left;
  }
  .tabla-items td {
    border: 1px solid #dbe2ea;
    padding: 5px;
    font-size: 10.5px;
    vertical-align: top;
  }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .totales {
    width: 320px;
    margin-left: auto;
    margin-top: 6px;
  }
  .totales td {
    border: 1px solid #dbe2ea;
    padding: 5px 8px;
    font-size: 11px;
  }
  .totales .label { background: #f8fafc; font-weight: 700; }
  .totales .final td {
    background: #1f4e79;
    color: #fff;
    font-weight: 700;
    font-size: 12px;
  }
  .nota {
    background: #f8fafc;
    border-left: 4px solid #1f4e79;
    padding: 8px 10px;
    font-size: 11px;
  }
  ul {
    margin: 6px 0 0 16px;
    padding: 0;
    font-size: 11px;
    line-height: 1.45;
  }
  .firmas {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
    margin-top: 14px;
  }
  .firma { text-align: center; padding-top: 18px; font-size: 11px; }
  .linea { border-top: 1px solid #4b5563; margin-bottom: 6px; }
  .pie {
    margin-top: 8px;
    border-top: 1px solid #dbe2ea;
    padding-top: 8px;
    font-size: 10px;
    color: #6b7280;
    text-align: center;
  }

  @page { size: letter; margin: 10mm; }
  @media print {
    html, body { width: 100%; height: auto; }
    body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .toolbar { display: none !important; }
    .hoja {
      width: 100%;
      max-width: none;
      margin: 0;
      padding: 14px;
      box-shadow: none;
      border-radius: 0;
      background: #ffffff;
    }
  }

  @media screen and (max-width: 820px) {
    body { padding: 12px; }
    .hoja { padding: 20px; }
    .encabezado,
    .grid-2,
    .firmas { grid-template-columns: 1fr; display: grid; }
    .empresa, .doc { width: 100%; }
    .doc { text-align: left; }
    .doc-inline { align-items: flex-start; min-width: 0; display: flex; }
    .totales { width: 100%; }
  }
</style>

<div class="toolbar no-print">
  <button class="btn btn-dark btn-sm" type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
  <?php if (!empty($esVistaPublica ?? false)): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/cotizacion/publica/' . ($token ?? ''))) ?>">Volver</a>
  <?php else: ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/cotizaciones/ver/' . $cotizacion['id'])) ?>">Volver</a>
  <?php endif; ?>
</div>

<div class="hoja">
  <div class="encabezado">
    <div class="empresa">
      <?php if ($logoEmpresaSrc): ?>
        <img src="<?= e($logoEmpresaSrc) ?>" alt="Logo empresa" class="empresa-logo">
      <?php endif; ?>
      <h1><?= e($empresaNombre) ?></h1>
      <p><strong>RUT:</strong> <?= e($empresa['identificador_fiscal'] ?? '') ?></p>
      <p><?= e(trim((string) (($empresa['direccion'] ?? '') . ', ' . ($empresa['ciudad'] ?? '')))) ?></p>
      <p><strong>Teléfono:</strong> <?= e($empresa['telefono'] ?? '') ?></p>
      <p><strong>Correo:</strong> <?= e($empresa['correo'] ?? '') ?></p>
      <p><strong>Web:</strong> <?= e($empresa['sitio_web'] ?? '') ?></p>
    </div>
    <div class="doc">
      <div class="doc-inline">
        <span class="doc-titulo"><strong>COTIZACIÓN</strong></span>
        <span class="doc-meta"><strong>N°:</strong> <?= e($cotizacion['numero'] ?? '') ?></span>
        <span class="doc-meta"><strong>Fecha:</strong> <?= e($fechaEmision) ?></span>
        <span class="doc-meta"><strong>Validez:</strong> <?= e($fechaVencimiento) ?></span>
      </div>
    </div>
  </div>

  <div class="lista-precio">
    <strong>Lista de precios aplicada:</strong>
    <?= e($listaNombre !== '' ? $listaNombre : 'Sin lista de precios específica (precio base).') ?>
  </div>
  <?php if ($mostrarTarjetaLista): ?>
    <div class="tarjeta-lista">
      <strong>Descuento por lista aplicado:</strong>
      <?= e($listaNombre) ?> · <strong>$<?= number_format($descuentoListaMonto, 0, ',', '.') ?></strong>
    </div>
  <?php endif; ?>

  <div class="bloque">
    <h3>Datos del cliente</h3>
    <div class="grid-2">
      <div class="dato"><strong>Cliente:</strong> <?= e($clienteNombre) ?></div>
      <div class="dato"><strong>RUT:</strong> <?= e($cotizacion['cliente_identificador_fiscal'] ?? '') ?></div>
      <div class="dato"><strong>Contacto:</strong> <?= e($cotizacion['cliente'] ?? '') ?></div>
      <div class="dato"><strong>Correo:</strong> <?= e($cotizacion['cliente_correo'] ?? '') ?></div>
      <div class="dato"><strong>Teléfono:</strong> <?= e($cotizacion['cliente_telefono'] ?? '') ?></div>
      <div class="dato"><strong>Dirección:</strong> <?= e(trim((string) (($cotizacion['cliente_direccion'] ?? '') . ', ' . ($cotizacion['cliente_ciudad'] ?? '')))) ?></div>
    </div>
  </div>

  <div class="bloque">
    <h3>Detalle de la cotización</h3>
    <div class="grid-2" style="margin-bottom: 8px;">
      <div class="dato"><strong>Vendedor:</strong> <?= e($cotizacion['vendedor'] ?? '') ?></div>
      <div class="dato"><strong>Moneda:</strong> <?= e($moneda) ?></div>
      <div class="dato"><strong>Forma de pago:</strong> <?= e($cotizacion['forma_pago'] ?? 'Según acuerdo comercial') ?></div>
      <div class="dato"><strong>Plazo de entrega:</strong> <?= e($cotizacion['plazo_entrega'] ?? 'Según disponibilidad') ?></div>
    </div>

    <table class="tabla-items">
      <thead>
        <tr>
          <th style="width: 90px;">Código</th>
          <th>Producto / Descripción</th>
          <th style="width: 75px;" class="text-center">Cant.</th>
          <th style="width: 85px;" class="text-center">Unidad</th>
          <th style="width: 120px;" class="text-right">P. Unitario</th>
          <th style="width: 120px;" class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($cotizacion['items'] ?? []) as $it): ?>
          <tr>
            <td><?= e($it['codigo'] ?? ('ITM-' . (string) ($it['id'] ?? ''))) ?></td>
            <td>
              <strong><?= e((string) ($it['producto_nombre'] ?? 'Producto')) ?></strong><br>
              <span><?= e($it['descripcion'] ?? '') ?></span>
            </td>
            <td class="text-center"><?= number_format((float) ($it['cantidad'] ?? 0), 2) ?></td>
            <td class="text-center"><?= e($it['unidad'] ?? 'Unidad') ?></td>
            <td class="text-right">$<?= number_format((float) ($it['precio_unitario'] ?? 0), 0, ',', '.') ?></td>
            <td class="text-right">$<?= number_format((float) ($it['total'] ?? 0), 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <table class="totales">
      <tr><td class="label">Subtotal</td><td class="text-right">$<?= number_format((float) ($cotizacion['subtotal'] ?? 0), 0, ',', '.') ?></td></tr>
      <tr><td class="label">Descuento</td><td class="text-right">- <?= e($descuentoDetalle) ?></td></tr>
      <?php if ($mostrarTarjetaLista): ?>
        <tr><td class="label">Descuento por lista</td><td class="text-right">- $<?= number_format($descuentoListaMonto, 0, ',', '.') ?></td></tr>
      <?php endif; ?>
      <tr><td class="label">Neto</td><td class="text-right">$<?= number_format($neto, 0, ',', '.') ?></td></tr>
      <tr><td class="label">IVA (19%)</td><td class="text-right">$<?= number_format((float) ($cotizacion['impuesto'] ?? 0), 0, ',', '.') ?></td></tr>
      <tr class="final"><td>Total</td><td class="text-right">$<?= number_format((float) ($cotizacion['total'] ?? 0), 0, ',', '.') ?></td></tr>
    </table>
  </div>

  <div class="bloque">
    <h3>Observaciones</h3>
    <div class="nota"><?= nl2br(e($cotizacion['observaciones'] ?? '')) ?></div>
  </div>

  <div class="bloque">
    <h3>Términos y condiciones</h3>
    <ul>
      <?php foreach (preg_split('/\r\n|\r|\n/', trim((string) ($cotizacion['terminos_condiciones'] ?? ''))) as $termino): ?>
        <?php if (trim($termino) !== ''): ?>
          <li><?= e($termino) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="firmas">
    <div class="firma"><div class="linea"></div><strong><?= e($cotizacion['vendedor'] ?? '') ?></strong><br>Ejecutivo Comercial</div>
    <div class="firma"><div class="linea"></div><strong><?= e($cotizacion['cliente'] ?? '') ?></strong><br>Aceptación cliente</div>
  </div>

  <div class="pie">Documento generado automáticamente por el sistema de cotizaciones.</div>
</div>

<?php if ($modoPdf): ?>
<script>
  window.addEventListener('load', () => {
    const titleBase = 'Cotizacion-<?= e($cotizacion['numero'] ?? (string) $cotizacion['id']) ?>';
    document.title = titleBase;
  });
</script>
<?php endif; ?>
