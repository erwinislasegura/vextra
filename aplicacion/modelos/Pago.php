<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Pago extends Modelo
{
    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT p.*, e.nombre_comercial AS empresa, pl.nombre AS plan
            FROM pagos p
            INNER JOIN empresas e ON e.id = p.empresa_id
            LEFT JOIN suscripciones s ON s.id = p.suscripcion_id
            LEFT JOIN planes pl ON pl.id = s.plan_id
            WHERE 1=1';
        $params = [];

        if (!empty($filtros['desde'])) {
            $sql .= ' AND DATE(p.fecha_pago) >= :desde';
            $params['desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= ' AND DATE(p.fecha_pago) <= :hasta';
            $params['hasta'] = $filtros['hasta'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= ' AND p.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }

        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO pagos (empresa_id,suscripcion_id,monto,moneda,metodo,frecuencia,estado,referencia_externa,observaciones,payload,fecha_pago,fecha_creacion) VALUES (:empresa_id,:suscripcion_id,:monto,:moneda,:metodo,:frecuencia,:estado,:referencia_externa,:observaciones,:payload,:fecha_pago,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
