<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\PlanFuncionalidad;

class ServicioPlan
{
    public function __construct(
        private ?Plan $planModelo = null,
        private ?PlanFuncionalidad $planFuncionalidadModelo = null
    ) {
        $this->planModelo ??= new Plan();
        $this->planFuncionalidadModelo ??= new PlanFuncionalidad();
    }

    public function obtenerRegla(int $planId, string $codigoFuncionalidad): ?array
    {
        return $this->planFuncionalidadModelo->obtenerPorPlanYCodigo($planId, $codigoFuncionalidad);
    }

    public function validarLimite(int $empresaId, string $codigoFuncionalidad, int $actual, string $mensaje): void
    {
        $plan = $this->planModelo->obtenerPlanActivoEmpresa($empresaId);
        if (!$plan) {
            throw new \RuntimeException('La empresa no tiene suscripción activa.');
        }

        $regla = $this->obtenerRegla((int) $plan['plan_id'], $codigoFuncionalidad);
        if (!$regla || !$regla['activo']) {
            throw new \RuntimeException('Tu plan no incluye esta funcionalidad.');
        }

        if (!$regla['es_ilimitado'] && (int) $regla['valor_numerico'] <= $actual) {
            throw new \RuntimeException($mensaje);
        }
    }
}
