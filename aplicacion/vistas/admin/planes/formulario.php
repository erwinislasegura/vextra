<?php $esEditar = (bool)$plan; ?>
<h1 class="h5 mb-3"><?= $esEditar ? 'Editar plan' : 'Crear plan' ?></h1>
<form method="POST" class="row g-2"><?= csrf_campo() ?>
<div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= e($plan['nombre'] ?? '') ?>" required></div>
<div class="col-md-4"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= e($plan['slug'] ?? '') ?>" required></div>
<div class="col-md-4"><label class="form-label">Estado</label><select class="form-select" name="estado"><option value="activo" <?= (($plan['estado'] ?? '')==='activo')?'selected':'' ?>>Activo</option><option value="inactivo" <?= (($plan['estado'] ?? '')==='inactivo')?'selected':'' ?>>Inactivo</option></select></div>
<div class="col-md-3"><label class="form-label">Precio mensual (CLP)</label><input class="form-control" name="precio_mensual" type="number" value="<?= e((string)($plan['precio_mensual'] ?? 0)) ?>"></div>
<div class="col-md-3"><label class="form-label">Descuento anual (%)</label><input class="form-control" name="descuento_anual_pct" type="number" value="<?= e((string)($plan['descuento_anual_pct'] ?? 10)) ?>"></div>
<div class="col-md-3"><label class="form-label">Precio anual (CLP)</label><input class="form-control" name="precio_anual" type="number" value="<?= e((string)($plan['precio_anual'] ?? 0)) ?>"></div>
<div class="col-md-3"><label class="form-label">Duración (días)</label><input class="form-control" name="duracion_dias" type="number" value="<?= e((string)($plan['duracion_dias'] ?? 30)) ?>"></div>
<div class="col-md-3"><label class="form-label">Días prueba Flow</label><input class="form-control" name="flow_dias_prueba" type="number" min="0" value="<?= e((string)($plan['flow_dias_prueba'] ?? 0)) ?>"></div>
<div class="col-md-3"><label class="form-label">Días hasta cobro Flow</label><input class="form-control" name="flow_dias_cobro" type="number" min="1" value="<?= e((string)($plan['flow_dias_cobro'] ?? 3)) ?>"></div>
<div class="col-md-4"><label class="form-label">Máximo usuarios</label><input class="form-control" name="maximo_usuarios" type="number" value="<?= e((string)($plan['maximo_usuarios'] ?? 1)) ?>"><div class="form-text">Este valor se sincroniza con la funcionalidad <code>maximo_usuarios</code> del plan.</div></div>
<div class="col-md-4"><label class="form-label">Color visual</label><input class="form-control" name="color_visual" value="<?= e($plan['color_visual'] ?? '#4632a8') ?>"></div>
<div class="col-md-4"><label class="form-label">Orden de visualización</label><input class="form-control" type="number" name="orden_visualizacion" value="<?= e((string)($plan['orden_visualizacion'] ?? 0)) ?>"></div>
<div class="col-md-6"><label class="form-label">Resumen comercial</label><input class="form-control" name="resumen_comercial" value="<?= e($plan['resumen_comercial'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Descripción</label><input class="form-control" name="descripcion_comercial" value="<?= e($plan['descripcion_comercial'] ?? '') ?>"></div>
<div class="col-md-3"><label class="form-label">Etiqueta/Insignia</label><input class="form-control" name="insignia" value="<?= e($plan['insignia'] ?? '') ?>"></div>
<div class="col-md-9"><label class="form-label">Observaciones internas</label><input class="form-control" name="observaciones_internas" value="<?= e($plan['observaciones_internas'] ?? '') ?>"></div>
<div class="col-12 d-flex flex-wrap gap-3 small mt-1">
<label><input type="checkbox" name="visible" <?= !isset($plan['visible']) || $plan['visible'] ? 'checked':'' ?>> Visible en landing</label>
<label><input type="checkbox" name="destacado" <?= isset($plan['destacado']) && $plan['destacado'] ? 'checked':'' ?>> Plan destacado</label>
<label><input type="checkbox" name="recomendado" <?= isset($plan['recomendado']) && $plan['recomendado'] ? 'checked':'' ?>> Plan recomendado</label>
<label><input type="checkbox" name="usuarios_ilimitados" <?= isset($plan['usuarios_ilimitados']) && $plan['usuarios_ilimitados'] ? 'checked':'' ?>> Usuarios ilimitados</label>
</div>
<div class="col-12"><button class="btn btn-primary btn-sm">Guardar plan</button></div>
</form>
