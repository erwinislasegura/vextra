<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Pago;

class ServicioPagos
{
    public function registrarPago(array $datos): int
    {
        $pago = new Pago();
        return $pago->crear($datos);
    }

    public function procesarWebhook(array $payload): void
    {
        // Punto de extensión para pasarela real.
        // Debe validar firma y actualizar el estado de pagos/suscripciones.
    }
}
