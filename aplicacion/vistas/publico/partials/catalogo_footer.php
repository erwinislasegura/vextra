<?php
$catalogoFooterInicioUrl = (string) ($catalogoFooterInicioUrl ?? '#catalogoProductos');
$catalogoFooterProductosUrl = (string) ($catalogoFooterProductosUrl ?? '#catalogoProductos');
$renderIconoContactoFooter = static function (string $tipo): string {
    return match ($tipo) {
        'telefono' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a13 13 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2.2 2A16 16 0 0 1 3 6.2 2 2 0 0 1 5 4z"/></svg>',
        'correo' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>',
        default => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.6"/></svg>',
    };
};
?>
<footer class="footer" id="footerCatalogo">
  <div class="catalogo-container footer-content">
    <div class="footer-brand footer-col">
      <img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa">
      <p><?= e((string) (($empresa['descripcion'] ?? '') !== '' ? $empresa['descripcion'] : 'Diseño profesional para mostrar y vender productos online.')) ?></p>
    </div>
    <div class="footer-col">
      <h4>Accesos rápidos</h4>
      <nav class="footer-menu mt-2">
        <a href="<?= e($catalogoFooterInicioUrl) ?>">Inicio</a>
        <a href="<?= e($catalogoNosotrosUrl) ?>">Nosotros</a>
        <a href="<?= e($catalogoContactoUrl) ?>">Contacto</a>
        <a href="<?= e($catalogoFooterProductosUrl) ?>" id="showFeaturedBtnTop">Productos destacados</a>
        <a href="<?= e($catalogoFooterProductosUrl) ?>" id="showOffersBtnTop">Ofertas</a>
      </nav>
    </div>
    <div class="footer-contact footer-col">
      <h4>Datos de contacto</h4>
      <p><span class="dot"><?= $renderIconoContactoFooter('telefono') ?></span><?= e((string) ($empresa['telefono'] ?? 'No informado')) ?></p>
      <p><span class="dot"><?= $renderIconoContactoFooter('correo') ?></span><?= e((string) ($empresa['correo'] ?? 'No informado')) ?></p>
      <p><span class="dot"><?= $renderIconoContactoFooter('direccion') ?></span><?= e((string) ($empresa['direccion'] ?? 'No informada')) ?></p>
      <p><span class="dot"><?= $renderIconoContactoFooter('direccion') ?></span><?= e(trim((string) (($empresa['ciudad'] ?? '') . ' ' . ($empresa['pais'] ?? '')))) ?></p>
    </div>
    <div class="footer-follow footer-col">
      <h4>Síguenos</h4>
      <p>Conéctate en redes sociales y conoce ofertas y productos destacados.</p>
      <?php if ($socialesTopbar !== []): ?>
        <div class="footer-sociales" aria-label="Redes sociales del catálogo">
          <?php foreach ($socialesTopbar as $red): ?>
            <a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e((string) ($red['label'] ?? 'Red social')) ?>">
              <?= $renderIconoRed((string) ($red['id'] ?? '')) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</footer>
<div class="footer-bottom">
  <div class="catalogo-container footer-bottom__content">
    <span>© <?= date('Y') ?> • Todos los derechos reservados</span>
    <span>Catálogo construido con tecnología de <a href="https://vextra.cl" target="_blank" rel="noopener noreferrer">Vextra.cl</a></span>
  </div>
</div>
