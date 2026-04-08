<?php

namespace Aplicacion\Middlewares;

class SuperAdminMiddleware
{
    public function manejar(): void
    {
        if (!tiene_rol('superadministrador')) {
            http_response_code(403);
            exit('Acceso restringido para superadministrador.');
        }
    }
}
