<section class="modulo-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h4 mb-0">Clientes</h1>
  <div class="d-flex gap-2">
    <a href="<?= e(url('/app/contactos')) ?>" class="btn btn-outline-primary btn-sm">Ver contactos</a>
  </div>
</section>

<div class="alert alert-info info-modulo mb-3">
  <div class="fw-semibold mb-1">Información clave para gestionar clientes</div>
  <ul class="mb-0 small ps-3">
    <li>Registra razón social y datos fiscales correctos para emitir cotizaciones sin errores.</li>
    <li>Asigna vendedor y lista de precios para acelerar propuestas comerciales.</li>
    <li>Mantén observaciones actualizadas para dar mejor contexto al equipo.</li>
  </ul>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Nuevo cliente</span>
    <span class="small text-muted">Completa la información principal para registrarlo rápido.</span>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= e(url('/app/clientes')) ?>" class="row g-3">
      <?= csrf_campo() ?>
      <div class="col-md-4">
        <label class="form-label" for="cliente_razon_social">Razón social</label>
        <input id="cliente_razon_social" name="razon_social" class="form-control" required maxlength="150" placeholder="Ej: Comercial Andina SpA">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre comercial</label>
        <input name="nombre_comercial" class="form-control" maxlength="150" placeholder="Ej: Andina">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre de contacto</label>
        <input name="nombre" class="form-control" maxlength="120" placeholder="Ej: María Pérez">
      </div>

      <div class="col-md-3">
        <label class="form-label">RUT/ID fiscal</label>
        <input name="identificador_fiscal" class="form-control" maxlength="50" placeholder="Ej: 76.123.456-7">
      </div>
      <div class="col-md-3">
        <label class="form-label">Giro</label>
        <input name="giro" class="form-control" maxlength="120" placeholder="Ej: Servicios TI">
      </div>
      <div class="col-md-3">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="150" placeholder="cliente@empresa.com">
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" maxlength="50" placeholder="+56 9 1234 5678">
      </div>

      <div class="col-md-4">
        <label class="form-label">Dirección</label>
        <input name="direccion" class="form-control" maxlength="180" placeholder="Calle, número, comuna">
      </div>
      <div class="col-md-3">
        <label class="form-label">Ciudad</label>
        <input name="ciudad" class="form-control" maxlength="80" placeholder="Santiago">
      </div>
      <?php if (($permiteAsignarVendedor ?? false)): ?>
      <div class="col-md-3">
        <label class="form-label">Vendedor</label>
        <select name="vendedor_id" class="form-select">
          <option value="">Sin asignar</option>
          <?php foreach ($vendedores as $v): ?>
            <option value="<?= (int) $v['id'] ?>"><?= e($v['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <?php if (($permiteGestionListasPrecios ?? false)): ?>
      <div class="col-md-2">
        <label class="form-label">Lista de precios</label>
        <select name="lista_precio_id" class="form-select">
          <option value="">General</option>
          <?php foreach ($listasPrecios as $lista): ?>
            <option value="<?= (int) $lista['id'] ?>"><?= e($lista['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Observaciones</label>
        <textarea name="notas" class="form-control" rows="2" placeholder="Notas internas del cliente"></textarea>
      </div>

      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary btn-sm px-4">Guardar cliente</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong>Listado de clientes</strong>
      <div class="small text-muted">Registros encontrados: <?= count($clientes) ?></div>
    </div>
    <div class="d-flex gap-2">
      <?php if (($permiteExportarExcel ?? false)): ?>
      <a
        href="<?= e(url('/app/clientes/exportar/excel?q=' . urlencode($buscar))) ?>"
        class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>"
        style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"
      ><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
      <?php endif; ?>
      <form method="GET" class="d-flex gap-2">
        <input class="form-control form-control-sm" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por nombre, fiscal, correo...">
        <button class="btn btn-outline-secondary btn-sm">Buscar</button>
        <?php if ($buscar !== ''): ?>
          <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/clientes')) ?>">Limpiar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="table-responsive" style="overflow: visible;">
    <table class="table table-hover align-middle table-sm mb-0 tabla-admin">
      <thead class="table-light">
        <tr>
          <th>Razón social</th>
          <th>Comercial</th>
          <th>Fiscal</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Ciudad</th>
          <?php if (($permiteGestionListasPrecios ?? false)): ?><th>Lista precio</th><?php endif; ?>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clientes)): ?>
          <tr>
            <td colspan="<?= (($permiteGestionListasPrecios ?? false) ? 9 : 8) ?>" class="text-center py-4 text-muted">No hay clientes registrados con este criterio.</td>
          </tr>
        <?php else: ?>
          <?php foreach($clientes as $c): ?>
            <tr>
              <td><?= e($c['razon_social'] ?: $c['nombre']) ?></td>
              <td><?= e($c['nombre_comercial'] ?: $c['nombre']) ?></td>
              <td><?= e($c['identificador_fiscal'] ?? '') ?></td>
              <td><?= e($c['correo']) ?></td>
              <td><?= e($c['telefono']) ?></td>
              <td><?= e($c['ciudad'] ?? '') ?></td>
              <?php if (($permiteGestionListasPrecios ?? false)): ?>
              <td>
                <?php $listaIds = array_map('intval', (array) ($mapaListasPorCliente[(int) $c['id']] ?? [])); ?>
                <?php if ($listaIds === []): ?>
                  <span class="text-muted">Sin lista</span>
                <?php else: ?>
                  <?php
                    $nombres = [];
                    foreach ($listasPrecios as $lp) {
                        if (in_array((int) $lp['id'], $listaIds, true)) { $nombres[] = (string) $lp['nombre']; }
                    }
                  ?>
                  <?= e(implode(', ', $nombres)) ?>
                <?php endif; ?>
              </td>
              <?php endif; ?>
              <td>
                <span class="badge <?= ($c['estado'] === 'activo') ? 'badge-estado-activo' : 'badge-estado-inactivo' ?>">
                  <?= e(ucfirst($c['estado'])) ?>
                </span>
              </td>
              <td class="text-end">
                <div class="dropdown dropup">
                  <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= e(url('/app/clientes/ver/' . $c['id'])) ?>">Ver</a></li>
                    <li><a class="dropdown-item" href="<?= e(url('/app/clientes/editar/' . $c['id'])) ?>">Editar</a></li>
                    <li><a class="dropdown-item" href="<?= e(url('/app/contactos')) ?>">Ver contactos</a></li>
                    <li>
                      <form method="POST" action="<?= e(url('/app/clientes/eliminar/' . $c['id'])) ?>" onsubmit="return confirm('¿Confirmas eliminar este cliente?')">
                        <?= csrf_campo() ?>
                        <button class="dropdown-item text-danger" type="submit">Eliminar</button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
