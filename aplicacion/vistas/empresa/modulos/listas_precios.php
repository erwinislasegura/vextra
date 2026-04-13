<?php

use Aplicacion\Servicios\ExcelExpoFormato;

require __DIR__ . '/_tabla_simple.php';
?>

<div class="alert alert-info info-modulo mb-3">
    <h2 class="h6 mb-2">¿Cómo usar listas de precios para productos y futuras cotizaciones?</h2>
    <ul class="mb-0 small">
        <li>Usa un <strong>nombre descriptivo</strong> para identificar rápido cuándo aplicar la lista.</li>
        <li>Selecciona <strong>canal de venta</strong> para separar precios de tienda física, delivery o e-commerce.</li>
        <li>Define <strong>vigencias</strong> para controlar promociones y evitar usar precios fuera de fecha.</li>
        <li>En <strong>Reglas base</strong> documenta reglas por producto (SKU/categoría) para reutilizarlas en cotizaciones.</li>
    </ul>
</div>

<?php
$formulario = '
<div class="col-md-3">
    <label class="form-label">Nombre</label>
    <input name="nombre" class="form-control" required placeholder="Ej: Mayorista 2026">
</div>
<div class="col-md-2">
    <label class="form-label">Vigencia desde</label>
    <input type="date" name="vigencia_desde" class="form-control">
</div>
<div class="col-md-2">
    <label class="form-label">Vigencia hasta</label>
    <input type="date" name="vigencia_hasta" class="form-control">
</div>
<div class="col-md-2">
    <label class="form-label">Tipo de lista</label>
    <select name="tipo_lista" class="form-select">
        <option value="general">General</option>
        <option value="cliente">Cliente</option>
        <option value="canal">Canal</option>
        <option value="volumen">Volumen (escalonado)</option>
    </select>
    <div class="form-text">Usa "Volumen" para activar descuentos por tramos de cantidad.</div>
</div>
<div class="col-md-2">
    <label class="form-label">Canal de venta</label>
    <select name="canal_venta" class="form-select">
        <option value="">Todos</option>
        <option value="local">Local</option>
        <option value="delivery">Delivery</option>
        <option value="ecommerce">E-commerce</option>
    </select>
    <div class="form-text">Si aplica en todos los canales, deja "Todos".</div>
</div>
<div class="col-md-3">
    <label class="form-label">Estado de la lista</label>
    <select name="estado" class="form-select">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
    </select>
</div>
<div class="col-md-2">
    <label class="form-label">Tipo de ajuste</label>
    <select name="ajuste_tipo" class="form-select">
        <option value="incremento">Incremento</option>
        <option value="descuento">Descuento</option>
    </select>
</div>
<div class="col-md-2">
    <label class="form-label">Porcentaje de ajuste (%)</label>
    <input type="number" min="0" step="1" name="ajuste_porcentaje" class="form-control" value="0">
    <div class="form-text">Ingresa solo enteros. Ejemplo: 10 = 10%.</div>
</div>
<div class="col-12">
    <label class="form-label">Reglas base (recomendado para productos y cotizaciones)</label>
    <textarea name="reglas_base" class="form-control" rows="5" placeholder="Ejemplo recomendado:\n- ALCANCE: categoria=electrónica\n- AJUSTE: +8% sobre precio base\n- DESCUENTO: 3% por cantidad > 20\n- TRAMOS VOLUMEN (tipo=volumen): 10:15%, 50:20%\n- OBS: aplicar en cotizaciones B2B"></textarea>
    <div class="form-text">Para tipo "Volumen", usa formato de tramos en texto: <code>10:15%, 50:20%</code>.</div>
</div>';

$registrosListado = array_map(static function (array $fila): array {
    if (isset($fila['ajuste_porcentaje']) && $fila['ajuste_porcentaje'] !== '') {
        $fila['ajuste_porcentaje'] = (string) round((float) $fila['ajuste_porcentaje']) . '%';
    }

    return $fila;
}, $registros);

$accionesListado = sprintf(
    '<a href="%s" class="%s" style="%s">%s</a>',
    e(url('/app/listas-precios/exportar/excel?q=' . urlencode($buscar))),
    e(ExcelExpoFormato::BOTON_CLASES),
    e(ExcelExpoFormato::BOTON_ESTILO),
    e(ExcelExpoFormato::BOTON_TEXTO)
);

render_modulo_simple(
    'Listas de precios',
    '/app/listas-precios',
    ['nombre', 'vigencia_desde', 'vigencia_hasta', 'tipo_lista', 'canal_venta', 'ajuste_tipo', 'ajuste_porcentaje', 'estado'],
    $registrosListado,
    $formulario,
    $buscar,
    $accionesListado,
    'Listas de precios configuradas'
);
?>
