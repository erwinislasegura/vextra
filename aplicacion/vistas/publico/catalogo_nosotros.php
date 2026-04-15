<?php
$catalogoBaseUrl = url('/catalogo/' . (int) ($empresa['id'] ?? 0));
$catalogoContactoUrl = $catalogoBaseUrl . '/contacto';
$catalogoNosotrosUrl = $catalogoBaseUrl . '/nosotros';
$colorPrimario = trim((string) ($catalogoTopbar['color_primario'] ?? ''));
if (preg_match('/^#([A-Fa-f0-9]{6})$/', $colorPrimario) !== 1) {
    $colorPrimario = '#4632A8';
}
$colorAcento = trim((string) ($catalogoTopbar['color_acento'] ?? ''));
if (preg_match('/^#([A-Fa-f0-9]{6})$/', $colorAcento) !== 1) {
    $colorAcento = '#5415B0';
}
$topbarTexto = trim((string) ($catalogoTopbar['texto'] ?? ''));
if ($topbarTexto === '') {
    $topbarTexto = 'Envíos a todo el país • Garantía en todos los productos';
}
$nosotrosTitulo = trim((string) ($catalogoTopbar['nosotros_titulo'] ?? ''));
if ($nosotrosTitulo === '') {
    $nosotrosTitulo = 'Nosotros';
}
$nosotrosDescripcion = trim((string) ($catalogoTopbar['nosotros_descripcion'] ?? ''));
if ($nosotrosDescripcion === '') {
    $nosotrosDescripcion = trim((string) ($empresa['descripcion'] ?? ''));
}
if ($nosotrosDescripcion === '') {
    $nosotrosDescripcion = 'Somos un equipo enfocado en entregar una experiencia de compra clara, rápida y confiable para cada cliente.';
}
$nosotrosImagen = (string) ($catalogoTopbar['nosotros_imagen'] ?? url('/img/placeholder-producto.svg'));
$socialesTopbar = [
    ['id' => 'facebook', 'url' => trim((string) ($catalogoTopbar['sociales']['facebook'] ?? '')), 'label' => 'Facebook'],
    ['id' => 'instagram', 'url' => trim((string) ($catalogoTopbar['sociales']['instagram'] ?? '')), 'label' => 'Instagram'],
    ['id' => 'tiktok', 'url' => trim((string) ($catalogoTopbar['sociales']['tiktok'] ?? '')), 'label' => 'TikTok'],
    ['id' => 'linkedin', 'url' => trim((string) ($catalogoTopbar['sociales']['linkedin'] ?? '')), 'label' => 'LinkedIn'],
    ['id' => 'youtube', 'url' => trim((string) ($catalogoTopbar['sociales']['youtube'] ?? '')), 'label' => 'YouTube'],
];
$socialesTopbar = array_values(array_filter($socialesTopbar, static fn(array $red): bool => $red['url'] !== ''));
$renderIconoRed = static function (string $id): string {
    return match ($id) {
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.5-3.2h-3.3V7.5c0-.9.3-1.6 1.6-1.6h1.8V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.4H8v3.2h2.4V21h3.1z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2zm0 1.9A3.9 3.9 0 0 0 3.9 7.8v8.4a3.9 3.9 0 0 0 3.9 3.9h8.4a3.9 3.9 0 0 0 3.9-3.9V7.8a3.9 3.9 0 0 0-3.9-3.9H7.8zm8.9 1.5a1.2 1.2 0 1 1 0 2.3 1.2 1.2 0 0 1 0-2.3zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.9a3.1 3.1 0 1 0 0 6.2 3.1 3.1 0 0 0 0-6.2z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 3h2.7c.2 1.5 1 2.8 2.3 3.6.9.6 1.9.9 3 .9V10a8 8 0 0 1-4.9-1.7v7.3a5.6 5.6 0 1 1-5.6-5.6c.4 0 .7 0 1 .1v2.7a2.9 2.9 0 1 0 1.5 2.5V3z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 8.9H3.5V21h2.9V8.9zM5 3A1.8 1.8 0 1 0 5 6.6 1.8 1.8 0 0 0 5 3zM21 13.8c0-3.3-1.8-5.3-4.6-5.3-2.1 0-3 .8-3.6 1.6V8.9h-2.9V21h2.9v-6.7c0-1.8 1-2.8 2.5-2.8s2.2 1 2.2 2.8V21H21v-7.2z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 12s0-3.2-.4-4.7a3 3 0 0 0-2.1-2.1C18.9 4.8 12 4.8 12 4.8s-6.9 0-8.5.4a3 3 0 0 0-2.1 2.1C1 8.8 1 12 1 12s0 3.2.4 4.7a3 3 0 0 0 2.1 2.1c1.6.4 8.5.4 8.5.4s6.9 0 8.5-.4a3 3 0 0 0 2.1-2.1c.4-1.5.4-4.7.4-4.7zm-13.8 3.9V8.1l6.1 3.9-6.1 3.9z"/></svg>',
        default => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/></svg>',
    };
};
?>
<style>
  :root{--primary:<?= e($colorPrimario) ?>;--accent:<?= e($colorAcento) ?>;--bg:#eef2f7;--border:#dbe3ee;--muted:#64748b;--text:#0f172a;--shadow:0 10px 25px rgba(15,23,42,.08)}
  .catalogo-page{background:var(--bg);min-height:100vh}
  .catalogo-container{width:min(1280px,92%);margin:0 auto}
  .catalogo-topbar{background:var(--primary);color:#fff;padding:8px 0;font-size:13px}
  .catalogo-topbar__content{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .catalogo-topbar__sociales{display:flex;align-items:center;gap:10px}
  .catalogo-topbar__sociales a{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:999px;border:1px solid rgba(255,255,255,.5);color:#fff;text-decoration:none}
  .catalogo-topbar__sociales a svg{width:14px;height:14px;fill:#fff;display:block}
  .catalogo-header{position:sticky;top:0;z-index:45;background:#ececf0;border-bottom:1px solid #d7d9e3}
  .catalogo-navbar{display:grid;grid-template-columns:220px 1fr auto auto;gap:14px;align-items:center;padding:12px 0}
  .catalogo-logo img{width:170px;height:36px;object-fit:contain;background:transparent}
  .search-box{display:flex;align-items:center;background:#f5f6fa;border:1px solid #cfd3e2;border-radius:999px;overflow:hidden;min-height:46px}
  .search-box input{width:100%;padding:11px 18px;border:none;outline:none;background:transparent;font-size:18px;line-height:1.2;color:#444;font-weight:500}
  .search-box input::placeholder{color:#7d8395}
  .search-box button{background:#48248b;color:#fff;padding:11px 28px;font-weight:700;border:none;border-radius:999px}
  .nav-actions{display:flex;justify-content:center;align-items:center;gap:24px;white-space:nowrap}
  .menu-link{padding:0;font-weight:700;font-size:18px;color:#48248b;text-decoration:none;border:none;background:transparent}
  .menu-link:hover{color:#5b33a6}
  .btn-primary-custom{padding:11px 18px;border-radius:12px;font-weight:700;border:1px solid #48248b;background:#48248b;color:#fff;text-decoration:none}
  .nosotros-wrap{padding:30px 0 42px}
  .nosotros-card{background:#fff;border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow);padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start}
  .nosotros-card img{width:100%;max-height:420px;object-fit:cover;border-radius:16px;background:#f8fafc}
  .nosotros-card h1{font-size:32px;color:var(--primary);margin-bottom:10px}
  .nosotros-card p{color:var(--muted);line-height:1.6}
  .footer{position:relative;color:#fff;padding:30px 0 20px;background:linear-gradient(120deg,var(--primary),var(--accent));margin-top:24px}
  .footer-content{display:grid;grid-template-columns:1.1fr .9fr 1fr .9fr;gap:22px}
  .footer-col h4{font-size:18px;font-weight:600;margin:0 0 10px}
  .footer-brand img{width:128px;height:60px;object-fit:contain;background:#fff;border-radius:10px;padding:4px 8px;border:1px solid rgba(255,255,255,.35);margin-bottom:8px}
  .footer-brand p,.footer-contact p,.footer-menu a,.footer-follow p{font-size:13px;color:rgba(255,255,255,.92);margin:0}
  .footer-menu{display:grid;gap:8px}
  .footer-menu a{color:#fff;text-decoration:none}
  .footer-sociales{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
  .footer-sociales a{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:999px;border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08);color:#fff;text-decoration:none}
  .footer-sociales a svg{width:14px;height:14px;fill:#fff}
  @media (max-width:1100px){.catalogo-navbar,.nosotros-card,.footer-content{grid-template-columns:1fr}.nav-actions{justify-content:flex-start;gap:12px}}
</style>
<div class="catalogo-page">
  <div class="catalogo-topbar"><div class="catalogo-container catalogo-topbar__content"><div><?= e($topbarTexto) ?></div><?php if ($socialesTopbar !== []): ?><div class="catalogo-topbar__sociales"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div><?php endif; ?></div></div>
  <header class="catalogo-header"><div class="catalogo-container catalogo-navbar"><a class="catalogo-logo" href="<?= e($catalogoBaseUrl) ?>"><img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa"></a><form class="search-box" method="GET" action="<?= e($catalogoBaseUrl) ?>"><input type="text" name="q" placeholder="Buscar productos, categorías o marcas..."><button type="submit">Buscar</button></form><nav class="nav-actions" aria-label="Menú superior catálogo"><a class="menu-link" href="<?= e($catalogoBaseUrl) ?>">Inicio</a><a class="menu-link" href="<?= e($catalogoNosotrosUrl) ?>">Nosotros</a><a class="menu-link" href="<?= e($catalogoContactoUrl) ?>">Contacto</a></nav><a class="btn-primary-custom d-inline-flex align-items-center gap-2" href="<?= e($catalogoBaseUrl) ?>"><span aria-hidden="true">🛒</span><span>Ver carrito</span></a></div></header>
  <section class="nosotros-wrap"><div class="catalogo-container"><article class="nosotros-card"><img src="<?= e($nosotrosImagen) ?>" alt="Foto de nosotros"><div><h1><?= e($nosotrosTitulo) ?></h1><p><?= nl2br(e($nosotrosDescripcion)) ?></p></div></article></div></section>
  <footer class="footer"><div class="catalogo-container footer-content"><div class="footer-brand footer-col"><img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa"><p><?= e((string) (($empresa['descripcion'] ?? '') !== '' ? $empresa['descripcion'] : 'Diseño profesional para mostrar y vender productos online.')) ?></p></div><div class="footer-col"><h4>Accesos rápidos</h4><nav class="footer-menu mt-2"><a href="<?= e($catalogoBaseUrl) ?>">Inicio</a><a href="<?= e($catalogoBaseUrl) ?>/nosotros">Nosotros</a><a href="<?= e($catalogoBaseUrl) ?>/contacto">Contacto</a></nav></div><div class="footer-contact footer-col"><h4>Datos de contacto</h4><p><?= e((string) ($empresa['telefono'] ?? 'No informado')) ?></p><p><?= e((string) ($empresa['correo'] ?? 'No informado')) ?></p><p><?= e((string) ($empresa['direccion'] ?? 'No informada')) ?></p></div><div class="footer-follow footer-col"><h4>Síguenos</h4><p>Conéctate en redes sociales y conoce ofertas y productos destacados.</p><?php if ($socialesTopbar !== []): ?><div class="footer-sociales"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div><?php endif; ?></div></div></footer>
</div>
