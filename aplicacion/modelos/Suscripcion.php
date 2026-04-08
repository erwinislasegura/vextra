<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Suscripcion extends Modelo
{
    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT s.*, e.nombre_comercial AS empresa, p.nombre AS plan, p.precio_mensual,
                DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM suscripciones s
                INNER JOIN empresas e ON e.id=s.empresa_id
                INNER JOIN planes p ON p.id=s.plan_id
                WHERE s.fecha_eliminacion IS NULL';
        $params = [];
        if (!empty($filtros['estado'])) {
            $sql .= ' AND s.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['plan_id'])) {
            $sql .= ' AND s.plan_id = :plan_id';
            $params['plan_id'] = (int) $filtros['plan_id'];
        }
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND s.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }
        $sql .= ' ORDER BY s.fecha_vencimiento ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recientes(int $limite = 8): array
    {
        $stmt = $this->db->prepare('SELECT s.id, s.estado, s.fecha_actualizacion, s.fecha_creacion, e.nombre_comercial AS empresa, p.nombre AS plan
            FROM suscripciones s
            INNER JOIN empresas e ON e.id = s.empresa_id
            INNER JOIN planes p ON p.id = s.plan_id
            WHERE s.fecha_eliminacion IS NULL
            ORDER BY COALESCE(s.fecha_actualizacion, s.fecha_creacion) DESC
            LIMIT :limite');
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO suscripciones (empresa_id, plan_id, estado, fecha_inicio, fecha_vencimiento, observaciones, renovacion_automatica, fecha_creacion) VALUES (:empresa_id,:plan_id,:estado,:fecha_inicio,:fecha_vencimiento,:observaciones,:renovacion_automatica,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM suscripciones WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizar(int $id, array $data): void
    {
        $payloadActualizacion = [
            'id' => $id,
            'plan_id' => (int) ($data['plan_id'] ?? 0),
            'estado' => (string) ($data['estado'] ?? 'activa'),
            'fecha_inicio' => $data['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? date('Y-m-d'),
            'observaciones' => (string) ($data['observaciones'] ?? ''),
        ];
        $this->db->prepare('UPDATE suscripciones SET plan_id = :plan_id, estado = :estado, fecha_inicio = :fecha_inicio, fecha_vencimiento = :fecha_vencimiento, observaciones = :observaciones, fecha_actualizacion = NOW() WHERE id = :id')->execute($payloadActualizacion);

        $empresaId = (int) ($data['empresa_id'] ?? 0);
        if ($empresaId <= 0) {
            return;
        }

        $this->db->prepare('UPDATE empresas SET plan_id = :plan_id, estado = :estado_empresa, fecha_actualizacion = NOW() WHERE id = :empresa_id')->execute([
            'plan_id' => $payloadActualizacion['plan_id'],
            'estado_empresa' => in_array($payloadActualizacion['estado'], ['cancelada', 'vencida', 'suspendida'], true) ? $payloadActualizacion['estado'] : 'activa',
            'empresa_id' => $empresaId,
        ]);
    }

    public function obtenerUltimaPorEmpresa(int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM suscripciones WHERE empresa_id = :empresa_id AND fecha_eliminacion IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarEstado(int $id, string $estado, string $observaciones): void
    {
        $this->db->prepare('UPDATE suscripciones SET estado=:estado, observaciones=:observaciones, fecha_actualizacion=NOW() WHERE id=:id')->execute(['id' => $id, 'estado' => $estado, 'observaciones' => $observaciones]);
        $this->db->prepare('INSERT INTO historial_suscripciones (suscripcion_id, accion, observaciones, fecha_creacion) VALUES (:suscripcion_id,:accion,:observaciones,NOW())')->execute(['suscripcion_id' => $id, 'accion' => 'actualizacion_estado', 'observaciones' => $observaciones]);
    }

    public function obtenerResumenVigenciaEmpresa(int $empresaId): ?array
    {
        $sql = 'SELECT s.id, s.estado, s.fecha_inicio, s.fecha_vencimiento, p.nombre AS plan_nombre,
                CASE
                    WHEN s.estado IN ("cancelada", "suspendida") THEN NULL
                    WHEN s.fecha_vencimiento IS NULL THEN NULL
                    ELSE DATEDIFF(s.fecha_vencimiento, CURDATE())
                END AS dias_restantes
            FROM suscripciones s
            INNER JOIN planes p ON p.id = s.plan_id
            WHERE s.empresa_id = :empresa_id
              AND s.fecha_eliminacion IS NULL
            ORDER BY s.id DESC
            LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }
}
