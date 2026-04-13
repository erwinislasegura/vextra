<?php

namespace Aplicacion\Controladores\Autenticacion;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Nucleo\BaseDatos;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;
use Throwable;

class AutenticacionControlador extends Controlador
{
    public function mostrarLogin(): void
    {
        $this->vista('autenticacion/login', [
            'meta_title' => 'Iniciar sesión | Vextra',
            'meta_description' => 'Accede a Vextra para gestionar cotizaciones, clientes, inventario y seguimiento comercial en tu empresa.',
            'meta_keywords' => 'iniciar sesión vextra, acceso plataforma comercial, software cotizaciones login',
        ], 'publico');
    }

    public function iniciarSesion(): void
    {
        validar_csrf();
        $correo = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$correo || $password === '') {
            flash('danger', 'Completa correo y contraseña correctamente.');
            $this->redirigir('/iniciar-sesion');
        }

        $usuario = (new Usuario())->buscarPorCorreo($correo);
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            flash('danger', 'Credenciales inválidas.');
            $this->redirigir('/iniciar-sesion');
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'empresa_id' => $usuario['empresa_id'] ? (int) $usuario['empresa_id'] : null,
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'rol_codigo' => $usuario['rol_codigo'],
        ];

        if ($usuario['rol_codigo'] === 'superadministrador') {
            $this->redirigir('/admin/panel');
        }

        $this->redirigir('/app/panel');
    }

    public function cerrarSesion(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirigir('/iniciar-sesion');
    }

    public function mostrarRegistro(): void
    {
        $planes = (new Plan())->listar(true);
        $datosFormulario = $_SESSION['registro_old'] ?? [];
        unset($_SESSION['registro_old']);

        $planPreseleccionado = 0;
        $tipoCobroPreseleccionado = in_array((string) ($_GET['frecuencia'] ?? ''), ['mensual', 'anual'], true)
            ? (string) $_GET['frecuencia']
            : 'mensual';
        if (!empty($datosFormulario['plan_id'])) {
            $planPreseleccionado = (int) $datosFormulario['plan_id'];
        }
        if (!empty($datosFormulario['tipo_cobro']) && in_array((string) $datosFormulario['tipo_cobro'], ['mensual', 'anual'], true)) {
            $tipoCobroPreseleccionado = (string) $datosFormulario['tipo_cobro'];
        }
        if (isset($_GET['plan'])) {
            $pre = (new Plan())->buscar((int) $_GET['plan']);
            $planPreseleccionado = ($pre && ($pre['estado'] ?? '') === 'activo' && (int) ($pre['visible'] ?? 0) === 1) ? (int) $pre['id'] : 0;
        }
        $this->vista('autenticacion/registro', [
            'planes' => $planes,
            'planPreseleccionado' => $planPreseleccionado,
            'tipoCobroPreseleccionado' => $tipoCobroPreseleccionado,
            'datosFormulario' => $datosFormulario,
            'requiereRecaptcha' => true,
            'meta_title' => 'Registro de empresa | Vextra',
            'meta_description' => 'Crea tu cuenta empresarial en Vextra y elige un plan mensual o anual para ordenar tus cotizaciones y ventas.',
            'meta_keywords' => 'registro vextra, crear cuenta empresarial, planes de cotizaciones',
        ], 'publico');
    }

    public function registrarEmpresa(): void
    {
        validar_csrf();

        if (!validar_recaptcha_post('registro_empresa')) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'No pudimos validar reCAPTCHA. Intenta nuevamente.');
            $this->redirigir('/registro');
        }

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $razonSocial = trim((string) ($_POST['razon_social'] ?? ''));
        $nombreComercial = trim((string) ($_POST['nombre_comercial'] ?? ''));
        $identificadorFiscal = trim((string) ($_POST['identificador_fiscal'] ?? ''));
        $correoEmpresa = filter_var(trim((string) ($_POST['correo_empresa'] ?? '')), FILTER_VALIDATE_EMAIL);
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $ciudad = trim((string) ($_POST['ciudad'] ?? ''));
        $pais = trim((string) ($_POST['pais'] ?? ''));
        $nombreAdmin = trim((string) ($_POST['nombre_admin'] ?? ''));
        $correoAdmin = filter_var(trim((string) ($_POST['correo_admin'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $aceptaTerminos = (string) ($_POST['acepta_terminos'] ?? '') === '1';
        $tipoCobro = (string) ($_POST['tipo_cobro'] ?? 'mensual');
        if (!in_array($tipoCobro, ['mensual', 'anual'], true)) {
            $tipoCobro = 'mensual';
        }

        if ($razonSocial === '' || $nombreComercial === '' || $identificadorFiscal === '' || !$correoEmpresa || $nombreAdmin === '' || !$correoAdmin || strlen($password) < 8 || $planId <= 0) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'Completa los campos obligatorios con información válida para crear la empresa.');
            $this->redirigir('/registro');
        }
        if (!$aceptaTerminos) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'Debes aceptar los términos y condiciones para continuar con el registro y cobro del plan.');
            $this->redirigir('/registro');
        }

        $plan = (new Plan())->buscar($planId);
        if (!$plan || ($plan['estado'] ?? '') !== 'activo' || (int) ($plan['visible'] ?? 0) !== 1) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'Selecciona un plan válido para continuar con el registro.');
            $this->redirigir('/registro');
        }

        $empresaModel = new Empresa();
        $usuarioModel = new Usuario();
        $suscripcionModel = new Suscripcion();
        $rolAdminEmpresaId = $usuarioModel->obtenerRolIdPorCodigo('administrador_empresa');

        if (!$rolAdminEmpresaId) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'No se encontró el rol administrador de empresa. Contacta a soporte para habilitar el registro.');
            $this->redirigir('/registro');
        }

        if ($empresaModel->existePorIdentificadorFiscal($identificadorFiscal)) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'Ya existe una empresa registrada con ese RUT/NIT.');
            $this->redirigir('/registro');
        }

        if ($usuarioModel->buscarPorCorreo($correoAdmin)) {
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'El correo del administrador ya está en uso. Usa otro correo para continuar.');
            $this->redirigir('/registro');
        }

        $db = BaseDatos::obtener();
        $empresaId = 0;
        $suscripcionId = 0;

        try {
            $db->beginTransaction();

            $empresaId = $empresaModel->crear([
                'razon_social' => $razonSocial,
                'nombre_comercial' => $nombreComercial,
                'identificador_fiscal' => $identificadorFiscal,
                'correo' => $correoEmpresa,
                'telefono' => $telefono !== '' ? $telefono : null,
                'direccion' => $direccion !== '' ? $direccion : null,
                'ciudad' => $ciudad !== '' ? $ciudad : null,
                'pais' => $pais !== '' ? $pais : null,
                'estado' => 'activa',
                'fecha_activacion' => date('Y-m-d'),
                'plan_id' => $planId,
            ]);

            $usuarioModel->crear([
                'empresa_id' => $empresaId,
                'rol_id' => $rolAdminEmpresaId,
                'nombre' => $nombreAdmin,
                'correo' => $correoAdmin,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'telefono' => null,
                'cargo' => null,
                'biografia' => null,
                'estado' => 'activo',
            ]);

            $diasPruebaRegistro = 30;
            $suscripcionId = $suscripcionModel->crear([
                'empresa_id' => $empresaId,
                'plan_id' => $planId,
                'estado' => 'pendiente',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+' . $diasPruebaRegistro . ' days')),
                'observaciones' => 'Alta inicial desde registro (' . $tipoCobro . '). Prueba gratis de ' . $diasPruebaRegistro . ' días antes del primer cobro.',
                'renovacion_automatica' => 1,
            ]);

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[registro_empresa] Error al crear empresa: ' . $e->getMessage());
            $this->guardarDatosRegistroTemporal($_POST);
            flash('danger', 'No fue posible crear la empresa. Detalle: ' . $e->getMessage());
            $this->redirigir('/registro');
        }

        flash('success', '¡Bienvenido a Vextra! Tu empresa se creó correctamente y ya tienes 30 días de prueba gratis. Te avisaremos antes de que finalice tu prueba.');
        $this->redirigir('/iniciar-sesion');
    }

    public function recuperarContrasena(): void
    {
        $this->vista('autenticacion/recuperar', [], 'publico');
    }

    public function restablecerContrasena(): void
    {
        $this->vista('autenticacion/restablecer', [], 'publico');
    }

    private function guardarDatosRegistroTemporal(array $post): void
    {
        $_SESSION['registro_old'] = [
            'razon_social' => trim((string) ($post['razon_social'] ?? '')),
            'nombre_comercial' => trim((string) ($post['nombre_comercial'] ?? '')),
            'identificador_fiscal' => trim((string) ($post['identificador_fiscal'] ?? '')),
            'correo_empresa' => trim((string) ($post['correo_empresa'] ?? '')),
            'telefono' => trim((string) ($post['telefono'] ?? '')),
            'direccion' => trim((string) ($post['direccion'] ?? '')),
            'ciudad' => trim((string) ($post['ciudad'] ?? '')),
            'pais' => trim((string) ($post['pais'] ?? '')),
            'plan_id' => (int) ($post['plan_id'] ?? 0),
            'tipo_cobro' => (string) ($post['tipo_cobro'] ?? 'mensual'),
            'nombre_admin' => trim((string) ($post['nombre_admin'] ?? '')),
            'correo_admin' => trim((string) ($post['correo_admin'] ?? '')),
            'password' => (string) ($post['password'] ?? ''),
            'acepta_terminos' => (string) ($post['acepta_terminos'] ?? '0'),
        ];
    }

    private function revertirRegistroPendiente(int $empresaId, int $suscripcionId, string $correoAdmin): void
    {
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
                $stmtHistorial = $db->prepare('DELETE FROM historial_suscripciones WHERE suscripcion_id = :suscripcion_id');
                $stmtHistorial->execute(['suscripcion_id' => $suscripcionId]);

                $stmtFlowSuscripcion = $db->prepare('DELETE FROM flow_suscripciones WHERE suscripcion_id = :suscripcion_id');
                $stmtFlowSuscripcion->execute(['suscripcion_id' => $suscripcionId]);

                $stmtFlowPagos = $db->prepare('DELETE FROM flow_pagos WHERE suscripcion_id = :suscripcion_id');
                $stmtFlowPagos->execute(['suscripcion_id' => $suscripcionId]);

                $stmtPagos = $db->prepare('DELETE FROM pagos WHERE suscripcion_id = :suscripcion_id');
                $stmtPagos->execute(['suscripcion_id' => $suscripcionId]);

                $stmtSuscripcion = $db->prepare('DELETE FROM suscripciones WHERE id = :suscripcion_id');
                $stmtSuscripcion->execute(['suscripcion_id' => $suscripcionId]);
            }

            if ($empresaId > 0) {
                $stmtFlowClientes = $db->prepare('DELETE FROM flow_clientes WHERE empresa_id = :empresa_id');
                $stmtFlowClientes->execute(['empresa_id' => $empresaId]);

                $stmtEmpresa = $db->prepare('DELETE FROM empresas WHERE id = :empresa_id');
                $stmtEmpresa->execute(['empresa_id' => $empresaId]);
            }

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[registro_empresa] No se pudo revertir registro pendiente: ' . $e->getMessage());
        }
    }

}
