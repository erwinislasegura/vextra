<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\FlowPago;
use Aplicacion\Modelos\Pago;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Modelos\Usuario;

class FlowPagosService
{
    public function __construct(
        private readonly FlowApiService $api = new FlowApiService(),
        private readonly FlowLogService $log = new FlowLogService(),
    ) {}

    public function crearPagoUnico(
        int $empresaId,
        int $planId,
        string $frecuencia = 'mensual',
        string $concepto = 'Cobro plan',
        ?string $urlReturn = null,
        ?string $urlConfirmation = null,
        ?int $suscripcionId = null,
    ): array
    {
        $plan = (new Plan())->buscar($planId);
        if (!$plan) {
            throw new \RuntimeException('Plan no encontrado.');
        }
        $suscripcionId = $suscripcionId && $suscripcionId > 0 ? $suscripcionId : (int) ((new Suscripcion())->obtenerUltimaPorEmpresa($empresaId)['id'] ?? 0);
        if ($suscripcionId <= 0) {
            throw new \RuntimeException('No se encontró una suscripción asociada para registrar el pago.');
        }

        $monto = $frecuencia === 'anual' ? (float) $plan['precio_anual'] : (float) $plan['precio_mensual'];
        $commerceOrder = 'EMP' . $empresaId . '-PLAN' . $planId . '-' . strtoupper(substr(sha1((string) microtime(true)), 0, 8));
        $basePublica = FlowApiService::obtenerBasePublicaAplicacion();
        $emailCobro = $this->resolverEmailCobro($empresaId);

        $payload = [
            'commerceOrder' => $commerceOrder,
            'subject' => $concepto . ' ' . $plan['nombre'] . ' (' . $frecuencia . ')',
            'currency' => 'CLP',
            'amount' => (int) $monto,
            'email' => $emailCobro,
            'urlConfirmation' => $urlConfirmation ?: ($basePublica . '/flow/webhook/payment-confirmation'),
            'urlReturn' => $urlReturn ?: ($basePublica . '/retorno/pago'),
        ];

        try {
            $response = $this->api->post('payment/create', $payload);
        } catch (\RuntimeException $e) {
            if (!$this->esErrorEmailInvalidoFlow($e->getMessage())) {
                throw $e;
            }

            $payload['email'] = $this->construirEmailTecnicoCobro($empresaId);
            $response = $this->api->post('payment/create', $payload);
            $this->log->warning('pago', 'Flow rechazó email de cobro. Se reintentó con correo técnico.', null, $empresaId, [
                'correo_usado_previo' => $emailCobro,
                'correo_tecnico' => $payload['email'],
            ]);
        }

        $pagoId = (new Pago())->crear([
            'empresa_id' => $empresaId,
            'suscripcion_id' => $suscripcionId,
            'monto' => $monto,
            'moneda' => 'CLP',
            'metodo' => 'flow',
            'frecuencia' => $frecuencia,
            'estado' => 'pendiente',
            'referencia_externa' => (string) ($response['token'] ?? $commerceOrder),
            'observaciones' => 'Pago creado por panel administrador',
            'payload' => json_encode($response, JSON_UNESCAPED_UNICODE),
            'fecha_pago' => date('Y-m-d H:i:s'),
        ]);

        (new FlowPago())->guardar([
            'pago_id' => $pagoId,
            'suscripcion_id' => $suscripcionId,
            'empresa_id' => $empresaId,
            'plan_id' => $planId,
            'tipo_pago' => 'unico',
            'commerce_order' => $commerceOrder,
            'flow_token' => (string) ($response['token'] ?? ''),
            'flow_order' => null,
            'flow_payment_id' => null,
            'estado_local' => 'pendiente',
            'estado_flow' => 'created',
            'monto' => $monto,
            'moneda' => 'CLP',
            'entorno_flow' => $this->api->configuracionActiva()['entorno'],
            'fecha_confirmacion' => null,
            'observaciones' => 'Pendiente confirmación oficial Flow',
            'payload_request' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_response' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        $this->suspenderCuentaPorPagoPendiente($suscripcionId, $empresaId, $planId);

        $this->log->info('pago', 'Pago único creado en Flow', (string) ($response['token'] ?? ''), $empresaId, $response);
        return $response;
    }

    public function sincronizarEstadoPorToken(string $token): array
    {
        $status = $this->api->get('payment/getStatus', ['token' => $token]);
        $estado = $this->resolverEstadoPagoDesdeRespuesta($status);
        $flowPago = (new FlowPago())->buscarPorToken($token);

        if ($flowPago) {
            (new FlowPago())->guardar([
                'pago_id' => $flowPago['pago_id'],
                'suscripcion_id' => $flowPago['suscripcion_id'],
                'empresa_id' => $flowPago['empresa_id'],
                'plan_id' => $flowPago['plan_id'],
                'tipo_pago' => $flowPago['tipo_pago'],
                'commerce_order' => $flowPago['commerce_order'],
                'flow_token' => $token,
                'flow_order' => $status['flowOrder'] ?? null,
                'flow_payment_id' => (string) ($status['flowOrder'] ?? ''),
                'estado_local' => $estado,
                'estado_flow' => (string) ($status['status'] ?? ''),
                'monto' => (float) ($status['amount'] ?? $flowPago['monto']),
                'moneda' => (string) ($status['currency'] ?? $flowPago['moneda']),
                'entorno_flow' => $flowPago['entorno_flow'],
                'fecha_confirmacion' => $estado === 'aprobado' ? date('Y-m-d H:i:s') : null,
                'observaciones' => 'Sincronizado con Flow (' . date('Y-m-d H:i:s') . ')',
                'payload_request' => $flowPago['payload_request'],
                'payload_response' => json_encode($status, JSON_UNESCAPED_UNICODE),
            ]);

            if ((int) $flowPago['pago_id'] > 0) {
                \Aplicacion\Nucleo\BaseDatos::obtener()->prepare('UPDATE pagos SET estado=:estado, flow_status=:flow_status, flow_token=:flow_token, flow_payment_id=:flow_payment_id, fecha_confirmacion=:fecha_confirmacion, payload_response=:payload_response WHERE id=:id')->execute([
                    'estado' => $estado,
                    'flow_status' => (string) ($status['status'] ?? ''),
                    'flow_token' => $token,
                    'flow_payment_id' => (string) ($status['flowOrder'] ?? ''),
                    'fecha_confirmacion' => $estado === 'aprobado' ? date('Y-m-d H:i:s') : null,
                    'payload_response' => json_encode($status, JSON_UNESCAPED_UNICODE),
                    'id' => $flowPago['pago_id'],
                ]);
            }

            $this->aplicarResultadoASuscripcion($flowPago, $estado);
        }

        $this->log->info('pago', 'Estado de pago sincronizado', $token, $flowPago['empresa_id'] ?? null, $status);
        return $status;
    }

    public function mapearEstadoPago(int $status): string
    {
        return match ($status) {
            2 => 'aprobado',
            3 => 'rechazado',
            4 => 'anulado',
            default => 'pendiente',
        };
    }

    public function resolverEstadoPagoDesdeRespuesta(array $status): string
    {
        $estado = $this->mapearEstadoPago((int) ($status['status'] ?? 0));
        if ($estado !== 'pendiente') {
            return $estado;
        }

        $paymentData = $status['paymentData'] ?? null;
        if (is_array($paymentData) && !empty($paymentData)) {
            $ultimoIntento = end($paymentData);
            if (is_array($ultimoIntento)) {
                $estadoIntento = (int) ($ultimoIntento['status'] ?? 0);
                if ($estadoIntento === 2) {
                    return 'aprobado';
                }
                if ($estadoIntento === 3 || $estadoIntento === 4) {
                    return 'rechazado';
                }
            }
        }

        return 'pendiente';
    }

    private function resolverEmailCobro(int $empresaId): string
    {
        $empresa = (new Empresa())->buscar($empresaId);

        $candidatos = [
            (string) ($empresa['correo'] ?? ''),
        ];

        $admin = (new Usuario())->obtenerAdministradorPrincipalPorEmpresa($empresaId);
        if ($admin) {
            $candidatos[] = (string) ($admin['correo'] ?? '');
        }

        foreach ($candidatos as $email) {
            $emailNormalizado = $this->normalizarEmailCompatibleFlow($email);
            if ($emailNormalizado !== null) {
                return $emailNormalizado;
            }
        }

        return 'pagos.empresa' . max(1, $empresaId) . '@outlook.com';
    }

    private function normalizarEmailCompatibleFlow(string $email): ?string
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $this->esEmailCompatibleFlow($email) ? $email : null;
    }

    private function esEmailCompatibleFlow(string $email): bool
    {
        if (strlen($email) > 120) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9._-]+@[a-z0-9.-]+\\.[a-z]{2,}$/', $email);
    }

