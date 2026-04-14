<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowConfiguracionEmpresa extends Modelo
{
    public function obtenerPorEmpresa(int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_configuraciones_empresa WHERE empresa_id = :empresa_id LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(int $empresaId, array $data): void
    {
        $sql = 'INSERT INTO flow_configuraciones_empresa (empresa_id, api_key, secret_key_enc, entorno, activo, fecha_actualizacion)
                VALUES (:empresa_id, :api_key, :secret_key_enc, :entorno, :activo, NOW())
                ON DUPLICATE KEY UPDATE
                    api_key = VALUES(api_key),
                    secret_key_enc = VALUES(secret_key_enc),
                    entorno = VALUES(entorno),
                    activo = VALUES(activo),
                    fecha_actualizacion = NOW()';
        $this->db->prepare($sql)->execute([
            'empresa_id' => $empresaId,
            'api_key' => (string) ($data['api_key'] ?? ''),
            'secret_key_enc' => (string) ($data['secret_key_enc'] ?? ''),
            'entorno' => (string) ($data['entorno'] ?? 'sandbox'),
            'activo' => (int) ($data['activo'] ?? 0),
        ]);
    }
}
