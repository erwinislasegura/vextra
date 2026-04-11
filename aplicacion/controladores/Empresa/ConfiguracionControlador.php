<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Servicios\ServicioAlertaStock;
use Aplicacion\Servicios\ServicioCorreo;

class ConfiguracionControlador extends Controlador
{
    public function index(): void
    {
        $empresaId = empresa_actual_id();
        $empresa = (new Empresa())->obtenerConfiguracion($empresaId);
        $adminEmpresa = (new Usuario())->obtenerAdministradorPrincipalPorEmpresa($empresaId);

        if (!$empresa) {
            flash('danger', 'No se encontró la configuración de la empresa.');
            $this->redirigir('/app/panel');
        }

        $this->vista('empresa/configuracion/index', compact('empresa', 'adminEmpresa'), 'empresa');
    }

    public function guardar(): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $modelo = new Empresa();
        $empresa = $modelo->obtenerConfiguracion($empresaId);

        if (!$empresa) {
            flash('danger', 'No se encontró la empresa para actualizar.');
            $this->redirigir('/app/configuracion');
        }

        $logoActual = (string) ($empresa['logo'] ?? '');
        $logoNuevo = $this->guardarLogoEmpresa($logoActual);

        $imapPasswordActual = (string) ($empresa['imap_password'] ?? '');
        $imapPassword = trim((string) ($_POST['imap_password'] ?? ''));
        if ($imapPassword === '') {
            $imapPassword = $imapPasswordActual;
        }

        $correoEmpresa = mb_strtolower(trim((string) ($_POST['correo'] ?? '')));
        if (!filter_var($correoEmpresa, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Ingresa un correo principal válido para la empresa.');
            $this->redirigir('/app/configuracion');
        }

        $modelo->actualizarConfiguracion($empresaId, [
            'razon_social' => trim((string) ($_POST['razon_social'] ?? '')),
            'nombre_comercial' => trim((string) ($_POST['nombre_comercial'] ?? '')),
            'identificador_fiscal' => trim((string) ($_POST['identificador_fiscal'] ?? '')),
            'correo' => $correoEmpresa,
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'direccion' => trim((string) ($_POST['direccion'] ?? '')),
            'ciudad' => trim((string) ($_POST['ciudad'] ?? '')),
            'pais' => trim((string) ($_POST['pais'] ?? '')),
            'logo' => $logoNuevo,
            'imap_host' => trim((string) ($_POST['imap_host'] ?? '')),
            'imap_port' => (int) ($_POST['imap_port'] ?? 0) ?: null,
            'imap_encryption' => trim((string) ($_POST['imap_encryption'] ?? 'tls')),
            'imap_usuario' => trim((string) ($_POST['imap_usuario'] ?? '')),
            'imap_password' => $imapPassword,
            'imap_remitente_correo' => trim((string) ($_POST['imap_remitente_correo'] ?? '')),
            'imap_remitente_nombre' => trim((string) ($_POST['imap_remitente_nombre'] ?? '')),
        ]);

        $adminEmpresa = (new Usuario())->obtenerAdministradorPrincipalPorEmpresa($empresaId);
        $nombreAdmin = trim((string) ($_POST['nombre_admin'] ?? ''));
        $correoAdmin = mb_strtolower(trim((string) ($_POST['correo_admin'] ?? '')));
        if ($correoAdmin !== '' && !filter_var($correoAdmin, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'El correo del administrador no es válido.');
            $this->redirigir('/app/configuracion');
        }
        if ($adminEmpresa && $nombreAdmin !== '' && $correoAdmin !== '' && filter_var($correoAdmin, FILTER_VALIDATE_EMAIL)) {
            $correoEnUso = (new Usuario())->buscarPorCorreo($correoAdmin);
            if (!$correoEnUso || (int) ($correoEnUso['id'] ?? 0) === (int) $adminEmpresa['id']) {
                (new Usuario())->actualizarCredencialesAdmin((int) $adminEmpresa['id'], $correoAdmin, $nombreAdmin);
            }
        }

        flash('success', 'Configuración actualizada correctamente.');
        $this->redirigir('/app/configuracion');
    }