    private function esErrorEmailInvalidoFlow(string $mensaje): bool
    {
        return str_contains(strtolower($mensaje), 'email is not valid');
    }

    private function construirEmailTecnicoCobro(int $empresaId): string
    {
        return 'pagos.flow.' . max(1, $empresaId) . '@gmail.com';
    }

    private function aplicarResultadoASuscripcion(array $flowPago, string $estadoPago): void
    {
        $suscripcionId = (int) ($flowPago['suscripcion_id'] ?? 0);
        $empresaId = (int) ($flowPago['empresa_id'] ?? 0);
        $planId = (int) ($flowPago['plan_id'] ?? 0);
        if ($suscripcionId <= 0 || $empresaId <= 0 || $planId <= 0) {
            return;
        }

        if ($estadoPago === 'aprobado') {
            $hoy = date('Y-m-d');
            $plan = (new Plan())->buscar($planId);
            $duracionDias = max(1, (int) ($plan['duracion_dias'] ?? 30));
            $nuevaFechaVencimiento = date('Y-m-d', strtotime($hoy . ' +' . $duracionDias . ' days'));

            (new Suscripcion())->actualizar($suscripcionId, [
                'empresa_id' => $empresaId,
                'plan_id' => $planId,
                'estado' => 'activa',
                'fecha_inicio' => $hoy,
                'fecha_vencimiento' => $nuevaFechaVencimiento,
                'observaciones' => 'Suscripción activada por pago aprobado en Flow.',
            ]);
            return;
        }

        if ($estadoPago === 'rechazado' || $estadoPago === 'anulado') {
            $suscripcionActual = (new Suscripcion())->buscar($suscripcionId);
            (new Suscripcion())->actualizar($suscripcionId, [
                'empresa_id' => $empresaId,
                'plan_id' => $planId,
                'estado' => 'suspendida',
                'fecha_inicio' => $suscripcionActual['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_vencimiento' => $suscripcionActual['fecha_vencimiento'] ?? date('Y-m-d'),
                'observaciones' => 'Cuenta suspendida: pago Flow no aprobado (' . $estadoPago . ').',
            ]);
        }
    }

    private function suspenderCuentaPorPagoPendiente(int $suscripcionId, int $empresaId, int $planId): void
    {
        $suscripcionActual = (new Suscripcion())->buscar($suscripcionId);
        if (!$suscripcionActual) {
            return;
        }

        (new Suscripcion())->actualizar($suscripcionId, [
            'empresa_id' => $empresaId,
            'plan_id' => $planId,
            'estado' => 'suspendida',
            'fecha_inicio' => $suscripcionActual['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_vencimiento' => $suscripcionActual['fecha_vencimiento'] ?? date('Y-m-d'),
            'observaciones' => 'Cuenta suspendida temporalmente hasta validar pago Flow en /admin/flow/pagos.',
        ]);
    }
}
