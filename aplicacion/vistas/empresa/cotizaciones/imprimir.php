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
?>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 24px;
    background: #eef2f7;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
    font-size: 14px;
    line-height: 1.35;
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
    padding: 28px;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
    border-radius: 8px;
  }
  .encabezado {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 2px solid #1f4e79;
    padding-bottom: 12px;
    margin-bottom: 14px;
  }
  .empresa, .doc { width: 50%; }
  .empresa h1 {
    margin: 0 0 6px;
    color: #1f4e79;
    font-size: 25px;
  }
  .empresa p, .doc p { margin: 2px 0; font-size: 13px; }
  .doc { text-align: right; }
  .doc h2 {
    margin: 0 0 6px;
    font-size: 28px;
    color: #1f4e79;
    letter-spacing: 1px;
  }
  .lista-precio {
    margin: 0 0 14px;
    background: #f8fafc;
    border: 1px solid #dbe2ea;
    border-left: 4px solid #1f4e79;
    padding: 8px 10px;
    font-size: 13px;
  }
  .tarjeta-lista {
    margin: 0 0 14px;
    background: #ecfdf3;
    border: 1px solid #b7efcf;
    border-left: 4px solid #2f9e62;
    padding: 10px 12px;
    font-size: 13px;
    color: #1f5137;
  }
  .bloque { margin-bottom: 14px; }
  .bloque h3 {
    margin: 0 0 8px;
    font-size: 15px;
    color: #1f4e79;
    border-bottom: 1px solid #d8dee8;
    padding-bottom: 5px;
  }
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 20px;
  }
  .dato { font-size: 13px; }
  .dato strong { color: #111827; }
  table { width: 100%; border-collapse: collapse; }
  .tabla-items th {
    background: #1f4e79;
    color: #fff;
    font-weight: 600;
    font-size: 12px;
    padding: 7px 6px;
    text-align: left;
  }
  .tabla-items td {
    border: 1px solid #dbe2ea;
    padding: 6px;
    font-size: 12px;
    vertical-align: top;
  }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .totales {
    width: 320px;
    margin-left: auto;
    margin-top: 10px;
  }
  .totales td {
    border: 1px solid #dbe2ea;
    padding: 7px 10px;
    font-size: 13px;
  }
  .totales .label { background: #f8fafc; font-weight: 700; }
  .totales .final td {
    background: #1f4e79;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
  }
  .nota {
    background: #f8fafc;
    border-left: 4px solid #1f4e79;
    padding: 10px 12px;
    font-size: 13px;
  }
  ul {
    margin: 6px 0 0 16px;
    padding: 0;
    font-size: 13px;
    line-height: 1.45;
  }
  .firmas {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
    margin-top: 24px;
  }
  .firma { text-align: center; padding-top: 26px; font-size: 13px; }
  .linea { border-top: 1px solid #4b5563; margin-bottom: 6px; }
  .pie {
    margin-top: 14px;
    border-top: 1px solid #dbe2ea;
    padding-top: 8px;
    font-size: 11px;
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
      padding: 20px;
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
      <h1><?= e($empresaNombre) ?></h1>
      <p><strong>RUT:</strong> <?= e($empresa['identificador_fiscal'] ?? '') ?></p>
      <p><?= e(trim((string) (($empresa['direccion'] ?? '') . ', ' . ($empresa['ciudad'] ?? '')))) ?></p>
      <p><strong>Teléfono:</strong> <?= e($empresa['telefono'] ?? '') ?></p>
      <p><strong>Correo:</strong> <?= e($empresa['correo'] ?? '') ?></p>
      <p><strong>Web:</strong> <?= e($empresa['sitio_web'] ?? '') ?></p>
    </div>
    <div class="doc">
      <h2>COTIZACIÓN</h2>
      <p><strong>N°:</strong> <?= e($cotizacion['numero'] ?? '') ?></p>
      <p><strong>Fecha:</strong> <?= e($fechaEmision) ?></p>
      <p><strong>Validez:</strong> <?= e($fechaVencimiento) ?></p>
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
