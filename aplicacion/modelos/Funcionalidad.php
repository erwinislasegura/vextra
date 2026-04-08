<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Funcionalidad extends Modelo
{
    public function listar(): array
    {
        return $this->db->query('SELECT * FROM funcionalidades WHERE fecha_eliminacion IS NULL ORDER BY nombre')->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM funcionalidades WHERE id=:id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }



    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM funcionalidades WHERE codigo_interno=:codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado, fecha_creacion) VALUES (:nombre,:codigo_interno,:descripcion,:tipo_valor,:estado,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE funcionalidades SET nombre=:nombre, codigo_interno=:codigo_interno, descripcion=:descripcion, tipo_valor=:tipo_valor, estado=:estado, fecha_actualizacion=NOW() WHERE id=:id';
        $this->db->prepare($sql)->execute($data);
    }
}
