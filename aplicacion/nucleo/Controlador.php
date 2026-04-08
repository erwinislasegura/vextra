<?php

namespace Aplicacion\Nucleo;

abstract class Controlador
{
    protected function vista(string $plantilla, array $datos = [], string $diseno = 'publico'): void
    {
        extract($datos, EXTR_OVERWRITE);
        $contenido = __DIR__ . '/../vistas/' . $plantilla . '.php';
        $disenoRuta = __DIR__ . '/../vistas/disenos/' . $diseno . '.php';
        require $disenoRuta;
    }

    protected function redirigir(string $ruta): void
    {
        $destino = preg_match('#^https?://#i', $ruta) ? $ruta : url($ruta);
        header('Location: ' . $destino);
        exit;
    }
}
