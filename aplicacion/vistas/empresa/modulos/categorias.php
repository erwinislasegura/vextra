<?php

use Aplicacion\Servicios\ExcelExpoFormato;

require __DIR__ . '/_tabla_simple.php';

$formularioCategorias = <<<'HTML'
<div class="col-md-5">
    <label class="form-label" for="categoria_nombre">Nombre</label>
    <input id="categoria_nombre" name="nombre" class="form-control" maxlength="120" placeholder="Ej. Electrónica" required>
</div>
<div class="col-md-5">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Describe brevemente esta categoría"></textarea>
</div>
<div class="col-md-2">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
    </select>
</div>
<div class="col-12">
    <small class="text-muted">Usa categorías claras para facilitar la búsqueda y reportes de productos.</small>
</div>
HTML;

$accionesListado = sprintf(
    '<a href="%s" class="%s" style="%s">%s</a>',
    e(url('/app/categorias/exportar/excel?q=' . urlencode($buscar))),
    e(ExcelExpoFormato::BOTON_CLASES),
    e(ExcelExpoFormato::BOTON_ESTILO),
    e(ExcelExpoFormato::BOTON_TEXTO)
);

echo '<div class="alert alert-info info-modulo mb-3">'
    . '<div class="fw-semibold mb-1">Uso recomendado de categorías</div>'
    . '<ul class="mb-0 small ps-3">'
    . '<li>Define categorías por línea de negocio para mejorar orden y búsquedas.</li>'
    . '<li>Usa descripciones cortas para alinear criterios entre ventas y operación.</li>'
    . '<li>Mantén inactivas las categorías obsoletas para no perder historial.</li>'
    . '</ul>'
    . '</div>';

render_modulo_simple(
    'Categorías',
    '/app/categorias',
    ['nombre', 'descripcion', 'estado'],
    $registros,
    $formularioCategorias,
    $buscar,
    $accionesListado
);
