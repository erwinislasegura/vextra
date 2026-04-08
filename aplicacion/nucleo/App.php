<?php

namespace Aplicacion\Nucleo;

class App
{
    public static function iniciar(): void
    {
        CargadorEntorno::cargar(__DIR__ . '/../../.env');
        $config = require __DIR__ . '/../../configuracion/aplicacion.php';
        date_default_timezone_set($config['zona_horaria']);

        require_once __DIR__ . '/../ayudantes/funciones.php';
        iniciar_sesion_segura($config['sesion_nombre']);

        $enrutador = new Enrutador();
        require __DIR__ . '/../../rutas/web.php';
        require __DIR__ . '/../../rutas/autenticacion.php';
        require __DIR__ . '/../../rutas/admin.php';
        require __DIR__ . '/../../rutas/empresa.php';

        $uri = self::normalizarUri((string) ($_SERVER['REQUEST_URI'] ?? '/'));
        $enrutador->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri);
    }

    private static function normalizarUri(string $requestUri): string
    {
        $uri = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $scriptParentDir = str_replace('\\', '/', dirname($scriptDir));
        $appUrlPath = parse_url((string) ($_ENV['APP_URL'] ?? ''), PHP_URL_PATH) ?: '';
        $appUrlBase = rtrim(str_replace('\\', '/', $appUrlPath), '/');

        // Soporta instalación en subcarpetas como /cotiza y /cotiza/public
        foreach ([$scriptDir, $scriptParentDir, $appUrlBase] as $base) {
            if ($base !== '/' && $base !== '.' && $base !== '' && str_starts_with($uri, $base)) {
                $uri = substr($uri, strlen($base)) ?: '/';
                break;
            }
        }

        // Cuando Apache reescribe a /public/index.php o /index.php
        if ($uri === '/public/index.php' || $uri === '/index.php') {
            $uri = '/';
        }
        if (str_starts_with($uri, '/public/')) {
            $uri = substr($uri, strlen('/public')) ?: '/';
        }

        // Normaliza slash final para evitar 404 entre "/ruta" y "/ruta/".
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
            if ($uri === '') {
                $uri = '/';
            }
        }

        return $uri ?: '/';
    }
}
