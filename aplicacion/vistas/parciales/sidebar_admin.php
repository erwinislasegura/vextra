<?php
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$logoAdmin = '/img/logo/logo_vextra.png';
if (is_file(__DIR__ . '/../../../img/logo/logo-vextra-blanco.png')) {
    $logoAdmin = '/img/logo/logo-vextra-blanco.png';
}
$items = [
    ['/admin/panel', 'Dashboard', 'bi-speedometer2'],
    ['/admin/empresas', 'Empresas', 'bi-buildings'],
    ['/admin/administradores-empresa', 'Administradores de empresas', 'bi-people'],
    ['/admin/planes', 'Planes', 'bi-award'],
    ['/admin/funcionalidades', 'Funciones de planes', 'bi-grid-3x3-gap'],
    ['/admin/suscripciones', 'Suscripciones', 'bi-card-checklist'],
    ['/admin/pagos', 'Pagos', 'bi-cash-stack'],
    ['/admin/flow', 'Flow dashboard', 'bi-credit-card-2-front'],
    ['/admin/flow/configuracion', 'Flow configuración', 'bi-shield-lock'],
    ['/admin/flow/planes', 'Flow planes', 'bi-diagram-3'],
    ['/admin/flow/clientes', 'Flow clientes', 'bi-person-badge'],
    ['/admin/flow/suscripciones', 'Flow suscripciones', 'bi-arrow-repeat'],
    ['/admin/flow/pagos', 'Flow pagos', 'bi-wallet2'],
    ['/admin/flow/logs', 'Flow logs/webhooks', 'bi-journal-text'],
    ['/admin/reportes', 'Reportes', 'bi-bar-chart-line'],
    ['/admin/configuracion', 'Configuración general', 'bi-gear'],
    ['/admin/configuracion#imap-smtp-admin', 'IMAP/SMTP notificaciones', 'bi-envelope-at'],
    ['/admin/historial', 'Historial / actividad', 'bi-clock-history'],
];
?>
<aside class="sidebar sidebar-app sidebar-admin p-3 border-end">
  <a href="<?= e(url('/admin/panel')) ?>" class="sidebar-admin__brand mb-3 text-decoration-none">
    <img src="<?= e(url($logoAdmin)) ?>" alt="Vextra" class="sidebar-admin__logo" width="146" height="72">
    <div class="sidebar-app__titulo">Centro de control SaaS</div>
  </a>
  <nav class="nav flex-column small gap-1">
    <?php foreach ($items as [$url, $texto, $icono]): ?>
      <?php $urlPath = parse_url($url, PHP_URL_PATH) ?: $url; ?>
      <a class="nav-link d-flex align-items-center gap-2 <?= str_starts_with($rutaActual, $urlPath) ? 'active' : '' ?>" href="<?= e(url($url)) ?>">
        <i class="bi <?= e($icono) ?>"></i><span><?= e($texto) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