    public function correosStock(): void
    {
        $this->validarPermisoAlertas();
        $empresaId = (int) empresa_actual_id();
        $servicio = new ServicioAlertaStock();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validar_csrf();
            $accion = trim((string) ($_POST['accion'] ?? 'guardar'));
            $config = [
                'activar_alerta_stock_bajo' => isset($_POST['activar_alerta_stock_bajo']) ? '1' : '0',
                'activar_alerta_stock_critico' => isset($_POST['activar_alerta_stock_critico']) ? '1' : '0',
                'destinatarios_alerta_stock' => trim((string) ($_POST['destinatarios_alerta_stock'] ?? '')),
                'asunto_stock_bajo' => trim((string) ($_POST['asunto_stock_bajo'] ?? '')),
                'asunto_stock_critico' => trim((string) ($_POST['asunto_stock_critico'] ?? '')),
                'plantilla_html_stock_bajo' => trim((string) ($_POST['plantilla_html_stock_bajo'] ?? '')),
                'plantilla_html_stock_critico' => trim((string) ($_POST['plantilla_html_stock_critico'] ?? '')),
            ];

            if ($accion === 'guardar') {
                $servicio->guardarConfiguracion($empresaId, $config);
                flash('success', 'Configuración de alertas de stock guardada correctamente.');
            }

            if ($accion === 'enviar_prueba') {
                $destino = trim((string) ($_POST['correo_prueba'] ?? ''));
                if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
                    flash('danger', 'Correo de prueba inválido.');
                } else {
                    $variables = $this->variablesPruebaStock();
                    $asunto = strtr($config['asunto_stock_bajo'], $variables);
                    $html = $servicio->vistaPrevia($config['plantilla_html_stock_bajo'], $variables);
                    (new ServicioCorreo())->enviarConEmpresa($empresaId, $destino, $asunto, 'alerta_stock_prueba', ['html' => $html, 'variables' => $variables]);
                    flash('success', 'Correo de prueba enviado (registrado en logs).');
                }
            }
        }

        $configuracion = $servicio->obtenerConfiguracion($empresaId);
        $variables = $servicio->variablesAyuda();
        $previewVars = $this->variablesPruebaStock();
        $vistaPreviaBajo = $servicio->vistaPrevia($configuracion['plantilla_html_stock_bajo'], $previewVars);
        $vistaPreviaCritico = $servicio->vistaPrevia($configuracion['plantilla_html_stock_critico'], $previewVars);
        $asuntoPreviaBajo = strtr($configuracion['asunto_stock_bajo'], $previewVars);
        $asuntoPreviaCritico = strtr($configuracion['asunto_stock_critico'], $previewVars);

        $this->vista('empresa/configuracion/correos_stock', compact('configuracion', 'variables', 'vistaPreviaBajo', 'vistaPreviaCritico', 'asuntoPreviaBajo', 'asuntoPreviaCritico'), 'empresa');
    }

    private function variablesPruebaStock(): array
    {
        $usuario = usuario_actual();
        $empresa = (new Empresa())->buscar((int) empresa_actual_id()) ?: [];
        return [
            '{empresa}' => (string) ($empresa['nombre_comercial'] ?? 'Empresa Demo'),
            '{producto}' => 'Producto demo',
            '{codigo}' => 'P-001',
            '{stock_actual}' => '5.00',
            '{stock_minimo}' => '10.00',
            '{stock_critico}' => '3.00',
            '{fecha}' => date('Y-m-d H:i:s'),
            '{usuario}' => (string) ($usuario['nombre'] ?? 'Usuario demo'),
        ];
    }

    private function validarPermisoAlertas(): void
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            http_response_code(403);
            exit('No autorizado');
        }

        if (($usuario['rol_codigo'] ?? '') === 'superadministrador' || ($usuario['rol_codigo'] ?? '') === 'administrador_empresa') {
            return;
        }

        http_response_code(403);
        exit('No tienes permisos para configurar alertas de stock.');
    }

    private function guardarLogoEmpresa(string $logoActual): string
    {
        if (!isset($_FILES['logo']) || (int) ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $logoActual;
        }

        if ((int) ($_FILES['logo']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            flash('danger', 'No se pudo subir el logo. Intenta nuevamente.');
            $this->redirigir('/app/configuracion');
        }

        $nombre = (string) ($_FILES['logo']['name'] ?? '');
        $tmp = (string) ($_FILES['logo']['tmp_name'] ?? '');
        $tamano = (int) ($_FILES['logo']['size'] ?? 0);

        if ($tamano > 2 * 1024 * 1024) {
            flash('danger', 'El logo supera el tamaño permitido (2MB).');
            $this->redirigir('/app/configuracion');
        }

        $extension = mb_strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        if (!in_array($extension, $permitidas, true)) {
            flash('danger', 'Formato de logo no permitido. Usa PNG, JPG, WEBP o SVG.');
            $this->redirigir('/app/configuracion');
        }

        $directorio = dirname(__DIR__, 4) . '/uploads/logos';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $nombreFinal = 'logo_empresa_' . empresa_actual_id() . '_' . date('YmdHis') . '.' . $extension;
        $rutaFinal = $directorio . '/' . $nombreFinal;
        if (!move_uploaded_file($tmp, $rutaFinal)) {
            flash('danger', 'No se pudo guardar el archivo del logo.');
            $this->redirigir('/app/configuracion');
        }

        return '/uploads/logos/' . $nombreFinal;
    }
}
