<?php

namespace Aplicacion\Servicios;

class FlowFirmaService
{
    public function firmar(array $params, string $secretKey): string
    {
        unset($params['s']);
        foreach ($params as $k => $v) {
            if ($v === null || $v === '') {
                unset($params[$k]);
            }
        }
        ksort($params);
        $toSign = '';
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }
        return hash_hmac('sha256', $toSign, $secretKey);
    }
}
