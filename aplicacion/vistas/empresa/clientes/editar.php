<h1 class="h4 mb-3">Editar cliente</h1>
<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Recomendaciones de actualización</div>
  <ul class="mb-0 small ps-3">
    <li>Verifica estado y listas de precios para reflejar las condiciones vigentes del cliente.</li>
    <li>Actualiza observaciones para mantener continuidad entre ventas, soporte y postventa.</li>
  </ul>
</div>
<div class="card"><div class="card-body"><form method="POST" class="row g-2"><?= csrf_campo() ?>
<div class="col-md-3"><label class="form-label" for="editar_cliente_razon_social">Razón social</label><input id="editar_cliente_razon_social" name="razon_social" class="form-control" value="<?= e($cliente['razon_social']) ?>"></div>
<div class="col-md-3"><label class="form-label">Nombre comercial</label><input name="nombre_comercial" class="form-control" value="<?= e($cliente['nombre_comercial']) ?>"></div>
<div class="col-md-2"><label class="form-label">ID fiscal</label><input name="identificador_fiscal" class="form-control" value="<?= e($cliente['identificador_fiscal']) ?>"></div>
<div class="col-md-2"><label class="form-label">Giro</label><input name="giro" class="form-control" value="<?= e($cliente['giro']) ?>"></div>
<div class="col-md-2"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="activo" <?= $cliente['estado']==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $cliente['estado']==='inactivo'?'selected':'' ?>>Inactivo</option></select></div>
<div class="col-md-3"><label class="form-label">Correo</label><input name="correo" class="form-control" value="<?= e($cliente['correo']) ?>"></div>
<div class="col-md-2"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="<?= e($cliente['telefono']) ?>"></div>
<div class="col-md-3"><label class="form-label">Dirección</label><input name="direccion" class="form-control" value="<?= e($cliente['direccion']) ?>"></div>
<div class="col-md-2"><label class="form-label">Ciudad</label><input name="ciudad" class="form-control" value="<?= e($cliente['ciudad']) ?>"></div>
<?php if (($permiteAsignarVendedor ?? false)): ?><div class="col-md-2"><label class="form-label">Vendedor</label><select name="vendedor_id" class="form-select"><option value="">Sin asignar</option><?php foreach($vendedores as $v): ?><option value="<?= (int)$v['id'] ?>" <?= (int)$cliente['vendedor_id']===(int)$v['id'] ? 'selected' : '' ?>><?= e($v['nombre']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
<?php if (($permiteGestionListasPrecios ?? false)): ?><div class="col-md-3"><label class="form-label">Listas de precios</label><select name="lista_precio_ids[]" class="form-select" multiple size="4"><?php foreach($listasPrecios as $lp): ?><option value="<?= (int)$lp['id'] ?>" <?= in_array((int) $lp['id'], (array) ($listaPrecioClienteIds ?? []), true) ? 'selected' : '' ?>><?= e($lp['nombre']) ?></option><?php endforeach; ?></select><div class="form-text">Selecciona una o más listas. Sin selección: sin lista.</div></div><?php endif; ?>
<div class="col-md-12"><label class="form-label">Observaciones</label><textarea name="notas" class="form-control" rows="2"><?= e($cliente['notas']) ?></textarea></div>
<div class="col-12"><button class="btn btn-primary btn-sm">Guardar cambios</button> <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/clientes')) ?>">Cancelar</a></div>
</form></div></div>
