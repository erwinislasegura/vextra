<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class ProductoImagen extends Modelo
{
    public function listarPorProducto(int $empresaId, int $productoId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM productos_imagenes WHERE empresa_id=:empresa_id AND producto_id=:producto_id ORDER BY es_principal DESC, id ASC');
        $stmt->execute(['empresa_id' => $empresaId, 'producto_id' => $productoId]);
        return $stmt->fetchAll();
    }

    public function contarPorProducto(int $empresaId, int $productoId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM productos_imagenes WHERE empresa_id=:empresa_id AND producto_id=:producto_id');
        $stmt->execute(['empresa_id' => $empresaId, 'producto_id' => $productoId]);
        return (int) $stmt->fetchColumn();
    }

    public function crear(int $empresaId, int $productoId, string $ruta, bool $esPrincipal = false): int
    {
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
}
