<?php

use Aplicacion\Servicios\ExcelExpoFormato;

require __DIR__ . '/_tabla_simple.php';

$vendedor = null;
ob_start();
require __DIR__ . '/_formulario_vendedor.php';
$formularioVendedor = (string) ob_get_clean();

$accionesListado = sprintf(
    '<a href="%s" class="%s" style="%s">%s</a>',
    e(url('/app/vendedores/exportar/excel?q=' . urlencode($buscar))),
    e(ExcelExpoFormato::BOTON_CLASES),
    e(ExcelExpoFormato::BOTON_ESTILO),
    e(ExcelExpoFormato::BOTON_TEXTO)
);

echo '<div class="alert alert-info info-modulo mb-3">'
    . '<div class="fw-semibold mb-1">Buenas prácticas para gestionar vendedores</div>'
    . '<ul class="mb-0 small ps-3">'
    . '<li>Asigna comisión y estado actual para reflejar correctamente el desempeño comercial.</li>'
    . '<li>Vincula usuario asociado para trazabilidad de acciones por vendedor.</li>'
    . '<li>Mantén correo y teléfono actualizados para coordinación con clientes.</li>'
    . '</ul>'
    . '</div>';

render_modulo_simple(
    'Vendedores',
    '/app/vendedores',
    ['nombre', 'correo', 'telefono', 'comision', 'estado', 'usuario_nombre'],
    $registros,
    $formularioVendedor,
    $buscar,
    $accionesListado
);
