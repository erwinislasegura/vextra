<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\FlowConfiguracion;
use RuntimeException;

class FlowApiService
{
    private FlowFirmaService $firmaService;
    private FlowLogService $logService;

    public function __construct()
    {
        $this->firmaService = new FlowFirmaService();
        $this->logService = new FlowLogService();
    }

    public function configuracionActiva(): array
    {
        $config = (new FlowConfiguracion())->obtener();
        if (!$config || (int) ($config['activo'] ?? 0) !== 1) {
            throw new RuntimeException('Integración Flow inactiva.');
        }
        $apiKey = $this->normalizarCredencial((string) ($config['api_key'] ?? ''));
        $secret = $this->normalizarCredencial($this->desencriptarSecret((string) ($config['secret_key_enc'] ?? '')));
        if ($apiKey === '' || $secret === '') {
            throw new RuntimeException('Credenciales Flow incompletas.');
        }

        $config['api_key'] = $apiKey;
        $config['secret_key'] = $secret;
        $config['base_url_real'] = $this->resolverBaseUrl($config);
        return $config;
    }

    public function post(string $endpoint, array $params): array
    {
        $config = $this->configuracionActiva();
        $payload = array_merge(['apiKey' => $config['api_key']], $params);
        $payload['s'] = $this->firmaService->firmar($payload, $config['secret_key']);
        $url = rtrim((string) $config['base_url_real'], '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('Error de conexión Flow: ' . curl_error($ch));
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $parsed = json_decode($response, true);
        if (!is_array($parsed)) {
            throw new RuntimeException('Respuesta inválida de Flow.');
        }
        if ($httpCode >= 400) {
            $reintentoEmail = $this->reintentarSiEmailInvalido('POST', $endpoint, $payload, $config, $httpCode, $parsed);
            if ($reintentoEmail !== null) {
                return $reintentoEmail;
            }
            $reintento = $this->reintentarSiApiKeyNoCoincide('POST', $endpoint, $payload, $config, $httpCode, $parsed);
            if ($reintento !== null) {
                return $reintento;
            }
            $this->logService->error('api', 'Flow POST con error HTTP', $endpoint, $this->extraerEmpresaIdDesdePayload($payload), [
                'url' => $url,
                'http_code' => $httpCode,
                'response' => $parsed,
            ]);
            throw new RuntimeException($this->armarMensajeErrorFlow($httpCode, $parsed, $url, (string) ($config['entorno'] ?? 'sandbox')));
        }
        return $parsed;
    }

    public function get(string $endpoint, array $params): array
    {
        $config = $this->configuracionActiva();
        $payload = array_merge(['apiKey' => $config['api_key']], $params);
        $payload['s'] = $this->firmaService->firmar($payload, $config['secret_key']);
        $url = rtrim((string) $config['base_url_real'], '/') . '/' . ltrim($endpoint, '/') . '?' . http_build_query($payload);

        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('Error de conexión Flow: ' . curl_error($ch));
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $parsed = json_decode($response, true);
        if (!is_array($parsed)) {
            throw new RuntimeException('Respuesta inválida de Flow.');
        }
        if ($httpCode >= 400) {
            $reintentoEmail = $this->reintentarSiEmailInvalido('GET', $endpoint, $payload, $config, $httpCode, $parsed);
            if ($reintentoEmail !== null) {
                return $reintentoEmail;
            }
            $reintento = $this->reintentarSiApiKeyNoCoincide('GET', $endpoint, $payload, $config, $httpCode, $parsed);
            if ($reintento !== null) {
                return $reintento;
            }
            $this->logService->error('api', 'Flow GET con error HTTP', $endpoint, $this->extraerEmpresaIdDesdePayload($payload), [
                'url' => $url,
                'http_code' => $httpCode,
                'response' => $parsed,
            ]);
            throw new RuntimeException($this->armarMensajeErrorFlow($httpCode, $parsed, $url, (string) ($config['entorno'] ?? 'sandbox')));
        }

        return $parsed;
    }

    public function encriptarSecret(string $secret): string
    {
        return base64_encode($secret);
    }

    public function desencriptarSecret(string $secretEnc): string
    {
        if ($secretEnc === '') {
            return '';
        }
        return base64_decode($secretEnc, true) ?: '';
    }

    private function resolverBaseUrl(array $config): string
    {
        $entorno = (string) ($config['entorno'] ?? 'sandbox');
        $baseUrl = trim((string) ($config['base_url'] ?? ''));
        if ($baseUrl !== '') {
            if ($entorno === 'produccion' && str_contains($baseUrl, 'sandbox.flow.cl')) {
                return 'https://www.flow.cl/api';
            }
            if ($entorno === 'sandbox' && str_contains($baseUrl, 'www.flow.cl')) {
                return 'https://sandbox.flow.cl/api';
            }
            return $baseUrl;
        }

        return $entorno === 'produccion'
            ? 'https://www.flow.cl/api'
            : 'https://sandbox.flow.cl/api';
    }

    private function normalizarCredencial(string $valor): string
    {
        $valor = trim($valor);
        $valor = trim($valor, "\"'");
        return preg_replace('/\s+/', '', $valor) ?: '';
    }

    private function armarMensajeErrorFlow(int $httpCode, array $parsed, string $url, string $entorno): string
    {
        $detalle = (string) ($parsed['message'] ?? 'Sin detalle');
        if ($httpCode === 401 && stripos($detalle, 'apiKey not found') !== false) {
            return 'Flow respondió 401 (apiKey no encontrada). Verifica apiKey/secretKey y que el entorno coincida (sandbox vs producción). Endpoint usado: ' . $url . ' | entorno: ' . $entorno;
        }

        return 'Flow devolvió error HTTP ' . $httpCode . ': ' . $detalle;
    }

    private function reintentarSiApiKeyNoCoincide(string $metodo, string $endpoint, array $payload, array $config, int $httpCode, array $parsed): ?array
    {
        if (!$this->esErrorApiKeyNoEncontrada($httpCode, $parsed)) {
            return null;
        }

        $baseAlternativa = $this->resolverBaseUrlAlternativa((string) ($config['base_url_real'] ?? ''));
        if ($baseAlternativa === null) {
            return null;
        }
        $this->logService->warning('api', 'Reintento Flow por apiKey/entorno', $endpoint, $this->extraerEmpresaIdDesdePayload($payload), [
            'url' => $baseAlternativa . '/' . ltrim($endpoint, '/'),
            'entorno_original' => (string) ($config['entorno'] ?? 'sandbox'),
        ]);

        if ($metodo === 'POST') {
            [$codigo, $respuesta] = $this->ejecutarCurlPost($baseAlternativa . '/' . ltrim($endpoint, '/'), $payload);
        } else {
            [$codigo, $respuesta] = $this->ejecutarCurlGet($baseAlternativa . '/' . ltrim($endpoint, '/') . '?' . http_build_query($payload));
        }

        if (!is_array($respuesta)) {
            return null;
        }

        return $codigo < 400 ? $respuesta : null;
    }

    private function reintentarSiEmailInvalido(string $metodo, string $endpoint, array $payload, array $config, int $httpCode, array $parsed): ?array
    {
        if (!$this->esErrorEmailInvalido($httpCode, $parsed) || empty($payload['email'])) {
            return null;
        }

        $payload['email'] = $this->construirEmailFallback($payload);
        $baseActual = rtrim((string) ($config['base_url_real'] ?? ''), '/');
        if ($baseActual === '') {
            return null;
        }
        $this->logService->warning('api', 'Reintento Flow por email inválido', $endpoint, $this->extraerEmpresaIdDesdePayload($payload), [
            'url' => $baseActual . '/' . ltrim($endpoint, '/'),
            'email_fallback' => $payload['email'],
        ]);

        if ($metodo === 'POST') {
            [$codigo, $respuesta] = $this->ejecutarCurlPost($baseActual . '/' . ltrim($endpoint, '/'), $payload);
        } else {
            [$codigo, $respuesta] = $this->ejecutarCurlGet($baseActual . '/' . ltrim($endpoint, '/') . '?' . http_build_query($payload));
        }

        return ($codigo < 400 && is_array($respuesta)) ? $respuesta : null;
    }

    private function esErrorApiKeyNoEncontrada(int $httpCode, array $parsed): bool
    {
        if ($httpCode !== 401) {
            return false;
        }

        $detalle = strtolower((string) ($parsed['message'] ?? ''));
        return str_contains($detalle, 'apikey not found');
    }

    private function esErrorEmailInvalido(int $httpCode, array $parsed): bool
    {
        if ($httpCode !== 401) {
            return false;
        }

        $detalle = strtolower((string) ($parsed['message'] ?? ''));
        return str_contains($detalle, 'email is not valid');
    }

    private function construirEmailFallback(array $payload): string
    {
        $externalId = preg_replace('/[^a-z0-9]/', '', strtolower((string) ($payload['externalId'] ?? '')));
        if ($externalId === '') {
            $externalId = substr(sha1((string) microtime(true)), 0, 10);
        }

        return 'flow.' . $externalId . '@gmail.com';
    }

    private function extraerEmpresaIdDesdePayload(array $payload): ?int
    {
        $externalId = (string) ($payload['externalId'] ?? '');
        if ($externalId === '' || !ctype_digit($externalId)) {
            return null;
        }

        $empresaId = (int) $externalId;
        return $empresaId > 0 ? $empresaId : null;
    }

    private function resolverBaseUrlAlternativa(string $baseActual): ?string
    {
        if (str_contains($baseActual, 'sandbox.flow.cl')) {
            return 'https://www.flow.cl/api';
        }

        if (str_contains($baseActual, 'www.flow.cl')) {
            return 'https://sandbox.flow.cl/api';
        }

        return null;
    }

    private function ejecutarCurlPost(string $url, array $payload): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return [500, null];
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $parsed = json_decode($response, true);
        return [$httpCode, is_array($parsed) ? $parsed : null];
    }

    private function ejecutarCurlGet(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return [500, null];
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $parsed = json_decode($response, true);
        return [$httpCode, is_array($parsed) ? $parsed : null];
    }

    public static function obtenerBasePublicaAplicacion(): string
    {
        $env = trim((string) ($_ENV['APP_URL'] ?? ''));
        if ($env !== '') {
            return rtrim($env, '/');
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $esquema = $https ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $basePath = function_exists('base_path_url') ? base_path_url() : '';
        return rtrim($esquema . '://' . $host . $basePath, '/');
    }

    public static function construirUrlPublica(string $ruta): string
    {
        $base = self::obtenerBasePublicaAplicacion();
        return rtrim($base, '/') . '/' . ltrim($ruta, '/');
    }
}
