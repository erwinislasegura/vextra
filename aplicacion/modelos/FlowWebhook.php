<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowWebhook extends Modelo
{
    public function registrar(string $tipoEvento, ?string $token, array $payload): ?int
    {
        $hash = hash('sha256', $tipoEvento . '|' . (string) $token . '|' . json_encode($payload));
        $stmt = $this->db->prepare('SELECT id FROM flow_webhooks WHERE hash_unico = :hash LIMIT 1');
        $stmt->execute(['hash' => $hash]);
        $idExistente = $stmt->fetchColumn();
        if ($idExistente) {
            return null;
        }

        $this->db->prepare('INSERT INTO flow_webhooks (tipo_evento, token, hash_unico, payload, procesado, fecha_creacion) VALUES (:tipo_evento,:token,:hash_unico,:payload,0,NOW())')->execute([
            'tipo_evento' => $tipoEvento,
            'token' => $token,
            'hash_unico' => $hash,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function marcarProcesado(int $id, string $resultado, ?string $error = null): void
    {
        $this->db->prepare('UPDATE flow_webhooks SET procesado=1, resultado=:resultado, error_detalle=:error_detalle, fecha_procesamiento=NOW() WHERE id=:id')->execute([
            'id' => $id,
            'resultado' => $resultado,
            'error_detalle' => $error,
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT * FROM flow_webhooks WHERE 1=1';
        $params = [];
        if (!empty($filtros['tipo'])) {
            $sql .= ' AND tipo_evento = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }
        $sql .= ' ORDER BY id DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
