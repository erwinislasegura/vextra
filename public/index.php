<?php

declare(strict_types=1);


if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return strpos($haystack, $needle) !== false;
    }
}


require_once __DIR__ . '/../aplicacion/nucleo/CargadorEntorno.php';
\Aplicacion\Nucleo\CargadorEntorno::cargar(__DIR__ . '/../.env');

$debug = filter_var((string) ($_ENV['APP_DEBUG'] ?? 'false'), FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}


$registrarErrorLocal = static function (string $mensaje): void {
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
    $rutaLog = __DIR__ . '/../logs/app.log';
    $dir = dirname($rutaLog);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $ok = @file_put_contents($rutaLog, $linea, FILE_APPEND);
    if ($ok === false) {
        @file_put_contents(sys_get_temp_dir() . '/vextra_app.log', $linea, FILE_APPEND);
    }
};

set_exception_handler(static function (Throwable $e) use ($debug, $registrarErrorLocal): void {
    $ref = uniqid('ERR-', true);
    $mensaje = '[' . $ref . '] [Vextra][Uncaught] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
    error_log($mensaje);
    $registrarErrorLocal($mensaje);
    http_response_code(500);

    if ($debug) {
        echo '<h1>Error 500</h1>';
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }

    $tipo = basename(str_replace('\\', '/', get_class($e)));
    $origen = basename($e->getFile()) . ':' . $e->getLine();
    echo 'Error interno del servidor. Ref: ' . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8')
        . ' | Tipo: ' . htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8')
        . ' | Origen: ' . htmlspecialchars($origen, ENT_QUOTES, 'UTF-8');
});

register_shutdown_function(static function () use ($debug, $registrarErrorLocal): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    if (!in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    $ref = uniqid('ERR-', true);
    $mensaje = '[' . $ref . '] [Vextra][Fatal] ' . $error['message'] . ' @ ' . $error['file'] . ':' . $error['line'];
    error_log($mensaje);
    $registrarErrorLocal($mensaje);
    http_response_code(500);

    if ($debug) {
        echo '<h1>Error fatal</h1>';
        echo '<pre>' . htmlspecialchars($error['message'] . ' @ ' . $error['file'] . ':' . $error['line'], ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }

    $origen = basename($error['file']) . ':' . $error['line'];
    echo 'Error interno del servidor. Ref: ' . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8')
        . ' | Tipo: FatalError'
        . ' | Origen: ' . htmlspecialchars($origen, ENT_QUOTES, 'UTF-8');
});

spl_autoload_register(function (string $clase): void {
    $prefijo = 'Aplicacion\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $relativa = str_replace('\\', '/', substr($clase, strlen($prefijo)));
    $ruta = __DIR__ . '/../aplicacion/' . $relativa . '.php';

    if (!is_file($ruta)) {
        $segmentos = explode('/', $relativa);
        $archivo = array_pop($segmentos);
        if (!empty($segmentos)) {
            $segmentos[0] = strtolower($segmentos[0]);
        }
        $ruta = __DIR__ . '/../aplicacion/' . (empty($segmentos) ? '' : implode('/', $segmentos) . '/') . $archivo . '.php';
    }

    if (is_file($ruta)) {
        require_once $ruta;
    }
});

require_once __DIR__ . '/../aplicacion/nucleo/App.php';

\Aplicacion\Nucleo\App::iniciar();
