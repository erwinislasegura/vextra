<?php
/**
 * Actualizador incremental del módulo POS.
 * Uso: php scripts/actualizar_pos.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../aplicacion/nucleo/CargadorEntorno.php';

use Aplicacion\Nucleo\CargadorEntorno;

CargadorEntorno::cargar(__DIR__ . '/../.env');
$config = require __DIR__ . '/../configuracion/base_datos.php';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['puerto'], $config['nombre'], $config['charset']);
$sqlPath = __DIR__ . '/../base_datos/actualizaciones/actualizacion_pos_comercial.sql';
$logPath = __DIR__ . '/../base_datos/actualizaciones/actualizacion_pos_comercial.log';

if (!file_exists($sqlPath)) {
    fwrite(STDERR, "No se encontró el SQL de actualización POS: {$sqlPath}\n");
    exit(1);
}

try {
    $pdo = new PDO($dsn, $config['usuario'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "[INFO] Iniciando actualización del módulo POS...\n";
    echo "[INFO] Recomendación: respalda la base de datos antes de continuar.\n";

    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);

    $mensaje = sprintf("[%s] Actualización POS aplicada correctamente.%s", date('Y-m-d H:i:s'), PHP_EOL);
    file_put_contents($logPath, $mensaje, FILE_APPEND);

    echo "[OK] Actualización POS finalizada.\n";
    echo "[OK] Log: {$logPath}\n";
} catch (Throwable $e) {
    file_put_contents($logPath, sprintf("[%s] ERROR: %s%s", date('Y-m-d H:i:s'), $e->getMessage(), PHP_EOL), FILE_APPEND);
    fwrite(STDERR, "[ERROR] Falló la actualización POS: {$e->getMessage()}\n");
    exit(1);
}
