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
  <title>Admin - Vextra</title>
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
<div class="d-flex app-shell app-shell-admin">
  <?php require __DIR__ . '/../parciales/sidebar_admin.php'; ?>
  <div class="flex-grow-1">
    <?php require __DIR__ . '/../parciales/topbar.php'; ?>
    <main class="container-fluid py-3"><?php if ($flash = obtener_flash()): ?><div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div><?php endif; require $contenido; ?></main>
  </div>
</div>
<script>window.APP_BASE_PATH = "<?= e(base_path_url()) ?>";</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(url('/assets/js/app.js')) ?>"></script>
</body>
</html>
