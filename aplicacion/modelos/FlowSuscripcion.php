<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowSuscripcion extends Modelo
{
    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT fs.*, e.nombre_comercial AS empresa, p.nombre AS plan
            FROM flow_suscripciones fs
            INNER JOIN empresas e ON e.id = fs.empresa_id
            INNER JOIN planes p ON p.id = fs.plan_id
            WHERE 1=1';
        $params = [];
        if (!empty($filtros['estado'])) {
            $sql .= ' AND fs.estado_local = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['plan_id'])) {
            $sql .= ' AND fs.plan_id = :plan_id';
            $params['plan_id'] = (int) $filtros['plan_id'];
        }
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND fs.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }
        $sql .= ' ORDER BY fs.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorFlowId(string $flowSubscriptionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_suscripciones WHERE flow_subscription_id = :flow_subscription_id LIMIT 1');
        $stmt->execute(['flow_subscription_id' => $flowSubscriptionId]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(array $data): int
    {
        $actual = $this->buscarPorFlowId((string) $data['flow_subscription_id']);
        if ($actual) {
            $data['id'] = (int) $actual['id'];
            $this->db->prepare('UPDATE flow_suscripciones SET suscripcion_id=:suscripcion_id, empresa_id=:empresa_id, plan_id=:plan_id, flow_customer_id=:flow_customer_id, flow_plan_id=:flow_plan_id, tipo_cobro=:tipo_cobro, estado_local=:estado_local, estado_flow=:estado_flow, entorno_flow=:entorno_flow, fecha_inicio=:fecha_inicio, fecha_vencimiento=:fecha_vencimiento, proxima_renovacion=:proxima_renovacion, fecha_cancelacion=:fecha_cancelacion, observaciones=:observaciones, payload_request=:payload_request, payload_response=:payload_response, fecha_actualizacion=NOW() WHERE id=:id')->execute($data);
            return (int) $actual['id'];
        }
        $this->db->prepare('INSERT INTO flow_suscripciones (suscripcion_id,empresa_id,plan_id,flow_customer_id,flow_plan_id,flow_subscription_id,tipo_cobro,estado_local,estado_flow,entorno_flow,fecha_inicio,fecha_vencimiento,proxima_renovacion,fecha_cancelacion,observaciones,payload_request,payload_response,fecha_creacion) VALUES (:suscripcion_id,:empresa_id,:plan_id,:flow_customer_id,:flow_plan_id,:flow_subscription_id,:tipo_cobro,:estado_local,:estado_flow,:entorno_flow,:fecha_inicio,:fecha_vencimiento,:proxima_renovacion,:fecha_cancelacion,:observaciones,:payload_request,:payload_response,NOW())')->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
