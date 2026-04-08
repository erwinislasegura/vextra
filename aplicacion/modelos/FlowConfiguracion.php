<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowConfiguracion extends Modelo
{
    public function obtener(): ?array
    {
        $fila = $this->db->query('SELECT * FROM flow_configuracion ORDER BY id DESC LIMIT 1')->fetch();
        return $fila ?: null;
    }

    public function guardar(array $data): void
    {
        $actual = $this->obtener();
        if ($actual) {
            $data['id'] = (int) $actual['id'];
            $sql = 'UPDATE flow_configuracion SET
                activo=:activo,
                entorno=:entorno,
                api_key=:api_key,
                secret_key_enc=:secret_key_enc,
                base_url=:base_url,
                habilitar_pagos_unicos=:habilitar_pagos_unicos,
                habilitar_suscripciones=:habilitar_suscripciones,
                url_confirmacion=:url_confirmacion,
                url_retorno=:url_retorno,
                url_webhook_pago=:url_webhook_pago,
                url_webhook_suscripcion=:url_webhook_suscripcion,
                url_retorno_registro=:url_retorno_registro,
                fecha_actualizacion=NOW()
            WHERE id=:id';
            $this->db->prepare($sql)->execute($data);
            return;
        }

        $sql = 'INSERT INTO flow_configuracion (activo,entorno,api_key,secret_key_enc,base_url,habilitar_pagos_unicos,habilitar_suscripciones,url_confirmacion,url_retorno,url_webhook_pago,url_webhook_suscripcion,url_retorno_registro,fecha_creacion)
            VALUES (:activo,:entorno,:api_key,:secret_key_enc,:base_url,:habilitar_pagos_unicos,:habilitar_suscripciones,:url_confirmacion,:url_retorno,:url_webhook_pago,:url_webhook_suscripcion,:url_retorno_registro,NOW())';
        $this->db->prepare($sql)->execute($data);
    }
}
