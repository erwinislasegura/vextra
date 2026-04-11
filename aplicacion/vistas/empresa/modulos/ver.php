<h1 class="h4 mb-3">Ver registro - <?= e($titulo) ?></h1>

<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <?php foreach ($registro as $k => $v): ?>
                <?php if (in_array($k, ['id', 'empresa_id'], true)) { continue; } ?>
                <dt class="col-sm-3"><?= e(ucwords(str_replace('_', ' ', $k))) ?></dt>
                <dd class="col-sm-9"><?= e((string) $v) ?></dd>
            <?php endforeach; ?>
        </dl>

        <?php if (($modulo ?? '') === 'aprobaciones'): ?>
            <hr>
            <div class="small text-uppercase fw-semibold text-muted mb-2">Firma cliente de la cotización</div>
            <?php if (!empty($cotizacionAprobacion['firma_cliente'])): ?>
                <div class="mb-2"><strong>Firmante:</strong> <?= e((string) ($cotizacionAprobacion['nombre_firmante_cliente'] ?? 'Cliente')) ?></div>
                <?php if (!empty($cotizacionAprobacion['numero'])): ?>
                    <div class="small text-muted mb-2">Cotización: <?= e((string) $cotizacionAprobacion['numero']) ?></div>
                <?php endif; ?>
                <img
                    src="<?= e((string) $cotizacionAprobacion['firma_cliente']) ?>"
                    alt="Firma del cliente"
                    style="max-width: 420px; width: 100%; border: 1px solid #dee2e6; border-radius: .35rem; background: #fff;"
                >
            <?php else: ?>
                <div class="text-muted">Esta cotización no tiene firma registrada.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/' . $modulo)) ?>">Volver</a>
    <a class="btn btn-primary btn-sm" href="<?= e(url('/app/' . $modulo . '/editar/' . $registro['id'])) ?>">Editar</a>
</div>
