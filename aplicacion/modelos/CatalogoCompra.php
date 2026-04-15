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

    public function descontarStockPorCompraToken(string $token): void
    {
        $compra = $this->buscarPorToken($token);
        if (!$compra || (string) ($compra['estado_pago'] ?? '') !== 'aprobado') {
            return;
        }

        $compraId = (int) ($compra['id'] ?? 0);
        $empresaId = (int) ($compra['empresa_id'] ?? 0);
        if ($compraId <= 0 || $empresaId <= 0) {
            return;
        }

        $items = $this->listarItems($compraId);
        if ($items === []) {
            return;
        }

        $this->db->beginTransaction();
        try {
            $stmtExisteMov = $this->db->prepare('SELECT id FROM movimientos_inventario WHERE empresa_id = :empresa_id AND producto_id = :producto_id AND modulo_origen = "catalogo_checkout" AND referencia_id = :referencia_id LIMIT 1');
            $stmtProducto = $this->db->prepare('SELECT COALESCE(stock_actual,0) AS stock_actual FROM productos WHERE id = :producto_id AND empresa_id = :empresa_id AND fecha_eliminacion IS NULL LIMIT 1 FOR UPDATE');
            $stmtUpdStock = $this->db->prepare('UPDATE productos SET stock_actual = :stock_actual, fecha_actualizacion = NOW() WHERE id = :producto_id AND empresa_id = :empresa_id');
            $stmtMov = $this->db->prepare('INSERT INTO movimientos_inventario (empresa_id,producto_id,tipo_movimiento,modulo_origen,documento_origen,referencia_id,entrada,salida,saldo_resultante,observacion,usuario_id,fecha_creacion) VALUES (:empresa_id,:producto_id,:tipo_movimiento,:modulo_origen,:documento_origen,:referencia_id,:entrada,:salida,:saldo_resultante,:observacion,:usuario_id,NOW())');

            foreach ($items as $item) {
                $productoId = (int) ($item['producto_id'] ?? 0);
                $cantidad = max(0, (float) ($item['cantidad'] ?? 0));
                if ($productoId <= 0 || $cantidad <= 0) {
                    continue;
                }

                $stmtExisteMov->execute([
                    'empresa_id' => $empresaId,
                    'producto_id' => $productoId,
                    'referencia_id' => $compraId,
                ]);
                if ($stmtExisteMov->fetch()) {
                    continue;
                }

                $stmtProducto->execute([
                    'producto_id' => $productoId,
                    'empresa_id' => $empresaId,
                ]);
                $producto = $stmtProducto->fetch();
                if (!$producto) {
                    continue;
                }

                $stockAnterior = (float) ($producto['stock_actual'] ?? 0);
                $stockNuevo = max(0, $stockAnterior - $cantidad);
                $salidaReal = max(0, $stockAnterior - $stockNuevo);

                $stmtUpdStock->execute([
                    'stock_actual' => $stockNuevo,
                    'producto_id' => $productoId,
                    'empresa_id' => $empresaId,
                ]);

                $stmtMov->execute([
                    'empresa_id' => $empresaId,
                    'producto_id' => $productoId,
                    'tipo_movimiento' => 'salida_catalogo',
                    'modulo_origen' => 'catalogo_checkout',
                    'documento_origen' => 'Compra catálogo #' . $compraId,
                    'referencia_id' => $compraId,
                    'entrada' => 0,
                    'salida' => $salidaReal,
                    'saldo_resultante' => $stockNuevo,
                    'observacion' => 'Descuento automático por compra aprobada en catálogo. Token: ' . $token,
                    'usuario_id' => null,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
