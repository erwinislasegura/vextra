<?php

namespace Aplicacion\Middlewares;

class AutenticadoMiddleware
{
    public function manejar(): void
    {
        if (!usuario_actual()) {
            header('Location: ' . url('/iniciar-sesion')); 
            exit;
        }
    }
}
