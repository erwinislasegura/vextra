<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowPlan extends Modelo
{
    public function listar(): array
    {
        return $this->db->query('SELECT fp.*, p.nombre AS plan_nombre FROM flow_planes fp INNER JOIN planes p ON p.id = fp.plan_id ORDER BY p.orden_visualizacion ASC, fp.modalidad ASC')->fetchAll();
    }

    public function buscarPorPlanYModalidad(int $planId, string $modalidad): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_planes WHERE plan_id=:plan_id AND modalidad=:modalidad LIMIT 1');
        $stmt->execute(['plan_id' => $planId, 'modalidad' => $modalidad]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(array $data): void
    {
        $actual = $this->buscarPorPlanYModalidad((int) $data['plan_id'], (string) $data['modalidad']);
        if ($actual) {
            $this->db->prepare('UPDATE flow_planes SET flow_plan_id=:flow_plan_id, moneda=:moneda, monto=:monto, intervalo=:intervalo, intervalo_cantidad=:intervalo_cantidad, dias_trial=:dias_trial, dias_vencimiento=:dias_vencimiento, periodos=:periodos, estado_local=:estado_local, estado_flow=:estado_flow, entorno_flow=:entorno_flow, payload_request=:payload_request, payload_response=:payload_response, fecha_actualizacion=NOW() WHERE id=:id')->execute([
                'id' => (int) $actual['id'],
                'flow_plan_id' => $data['flow_plan_id'],
                'moneda' => $data['moneda'],
                'monto' => $data['monto'],
                'intervalo' => $data['intervalo'],
                'intervalo_cantidad' => $data['intervalo_cantidad'],
                'dias_trial' => $data['dias_trial'],
                'dias_vencimiento' => $data['dias_vencimiento'],
                'periodos' => $data['periodos'],
                'estado_local' => $data['estado_local'],
                'estado_flow' => $data['estado_flow'],
                'entorno_flow' => $data['entorno_flow'],
                'payload_request' => $data['payload_request'],
                'payload_response' => $data['payload_response'],
            ]);
            return;
        }
        $this->db->prepare('INSERT INTO flow_planes (plan_id,modalidad,flow_plan_id,moneda,monto,intervalo,intervalo_cantidad,dias_trial,dias_vencimiento,periodos,estado_local,estado_flow,entorno_flow,payload_request,payload_response,fecha_creacion) VALUES (:plan_id,:modalidad,:flow_plan_id,:moneda,:monto,:intervalo,:intervalo_cantidad,:dias_trial,:dias_vencimiento,:periodos,:estado_local,:estado_flow,:entorno_flow,:payload_request,:payload_response,NOW())')->execute([
            'plan_id' => $data['plan_id'],
            'modalidad' => $data['modalidad'],
            'flow_plan_id' => $data['flow_plan_id'],
            'moneda' => $data['moneda'],
            'monto' => $data['monto'],
            'intervalo' => $data['intervalo'],
            'intervalo_cantidad' => $data['intervalo_cantidad'],
            'dias_trial' => $data['dias_trial'],
            'dias_vencimiento' => $data['dias_vencimiento'],
            'periodos' => $data['periodos'],
            'estado_local' => $data['estado_local'],
            'estado_flow' => $data['estado_flow'],
            'entorno_flow' => $data['entorno_flow'],
            'payload_request' => $data['payload_request'],
            'payload_response' => $data['payload_response'],
        ]);
    }
}
