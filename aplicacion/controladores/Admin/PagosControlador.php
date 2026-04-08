<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\Pago;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Nucleo\Controlador;

class PagosControlador extends Controlador
{
    public function index(): void
    {
        $filtros = [
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
            'estado' => $_GET['estado'] ?? '',
        ];
        $pagos = (new Pago())->listar($filtros);
        $suscripciones = (new Suscripcion())->listar();
        $planes = (new Plan())->listar();
        $this->vista('admin/pagos/index', compact('pagos', 'filtros', 'suscripciones', 'planes'), 'admin');
    }
}
