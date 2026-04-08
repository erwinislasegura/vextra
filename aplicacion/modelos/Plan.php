<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Plan extends Modelo
{
    public function listar(bool $soloVisibles = false): array
    {
        $sql = 'SELECT * FROM planes WHERE fecha_eliminacion IS NULL';
        if ($soloVisibles) {
            $sql .= ' AND visible = 1 AND estado = "activo"';
        }
        $sql .= ' ORDER BY orden_visualizacion ASC, id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listarActivos(): array
    {
        return $this->db->query("SELECT * FROM planes WHERE fecha_eliminacion IS NULL AND estado = 'activo' ORDER BY orden_visualizacion ASC, id ASC")->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM planes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM planes WHERE slug = :slug AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPublicoPorSlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM planes WHERE slug = :slug AND estado = "activo" AND visible = 1 AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO planes (nombre, slug, descripcion_comercial, precio_mensual, descuento_anual_pct, precio_anual, duracion_dias, visible, destacado, recomendado, orden_visualizacion, insignia, resumen_comercial, color_visual, maximo_usuarios, usuarios_ilimitados, observaciones_internas, estado, flow_dias_prueba, flow_dias_cobro, fecha_creacion) VALUES (:nombre,:slug,:descripcion_comercial,:precio_mensual,:descuento_anual_pct,:precio_anual,:duracion_dias,:visible,:destacado,:recomendado,:orden_visualizacion,:insignia,:resumen_comercial,:color_visual,:maximo_usuarios,:usuarios_ilimitados,:observaciones_internas,:estado,:flow_dias_prueba,:flow_dias_cobro,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE planes SET nombre=:nombre, slug=:slug, descripcion_comercial=:descripcion_comercial, precio_mensual=:precio_mensual, descuento_anual_pct=:descuento_anual_pct, precio_anual=:precio_anual, duracion_dias=:duracion_dias, visible=:visible, destacado=:destacado, recomendado=:recomendado, orden_visualizacion=:orden_visualizacion, insignia=:insignia, resumen_comercial=:resumen_comercial, color_visual=:color_visual, maximo_usuarios=:maximo_usuarios, usuarios_ilimitados=:usuarios_ilimitados, observaciones_internas=:observaciones_internas, estado=:estado, flow_dias_prueba=:flow_dias_prueba, flow_dias_cobro=:flow_dias_cobro, fecha_actualizacion=NOW() WHERE id=:id';
        $this->db->prepare($sql)->execute($data);
    }



    public function actualizarLimiteUsuarios(int $planId, int $maximoUsuarios, int $usuariosIlimitados): void
    {
        $stmt = $this->db->prepare('UPDATE planes SET maximo_usuarios = :maximo_usuarios, usuarios_ilimitados = :usuarios_ilimitados, fecha_actualizacion = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $planId,
            'maximo_usuarios' => max(0, $maximoUsuarios),
            'usuarios_ilimitados' => $usuariosIlimitados ? 1 : 0,
        ]);
    }

    public function eliminar(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE planes SET fecha_eliminacion = NOW(), fecha_actualizacion = NOW() WHERE id = :id AND fecha_eliminacion IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public function obtenerPlanActivoEmpresa(int $empresaId): ?array
    {
        $sql = 'SELECT s.plan_id, s.fecha_vencimiento, s.estado FROM suscripciones s WHERE s.empresa_id = :empresa_id ORDER BY s.id DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }
}
