<?php
/**
 * Actualizador de panel comercial.
 * Uso: php scripts/actualizar_proyecto.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../aplicacion/nucleo/CargadorEntorno.php';

use Aplicacion\Nucleo\CargadorEntorno;

CargadorEntorno::cargar(__DIR__ . '/../.env');
$config = require __DIR__ . '/../configuracion/base_datos.php';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['puerto'], $config['nombre'], $config['charset']);
$sqlPath = __DIR__ . '/../base_datos/actualizaciones/actualizacion_mejora_panel_comercial.sql';
$logPath = __DIR__ . '/../base_datos/actualizaciones/actualizacion_mejora_panel_comercial.log';

if (!file_exists($sqlPath)) {
    fwrite(STDERR, "No se encontró el SQL de actualización: {$sqlPath}\n");
    exit(1);
}

try {
    $pdo = new PDO($dsn, $config['usuario'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "[INFO] Iniciando actualización de panel comercial...\n";
    echo "[INFO] Recomendación: respalda la base de datos antes de continuar.\n";

    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);

    $mensaje = sprintf("[%s] Actualización aplicada correctamente.%s", date('Y-m-d H:i:s'), PHP_EOL);
    file_put_contents($logPath, $mensaje, FILE_APPEND);

    echo "[OK] Actualización finalizada.\n";
    echo "[OK] Log: {$logPath}\n";
    echo "[OK] Si usas OPcache, limpia caché PHP y reinicia servicios web.\n";
} catch (Throwable $e) {
    file_put_contents($logPath, sprintf("[%s] ERROR: %s%s", date('Y-m-d H:i:s'), $e->getMessage(), PHP_EOL), FILE_APPEND);
    fwrite(STDERR, "[ERROR] Falló la actualización: {$e->getMessage()}\n");
    exit(1);
}
