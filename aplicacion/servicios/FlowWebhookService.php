<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\FlowPago;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Modelos\FlowWebhook;
use Aplicacion\Modelos\CatalogoCompra;

class FlowWebhookService
{
    public function __construct(
        private readonly FlowPagosService $pagos = new FlowPagosService(),
        private readonly FlowSuscripcionesService $suscripciones = new FlowSuscripcionesService(),
        private readonly FlowClientesService $clientes = new FlowClientesService(),
        private readonly FlowLogService $log = new FlowLogService(),
    ) {}

    public function procesarPago(array $payload): void
    {
        $token = (string) ($payload['token'] ?? '');
        $id = (new FlowWebhook())->registrar('payment_confirmation', $token, $payload);
        if ($id === null) {
            return;
        }

        try {
            $status = $this->pagos->sincronizarEstadoPorToken($token);
            $this->sincronizarCompraCatalogo($token, $status);
            $this->enviarCorreoSiPagoAprobado($token, $status);
            (new FlowWebhook())->marcarProcesado($id, 'ok');
            $this->log->info('webhook', 'Webhook pago procesado', $token, null, $status);
        } catch (\Throwable $e) {
            (new FlowWebhook())->marcarProcesado($id, 'error', $e->getMessage());
            $this->log->error('webhook', 'Error al procesar webhook de pago: ' . $e->getMessage(), $token, null, $payload);
        }
    }

    public function procesarSuscripcion(array $payload): void
    {
        $subId = (string) ($payload['subscriptionId'] ?? '');
        $id = (new FlowWebhook())->registrar('subscription_callback', $subId, $payload);
        if ($id === null) {
            return;
        }

        try {
            $status = $this->suscripciones->sincronizarEstado($subId);
            (new FlowWebhook())->marcarProcesado($id, 'ok');
            $this->log->info('webhook', 'Webhook suscripción procesado', $subId, null, $status);
        } catch (\Throwable $e) {
            (new FlowWebhook())->marcarProcesado($id, 'error', $e->getMessage());
            $this->log->error('webhook', 'Error al procesar webhook suscripción: ' . $e->getMessage(), $subId, null, $payload);
        }
    }

    public function procesarRegistroTarjeta(array $payload): void
    {
        $token = (string) ($payload['token'] ?? '');
        $id = (new FlowWebhook())->registrar('card_register_callback', $token, $payload);
        if ($id === null) {
            return;
        }

        try {
            $this->clientes->sincronizarRegistro($token);
            (new FlowWebhook())->marcarProcesado($id, 'ok');
            $this->log->info('webhook', 'Webhook registro medio pago procesado', $token, null, $payload);
        } catch (\Throwable $e) {
            (new FlowWebhook())->marcarProcesado($id, 'error', $e->getMessage());
            $this->log->error('webhook', 'Error webhook registro medio pago: ' . $e->getMessage(), $token, null, $payload);
        }
    }

    private function sincronizarCompraCatalogo(string $token, array $status): void
    {
        if ($token === '') {
            return;
        }

        $estado = $this->pagos->resolverEstadoPagoDesdeRespuesta($status);
        (new CatalogoCompra())->actualizarEstadoPorToken($token, $estado, $status);
    }

