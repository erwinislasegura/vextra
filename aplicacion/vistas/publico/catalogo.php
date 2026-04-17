<?php
$fmon = static fn(float $m): string => '$' . number_format($m, 0, ',', '.');
$catalogoBaseUrl = url('/catalogo/' . (int) ($empresa['id'] ?? 0));
$catalogoNosotrosUrl = $catalogoBaseUrl . '/nosotros';
$catalogoContactoUrl = $catalogoBaseUrl . '/contacto';
$resolverImagenProducto = static function (?string $ruta): string {
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return url('/img/placeholder-producto.svg');
    }
    if (preg_match('/^https?:\/\//i', $ruta) === 1) {
        return $ruta;
    }

    $normalizada = str_replace('\\', '/', $ruta);
    if (str_starts_with($normalizada, '/media/archivo')) {
        return url($normalizada);
    }

    $normalizada = preg_replace('#^https?://[^/]+#i', '', $normalizada) ?? $normalizada;
    $normalizada = preg_replace('#^/?public/#i', '/', $normalizada) ?? $normalizada;
    $normalizada = preg_replace('#^/?aplicacion/public/#i', '/', $normalizada) ?? $normalizada;
    if (str_starts_with($normalizada, 'uploads/')) {
        $normalizada = '/' . $normalizada;
    }
    if (str_contains($normalizada, '/uploads/')) {
        $partes = explode('/uploads/', $normalizada, 2);
        $normalizada = '/uploads/' . ($partes[1] ?? '');
    } elseif (str_contains($normalizada, 'productos_catalogo/')) {
        $partes = explode('productos_catalogo/', $normalizada, 2);
        $normalizada = '/uploads/productos_catalogo/' . ($partes[1] ?? '');
    }
    $normalizada = '/' . ltrim($normalizada, '/');

    if (str_starts_with($normalizada, '/uploads/') || str_starts_with($normalizada, '/img/')) {
        return url($normalizada);
    }

    return url('/' . ltrim($normalizada, '/'));
};

