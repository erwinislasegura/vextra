<?php

namespace Aplicacion\Controladores\Integraciones;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Nucleo\BaseDatos;
use Aplicacion\Modelos\FlowPago;
use Aplicacion\Modelos\Plan;
use Aplicacion\Servicios\ServicioCorreo;
use Aplicacion\Servicios\FlowClientesService;
use Aplicacion\Servicios\FlowPagosService;
use Aplicacion\Servicios\FlowSuscripcionesService;
use Aplicacion\Servicios\FlowWebhookService;

class FlowWebhookControlador extends Controlador
{
    public function confirmacionPago(): void
    {
        (new FlowWebhookService())->procesarPago($_POST);
        http_response_code(200);
        echo 'OK';
    }

    public function callbackSuscripcion(): void
    {
        (new FlowWebhookService())->procesarSuscripcion($_POST);
        http_response_code(200);
        echo 'OK';
    }

    public function callbackRegistroTarjeta(): void
    {
        (new FlowWebhookService())->procesarRegistroTarjeta($_POST);
        http_response_code(200);
        echo 'OK';
    }

    public function retornoPago(?string $suscripcionIdRuta = null): void
    {
        $payloadRetorno = $this->obtenerPayloadRetornoFlow();
        $origen = (string) ($payloadRetorno['origen'] ?? $_GET['origen'] ?? $_POST['origen'] ?? '');

        if ($origen === 'registro') {
            $this->vista('publico/retorno_pago_flow', [
                'token' => (string) ($payloadRetorno['token'] ?? $_GET['token'] ?? $_POST['token'] ?? ''),
                'suscripcion_id' => (int) ($payloadRetorno['suscripcion_id'] ?? $_GET['suscripcion_id'] ?? $_POST['suscripcion_id'] ?? 0),
                'origen' => $origen,
            ]);
            return;
        }

        flash('success', 'Retorno Flow recibido. El estado se confirmó con la API oficial.');
        $this->redirigir('/admin/flow/pagos');
    }

