<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class PlanFuncionalidad extends Modelo
{
    public function listarPorPlan(int $planId): array
    {
        $sql = 'SELECT pf.*, f.nombre, f.codigo_interno, f.descripcion, f.tipo_valor, f.estado AS funcionalidad_estado
            FROM plan_funcionalidades pf
            INNER JOIN funcionalidades f ON f.id = pf.funcionalidad_id
            WHERE pf.plan_id = :plan_id
            ORDER BY f.nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function listarActivasPorPlan(int $planId): array
    {
        $sql = 'SELECT f.nombre, f.codigo_interno, f.descripcion, pf.valor_numerico, pf.es_ilimitado
            FROM plan_funcionalidades pf
            INNER JOIN funcionalidades f ON f.id = pf.funcionalidad_id
            WHERE pf.plan_id = :plan_id
              AND pf.activo = 1
              AND f.estado = "activo"
              AND f.fecha_eliminacion IS NULL
            ORDER BY f.nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function obtenerPorPlanYCodigo(int $planId, string $codigo): ?array
    {
        $sql = 'SELECT pf.* FROM plan_funcionalidades pf INNER JOIN funcionalidades f ON f.id = pf.funcionalidad_id WHERE pf.plan_id=:plan_id AND f.codigo_interno=:codigo LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['plan_id' => $planId, 'codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    public function guardarAsignacion(int $planId, int $funcionalidadId, array $data): void
    {
        $sql = 'INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion) VALUES (:plan_id,:funcionalidad_id,:activo,:valor_numerico,:es_ilimitado,NOW()) ON DUPLICATE KEY UPDATE activo=VALUES(activo), valor_numerico=VALUES(valor_numerico), es_ilimitado=VALUES(es_ilimitado), fecha_actualizacion=NOW()';
        $this->db->prepare($sql)->execute([
            'plan_id' => $planId,
            'funcionalidad_id' => $funcionalidadId,
            'activo' => $data['activo'],
            'valor_numerico' => $data['valor_numerico'],
            'es_ilimitado' => $data['es_ilimitado'],
        ]);
    }
}
