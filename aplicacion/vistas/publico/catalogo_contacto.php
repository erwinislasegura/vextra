<?php
$catalogoBaseUrl = url('/catalogo/' . (int) ($empresa['id'] ?? 0));
$catalogoNosotrosUrl = $catalogoBaseUrl . '/nosotros';
$accionFormulario = $catalogoBaseUrl . '/contacto';

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
$contactoTitulo = trim((string) ($catalogoTopbar['contacto_form_titulo'] ?? ''));
if ($contactoTitulo === '') {
    $contactoTitulo = 'Nos pondremos en contacto a la brevedad';
}
$contactoSubtitulo = trim((string) ($catalogoTopbar['contacto_form_subtitulo'] ?? ''));
if ($contactoSubtitulo === '') {
    $contactoSubtitulo = 'Enviar un mensaje';
}
$contactoBajada = trim((string) ($catalogoTopbar['contacto_form_bajada'] ?? ''));
if ($contactoBajada === '') {
    $contactoBajada = 'Déjanos tu consulta y te responderemos dentro de nuestro horario de atención.';
}
$textoBoton = trim((string) ($catalogoTopbar['contacto_form_texto_boton'] ?? ''));
if ($textoBoton === '') {
    $textoBoton = 'Enviar mensaje';
}

$sliderImagen = trim((string) ($sliderCatalogo['imagen'] ?? ''));
if ($sliderImagen === '') {
    $sliderImagen = url('/img/placeholder-producto.svg');
}

$mapaActivo = (string) ($catalogoTopbar['contacto_mapa_activo'] ?? '1') !== '0';
$mapaUrl = trim((string) ($catalogoTopbar['contacto_mapa_url'] ?? ''));
if ($mapaUrl === '') {
    $direccionMapa = trim((string) (($empresa['direccion'] ?? '') . ' ' . ($empresa['ciudad'] ?? '') . ' ' . ($empresa['pais'] ?? '')));
    if ($direccionMapa === '') {
        $direccionMapa = 'Santiago Chile';
    }
    $mapaUrl = 'https://maps.google.com/maps?q=' . rawurlencode($direccionMapa) . '&output=embed';
}
if (!str_contains($mapaUrl, 'output=embed') && str_contains($mapaUrl, 'maps.google.')) {
    if (str_contains($mapaUrl, '/maps?q=')) {
        $mapaUrl .= (str_contains($mapaUrl, '?') ? '&' : '?') . 'output=embed';
    } elseif (str_contains($mapaUrl, '/place/')) {
        $q = rawurlencode((string) preg_replace('#^.*?/place/#', '', $mapaUrl));
        $mapaUrl = 'https://maps.google.com/maps?q=' . $q . '&output=embed';
    }
}

