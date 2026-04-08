<?php

namespace Aplicacion\Nucleo;

use PDO;
use PDOException;

class BaseDatos
{
    private static ?PDO $conexion = null;

    public static function obtener(): PDO
    {
        if (self::$conexion) {
            return self::$conexion;
        }

        $config = require __DIR__ . '/../../configuracion/base_datos.php';
        $dsn = '';
        $socket = (string) ($config['socket'] ?? '');
        if ($socket !== '') {
            $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $socket, $config['nombre'], $config['charset']);
        } else {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['puerto'], $config['nombre'], $config['charset']);
        }

        try {
            self::$conexion = new PDO($dsn, $config['usuario'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $debug = filter_var((string) ($_ENV['APP_DEBUG'] ?? 'false'), FILTER_VALIDATE_BOOLEAN);
            $ref = uniqid('DB-', true);
            $mensaje = '[' . $ref . '] [Vextra][DB] ' . $e->getMessage();
            error_log($mensaje);

            $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
            $rutaLog = __DIR__ . '/../../logs/app.log';
            $dirLog = dirname($rutaLog);
            if (!is_dir($dirLog)) {
                @mkdir($dirLog, 0775, true);
            }
            $ok = @file_put_contents($rutaLog, $linea, FILE_APPEND);
            if ($ok === false) {
                @file_put_contents(sys_get_temp_dir() . '/cotizapro_app.log', $linea, FILE_APPEND);
            }

            http_response_code(500);
            if ($debug) {
                echo 'Error de conexión a base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            } else {
                echo 'Error de conexión a base de datos. Ref: ' . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8')
                    . ' | SQLSTATE: ' . htmlspecialchars((string) $e->getCode(), ENT_QUOTES, 'UTF-8');
            }
            exit;
        }

        return self::$conexion;
    }
}
