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
            'descripcion' => mb_substr(trim((string) ($_POST['descripcion'] ?? '')), 0, 280),
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

    public function dominioCatalogo(): void
    {
        $empresaId = empresa_actual_id();
        $modelo = new Empresa();
        $empresa = $modelo->obtenerConfiguracion($empresaId);

        if (!$empresa) {
            flash('danger', 'No se encontró la empresa para actualizar.');
            $this->redirigir('/app/panel');
        }

        $incluyeDominioCatalogo = plan_tiene_funcionalidad_empresa_actual('catalogo_dominio_personalizado');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validar_csrf();
            $accion = trim((string) ($_POST['accion'] ?? 'guardar'));

            $catalogoDominioNormalizado = $this->normalizarDominioCatalogo((string) ($_POST['catalogo_dominio'] ?? ''));
            if ($catalogoDominioNormalizado === false) {
                flash('danger', 'Ingresa un dominio válido para el catálogo (ej: tienda.tudominio.com).');
                $this->redirigir('/app/configuracion/dominio-catalogo');
            }

            if ($accion === 'verificar_dns') {
                if ($catalogoDominioNormalizado === '') {
                    flash('danger', 'Ingresa un dominio para verificar DNS.');
                    $this->redirigir('/app/configuracion/dominio-catalogo');
                }

                $diagnostico = $this->diagnosticarDominioCatalogo($catalogoDominioNormalizado);
                if ((bool) ($diagnostico['coincide'] ?? false)) {
                    flash('success', 'DNS verificado correctamente: el dominio apunta al servidor de Vextra.');
                } else {
                    $ipsDominioTxt = implode(', ', $diagnostico['ips_dominio'] ?? []) ?: 'sin A';
                    $ipsEsperadasTxt = implode(', ', $diagnostico['ips_esperadas'] ?? []) ?: 'sin IP esperada';
                    flash('warning', 'DNS aún no coincide. Dominio: ' . $ipsDominioTxt . ' | Esperado: ' . $ipsEsperadasTxt . '.');
                }
                $this->redirigir('/app/configuracion/dominio-catalogo');
            }

            if (!$incluyeDominioCatalogo) {
                flash('warning', 'Tu plan actual no incluye dominio personalizado para catálogo.');
                $this->redirigir('/app/configuracion/dominio-catalogo');
            }

            if ($catalogoDominioNormalizado !== '') {
                $empresaConDominio = $modelo->buscarPorCatalogoDominio($catalogoDominioNormalizado);
                if ($empresaConDominio && (int) ($empresaConDominio['id'] ?? 0) !== $empresaId) {
                    flash('danger', 'Ese dominio ya está siendo usado por otra empresa.');
                    $this->redirigir('/app/configuracion/dominio-catalogo');
                }
            }

            $modelo->actualizarCatalogoDominio($empresaId, $catalogoDominioNormalizado !== '' ? $catalogoDominioNormalizado : null);

            flash('success', 'Dominio de catálogo actualizado correctamente.');
            $this->redirigir('/app/configuracion/dominio-catalogo');
        }

        $catalogoDominio = trim((string) ($empresa['catalogo_dominio'] ?? ''));
        $documentRootActual = trim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $diagnosticoDns = $catalogoDominio !== '' ? $this->diagnosticarDominioCatalogo($catalogoDominio) : null;
        $this->vista('empresa/configuracion/dominio_catalogo', compact('empresa', 'catalogoDominio', 'incluyeDominioCatalogo', 'documentRootActual', 'diagnosticoDns'), 'empresa');
    }

    private function diagnosticarDominioCatalogo(string $dominio): array
    {
        $ipsDominio = $this->resolverIpsDominio($dominio);
        $ipsEsperadas = $this->resolverIpsEsperadasServidor();

        return [
            'dominio' => $dominio,
            'ips_dominio' => $ipsDominio,
            'ips_esperadas' => $ipsEsperadas,
            'coincide' => $ipsDominio !== [] && $ipsEsperadas !== [] && array_intersect($ipsDominio, $ipsEsperadas) !== [],
        ];
    }

    private function resolverIpsDominio(string $dominio): array
    {
        $registros = @dns_get_record($dominio, DNS_A);
        if (!is_array($registros)) {
            return [];
        }

        $ips = [];
        foreach ($registros as $registro) {
            $ip = trim((string) ($registro['ip'] ?? ''));
            if ($ip !== '') {
                $ips[] = $ip;
            }
        }

        $ips = array_values(array_unique($ips));
        sort($ips);
        return $ips;
    }

    private function resolverIpsEsperadasServidor(): array
    {
        $hosts = [];

        $appUrl = trim((string) ($_ENV['APP_URL'] ?? ''));
        $hostApp = parse_url($appUrl, PHP_URL_HOST);
        if (is_string($hostApp) && $hostApp !== '') {
            $hosts[] = $hostApp;
        }

        $hostActual = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($hostActual !== '') {
            $hosts[] = explode(':', $hostActual, 2)[0];
        }

        $ips = [];
        foreach (array_unique($hosts) as $host) {
            foreach ($this->resolverIpsDominio($host) as $ip) {
                $ips[] = $ip;
            }
        }

        $serverAddr = trim((string) ($_SERVER['SERVER_ADDR'] ?? ''));
        if ($serverAddr !== '') {
            $ips[] = $serverAddr;
        }

        $ips = array_values(array_unique($ips));
        sort($ips);
        return $ips;
    }

    private function normalizarDominioCatalogo(string $valor): string|false
    {
        $dominio = trim(mb_strtolower($valor));
        if ($dominio === '') {
            return '';
        }

        $dominio = preg_replace('#^https?://#i', '', $dominio) ?? $dominio;
        $dominio = strtok($dominio, '/?#') ?: $dominio;
        $dominio = trim($dominio, '.');

        if ($dominio === '' || str_contains($dominio, ' ')) {
            return false;
        }

        if ((bool) preg_match('/^([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))+$/i', $dominio) !== true) {
            return false;
        }

        return $dominio;
    }

    public function logoEmpresa(): void
    {
        $empresaId = empresa_actual_id();
        $empresa = (new Empresa())->obtenerConfiguracion((int) $empresaId);
        $logo = trim((string) ($empresa['logo'] ?? ''));

        if ($logo === '') {
            http_response_code(404);
            exit('Logo no configurado.');
        }

        if (preg_match('/^https?:\/\//i', $logo) === 1) {
            header('Location: ' . $logo, true, 302);
            return;
        }

        $logo = str_replace('\\', '/', $logo);
        if (!str_starts_with($logo, '/')) {
            $logo = '/' . ltrim($logo, '/');
        }

        if (str_starts_with($logo, '/public/uploads/')) {
            $logo = '/uploads/' . ltrim(substr($logo, 16), '/');
        } elseif (str_starts_with($logo, '/aplicacion/public/uploads/')) {
            $logo = '/uploads/' . ltrim(substr($logo, 26), '/');
        }

        $raiz = dirname(__DIR__, 4);
        $candidatas = [
            $raiz . $logo,
            $raiz . '/public' . $logo,
            $raiz . '/aplicacion/public' . $logo,
        ];

        foreach ($candidatas as $ruta) {
            if (!is_file($ruta)) {
                continue;
            }

            $mime = (string) (mime_content_type($ruta) ?: 'application/octet-stream');
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($ruta));
            header('Cache-Control: private, max-age=300');
            readfile($ruta);
            return;
        }

        http_response_code(404);
        exit('Logo no encontrado.');
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

        $nombreFinal = 'logo_empresa_' . empresa_actual_id() . '_' . date('YmdHis') . '.' . $extension;
        $raizProyecto = dirname(__DIR__, 4);
        $directorios = [
            $raizProyecto . '/uploads/logos',
            $raizProyecto . '/public/uploads/logos',
        ];

        foreach ($directorios as $directorio) {
            if (!is_dir($directorio)) {
                mkdir($directorio, 0775, true);
            }
        }

        $directorioPrincipal = null;
        foreach ($directorios as $directorio) {
            if (is_dir($directorio) && is_writable($directorio)) {
                $directorioPrincipal = $directorio;
                break;
            }
        }

        if ($directorioPrincipal === null) {
            flash('danger', 'No hay permisos para guardar el logo en el servidor.');
            $this->redirigir('/app/configuracion');
        }

        $rutaFinal = $directorioPrincipal . '/' . $nombreFinal;
        if (!move_uploaded_file($tmp, $rutaFinal)) {
            flash('danger', 'No se pudo guardar el archivo del logo.');
            $this->redirigir('/app/configuracion');
        }

        foreach ($directorios as $directorioReplica) {
            $rutaReplica = $directorioReplica . '/' . $nombreFinal;
            if ($rutaReplica === $rutaFinal) {
                continue;
            }

            if (is_dir($directorioReplica) && is_writable($directorioReplica)) {
                @copy($rutaFinal, $rutaReplica);
            }
        }

        return '/uploads/logos/' . $nombreFinal;
    }
}
