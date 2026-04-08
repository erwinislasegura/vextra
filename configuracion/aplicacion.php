<?php

return [
    'nombre' => $_ENV['APP_NOMBRE'] ?? 'CotizaPro',
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOL),
    'zona_horaria' => $_ENV['APP_ZONA_HORARIA'] ?? 'America/Bogota',
    'sesion_nombre' => $_ENV['SESION_NOMBRE'] ?? 'cotiza_sesion',
    'sesion_tiempo' => (int) ($_ENV['SESION_TIEMPO'] ?? 120),
];
