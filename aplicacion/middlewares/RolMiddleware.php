<?php

namespace Aplicacion\Middlewares;

class RolMiddleware
{
    public function __construct(private array $roles = []) {}

    public function manejar(): void
    {
        if (!usuario_actual()) {
            header('Location: ' . url('/iniciar-sesion')); 
            exit;
        }
        if ($this->roles && !tiene_rol($this->roles)) {
            http_response_code(403);
            exit('No autorizado');
        }
    }
}
