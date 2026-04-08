<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowPago extends Modelo
{
    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT fp.*, e.nombre_comercial AS empresa, p.nombre AS plan
            FROM flow_pagos fp
            INNER JOIN empresas e ON e.id = fp.empresa_id
            LEFT JOIN planes p ON p.id = fp.plan_id
            WHERE 1=1';
        $params = [];
        if (!empty($filtros['estado'])) {
            $sql .= ' AND fp.estado_local = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND fp.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }
        if (!empty($filtros['plan_id'])) {
            $sql .= ' AND fp.plan_id = :plan_id';
            $params['plan_id'] = (int) $filtros['plan_id'];
        }
        if (!empty($filtros['desde'])) {
            $sql .= ' AND DATE(fp.fecha_creacion) >= :desde';
            $params['desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= ' AND DATE(fp.fecha_creacion) <= :hasta';
            $params['hasta'] = $filtros['hasta'];
        }
        $sql .= ' ORDER BY fp.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_pagos WHERE flow_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function buscarUltimoPorSuscripcionId(int $suscripcionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_pagos WHERE suscripcion_id = :suscripcion_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['suscripcion_id' => $suscripcionId]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(array $data): int
    {
        $actual = !empty($data['flow_token']) ? $this->buscarPorToken((string) $data['flow_token']) : null;
        if ($actual) {
            $data['id'] = (int) $actual['id'];
            $this->db->prepare('UPDATE flow_pagos SET pago_id=:pago_id, suscripcion_id=:suscripcion_id, empresa_id=:empresa_id, plan_id=:plan_id, tipo_pago=:tipo_pago, commerce_order=:commerce_order, flow_token=:flow_token, flow_order=:flow_order, flow_payment_id=:flow_payment_id, estado_local=:estado_local, estado_flow=:estado_flow, monto=:monto, moneda=:moneda, entorno_flow=:entorno_flow, fecha_confirmacion=:fecha_confirmacion, observaciones=:observaciones, payload_request=:payload_request, payload_response=:payload_response, fecha_actualizacion=NOW() WHERE id=:id')->execute($data);
            return (int) $actual['id'];
        }

        $this->db->prepare('INSERT INTO flow_pagos (pago_id,suscripcion_id,empresa_id,plan_id,tipo_pago,commerce_order,flow_token,flow_order,flow_payment_id,estado_local,estado_flow,monto,moneda,entorno_flow,fecha_confirmacion,observaciones,payload_request,payload_response,fecha_creacion) VALUES (:pago_id,:suscripcion_id,:empresa_id,:plan_id,:tipo_pago,:commerce_order,:flow_token,:flow_order,:flow_payment_id,:estado_local,:estado_flow,:monto,:moneda,:entorno_flow,:fecha_confirmacion,:observaciones,:payload_request,:payload_response,NOW())')->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
