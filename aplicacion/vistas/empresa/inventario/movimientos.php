<h1 class="h4 mb-3">Movimientos de inventario</h1>
<div class="alert alert-info py-2 px-3 small">
  Consulta aquí todas las entradas y salidas de stock registradas por recepciones, ajustes y ventas.
  Puedes filtrar por producto y exportar el resultado actual a Excel para análisis o respaldo.
</div>
<?php
$etiquetasMovimiento = [
  'recepcion_proveedor' => 'Recepción de proveedor',
  'ajuste_entrada' => 'Ajuste de entrada',
  'ajuste_salida' => 'Ajuste de salida',
  'venta' => 'Venta',
  'devolucion_venta' => 'Devolución de venta',
];
?>
<div class="card"><div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><span>Historial</span><div class="d-flex gap-2"><a href="<?= e(url('/app/inventario/movimientos/exportar/excel?producto_id=' . (int) ($productoId ?? 0))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a><form method="GET" class="d-flex gap-2 align-items-center"><input type="text" id="buscador-producto-movimientos" class="form-control form-control-sm" placeholder="Buscar producto..." style="min-width:220px;"><select id="filtro-producto-movimientos" name="producto_id" class="form-select form-select-sm"><option value="0">Todos los productos</option><?php foreach($productos as $p): ?><option value="<?= (int)$p['id'] ?>" <?= (int)($productoId ?? 0)===(int)$p['id']?'selected':'' ?>><?= e(($p['codigo'] ?? '') . ' · ' . ($p['nombre'] ?? '')) ?></option><?php endforeach; ?></select><button class="btn btn-outline-secondary btn-sm">Filtrar</button></form></div></div>
<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Fecha</th><th>Producto</th><th>Movimiento</th><th>Origen</th><th>Entrada</th><th>Salida</th><th>Saldo</th><th>Usuario</th></tr></thead><tbody>
<?php if(empty($movimientos)): ?><tr><td colspan="8" class="text-center text-muted py-3">Sin movimientos.</td></tr><?php else: foreach($movimientos as $m): ?><tr><td><?= e($m['fecha_creacion']) ?></td><td><?= e(($m['codigo'] ?? '') . ' · ' . ($m['producto_nombre'] ?? '')) ?></td><td><div><?= e($etiquetasMovimiento[$m['tipo_movimiento'] ?? ''] ?? ucwords(str_replace('_', ' ', (string) ($m['tipo_movimiento'] ?? '')))) ?></div><div class="small text-muted"><?= e($m['modulo_origen'] ?? '-') ?><?= !empty($m['observacion']) ? ' · ' . e($m['observacion']) : '' ?></div></td><td><?= e($m['documento_origen'] ?? '-') ?></td><td><?= number_format((float)$m['entrada'],2) ?></td><td><?= number_format((float)$m['salida'],2) ?></td><td><?= number_format((float)$m['saldo_resultante'],2) ?></td><td><?= e($m['usuario_nombre'] ?? '-') ?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<script>
(function(){
  const input = document.getElementById('buscador-producto-movimientos');
  const select = document.getElementById('filtro-producto-movimientos');
  if (!input || !select) return;

  const opciones = Array.from(select.options).map((op) => ({
    value: op.value,
    text: op.text,
    selected: op.selected,
  }));

  const render = () => {
    const termino = input.value.trim().toLowerCase();
    const valorActual = select.value;
    select.innerHTML = '';

    opciones.forEach((opcion) => {
      const coincide = termino === '' || opcion.text.toLowerCase().includes(termino);
      if (!coincide) return;
      const option = document.createElement('option');
      option.value = opcion.value;
      option.textContent = opcion.text;
      if (option.value === valorActual || (valorActual === '' && opcion.selected)) {
        option.selected = true;
      }
      select.appendChild(option);
    });
  };

  input.addEventListener('input', render);
})();
</script>
