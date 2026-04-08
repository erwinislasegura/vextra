<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Contactos</h1>
    <a href="<?= e(url('/app/clientes')) ?>" class="btn btn-outline-primary btn-sm">Volver a clientes</a>
</div>

<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Uso recomendado de contactos</div>
    <ul class="mb-0 small ps-3">
        <li>Marca un contacto principal para priorizar comunicaciones y seguimientos.</li>
        <li>Mantén cargo, correo y teléfonos actualizados para mejorar la respuesta comercial.</li>
        <li>Asocia cada contacto al cliente correcto para trazabilidad en cotizaciones.</li>
    </ul>
</div>

<div class="card mb-3">
    <div class="card-header">Nuevo contacto</div>
    <div class="card-body">
        <?php if ($clientes === []): ?>
            <div class="alert alert-warning mb-0">
                No hay clientes registrados activos para asociar contactos.
                <a href="<?= e(url('/app/clientes/crear')) ?>" class="alert-link">Registrar cliente</a>.
            </div>
        <?php else: ?>
            <?php include __DIR__ . '/_formulario.php'; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong>Listado de contactos</strong>
            <div class="small text-muted">Registros encontrados: <?= count($contactos) ?></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/app/contactos/exportar/excel?q=' . urlencode($buscar))) ?>" class="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_CLASES) ?>" style="<?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_ESTILO) ?>"><?= e(\Aplicacion\Servicios\ExcelExpoFormato::BOTON_TEXTO) ?></a>
            <form class="d-flex gap-2" method="GET" action="<?= e(url('/app/contactos')) ?>">
                <input class="form-control form-control-sm" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por cliente, nombre o correo">
                <button class="btn btn-outline-secondary btn-sm">Buscar</button>
                <?php if ($buscar !== ''): ?>
                    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/app/contactos')) ?>">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="table-responsive" style="overflow: visible;">
        <table class="table table-sm table-hover mb-0 tabla-admin">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Celular</th>
                    <th>Principal</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($contactos === []): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No se encontraron contactos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contactos as $r): ?>
                        <tr>
                            <td><?= e($r['cliente_nombre'] ?: $r['cliente_razon_social'] ?: ('#' . (int) $r['cliente_id'])) ?></td>
                            <td><?= e($r['nombre']) ?></td>
                            <td><?= e($r['cargo']) ?></td>
                            <td><?= e($r['correo']) ?></td>
                            <td><?= e($r['telefono']) ?></td>
                            <td><?= e($r['celular']) ?></td>
                            <td><?= !empty($r['es_principal']) ? 'Sí' : 'No' ?></td>
                            <td class="text-end">
                                <div class="dropdown dropup">
                                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= e(url('/app/contactos/ver/' . $r['id'])) ?>">Ver</a></li>
                                        <li><a class="dropdown-item" href="<?= e(url('/app/contactos/editar/' . $r['id'])) ?>">Editar</a></li>
                                        <li>
                                            <form method="POST" action="<?= e(url('/app/contactos/eliminar/' . $r['id'])) ?>" onsubmit="return confirm('¿Eliminar contacto?')">
                                                <?= csrf_campo() ?>
                                                <button type="submit" class="dropdown-item text-danger">Eliminar</button>
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
