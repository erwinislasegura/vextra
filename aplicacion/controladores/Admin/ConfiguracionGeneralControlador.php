<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Configuracion;
use Throwable;

class ConfiguracionGeneralControlador extends Controlador
{
    private const CLAVES_CONFIG = [
        'nombre_plataforma',
        'correo_soporte',
        'moneda_defecto',
        'zona_horaria',
        'estado_plataforma',
        'recaptcha_habilitado',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'smtp_notif_host',
        'smtp_notif_port',
        'smtp_notif_usuario',
        'smtp_notif_password',
        'smtp_notif_encryption',
        'smtp_notif_remitente_correo',
        'smtp_notif_remitente_nombre',
        'imap_notif_port',
        'pop3_notif_port',
    ];

    public function index(): void
    {
        $config = $this->obtenerConfiguracionVista();
        $this->vista('admin/configuracion/index', compact('config'), 'admin');
    }

    public function guardar(): void
    {
        validar_csrf();

        $moneda = mb_strtoupper(trim((string) ($_POST['moneda_defecto'] ?? 'CLP')));
        if (!in_array($moneda, ['CLP', 'USD', 'EUR'], true)) {
            $moneda = 'CLP';
        }

        $estado = trim((string) ($_POST['estado_plataforma'] ?? 'activo'));
        if (!in_array($estado, ['activo', 'mantenimiento'], true)) {
            $estado = 'activo';
        }

        $correoSoporte = trim((string) ($_POST['correo_soporte'] ?? ''));
        if ($correoSoporte !== '' && !filter_var($correoSoporte, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'El correo de soporte no es válido.');
            $this->redirigir('/admin/configuracion');
        }

        $data = [
            'nombre_plataforma' => trim((string) ($_POST['nombre_plataforma'] ?? 'Vextra')),
            'correo_soporte' => $correoSoporte,
            'moneda_defecto' => $moneda,
            'zona_horaria' => trim((string) ($_POST['zona_horaria'] ?? 'America/Santiago')),
            'estado_plataforma' => $estado,
            'recaptcha_habilitado' => isset($_POST['recaptcha_habilitado']) ? '1' : '0',
            'recaptcha_site_key' => trim((string) ($_POST['recaptcha_site_key'] ?? '')),
            'recaptcha_secret_key' => trim((string) ($_POST['recaptcha_secret_key'] ?? '')),
            'smtp_notif_host' => trim((string) ($_POST['smtp_notif_host'] ?? 'mail.vextra.cl')),
            'smtp_notif_port' => trim((string) ($_POST['smtp_notif_port'] ?? '465')),
            'smtp_notif_usuario' => trim((string) ($_POST['smtp_notif_usuario'] ?? 'noresponder@vextra.cl')),
            'smtp_notif_encryption' => trim((string) ($_POST['smtp_notif_encryption'] ?? 'ssl')),
            'smtp_notif_remitente_correo' => trim((string) ($_POST['smtp_notif_remitente_correo'] ?? 'noresponder@vextra.cl')),
            'smtp_notif_remitente_nombre' => trim((string) ($_POST['smtp_notif_remitente_nombre'] ?? 'Vextra Notificaciones')),
            'imap_notif_port' => trim((string) ($_POST['imap_notif_port'] ?? '993')),
            'pop3_notif_port' => trim((string) ($_POST['pop3_notif_port'] ?? '995')),
        ];
        $smtpPasswordActual = (string) ((new Configuracion())->obtenerMapa(['smtp_notif_password'])['smtp_notif_password'] ?? 'Tb*Kz{ny{[_E!%,Q');
        $smtpPasswordNueva = trim((string) ($_POST['smtp_notif_password'] ?? ''));
        $data['smtp_notif_password'] = $smtpPasswordNueva !== '' ? $smtpPasswordNueva : $smtpPasswordActual;

        try {
            (new Configuracion())->guardarMultiples($data);
            flash('success', 'Configuración general guardada correctamente.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo guardar la configuración general.');
        }

        $this->redirigir('/admin/configuracion');
    }

    private function obtenerConfiguracionVista(): array
    {
        $configDb = (new Configuracion())->obtenerMapa(self::CLAVES_CONFIG);
        return [
            'nombre_plataforma' => (string) ($configDb['nombre_plataforma'] ?? 'Vextra'),
            'correo_soporte' => (string) ($configDb['correo_soporte'] ?? ''),
            'moneda_defecto' => (string) ($configDb['moneda_defecto'] ?? 'CLP'),
            'zona_horaria' => (string) ($configDb['zona_horaria'] ?? 'America/Santiago'),
            'estado_plataforma' => (string) ($configDb['estado_plataforma'] ?? 'activo'),
            'recaptcha_habilitado' => (string) ($configDb['recaptcha_habilitado'] ?? '0'),
            'recaptcha_site_key' => (string) ($configDb['recaptcha_site_key'] ?? ''),
            'recaptcha_secret_key' => (string) ($configDb['recaptcha_secret_key'] ?? ''),
            'smtp_notif_host' => (string) ($configDb['smtp_notif_host'] ?? 'mail.vextra.cl'),
            'smtp_notif_port' => (string) ($configDb['smtp_notif_port'] ?? '465'),
            'smtp_notif_usuario' => (string) ($configDb['smtp_notif_usuario'] ?? 'noresponder@vextra.cl'),
            'smtp_notif_password' => (string) ($configDb['smtp_notif_password'] ?? 'Tb*Kz{ny{[_E!%,Q'),
            'smtp_notif_encryption' => (string) ($configDb['smtp_notif_encryption'] ?? 'ssl'),
            'smtp_notif_remitente_correo' => (string) ($configDb['smtp_notif_remitente_correo'] ?? 'noresponder@vextra.cl'),
            'smtp_notif_remitente_nombre' => (string) ($configDb['smtp_notif_remitente_nombre'] ?? 'Vextra Notificaciones'),
            'imap_notif_port' => (string) ($configDb['imap_notif_port'] ?? '993'),
            'pop3_notif_port' => (string) ($configDb['pop3_notif_port'] ?? '995'),
        ];
    }
}
