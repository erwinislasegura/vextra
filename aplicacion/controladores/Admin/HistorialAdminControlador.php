<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\LogAdministracion;
use Aplicacion\Nucleo\Controlador;

class HistorialAdminControlador extends Controlador
{
    public function index(): void
    {
        $logs = (new LogAdministracion())->listar(120);
        $this->vista('admin/historial/index', compact('logs'), 'admin');
    }
}
