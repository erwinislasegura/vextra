<?php

use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\PlanFuncionalidad;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Configuracion;

function iniciar_sesion_segura(string $nombre): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name($nombre);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function base_path_url(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = str_replace('\\', '/', dirname($scriptName));

    if ($dir === '/' || $dir === '.') {
        return '';
    }

    // Si corre desde /public/index.php, la base pública real es el padre.
    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7) ?: '';
    }

    return rtrim($dir, '/');
}

function url(string $ruta = '/'): string
{
    $base = base_path_url();
    $ruta = '/' . ltrim($ruta, '/');
    if ($ruta === '/index.php') {
        $ruta = '/';
    }
    return ($base === '' ? '' : $base) . $ruta;
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function validar_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function tiene_rol(string|array $roles): bool
{
    $usuario = usuario_actual();
    if (!$usuario) {
        return false;
    }
    $roles = (array) $roles;
    return in_array($usuario['rol_codigo'], $roles, true);
}

function empresa_actual_id(): ?int
{
    $usuario = usuario_actual();
    if (($usuario['rol_codigo'] ?? '') === 'superadministrador') {
        $contextoEmpresa = (int) ($_SESSION['admin_empresa_contexto_id'] ?? 0);
        return $contextoEmpresa > 0 ? $contextoEmpresa : null;
    }

    return usuario_actual()['empresa_id'] ?? null;
}

function resumen_plan_empresa_actual(): ?array
{
    static $cache = null;
    static $resuelto = false;

    if ($resuelto) {
        return $cache;
    }

    $resuelto = true;
    $empresaId = empresa_actual_id();
    if (!$empresaId) {
        return null;
    }

    $cache = (new Suscripcion())->obtenerResumenVigenciaEmpresa($empresaId);
    return $cache;
}

function funcionalidades_plan_empresa_actual(): array
{
    static $cache = null;
    static $resuelto = false;

    if ($resuelto) {
        return $cache ?? [];
    }

    $resuelto = true;
    $cache = [];
    $empresaId = empresa_actual_id();
    if (!$empresaId) {
        return [];
    }

    $planActivo = (new Plan())->obtenerPlanActivoEmpresa($empresaId);
    if (!$planActivo || (int) ($planActivo['plan_id'] ?? 0) <= 0) {
        return [];
    }

    foreach ((new PlanFuncionalidad())->listarActivasPorPlan((int) $planActivo['plan_id']) as $fila) {
        $cache[$fila['codigo_interno']] = $fila;
    }

    return $cache;
}

function plan_tiene_funcionalidad_empresa_actual(string $codigo): bool
{
    $funcionalidades = funcionalidades_plan_empresa_actual();
    return isset($funcionalidades[$codigo]);
}

function nombre_empresa_actual(): ?string
{
    static $nombre = null;
    static $resuelto = false;

    if ($resuelto) {
        return $nombre;
    }

    $resuelto = true;
    $empresaId = empresa_actual_id();
    if (!$empresaId) {
        return null;
    }

    $empresa = (new Empresa())->buscar($empresaId);
    $nombre = $empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? null;
    return $nombre;
}

function obtener_configuracion_recaptcha(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $cache = (new Configuracion())->obtenerMapa([
        'recaptcha_habilitado',
        'recaptcha_site_key',
        'recaptcha_secret_key',
    ]);

    return $cache;
}

function recaptcha_habilitado_publico(): bool
{
    $cfg = obtener_configuracion_recaptcha();
    return (string) ($cfg['recaptcha_habilitado'] ?? '0') === '1';
}

function recaptcha_site_key_publico(): string
{
    $cfg = obtener_configuracion_recaptcha();
    return trim((string) ($cfg['recaptcha_site_key'] ?? ''));
}

function validar_recaptcha_post(string $accionEsperada = 'submit'): bool
{
    if (!recaptcha_habilitado_publico()) {
        return true;
    }

    $cfg = obtener_configuracion_recaptcha();
    $secret = trim((string) ($cfg['recaptcha_secret_key'] ?? ''));
    $siteKey = trim((string) ($cfg['recaptcha_site_key'] ?? ''));
    $token = trim((string) ($_POST['g-recaptcha-response'] ?? ''));

    // Evita bloquear formularios públicos por configuraciones incompletas.
    if ($secret === '' || $siteKey === '') {
        error_log('[recaptcha] Validación omitida por configuración incompleta (site/secret key vacía).');
        return true;
    }

    if ($token === '') {
        return false;
    }

    $postFields = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    if ($ch === false) {
        return false;
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $respuesta = curl_exec($ch);
    if ($respuesta === false) {
        error_log('[recaptcha] Error al consultar siteverify: ' . curl_error($ch));
        curl_close($ch);
        return true;
    }

    curl_close($ch);
    $json = json_decode($respuesta, true);
    if (!is_array($json) || !($json['success'] ?? false)) {
        return false;
    }

    $action = strtolower(trim((string) ($json['action'] ?? '')));
    $accionEsperada = strtolower(trim($accionEsperada));

    if ($action !== '' && $accionEsperada !== '' && $action !== $accionEsperada) {
        return false;
    }

    if (!array_key_exists('score', $json)) {
        return true;
    }

    $score = (float) $json['score'];
    return $score >= 0.5;
}
