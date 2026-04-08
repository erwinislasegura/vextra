<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowLog extends Modelo
{
    public function registrar(string $tipo, string $nivel, string $mensaje, ?string $referencia = null, ?int $empresaId = null, ?array $payload = null): void
    {
        $usuario = usuario_actual();
        $this->db->prepare('INSERT INTO flow_logs (empresa_id,admin_usuario_id,tipo,nivel,mensaje,referencia,payload,fecha_creacion) VALUES (:empresa_id,:admin_usuario_id,:tipo,:nivel,:mensaje,:referencia,:payload,NOW())')->execute([
            'empresa_id' => $empresaId,
            'admin_usuario_id' => $usuario['id'] ?? null,
            'tipo' => $tipo,
            'nivel' => $nivel,
            'mensaje' => $mensaje,
            'referencia' => $referencia,
            'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT fl.*, e.nombre_comercial AS empresa FROM flow_logs fl LEFT JOIN empresas e ON e.id = fl.empresa_id WHERE 1=1';
        $params = [];
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND fl.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= ' AND fl.tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['desde'])) {
            $sql .= ' AND DATE(fl.fecha_creacion) >= :desde';
            $params['desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= ' AND DATE(fl.fecha_creacion) <= :hasta';
            $params['hasta'] = $filtros['hasta'];
        }
        $sql .= ' ORDER BY fl.id DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
