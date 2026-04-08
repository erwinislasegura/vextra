<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\FlowCliente;
use Aplicacion\Modelos\FlowConfiguracion;
use Aplicacion\Modelos\FlowLog;
use Aplicacion\Modelos\FlowPago;
use Aplicacion\Modelos\FlowPlan;
use Aplicacion\Modelos\FlowSuscripcion;
use Aplicacion\Modelos\FlowWebhook;
use Aplicacion\Modelos\Plan;
use Aplicacion\Nucleo\BaseDatos;
use Aplicacion\Nucleo\Controlador;
use Aplicacion\Servicios\FlowApiService;
use Aplicacion\Servicios\FlowClientesService;
use Aplicacion\Servicios\FlowPagosService;
use Aplicacion\Servicios\FlowPlanesService;
use Aplicacion\Servicios\FlowSuscripcionesService;

class FlowAdminControlador extends Controlador
{
    public function dashboard(): void
    {
        $db = BaseDatos::obtener();
        $resumen = [
            'clientes' => (int) $db->query('SELECT COUNT(*) total FROM flow_clientes WHERE fecha_eliminacion IS NULL')->fetch()['total'],
            'suscripciones_activas' => (int) $db->query("SELECT COUNT(*) total FROM flow_suscripciones WHERE estado_local = 'activa'")->fetch()['total'],
            'suscripciones_canceladas' => (int) $db->query("SELECT COUNT(*) total FROM flow_suscripciones WHERE estado_local IN ('cancelada','vencida')")->fetch()['total'],
            'pagos_hoy' => (int) $db->query('SELECT COUNT(*) total FROM flow_pagos WHERE DATE(fecha_creacion) = CURDATE()')->fetch()['total'],
            'ingresos_mensuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(CASE WHEN tipo_cobro='mensual' AND estado_local IN ('activa','pendiente') THEN p.precio_mensual ELSE 0 END),0) total FROM flow_suscripciones fs INNER JOIN planes p ON p.id = fs.plan_id")->fetch()['total'],
            'ingresos_anuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(CASE WHEN tipo_cobro='anual' AND estado_local IN ('activa','pendiente') THEN p.precio_anual ELSE 0 END),0) total FROM flow_suscripciones fs INNER JOIN planes p ON p.id = fs.plan_id")->fetch()['total'],
        ];

        $pagosRecientes = $db->query('SELECT fp.*, e.nombre_comercial AS empresa FROM flow_pagos fp INNER JOIN empresas e ON e.id = fp.empresa_id ORDER BY fp.id DESC LIMIT 10')->fetchAll();
        $renovaciones = $db->query("SELECT fs.*, e.nombre_comercial AS empresa, p.nombre AS plan FROM flow_suscripciones fs INNER JOIN empresas e ON e.id = fs.empresa_id INNER JOIN planes p ON p.id = fs.plan_id WHERE fs.proxima_renovacion IS NOT NULL ORDER BY fs.proxima_renovacion ASC LIMIT 10")->fetchAll();
        $problemas = $db->query("SELECT e.nombre_comercial, fs.estado_local, fs.flow_subscription_id FROM flow_suscripciones fs INNER JOIN empresas e ON e.id = fs.empresa_id WHERE fs.estado_local IN ('suspendida','cancelada','vencida') ORDER BY fs.id DESC LIMIT 10")->fetchAll();
        $empresasPorPlan = $db->query("SELECT p.nombre, COUNT(*) total FROM flow_suscripciones fs INNER JOIN planes p ON p.id = fs.plan_id WHERE fs.estado_local='activa' GROUP BY p.id, p.nombre ORDER BY total DESC")->fetchAll();

        $this->vista('admin/flow/dashboard', compact('resumen', 'pagosRecientes', 'renovaciones', 'problemas', 'empresasPorPlan'), 'admin');
    }

    public function configuracion(): void
    {
        $config = (new FlowConfiguracion())->obtener();
        if ($config) {
            $config['secret_key_masked'] = empty($config['secret_key_enc']) ? '' : '********' . substr(base64_decode((string) $config['secret_key_enc']) ?: '', -4);
        }
        $this->vista('admin/flow/configuracion', compact('config'), 'admin');
    }

    public function guardarConfiguracion(): void
    {
        validar_csrf();
        $api = new FlowApiService();
        $actual = (new FlowConfiguracion())->obtener();

        $secretPlano = trim((string) ($_POST['secret_key'] ?? ''));
        $secretEnc = $secretPlano !== '' ? $api->encriptarSecret($secretPlano) : (string) ($actual['secret_key_enc'] ?? '');

        $entorno = $_POST['entorno'] === 'produccion' ? 'produccion' : 'sandbox';
        $baseUrl = trim((string) ($_POST['base_url'] ?? ''));
        if ($baseUrl === '') {
            $baseUrl = $entorno === 'produccion'
                ? 'https://www.flow.cl/api'
                : 'https://sandbox.flow.cl/api';
        }

        (new FlowConfiguracion())->guardar([
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'entorno' => $entorno,
            'api_key' => trim((string) ($_POST['api_key'] ?? '')),
            'secret_key_enc' => $secretEnc,
            'base_url' => $baseUrl,
            'habilitar_pagos_unicos' => isset($_POST['habilitar_pagos_unicos']) ? 1 : 0,
            'habilitar_suscripciones' => isset($_POST['habilitar_suscripciones']) ? 1 : 0,
            'url_confirmacion' => trim((string) ($_POST['url_confirmacion'] ?? '')),
            'url_retorno' => trim((string) ($_POST['url_retorno'] ?? '')),
            'url_webhook_pago' => trim((string) ($_POST['url_webhook_pago'] ?? '')),
            'url_webhook_suscripcion' => trim((string) ($_POST['url_webhook_suscripcion'] ?? '')),
            'url_retorno_registro' => trim((string) ($_POST['url_retorno_registro'] ?? '')),
        ]);

        flash('success', 'Configuración Flow guardada.');
        $this->redirigir('/admin/flow/configuracion');
    }

