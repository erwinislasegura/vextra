<!doctype html>
<html lang="es">
<head>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    window.gtag = gtag;

    const cargarAnalytics = () => {
      if (window.__analyticsLoaded) return;
      window.__analyticsLoaded = true;
      const script = document.createElement('script');
      script.src = 'https://www.googletagmanager.com/gtag/js?id=G-X41LED0NXW';
      script.async = true;
      document.head.appendChild(script);
      gtag('js', new Date());
      gtag('config', 'G-X41LED0NXW');
    };

    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(cargarAnalytics, { timeout: 2000 });
    } else {
      window.addEventListener('load', cargarAnalytics, { once: true });
    }
  </script>
  <?php
    $metaTitle = (string) ($meta_title ?? 'Vextra | Sistema de cotizaciones para empresas');
    $metaDescription = (string) ($meta_description ?? 'Vextra es un sistema de cotizaciones para empresas que ayuda a vender más con procesos comerciales ordenados, seguimiento de oportunidades y planes escalables.');
    $metaKeywords = (string) ($meta_keywords ?? 'sistema de cotizaciones, software de cotizaciones, cotizaciones para empresas, control de cotizaciones, planes de software comercial');
    $metaUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    $logoUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('/img/logo/logo_vextra.png');
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $faviconUrl = $baseUrl . url('/img/logo/icono.png');
  ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($metaTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="keywords" content="<?= e($metaKeywords) ?>">
  <meta name="robots" content="index,follow">
  <meta name="theme-color" content="#4632a8">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($metaTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:url" content="<?= e($metaUrl) ?>">
  <meta property="og:site_name" content="Vextra">
  <meta property="og:locale" content="es_CL">
  <meta property="og:image" content="<?= e($logoUrl) ?>">
  <meta property="og:image:secure_url" content="<?= e($logoUrl) ?>">
  <meta property="og:image:alt" content="Logo Vextra">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($metaTitle) ?>">
  <meta name="twitter:description" content="<?= e($metaDescription) ?>">
  <meta name="twitter:image" content="<?= e($logoUrl) ?>">
  <link rel="canonical" href="<?= e($metaUrl) ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e($faviconUrl) ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e($faviconUrl) ?>">
  <link rel="icon" type="image/png" sizes="48x48" href="<?= e($faviconUrl) ?>">
  <link rel="shortcut icon" href="<?= e($faviconUrl) ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= e($faviconUrl) ?>">
  <link rel="manifest" href="<?= e($baseUrl . url('/site.webmanifest')) ?>">
  <script>
    window.__vextraDeferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (event) {
      event.preventDefault();
      window.__vextraDeferredInstallPrompt = event;
      window.dispatchEvent(new CustomEvent('vextra:install-ready'));
    });
  </script>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://www.googletagmanager.com">
  <link rel="preconnect" href="https://www.google.com">
  <link rel="preconnect" href="https://www.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(url('/assets/css/app.css')) ?>" rel="stylesheet">
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Vextra",
      "url": "<?= e($baseUrl) ?>",
      "logo": "<?= e($logoUrl) ?>"
    }
  </script>
</head>
<body class="bg-light public-page">
<?php if (empty($ocultarNavbarPublico)): ?>
  <?php require __DIR__ . '/../parciales/navbar_publico.php'; ?>
<?php endif; ?>
<main>
  <?php if ($flash = obtener_flash()): ?>
    <div class="container pt-3"><div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div></div>
  <?php endif; ?>
  <?php require $contenido; ?>
</main>
<?php require __DIR__ . '/../parciales/footer_publico.php'; ?>
<?php
$recaptchaActivo = recaptcha_habilitado_publico();
$recaptchaSiteKey = recaptcha_site_key_publico();
$usarRecaptcha = $recaptchaActivo && $recaptchaSiteKey !== '' && !empty($requiereRecaptcha);
?>
<?php if ($usarRecaptcha): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?= e($recaptchaSiteKey) ?>"></script>
<?php endif; ?>
<script>
  (() => {
    const toggler = document.querySelector('[data-nav-toggle]');
    if (!toggler) return;

    const targetSelector = toggler.getAttribute('data-nav-toggle');
    const collapse = targetSelector ? document.querySelector(targetSelector) : null;
    if (!collapse) return;

    const closeMenu = () => {
      collapse.classList.remove('show');
      toggler.setAttribute('aria-expanded', 'false');
    };

    toggler.addEventListener('click', () => {
      const abierto = collapse.classList.toggle('show');
      toggler.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });

    collapse.querySelectorAll('a').forEach((enlace) => {
      enlace.addEventListener('click', closeMenu);
    });
  })();
</script>
<script>window.APP_BASE_PATH = "<?= e(base_path_url()) ?>";</script>
<script defer src="<?= e(url('/assets/js/app.js')) ?>"></script>
<?php if ($usarRecaptcha): ?>
  <script>
    (() => {
      const siteKey = <?= json_encode($recaptchaSiteKey, JSON_UNESCAPED_SLASHES) ?>;
      const forms = document.querySelectorAll('form[data-recaptcha-form="1"]');
      if (!siteKey || !forms.length || typeof grecaptcha === 'undefined') {
        return;
      }

      forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
          if (form.dataset.recaptchaValidated === '1') {
            return;
          }

          event.preventDefault();
          const action = form.dataset.recaptchaAction || 'submit';

          grecaptcha.ready(() => {
            grecaptcha.execute(siteKey, { action }).then((token) => {
              let input = form.querySelector('input[name="g-recaptcha-response"]');
              if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'g-recaptcha-response';
                form.appendChild(input);
              }

              input.value = token;
              form.dataset.recaptchaValidated = '1';
              form.submit();
            });
          });
        });
      });
    })();
  </script>
<?php endif; ?>
</body>
</html>
