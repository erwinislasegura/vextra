<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\FlowCliente;
use Aplicacion\Modelos\Usuario;

class FlowClientesService
{
    public function __construct(
        private readonly FlowApiService $api = new FlowApiService(),
        private readonly FlowLogService $log = new FlowLogService(),
    ) {}

    public function crearCliente(int $empresaId): array
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            throw new \RuntimeException('Empresa no encontrada.');
        }

        $customerId = 'emp_' . $empresaId;
        $email = $this->resolverEmailValidoEmpresa($empresa);
        if (strtolower(trim((string) ($empresa['correo'] ?? ''))) !== $email) {
            $this->log->warning('cliente', 'Correo de empresa inválido; se usa correo técnico para Flow', null, $empresaId, [
                'correo_original' => $empresa['correo'] ?? null,
                'correo_usado' => $email,
            ]);
        }
        $payload = [
            'name' => $empresa['nombre_comercial'] ?: $empresa['razon_social'],
            'email' => $email,
            'externalId' => (string) $empresaId,
        ];

        try {
            $response = $this->api->post('customer/create', $payload);
        } catch (\RuntimeException $e) {
            if (!$this->esErrorEmailInvalidoFlow($e->getMessage())) {
                throw $e;
            }

            $emailAlternativo = $this->construirEmailTecnicoFlow($empresaId);
            $payload['email'] = $emailAlternativo;
            $response = $this->api->post('customer/create', $payload);
            $email = $emailAlternativo;
            $this->log->warning('cliente', 'Flow rechazó email original. Se reintentó con correo técnico.', null, $empresaId, [
                'correo_original' => $empresa['correo'] ?? null,
                'correo_tecnico' => $emailAlternativo,
            ]);
        }
        $flowCustomerId = (string) ($response['customerId'] ?? $customerId);

        (new FlowCliente())->guardar([
            'empresa_id' => $empresaId,
            'flow_customer_id' => $flowCustomerId,
            'correo' => $email,
            'nombre' => (string) ($empresa['nombre_comercial'] ?: $empresa['razon_social']),
            'estado_local' => 'activo',
            'estado_flow' => 'creado',
            'token_registro' => null,
            'url_registro' => null,
            'medio_pago_registrado' => 0,
            'payload_request' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_response' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        $db = \Aplicacion\Nucleo\BaseDatos::obtener();
        $db->prepare('UPDATE empresas SET flow_customer_id=:flow_customer_id, flow_ultima_sincronizacion=NOW(), fecha_actualizacion=NOW() WHERE id=:id')->execute([
            'id' => $empresaId,
            'flow_customer_id' => $flowCustomerId,
        ]);

        $this->log->info('cliente', 'Cliente Flow creado', $flowCustomerId, $empresaId, $response);
        return $response;
    }

    public function iniciarRegistroMedioPago(int $empresaId, ?string $urlRetorno = null): array
    {
        $cliente = (new FlowCliente())->buscarPorEmpresa($empresaId);
        if (!$cliente) {
            $this->crearCliente($empresaId);
            $cliente = (new FlowCliente())->buscarPorEmpresa($empresaId);
        }
        if (!$cliente) {
            throw new \RuntimeException('No se pudo crear/obtener cliente Flow.');
        }

        $urlRetorno = $urlRetorno ?: (FlowApiService::obtenerBasePublicaAplicacion() . '/flow/retorno/registro');
        $payload = [
            'customerId' => $cliente['flow_customer_id'],
            'url_return' => $urlRetorno,
        ];

        $response = $this->api->post('customer/register', $payload);

        (new FlowCliente())->guardar([
            'empresa_id' => $empresaId,
            'flow_customer_id' => $cliente['flow_customer_id'],
            'correo' => $cliente['correo'],
            'nombre' => $cliente['nombre'],
            'estado_local' => 'registro_en_proceso',
            'estado_flow' => 'pendiente_registro',
            'token_registro' => $response['token'] ?? null,
            'url_registro' => isset($response['url'], $response['token']) ? $response['url'] . '?token=' . $response['token'] : null,
            'medio_pago_registrado' => 0,
            'payload_request' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_response' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        $this->log->info('registro_medio_pago', 'Inicio de registro de medio de pago', (string) ($response['token'] ?? ''), $empresaId, $response);
        return $response;
    }

    public function sincronizarRegistro(string $token, ?int $empresaId = null): array
    {
        $response = $this->api->get('customer/getRegisterStatus', ['token' => $token]);
        $medioRegistrado = (int) ($response['status'] ?? 0) === 1 ? 1 : 0;

        if ($empresaId) {
            $cliente = (new FlowCliente())->buscarPorEmpresa($empresaId);
            if ($cliente) {
                (new FlowCliente())->guardar([
                    'empresa_id' => $empresaId,
                    'flow_customer_id' => $cliente['flow_customer_id'],
                    'correo' => $cliente['correo'],
                    'nombre' => $cliente['nombre'],
                    'estado_local' => $medioRegistrado ? 'activo' : 'registro_fallido',
                    'estado_flow' => $medioRegistrado ? 'registrado' : 'fallido',
                    'token_registro' => $token,
                    'url_registro' => $cliente['url_registro'],
                    'medio_pago_registrado' => $medioRegistrado,
                    'payload_request' => $cliente['payload_request'],
                    'payload_response' => json_encode($response, JSON_UNESCAPED_UNICODE),
                ]);

                \Aplicacion\Nucleo\BaseDatos::obtener()->prepare('UPDATE empresas SET flow_medio_pago_registrado=:ok, flow_estado_registro=:estado, flow_ultima_sincronizacion=NOW() WHERE id=:empresa_id')->execute([
                    'ok' => $medioRegistrado,
                    'estado' => $medioRegistrado ? 'registrado' : 'fallido',
                    'empresa_id' => $empresaId,
                ]);
            }
        }

        $this->log->info('registro_medio_pago', 'Sincronización de registro de tarjeta', $token, $empresaId, $response);
        return $response;
    }

    private function resolverEmailValidoEmpresa(array $empresa): string
    {
        $correosCandidatos = [
            strtolower(trim((string) ($empresa['correo'] ?? ''))),
        ];

        $empresaId = (int) ($empresa['id'] ?? 0);
        if ($empresaId > 0) {
            $admin = (new Usuario())->obtenerAdministradorPrincipalPorEmpresa($empresaId);
            if ($admin) {
                $correosCandidatos[] = strtolower(trim((string) ($admin['correo'] ?? '')));
            }
        }

        foreach ($correosCandidatos as $email) {
            if ($this->esEmailCompatibleFlow($email)) {
                return $email;
            }
        }

        return 'cliente.empresa' . max(1, $empresaId) . '@gmail.com';
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

    private function construirEmailTecnicoFlow(int $empresaId): string
    {
        return 'cliente.flow.' . max(1, $empresaId) . '@gmail.com';
    }
}
