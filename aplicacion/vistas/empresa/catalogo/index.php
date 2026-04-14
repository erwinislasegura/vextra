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
      <h2 class="h5 mb-1">Slider destacado del catálogo público</h2>
      <p class="text-muted small mb-3">Este bloque se mostrará debajo de la barra superior del catálogo en línea.</p>
      <form method="POST" action="<?= e(url('/app/catalogo-en-linea/configuracion')) ?>" enctype="multipart/form-data" class="row g-3">
        <?= csrf_campo() ?>
        <div class="col-lg-5">
          <label class="form-label">Imagen (JPG, PNG o WEBP)</label>
          <input type="file" name="slider_imagen" accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Recomendado: 1600x500 px, peso menor a 1 MB.</div>
          <?php if (!empty($sliderCatalogo['slider_imagen'])): ?>
            <div class="mt-3">
              <img src="<?= e(url((string) $sliderCatalogo['slider_imagen'])) ?>" alt="Imagen actual slider" class="img-fluid rounded border">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="eliminar_slider_imagen" id="eliminar_slider_imagen" value="1">
                <label class="form-check-label" for="eliminar_slider_imagen">Eliminar imagen actual</label>
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
})();
</script>
