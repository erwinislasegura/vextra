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
        $this->vistaPublica('publico/inicio', ['planes' => $planes], 'inicio');
    }

    public function caracteristicas(): void
    {
        $this->vistaPublica('publico/caracteristicas', [], 'caracteristicas');
    }

    public function planes(): void
    {
        $planes = (new Plan())->listar(true);
        $planes = $this->agregarFuncionalidadesPlanes($planes);
        $this->vistaPublica('publico/planes', ['planes' => $planes], 'planes');
    }

    public function contacto(): void
    {
        $this->vistaPublica('publico/contacto', [], 'contacto');
    }

    public function preguntasFrecuentes(): void
    {
        $this->vistaPublica('publico/preguntas_frecuentes', [], 'faq');
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
        $this->vistaPublica('publico/contratar', ['plan' => $plan], 'contratar');
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

        $this->vistaPublica('publico/cotizacion_publica', compact('cotizacion', 'token'), 'cotizacion_publica');
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

    private function vistaPublica(string $vista, array $data, string $pagina): void
    {
        $this->vista($vista, array_merge($this->obtenerSeoPorPagina($pagina), $data), 'publico');
    }

    private function obtenerSeoPorPagina(string $pagina): array
    {
        $seo = [
            'inicio' => [
                'meta_title' => 'Inicio | Vextra - Sistema de cotizaciones para empresas',
                'meta_description' => 'Vextra te ayuda a ordenar cotizaciones, clientes y ventas en una plataforma cloud para mejorar productividad y cierres comerciales.',
                'meta_keywords' => 'inicio vextra, sistema de cotizaciones, software comercial, gestión de ventas',
            ],
            'caracteristicas' => [
                'meta_title' => 'Características | Vextra - Funcionalidades del sistema',
                'meta_description' => 'Conoce las funcionalidades de Vextra para cotizar más rápido, gestionar clientes, productos y seguimiento comercial en un solo lugar.',
                'meta_keywords' => 'características vextra, funcionalidades software cotizaciones, gestión comercial',
            ],
            'planes' => [
                'meta_title' => 'Planes y precios | Vextra',
                'meta_description' => 'Revisa los planes y precios de Vextra, compara opciones mensuales y anuales y elige la mejor alternativa para tu empresa.',
                'meta_keywords' => 'planes vextra, precios software cotizaciones, plan mensual, plan anual',
            ],
            'contacto' => [
                'meta_title' => 'Contacto comercial | Vextra',
                'meta_description' => 'Habla con el equipo comercial de Vextra para resolver dudas de planes, implementación y soporte para clientes actuales.',
                'meta_keywords' => 'contacto vextra, asesoría comercial, soporte clientes',
            ],
            'faq' => [
                'meta_title' => 'Preguntas frecuentes | Vextra',
                'meta_description' => 'Resuelve dudas frecuentes sobre Vextra, su funcionamiento, implementación, cotizaciones y administración comercial.',
                'meta_keywords' => 'faq vextra, preguntas frecuentes software cotizaciones',
            ],
            'contratar' => [
                'meta_title' => 'Contratar plan | Vextra',
                'meta_description' => 'Inicia la contratación de tu plan Vextra y comienza a gestionar cotizaciones y ventas con una plataforma profesional.',
                'meta_keywords' => 'contratar vextra, activar plan, software cotizaciones',
            ],
            'cotizacion_publica' => [
                'meta_title' => 'Cotización en línea | Vextra',
                'meta_description' => 'Revisa el detalle de tu cotización en línea, incluyendo productos, condiciones comerciales y estado de aprobación.',
                'meta_keywords' => 'cotización online, seguimiento cotización, aprobación cliente',
            ],
        ];

        return $seo[$pagina] ?? [
            'meta_title' => 'Vextra | Sistema de cotizaciones para empresas',
            'meta_description' => 'Vextra es un sistema de cotizaciones para empresas que ayuda a vender más con procesos comerciales ordenados.',
            'meta_keywords' => 'sistema de cotizaciones, software de cotizaciones, cotizaciones para empresas',
        ];
    }
}
