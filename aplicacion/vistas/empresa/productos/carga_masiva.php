<section class="modulo-head mb-3">
  <h1 class="h4 mb-1">Carga masiva de productos</h1>
  <p class="text-muted mb-0">Sube cientos o miles de productos/categorías en pocos minutos usando plantillas de Excel.</p>
</section>

<div class="alert alert-info">
  <strong>¿Cómo funciona?</strong>
  <ol class="mb-0 mt-2">
    <li>Descarga la plantilla de ejemplo (productos o categorías).</li>
    <li>Llénala en Excel respetando el orden de columnas.</li>
    <li>Guárdala y súbela en el mismo formato <strong>Excel (.xls)</strong> de la plantilla.</li>
    <li>Sube el archivo desde el formulario correspondiente.</li>
  </ol>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Carga masiva de productos</strong>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/productos/carga-masiva/plantilla/productos')) ?>">Descargar plantilla productos</a>
      </div>
      <div class="card-body">
        <p class="mb-2">Campos esperados (mismo orden de la plantilla):</p>
        <code>tipo, categoria, codigo, sku, codigo_barras, nombre, descripcion, unidad, precio, costo, impuesto, descuento_maximo, stock_minimo, stock_critico, stock_actual, estado</code>
        <ul class="mt-3 mb-3 small text-muted">
          <li><strong>Obligatorios:</strong> <code>codigo</code> y <code>nombre</code>.</li>
          <li><strong>tipo:</strong> producto o servicio.</li>
          <li><strong>estado:</strong> activo o inactivo.</li>
          <li>Si la <code>categoria</code> no existe, el sistema la crea automáticamente.</li>
        </ul>
        <form method="POST" action="<?= e(url('/app/productos/carga-masiva/productos')) ?>" enctype="multipart/form-data" class="row g-2">
          <?= csrf_campo() ?>
          <div class="col-md-9">
            <input type="file" name="archivo_productos" class="form-control" accept=".xls,.xlsx,.csv" required>
          </div>
          <div class="col-md-3 d-grid">
            <button class="btn btn-primary">Cargar productos</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Carga masiva de categorías</strong>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/productos/carga-masiva/plantilla/categorias')) ?>">Plantilla categorías</a>
      </div>
      <div class="card-body">
        <p class="mb-2">Campos esperados (mismo orden de la plantilla):</p>
        <code>nombre, descripcion, estado</code>
        <ul class="mt-3 mb-3 small text-muted">
          <li><strong>Obligatorio:</strong> <code>nombre</code>.</li>
          <li>Categorías repetidas se omiten automáticamente.</li>
        </ul>
        <form method="POST" action="<?= e(url('/app/productos/carga-masiva/categorias')) ?>" enctype="multipart/form-data" class="row g-2">
          <?= csrf_campo() ?>
          <div class="col-12">
            <input type="file" name="archivo_categorias" class="form-control" accept=".xls,.xlsx,.csv" required>
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-secondary">Cargar categorías</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
