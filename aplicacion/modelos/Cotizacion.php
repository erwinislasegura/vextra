<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Cotizacion extends Modelo
{
    public function listar(int $empresaId): array
    {
        $sql = 'SELECT c.*,
            COALESCE(NULLIF(cl.razon_social, ""), NULLIF(cl.nombre_comercial, ""), cl.nombre) AS cliente,
            u.nombre AS vendedor
            FROM cotizaciones c
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.empresa_id = :empresa_id AND c.fecha_eliminacion IS NULL
            ORDER BY c.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function contarMes(int $empresaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND MONTH(fecha_emision)=MONTH(CURDATE()) AND YEAR(fecha_emision)=YEAR(CURDATE()) AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetch()['total'];
    }

    public function siguienteNumero(int $empresaId): string
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(consecutivo),0)+1 AS siguiente FROM cotizaciones WHERE empresa_id=:empresa_id');
        $stmt->execute(['empresa_id' => $empresaId]);
        $num = (int) $stmt->fetch()['siguiente'];
        return 'COT-' . str_pad((string) $empresaId, 3, '0', STR_PAD_LEFT) . '-' . str_pad((string) $num, 6, '0', STR_PAD_LEFT);
    }

    public function crearConItems(array $cotizacion, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $sql = 'INSERT INTO cotizaciones (empresa_id, cliente_id, usuario_id, numero, consecutivo, estado, subtotal, descuento_tipo, descuento_valor, descuento, impuesto, total, observaciones, terminos_condiciones, lista_precio_id, token_publico, fecha_emision, fecha_vencimiento, fecha_creacion) VALUES (:empresa_id,:cliente_id,:usuario_id,:numero,:consecutivo,:estado,:subtotal,:descuento_tipo,:descuento_valor,:descuento,:impuesto,:total,:observaciones,:terminos_condiciones,:lista_precio_id,:token_publico,:fecha_emision,:fecha_vencimiento,NOW())';
            $this->db->prepare($sql)->execute($cotizacion);
            $cotizacionId = (int) $this->db->lastInsertId();

            $sqlItem = 'INSERT INTO items_cotizacion (cotizacion_id, producto_id, descripcion, cantidad, precio_unitario, descuento_tipo, descuento_valor, descuento_monto, porcentaje_impuesto, subtotal, total, fecha_creacion) VALUES (:cotizacion_id,:producto_id,:descripcion,:cantidad,:precio_unitario,:descuento_tipo,:descuento_valor,:descuento_monto,:porcentaje_impuesto,:subtotal,:total,NOW())';
            $stmtItem = $this->db->prepare($sqlItem);
            foreach ($items as $item) {
                $item['cotizacion_id'] = $cotizacionId;
                $stmtItem->execute($item);
            }

            $this->db->prepare('INSERT INTO historial_estados_cotizacion (cotizacion_id, estado, observaciones, usuario_id, fecha_creacion) VALUES (:cotizacion_id,:estado,:observaciones,:usuario_id,NOW())')->execute([
                'cotizacion_id' => $cotizacionId,
                'estado' => $cotizacion['estado'],
                'observaciones' => 'Creación inicial',
                'usuario_id' => $cotizacion['usuario_id'],
            ]);

            $this->db->commit();
            return $cotizacionId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT c.*,
                COALESCE(NULLIF(cl.razon_social, ""), NULLIF(cl.nombre_comercial, ""), cl.nombre) AS cliente,
                cl.razon_social AS cliente_razon_social,
                cl.identificador_fiscal AS cliente_identificador_fiscal,
                cl.correo AS cliente_correo,
                cl.telefono AS cliente_telefono,
                cl.direccion AS cliente_direccion,
                cl.ciudad AS cliente_ciudad,
                u.nombre AS vendedor
            FROM cotizaciones c
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.empresa_id=:empresa_id AND c.id=:id AND c.fecha_eliminacion IS NULL
            LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        $cotizacion = $stmt->fetch() ?: null;
        if (!$cotizacion) {
            return null;
        }
        $stmtItems = $this->db->prepare('SELECT ic.*, p.codigo, p.nombre AS producto_nombre, p.descripcion AS producto_descripcion, p.unidad
            FROM items_cotizacion ic
            LEFT JOIN productos p ON p.id = ic.producto_id
            WHERE ic.cotizacion_id=:cotizacion_id
            ORDER BY ic.id ASC');
        $stmtItems->execute(['cotizacion_id' => $id]);
        $cotizacion['items'] = $stmtItems->fetchAll();
        return $cotizacion;
    }

    public function actualizarTokenPublico(int $empresaId, int $id, string $token): void
    {
        $stmt = $this->db->prepare('UPDATE cotizaciones SET token_publico=:token_publico, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL');
        $stmt->execute([
            'token_publico' => $token,
            'empresa_id' => $empresaId,
            'id' => $id,
        ]);
    }

    public function obtenerPorTokenPublico(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT c.*,
                COALESCE(NULLIF(cl.razon_social, ""), NULLIF(cl.nombre_comercial, ""), cl.nombre) AS cliente,
                cl.razon_social AS cliente_razon_social,
                cl.identificador_fiscal AS cliente_identificador_fiscal,
                cl.correo AS cliente_correo,
                cl.telefono AS cliente_telefono,
                cl.direccion AS cliente_direccion,
                cl.ciudad AS cliente_ciudad,
                u.nombre AS vendedor
            FROM cotizaciones c
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.token_publico=:token_publico AND c.fecha_eliminacion IS NULL
            LIMIT 1');
        $stmt->execute(['token_publico' => $token]);
        $cotizacion = $stmt->fetch() ?: null;
        if (!$cotizacion) {
            return null;
        }

        $stmtItems = $this->db->prepare('SELECT ic.*, p.codigo, p.nombre AS producto_nombre, p.descripcion AS producto_descripcion, p.unidad
            FROM items_cotizacion ic
            LEFT JOIN productos p ON p.id = ic.producto_id
            WHERE ic.cotizacion_id=:cotizacion_id
            ORDER BY ic.id ASC');
        $stmtItems->execute(['cotizacion_id' => (int) $cotizacion['id']]);
        $cotizacion['items'] = $stmtItems->fetchAll();
        return $cotizacion;
    }

    public function actualizarBasico(int $empresaId, int $id, array $data): void
    {
        $sql = 'UPDATE cotizaciones SET estado=:estado, observaciones=:observaciones, terminos_condiciones=:terminos_condiciones, fecha_vencimiento=:fecha_vencimiento, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';
        $data['empresa_id'] = $empresaId;
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function actualizarDecisionPublica(int $empresaId, int $id, array $data): void
    {
        $sql = 'UPDATE cotizaciones SET
            estado=:estado,
            observaciones=:observaciones,
            terminos_condiciones=:terminos_condiciones,
            fecha_vencimiento=:fecha_vencimiento,
            firma_cliente=:firma_cliente,
            nombre_firmante_cliente=:nombre_firmante_cliente,
            fecha_aprobacion_cliente=:fecha_aprobacion_cliente,
            fecha_actualizacion=NOW()
            WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';

        $data['empresa_id'] = $empresaId;
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function actualizarConItems(int $empresaId, int $id, array $cotizacion, array $items): void
    {
        $this->db->beginTransaction();
        try {
            $sql = 'UPDATE cotizaciones SET cliente_id=:cliente_id, estado=:estado, subtotal=:subtotal, descuento_tipo=:descuento_tipo, descuento_valor=:descuento_valor, descuento=:descuento, impuesto=:impuesto, total=:total, observaciones=:observaciones, terminos_condiciones=:terminos_condiciones, lista_precio_id=:lista_precio_id, fecha_emision=:fecha_emision, fecha_vencimiento=:fecha_vencimiento, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';
            $cotizacion['empresa_id'] = $empresaId;
            $cotizacion['id'] = $id;
            $this->db->prepare($sql)->execute($cotizacion);

            $this->db->prepare('DELETE FROM items_cotizacion WHERE cotizacion_id=:cotizacion_id')->execute(['cotizacion_id' => $id]);
            $sqlItem = 'INSERT INTO items_cotizacion (cotizacion_id, producto_id, descripcion, cantidad, precio_unitario, descuento_tipo, descuento_valor, descuento_monto, porcentaje_impuesto, subtotal, total, fecha_creacion) VALUES (:cotizacion_id,:producto_id,:descripcion,:cantidad,:precio_unitario,:descuento_tipo,:descuento_valor,:descuento_monto,:porcentaje_impuesto,:subtotal,:total,NOW())';
            $stmtItem = $this->db->prepare($sqlItem);
            foreach ($items as $item) {
                $item['cotizacion_id'] = $id;
                $stmtItem->execute($item);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminar(int $empresaId, int $id): void
    {
        $stmt = $this->db->prepare('UPDATE cotizaciones SET fecha_eliminacion = NOW(), estado = "anulada" WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
    }

    public function actualizarEstadoConHistorial(int $empresaId, int $id, string $estado, int $usuarioId, string $observaciones = ''): void
    {
        $this->db->beginTransaction();
        try {
            $stmtCotizacion = $this->db->prepare('SELECT id, estado FROM cotizaciones WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL LIMIT 1');
            $stmtCotizacion->execute(['empresa_id' => $empresaId, 'id' => $id]);
            $cotizacion = $stmtCotizacion->fetch();
            if (!$cotizacion) {
                $this->db->rollBack();
                return;
            }

            $stmtUpdate = $this->db->prepare('UPDATE cotizaciones SET estado = :estado, fecha_actualizacion = NOW() WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL');
            $stmtUpdate->execute([
                'estado' => $estado,
                'empresa_id' => $empresaId,
                'id' => $id,
            ]);

            $stmtHistorial = $this->db->prepare('INSERT INTO historial_estados_cotizacion (cotizacion_id, estado, observaciones, usuario_id, fecha_creacion) VALUES (:cotizacion_id, :estado, :observaciones, :usuario_id, NOW())');
            $stmtHistorial->execute([
                'cotizacion_id' => $id,
                'estado' => $estado,
                'observaciones' => $observaciones !== '' ? $observaciones : 'Cambio de estado desde seguimiento comercial',
                'usuario_id' => $usuarioId,
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
