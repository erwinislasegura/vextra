<?php
$camposMonedaExactos = [
    'monto',
    'total',
    'subtotal',
    'impuesto',
    'descuento',
    'descuento_monto',
    'precio',
    'precio_unitario',
];

$esCampoMoneda = static function (string $campo) use ($camposMonedaExactos): bool {
    if (in_array($campo, $camposMonedaExactos, true)) {
        return true;
    }

    return str_contains($campo, '_monto') || str_contains($campo, '_total') || str_contains($campo, 'precio_');
};

$formatearPesosClp = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return '—';
    }

    return '$' . number_format((float) $valor, 0, ',', '.');
};
?>

<h1 class="h4 mb-3">Ver registro - <?= e($titulo) ?></h1>

<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <?php foreach ($registro as $k => $v): ?>
                <?php if (in_array($k, ['id', 'empresa_id'], true)) { continue; } ?>
                <dt class="col-sm-3"><?= e(ucwords(str_replace('_', ' ', $k))) ?></dt>
                <dd class="col-sm-9">
                    <?php if ($esCampoMoneda((string) $k) && is_numeric($v)): ?>
                        <?= e($formatearPesosClp($v)) ?>
                    <?php else: ?>
                        <?= e((string) $v) ?>
                    <?php endif; ?>
                </dd>
            <?php endforeach; ?>
        </dl>

        <?php if (isset($registro['cotizacion_id']) || !empty($cotizacionAprobacion)): ?>
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
