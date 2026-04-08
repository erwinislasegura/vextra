<?php

namespace Aplicacion\Nucleo;

class Contenedor
{
    private array $instancias = [];

    public function set(string $clave, mixed $valor): void
    {
        $this->instancias[$clave] = $valor;
    }

    public function get(string $clave): mixed
    {
        return $this->instancias[$clave] ?? null;
    }
}
