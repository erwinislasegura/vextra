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
