<?php
$empresaNombre = trim((string) (($empresa['nombre_comercial'] ?? '') !== '' ? $empresa['nombre_comercial'] : ($empresa['razon_social'] ?? 'Comercial')));
$modoPdf = ($_GET['modo'] ?? '') === 'pdf';
$fechaEmision = !empty($orden['fecha_emision']) ? date('d-m-Y', strtotime((string) $orden['fecha_emision'])) : '';
$fechaEntrega = !empty($orden['fecha_entrega_estimada']) ? date('d-m-Y', strtotime((string) $orden['fecha_entrega_estimada'])) : '';
$total = 0.0;
foreach (($orden['detalles'] ?? []) as $item) {
    $total += (float) ($item['subtotal'] ?? 0);
}
?>
<style>
  * { box-sizing: border-box; }
  body { margin:0; padding:24px; background:#eef2f7; font-family:Arial,Helvetica,sans-serif; color:#1f2937; font-size:14px; line-height:1.35; }
  .toolbar { max-width:980px; margin:0 auto 12px; display:flex; gap:8px; flex-wrap:wrap; }
  .hoja { max-width:980px; margin:0 auto; background:#fff; padding:28px; box-shadow:0 8px 30px rgba(0,0,0,.08); border-radius:8px; }
  .encabezado { display:flex; justify-content:space-between; gap:20px; border-bottom:2px solid #1f4e79; padding-bottom:12px; margin-bottom:14px; }
  .empresa,.doc { width:50%; }
  .empresa h1 { margin:0 0 6px; color:#1f4e79; font-size:25px; }
  .empresa p,.doc p { margin:2px 0; font-size:13px; }
  .doc { text-align:right; }
  .doc h2 { margin:0 0 6px; font-size:28px; color:#1f4e79; letter-spacing:1px; }
  .bloque { margin-bottom:14px; }
  .bloque h3 { margin:0 0 8px; font-size:15px; color:#1f4e79; border-bottom:1px solid #d8dee8; padding-bottom:5px; }
  .grid-2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px 20px; }
  .dato { font-size:13px; }
  table { width:100%; border-collapse:collapse; }
  .tabla-items th { background:#1f4e79; color:#fff; font-weight:600; font-size:12px; padding:7px 6px; text-align:left; }
  .tabla-items td { border:1px solid #dbe2ea; padding:6px; font-size:12px; vertical-align:top; }
  .text-center { text-align:center; } .text-right { text-align:right; }
  .totales { width:320px; margin-left:auto; margin-top:10px; }
  .totales td { border:1px solid #dbe2ea; padding:7px 10px; font-size:13px; }
  .totales .final td { background:#1f4e79; color:#fff; font-weight:700; font-size:14px; }
  .nota { background:#f8fafc; border-left:4px solid #1f4e79; padding:10px 12px; font-size:13px; }
  .pie { margin-top:14px; border-top:1px solid #dbe2ea; padding-top:8px; font-size:11px; color:#6b7280; text-align:center; }
  @page { size: letter; margin: 10mm; }
  @media print {
    html,body { width:100%; height:auto; }
    body { margin:0; padding:0; background:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .toolbar { display:none !important; }
    .hoja { width:100%; max-width:none; margin:0; padding:20px; box-shadow:none; border-radius:0; background:#fff; }
  }
</style>

<div class="toolbar no-print">
  <button class="btn btn-dark btn-sm" type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/inventario/ordenes-compra/editar/' . (int) $orden['id'])) ?>">Volver</a>
</div>

<div class="hoja">
  <div class="encabezado">
    <div class="empresa">
      <h1><?= e($empresaNombre) ?></h1>
      <p><strong>RUT:</strong> <?= e($empresa['identificador_fiscal'] ?? '') ?></p>
      <p><?= e(trim((string) (($empresa['direccion'] ?? '') . ', ' . ($empresa['ciudad'] ?? '')))) ?></p>
      <p><strong>Teléfono:</strong> <?= e($empresa['telefono'] ?? '') ?></p>
      <p><strong>Correo:</strong> <?= e($empresa['correo'] ?? '') ?></p>
    </div>
    <div class="doc">
      <h2>ORDEN DE COMPRA</h2>
      <p><strong>N°:</strong> <?= e($orden['numero'] ?? '') ?></p>
      <p><strong>Fecha:</strong> <?= e($fechaEmision) ?></p>
      <p><strong>Entrega:</strong> <?= e($fechaEntrega) ?></p>
    </div>
  </div>

  <div class="bloque">
    <h3>Datos del proveedor</h3>
    <div class="grid-2">
      <div class="dato"><strong>Proveedor:</strong> <?= e($orden['proveedor_nombre'] ?? 'Sin proveedor') ?></div>
      <div class="dato"><strong>Correo:</strong> <?= e($orden['proveedor_correo'] ?? '-') ?></div>
      <div class="dato"><strong>Estado:</strong> <?= e($orden['estado'] ?? 'borrador') ?></div>
      <div class="dato"><strong>Referencia:</strong> <?= e($orden['referencia'] ?? '-') ?></div>
    </div>
  </div>

  <div class="bloque">
    <h3>Detalle de la orden</h3>
    <table class="tabla-items">
      <thead>
        <tr><th>Código</th><th>Descripción</th><th class="text-center">Cantidad</th><th class="text-right">Costo unitario</th><th class="text-right">Subtotal</th></tr>
      </thead>
      <tbody>
        <?php foreach (($orden['detalles'] ?? []) as $d): ?>
          <tr>
            <td><?= e($d['codigo'] ?? '') ?></td>
            <td><?= e($d['nombre'] ?? '') ?></td>
            <td class="text-center"><?= number_format((float) ($d['cantidad'] ?? 0), 2, ',', '.') ?></td>
            <td class="text-right">$<?= number_format((float) ($d['costo_unitario'] ?? 0), 0, ',', '.') ?></td>
            <td class="text-right">$<?= number_format((float) ($d['subtotal'] ?? 0), 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <table class="totales">
      <tr class="final"><td>Total orden</td><td class="text-right">$<?= number_format($total, 0, ',', '.') ?></td></tr>
    </table>
  </div>

  <div class="bloque">
    <h3>Observaciones</h3>
    <div class="nota"><?= nl2br(e((string) ($orden['observacion'] ?? 'Sin observaciones.'))) ?></div>
  </div>

  <div class="pie">Documento generado automáticamente por el sistema de inventario.</div>
</div>

<?php if ($modoPdf): ?>
<script>setTimeout(() => window.print(), 300);</script>
<?php endif; ?>
