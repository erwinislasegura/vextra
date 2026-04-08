<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\FlowCliente;
use Aplicacion\Modelos\FlowPlan;
use Aplicacion\Modelos\FlowSuscripcion;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;

class FlowSuscripcionesService
{
    public function __construct(
        private readonly FlowApiService $api = new FlowApiService(),
        private readonly FlowLogService $log = new FlowLogService(),
    ) {}

    public function crearSuscripcion(int $empresaId, int $planId, string $tipoCobro = 'mensual', ?int $suscripcionLocalId = null): array
    {
        $cliente = (new FlowCliente())->buscarPorEmpresa($empresaId);
        if (!$cliente) {
            (new FlowClientesService($this->api, $this->log))->crearCliente($empresaId);
            $cliente = (new FlowCliente())->buscarPorEmpresa($empresaId);
        }
        if (!$cliente) {
            throw new \RuntimeException('Cliente Flow no disponible.');
        }

        $flowPlan = (new FlowPlan())->buscarPorPlanYModalidad($planId, $tipoCobro);
        if (!$flowPlan) {
            (new FlowPlanesService($this->api, $this->log))->crearOActualizarPlan($planId, $tipoCobro);
            $flowPlan = (new FlowPlan())->buscarPorPlanYModalidad($planId, $tipoCobro);
        }
        if (!$flowPlan) {
            throw new \RuntimeException('Plan Flow no disponible.');
        }

        $payload = [
            'planId' => $flowPlan['flow_plan_id'],
            'customerId' => $cliente['flow_customer_id'],
            'subscription_start' => date('Y-m-d'),
        ];

        $response = $this->api->post('subscription/create', $payload);
        $flowSubId = (string) ($response['subscriptionId'] ?? '');
        if ($flowSubId === '') {
            throw new \RuntimeException('Flow no devolvió subscriptionId.');
        }

        $suscripcionLocalId = $suscripcionLocalId && $suscripcionLocalId > 0
            ? $suscripcionLocalId
            : (new Suscripcion())->crear([
                'empresa_id' => $empresaId,
                'plan_id' => $planId,
                'estado' => 'pendiente',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_vencimiento' => $tipoCobro === 'anual' ? date('Y-m-d', strtotime('+365 day')) : date('Y-m-d', strtotime('+30 day')),
                'observaciones' => 'Suscripción creada desde Flow y pendiente de estado oficial.',
                'renovacion_automatica' => 1,
            ]);

        (new FlowSuscripcion())->guardar([
            'suscripcion_id' => $suscripcionLocalId,
            'empresa_id' => $empresaId,
            'plan_id' => $planId,
            'flow_customer_id' => $cliente['flow_customer_id'],
            'flow_plan_id' => $flowPlan['flow_plan_id'],
            'flow_subscription_id' => $flowSubId,
            'tipo_cobro' => $tipoCobro,
            'estado_local' => 'pendiente',
            'estado_flow' => (string) ($response['status'] ?? '0'),
            'entorno_flow' => $this->api->configuracionActiva()['entorno'],
            'fecha_inicio' => $response['subscription_start'] ?? date('Y-m-d H:i:s'),
            'fecha_vencimiento' => $response['subscription_end'] ?? null,
            'proxima_renovacion' => $response['next_invoice_date'] ?? null,
            'fecha_cancelacion' => null,
            'observaciones' => 'Creada desde panel admin.',
            'payload_request' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_response' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        $this->log->info('suscripcion', 'Suscripción Flow creada', $flowSubId, $empresaId, $response);
        return $response;
    }

    public function sincronizarEstado(string $flowSubscriptionId): array
    {
        $estado = $this->api->get('subscription/get', ['subscriptionId' => $flowSubscriptionId]);
        $flowSub = (new FlowSuscripcion())->buscarPorFlowId($flowSubscriptionId);
        if (!$flowSub) {
            throw new \RuntimeException('Suscripción Flow local no encontrada.');
        }

        $estadoFlow = (int) ($estado['status'] ?? 0);
        $estadoLocal = $this->mapearEstadoSuscripcion($estadoFlow);

        (new FlowSuscripcion())->guardar([
            'suscripcion_id' => $flowSub['suscripcion_id'],
            'empresa_id' => $flowSub['empresa_id'],
            'plan_id' => $flowSub['plan_id'],
            'flow_customer_id' => $flowSub['flow_customer_id'],
            'flow_plan_id' => $flowSub['flow_plan_id'],
            'flow_subscription_id' => $flowSub['flow_subscription_id'],
            'tipo_cobro' => $flowSub['tipo_cobro'],
            'estado_local' => $estadoLocal,
            'estado_flow' => (string) $estadoFlow,
            'entorno_flow' => $flowSub['entorno_flow'],
            'fecha_inicio' => $estado['subscription_start'] ?? $flowSub['fecha_inicio'],
            'fecha_vencimiento' => $estado['subscription_end'] ?? $flowSub['fecha_vencimiento'],
            'proxima_renovacion' => $estado['next_invoice_date'] ?? $flowSub['proxima_renovacion'],
            'fecha_cancelacion' => $estadoLocal === 'cancelada' ? date('Y-m-d H:i:s') : null,
            'observaciones' => 'Sincronización manual/automática',
            'payload_request' => $flowSub['payload_request'],
            'payload_response' => json_encode($estado, JSON_UNESCAPED_UNICODE),
        ]);

        (new Suscripcion())->actualizar((int) $flowSub['suscripcion_id'], [
            'empresa_id' => (int) $flowSub['empresa_id'],
            'plan_id' => (int) $flowSub['plan_id'],
            'estado' => $estadoLocal,
            'fecha_inicio' => substr((string) ($estado['subscription_start'] ?? date('Y-m-d')), 0, 10),
            'fecha_vencimiento' => substr((string) ($estado['subscription_end'] ?? date('Y-m-d')), 0, 10),
            'observaciones' => 'Estado sincronizado Flow: ' . $estadoFlow,
        ]);

        $this->log->info('suscripcion', 'Suscripción sincronizada', $flowSubscriptionId, (int) $flowSub['empresa_id'], $estado);
        return $estado;
    }

    public function cancelar(string $flowSubscriptionId): array
    {
        $response = $this->api->post('subscription/cancel', ['subscriptionId' => $flowSubscriptionId]);
        $this->sincronizarEstado($flowSubscriptionId);
        return $response;
    }

    public function mapearEstadoSuscripcion(int $status): string
    {
        return match ($status) {
            1 => 'activa',
            2 => 'suspendida',
            3 => 'cancelada',
            default => 'pendiente',
        };
    }
}
