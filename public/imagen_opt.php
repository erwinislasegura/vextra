<?php

declare(strict_types=1);

$imagenes = [
    'dashboard_inicio' => 'Dashboard - Inicio.png',
    'punto_venta' => 'Punto de venta.png',
    'movimientos_inventario' => 'Movimientos de inventario.png',
    'clientes' => 'Clientes.png',
    'cotizaciones_1' => 'Cotizaciones 1.png',
    'cotizaciones_2' => 'Cotizaciones 2.png',
    'cotizaciones_3' => 'Cotizaciones 3.png',
    'cotizaciones_4' => 'Cotizaciones 4.png',
    'cotizaciones_5' => 'Cotizaciones 5.png',
];

$clave = isset($_GET['k']) ? (string) $_GET['k'] : '';
if (!isset($imagenes[$clave])) {
    http_response_code(404);
    exit('Imagen no disponible');
}

$anchoMax = isset($_GET['w']) ? max(200, min(1600, (int) $_GET['w'])) : 1200;
$altoMax = isset($_GET['h']) ? max(200, min(1200, (int) $_GET['h'])) : 800;
$calidad = isset($_GET['q']) ? max(55, min(85, (int) $_GET['q'])) : 76;

$rutaOrigen = __DIR__ . '/../img/Captura Sistema/' . $imagenes[$clave];
if (!is_file($rutaOrigen)) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

$cacheDir = __DIR__ . '/assets/cache/landing';
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
    http_response_code(500);
    exit('No se pudo crear caché');
}

$nombreCache = sprintf('%s_%dx%d_q%d.jpg', $clave, $anchoMax, $altoMax, $calidad);
$rutaCache = $cacheDir . '/' . $nombreCache;

if (!is_file($rutaCache) || filemtime($rutaCache) < filemtime($rutaOrigen)) {
    $origen = @imagecreatefrompng($rutaOrigen);
    if (!$origen) {
        http_response_code(500);
        exit('No se pudo procesar imagen');
    }

    $ancho = imagesx($origen);
    $alto = imagesy($origen);
    $ratio = min($anchoMax / max(1, $ancho), $altoMax / max(1, $alto), 1);
    $nuevoAncho = max(1, (int) round($ancho * $ratio));
    $nuevoAlto = max(1, (int) round($alto * $ratio));

    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
    imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
    imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

    imagejpeg($destino, $rutaCache, $calidad);
    imagedestroy($destino);
    imagedestroy($origen);
}

$ultimaMod = gmdate('D, d M Y H:i:s', filemtime($rutaCache)) . ' GMT';
$etag = '"' . md5($rutaCache . '|' . filemtime($rutaCache) . '|' . filesize($rutaCache)) . '"';

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=2592000, immutable');
header('Last-Modified: ' . $ultimaMod);
header('ETag: ' . $etag);

if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag) ||
    (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && trim((string) $_SERVER['HTTP_IF_MODIFIED_SINCE']) === $ultimaMod)) {
    http_response_code(304);
    exit;
}

readfile($rutaCache);
