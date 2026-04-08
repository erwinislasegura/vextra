<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\FlowLog;

class FlowLogService
{
    public function info(string $tipo, string $mensaje, ?string $referencia = null, ?int $empresaId = null, ?array $payload = null): void
    {
        (new FlowLog())->registrar($tipo, 'info', $mensaje, $referencia, $empresaId, $payload);
    }

    public function warning(string $tipo, string $mensaje, ?string $referencia = null, ?int $empresaId = null, ?array $payload = null): void
    {
        (new FlowLog())->registrar($tipo, 'warning', $mensaje, $referencia, $empresaId, $payload);
    }

    public function error(string $tipo, string $mensaje, ?string $referencia = null, ?int $empresaId = null, ?array $payload = null): void
    {
        (new FlowLog())->registrar($tipo, 'error', $mensaje, $referencia, $empresaId, $payload);
    }
}
