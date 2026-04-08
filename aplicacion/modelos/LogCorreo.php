<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class LogCorreo extends Modelo
{
    public function registrar(array $data): void
    {
        $sql = 'INSERT INTO logs_correos (destinatario, asunto, plantilla, payload, estado, fecha_creacion) VALUES (:destinatario,:asunto,:plantilla,:payload,:estado,NOW())';
        $this->db->prepare($sql)->execute($data);
    }
}
