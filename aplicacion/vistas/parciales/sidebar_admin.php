<?php
use Aplicacion\Modelos\SoporteChat;
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$chatsSoporteNuevosAdmin = (new SoporteChat())->contarNoLeidosAdmin();
$logoAdmin = '/img/logo/logo_vextra.png';
if (is_file(__DIR__ . '/../../../img/logo/logo-vextra-blanco.png')) {
    $logoAdmin = '/img/logo/logo-vextra-blanco.png';
}
$items = [
    'General' => [
        ['/admin/panel', 'Dashboard ejecutivo', 'bi-speedometer2'],
        ['/admin/reportes', 'Reportes', 'bi-bar-chart-line'],
        ['/admin/historial', 'Historial y actividad', 'bi-clock-history'],
    ],
    'Comercial' => [
        ['/admin/empresas', 'Empresas', 'bi-buildings'],
        ['/admin/administradores-empresa', 'Administradores empresas', 'bi-people'],
        ['/admin/planes', 'Planes', 'bi-award'],
        ['/admin/funcionalidades', 'Funciones de planes', 'bi-grid-3x3-gap'],
        ['/admin/suscripciones', 'Suscripciones', 'bi-card-checklist'],
        ['/admin/pagos', 'Pagos', 'bi-cash-stack'],
        ['/admin/soporte-chats', 'Chat soporte', 'bi-headset'],
    ],
    'Flow' => [
        ['/admin/flow', 'Dashboard Flow', 'bi-credit-card-2-front'],
        ['/admin/flow/configuracion', 'Configuración', 'bi-shield-lock'],
        ['/admin/flow/planes', 'Planes', 'bi-diagram-3'],
        ['/admin/flow/clientes', 'Clientes', 'bi-person-badge'],
        ['/admin/flow/suscripciones', 'Suscripciones', 'bi-arrow-repeat'],
        ['/admin/flow/pagos', 'Pagos', 'bi-wallet2'],
        ['/admin/flow/logs', 'Logs y webhooks', 'bi-journal-text'],
    ],
    'Sistema' => [
        ['/admin/configuracion', 'Configuración general', 'bi-gear'],
        ['/admin/configuracion#imap-smtp-admin', 'Correo IMAP/SMTP', 'bi-envelope-at'],
    ],
];
?>
<aside class="sidebar sidebar-app sidebar-admin p-3 border-end">
  <a href="<?= e(url('/admin/panel')) ?>" class="sidebar-admin__brand mb-3 text-decoration-none">
    <img src="<?= e(url($logoAdmin)) ?>" alt="Vextra" class="sidebar-admin__logo" width="146" height="72">
    <div class="sidebar-app__titulo">Centro de control SaaS</div>
  </a>
  <nav class="nav flex-column small gap-2">
    <?php foreach ($items as $seccion => $links): ?>
      <div class="sidebar-admin__group">
        <div class="sidebar-admin__group-title"><?= e($seccion) ?></div>
        <div class="d-grid gap-1">
          <?php foreach ($links as [$url, $texto, $icono]): ?>
            <?php $urlPath = parse_url($url, PHP_URL_PATH) ?: $url; ?>
            <a class="nav-link d-flex align-items-center gap-2 <?= str_starts_with($rutaActual, $urlPath) ? 'active' : '' ?>" href="<?= e(url($url)) ?>">
              <i class="bi <?= e($icono) ?>"></i><span><?= e($texto) ?></span>
              <?php if ($urlPath === '/admin/soporte-chats' && $chatsSoporteNuevosAdmin > 0): ?>
                <span class="badge text-bg-success ms-auto"><?= (int) $chatsSoporteNuevosAdmin ?></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </nav>
</aside>
