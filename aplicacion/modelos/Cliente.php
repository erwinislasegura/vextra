<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Cliente extends Modelo
{
    public function listar(int $empresaId, string $buscar = ''): array
    {
        $sql = 'SELECT * FROM clientes WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL';
        $params = ['empresa_id' => $empresaId];
        if ($buscar !== '') {
            $sql .= ' AND (nombre LIKE :buscar OR correo LIKE :buscar OR razon_social LIKE :buscar OR nombre_comercial LIKE :buscar OR identificador_fiscal LIKE :buscar)';
            $params['buscar'] = "%{$buscar}%";
        }
        $sql .= ' ORDER BY id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contar(int $empresaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM clientes WHERE empresa_id = :empresa_id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetch()['total'];
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO clientes (empresa_id, nombre, razon_social, nombre_comercial, identificador_fiscal, giro, correo, telefono, direccion, ciudad, vendedor_id, notas, estado, fecha_creacion) VALUES (:empresa_id,:nombre,:razon_social,:nombre_comercial,:identificador_fiscal,:giro,:correo,:telefono,:direccion,:ciudad,:vendedor_id,:notas,:estado,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function obtenerPorId(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizar(int $empresaId, int $id, array $data): void
    {
        $sql = 'UPDATE clientes SET razon_social=:razon_social, nombre_comercial=:nombre_comercial, identificador_fiscal=:identificador_fiscal, giro=:giro, correo=:correo, telefono=:telefono, direccion=:direccion, ciudad=:ciudad, vendedor_id=:vendedor_id, notas=:notas, estado=:estado, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';
        $data['empresa_id'] = $empresaId;
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function eliminar(int $empresaId, int $id): void
    {
        $stmt = $this->db->prepare('UPDATE clientes SET fecha_eliminacion = NOW() WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
    }
}
