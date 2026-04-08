<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Usuario extends Modelo
{
    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = 'SELECT u.*, r.codigo AS rol_codigo FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.correo = :correo AND u.fecha_eliminacion IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $correo]);
        return $stmt->fetch() ?: null;
    }

    public function listarPorEmpresa(int $empresaId): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.nombre, u.correo, u.telefono, u.cargo, u.estado, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.empresa_id = :empresa_id AND u.fecha_eliminacion IS NULL ORDER BY u.id DESC');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }



    public function contarPorEmpresa(int $empresaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) total FROM usuarios WHERE empresa_id = :empresa_id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function listarAdministradoresEmpresa(array $filtros = []): array
    {
        $sql = 'SELECT u.id, u.empresa_id, u.nombre, u.correo, u.telefono, u.estado, u.ultimo_acceso, u.password_actualizado_en,
                e.nombre_comercial AS empresa_nombre, e.estado AS empresa_estado
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE r.codigo = "administrador_empresa" AND u.fecha_eliminacion IS NULL';
        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= ' AND (u.nombre LIKE :q OR u.correo LIKE :q OR e.nombre_comercial LIKE :q)';
            $params['q'] = '%' . $filtros['busqueda'] . '%';
        }
        if (!empty($filtros['empresa_id'])) {
            $sql .= ' AND u.empresa_id = :empresa_id';
            $params['empresa_id'] = (int) $filtros['empresa_id'];
        }

        $sql .= ' ORDER BY u.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerAdministradorEmpresa(int $id): ?array
    {
        $sql = 'SELECT u.*, e.nombre_comercial AS empresa_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE r.codigo = "administrador_empresa" AND u.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerAdministradorPrincipalPorEmpresa(int $empresaId): ?array
    {
        $sql = 'SELECT u.*
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.empresa_id = :empresa_id
              AND r.codigo = "administrador_empresa"
              AND u.fecha_eliminacion IS NULL
            ORDER BY u.id ASC
            LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, telefono, cargo, biografia, estado, fecha_creacion) VALUES (:empresa_id, :rol_id, :nombre, :correo, :password, :telefono, :cargo, :biografia, :estado, NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function obtenerRolIdPorCodigo(string $codigo): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);
        $rolId = $stmt->fetchColumn();
        return $rolId !== false ? (int) $rolId : null;
    }

    public function listarRolesEmpresa(): array
    {
        $sql = "SELECT id, nombre, codigo FROM roles WHERE codigo IN (
            'administrador_empresa',
            'vendedor',
            'administrativo',
            'contabilidad',
            'supervisor_comercial',
            'operaciones',
            'usuario_empresa'
        )
        ORDER BY FIELD(
            codigo,
            'administrador_empresa',
            'vendedor',
            'administrativo',
            'contabilidad',
            'supervisor_comercial',
            'operaciones',
            'usuario_empresa'
        )";

        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorIdEmpresa(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.empresa_id=:empresa_id AND u.id=:id AND u.fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarEmpresa(int $empresaId, int $id, array $data): void
    {
        $campos = 'nombre=:nombre, correo=:correo, telefono=:telefono, cargo=:cargo, biografia=:biografia, rol_id=:rol_id, estado=:estado';

        if (isset($data['password'])) {
            $campos .= ', password=:password';
        }

        $sql = 'UPDATE usuarios SET ' . $campos . ', fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';
        $data['empresa_id'] = $empresaId;
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        $this->db->prepare('UPDATE usuarios SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id')->execute([
            'id' => $id,
            'estado' => $estado,
        ]);
    }

    public function actualizarCredencialesAdmin(int $id, string $correo, string $nombre): void
    {
        $this->db->prepare('UPDATE usuarios SET correo = :correo, nombre = :nombre, fecha_actualizacion = NOW() WHERE id = :id')->execute([
            'id' => $id,
            'correo' => $correo,
            'nombre' => $nombre,
        ]);
    }

    public function resetearPasswordAdminEmpresa(int $id, string $passwordHash): void
    {
        $this->db->prepare('UPDATE usuarios SET password = :password, password_actualizado_en = NOW(), fecha_actualizacion = NOW() WHERE id = :id')->execute([
            'id' => $id,
            'password' => $passwordHash,
        ]);
    }
}