$camposPermitidos = [
    'nombre' => ['label' => 'Nombre', 'placeholder' => 'Nombre', 'type' => 'text', 'required' => true],
    'telefono' => ['label' => 'Teléfono', 'placeholder' => 'Teléfono', 'type' => 'text', 'required' => false],
    'email' => ['label' => 'Email', 'placeholder' => 'Email', 'type' => 'email', 'required' => true],
    'asunto' => ['label' => 'Asunto', 'placeholder' => 'Asunto', 'type' => 'text', 'required' => false],
    'mensaje' => ['label' => 'Mensaje', 'placeholder' => 'Mensaje', 'type' => 'textarea', 'required' => true],
    'empresa' => ['label' => 'Empresa', 'placeholder' => 'Empresa', 'type' => 'text', 'required' => false],
    'whatsapp' => ['label' => 'WhatsApp', 'placeholder' => 'WhatsApp', 'type' => 'text', 'required' => false],
    'ciudad' => ['label' => 'Ciudad', 'placeholder' => 'Ciudad', 'type' => 'text', 'required' => false],
    'direccion' => ['label' => 'Dirección', 'placeholder' => 'Dirección', 'type' => 'text', 'required' => false],
    'cargo' => ['label' => 'Cargo / Rol', 'placeholder' => 'Cargo / Rol', 'type' => 'text', 'required' => false],
];
$camposActivos = json_decode((string) ($catalogoTopbar['contacto_form_campos'] ?? ''), true);
if (!is_array($camposActivos) || $camposActivos === []) {
    $camposActivos = ['nombre', 'telefono', 'email', 'asunto', 'mensaje'];
}
$camposActivos = array_values(array_filter(array_map(static fn($campo): string => trim((string) $campo), $camposActivos), static fn($campo): bool => isset($camposPermitidos[$campo])));
if (!in_array('nombre', $camposActivos, true)) $camposActivos[] = 'nombre';
if (!in_array('email', $camposActivos, true)) $camposActivos[] = 'email';
if (!in_array('mensaje', $camposActivos, true)) $camposActivos[] = 'mensaje';

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
  :root{--primary:<?= e($colorPrimario) ?>;--accent:<?= e($colorAcento) ?>;--bg:#eef2f7;--border:#dbe3ee;--text:#0f172a;--muted:#64748b;--shadow:0 10px 25px rgba(15,23,42,.08)}
  .catalogo-page{background:var(--bg)}
  .catalogo-container{width:min(1280px,92%);margin:0 auto}
  .catalogo-topbar{background:var(--primary);color:#fff;padding:8px 0;font-size:13px}
  .catalogo-topbar__content{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .catalogo-topbar__sociales{display:flex;align-items:center;gap:10px}
  .catalogo-topbar__sociales a{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:999px;border:1px solid rgba(255,255,255,.5);color:#fff;text-decoration:none}
  .catalogo-topbar__sociales a svg{width:14px;height:14px;fill:#fff}
  .catalogo-header{position:sticky;top:0;z-index:45;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
  .catalogo-navbar{display:grid;grid-template-columns:240px 1fr auto auto;gap:10px;align-items:center;padding:10px 0}
  .catalogo-logo img{width:120px;height:60px;object-fit:contain}
  .search-box{display:flex;align-items:center;background:#fff;border:1px solid var(--border);border-radius:999px;overflow:hidden}
  .search-box input{width:100%;padding:10px 14px;border:none;background:transparent}
  .search-box button{background:var(--accent);color:#fff;padding:10px 18px;font-weight:700;border:none}
  .btn-outline,.btn-primary-custom{padding:9px 13px;border-radius:10px;font-weight:700;border:1px solid var(--border);background:#fff;color:var(--text);text-decoration:none}
  .btn-primary-custom{background:var(--accent);border-color:var(--accent);color:#fff}

  .contact-hero{margin-top:10px;border-radius:18px;min-height:210px;display:flex;align-items:flex-end;padding:24px;background-size:cover;background-position:center;position:relative;overflow:hidden;box-shadow:var(--shadow)}
  .contact-hero::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(15,23,42,.65),rgba(15,23,42,.25))}
  .contact-hero h1{position:relative;color:#fff;font-size:42px;font-weight:800;margin:0}

  .contact-layout{padding:18px 0 24px}
  .contact-card{background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:24px;display:grid;grid-template-columns:360px 1fr;gap:30px}
  .contact-subtitle{font-family:Georgia,serif;font-style:italic;font-size:22px;color:var(--primary);margin-bottom:8px}
  .contact-title{font-size:56px;line-height:1.05;margin-bottom:12px;color:#312d34;font-weight:800}
  .contact-desc{font-size:18px;line-height:1.6;color:var(--muted)}
  .contact-follow{margin-top:24px}.contact-follow h4{font-size:20px;margin-bottom:10px}
  .contact-icons{display:flex;gap:10px}.contact-icons a{width:40px;height:40px;border:1px solid var(--border);border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:var(--primary);background:#fff}

  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .form-group label{display:block;font-weight:700;color:#334155;margin-bottom:5px}
  .form-group input,.form-group textarea{width:100%;border:1px solid var(--border);padding:12px 14px;font-size:16px;border-radius:8px;background:#fff}
  .form-group textarea{min-height:190px;resize:vertical}
  .form-group.full{grid-column:1 / -1}
  .btn-submit{margin-top:14px;background:var(--accent);border:none;color:#fff;padding:14px 30px;border-radius:10px;font-size:20px;font-weight:800;text-transform:uppercase}

  .map-wrap{margin:6px 0 0}.map-wrap iframe{width:100%;height:350px;border:0;display:block}

  .footer{position:relative;color:#fff;padding:30px 0 20px;background:linear-gradient(120deg,var(--primary),var(--accent));margin-top:18px}
  .footer-content{display:grid;grid-template-columns:1.1fr .9fr 1fr .9fr;gap:22px}
  .footer-col h4{font-size:18px;font-weight:600;margin:0 0 10px}
  .footer-brand img{width:128px;height:60px;object-fit:contain;background:#fff;border-radius:10px;padding:4px 8px;border:1px solid rgba(255,255,255,.35);margin-bottom:8px}
  .footer-brand p,.footer-contact p,.footer-menu a,.footer-follow p{font-size:13px;color:rgba(255,255,255,.92);margin:0}
  .footer-menu{display:grid;gap:8px}.footer-menu a{color:#fff;text-decoration:none}
  .footer-sociales{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
  .footer-sociales a{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:999px;border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08);color:#fff;text-decoration:none}
  .footer-sociales a svg{width:14px;height:14px;fill:#fff}
  @media (max-width:1100px){.catalogo-navbar,.contact-card,.form-grid,.footer-content{grid-template-columns:1fr}.contact-title{font-size:42px}}
</style>

<div class="catalogo-page">
  <div class="catalogo-topbar">
    <div class="catalogo-container catalogo-topbar__content">
      <div><?= e($topbarTexto) ?></div>
      <?php if ($socialesTopbar !== []): ?><div class="catalogo-topbar__sociales"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div><?php endif; ?>
    </div>
  </div>

  <header class="catalogo-header">
    <div class="catalogo-container catalogo-navbar">
      <a class="catalogo-logo" href="<?= e($catalogoBaseUrl) ?>"><img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa"></a>
      <div class="search-box"><input type="text" placeholder="Ir al catálogo para buscar productos..." disabled><button type="button" onclick="window.location.href='<?= e($catalogoBaseUrl) ?>'">Ir</button></div>
      <a class="btn-outline" href="<?= e($catalogoBaseUrl) ?>">Catálogo</a>
      <a class="btn-primary-custom" href="<?= e($catalogoNosotrosUrl) ?>">Nosotros</a>
    </div>
  </header>

  <section class="catalogo-container"><div class="contact-hero" style="background-image:url('<?= e($sliderImagen) ?>')"><h1>Contacto</h1></div></section>

  <section class="contact-layout">
    <div class="catalogo-container contact-card">
      <div>
        <div class="contact-subtitle"><?= e($contactoSubtitulo) ?></div>
        <h2 class="contact-title"><?= e($contactoTitulo) ?></h2>
        <p class="contact-desc"><?= nl2br(e($contactoBajada)) ?></p>
        <?php if ($socialesTopbar !== []): ?><div class="contact-follow"><h4>Síguenos:</h4><div class="contact-icons"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e((string) ($red['label'] ?? 'Red social')) ?>"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div></div><?php endif; ?>
      </div>
      <div>
        <form method="POST" action="<?= e($accionFormulario) ?>">
          <?= csrf_campo() ?>
          <div class="form-grid">
            <?php foreach ($camposActivos as $campoClave): $cfg = $camposPermitidos[$campoClave] ?? null; if ($cfg === null) continue; $esTextarea = $cfg['type'] === 'textarea'; ?>
              <div class="form-group <?= $esTextarea ? 'full' : '' ?>">
                <label for="campo_<?= e($campoClave) ?>"><?= e((string) $cfg['label']) ?><?= !empty($cfg['required']) ? ' *' : '' ?></label>
                <?php if ($esTextarea): ?><textarea id="campo_<?= e($campoClave) ?>" name="<?= e($campoClave) ?>" placeholder="<?= e((string) $cfg['placeholder']) ?>" <?= !empty($cfg['required']) ? 'required' : '' ?>></textarea>
                <?php else: ?><input id="campo_<?= e($campoClave) ?>" type="<?= e((string) $cfg['type']) ?>" name="<?= e($campoClave) ?>" placeholder="<?= e((string) $cfg['placeholder']) ?>" <?= !empty($cfg['required']) ? 'required' : '' ?>><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="btn-submit" type="submit"><?= e($textoBoton) ?></button>
        </form>
      </div>
    </div>
  </section>

  <?php if ($mapaActivo): ?><div class="map-wrap"><iframe src="<?= e($mapaUrl) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa de ubicación"></iframe></div><?php endif; ?>

  <footer class="footer">
    <div class="catalogo-container footer-content">
      <div class="footer-brand footer-col"><img src="<?= e((string) ($logoCatalogo ?: url('/img/logo/icono.png'))) ?>" alt="Logo empresa"><p><?= e((string) (($empresa['descripcion'] ?? '') !== '' ? $empresa['descripcion'] : 'Diseño profesional para mostrar y vender productos online.')) ?></p></div>
      <div class="footer-col"><h4>Accesos rápidos</h4><nav class="footer-menu mt-2"><a href="<?= e($catalogoBaseUrl) ?>">Inicio</a><a href="<?= e($catalogoBaseUrl) ?>/nosotros">Nosotros</a><a href="<?= e($catalogoBaseUrl) ?>/contacto">Contacto</a></nav></div>
      <div class="footer-contact footer-col"><h4>Datos de contacto</h4><p><?= e((string) ($empresa['telefono'] ?? 'No informado')) ?></p><p><?= e((string) ($empresa['correo'] ?? 'No informado')) ?></p><p><?= e((string) ($empresa['direccion'] ?? 'No informada')) ?></p></div>
      <div class="footer-follow footer-col"><h4>Síguenos</h4><p>Conéctate en redes sociales y conoce ofertas y productos destacados.</p><?php if ($socialesTopbar !== []): ?><div class="footer-sociales"><?php foreach ($socialesTopbar as $red): ?><a href="<?= e((string) $red['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $renderIconoRed((string) ($red['id'] ?? '')) ?></a><?php endforeach; ?></div><?php endif; ?></div>
    </div>
  </footer>
</div>
