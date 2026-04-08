<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class LogAdministracion extends Modelo
{
    public function registrar(string $modulo, string $accion, string $detalle = '', ?int $empresaId = null, ?int $usuarioObjetivoId = null): void
    {
        $usuario = usuario_actual();
        $this->db->prepare('INSERT INTO logs_administracion (admin_usuario_id, empresa_id, usuario_objetivo_id, modulo, accion, detalle, ip, user_agent, fecha_creacion) VALUES (:admin_usuario_id, :empresa_id, :usuario_objetivo_id, :modulo, :accion, :detalle, :ip, :user_agent, NOW())')->execute([
            'admin_usuario_id' => $usuario['id'] ?? null,
            'empresa_id' => $empresaId,
            'usuario_objetivo_id' => $usuarioObjetivoId,
            'modulo' => $modulo,
            'accion' => $accion,
            'detalle' => $detalle,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function listar(int $limite = 50): array
    {
        $stmt = $this->db->prepare('SELECT l.*, a.nombre AS admin_nombre, u.nombre AS usuario_objetivo_nombre, e.nombre_comercial AS empresa_nombre
            FROM logs_administracion l
            LEFT JOIN usuarios a ON a.id = l.admin_usuario_id
            LEFT JOIN usuarios u ON u.id = l.usuario_objetivo_id
            LEFT JOIN empresas e ON e.id = l.empresa_id
            ORDER BY l.id DESC
            LIMIT :limite');
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