    public function planes(): void
    {
        $planes = (new Plan())->listar();
        $flowPlanes = (new FlowPlan())->listar();
        $this->vista('admin/flow/planes', compact('planes', 'flowPlanes'), 'admin');
    }

    public function crearPlanFlow(int $planId, string $modalidad): void
    {
        validar_csrf();
        try {
            $respuesta = (new FlowPlanesService())->crearOActualizarPlan(
                $planId,
                $modalidad === 'anual' ? 'anual' : 'mensual'
            );
            if (($respuesta['status'] ?? '') === 'already_exists') {
                flash('warning', 'El plan ya existía en Flow. Se vinculó localmente para continuar la operación.');
            } else {
                flash('success', 'Plan sincronizado con Flow.');
            }
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo sincronizar el plan en Flow: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/planes');
    }

    public function clientes(): void
    {
        $clientes = (new FlowCliente())->listar();
        $empresas = (new Empresa())->listar();
        $this->vista('admin/flow/clientes', compact('clientes', 'empresas'), 'admin');
    }

    public function crearCliente(): void
    {
        validar_csrf();
        $empresaId = (int) ($_POST['empresa_id'] ?? 0);
        try {
            (new FlowClientesService())->crearCliente($empresaId);
            flash('success', 'Cliente Flow creado correctamente.');
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo crear cliente Flow: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/clientes');
    }

    public function iniciarRegistroTarjeta(int $empresaId): void
    {
        validar_csrf();
        flash('warning', 'Flujo deshabilitado: este proyecto opera en modo Flow Ecommerce sin Cargo Automático.');
        $this->redirigir('/admin/flow/clientes');
    }

    public function suscripciones(): void
    {
        $filtros = ['estado' => $_GET['estado'] ?? '', 'plan_id' => $_GET['plan_id'] ?? '', 'empresa_id' => $_GET['empresa_id'] ?? ''];
        $suscripciones = (new FlowSuscripcion())->listar($filtros);
        $empresas = (new Empresa())->listar();
        $planes = (new Plan())->listar();
        $this->vista('admin/flow/suscripciones', compact('suscripciones', 'empresas', 'planes', 'filtros'), 'admin');
    }

    public function crearSuscripcion(): void
    {
        validar_csrf();
        flash('warning', 'Flujo deshabilitado: este proyecto opera en modo Flow Ecommerce sin suscripción automática.');
        $this->redirigir('/admin/flow/suscripciones');
    }

    public function sincronizarSuscripcion(string $flowSubscriptionId): void
    {
        validar_csrf();
        try {
            (new FlowSuscripcionesService())->sincronizarEstado($flowSubscriptionId);
            flash('success', 'Suscripción sincronizada.');
        } catch (\Throwable $e) {
            flash('danger', 'Error al sincronizar suscripción: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/suscripciones');
    }

    public function cancelarSuscripcion(string $flowSubscriptionId): void
    {
        validar_csrf();
        try {
            (new FlowSuscripcionesService())->cancelar($flowSubscriptionId);
            flash('success', 'Suscripción cancelada en Flow.');
        } catch (\Throwable $e) {
            flash('danger', 'Error al cancelar suscripción: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/suscripciones');
    }

    public function pagos(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'empresa_id' => $_GET['empresa_id'] ?? '',
            'plan_id' => $_GET['plan_id'] ?? '',
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
        ];
        $pagos = (new FlowPago())->listar($filtros);
        $empresas = (new Empresa())->listar();
        $planes = (new Plan())->listar();
        $this->vista('admin/flow/pagos', compact('pagos', 'empresas', 'planes', 'filtros'), 'admin');
    }

    public function crearPago(): void
    {
        validar_csrf();
        try {
            $resp = (new FlowPagosService())->crearPagoUnico((int) $_POST['empresa_id'], (int) $_POST['plan_id'], (string) ($_POST['frecuencia'] ?? 'mensual'), trim((string) ($_POST['concepto'] ?? 'Cobro administrativo')));
            $url = isset($resp['url'], $resp['token']) ? $resp['url'] . '?token=' . $resp['token'] : null;
            flash('success', $url ? 'Pago creado. URL de pago: ' . $url : 'Pago creado en Flow.');
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo crear pago: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/pagos');
    }

    public function sincronizarPago(string $token): void
    {
        validar_csrf();
        try {
            (new FlowPagosService())->sincronizarEstadoPorToken($token);
            flash('success', 'Pago sincronizado.');
        } catch (\Throwable $e) {
            flash('danger', 'Error al sincronizar pago: ' . $e->getMessage());
        }
        $this->redirigir('/admin/flow/pagos');
    }

    public function logs(): void
    {
        $filtros = [
            'empresa_id' => $_GET['empresa_id'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
        ];
        $logs = (new FlowLog())->listar($filtros);
        $webhooks = (new FlowWebhook())->listar(['tipo' => $_GET['tipo_webhook'] ?? '']);
        $empresas = (new Empresa())->listar();
        $this->vista('admin/flow/logs', compact('logs', 'webhooks', 'empresas', 'filtros'), 'admin');
    }
}
