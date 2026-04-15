<?php

use Aplicacion\Controladores\Publico\PublicoControlador;
use Aplicacion\Controladores\Integraciones\FlowWebhookControlador;


$enrutador->agregar('GET', '/', [PublicoControlador::class, 'inicio']);
$enrutador->agregar('GET', '/caracteristicas', [PublicoControlador::class, 'caracteristicas']);
$enrutador->agregar('GET', '/planes', [PublicoControlador::class, 'planes']);
$enrutador->agregar('GET', '/contacto', [PublicoControlador::class, 'contacto']);
$enrutador->agregar('GET', '/preguntas-frecuentes', [PublicoControlador::class, 'preguntasFrecuentes']);
$enrutador->agregar('GET', '/imagen-opt/{clave}', [PublicoControlador::class, 'imagenOptimizada']);
$enrutador->agregar('GET', '/sitemap.xml', [PublicoControlador::class, 'sitemapXml']);
$enrutador->agregar('GET', '/robots.txt', [PublicoControlador::class, 'robotsTxt']);
$enrutador->agregar('POST', '/contacto', [PublicoControlador::class, 'enviarContacto']);
$enrutador->agregar('GET', '/contratar/{plan}', [PublicoControlador::class, 'contratar']);
$enrutador->agregar('GET', '/cotizacion/publica/{token}', [PublicoControlador::class, 'verCotizacionPublica']);
$enrutador->agregar('GET', '/cotizacion/publica/{token}/imprimir', [PublicoControlador::class, 'imprimirCotizacionPublica']);
$enrutador->agregar('POST', '/cotizacion/publica/{token}/decision', [PublicoControlador::class, 'registrarDecisionCotizacion']);
$enrutador->agregar('GET', '/orden-compra/publica/{token}', [PublicoControlador::class, 'verOrdenCompraPublica']);
$enrutador->agregar('GET', '/orden-compra/publica/{token}/imprimir', [PublicoControlador::class, 'imprimirOrdenCompraPublica']);
$enrutador->agregar('GET', '/media/producto/{id}', [PublicoControlador::class, 'imagenProducto']);
$enrutador->agregar('GET', '/media/archivo', [PublicoControlador::class, 'mediaArchivo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}', [PublicoControlador::class, 'catalogoEnLinea']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/nosotros', [PublicoControlador::class, 'catalogoNosotros']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/contacto', [PublicoControlador::class, 'catalogoContacto']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/contacto', [PublicoControlador::class, 'enviarContactoCatalogo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/logo', [PublicoControlador::class, 'logoCatalogoEmpresa']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/slider/{tipo}', [PublicoControlador::class, 'sliderCatalogoImagen']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/nosotros/imagen', [PublicoControlador::class, 'imagenCatalogoNosotros']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/nosotros/banner', [PublicoControlador::class, 'imagenCatalogoNosotrosBanner']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/producto/{productoId}/imagen', [PublicoControlador::class, 'imagenCatalogoProducto']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/checkout/formulario', [PublicoControlador::class, 'prepararCheckoutCatalogo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/checkout', [PublicoControlador::class, 'formularioCheckoutCatalogo']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/checkout', [PublicoControlador::class, 'checkoutCatalogo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/checkout/exito', [PublicoControlador::class, 'exitoCheckoutCatalogo']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/checkout/exito', [PublicoControlador::class, 'exitoCheckoutCatalogo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/checkout/exito/', [PublicoControlador::class, 'exitoCheckoutCatalogo']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/checkout/exito/', [PublicoControlador::class, 'exitoCheckoutCatalogo']);
$enrutador->agregar('GET', '/catalogo/{empresaId}/checkout/rechazado', [PublicoControlador::class, 'rechazoCheckoutCatalogo']);
$enrutador->agregar('POST', '/catalogo/{empresaId}/checkout/rechazado', [PublicoControlador::class, 'rechazoCheckoutCatalogo']);

$enrutador->agregar('POST', '/flow/webhook/payment-confirmation', [FlowWebhookControlador::class, 'confirmacionPago']);
$enrutador->agregar('POST', '/flow/webhook/subscription', [FlowWebhookControlador::class, 'callbackSuscripcion']);
$enrutador->agregar('POST', '/flow/webhook/card-register', [FlowWebhookControlador::class, 'callbackRegistroTarjeta']);
$enrutador->agregar('GET', '/flow/retorno/pago', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('POST', '/flow/retorno/pago', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('GET', '/flow/retorno/pago/', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('POST', '/flow/retorno/pago/', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('GET', '/flow/retorno/pago/estado', [FlowWebhookControlador::class, 'estadoRetornoPago']);
$enrutador->agregar('POST', '/flow/retorno/pago/estado', [FlowWebhookControlador::class, 'estadoRetornoPago']);
$enrutador->agregar('GET', '/flow/retorno/pago/no-confirmado', [FlowWebhookControlador::class, 'retornoPagoNoConfirmado']);
$enrutador->agregar('GET', '/flow/retorno/registro', [FlowWebhookControlador::class, 'retornoRegistroTarjeta']);
$enrutador->agregar('POST', '/flow/retorno/registro', [FlowWebhookControlador::class, 'retornoRegistroTarjeta']);

// Rutas espejo para entornos donde el prefijo /flow puede ser absorbido por reglas del hosting/rewrite.
$enrutador->agregar('POST', '/webhook/payment-confirmation', [FlowWebhookControlador::class, 'confirmacionPago']);
$enrutador->agregar('POST', '/webhook/subscription', [FlowWebhookControlador::class, 'callbackSuscripcion']);
$enrutador->agregar('POST', '/webhook/card-register', [FlowWebhookControlador::class, 'callbackRegistroTarjeta']);
$enrutador->agregar('GET', '/retorno/pago', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('POST', '/retorno/pago', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('GET', '/retorno/pago/', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('POST', '/retorno/pago/', [FlowWebhookControlador::class, 'retornoPago']);
$enrutador->agregar('GET', '/retorno/pago/estado', [FlowWebhookControlador::class, 'estadoRetornoPago']);
$enrutador->agregar('POST', '/retorno/pago/estado', [FlowWebhookControlador::class, 'estadoRetornoPago']);
$enrutador->agregar('GET', '/retorno/pago/no-confirmado', [FlowWebhookControlador::class, 'retornoPagoNoConfirmado']);
$enrutador->agregar('GET', '/retorno/registro', [FlowWebhookControlador::class, 'retornoRegistroTarjeta']);
$enrutador->agregar('POST', '/retorno/registro', [FlowWebhookControlador::class, 'retornoRegistroTarjeta']);