    private function enviarCorreoSiPagoAprobado(string $token, array $status): void
    {
        $estado = $this->pagos->resolverEstadoPagoDesdeRespuesta($status);
        if ($estado !== 'aprobado') {
            return;
        }

        $flowPago = (new FlowPago())->buscarPorToken($token);
        if (!$flowPago) {
            return;
        }

        $observaciones = (string) ($flowPago['observaciones'] ?? '');
        if (stripos($observaciones, 'correo_confirmacion_enviado') !== false) {
            return;
        }

        $empresaId = (int) ($flowPago['empresa_id'] ?? 0);
        if ($empresaId <= 0) {
            return;
        }

        $admin = (new Usuario())->obtenerAdministradorPrincipalPorEmpresa($empresaId);
        $correo = trim((string) ($admin['correo'] ?? ''));
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $planNombre = 'Plan contratado';
        $plan = (new Plan())->buscar((int) ($flowPago['plan_id'] ?? 0));
        if ($plan) {
            $planNombre = (string) ($plan['nombre'] ?? $planNombre);
        }

        $monto = (float) ($flowPago['monto'] ?? 0);
        $moneda = (string) ($flowPago['moneda'] ?? 'CLP');
        $montoPlan = $monto > 0 ? '$' . number_format($monto, 0, ',', '.') . ' ' . $moneda : '';
        $tipoPago = (string) ($flowPago['tipo_pago'] ?? 'mensual');
        $duracionPlan = $tipoPago === 'anual' ? '12 meses' : '1 mes';

        $nombreAdmin = trim((string) ($admin['nombre'] ?? ''));
        $linkLogin = url('/iniciar-sesion');
        $html = '<div style="font-family:Arial,sans-serif;background:#f6f7fb;padding:24px 0;">'
            . '<table role="presentation" style="width:100%;max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">'
            . '<tr><td style="background:#4632a8;color:#ffffff;padding:18px 24px;border-radius:12px 12px 0 0;">'
            . '<h2 style="margin:0;font-size:22px;">Pago confirmado en Vextra</h2>'
            . '</td></tr>'
            . '<tr><td style="padding:20px 24px;color:#1f2937;">'
            . '<p style="margin:0 0 12px;">Hola ' . htmlspecialchars($nombreAdmin !== '' ? $nombreAdmin : 'equipo') . ',</p>'
            . '<p style="margin:0 0 16px;">Tu pago fue aprobado y tu cuenta ya está activa.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Usuario</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($correo) . '</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Contraseña</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">La que registraste al crear tu cuenta</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Plan</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($planNombre) . '</td></tr>'
            . ($montoPlan !== '' ? '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Monto</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($montoPlan) . '</td></tr>' : '')
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Duración</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($duracionPlan) . '</td></tr>'
            . '<tr><td style="padding:8px;"><strong>Inicio de sesión</strong></td><td style="padding:8px;"><a href="' . htmlspecialchars($linkLogin) . '">' . htmlspecialchars($linkLogin) . '</a></td></tr>'
            . '</table>'
            . '<p style="margin:16px 0 0;font-size:12px;color:#6b7280;">Correo enviado automáticamente desde noresponder@vextra.cl.</p>'
            . '</div>';

        (new ServicioCorreo())->enviarNotificacionCliente(
            $correo,
            'Pago confirmado en Flow - Vextra',
            'flow_pago_confirmado',
            ['html' => $html]
        );

        (new FlowPago())->guardar([
            'pago_id' => $flowPago['pago_id'],
            'suscripcion_id' => $flowPago['suscripcion_id'],
            'empresa_id' => $flowPago['empresa_id'],
            'plan_id' => $flowPago['plan_id'],
            'tipo_pago' => $flowPago['tipo_pago'],
            'commerce_order' => $flowPago['commerce_order'],
            'flow_token' => $flowPago['flow_token'],
            'flow_order' => $flowPago['flow_order'],
            'flow_payment_id' => $flowPago['flow_payment_id'],
            'estado_local' => $flowPago['estado_local'],
            'estado_flow' => $flowPago['estado_flow'],
            'monto' => $flowPago['monto'],
            'moneda' => $flowPago['moneda'],
            'entorno_flow' => $flowPago['entorno_flow'],
            'fecha_confirmacion' => $flowPago['fecha_confirmacion'],
            'observaciones' => trim($observaciones . ' | correo_confirmacion_enviado=' . date('Y-m-d H:i:s')),
            'payload_request' => $flowPago['payload_request'],
            'payload_response' => $flowPago['payload_response'],
        ]);
    }
}
