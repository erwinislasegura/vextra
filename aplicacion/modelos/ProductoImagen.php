<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class ProductoImagen extends Modelo
{
    public function obtenerPorId(int $id): ?array
    {
        if (!$this->tieneTablaImagenes()) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM productos_imagenes WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerPrincipalPorProductoId(int $productoId): ?array
    {
        if (!$this->tieneTablaImagenes()) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM productos_imagenes WHERE producto_id=:producto_id ORDER BY es_principal DESC, id ASC LIMIT 1');
        $stmt->execute(['producto_id' => $productoId]);
        return $stmt->fetch() ?: null;
    }

    public function listarPorProducto(int $empresaId, int $productoId): array
    {
        if (!$this->tieneTablaImagenes()) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM productos_imagenes WHERE empresa_id=:empresa_id AND producto_id=:producto_id ORDER BY es_principal DESC, id ASC');
        $stmt->execute(['empresa_id' => $empresaId, 'producto_id' => $productoId]);
        return $stmt->fetchAll();
    }

    public function contarPorProducto(int $empresaId, int $productoId): int
    {
        if (!$this->tieneTablaImagenes()) {
            return 0;
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM productos_imagenes WHERE empresa_id=:empresa_id AND producto_id=:producto_id');
        $stmt->execute(['empresa_id' => $empresaId, 'producto_id' => $productoId]);
        return (int) $stmt->fetchColumn();
    }

    public function crear(int $empresaId, int $productoId, string $ruta, bool $esPrincipal = false): int
    {
        if (!$this->tieneTablaImagenes()) {
            return 0;
        }
        $stmt = $this->db->prepare('INSERT INTO productos_imagenes (empresa_id, producto_id, ruta, es_principal, fecha_creacion) VALUES (:empresa_id, :producto_id, :ruta, :es_principal, NOW())');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'producto_id' => $productoId,
            'ruta' => $ruta,
            'es_principal' => $esPrincipal ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function marcarPrincipal(int $empresaId, int $productoId, int $imagenId): void
    {
        if (!$this->tieneTablaImagenes()) {
            return;
        }
        $this->db->prepare('UPDATE productos_imagenes SET es_principal = 0 WHERE empresa_id=:empresa_id AND producto_id=:producto_id')->execute([
            'empresa_id' => $empresaId,
            'producto_id' => $productoId,
        ]);
        $this->db->prepare('UPDATE productos_imagenes SET es_principal = 1 WHERE id=:id AND empresa_id=:empresa_id AND producto_id=:producto_id')->execute([
            'id' => $imagenId,
            'empresa_id' => $empresaId,
            'producto_id' => $productoId,
        ]);
    }

    public function eliminarPorIds(int $empresaId, int $productoId, array $ids): void
    {
        if (!$this->tieneTablaImagenes()) {
            return;
        }
        if ($ids === []) {
            return;
        }
        $ids = array_values(array_filter(array_map('intval', $ids), static fn($id) => $id > 0));
        if ($ids === []) {
            return;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM productos_imagenes WHERE empresa_id=? AND producto_id=? AND id IN ($in)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$empresaId, $productoId], $ids));
    }

    private function tieneTablaImagenes(): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla');
        $stmt->execute(['tabla' => 'productos_imagenes']);
        return ((int) $stmt->fetchColumn()) > 0;
    }
}