$topbarTexto = trim((string) ($catalogoTopbar['texto'] ?? ''));
if ($topbarTexto === '') {
    $topbarTexto = 'Envíos a todo el país • Garantía en todos los productos';
}
$colorPrimario = trim((string) ($catalogoTopbar['color_primario'] ?? ''));
if (preg_match('/^#([A-Fa-f0-9]{6})$/', $colorPrimario) !== 1) {
    $colorPrimario = '#4632A8';
}
$colorAcento = trim((string) ($catalogoTopbar['color_acento'] ?? ''));
if (preg_match('/^#([A-Fa-f0-9]{6})$/', $colorAcento) !== 1) {
    $colorAcento = '#5415B0';
}
$columnasProductos = (int) ($catalogoTopbar['columnas_productos'] ?? 3);
if ($columnasProductos < 2 || $columnasProductos > 5) {
    $columnasProductos = 3;
}
$socialesTopbar = [
    ['id' => 'facebook', 'url' => trim((string) ($catalogoTopbar['sociales']['facebook'] ?? '')), 'label' => 'Facebook'],
    ['id' => 'instagram', 'url' => trim((string) ($catalogoTopbar['sociales']['instagram'] ?? '')), 'label' => 'Instagram'],
    ['id' => 'tiktok', 'url' => trim((string) ($catalogoTopbar['sociales']['tiktok'] ?? '')), 'label' => 'TikTok'],
    ['id' => 'linkedin', 'url' => trim((string) ($catalogoTopbar['sociales']['linkedin'] ?? '')), 'label' => 'LinkedIn'],
    ['id' => 'youtube', 'url' => trim((string) ($catalogoTopbar['sociales']['youtube'] ?? '')), 'label' => 'YouTube'],
];
$socialesTopbar = array_values(array_filter($socialesTopbar, static fn(array $red): bool => $red['url'] !== ''));
$sliderImagenPrincipal = (string) ($sliderCatalogo['imagen'] ?: url('/media/archivo?ruta=' . rawurlencode('/img/placeholder-producto.svg')));
$sliderImagenSecundaria = (string) ($sliderCatalogo['imagen_secundaria'] ?: $sliderImagenPrincipal);
$productosDestacados = array_values(array_filter($productos, static fn(array $producto): bool => (int) ($producto['destacado_catalogo'] ?? 0) === 1));
$productosProximos = array_values(array_filter($productos, static fn(array $producto): bool => (int) ($producto['proximo_catalogo'] ?? 0) === 1));
$productosCarrusel = array_slice(array_merge($productosDestacados, $productosProximos), 0, 24);
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
  :root{--primary:<?= e($colorPrimario) ?>;--primary-soft:<?= e($colorPrimario) ?>;--accent:<?= e($colorAcento) ?>;--accent-hover:<?= e($colorPrimario) ?>;--danger:#dc2626;--bg:#eef2f7;--card:#ffffff;--text:#0f172a;--muted:#64748b;--border:#dbe3ee;--shadow:0 10px 25px rgba(15,23,42,.08);--radius:18px}
  .catalogo-page{background:var(--bg)}
  .catalogo-container{width:min(1280px,92%);margin:0 auto}
  .catalogo-topbar{background:var(--primary);color:#fff;padding:8px 0;font-size:13px}
  .catalogo-topbar__content{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .catalogo-topbar__sociales{display:flex;align-items:center;gap:10px}
  .catalogo-topbar__sociales a{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:999px;border:1px solid rgba(255,255,255,.5);color:#fff;text-decoration:none;transition:all .2s ease}
  .catalogo-topbar__sociales a svg{width:14px;height:14px;fill:#fff;display:block}
  .catalogo-topbar__sociales a:hover{background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.55)}
  .catalogo-header{position:sticky;top:0;z-index:45;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
  .catalogo-navbar{display:grid;grid-template-columns:340px minmax(0,1fr) auto auto;gap:10px;align-items:center;padding:10px 0}
  .catalogo-logo{display:flex;align-items:center;gap:.55rem;color:var(--text);font-size:16px;font-weight:800;text-decoration:none;line-height:1.05}
  .catalogo-logo img{width:120px;height:60px;object-fit:contain;background:transparent}
  .catalogo-logo small{display:block;font-size:11px;font-weight:600;color:var(--muted);margin-top:2px}
  .catalogo-logo span{color:var(--accent)}
  .search-box{display:flex;align-items:center;background:#fff;border:1px solid var(--border);border-radius:999px;overflow:hidden;min-width:0}
  .search-box input{width:100%;padding:10px 14px;border:none;outline:none;background:transparent;font-size:14px}
  .search-box button{background:var(--accent);color:#fff;padding:10px 18px;font-weight:700;border:none}
  .nav-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .menu-link{padding:9px 6px;font-weight:600;color:var(--primary);text-decoration:none;border:none;background:transparent;white-space:nowrap}
  .menu-link:hover{color:var(--accent)}
  .btn-outline,.btn-primary-custom,.btn-soft,.btn-danger-soft{padding:9px 13px;border-radius:10px;font-weight:700;border:1px solid var(--border);background:#fff;color:var(--text)}
  .btn-primary-custom{background:var(--accent);border-color:var(--accent);color:#fff}
  .btn-reservar{background:linear-gradient(135deg,#f59e0b,#d97706);border-color:#d97706 !important;color:#fff !important}
  .catalogo-navbar .btn-primary-custom,.catalogo-navbar .btn-primary-custom span,.catalogo-navbar .btn-primary-custom svg{color:#fff !important;fill:#fff !important;stroke:#fff !important;text-decoration:none !important}
  .catalogo-mobile-toggle{display:none;align-items:center;justify-content:center;flex-direction:column;gap:4px;width:42px;height:42px;border-radius:12px;border:1px solid var(--primary);background:var(--primary);color:#fff}
  .catalogo-mobile-toggle span{display:block;width:18px;height:2px;background:currentColor;border-radius:999px;transition:all .2s ease}
  .catalogo-header.is-mobile-open .catalogo-mobile-toggle span:nth-child(1){transform:translateY(6px) rotate(45deg)}
  .catalogo-header.is-mobile-open .catalogo-mobile-toggle span:nth-child(2){opacity:0}
  .catalogo-header.is-mobile-open .catalogo-mobile-toggle span:nth-child(3){transform:translateY(-6px) rotate(-45deg)}
  .btn-soft{background:#eff6ff;color:var(--accent)}
  .btn-danger-soft{background:#fff1f2;color:var(--danger);border-color:#fde2e2}
  .hero{padding:26px 0 22px}
  .hero-grid{display:grid;grid-template-columns:1fr}
  .info-section{padding:8px 0 24px}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
  .info-card{background:#fff;border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow);padding:20px}
  .info-card h2{font-size:24px;color:var(--primary);margin-bottom:10px}
  .info-card p{color:var(--muted);margin:0}
  .nosotros-foto{width:100%;max-height:280px;object-fit:cover;border-radius:14px;margin-bottom:12px;background:#f8fafc}
  .contacto-list{display:grid;gap:8px;margin-top:12px}
  .contacto-list strong{color:var(--primary)}
  .slider{position:relative;min-height:420px;border-radius:26px;overflow:hidden;box-shadow:var(--shadow);background:linear-gradient(135deg,#0f172a,#1d4ed8)}
  .slide{position:absolute;inset:0;opacity:0;visibility:hidden;transition:opacity 1.6s ease-in-out,transform 8s ease-out;transform:scale(1.05);display:flex;align-items:flex-end;padding:40px;color:#fff;background-size:cover;background-position:center}
  .slide::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(15,23,42,.78) 0%,rgba(15,23,42,.45) 45%,rgba(15,23,42,.2) 100%)}
  .slide-content{position:relative;z-index:2;max-width:min(720px,92%)}
  .slide.active{opacity:1;visibility:visible;transform:scale(1)}
  .slide h2{font-size:52px;line-height:1.1;margin-bottom:14px;font-weight:800}
  .slide p{color:rgba(255,255,255,.92);margin-bottom:20px;max-width:520px;font-size:18px;font-weight:500;line-height:1.5}
  .slide-actions{display:flex;gap:12px;flex-wrap:wrap}
  .slide-actions .btn-primary-custom{color:#fff !important;font-weight:600;padding:12px 30px;min-width:220px;text-align:center;text-decoration:none !important}
  .home-carousel{padding:0 0 26px}
  .home-carousel__shell{background:linear-gradient(140deg,#ffffff 0%,#f1f4ff 100%);border:1px solid #d7dcf7;border-radius:18px;padding:14px 14px 8px;box-shadow:0 14px 30px rgba(37,45,89,.10)}
  .home-carousel__header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
  .home-carousel__header h3{margin:0;color:var(--primary);font-size:32px;font-weight:800}
  .home-carousel__controls{display:flex;gap:8px}
  .home-carousel__nav{width:36px;height:36px;border-radius:10px;border:1px solid #cbcffa;background:#fff;color:#312e81;font-weight:700}
  .home-carousel__track{display:flex;gap:16px;overflow-x:auto;padding:4px 2px 14px;scrollbar-width:thin;scroll-snap-type:x mandatory}
  .home-carousel__item{min-width:270px;max-width:270px;background:linear-gradient(180deg,#ffffff 0%,#f3f4ff 100%);border:1px solid #d4d8f0;border-radius:12px;padding:12px;box-shadow:0 8px 18px rgba(30,41,59,.12);scroll-snap-align:start;display:flex;flex-direction:column;gap:10px}
  .home-carousel__item img{width:100%;height:190px;object-fit:cover;border-radius:10px;background:#eef2ff}
  .home-carousel__item h4{font-size:17px;margin:0;color:var(--primary);font-weight:700;line-height:1.25}
  .home-carousel__meta{font-size:13px;margin:0;color:var(--muted)}
  .home-carousel__actions{margin-top:auto;display:flex;align-items:center;justify-content:space-between;gap:10px}
  .home-carousel__actions .btn{font-size:13px;font-weight:700;padding:8px 14px;border-radius:8px}
  .section-head h2,.sidebar h3{font-size:18px;color:var(--primary);font-weight:700}
  .promo-box{padding:16px;border-radius:18px;background:#f8fafc;border:1px solid var(--border)}
  .promo-box p{color:var(--muted);margin:6px 0 0}
  .filters-section{padding:8px 0 24px}
  .filters-wrap{background:#fff;border:1px solid var(--border);border-radius:22px;padding:16px;box-shadow:var(--shadow);display:grid;grid-template-columns:1.2fr repeat(3,1fr) auto;gap:12px;align-items:end}
  .field{display:flex;flex-direction:column;gap:8px}.field label{font-size:13px;font-weight:700;color:var(--muted)}
  .field input,.field select{border:1px solid var(--border);background:#fff;padding:13px 14px;border-radius:14px;outline:none;font-size:14px}
  .main-content{padding:0 0 46px}
  .content-grid{display:grid;grid-template-columns:280px 1fr;gap:22px}
  .sidebar{background:#fff;border-radius:18px;box-shadow:0 8px 18px rgba(15,23,42,.08);padding:14px;border:1px solid var(--border);height:fit-content;position:sticky;top:96px}
  .sidebar h3{background:#f3f6fb;border:1px solid var(--border);border-radius:12px;padding:8px 10px}
  .category-list,.feature-list{list-style:none;display:grid;gap:4px;margin-top:8px}
  .category-list{max-height:320px;overflow:auto;padding-right:4px}
  .feature-list{grid-template-columns:1fr 1fr}
  .category-list button,.feature-list button{width:100%;text-align:left;background:transparent;border:none;border-bottom:1px solid #e6edf6;border-radius:0;padding:8px 4px;font-weight:400;font-size:14px;color:var(--primary-soft)}
  .category-list button.active{color:var(--accent);font-weight:600}
  .section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap}
  .section-head p{color:var(--muted)}
  .products-grid{display:grid;grid-template-columns:repeat(<?= (int) $columnasProductos ?>,minmax(0,1fr));gap:18px}
  .product-card{background:#fff;border:1px solid #d7deea;border-radius:12px;overflow:hidden;box-shadow:0 10px 20px rgba(15,23,42,.08);display:flex;flex-direction:column;transition:transform .2s ease,box-shadow .2s ease}
  .product-card:hover{transform:translateY(-4px);box-shadow:0 16px 30px rgba(15,23,42,.12)}
  .product-image{position:relative;height:220px;background:#dce3ee;overflow:hidden}.product-image img{width:100%;height:100%;object-fit:cover}
  .badge-mini{position:absolute;top:14px;left:14px;background:#fff;color:var(--primary);font-size:12px;font-weight:700;padding:7px 10px;border-radius:999px;box-shadow:var(--shadow)}
  .badge-mini.sale{background:linear-gradient(135deg,#ffedd5,#fed7aa);color:#9a3412;border:1px solid #fdba74}
  .badge-mini.destacado{left:auto;right:14px;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);color:#312e81;border:1px solid #a5b4fc}
  .badge-mini.proximo{left:auto;right:14px;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e;border:1px solid #fcd34d}
  .product-body{padding:16px;display:flex;flex-direction:column;gap:10px;flex:1}
  .category-tag{font-size:11px;font-weight:600;color:var(--accent);letter-spacing:.2px;text-transform:uppercase}
  .product-title{font-size:16px;font-weight:600;color:var(--primary);line-height:1.3;margin:0}
  .product-desc{color:var(--muted);font-size:13px;line-height:1.45;min-height:38px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .stock-line{font-size:13px;color:var(--muted);font-weight:600}
  .price-wrap{display:flex;align-items:baseline;gap:8px}.price{font-size:22px;font-weight:600;color:var(--primary)}.old-price{color:#94a3b8;text-decoration:line-through;font-weight:500}
  .card-actions{display:grid;grid-template-columns:1fr auto;gap:10px;margin-top:auto}
  .card-actions .btn-primary-custom{font-size:13px;font-weight:600;padding:8px 12px}
  .card-actions .btn-warning{font-size:13px;font-weight:700;padding:8px 12px;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;color:#fff}
  .icon-btn{width:48px;border-radius:14px;background:#f8fafc;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center}
  .icon-btn svg{width:18px;height:18px;stroke:var(--primary);fill:none;stroke-width:2}
  .cart-toggle{position:fixed;right:20px;bottom:20px;z-index:70;background:var(--primary);color:#fff;border-radius:14px;padding:10px 14px;box-shadow:var(--shadow);display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;border:none}
  .cart-count{background:var(--accent);min-width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:12px}
  .cart-panel{position:fixed;top:0;right:-420px;width:400px;max-width:100%;height:100vh;background:#fff;border-left:1px solid var(--border);box-shadow:-10px 0 30px rgba(15,23,42,.12);z-index:90;transition:right .3s ease;display:flex;flex-direction:column}
  .cart-panel.open{right:0}.cart-header,.cart-footer{padding:20px}.cart-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)}
  .cart-items{flex:1;overflow:auto;padding:14px;display:grid;gap:10px}.cart-item{display:grid;grid-template-columns:60px 1fr auto;gap:8px;align-items:center;border:1px solid var(--border);border-radius:12px;padding:8px}
  .cart-header h3{font-size:18px;font-weight:500;margin:0;color:var(--primary)}
  .cart-item h4{font-size:14px;font-weight:600;line-height:1.2;margin:0 0 2px;color:#243447}
  .cart-item p{font-size:12px;font-weight:500;margin:0;color:var(--text)}
  .cart-item__desc{font-size:11px;font-weight:400;line-height:1.35;color:var(--muted);margin-top:3px}
  .cart-item img{width:60px;height:60px;object-fit:cover;border-radius:9px}.qty-controls{display:flex;align-items:center;gap:7px;margin-top:6px;font-size:12px}
  .qty-controls button{width:24px;height:24px;border-radius:7px;background:#f1f5f9;border:1px solid var(--border);font-size:13px;font-weight:500;line-height:1}
  .cart-item .btn-danger-soft{font-size:13px;font-weight:500;padding:6px 9px}
  .cart-footer{border-top:1px solid var(--border);display:grid;gap:10px}.summary-row{display:flex;justify-content:space-between;font-size:14px;font-weight:500}
  .empty-state{text-align:center;color:var(--muted);padding:40px 10px}.overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);opacity:0;visibility:hidden;transition:.25s;z-index:80}.overlay.show{opacity:1;visibility:visible}
  .footer{position:relative;color:#fff;padding:30px 0 20px;margin-top:20px;background:linear-gradient(120deg,var(--primary),var(--accent))}
  .footer-content{display:grid;grid-template-columns:1.1fr .9fr 1fr .9fr;gap:22px}
  .footer-col h4{font-size:18px;font-weight:600;margin:0 0 10px}
  .footer-brand img{width:128px;height:60px;object-fit:contain;background:#fff;border-radius:10px;padding:4px 8px;border:1px solid rgba(255,255,255,.35);margin-bottom:8px}
  .footer-brand p,.footer-contact p,.footer-menu a,.footer-follow p{font-size:13px;color:rgba(255,255,255,.92);margin:0}
  .footer-contact{display:grid;gap:8px}
  .footer-contact p{display:flex;align-items:center;gap:8px}
  .footer-contact p .dot{width:24px;height:24px;border-radius:999px;border:1px solid rgba(255,255,255,.45);display:inline-flex;align-items:center;justify-content:center}
  .footer-contact p .dot svg{width:12px;height:12px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .footer-menu{display:grid;gap:8px}
  .footer-menu a,.footer-menu a:link,.footer-menu a:visited{color:#fff !important;text-decoration:none}
  .footer-menu a:hover{text-decoration:underline}
  .footer-follow{display:grid;gap:10px}
  .footer-sociales{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
  .footer-sociales a{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:999px;border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08);color:#fff;text-decoration:none}
  .footer-sociales a svg{width:14px;height:14px;fill:#fff}
  .footer-bottom{background:#fff;border-top:1px solid #e5e7eb;padding:10px 0}
  .footer-bottom__content{display:flex;justify-content:space-between;align-items:center;color:#4b5563;font-size:13px;font-weight:500;gap:12px}
  .footer-bottom__content a{color:#3f2a84;font-weight:700;text-decoration:none}
  .footer-bottom__content a:hover{text-decoration:underline}
  body.public-page > footer.border-top.bg-white.mt-5{display:none}
  .catalogo-checkout .form-control,.catalogo-checkout .form-select{border-radius:.65rem}
  .catalogo-checkout__block{border:1px solid #edf1f5;border-radius:.95rem;padding:1rem;background:#fff}
  .catalogo-checkout__title{font-weight:700;font-size:.95rem;margin-bottom:.75rem}
  @media (max-width:1100px){
    .catalogo-navbar,.hero-grid,.content-grid,.filters-wrap,.info-grid{grid-template-columns:1fr}
    .catalogo-navbar{gap:12px}
    .search-box{width:100%}
    .nav-actions{justify-content:flex-start}
    .catalogo-navbar .btn-primary-custom{justify-content:center}
    .slide h2{font-size:40px;font-weight:700}
    .slide p{font-size:16px;font-weight:400;max-width:100%}
    .sidebar{position:static}
    .sidebar{padding:12px}
    .sidebar h3{font-size:16px}
    .category-list,.feature-list{display:flex;gap:8px;overflow:auto;max-height:none;padding:4px 2px 2px;margin-top:8px}
    .category-list button,.feature-list button{border:1px solid var(--border);border-radius:999px;padding:8px 12px;white-space:nowrap;min-width:max-content;background:#fff;font-size:13px;font-weight:500}
    .products-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .home-carousel__header h3{font-size:24px}
    .home-carousel__item{min-width:250px;max-width:250px}
    .footer-content{grid-template-columns:repeat(2,minmax(0,1fr))}
  }
  @media (max-width:720px){
    .products-grid{grid-template-columns:1fr}
    .sidebar{display:none}
    .catalogo-topbar__content,.section-head{display:flex;flex-direction:column;align-items:flex-start}
    .catalogo-navbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px}
    .catalogo-logo{justify-content:flex-start}
    .catalogo-logo img{width:110px;height:56px}
    .catalogo-mobile-toggle{display:inline-flex}
    .search-box,.nav-actions{display:none}
    .catalogo-header.is-mobile-open .search-box{display:flex;grid-column:1/-1}
    .catalogo-header.is-mobile-open .nav-actions{display:flex;grid-column:1/-1}
    .catalogo-navbar > .btn-primary-custom{display:flex !important;grid-column:1/-1}
    .search-box{border-radius:14px}
    .search-box input,.search-box button{font-size:13px}
    .search-box button{padding:10px 14px}
    .nav-actions{justify-content:space-between;gap:6px}
    .menu-link{flex:1;text-align:center;padding:8px 8px;border:1px solid color-mix(in srgb,var(--primary) 25%,#fff);border-radius:10px;background:#fff;color:var(--primary);font-size:13px;font-weight:500}
    .catalogo-navbar .btn-primary-custom{width:100%;justify-content:center;font-size:14px;font-weight:600}
    .filters-wrap{padding:12px}
    .field input,.field select{padding:11px 12px}
    .slide{padding:24px}
    .slide h2{font-size:27px;font-weight:600}
    .slide p{font-size:14px;font-weight:400;line-height:1.45}
    .slide-actions .btn-primary-custom{width:100%;min-width:0}
    .footer-content{grid-template-columns:1fr}
    .footer-bottom__content{flex-direction:column;align-items:flex-start}
    .cart-panel{width:100%}
    .cart-toggle{right:12px;bottom:12px}
  }
</style>

<div class="catalogo-page">
  <?php
    $catalogoHeaderSearchAction = $catalogoBaseUrl;
    $catalogoHeaderSearchMethod = 'GET';
    $catalogoHeaderSearchName = 'q';
    $catalogoHeaderSearchValue = '';
    $catalogoHeaderSearchInputId = 'globalSearch';
    $catalogoHeaderSearchButtonId = 'searchBtn';
    $catalogoHeaderCartAsButton = true;
    $catalogoHeaderCartButtonId = 'openCartHeader';
    require __DIR__ . '/partials/catalogo_header.php';
  ?>

  <section class="hero">
    <div class="catalogo-container hero-grid">
      <div class="slider" id="slider">
        <article class="slide active" style="background-image:url('<?= e($sliderImagenPrincipal) ?>');">
          <div class="slide-content">
            <h2><?= e((string) ($sliderCatalogo['titulo'] ?? 'Catálogo online moderno y profesional')) ?></h2>
            <p><?= e((string) ($sliderCatalogo['bajada'] ?? 'Presenta tus productos con una experiencia de compra elegante, rápida y totalmente ordenada para tus clientes.')) ?></p>
            <div class="slide-actions">
              <?php if (!empty($sliderCatalogo['boton_url']) && !empty($sliderCatalogo['boton_texto'])): ?>
                <a href="<?= e((string) $sliderCatalogo['boton_url']) ?>" class="btn-primary-custom" target="_blank" rel="noopener noreferrer"><?= e((string) $sliderCatalogo['boton_texto']) ?></a>
              <?php endif; ?>
            </div>
          </div>
        </article>
        <article class="slide" style="background-image:url('<?= e($sliderImagenSecundaria) ?>');">
          <div class="slide-content">
            <h2><?= e((string) ($sliderCatalogo['titulo'] ?? 'Catálogo online moderno y profesional')) ?></h2>
            <p><?= e((string) ($sliderCatalogo['bajada'] ?? 'Presenta tus productos con una experiencia de compra elegante, rápida y totalmente ordenada para tus clientes.')) ?></p>
            <div class="slide-actions">
              <?php if (!empty($sliderCatalogo['boton_url']) && !empty($sliderCatalogo['boton_texto'])): ?>
                <a href="<?= e((string) $sliderCatalogo['boton_url']) ?>" class="btn-primary-custom" target="_blank" rel="noopener noreferrer"><?= e((string) $sliderCatalogo['boton_texto']) ?></a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <?php if (!empty($productosCarrusel)): ?>
    <section class="home-carousel">
      <div class="catalogo-container">
        <div class="home-carousel__shell">
          <div class="home-carousel__header">
            <h3>Destacados y próximos en llegar</h3>
            <div class="d-flex align-items-center gap-2">
              <small class="text-muted">Se desplaza automáticamente</small>
              <div class="home-carousel__controls">
                <button type="button" class="home-carousel__nav" id="homeCarouselPrev" aria-label="Anterior">‹</button>
                <button type="button" class="home-carousel__nav" id="homeCarouselNext" aria-label="Siguiente">›</button>
              </div>
            </div>
          </div>
          <div class="home-carousel__track" id="homeProductosCarrusel">
            <?php foreach ($productosCarrusel as $producto): ?>
              <?php
                $imagenProductoUrl = url('/catalogo/' . (int) $empresa['id'] . '/producto/' . (int) $producto['id'] . '/imagen');
                $nombreProducto = (string) ($producto['nombre'] ?? 'Producto');
                $categoriaProducto = (string) ($producto['categoria'] ?? 'Sin categoría');
                $esProximo = (int) ($producto['proximo_catalogo'] ?? 0) === 1;
                $diasProximo = max(0, (int) ($producto['proximo_dias_catalogo'] ?? 0));
                $precioProducto = (float) ($producto['precio'] ?? 0);
                $precioOfertaProducto = (float) ($producto['precio_oferta'] ?? 0);
                $precioMostrar = ($precioOfertaProducto > 0 && $precioOfertaProducto < $precioProducto) ? $precioOfertaProducto : $precioProducto;
              ?>
              <article class="home-carousel__item">
                <img src="<?= e($imagenProductoUrl) ?>" alt="<?= e($nombreProducto) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(url('/img/placeholder-producto.svg')) ?>';">
                <h4><?= e($nombreProducto) ?></h4>
                <p class="home-carousel__meta"><?= e($categoriaProducto) ?></p>
                <div class="home-carousel__actions">
                  <div>
                    <?php if ($esProximo): ?>
                      <span class="badge text-bg-warning">Llega en <?= $diasProximo ?> día(s)</span>
                    <?php else: ?>
                      <span class="badge text-bg-primary">Destacado</span>
                    <?php endif; ?>
                    <div class="small fw-semibold mt-1"><?= e($fmon($precioMostrar)) ?></div>
                  </div>
                  <?php if ($esProximo): ?>
                    <button
                      type="button"
                      class="btn btn-primary-custom btn-reservar"
                      data-add-cart
                      data-id="<?= (int) $producto['id'] ?>"
                      data-name="<?= e($nombreProducto) ?>"
                      data-price="<?= $precioMostrar ?>"
                      data-description="<?= e((string) ($producto['descripcion'] ?? '')) ?>"
                      data-image="<?= e($imagenProductoUrl) ?>"
                      data-proximo="1"
                      data-proximo-dias="<?= $diasProximo ?>"
                    >Reservar</button>
                  <?php else: ?>
                    <button type="button" class="btn btn-primary-custom" data-carousel-open data-id="<?= (int) $producto['id'] ?>">Lo quiero</button>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="filters-section" id="catalogoProductos">
    <div class="catalogo-container filters-wrap">
      <div class="field"><label for="searchInput">Buscar producto</label><input type="text" id="searchInput" placeholder="Ej: audífonos, reloj, mochila..."></div>
      <div class="field"><label for="categoryFilter">Categoría</label><select id="categoryFilter"><option value="all">Todas</option></select></div>
      <div class="field"><label for="priceFilter">Rango de precio</label><select id="priceFilter"><option value="all">Todos</option><option value="0-50000">Hasta $50.000</option><option value="50001-100000">$50.001 a $100.000</option><option value="100001-200000">$100.001 a $200.000</option><option value="200001-99999999">Más de $200.000</option></select></div>
      <div class="field"><label for="sortFilter">Ordenar por</label><select id="sortFilter"><option value="featured">Destacados</option><option value="price-asc">Precio: menor a mayor</option><option value="price-desc">Precio: mayor a menor</option><option value="name-asc">Nombre A-Z</option></select></div>
      <button class="btn-danger-soft" type="button" id="clearFilters">Limpiar</button>
    </div>
  </section>

  <main class="main-content">
    <div class="catalogo-container content-grid">
      <aside class="sidebar">
        <h3>Categorías</h3>
        <div class="category-list" id="categoryButtons"></div>
        <h3 style="margin-top:16px;">Acciones rápidas</h3>
        <div class="feature-list">
          <button type="button" id="showAllBtn">Ver todos los productos</button>
          <button type="button" id="showOffersBtn">Ver ofertas</button>
          <button type="button" id="showStockBtn">Solo con stock</button>
          <button type="button" id="showCheapestBtn">Más baratos primero</button>
          <button type="button" id="showExpensiveBtn">Más caros primero</button>
        </div>
      </aside>

      <section class="products-area">
        <div class="section-head">
          <div><h2>Nuestro catálogo</h2><p id="resultsInfo">Explora nuestros productos destacados.</p></div>
          <button class="btn-soft" type="button" id="scrollTopBtn">Ir arriba</button>
        </div>

        <div class="products-grid" id="productsGrid">
          <?php foreach ($productos as $producto): ?>
            <?php
              $imagenProducto = (string) ($producto['imagen_catalogo'] ?? $producto['imagen_catalogo_url'] ?? '');
              $imagenProductoUrl = url('/catalogo/' . (int) $empresa['id'] . '/producto/' . (int) $producto['id'] . '/imagen');
              $imagenProductoFallback = $resolverImagenProducto($imagenProducto);
              $placeholderProductoUrl = url('/media/archivo?ruta=' . rawurlencode('/img/placeholder-producto.svg'));
              $precio = (float) ($producto['precio'] ?? 0);
              $categoria = (string) ($producto['categoria'] ?? 'Sin categoría');
              $nombreProducto = (string) ($producto['nombre'] ?? 'Producto');
              $descripcionProducto = (string) ($producto['descripcion'] ?? 'Sin descripción');
              $stock = (int) ($producto['stock_actual'] ?? rand(3, 40));
              $destacado = (int) ($producto['destacado_catalogo'] ?? 0) === 1;
              $proximo = (int) ($producto['proximo_catalogo'] ?? 0) === 1;
              $proximoDias = max(0, (int) ($producto['proximo_dias_catalogo'] ?? 0));
              $precioOferta = (float) ($producto['precio_oferta'] ?? 0);
              $onSale = $precioOferta > 0 && $precioOferta < $precio;
              $precioMostrar = $onSale ? $precioOferta : $precio;
              $oldPrice = $onSale ? $precio : 0;
            ?>
            <article class="product-card" data-producto-card data-id="<?= (int) $producto['id'] ?>" data-name="<?= e($nombreProducto) ?>" data-price="<?= $precioMostrar ?>" data-category="<?= e($categoria) ?>" data-description="<?= e($descripcionProducto) ?>" data-image="<?= e($imagenProductoUrl) ?>" data-stock="<?= $stock ?>" data-onsale="<?= $onSale ? '1' : '0' ?>" data-oldprice="<?= $oldPrice ?>" data-image-fallback="<?= e($imagenProductoFallback !== '' ? $imagenProductoFallback : $placeholderProductoUrl) ?>" data-proximo="<?= $proximo ? '1' : '0' ?>" data-proximo-dias="<?= $proximoDias ?>">
              <div class="product-image">
                <img
                  src="<?= e($imagenProductoUrl) ?>"
                  alt="<?= e($nombreProducto) ?>"
                  loading="lazy"
                  onerror="if(this.dataset.fallback && this.src !== this.dataset.fallback){this.src=this.dataset.fallback;return;}this.onerror=null;this.src='<?= e($placeholderProductoUrl) ?>';"
                  data-fallback="<?= e($imagenProductoFallback !== '' ? $imagenProductoFallback : $placeholderProductoUrl) ?>"
                >
                <?php if ($onSale): ?>
                  <span class="badge-mini sale">Oferta</span>
                <?php endif; ?>
                <?php if ($destacado): ?>
                  <span class="badge-mini destacado">Destacado</span>
                <?php endif; ?>
                <?php if ($proximo): ?>
                  <span class="badge-mini proximo">Próximamente</span>
                <?php endif; ?>
              </div>
              <div class="product-body">
                <span class="category-tag"><?= e($categoria) ?></span>
                <h3 class="product-title"><?= e($nombreProducto) ?></h3>
                <p class="product-desc"><?= e($descripcionProducto) ?></p>
                <div class="stock-line">Stock: <?= $stock ?></div>
                <?php if ($proximo): ?>
                  <div class="stock-line text-warning-emphasis">Reserva · llega en <?= $proximoDias ?> día(s)</div>
                <?php endif; ?>
                <div class="price-wrap"><div class="price"><?= e($fmon($precioMostrar)) ?></div><?php if ($oldPrice > 0): ?><div class="old-price"><?= e($fmon($oldPrice)) ?></div><?php endif; ?></div>
                <div class="card-actions">
                  <?php if ($proximo): ?>
                    <button class="btn-primary-custom btn-reservar" type="button" data-add-cart data-id="<?= (int) $producto['id'] ?>" data-name="<?= e($nombreProducto) ?>" data-price="<?= $precioMostrar ?>">Reservar</button>
                  <?php else: ?>
                    <button class="btn-primary-custom" type="button" data-add-cart data-id="<?= (int) $producto['id'] ?>" data-name="<?= e($nombreProducto) ?>" data-price="<?= $precioMostrar ?>">Comprar</button>
                  <?php endif; ?>
                  <button class="icon-btn" type="button" data-view-product aria-label="Ver detalle del producto">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M3 11.5C4.8 8.3 8.1 6 12 6c3.9 0 7.2 2.3 9 5.5-1.8 3.2-5.1 5.5-9 5.5-3.9 0-7.2-2.3-9-5.5z"/>
                      <circle cx="12" cy="11.5" r="2.8"/>
                    </svg>
                  </button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>

  <div class="overlay" id="overlay"></div>
  <button class="cart-toggle" id="cartToggle">🛒 Carrito <span class="cart-count" id="cartCount">0</span></button>

  <aside class="cart-panel" id="cartPanel">
    <div class="cart-header"><h3>Tu carrito</h3><button class="btn-outline" type="button" id="closeCart">Cerrar</button></div>
    <div class="cart-items" id="cartItems"><div class="empty-state">Aún no has agregado productos.</div></div>
    <div class="cart-footer">
      <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">$0</span></div>
      <div class="summary-row"><span>Descuentos</span><span id="cartDiscount">$0</span></div>
      <div class="summary-row"><span>Total</span><span id="cartTotal">$0</span></div>
      <button class="btn-primary-custom" type="button" id="openCheckout">Finalizar compra</button>
      <button class="btn-outline" type="button" id="clearCart">Vaciar carrito</button>
    </div>
  </aside>

  <form id="checkoutPrepareForm" method="POST" action="<?= e(url('/catalogo/' . (int) $empresa['id'] . '/checkout/formulario')) ?>" class="d-none">
    <?= csrf_campo() ?>
    <input type="hidden" name="carrito_json" id="checkoutPrepareCarrito" value="[]">
  </form>

  <?php
    $catalogoFooterInicioUrl = '#catalogoProductos';
    $catalogoFooterProductosUrl = '#catalogoProductos';
    require __DIR__ . '/partials/catalogo_footer.php';
  ?>
</div>

<div class="modal fade" id="modalProductoDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 shadow">
    <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <div class="modal-body pt-1">
      <div class="row g-3 g-lg-4 align-items-start">
        <div class="col-lg-6">
          <img id="detalleProductoImagen" src="" alt="Producto" class="w-100 rounded-3" style="max-height:360px;object-fit:cover;background:#f8fafc">
        </div>
        <div class="col-lg-6">
          <div class="small text-uppercase text-muted mb-2" id="detalleProductoCategoria"></div>
          <h3 class="h4 mb-2" id="detalleProductoNombre"></h3>
          <p class="text-muted mb-2" id="detalleProductoDescripcion"></p>
          <div class="alert alert-warning py-2 px-3 mb-3 d-none" id="detalleProductoProximoAviso"></div>
          <div class="d-flex justify-content-between align-items-center border rounded-3 p-3 bg-light">
            <div>
              <div class="small text-muted">Precio</div>
              <div class="h4 mb-0" id="detalleProductoPrecio"></div>
            </div>
            <button type="button" class="btn btn-primary px-4" id="detalleAgregarCarrito">Comprar</button>
          </div>
        </div>
      </div>
    </div>
  </div></div>
</div>

<script>
(() => {
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const money = (v) => `$${Math.round(Number(v || 0)).toLocaleString('es-CL')}`;
  const resumenTexto = (txt, max = 68) => {
    const limpio = String(txt || '').replace(/\s+/g, ' ').trim();
    if (limpio.length <= max) return limpio;
    return `${limpio.slice(0, max).trimEnd()}…`;
  };
  const storageKey = 'vextra_catalogo_carrito_<?= (int) $empresa['id'] ?>';

  const cards = $$('.product-card');
  const products = cards.map((card) => ({
    id: Number(card.dataset.id || 0),
    name: String(card.dataset.name || ''),
    price: Number(card.dataset.price || 0),
    category: String(card.dataset.category || 'Sin categoría'),
    description: String(card.dataset.description || ''),
    image: String(card.dataset.image || ''),
    stock: Number(card.dataset.stock || 0),
    onSale: String(card.dataset.onsale || '0') === '1',
    featured: String(card.dataset.onsale || '0') !== '1',
    oldPrice: Number(card.dataset.oldprice || 0),
    proximo: String(card.dataset.proximo || '0') === '1',
    proximoDias: Number(card.dataset.proximoDias || 0),
    el: card,
  }));

  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { cart = []; }
  cart = Array.isArray(cart) ? cart.map((item) => ({ ...item, oldPrice: Number(item.oldPrice || 0) })) : [];

  let selectedCategory = 'all';
  let onlyOffers = false;
  let onlyStock = false;

  const categoryFilter = $('#categoryFilter');
  const categoryButtons = $('#categoryButtons');
  const searchInput = $('#searchInput');
  const globalSearch = $('#globalSearch');
  const priceFilter = $('#priceFilter');
  const sortFilter = $('#sortFilter');
  const resultsInfo = $('#resultsInfo');
  const productsGrid = $('#productsGrid');
  const cartPanel = $('#cartPanel');
  const overlay = $('#overlay');
  const cartItems = $('#cartItems');
  const cartCount = $('#cartCount');
  const cartSubtotal = $('#cartSubtotal');
  const cartDiscount = $('#cartDiscount');
  const cartTotal = $('#cartTotal');
  const checkoutPrepareCarrito = $('#checkoutPrepareCarrito');
  const getModalProductoDetalle = () => {
    if (!window.bootstrap) return null;
    const modalEl = $('#modalProductoDetalle');
    if (!modalEl) return null;
    return bootstrap.Modal.getOrCreateInstance(modalEl);
  };
  const detalleNombre = $('#detalleProductoNombre');
  const detalleDescripcion = $('#detalleProductoDescripcion');
  const detalleCategoria = $('#detalleProductoCategoria');
  const detallePrecio = $('#detalleProductoPrecio');
  const detalleImagen = $('#detalleProductoImagen');
  const detalleProximoAviso = $('#detalleProductoProximoAviso');
  const detalleAgregarCarrito = $('#detalleAgregarCarrito');
  let productoSeleccionado = null;

  const categories = ['all', ...new Set(products.map((p) => p.category))];
  const categoriesSidebar = ['all', ...categories.filter((c) => c !== 'all').slice(0, 10)];
  const renderCategories = () => {
    categoryFilter.innerHTML = categories.map((c) => `<option value="${c}">${c === 'all' ? 'Todas' : c}</option>`).join('');
    categoryFilter.value = selectedCategory;
    const categoriasSidebarRender = categoriesSidebar.includes(selectedCategory) || selectedCategory === 'all'
      ? categoriesSidebar
      : [...categoriesSidebar, selectedCategory];
    categoryButtons.innerHTML = categoriasSidebarRender.map((c) => `<button type="button" class="${selectedCategory === c ? 'active' : ''}" data-category="${c}">${c === 'all' ? 'Todas las categorías' : c}</button>`).join('');
    $$('button[data-category]', categoryButtons).forEach((btn) => {
      btn.addEventListener('click', () => {
        selectedCategory = btn.dataset.category || 'all';
        renderCategories();
        applyFilters();
      });
    });
  };

  const applyFilters = () => {
    const term = (searchInput.value.trim() || globalSearch.value.trim()).toLowerCase();
    const priceRange = priceFilter.value;
    let visible = products.filter((p) => {
      const matchTerm = p.name.toLowerCase().includes(term) || p.category.toLowerCase().includes(term) || p.description.toLowerCase().includes(term);
      const matchCategory = selectedCategory === 'all' || p.category === selectedCategory;
      let matchPrice = true;
      if (priceRange !== 'all') {
        const [min, max] = priceRange.split('-').map(Number);
        matchPrice = p.price >= min && p.price <= max;
      }
      const matchOffers = !onlyOffers || p.onSale;
      const matchStock = !onlyStock || p.stock > 0;
      return matchTerm && matchCategory && matchPrice && matchOffers && matchStock;
    });

    const sort = sortFilter.value;
    if (sort === 'price-asc') visible.sort((a, b) => a.price - b.price);
    else if (sort === 'price-desc') visible.sort((a, b) => b.price - a.price);
    else if (sort === 'name-asc') visible.sort((a, b) => a.name.localeCompare(b.name));
    else visible.sort((a, b) => Number(b.featured) - Number(a.featured));

    products.forEach((p) => { p.el.style.display = 'none'; });
    visible.forEach((p) => {
      p.el.style.display = '';
      productsGrid && productsGrid.appendChild(p.el);
    });
    resultsInfo.textContent = visible.length ? `Mostrando ${visible.length} producto(s) disponibles.` : 'No hay resultados con los filtros actuales.';
  };

  const saveCart = () => localStorage.setItem(storageKey, JSON.stringify(cart));
  const renderCart = () => {
    const totalItems = cart.reduce((sum, i) => sum + Number(i.quantity || 0), 0);
    const subtotal = cart.reduce((sum, i) => sum + Number(i.price || 0) * Number(i.quantity || 0), 0);
    const descuentoTotal = cart.reduce((sum, i) => {
      const oldPrice = Number(i.oldPrice || 0);
      const price = Number(i.price || 0);
      const qty = Number(i.quantity || 0);
      if (oldPrice > price) {
        return sum + ((oldPrice - price) * qty);
      }
      return sum;
    }, 0);
    cartCount.textContent = String(totalItems);
    cartSubtotal.textContent = money(subtotal);
    if (cartDiscount) cartDiscount.textContent = `-${money(descuentoTotal)}`;
    cartTotal.textContent = money(subtotal);

    if (!cart.length) {
      cartItems.innerHTML = '<div class="empty-state">Aún no has agregado productos.</div>';
      if (checkoutPrepareCarrito) checkoutPrepareCarrito.value = '[]';
      if (cartDiscount) cartDiscount.textContent = '$0';
      saveCart();
      return;
    }

    cartItems.innerHTML = cart.map((item) => `
      <div class="cart-item">
        <img src="${item.image}" alt="${item.name}">
        <div>
          <h4>${item.name}</h4>
          <p>${money(item.price)} c/u ${Number(item.oldPrice || 0) > Number(item.price || 0) ? `<span class="text-decoration-line-through text-muted">${money(item.oldPrice)}</span>` : ''}</p>
          <div class="cart-item__desc">
            ${resumenTexto(item.description || 'Producto seleccionado')}
            ${Number(item.proximo ? 1 : 0) === 1 ? `<div class="text-warning-emphasis mt-1">Llegada estimada: ${Math.max(0, Number(item.proximoDias || 0))} día(s).</div>` : ''}
          </div>
          <div class="qty-controls">
            <button type="button" data-cart-minus="${item.id}">-</button>
            <span>${item.quantity}</span>
            <button type="button" data-cart-plus="${item.id}">+</button>
          </div>
        </div>
        <button class="btn-danger-soft" type="button" data-cart-remove="${item.id}">X</button>
      </div>
    `).join('');

    if (checkoutPrepareCarrito) checkoutPrepareCarrito.value = JSON.stringify(cart.map((i) => ({ producto_id: Number(i.id), cantidad: Number(i.quantity) })));
    saveCart();
  };

  const openCart = () => { cartPanel.classList.add('open'); overlay.classList.add('show'); };
  const closeCart = () => { cartPanel.classList.remove('open'); overlay.classList.remove('show'); };

  const addToCart = (id, fallback = null) => {
    const product = products.find((p) => p.id === id) || (fallback && Number(fallback.id || 0) > 0 ? {
      id: Number(fallback.id || 0),
      name: String(fallback.name || 'Producto'),
      price: Number(fallback.price || 0),
      description: String(fallback.description || ''),
      image: String(fallback.image || '<?= e(url('/img/placeholder-producto.svg')) ?>'),
      stock: 0,
      onSale: false,
      featured: false,
      oldPrice: 0,
      proximo: String(fallback.proximo || '0') === '1',
      proximoDias: Number(fallback.proximoDias || 0),
      el: null,
    } : null);
    if (!product) return;
    if (product.proximo) return;
    const ex = cart.find((i) => i.id === id);
    if (ex) ex.quantity += 1;
    else cart.push({ id: product.id, name: product.name, description: product.description, image: product.image, price: product.price, oldPrice: product.oldPrice, quantity: 1, proximo: product.proximo, proximoDias: product.proximoDias });
    renderCart();
    openCart();
  };

  const initSlider = () => {
    const slides = $$('.slide', $('#slider'));
    if (slides.length < 2) return;
    let idx = 0;
    window.setInterval(() => {
      slides[idx].classList.remove('active');
      idx = (idx + 1) % slides.length;
      slides[idx].classList.add('active');
    }, 9000);
  };
  const initHomeCarrusel = () => {
    const carrusel = $('#homeProductosCarrusel');
    if (!carrusel) return;
    const prevBtn = $('#homeCarouselPrev');
    const nextBtn = $('#homeCarouselNext');
    const mover = (delta) => carrusel.scrollBy({ left: delta, behavior: 'smooth' });
    prevBtn && prevBtn.addEventListener('click', () => mover(-280));
    nextBtn && nextBtn.addEventListener('click', () => mover(280));
    window.setInterval(() => {
      const max = carrusel.scrollWidth - carrusel.clientWidth;
      if (max <= 0) return;
      const siguiente = carrusel.scrollLeft + 260 >= max ? 0 : carrusel.scrollLeft + 260;
      carrusel.scrollTo({ left: siguiente, behavior: 'smooth' });
    }, 3200);
  };

  const openDetailById = (productId) => {
    productoSeleccionado = products.find((p) => p.id === Number(productId || 0)) || null;
    const modalProductoDetalle = getModalProductoDetalle();
    if (!productoSeleccionado || !modalProductoDetalle) return;
    detalleNombre.textContent = productoSeleccionado.name;
    detalleDescripcion.textContent = productoSeleccionado.description;
    detalleCategoria.textContent = productoSeleccionado.category;
    detallePrecio.textContent = money(productoSeleccionado.price);
    detalleImagen.src = productoSeleccionado.image;
    detalleImagen.alt = productoSeleccionado.name;
      if (productoSeleccionado.proximo) {
        if (detalleProximoAviso) {
          const dias = Math.max(0, Number(productoSeleccionado.proximoDias || 0));
          detalleProximoAviso.textContent = `Este producto llegará en ${dias} día(s). Puedes reservarlo ahora.`;
          detalleProximoAviso.classList.remove('d-none');
        }
      if (detalleAgregarCarrito) {
        detalleAgregarCarrito.textContent = 'Reservar';
        detalleAgregarCarrito.classList.remove('btn-primary');
        detalleAgregarCarrito.classList.add('btn-reservar');
      }
    } else {
      if (detalleProximoAviso) detalleProximoAviso.classList.add('d-none');
      if (detalleAgregarCarrito) {
        detalleAgregarCarrito.textContent = 'Comprar';
        detalleAgregarCarrito.classList.remove('btn-reservar');
        detalleAgregarCarrito.classList.add('btn-primary');
      }
    }
    modalProductoDetalle.show();
  };

  $$('.product-card').forEach((card) => {
    const viewBtn = $('[data-view-product]', card);
    viewBtn && viewBtn.addEventListener('click', (e) => { e.stopPropagation(); openDetailById(card.dataset.id); });
  });
  $$('[data-carousel-open]').forEach((btn) => {
    btn.addEventListener('click', () => openDetailById(btn.dataset.id));
  });
  document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('[data-add-cart]');
    if (!addBtn) return;
    e.preventDefault();
    e.stopPropagation();
    addToCart(Number(addBtn.dataset.id || 0), addBtn.dataset);
  });

  $('#detalleAgregarCarrito').addEventListener('click', () => {
    if (!productoSeleccionado) return;
    if (!productoSeleccionado.proximo) {
      addToCart(Number(productoSeleccionado.id));
    }
    const modalProductoDetalle = getModalProductoDetalle();
    modalProductoDetalle && modalProductoDetalle.hide();
  });

  document.addEventListener('click', (e) => {
    const minus = e.target.closest('[data-cart-minus]');
    const plus = e.target.closest('[data-cart-plus]');
    const remove = e.target.closest('[data-cart-remove]');
    if (minus) {
      const id = Number(minus.dataset.cartMinus || 0);
      cart = cart.map((i) => i.id === id ? { ...i, quantity: Math.max(1, i.quantity - 1) } : i);
      renderCart();
    }
    if (plus) {
      const id = Number(plus.dataset.cartPlus || 0);
      cart = cart.map((i) => i.id === id ? { ...i, quantity: i.quantity + 1 } : i);
      renderCart();
    }
    if (remove) {
      const id = Number(remove.dataset.cartRemove || 0);
      cart = cart.filter((i) => i.id !== id);
      renderCart();
    }
  });

  $('#showAllBtn').addEventListener('click', () => { onlyOffers = false; onlyStock = false; applyFilters(); });
  $('#showOffersBtn').addEventListener('click', () => { onlyOffers = true; onlyStock = false; applyFilters(); });
  $('#showStockBtn').addEventListener('click', () => { onlyStock = true; onlyOffers = false; applyFilters(); });
  $('#showCheapestBtn').addEventListener('click', () => { sortFilter.value = 'price-asc'; onlyOffers = false; onlyStock = false; applyFilters(); });
  $('#showExpensiveBtn').addEventListener('click', () => { sortFilter.value = 'price-desc'; onlyOffers = false; onlyStock = false; applyFilters(); });
  const featuredTop = $('#showFeaturedBtnTop'); if (featuredTop) featuredTop.addEventListener('click', (e) => { e.preventDefault(); sortFilter.value = 'featured'; onlyOffers = false; onlyStock = false; applyFilters(); document.getElementById('catalogoProductos')?.scrollIntoView({ behavior: 'smooth' }); });
  const offersTop = $('#showOffersBtnTop'); if (offersTop) offersTop.addEventListener('click', (e) => { e.preventDefault(); onlyOffers = true; onlyStock = false; applyFilters(); document.getElementById('catalogoProductos')?.scrollIntoView({ behavior: 'smooth' }); });
  const stockTop = $('#showStockBtnTop'); if (stockTop) stockTop.addEventListener('click', () => { onlyStock = true; applyFilters(); });

  searchInput.addEventListener('input', applyFilters);
  globalSearch.addEventListener('input', applyFilters);
  categoryFilter.addEventListener('change', (e) => { selectedCategory = e.target.value || 'all'; renderCategories(); applyFilters(); });
  priceFilter.addEventListener('change', applyFilters);
  sortFilter.addEventListener('change', applyFilters);
  $('#clearFilters').addEventListener('click', () => {
    searchInput.value = ''; globalSearch.value = ''; selectedCategory = 'all'; onlyOffers = false; onlyStock = false;
    categoryFilter.value = 'all'; priceFilter.value = 'all'; sortFilter.value = 'featured'; renderCategories(); applyFilters();
  });

  const searchForm = document.querySelector('.search-box');
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => { e.preventDefault(); applyFilters(); });
  }
  $('#searchBtn').addEventListener('click', (e) => { e.preventDefault(); applyFilters(); });
  $('#cartToggle').addEventListener('click', openCart);
  $('#openCartHeader').addEventListener('click', openCart);
  $('#closeCart').addEventListener('click', closeCart);
  overlay.addEventListener('click', closeCart);
  $('#clearCart').addEventListener('click', () => { cart = []; renderCart(); });
  $('#scrollTopBtn').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  $('#openCheckout').addEventListener('click', () => {
    if (!cart.length) return;
    if (checkoutPrepareCarrito) {
      checkoutPrepareCarrito.value = JSON.stringify(cart.map((i) => ({ producto_id: Number(i.id), cantidad: Number(i.quantity) })));
    }
    const form = $('#checkoutPrepareForm');
    if (form) form.submit();
  });

  renderCategories();
  applyFilters();
  renderCart();
  initSlider();
  initHomeCarrusel();
})();
</script>
