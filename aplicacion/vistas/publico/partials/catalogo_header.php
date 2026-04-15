<?php
$catalogoHeaderSearchAction = (string) ($catalogoHeaderSearchAction ?? $catalogoBaseUrl);
$catalogoHeaderSearchMethod = (string) ($catalogoHeaderSearchMethod ?? 'GET');
$catalogoHeaderSearchName = (string) ($catalogoHeaderSearchName ?? 'q');
$catalogoHeaderSearchValue = (string) ($catalogoHeaderSearchValue ?? '');
$catalogoHeaderSearchInputId = (string) ($catalogoHeaderSearchInputId ?? '');
$catalogoHeaderSearchButtonId = (string) ($catalogoHeaderSearchButtonId ?? '');
$catalogoHeaderCartHref = (string) ($catalogoHeaderCartHref ?? $catalogoBaseUrl);
$catalogoHeaderCartAsButton = (bool) ($catalogoHeaderCartAsButton ?? false);
$catalogoHeaderCartButtonId = (string) ($catalogoHeaderCartButtonId ?? '');
?>
<div class="catalogo-topbar">
  <div class="catalogo-container catalogo-topbar__content">
    <div><?= e($topbarTexto) ?></div>
    <?php if ($socialesTopbar !== []): ?><div class="catalogo-topbar__sociales"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div><?php endif; ?>
  </div>
</div>

<header class="catalogo-header">
  <div class="catalogo-container catalogo-navbar">
    <a class="catalogo-logo" href="<?= e($catalogoBaseUrl) ?>"><img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa"></a>
    <button class="catalogo-mobile-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <form class="search-box" method="<?= e($catalogoHeaderSearchMethod) ?>" action="<?= e($catalogoHeaderSearchAction) ?>">
      <input type="text" name="<?= e($catalogoHeaderSearchName) ?>"<?= $catalogoHeaderSearchInputId !== '' ? ' id="' . e($catalogoHeaderSearchInputId) . '"' : '' ?> value="<?= e($catalogoHeaderSearchValue) ?>" placeholder="Buscar productos, categorías o marcas...">
      <button type="submit"<?= $catalogoHeaderSearchButtonId !== '' ? ' id="' . e($catalogoHeaderSearchButtonId) . '"' : '' ?>>Buscar</button>
    </form>
    <nav class="nav-actions" aria-label="Menú superior catálogo">
      <a class="menu-link" href="<?= e($catalogoBaseUrl) ?>">Inicio</a>
      <a class="menu-link" href="<?= e($catalogoNosotrosUrl) ?>">Nosotros</a>
      <a class="menu-link" href="<?= e($catalogoContactoUrl) ?>">Contacto</a>
    </nav>
    <?php if ($catalogoHeaderCartAsButton): ?>
      <button class="btn-primary-custom d-inline-flex align-items-center gap-2" type="button"<?= $catalogoHeaderCartButtonId !== '' ? ' id="' . e($catalogoHeaderCartButtonId) . '"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path d="M3 4h2l2.4 10.2a2 2 0 0 0 2 1.5h7.7a2 2 0 0 0 2-1.6L21 7H7" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/></svg><span>Ver carrito</span></button>
    <?php else: ?>
      <a class="btn-primary-custom d-inline-flex align-items-center gap-2" href="<?= e($catalogoHeaderCartHref) ?>"><svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path d="M3 4h2l2.4 10.2a2 2 0 0 0 2 1.5h7.7a2 2 0 0 0 2-1.6L21 7H7" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/></svg><span>Ver carrito</span></a>
    <?php endif; ?>
  </div>
</header>
<script>
  (() => {
    const header = document.currentScript?.previousElementSibling;
    if (!header || !header.classList.contains('catalogo-header')) return;
    const toggle = header.querySelector('.catalogo-mobile-toggle');
    if (!toggle) return;
    const setState = (open) => {
      header.classList.toggle('is-mobile-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    };
    toggle.addEventListener('click', () => setState(!header.classList.contains('is-mobile-open')));
    window.addEventListener('resize', () => {
      if (window.innerWidth > 720) {
        setState(false);
      }
    });
  })();
</script>