    public function estadoRetornoPago(): void
    {
        $resultado = $this->procesarEstadoRetornoRegistro();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function retornoPagoNoConfirmado(): void
    {
        $estado = (string) ($_GET['estado'] ?? 'pendiente');
        $mensaje = $estado === 'rechazado' || $estado === 'anulado'
            ? 'Flow informó que el pago no fue aprobado. Liberamos tu registro para que puedas intentarlo nuevamente con los mismos datos.'
            : 'Estamos validando la confirmación con Flow. Si ya pagaste, intenta iniciar sesión en unos segundos.';

        $this->vista('publico/retorno_pago_flow_no_confirmado', [
            'estado' => $estado,
            'mensaje' => $mensaje,
        ]);
    }

    private function obtenerPayloadRetornoFlow(): array
    {
        $payload = [];
        $raw = trim((string) file_get_contents('php://input'));
        if ($raw !== '') {
            parse_str($raw, $payloadRaw);
            if (is_array($payloadRaw)) {
                $payload = $payloadRaw;
            }
        }

        if (!is_array($payload)) {
            $payload = [];
        }

        if (!empty($_POST)) {
            $payload = array_merge($payload, $_POST);
        }

        if (isset($payload['Token']) && !isset($payload['token'])) {
            $payload['token'] = $payload['Token'];
        }

        return $payload;
    }

    public function retornoRegistroTarjeta(): void
    {
        $token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
        $origen = (string) ($_GET['origen'] ?? $_POST['origen'] ?? '');
        $registro = null;
        if ($token !== '') {
            $pendiente = $_SESSION['flow_registro_pendiente'] ?? null;
            $empresaId = is_array($pendiente) ? (int) ($pendiente['empresa_id'] ?? 0) : null;
            $registro = (new FlowClientesService())->sincronizarRegistro($token, $empresaId > 0 ? $empresaId : null);
        }

        if ($origen === 'registro') {
            $statusRegistro = (int) ($registro['status'] ?? 0);
            $pendiente = $_SESSION['flow_registro_pendiente'] ?? null;

            if ($statusRegistro === 1 && is_array($pendiente)) {
                try {
                    (new FlowSuscripcionesService())->crearSuscripcion(
                        (int) ($pendiente['empresa_id'] ?? 0),
                        (int) ($pendiente['plan_id'] ?? 0),
                        (string) ($pendiente['tipo_cobro'] ?? 'mensual'),
                        (int) ($pendiente['suscripcion_id'] ?? 0)
                    );
                    unset($_SESSION['flow_registro_pendiente']);
                    flash('success', 'Registro de medio de pago completado y suscripción Flow creada. Ya puedes iniciar sesión.');
                    $this->redirigir('/iniciar-sesion');
                } catch (\Throwable $e) {
                    flash('danger', 'El medio de pago se registró, pero no fue posible crear la suscripción en Flow: ' . $e->getMessage());
                    $this->redirigir('/iniciar-sesion');
                }
            }

            flash('danger', 'Flow no confirmó el registro del medio de pago. Debes intentarlo nuevamente para activar la suscripción.');
            $this->redirigir('/iniciar-sesion');
        }

        flash('success', 'Retorno de registro de tarjeta procesado.');
        $this->redirigir('/admin/flow/clientes');
    }

    private function reintentarConfirmacionPago(string $token, ?array $flowPago): array
    {
        $flowPagos = new FlowPagosService();
        $estadoPago = 'pendiente';

        for ($intento = 0; $intento < 3; $intento++) {
            usleep(2000000);
            try {
                $status = $flowPagos->sincronizarEstadoPorToken($token);
                $estadoPago = $flowPagos->resolverEstadoPagoDesdeRespuesta($status);
                $flowPagoActualizado = (new FlowPago())->buscarPorToken($token);
                if (is_array($flowPagoActualizado)) {
                    $flowPago = $flowPagoActualizado;
                }
                if ($estadoPago !== 'pendiente') {
                    break;
                }
            } catch (\Throwable $e) {
                $estadoPago = 'pendiente';
            }
        }

        return [$estadoPago, $flowPago];
    }

    private function procesarEstadoRetornoRegistro(): array
    {
        $payloadRetorno = $this->obtenerPayloadRetornoFlow();
        $token = (string) ($payloadRetorno['token'] ?? $_GET['token'] ?? $_POST['token'] ?? '');
        $suscripcionId = (int) ($payloadRetorno['suscripcion_id'] ?? $_GET['suscripcion_id'] ?? $_POST['suscripcion_id'] ?? 0);
        $flowPago = null;

        if ($token === '') {
            $pendiente = $_SESSION['flow_pago_registro_pendiente'] ?? null;
            if (is_array($pendiente)) {
                $token = (string) ($pendiente['flow_token'] ?? '');
                if ($suscripcionId <= 0) {
                    $suscripcionId = (int) ($pendiente['suscripcion_id'] ?? 0);
                }
            }
        }

        if ($token === '' && $suscripcionId > 0) {
            $flowPago = (new FlowPago())->buscarUltimoPorSuscripcionId($suscripcionId);
            if (is_array($flowPago)) {
                $token = (string) ($flowPago['flow_token'] ?? '');
            }
        }

        $estadoPago = 'pendiente';
        if ($token !== '') {
            try {
                $flowPagos = new FlowPagosService();
                $status = $flowPagos->sincronizarEstadoPorToken($token);
                $estadoPago = $flowPagos->resolverEstadoPagoDesdeRespuesta($status);
                if (!is_array($flowPago)) {
                    $flowPago = (new FlowPago())->buscarPorToken($token);
                }
            } catch (\Throwable $e) {
                $estadoPago = 'pendiente';
            }
        } elseif (is_array($flowPago)) {
            $estadoPago = (string) ($flowPago['estado_local'] ?? 'pendiente');
        }

        if ($token !== '' && $estadoPago === 'pendiente') {
            [$estadoPago, $flowPago] = $this->reintentarConfirmacionPago($token, $flowPago);
        }

        if ($estadoPago === 'aprobado') {
            $pendiente = $_SESSION['flow_pago_registro_pendiente'] ?? null;
            $this->enviarCorreoPagoConfirmadoRegistro($flowPago, is_array($pendiente) ? $pendiente : null);
            unset($_SESSION['flow_pago_registro_pendiente']);
            return [
                'estado' => 'aprobado',
                'tipo' => 'success',
                'titulo' => '¡Pago confirmado!',
                'mensaje' => 'Tu cuenta quedó activa y ya puedes iniciar sesión en Vextra.',
                'login_url' => url('/iniciar-sesion'),
            ];
        }

        if ($estadoPago === 'rechazado' || $estadoPago === 'anulado') {
            $pendiente = $_SESSION['flow_pago_registro_pendiente'] ?? null;
            if (is_array($pendiente)) {
                $this->eliminarRegistroNoAprobado($pendiente);
            }
            unset($_SESSION['flow_pago_registro_pendiente']);
            return [
                'estado' => $estadoPago,
                'tipo' => 'danger',
                'titulo' => 'Pago no aprobado',
                'mensaje' => 'Flow informó que el pago no fue aprobado. Tu registro fue liberado para que puedas volver a intentarlo con los mismos datos.',
                'login_url' => url('/iniciar-sesion'),
            ];
        }

        return [
            'estado' => 'pendiente',
            'tipo' => 'warning',
            'titulo' => 'Estamos confirmando tu pago',
            'mensaje' => 'Tu pago se registró correctamente. En cuanto Flow confirme, te avisaremos por correo.',
            'login_url' => url('/iniciar-sesion'),
        ];
    }

    private function enviarCorreoPagoConfirmadoRegistro(?array $flowPago, ?array $pendiente): void
    {
        if (!is_array($pendiente)) {
            return;
        }

        $correoAdmin = trim((string) ($pendiente['correo_admin'] ?? ''));
        if ($correoAdmin === '' || !filter_var($correoAdmin, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $nombreAdmin = trim((string) ($pendiente['nombre_admin'] ?? 'Administrador'));
        $passwordAdmin = (string) ($pendiente['password_admin'] ?? '');
        $planNombre = 'Plan contratado';
        $montoPlan = '';
        $frecuencia = '';
        $duracionPlan = '1 mes';
        if (is_array($flowPago)) {
            $plan = (new Plan())->buscar((int) ($flowPago['plan_id'] ?? 0));
            if ($plan) {
                $planNombre = (string) ($plan['nombre'] ?? $planNombre);
            }
            $monto = (float) ($flowPago['monto'] ?? 0);
            $moneda = (string) ($flowPago['moneda'] ?? 'CLP');
            if ($monto > 0) {
                $montoPlan = '$' . number_format($monto, 0, ',', '.') . ' ' . $moneda;
            }
            $frecuencia = (string) ($flowPago['tipo_pago'] ?? '');
            $duracionPlan = $frecuencia === 'anual' ? '12 meses' : '1 mes';
        }
        $linkLogin = 'https://vextra.cl/iniciar-sesion';
        $html = '<div style="font-family:Arial,sans-serif;background:#f6f7fb;padding:24px 0;">'
            . '<table role="presentation" style="width:100%;max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">'
            . '<tr><td style="background:#4632a8;color:#ffffff;padding:18px 24px;border-radius:12px 12px 0 0;">'
            . '<h2 style="margin:0;font-size:22px;">¡Tu cuenta en Vextra está lista!</h2>'
            . '</td></tr>'
            . '<tr><td style="padding:20px 24px;color:#1f2937;">'
            . '<p style="margin:0 0 12px;">Hola ' . htmlspecialchars($nombreAdmin) . ',</p>'
            . '<p style="margin:0 0 16px;">Confirmamos tu pago en Flow y tu cuenta ya está habilitada.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Usuario</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($correoAdmin) . '</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Contraseña</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($passwordAdmin) . '</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Plan</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($planNombre) . '</td></tr>'
            . ($montoPlan !== '' ? '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Monto</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($montoPlan) . '</td></tr>' : '')
            . ($frecuencia !== '' ? '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Tipo</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($frecuencia) . '</td></tr>' : '')
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Duración</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($duracionPlan) . '</td></tr>'
            . '<tr><td style="padding:8px;"><strong>Inicio de sesión</strong></td><td style="padding:8px;"><a href="' . htmlspecialchars($linkLogin) . '">' . htmlspecialchars($linkLogin) . '</a></td></tr>'
            . '</table>'
            . '<p style="margin:16px 0 0;font-size:12px;color:#6b7280;">Correo enviado automáticamente desde noresponder@vextra.cl.</p>'
            . '</td></tr></table></div>';
        (new ServicioCorreo())->enviarNotificacionCliente(
            $correoAdmin,
            'Cuenta creada con éxito y pago confirmado - Vextra',
            'registro_pago_confirmado',
            ['html' => $html]
        );
    }

    private function eliminarRegistroNoAprobado(array $pendiente): void
    {
        $empresaId = (int) ($pendiente['empresa_id'] ?? 0);
        $suscripcionId = (int) ($pendiente['suscripcion_id'] ?? 0);
        $correoAdmin = trim((string) ($pendiente['correo_admin'] ?? ''));

        if ($empresaId <= 0 && $suscripcionId <= 0 && $correoAdmin === '') {
            return;
        }

        $db = BaseDatos::obtener();
        try {
            $db->beginTransaction();

            if ($correoAdmin !== '') {
                $stmtUsuario = $db->prepare('DELETE FROM usuarios WHERE correo = :correo');
                $stmtUsuario->execute(['correo' => $correoAdmin]);
            }

            if ($suscripcionId > 0) {
                $db->prepare('DELETE FROM historial_suscripciones WHERE suscripcion_id = :suscripcion_id')
                    ->execute(['suscripcion_id' => $suscripcionId]);
                $db->prepare('DELETE FROM flow_suscripciones WHERE suscripcion_id = :suscripcion_id')
                    ->execute(['suscripcion_id' => $suscripcionId]);
                $db->prepare('DELETE FROM flow_pagos WHERE suscripcion_id = :suscripcion_id')
                    ->execute(['suscripcion_id' => $suscripcionId]);
                $db->prepare('DELETE FROM pagos WHERE suscripcion_id = :suscripcion_id')
                    ->execute(['suscripcion_id' => $suscripcionId]);
                $db->prepare('DELETE FROM suscripciones WHERE id = :suscripcion_id')
                    ->execute(['suscripcion_id' => $suscripcionId]);
            }

            if ($empresaId > 0) {
                $db->prepare('DELETE FROM flow_clientes WHERE empresa_id = :empresa_id')
                    ->execute(['empresa_id' => $empresaId]);
                $db->prepare('DELETE FROM empresas WHERE id = :empresa_id')
                    ->execute(['empresa_id' => $empresaId]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[flow_retorno] No se pudo eliminar registro no aprobado: ' . $e->getMessage());
        }
    }
}
