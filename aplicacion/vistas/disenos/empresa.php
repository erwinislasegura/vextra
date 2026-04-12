<!doctype html>
<html lang="es">
<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-X41LED0NXW"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-X41LED0NXW');
  </script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#4632a8">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title>App - Vextra</title>
  <link rel="icon" type="image/png" href="<?= e(url('/img/logo/logo_vextra.png')) ?>">
  <link rel="apple-touch-icon" href="<?= e(url('/img/logo/logo_vextra.png')) ?>">
  <link rel="manifest" href="<?= e(url('/site.webmanifest')) ?>">
  <script>
    window.__vextraDeferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (event) {
      event.preventDefault();
      window.__vextraDeferredInstallPrompt = event;
      window.dispatchEvent(new CustomEvent('vextra:install-ready'));
    });
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php
$estadoBloqueoCuenta = (string) ($_SESSION['bloqueo_cuenta_estado'] ?? '');
$planesCambioVencida = [];
$planActualId = 0;
if ($estadoBloqueoCuenta === 'vencida') {
    $planesCambioVencida = (new \Aplicacion\Modelos\Plan())->listar(true);
    $suscripcionActual = (new \Aplicacion\Modelos\Suscripcion())->obtenerUltimaPorEmpresa((int) empresa_actual_id());
    $planActualId = (int) ($suscripcionActual['plan_id'] ?? 0);
}
$mensajesBloqueo = [
    'suspendida' => [
        'titulo' => 'Cuenta suspendida',
        'detalle' => 'Hola, tu cuenta está suspendida temporalmente por validaciones comerciales pendientes. Con gusto te ayudamos a reactivarla cuanto antes.',
        'clase_header' => 'modal-bloqueo--suspendida',
        'icono' => 'bi-pause-circle-fill',
        'estilo_header' => 'background-color:#f59f00;border-bottom-color:#d97706;',
    ],
    'vencida' => [
        'titulo' => 'Cuenta vencida',
        'detalle' => 'Tu suscripción está vencida. Para reactivar tu cuenta puedes pagar tu plan actual o cambiar de plan y completar el pago en Flow.',
        'clase_header' => 'modal-bloqueo--vencida',
        'icono' => 'bi-hourglass-split',
        'estilo_header' => 'background-color:#111827;border-bottom-color:#1f2937;',
    ],
    'cancelada' => [
        'titulo' => 'Cuenta cancelada',
        'detalle' => 'Hola, tu cuenta fue cancelada y el acceso está deshabilitado. Si deseas volver a operar, nuestro equipo comercial puede orientarte en la reactivación.',
        'clase_header' => 'modal-bloqueo--cancelada',
        'icono' => 'bi-x-octagon-fill',
        'estilo_header' => 'background-color:#dc2626;border-bottom-color:#991b1b;',
    ],
];
        $cuentaBloqueada = isset($mensajesBloqueo[$estadoBloqueoCuenta]);
        $configBloqueo = $cuentaBloqueada ? $mensajesBloqueo[$estadoBloqueoCuenta] : null;
?>

<div class="d-flex app-shell<?= $cuentaBloqueada ? ' app-shell--bloqueada' : '' ?>">
  <?php require __DIR__ . '/../parciales/sidebar_empresa.php'; ?>
  <div class="flex-grow-1">
    <?php require __DIR__ . '/../parciales/topbar.php'; ?>
    <main class="container-fluid py-3">
      <?php if ($flash = obtener_flash()): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
      <?php endif; ?>
      <?php require $contenido; ?>
    </main>
  </div>
</div>

<?php if ($cuentaBloqueada): ?>
        <div class="modal fade" id="modalBloqueoCuenta" tabindex="-1" aria-labelledby="modalBloqueoCuentaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white <?= e($configBloqueo['clase_header']) ?>" style="<?= e($configBloqueo['estilo_header']) ?>">
                        <h5 class="modal-title" id="modalBloqueoCuentaLabel">
                            <i class="bi <?= e($configBloqueo['icono']) ?> me-2"></i><?= e($configBloqueo['titulo']) ?>
                        </h5>
                    </div>
                    <div class="modal-body">
                        <?php if ($estadoBloqueoCuenta === 'vencida'): ?>
                            <p class="mb-2 fs-6"><?= e($configBloqueo['detalle']) ?></p>
                            <p class="text-secondary small mb-3">Selecciona una opción de reactivación. El pago se procesa de forma segura mediante Flow.</p>
                            <div class="bloqueo-cuenta-opcion mb-3">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <h6 class="mb-0">Renovar plan actual</h6>
                                    <span class="badge text-bg-light border">Más rápido</span>
                                </div>
                                <p class="text-secondary small mb-2">Mantén tu plan vigente y reactiva tu cuenta en minutos.</p>
                                <form method="POST" action="<?= e(url('/app/panel/iniciar-pago-trial')) ?>" class="m-0">
                                    <?= csrf_campo() ?>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-shield-lock me-1"></i>Pagar plan actual en Flow
                                    </button>
                                </form>
                            </div>

                            <div class="bloqueo-cuenta-opcion">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <h6 class="mb-0">Cambiar plan y pagar</h6>
                                    <span class="badge text-bg-light border">Flexible</span>
                                </div>
                                <p class="text-secondary small mb-2">Elige el plan y modalidad de cobro antes de continuar al checkout de Flow.</p>
                                <form method="POST" action="<?= e(url('/app/panel/iniciar-pago-cambio-plan')) ?>" class="row g-2 align-items-end">
                                    <?= csrf_campo() ?>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Plan a contratar</label>
                                        <select name="plan_id" class="form-select form-select-sm" required>
                                            <option value="">Selecciona plan...</option>
                                            <?php foreach ($planesCambioVencida as $planOpcion): ?>
                                                <?php $seleccionado = (int) ($planOpcion['id'] ?? 0) === $planActualId; ?>
                                                <option value="<?= (int) $planOpcion['id'] ?>" <?= $seleccionado ? 'selected' : '' ?>>
                                                    <?= e($planOpcion['nombre']) ?> · $<?= number_format((float) ($planOpcion['precio_mensual'] ?? 0), 0, ',', '.') ?>/mes
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Modalidad</label>
                                        <select name="tipo_cobro" class="form-select form-select-sm" required>
                                            <option value="mensual">Mensual</option>
                                            <option value="anual">Anual</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-grid">
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            Pagar en Flow
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <p class="mb-0 text-secondary small mt-3"><i class="bi bi-info-circle me-1"></i>Mientras este estado permanezca activo, no podrás operar módulos ni ejecutar procesos internos.</p>
                        <?php else: ?>
                            <p class="mb-2 fs-6"><?= e($configBloqueo['detalle']) ?></p>
                            <p class="mb-0 text-secondary small">Mientras este estado permanezca activo, por seguridad no podrás operar módulos, crear registros ni ejecutar procesos internos.</p>
                        <?php endif; ?>
                    </div>
        <div class="modal-footer">
          <form method="POST" action="<?= e(url('/cerrar-sesion')) ?>" class="m-0">
            <?= csrf_campo() ?>
            <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>window.APP_BASE_PATH = "<?= e(base_path_url()) ?>";</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(url('/assets/js/app.js')) ?>"></script>
<?php if ($cuentaBloqueada): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var modalEl = document.getElementById('modalBloqueoCuenta');
      if (!modalEl || typeof bootstrap === 'undefined') {
        return;
      }

      var modal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false
      });
      modal.show();
    });
  </script>
<?php endif; ?>
</body>
</html>
