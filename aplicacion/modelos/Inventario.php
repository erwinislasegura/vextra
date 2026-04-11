<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;
use Throwable;

class Inventario extends Modelo
{
    public function listarProveedores(int $empresaId, array $filtros = []): array
    {
        $sql = 'SELECT * FROM proveedores_inventario WHERE empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        $busqueda = trim((string) ($filtros['q'] ?? ''));
        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE :busqueda OR identificador_fiscal LIKE :busqueda OR contacto LIKE :busqueda OR correo LIKE :busqueda OR telefono LIKE :busqueda OR ciudad LIKE :busqueda)';
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        $estado = (string) ($filtros['estado'] ?? '');
        if ($estado === 'activo' || $estado === 'inactivo') {
            $sql .= ' AND estado = :estado';
            $params['estado'] = $estado;
        }

        $sql .= ' ORDER BY nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function crearProveedor(int $empresaId, array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO proveedores_inventario (empresa_id,nombre,identificador_fiscal,contacto,correo,telefono,direccion,ciudad,observacion,estado,fecha_creacion) VALUES (:empresa_id,:nombre,:identificador_fiscal,:contacto,:correo,:telefono,:direccion,:ciudad,:observacion,:estado,NOW())');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'nombre' => $data['nombre'],
            'identificador_fiscal' => $data['identificador_fiscal'] ?? null,
            'contacto' => $data['contacto'] ?? null,
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'observacion' => $data['observacion'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
        ]);
        return (int) $this->db->lastInsertId();
    }


    public function obtenerProveedor(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM proveedores_inventario WHERE empresa_id=:empresa_id AND id=:id LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarProveedor(int $empresaId, int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE proveedores_inventario SET nombre=:nombre, identificador_fiscal=:identificador_fiscal, contacto=:contacto, correo=:correo, telefono=:telefono, direccion=:direccion, ciudad=:ciudad, observacion=:observacion, estado=:estado WHERE empresa_id=:empresa_id AND id=:id');
        $stmt->execute([
            'nombre' => $data['nombre'],
            'identificador_fiscal' => $data['identificador_fiscal'] ?? null,
            'contacto' => $data['contacto'] ?? null,
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'observacion' => $data['observacion'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
            'empresa_id' => $empresaId,
            'id' => $id,
        ]);
    }

    public function eliminarProveedor(int $empresaId, int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM proveedores_inventario WHERE empresa_id=:empresa_id AND id=:id');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'id' => $id,
        ]);
    }

    public function listarProductos(int $empresaId): array
    {
        $stmt = $this->db->prepare('SELECT id,codigo,nombre,COALESCE(stock_actual,0) AS stock_actual,COALESCE(stock_minimo,0) AS stock_minimo,COALESCE(stock_critico,0) AS stock_critico FROM productos WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL ORDER BY nombre ASC');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function crearRecepcion(array $cabecera, array $detalles): int
    {
        $this->db->beginTransaction();
        try {
            $stmtCab = $this->db->prepare('INSERT INTO recepciones_inventario (empresa_id,proveedor_id,orden_compra_id,tipo_documento,numero_documento,fecha_documento,referencia_interna,observacion,usuario_id,fecha_creacion) VALUES (:empresa_id,:proveedor_id,:orden_compra_id,:tipo_documento,:numero_documento,:fecha_documento,:referencia_interna,:observacion,:usuario_id,NOW())');
            $stmtCab->execute($cabecera);
            $recepcionId = (int) $this->db->lastInsertId();

            $stmtDet = $this->db->prepare('INSERT INTO recepciones_inventario_detalle (recepcion_id,producto_id,cantidad,costo_unitario,subtotal) VALUES (:recepcion_id,:producto_id,:cantidad,:costo_unitario,:subtotal)');
            $stmtProd = $this->db->prepare('SELECT nombre,COALESCE(stock_actual,0) AS stock_actual FROM productos WHERE id=:producto_id AND empresa_id=:empresa_id AND fecha_eliminacion IS NULL LIMIT 1 FOR UPDATE');
            $stmtUpd = $this->db->prepare('UPDATE productos SET stock_actual = :stock_actual, fecha_actualizacion = NOW() WHERE id=:producto_id AND empresa_id=:empresa_id');
            $stmtMov = $this->db->prepare('INSERT INTO movimientos_inventario (empresa_id,producto_id,tipo_movimiento,modulo_origen,documento_origen,referencia_id,entrada,salida,saldo_resultante,observacion,usuario_id,fecha_creacion) VALUES (:empresa_id,:producto_id,:tipo_movimiento,:modulo_origen,:documento_origen,:referencia_id,:entrada,:salida,:saldo_resultante,:observacion,:usuario_id,NOW())');

            $stocks = [];
            foreach ($detalles as $detalle) {
                $stmtDet->execute([
                    'recepcion_id' => $recepcionId,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                $stmtProd->execute([
                    'producto_id' => $detalle['producto_id'],
                    'empresa_id' => $cabecera['empresa_id'],
                ]);
                $producto = $stmtProd->fetch();
                if (!$producto) {
                    throw new \RuntimeException('Producto inválido en la recepción.');
                }

                $stockAnterior = (float) $producto['stock_actual'];
                $stockNuevo = $stockAnterior + (float) $detalle['cantidad'];
                $stmtUpd->execute([
                    'stock_actual' => $stockNuevo,
                    'producto_id' => $detalle['producto_id'],
                    'empresa_id' => $cabecera['empresa_id'],
                ]);

                $stmtMov->execute([
                    'empresa_id' => $cabecera['empresa_id'],
                    'producto_id' => $detalle['producto_id'],
                    'tipo_movimiento' => 'recepcion_proveedor',
                    'modulo_origen' => 'recepciones_inventario',
                    'documento_origen' => $cabecera['tipo_documento'] . ' #' . $cabecera['numero_documento'],
                    'referencia_id' => $recepcionId,
                    'entrada' => $detalle['cantidad'],
                    'salida' => 0,
                    'saldo_resultante' => $stockNuevo,
                    'observacion' => $cabecera['observacion'],
                    'usuario_id' => $cabecera['usuario_id'],
                ]);

                $stocks[] = [
                    'producto_id' => (int) $detalle['producto_id'],
                    'stock_anterior' => $stockAnterior,
                    'stock_actual' => $stockNuevo,
                ];
            }

            $this->db->commit();

            if (!empty($cabecera['orden_compra_id'])) {
                $this->actualizarEstadoOrdenCompraManual((int) $cabecera['empresa_id'], (int) $cabecera['orden_compra_id'], 'recepcionada');
            }
            return $recepcionId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function crearAjuste(array $data, bool $permitirNegativo = false): int
    {
        $this->db->beginTransaction();
        try {
            $stmtProd = $this->db->prepare('SELECT COALESCE(stock_actual,0) AS stock_actual FROM productos WHERE id=:producto_id AND empresa_id=:empresa_id AND fecha_eliminacion IS NULL LIMIT 1 FOR UPDATE');
            $stmtProd->execute(['producto_id' => $data['producto_id'], 'empresa_id' => $data['empresa_id']]);
            $producto = $stmtProd->fetch();
            if (!$producto) {
                throw new \RuntimeException('Producto no encontrado para ajuste.');
            }

            $stockAnterior = (float) $producto['stock_actual'];
            $cantidad = (float) $data['cantidad'];
            $entrada = $data['tipo_ajuste'] === 'entrada' ? $cantidad : 0;
            $salida = $data['tipo_ajuste'] === 'salida' ? $cantidad : 0;
            $stockNuevo = $stockAnterior + $entrada - $salida;

            if (!$permitirNegativo && $stockNuevo < 0) {
                throw new \RuntimeException('El ajuste deja el stock en negativo y no está permitido.');
            }

            $this->db->prepare('INSERT INTO ajustes_inventario (empresa_id,producto_id,tipo_ajuste,cantidad,motivo,observacion,usuario_id,fecha_creacion) VALUES (:empresa_id,:producto_id,:tipo_ajuste,:cantidad,:motivo,:observacion,:usuario_id,NOW())')
                ->execute($data);
            $ajusteId = (int) $this->db->lastInsertId();

            $this->db->prepare('UPDATE productos SET stock_actual=:stock_actual, fecha_actualizacion=NOW() WHERE id=:producto_id AND empresa_id=:empresa_id')
                ->execute([
                    'stock_actual' => $stockNuevo,
                    'producto_id' => $data['producto_id'],
                    'empresa_id' => $data['empresa_id'],
                ]);

            $this->db->prepare('INSERT INTO movimientos_inventario (empresa_id,producto_id,tipo_movimiento,modulo_origen,documento_origen,referencia_id,entrada,salida,saldo_resultante,observacion,usuario_id,fecha_creacion) VALUES (:empresa_id,:producto_id,:tipo_movimiento,:modulo_origen,:documento_origen,:referencia_id,:entrada,:salida,:saldo_resultante,:observacion,:usuario_id,NOW())')
                ->execute([
                    'empresa_id' => $data['empresa_id'],
                    'producto_id' => $data['producto_id'],
                    'tipo_movimiento' => $data['tipo_ajuste'] === 'entrada' ? 'ajuste_entrada' : 'ajuste_salida',
                    'modulo_origen' => 'ajustes_inventario',
                    'documento_origen' => 'ajuste #' . $ajusteId,
                    'referencia_id' => $ajusteId,
                    'entrada' => $entrada,
                    'salida' => $salida,
                    'saldo_resultante' => $stockNuevo,
                    'observacion' => trim($data['motivo'] . ' ' . $data['observacion']),
                    'usuario_id' => $data['usuario_id'],
                ]);

            $this->db->commit();
            return $ajusteId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listarRecepciones(int $empresaId, array $filtros = []): array
    {
        $sql = 'SELECT r.*, p.nombre AS proveedor_nombre, p.correo AS proveedor_correo, u.nombre AS usuario_nombre
            FROM recepciones_inventario r
            LEFT JOIN proveedores_inventario p ON p.id = r.proveedor_id
            LEFT JOIN usuarios u ON u.id = r.usuario_id
            WHERE r.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        $busqueda = trim((string) ($filtros['q'] ?? ''));
        if ($busqueda !== '') {
            $sql .= ' AND (p.nombre LIKE :busqueda OR r.numero_documento LIKE :busqueda OR r.referencia_interna LIKE :busqueda OR r.observacion LIKE :busqueda)';
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        $tipoDocumento = (string) ($filtros['tipo_documento'] ?? '');
        if ($tipoDocumento !== '') {
            $sql .= ' AND r.tipo_documento = :tipo_documento';
            $params['tipo_documento'] = $tipoDocumento;
        }

        $sql .= ' ORDER BY r.id DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerRecepcion(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT r.*, p.nombre AS proveedor_nombre, p.correo AS proveedor_correo, u.nombre AS usuario_nombre
            FROM recepciones_inventario r
            LEFT JOIN proveedores_inventario p ON p.id = r.proveedor_id
            LEFT JOIN usuarios u ON u.id = r.usuario_id
            WHERE r.empresa_id = :empresa_id AND r.id = :id LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        $recepcion = $stmt->fetch();
        if (!$recepcion) {
            return null;
        }

        $stmtDet = $this->db->prepare('SELECT d.*, pr.codigo, pr.nombre
            FROM recepciones_inventario_detalle d
            INNER JOIN productos pr ON pr.id = d.producto_id
            WHERE d.recepcion_id = :recepcion_id ORDER BY d.id ASC');
        $stmtDet->execute(['recepcion_id' => $id]);
        $recepcion['detalles'] = $stmtDet->fetchAll();

        return $recepcion;
    }

    public function actualizarRecepcionBasica(int $empresaId, int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE recepciones_inventario SET proveedor_id=:proveedor_id, tipo_documento=:tipo_documento, numero_documento=:numero_documento, fecha_documento=:fecha_documento, referencia_interna=:referencia_interna, observacion=:observacion, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id');
        $stmt->execute([
            'proveedor_id' => $data['proveedor_id'],
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
            'fecha_documento' => $data['fecha_documento'],
            'referencia_interna' => $data['referencia_interna'],
            'observacion' => $data['observacion'],
            'empresa_id' => $empresaId,
            'id' => $id,
        ]);
    }

    public function eliminarRecepcionCompleta(int $empresaId, int $recepcionId): void
    {
        $this->db->beginTransaction();
        try {
            $stmtRecepcion = $this->db->prepare('SELECT id, orden_compra_id FROM recepciones_inventario WHERE empresa_id=:empresa_id AND id=:id LIMIT 1');
            $stmtRecepcion->execute(['empresa_id' => $empresaId, 'id' => $recepcionId]);
            $recepcion = $stmtRecepcion->fetch();
            if (!$recepcion) {
                $this->db->rollBack();
                return;
            }

            $stmtDetalles = $this->db->prepare('SELECT producto_id, cantidad FROM recepciones_inventario_detalle WHERE recepcion_id=:recepcion_id');
            $stmtDetalles->execute(['recepcion_id' => $recepcionId]);
            $detalles = $stmtDetalles->fetchAll();

            $stmtStock = $this->db->prepare('UPDATE productos SET stock_actual = stock_actual - :cantidad, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:producto_id');
            foreach ($detalles as $detalle) {
                $cantidad = max(0, (float) ($detalle['cantidad'] ?? 0));
                $productoId = (int) ($detalle['producto_id'] ?? 0);
                if ($productoId <= 0 || $cantidad <= 0) {
                    continue;
                }
                $stmtStock->execute([
                    'cantidad' => $cantidad,
                    'empresa_id' => $empresaId,
                    'producto_id' => $productoId,
                ]);
            }

            $this->db->prepare('DELETE FROM movimientos_inventario WHERE empresa_id=:empresa_id AND modulo_origen="recepciones_inventario" AND referencia_id=:referencia_id')
                ->execute(['empresa_id' => $empresaId, 'referencia_id' => $recepcionId]);

            $this->db->prepare('DELETE FROM recepciones_inventario_detalle WHERE recepcion_id=:recepcion_id')
                ->execute(['recepcion_id' => $recepcionId]);
            $this->db->prepare('DELETE FROM recepciones_inventario WHERE empresa_id=:empresa_id AND id=:id')
                ->execute(['empresa_id' => $empresaId, 'id' => $recepcionId]);

            if (!empty($recepcion['orden_compra_id'])) {
                $ordenCompraId = (int) $recepcion['orden_compra_id'];
                $stmtConteo = $this->db->prepare('SELECT COUNT(*) FROM recepciones_inventario WHERE empresa_id=:empresa_id AND orden_compra_id=:orden_compra_id');
                $stmtConteo->execute(['empresa_id' => $empresaId, 'orden_compra_id' => $ordenCompraId]);
                $totalRecepciones = (int) $stmtConteo->fetchColumn();
                $this->actualizarEstadoOrdenCompraManual($empresaId, $ordenCompraId, $totalRecepciones > 0 ? 'recepcionada' : 'aprobada');
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listarAjustes(int $empresaId, array $filtros = []): array
    {
        $sql = 'SELECT a.*, pr.codigo, pr.nombre AS producto_nombre, u.nombre AS usuario_nombre
            FROM ajustes_inventario a
            INNER JOIN productos pr ON pr.id = a.producto_id
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        if (!empty($filtros['producto_id'])) {
            $sql .= ' AND a.producto_id = :producto_id';
            $params['producto_id'] = (int) $filtros['producto_id'];
        }
        if (!empty($filtros['tipo_ajuste'])) {
            $sql .= ' AND a.tipo_ajuste = :tipo_ajuste';
            $params['tipo_ajuste'] = $filtros['tipo_ajuste'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND DATE(a.fecha_creacion) >= :fecha_desde';
            $params['fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND DATE(a.fecha_creacion) <= :fecha_hasta';
            $params['fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= ' ORDER BY a.id DESC LIMIT 400';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerAjuste(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT a.*, pr.codigo, pr.nombre AS producto_nombre, pr.stock_actual, u.nombre AS usuario_nombre
            FROM ajustes_inventario a
            INNER JOIN productos pr ON pr.id = a.producto_id
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.empresa_id = :empresa_id AND a.id = :id LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function listarMovimientos(int $empresaId, ?int $productoId = null): array
    {
        $sql = 'SELECT m.*, p.codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
            FROM movimientos_inventario m
            INNER JOIN productos p ON p.id = m.producto_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            WHERE m.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];
        if ($productoId) {
            $sql .= ' AND m.producto_id = :producto_id';
            $params['producto_id'] = $productoId;
        }
        $sql .= ' ORDER BY m.id DESC LIMIT 500';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerAjustePermitirNegativo(int $empresaId): bool
    {
        $stmt = $this->db->prepare('SELECT valor FROM configuraciones_empresa WHERE empresa_id=:empresa_id AND clave = "inventario_permitir_stock_negativo" LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId]);
        return ((string) $stmt->fetchColumn()) === '1';
    }

    public function listarOrdenesCompra(int $empresaId, array $filtros = []): array
    {
        $sql = 'SELECT o.*, p.nombre AS proveedor_nombre, u.nombre AS usuario_nombre,
            (SELECT r.numero_documento FROM recepciones_inventario r WHERE r.empresa_id = o.empresa_id AND r.orden_compra_id = o.id ORDER BY r.id DESC LIMIT 1) AS numero_recepcion
            FROM ordenes_compra o
            LEFT JOIN proveedores_inventario p ON p.id = o.proveedor_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id
            WHERE o.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        $busqueda = trim((string) ($filtros['q'] ?? ''));
        if ($busqueda !== '') {
            $sql .= ' AND (o.numero LIKE :busqueda OR p.nombre LIKE :busqueda OR o.referencia LIKE :busqueda OR o.observacion LIKE :busqueda)';
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        $estado = (string) ($filtros['estado'] ?? '');
        if ($estado !== '') {
            $sql .= ' AND o.estado = :estado';
            $params['estado'] = $estado;
        }

        $sql .= ' ORDER BY o.id DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarOrdenesCompraAprobadasDisponiblesParaCotizacion(int $empresaId, ?int $cotizacionIdActual = null): array
    {
        $sql = 'SELECT o.*, p.nombre AS proveedor_nombre, u.nombre AS usuario_nombre
            FROM ordenes_compra o
            LEFT JOIN proveedores_inventario p ON p.id = o.proveedor_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id
            WHERE o.empresa_id = :empresa_id
              AND o.estado = "aprobada"
              AND NOT EXISTS (
                SELECT 1
                FROM cotizaciones c
                WHERE c.empresa_id = o.empresa_id
                  AND c.orden_compra_origen_id = o.id
                  AND c.fecha_eliminacion IS NULL
                  AND c.estado NOT IN ("rechazada", "anulada")';

        $params = ['empresa_id' => $empresaId];
        if ($cotizacionIdActual !== null && $cotizacionIdActual > 0) {
            $sql .= ' AND c.id <> :cotizacion_id_actual';
            $params['cotizacion_id_actual'] = $cotizacionIdActual;
        }
        $sql .= ') ORDER BY o.id DESC LIMIT 300';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function siguienteNumeroOrdenCompra(int $empresaId): string
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM ordenes_compra WHERE empresa_id = :empresa_id');
        $stmt->execute(['empresa_id' => $empresaId]);
        $n = (int) ($stmt->fetch()['total'] ?? 0) + 1;
        return 'OC-' . str_pad((string) $empresaId, 3, '0', STR_PAD_LEFT) . '-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }

    public function crearOrdenCompra(array $cabecera, array $detalles): int
    {
        $this->db->beginTransaction();
        try {
            $stmtCab = $this->db->prepare('INSERT INTO ordenes_compra (empresa_id,proveedor_id,numero,fecha_emision,fecha_entrega_estimada,estado,referencia,observacion,usuario_id,token_publico,fecha_creacion) VALUES (:empresa_id,:proveedor_id,:numero,:fecha_emision,:fecha_entrega_estimada,:estado,:referencia,:observacion,:usuario_id,:token_publico,NOW())');
            $stmtCab->execute($cabecera);
            $ordenId = (int) $this->db->lastInsertId();

            $stmtDet = $this->db->prepare('INSERT INTO ordenes_compra_detalle (orden_compra_id,producto_id,cantidad,costo_unitario,subtotal,fecha_creacion) VALUES (:orden_compra_id,:producto_id,:cantidad,:costo_unitario,:subtotal,NOW())');
            foreach ($detalles as $detalle) {
                $stmtDet->execute([
                    'orden_compra_id' => $ordenId,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }

            $this->db->commit();
            return $ordenId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerOrdenCompra(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, p.nombre AS proveedor_nombre, p.correo AS proveedor_correo, u.nombre AS usuario_nombre
            FROM ordenes_compra o
            LEFT JOIN proveedores_inventario p ON p.id = o.proveedor_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id
            WHERE o.empresa_id=:empresa_id AND o.id=:id LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        $orden = $stmt->fetch();
        if (!$orden) {
            return null;
        }

        $stmtDet = $this->db->prepare('SELECT d.*, pr.codigo, pr.nombre
            FROM ordenes_compra_detalle d
            INNER JOIN productos pr ON pr.id = d.producto_id
            WHERE d.orden_compra_id=:orden_compra_id ORDER BY d.id ASC');
        $stmtDet->execute(['orden_compra_id' => $id]);
        $orden['detalles'] = $stmtDet->fetchAll();

        return $orden;
    }

    public function actualizarOrdenCompra(int $empresaId, int $id, array $cabecera, array $detalles): void
    {
        $this->db->beginTransaction();
        try {
            $stmtCab = $this->db->prepare('UPDATE ordenes_compra SET proveedor_id=:proveedor_id, numero=:numero, fecha_emision=:fecha_emision, fecha_entrega_estimada=:fecha_entrega_estimada, referencia=:referencia, observacion=:observacion, usuario_id=:usuario_id, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id');
            $stmtCab->execute([
                'proveedor_id' => $cabecera['proveedor_id'],
                'numero' => $cabecera['numero'],
                'fecha_emision' => $cabecera['fecha_emision'],
                'fecha_entrega_estimada' => $cabecera['fecha_entrega_estimada'],
                'referencia' => $cabecera['referencia'],
                'observacion' => $cabecera['observacion'],
                'usuario_id' => $cabecera['usuario_id'],
                'empresa_id' => $empresaId,
                'id' => $id,
            ]);

            $this->db->prepare('DELETE FROM ordenes_compra_detalle WHERE orden_compra_id = :orden_compra_id')
                ->execute(['orden_compra_id' => $id]);

            $stmtDet = $this->db->prepare('INSERT INTO ordenes_compra_detalle (orden_compra_id,producto_id,cantidad,costo_unitario,subtotal,fecha_creacion) VALUES (:orden_compra_id,:producto_id,:cantidad,:costo_unitario,:subtotal,NOW())');
            foreach ($detalles as $detalle) {
                $stmtDet->execute([
                    'orden_compra_id' => $id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarTokenPublicoOrdenCompra(int $empresaId, int $id, string $token): void
    {
        $stmt = $this->db->prepare('UPDATE ordenes_compra SET token_publico=:token_publico, fecha_actualizacion=NOW() WHERE empresa_id=:empresa_id AND id=:id');
        $stmt->execute([
            'token_publico' => $token,
            'empresa_id' => $empresaId,
            'id' => $id,
        ]);
    }

    public function obtenerOrdenCompraPorTokenPublico(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, p.nombre AS proveedor_nombre, p.correo AS proveedor_correo, u.nombre AS usuario_nombre
            FROM ordenes_compra o
            LEFT JOIN proveedores_inventario p ON p.id = o.proveedor_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id
            WHERE o.token_publico = :token_publico LIMIT 1');
        $stmt->execute(['token_publico' => $token]);
        $orden = $stmt->fetch();
        if (!$orden) {
            return null;
        }

        $stmtDet = $this->db->prepare('SELECT d.*, pr.codigo, pr.nombre
            FROM ordenes_compra_detalle d
            INNER JOIN productos pr ON pr.id = d.producto_id
            WHERE d.orden_compra_id=:orden_compra_id ORDER BY d.id ASC');
        $stmtDet->execute(['orden_compra_id' => (int) $orden['id']]);
        $orden['detalles'] = $stmtDet->fetchAll();

        return $orden;
    }

    public function actualizarEstadoOrdenCompra(int $empresaId, int $ordenCompraId): void
    {
        $stmtRec = $this->db->prepare('SELECT COALESCE(SUM(rd.cantidad),0) AS recibido
            FROM recepciones_inventario r
            INNER JOIN recepciones_inventario_detalle rd ON rd.recepcion_id = r.id
            WHERE r.empresa_id = :empresa_id AND r.orden_compra_id = :orden_compra_id');
        $stmtRec->execute(['empresa_id' => $empresaId, 'orden_compra_id' => $ordenCompraId]);
        $recibido = (float) ($stmtRec->fetch()['recibido'] ?? 0);

        $estado = 'emitida';
        if ($recibido > 0) {
            $estado = 'recepcionada';
        }

        $this->db->prepare('UPDATE ordenes_compra SET estado = :estado, fecha_actualizacion = NOW() WHERE empresa_id = :empresa_id AND id = :id')
            ->execute(['estado' => $estado, 'empresa_id' => $empresaId, 'id' => $ordenCompraId]);
    }

    public function actualizarEstadoOrdenCompraManual(int $empresaId, int $ordenCompraId, string $estado): void
    {
        $estadoNormalizado = mb_strtolower(trim($estado));
        if (!in_array($estadoNormalizado, ['aprobada', 'rechazada', 'recepcionada'], true)) {
            throw new \InvalidArgumentException('Estado de orden de compra no permitido.');
        }

        $stmt = $this->db->prepare('UPDATE ordenes_compra SET estado = :estado, fecha_actualizacion = NOW() WHERE empresa_id = :empresa_id AND id = :id');
        $stmt->execute([
            'estado' => $estadoNormalizado,
            'empresa_id' => $empresaId,
            'id' => $ordenCompraId,
        ]);
    }
}
