<?php

namespace Aplicacion\Nucleo;

class CargadorEntorno
{
    public static function cargar(string $ruta): void
    {
        if (!is_file($ruta)) {
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }
            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);
            if (!array_key_exists($clave, $_ENV)) {
                $_ENV[$clave] = $valor;
            }
        }
    }
}
