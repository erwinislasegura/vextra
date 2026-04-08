<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Configuracion;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\LogCorreo;

class ServicioCorreo
{
    public function enviar(string $destinatario, string $asunto, string $plantilla, array $datos = []): bool
    {
        $destinatario = trim($destinatario);
        if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            (new LogCorreo())->registrar([
                'destinatario' => $destinatario,
                'asunto' => $asunto,
                'plantilla' => $plantilla,
                'payload' => json_encode($datos, JSON_UNESCAPED_UNICODE),
                'estado' => 'error',
            ]);
            return false;
        }

        $smtp = [];
        if (isset($datos['smtp_empresa']) && is_array($datos['smtp_empresa'])) {
            $smtp = $datos['smtp_empresa'];
        } elseif (isset($datos['smtp_global']) && is_array($datos['smtp_global'])) {
            $smtp = $datos['smtp_global'];
        }

        $remitenteCorreo = trim((string) ($smtp['remitente_correo'] ?? ''));
        $remitenteNombre = trim((string) ($smtp['remitente_nombre'] ?? 'Vextra'));
        if ($remitenteCorreo === '' || !filter_var($remitenteCorreo, FILTER_VALIDATE_EMAIL)) {
            $remitenteCorreo = 'noresponder@vextra.cl';
        }

        $html = (string) ($datos['html'] ?? '');
        if ($html === '' && isset($datos['mensaje_html'])) {
            $html = (string) $datos['mensaje_html'];
        }
        if ($html === '') {
            $html = '<p>Notificación automática de Vextra.</p>';
        }
        if (!preg_match('/<\\s*html[\\s>]/i', $html)) {
            $html = '<!doctype html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;">' . $html . '</body></html>';
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $remitenteNombre . ' <' . $remitenteCorreo . '>',
            'Reply-To: ' . $remitenteCorreo,
        ];

        $enviado = @mail($destinatario, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $html, implode("\r\n", $headers));

        $log = new LogCorreo();
        $log->registrar([
            'destinatario' => $destinatario,
            'asunto' => $asunto,
            'plantilla' => $plantilla,
            'payload' => json_encode($datos, JSON_UNESCAPED_UNICODE),
            'estado' => $enviado ? 'enviado' : 'error',
        ]);

        return $enviado;
    }

    public function enviarConEmpresa(int $empresaId, string $destinatario, string $asunto, string $plantilla, array $datos = []): bool
    {
        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $smtpEmpresa = [
            'host' => (string) ($empresa['imap_host'] ?? ''),
            'puerto' => (string) ($empresa['imap_port'] ?? ''),
            'usuario' => (string) ($empresa['imap_usuario'] ?? ''),
            'encryption' => (string) ($empresa['imap_encryption'] ?? ''),
            'remitente_correo' => trim((string) ($empresa['imap_remitente_correo'] ?? '')) !== ''
                ? trim((string) ($empresa['imap_remitente_correo'] ?? ''))
                : trim((string) ($empresa['correo'] ?? '')),
            'remitente_nombre' => trim((string) ($empresa['imap_remitente_nombre'] ?? '')) !== ''
                ? trim((string) ($empresa['imap_remitente_nombre'] ?? ''))
                : trim((string) ($empresa['nombre_comercial'] ?? '')),
        ];

        $datos['smtp_empresa'] = $smtpEmpresa;
        $datos['empresa_id'] = $empresaId;

        return $this->enviar($destinatario, $asunto, $plantilla, $datos);
    }

    public function enviarNotificacionCliente(string $destinatario, string $asunto, string $plantilla, array $datos = []): bool
    {
        $config = (new Configuracion())->obtenerMapa([
            'smtp_notif_host',
            'smtp_notif_port',
            'smtp_notif_usuario',
            'smtp_notif_password',
            'smtp_notif_encryption',
            'smtp_notif_remitente_correo',
            'smtp_notif_remitente_nombre',
        ]);

        $datos['smtp_global'] = [
            'host' => (string) ($config['smtp_notif_host'] ?? 'mail.vextra.cl'),
            'puerto' => (string) ($config['smtp_notif_port'] ?? '465'),
            'usuario' => (string) ($config['smtp_notif_usuario'] ?? 'noresponder@vextra.cl'),
            'password' => (string) ($config['smtp_notif_password'] ?? 'Tb*Kz{ny{[_E!%,Q'),
            'encryption' => (string) ($config['smtp_notif_encryption'] ?? 'ssl'),
            'remitente_correo' => 'noresponder@vextra.cl',
            'remitente_nombre' => (string) ($config['smtp_notif_remitente_nombre'] ?? 'Vextra Notificaciones'),
        ];

        return $this->enviar($destinatario, $asunto, $plantilla, $datos);
    }
}
