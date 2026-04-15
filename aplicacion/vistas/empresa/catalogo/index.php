<?php
$resolverImagenSlider = static function (?string $ruta): string {
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $ruta) === 1) {
        return $ruta;
    }

    $normalizada = str_replace('\\', '/', $ruta);
    $normalizada = preg_replace('#^https?://[^/]+#i', '', $normalizada) ?? $normalizada;
    $normalizada = preg_replace('#^/?public/#i', '/', $normalizada) ?? $normalizada;
    $normalizada = preg_replace('#^/?aplicacion/public/#i', '/', $normalizada) ?? $normalizada;

    if (str_starts_with($normalizada, 'uploads/')) {
        $normalizada = '/' . $normalizada;
    }
    if (str_contains($normalizada, '/uploads/')) {
        $partes = explode('/uploads/', $normalizada, 2);
        $normalizada = '/uploads/' . ($partes[1] ?? '');
    } elseif (str_contains($normalizada, 'catalogo_slider/')) {
        $partes = explode('catalogo_slider/', $normalizada, 2);
        $normalizada = '/uploads/catalogo_slider/' . ($partes[1] ?? '');
    }

    return url('/' . ltrim($normalizada, '/'));
};
?>

<section class="container-fluid px-0">
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h1 class="h4 mb-1">Catálogo en línea</h1>
      <p class="text-muted mb-0">Publica tus productos como landing profesional con filtros, carrito y checkout Flow.</p>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-uppercase text-muted small mb-2">Productos publicados</div>
          <div class="display-6 fw-bold"><?= (int) $publicados ?></div>
          <p class="small text-muted mt-2 mb-0">Recuerda marcar “Mostrar en catálogo en línea” en cada producto/servicio.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h6">URL pública de tu catálogo</h2>
          <div class="input-group mb-2">
            <input id="catalogoPublicoUrl" class="form-control" readonly value="<?= e($catalogoUrl) ?>">
            <button class="btn btn-outline-secondary" type="button" id="copiarCatalogoUrl">Copiar</button>
            <a class="btn btn-primary" target="_blank" rel="noopener" href="<?= e($catalogoUrl) ?>">Abrir catálogo</a>
          </div>
          <div class="small text-muted">Empresa: <strong><?= e((string) ($empresa['nombre_comercial'] ?? 'Mi empresa')) ?></strong></div>
          <hr>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/productos')) ?>">Ir a productos</a>
            <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/categorias')) ?>">Gestionar categorías</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mt-3">
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2 class="h5 mb-1">Personalización del catálogo público</h2>
      <p class="text-muted small mb-3">Configura el contenido de la barra superior y del slider destacado de tu catálogo en línea.</p>
      <form method="POST" action="<?= e(url('/app/catalogo-en-linea/configuracion')) ?>" enctype="multipart/form-data" class="row g-3">
        <?= csrf_campo() ?>
        <div class="col-12">
          <div class="border rounded-3 p-3">
            <h3 class="h6 mb-2">Barra superior</h3>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label">Texto principal superior</label>
                <input type="text" name="catalogo_topbar_texto" maxlength="220" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_topbar_texto'] ?? '')) ?>" placeholder="Ejemplo: Envíos a todo Chile • Garantía oficial • Atención personalizada">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">Facebook (URL)</label>
                <input type="url" name="catalogo_social_facebook" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_facebook'] ?? '')) ?>" placeholder="https://facebook.com/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">Instagram (URL)</label>
                <input type="url" name="catalogo_social_instagram" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_instagram'] ?? '')) ?>" placeholder="https://instagram.com/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">TikTok (URL)</label>
                <input type="url" name="catalogo_social_tiktok" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_tiktok'] ?? '')) ?>" placeholder="https://tiktok.com/@tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">LinkedIn (URL)</label>
                <input type="url" name="catalogo_social_linkedin" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_linkedin'] ?? '')) ?>" placeholder="https://linkedin.com/company/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">YouTube (URL)</label>
                <input type="url" name="catalogo_social_youtube" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_youtube'] ?? '')) ?>" placeholder="https://youtube.com/@tuempresa">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Color primario</label>
                <?php $colorPrimario = (string) ($sliderCatalogo['catalogo_color_primario'] ?? '#4632A8'); ?>
                <input id="catalogo_color_primario_picker" type="color" name="catalogo_color_primario_picker" class="form-control form-control-color w-100" value="<?= e($colorPrimario !== '' ? $colorPrimario : '#4632A8') ?>" title="Selecciona el color primario del catálogo">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Código color primario</label>
                <input type="text" name="catalogo_color_primario" maxlength="7" class="form-control" value="<?= e($colorPrimario) ?>" placeholder="#4632A8">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Color acento</label>
                <?php $colorAcento = (string) ($sliderCatalogo['catalogo_color_acento'] ?? '#5415B0'); ?>
                <input id="catalogo_color_acento_picker" type="color" name="catalogo_color_acento_picker" class="form-control form-control-color w-100" value="<?= e($colorAcento !== '' ? $colorAcento : '#5415B0') ?>" title="Selecciona el color de acento del catálogo">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Código color acento</label>
                <input type="text" name="catalogo_color_acento" maxlength="7" class="form-control" value="<?= e($colorAcento) ?>" placeholder="#5415B0">
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <hr class="my-1">
          <h3 class="h6 mb-2">Slider destacado</h3>
        </div>
        <div class="col-lg-5">
          <label class="form-label">Imagen principal (JPG, PNG o WEBP)</label>
          <input type="file" name="slider_imagen" accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Recomendado: 1600x500 px, peso menor a 1 MB.</div>
          <?php if (!empty($sliderCatalogo['slider_imagen'])): ?>
            <div class="mt-3">
              <img src="<?= e($resolverImagenSlider((string) $sliderCatalogo['slider_imagen'])) ?>" alt="Imagen actual slider" class="img-fluid rounded border">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="eliminar_slider_imagen" id="eliminar_slider_imagen" value="1">
                <label class="form-check-label" for="eliminar_slider_imagen">Eliminar imagen actual</label>
              </div>
            </div>
          <?php endif; ?>
          <hr>
          <label class="form-label">Imagen secundaria (JPG, PNG o WEBP)</label>
          <input type="file" name="slider_imagen_secundaria" accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Se usa en la segunda transición del slider.</div>
          <?php if (!empty($sliderCatalogo['slider_imagen_secundaria'])): ?>
            <div class="mt-3">
              <img src="<?= e($resolverImagenSlider((string) $sliderCatalogo['slider_imagen_secundaria'])) ?>" alt="Imagen secundaria slider" class="img-fluid rounded border">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="eliminar_slider_imagen_secundaria" id="eliminar_slider_imagen_secundaria" value="1">
                <label class="form-check-label" for="eliminar_slider_imagen_secundaria">Eliminar imagen secundaria</label>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7">
          <div class="mb-2">
            <label class="form-label">Título</label>
            <input type="text" name="slider_titulo" maxlength="120" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_titulo'] ?? '')) ?>" placeholder="Ejemplo: Promociones destacadas del mes">
          </div>
          <div class="mb-2">
            <label class="form-label">Bajada</label>
            <textarea name="slider_bajada" maxlength="220" class="form-control" rows="3" placeholder="Describe la campaña o el mensaje principal para tus clientes."><?= e((string) ($sliderCatalogo['slider_bajada'] ?? '')) ?></textarea>
          </div>
          <div class="row g-2">
            <div class="col-md-5">
              <label class="form-label">Texto botón</label>
              <input type="text" name="slider_boton_texto" maxlength="60" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_boton_texto'] ?? '')) ?>" placeholder="Comprar ahora">
            </div>
            <div class="col-md-7">
              <label class="form-label">URL botón (https://...)</label>
              <input type="url" name="slider_boton_url" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_boton_url'] ?? '')) ?>" placeholder="https://tuempresa.cl/promo">
            </div>
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary" type="submit">Guardar configuración del slider</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
(() => {
  const btn = document.getElementById('copiarCatalogoUrl');
  const input = document.getElementById('catalogoPublicoUrl');
  if (!btn || !input) return;
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(input.value);
      btn.textContent = 'Copiado';
      setTimeout(() => { btn.textContent = 'Copiar'; }, 1300);
    } catch (e) {
      input.select();
      document.execCommand('copy');
      btn.textContent = 'Copiado';
      setTimeout(() => { btn.textContent = 'Copiar'; }, 1300);
    }
  });

  const colorPrimario = document.querySelector('input[name="catalogo_color_primario"]');
  const colorPrimarioPicker = document.getElementById('catalogo_color_primario_picker');
  const colorAcento = document.querySelector('input[name="catalogo_color_acento"]');
  const colorAcentoPicker = document.getElementById('catalogo_color_acento_picker');

  if (colorPrimario && colorPrimarioPicker) {
    colorPrimarioPicker.addEventListener('input', () => { colorPrimario.value = colorPrimarioPicker.value.toUpperCase(); });
    colorPrimario.addEventListener('input', () => {
      if (/^#([0-9A-Fa-f]{6})$/.test(colorPrimario.value)) colorPrimarioPicker.value = colorPrimario.value;
    });
  }
  if (colorAcento && colorAcentoPicker) {
    colorAcentoPicker.addEventListener('input', () => { colorAcento.value = colorAcentoPicker.value.toUpperCase(); });
    colorAcento.addEventListener('input', () => {
      if (/^#([0-9A-Fa-f]{6})$/.test(colorAcento.value)) colorAcentoPicker.value = colorAcento.value;
    });
  }
})();
</script>
