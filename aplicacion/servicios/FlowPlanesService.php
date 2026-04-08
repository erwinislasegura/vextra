<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\FlowPlan;
use Aplicacion\Modelos\Plan;

class FlowPlanesService
{
    public function __construct(
        private readonly FlowApiService $api = new FlowApiService(),
        private readonly FlowLogService $log = new FlowLogService(),
    ) {}

    public function crearOActualizarPlan(int $planId, string $modalidad, ?int $diasPrueba = null, ?int $diasVencimiento = null): array
    {
        $plan = (new Plan())->buscar($planId);
        if (!$plan) {
            throw new \RuntimeException('Plan local no encontrado.');
        }

        $monto = $modalidad === 'anual' ? (float) $plan['precio_anual'] : (float) $plan['precio_mensual'];
        $flowPlanId = strtoupper((string) $plan['slug']) . '_' . strtoupper($modalidad);
        $interval = $modalidad === 'anual' ? 4 : 3;
        $existente = (new FlowPlan())->buscarPorPlanYModalidad($planId, $modalidad);
        $diasPrueba = $diasPrueba ?? (int) ($plan['flow_dias_prueba'] ?? ($existente['dias_trial'] ?? 0));
        $diasVencimiento = $diasVencimiento ?? (int) ($plan['flow_dias_cobro'] ?? ($existente['dias_vencimiento'] ?? 3));
        $diasPrueba = max(0, $diasPrueba);
        $diasVencimiento = max(1, $diasVencimiento);
        $periodosNumero = $this->resolverPeriodosNumero($plan, $modalidad);
        $reintentosCobro = 3;
        $opcionConversionMoneda = 1;

        $payload = [
            'planId' => $flowPlanId,
            'name' => $plan['nombre'] . ' ' . ucfirst($modalidad),
            'currency' => 'CLP',
            'amount' => (int) $monto,
            'interval' => $interval,
            'interval_count' => 1,
            'trial_period_days' => $diasPrueba,
            'days_until_due' => $diasVencimiento,
            'periods_number' => $periodosNumero,
            'urlCallback' => FlowApiService::construirUrlPublica('/flow/webhook/subscription'),
            'charges_retries_number' => $reintentosCobro,
            'currency_convert_option' => $opcionConversionMoneda,
        ];

        $respuesta = $this->crearOActualizarPlanEnFlow($payload);

        (new FlowPlan())->guardar([
            'plan_id' => $planId,
            'modalidad' => $modalidad,
            'flow_plan_id' => $flowPlanId,
            'moneda' => 'CLP',
            'monto' => $monto,
            'intervalo' => $interval,
            'intervalo_cantidad' => 1,
            'dias_trial' => $diasPrueba,
            'dias_vencimiento' => $diasVencimiento,
            'periodos' => $periodosNumero,
            'estado_local' => 'activo',
            'estado_flow' => 'activo',
            'entorno_flow' => $this->api->configuracionActiva()['entorno'],
            'payload_request' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_response' => json_encode($respuesta, JSON_UNESCAPED_UNICODE),
        ]);

        $col = $modalidad === 'anual' ? 'flow_plan_id_anual' : 'flow_plan_id_mensual';
        $db = \Aplicacion\Nucleo\BaseDatos::obtener();
        $stmt = $db->prepare("UPDATE planes SET {$col} = :flow_plan_id, flow_sincronizado = 1, flow_ultima_sincronizacion = NOW(), fecha_actualizacion = NOW() WHERE id = :id");
        $stmt->execute(['flow_plan_id' => $flowPlanId, 'id' => $planId]);

        if (($respuesta['status'] ?? '') === 'already_exists') {
            $this->log->warning('plan', 'Plan ya existía en Flow y se vinculó localmente', $flowPlanId, null, $respuesta);
        } else {
            $this->log->info('plan', 'Plan sincronizado con Flow', $flowPlanId, null, $respuesta);
        }
        return $respuesta;
    }

    private function crearOActualizarPlanEnFlow(array $payload): array
    {
        try {
            return $this->api->post('plans/create', $payload);
        } catch (\Throwable $e) {
            $mensaje = $e->getMessage();
            if (stripos($mensaje, 'planId has already been used') === false) {
                throw $e;
            }

            // Cuando el plan ya existe en Flow, se intenta actualizarlo en distintos endpoints.
            foreach (['plans/update', 'plans/edit', 'plan/update'] as $endpoint) {
                try {
                    return $this->api->post($endpoint, $payload);
                } catch (\Throwable $updateError) {
                    // Intenta siguiente variante de endpoint.
                }
            }

            // Si no se pudo actualizar por endpoint no disponible, se vincula localmente para no bloquear la operación.
            return [
                'status' => 'already_exists',
                'planId' => (string) ($payload['planId'] ?? ''),
                'message' => 'El plan ya existía en Flow; no fue posible ejecutar update automático.',
            ];
        }
    }

    private function resolverPeriodosNumero(array $plan, string $modalidad): int
    {
        $duracionDias = (int) ($plan['duracion_dias'] ?? 0);
        if ($duracionDias <= 0) {
            return 0;
        }

        if ($modalidad === 'anual') {
            return max(1, (int) ceil($duracionDias / 365));
        }

        return max(1, (int) ceil($duracionDias / 30));
    }
}
