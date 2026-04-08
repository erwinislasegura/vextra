<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class FlowCliente extends Modelo
{
    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT fc.*, e.nombre_comercial AS empresa FROM flow_clientes fc INNER JOIN empresas e ON e.id = fc.empresa_id WHERE fc.fecha_eliminacion IS NULL';
        $params = [];
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND fc.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }
        $sql .= ' ORDER BY fc.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorEmpresa(int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM flow_clientes WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(array $data): void
    {
        $existente = $this->buscarPorEmpresa((int) $data['empresa_id']);
        if ($existente) {
            $data['id'] = (int) $existente['id'];
            $this->db->prepare('UPDATE flow_clientes SET flow_customer_id=:flow_customer_id, correo=:correo, nombre=:nombre, estado_local=:estado_local, estado_flow=:estado_flow, token_registro=:token_registro, url_registro=:url_registro, medio_pago_registrado=:medio_pago_registrado, payload_request=:payload_request, payload_response=:payload_response, fecha_actualizacion=NOW() WHERE id=:id')->execute($data);
            return;
        }

        $this->db->prepare('INSERT INTO flow_clientes (empresa_id,flow_customer_id,correo,nombre,estado_local,estado_flow,token_registro,url_registro,medio_pago_registrado,payload_request,payload_response,fecha_creacion) VALUES (:empresa_id,:flow_customer_id,:correo,:nombre,:estado_local,:estado_flow,:token_registro,:url_registro,:medio_pago_registrado,:payload_request,:payload_response,NOW())')->execute($data);
    }
}
