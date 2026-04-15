<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class CatalogoCompra extends Modelo
{
    public function crear(array $data): int
    {
        $sql = 'INSERT INTO catalogo_compras (empresa_id, flow_token, commerce_order, estado_pago, estado_envio, comprador_nombre, comprador_correo, comprador_telefono, comprador_documento, comprador_empresa, envio_metodo, envio_direccion, envio_referencia, envio_comuna, envio_ciudad, envio_region, total, moneda, payload_flow, fecha_creacion)
            VALUES (:empresa_id, :flow_token, :commerce_order, :estado_pago, :estado_envio, :comprador_nombre, :comprador_correo, :comprador_telefono, :comprador_documento, :comprador_empresa, :envio_metodo, :envio_direccion, :envio_referencia, :envio_comuna, :envio_ciudad, :envio_region, :total, :moneda, :payload_flow, NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function guardarItems(int $compraId, array $items): void
    {
        $stmt = $this->db->prepare('INSERT INTO catalogo_compra_items (compra_id, producto_id, producto_nombre, cantidad, precio_unitario, subtotal, metadata, fecha_creacion) VALUES (:compra_id, :producto_id, :producto_nombre, :cantidad, :precio_unitario, :subtotal, :metadata, NOW())');
        foreach ($items as $item) {
            $stmt->execute([
                'compra_id' => $compraId,
                'producto_id' => (int) ($item['id'] ?? 0),
                'producto_nombre' => (string) ($item['nombre'] ?? ''),
                'cantidad' => (int) ($item['cantidad'] ?? 1),
                'precio_unitario' => (float) ($item['precio'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
                'metadata' => json_encode($item, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function buscarPorToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM catalogo_compras WHERE flow_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarEstadoPorToken(string $token, string $estadoPago, ?array $payload = null): void
    {
        $sql = 'UPDATE catalogo_compras SET estado_pago = :estado_pago, payload_flow = :payload_flow, fecha_actualizacion = NOW(), fecha_confirmacion_pago = CASE WHEN :estado_pago = "aprobado" THEN NOW() ELSE fecha_confirmacion_pago END WHERE flow_token = :token';
        $this->db->prepare($sql)->execute([
            'estado_pago' => $estadoPago,
            'payload_flow' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'token' => $token,
        ]);
    }

    public function listarPorEmpresa(int $empresaId, string $estado = ''): array
    {
        $sql = 'SELECT cc.*, COUNT(cci.id) AS total_items
            FROM catalogo_compras cc
            LEFT JOIN catalogo_compra_items cci ON cci.compra_id = cc.id
            WHERE cc.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];
        if ($estado !== '') {
            $sql .= ' AND cc.estado_pago = :estado';
            $params['estado'] = $estado;
        }
        $sql .= ' GROUP BY cc.id ORDER BY cc.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarItems(int $compraId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM catalogo_compra_items WHERE compra_id = :compra_id ORDER BY id ASC');
        $stmt->execute(['compra_id' => $compraId]);
        return $stmt->fetchAll();
    }
}
