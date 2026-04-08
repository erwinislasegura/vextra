<?php

namespace Aplicacion\Controladores\Publico;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\PlanFuncionalidad;
use Aplicacion\Servicios\ServicioCorreo;

class PublicoControlador extends Controlador
{
    public function inicio(): void
    {
        $planes = (new Plan())->listar(true);
        $planes = $this->agregarFuncionalidadesPlanes($planes);
        $this->vista('publico/inicio', ['planes' => $planes], 'publico');
    }

    public function caracteristicas(): void
    {
        $this->vista('publico/caracteristicas', [], 'publico');
    }

    public function planes(): void
    {
        $planes = (new Plan())->listar(true);
        $planes = $this->agregarFuncionalidadesPlanes($planes);
        $this->vista('publico/planes', ['planes' => $planes], 'publico');
    }

    public function contacto(): void
    {
        $this->vista('publico/contacto', [], 'publico');
    }

    public function preguntasFrecuentes(): void
    {
        $this->vista('publico/preguntas_frecuentes', [], 'publico');
    }

    public function enviarContacto(): void
    {
        validar_csrf();

        if (!validar_recaptcha_post('contacto_landing')) {
            flash('danger', 'No pudimos validar reCAPTCHA. Intenta nuevamente.');
            $this->redirigir('/contacto');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $empresa = trim((string) ($_POST['empresa'] ?? ''));
        $tipoContacto = (string) ($_POST['tipo_contacto'] ?? 'prospecto');
        if (!in_array($tipoContacto, ['prospecto', 'cliente_actual'], true)) {
            $tipoContacto = 'prospecto';
        }
        $motivoConsulta = trim((string) ($_POST['motivo_consulta'] ?? ''));
        $mensaje = trim($_POST['mensaje'] ?? '');

        if ($nombre === '' || !$correo || $mensaje === '' || $motivoConsulta === '') {
            flash('danger', 'Completa todos los campos del formulario de contacto.');
            $this->redirigir('/contacto');
        }

        $html = '<h2>Nuevo lead desde landing</h2>'
            . '<p><strong>Nombre:</strong> ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Correo:</strong> ' . htmlspecialchars((string) $correo, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Teléfono:</strong> ' . htmlspecialchars($telefono !== '' ? $telefono : 'No informado', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Empresa:</strong> ' . htmlspecialchars($empresa !== '' ? $empresa : 'No informada', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Tipo de contacto:</strong> ' . htmlspecialchars($tipoContacto === 'cliente_actual' ? 'Cliente actual' : 'Posible cliente', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Motivo de consulta:</strong> ' . htmlspecialchars($motivoConsulta, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>';

        (new ServicioCorreo())->enviar(
            'contacto@vextra.cl',
            'Nuevo lead desde landing',
            'landing_contacto',
            [
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'tipo_contacto' => $tipoContacto,
                'motivo_consulta' => $motivoConsulta,
                'mensaje' => $mensaje,
                'html' => $html,
            ]
        );

        flash('success', 'Gracias por escribirnos. Te contactaremos pronto.');
        $this->redirigir('/contacto');
    }

    public function contratar(string $planSlug): void
    {
        $plan = (new Plan())->buscarPublicoPorSlug($planSlug);
        if (!$plan) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }
        $this->vista('publico/contratar', ['plan' => $plan], 'publico');
    }

    private function agregarFuncionalidadesPlanes(array $planes): array
    {
        $planFuncionalidadModelo = new PlanFuncionalidad();
        foreach ($planes as &$plan) {
            $plan['funcionalidades'] = $planFuncionalidadModelo->listarActivasPorPlan((int) $plan['id']);
        }
        unset($plan);

        return $planes;
    }

    public function verCotizacionPublica(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $this->vista('publico/cotizacion_publica', compact('cotizacion', 'token'), 'publico');
    }

    public function imprimirCotizacionPublica(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = (new Empresa())->buscar((int) ($cotizacion['empresa_id'] ?? 0));
        $listaAplicada = [];
        $esVistaPublica = true;
        $this->vista('empresa/cotizaciones/imprimir', compact('cotizacion', 'empresa', 'listaAplicada', 'esVistaPublica', 'token'), 'impresion');
    }

    public function registrarDecisionCotizacion(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['aprobada', 'rechazada'], true)) {
            flash('danger', 'Decisión no válida.');
            $this->redirigir('/cotizacion/publica/' . $token);
        }

        $nombreFirmante = trim((string) ($_POST['nombre_firmante_cliente'] ?? ''));
        $firmaCliente = trim((string) ($_POST['firma_cliente'] ?? ''));

        if ($decision === 'aprobada') {
            if ($nombreFirmante === '' || $firmaCliente === '') {
                flash('danger', 'Para aprobar debes ingresar el nombre y la firma del cliente.');
                $this->redirigir('/cotizacion/publica/' . $token);
            }

            if (strpos($firmaCliente, 'data:image/png;base64,') !== 0) {
                flash('danger', 'La firma enviada no tiene un formato válido.');
                $this->redirigir('/cotizacion/publica/' . $token);
            }
        } else {
            $nombreFirmante = null;
            $firmaCliente = null;
        }

        (new Cotizacion())->actualizarDecisionPublica((int) $cotizacion['empresa_id'], (int) $cotizacion['id'], [
            'estado' => $decision,
            'observaciones' => (string) ($cotizacion['observaciones'] ?? ''),
            'terminos_condiciones' => (string) ($cotizacion['terminos_condiciones'] ?? ''),
            'fecha_vencimiento' => (string) ($cotizacion['fecha_vencimiento'] ?? date('Y-m-d')),
            'firma_cliente' => $firmaCliente,
            'nombre_firmante_cliente' => $nombreFirmante,
            'fecha_aprobacion_cliente' => $decision === 'aprobada' ? date('Y-m-d H:i:s') : null,
        ]);

        $gestion = new GestionComercial();
        $empresaId = (int) ($cotizacion['empresa_id'] ?? 0);
        $cotizacionId = (int) ($cotizacion['id'] ?? 0);
        if (!$gestion->existeAprobacionRegistrada($empresaId, $cotizacionId, $decision)) {
            $gestion->crear('aprobaciones_cotizacion', [
                'empresa_id' => $empresaId,
                'cotizacion_id' => $cotizacionId,
                'monto' => (float) ($cotizacion['total'] ?? 0),
                'motivo' => $decision === 'aprobada' ? 'Aprobación desde enlace público' : 'Rechazo desde enlace público',
                'solicitante' => $decision === 'aprobada' ? $nombreFirmante : ((string) ($cotizacion['cliente'] ?? 'Cliente')),
                'aprobador' => 'Cliente (portal público)',
                'estado' => $decision,
                'fecha_aprobacion' => date('Y-m-d'),
                'observaciones' => $decision === 'aprobada'
                    ? 'Cliente aprobó desde enlace público con firma digital.'
                    : 'Cliente rechazó desde enlace público.',
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        flash('success', $decision === 'aprobada'
            ? 'Has aceptado la cotización correctamente y registrado tu firma.'
            : 'Has rechazado la cotización correctamente.');
        $this->redirigir('/cotizacion/publica/' . $token);
    }
}
