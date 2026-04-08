<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class GestionComercial extends Modelo
{
    private array $tablasPermitidas = [
        'contactos_cliente',
        'vendedores',
        'categorias_productos',
        'listas_precios',
        'seguimientos_comerciales',
        'aprobaciones_cotizacion',
        'documentos_plantillas',
        'notificaciones_empresa',
        'historial_actividad',
    ];

    public function listarTablaEmpresa(string $tabla, int $empresaId, string $buscar = '', int $limite = 10): array
    {
        $permitidas = [
            'vendedores' => ['nombre', 'correo'],
            'categorias_productos' => ['nombre', 'descripcion'],
            'listas_precios' => ['nombre', 'tipo_lista'],
            'seguimientos_comerciales' => ['proxima_accion', 'estado_comercial'],
            'aprobaciones_cotizacion' => ['motivo', 'estado'],
            'documentos_plantillas' => ['nombre', 'tipo_documento'],
            'notificaciones_empresa' => ['titulo', 'tipo'],
            'historial_actividad' => ['modulo', 'accion'],
            'contactos_cliente' => ['nombre', 'correo'],
        ];

        if (!isset($permitidas[$tabla])) {
            return [];
        }

        $sql = "SELECT * FROM {$tabla} WHERE empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];

        if ($buscar !== '') {
            $condiciones = [];
            foreach ($permitidas[$tabla] as $indice => $campo) {
                $llave = "buscar_{$indice}";
                $condiciones[] = "{$campo} LIKE :{$llave}";
                $params[$llave] = "%{$buscar}%";
            }
            $sql .= ' AND (' . implode(' OR ', $condiciones) . ')';
        }

        if ($tabla === 'historial_actividad') {
            $sql .= ' ORDER BY fecha_creacion DESC';
        } else {
            $sql .= ' ORDER BY id DESC';
        }
        $sql .= ' LIMIT :limite';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarContactosRegistrados(int $empresaId, string $buscar = '', int $limite = 30): array
    {
        $sql = 'SELECT cc.*, c.nombre AS cliente_nombre, c.razon_social AS cliente_razon_social, c.estado AS cliente_estado
            FROM contactos_cliente cc
            INNER JOIN clientes c ON c.id = cc.cliente_id AND c.empresa_id = cc.empresa_id
            WHERE cc.empresa_id = :empresa_id
              AND c.fecha_eliminacion IS NULL';
        $params = ['empresa_id' => $empresaId];

        if ($buscar !== '') {
            $sql .= ' AND (
                cc.nombre LIKE :buscar
                OR cc.correo LIKE :buscar
                OR cc.cargo LIKE :buscar
                OR c.nombre LIKE :buscar
                OR c.razon_social LIKE :buscar
            )';
            $params['buscar'] = "%{$buscar}%";
        }

        $sql .= ' ORDER BY cc.id DESC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarSeguimientoCotizaciones(int $empresaId, string $buscar = '', string $estadoCotizacion = '', int $limite = 60): array
    {
        $sql = 'SELECT sc.*, c.numero AS cotizacion_numero, c.estado AS cotizacion_estado,
                COALESCE(NULLIF(cl.razon_social, ""), NULLIF(cl.nombre_comercial, ""), cl.nombre) AS cliente_nombre
            FROM seguimientos_comerciales sc
            LEFT JOIN cotizaciones c ON c.id = sc.cotizacion_id AND c.empresa_id = sc.empresa_id AND c.fecha_eliminacion IS NULL
            LEFT JOIN clientes cl ON cl.id = sc.cliente_id AND cl.empresa_id = sc.empresa_id AND cl.fecha_eliminacion IS NULL
            WHERE sc.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        if ($buscar !== '') {
            $sql .= ' AND (
                sc.responsable LIKE :buscar
                OR sc.proxima_accion LIKE :buscar
                OR sc.estado_comercial LIKE :buscar
                OR sc.comentarios LIKE :buscar
                OR c.numero LIKE :buscar
                OR cl.nombre LIKE :buscar
                OR cl.razon_social LIKE :buscar
            )';
            $params['buscar'] = "%{$buscar}%";
        }

        if ($estadoCotizacion !== '') {
            $sql .= ' AND c.estado = :estado_cotizacion';
            $params['estado_cotizacion'] = $estadoCotizacion;
        }

        $sql .= ' ORDER BY COALESCE(sc.fecha_seguimiento, DATE(sc.fecha_creacion)) DESC, sc.id DESC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarAprobacionesCotizaciones(int $empresaId, string $buscar = '', string $estadoAprobacion = '', int $limite = 60): array
    {
        $sql = 'SELECT ac.*, c.numero AS cotizacion_numero, c.estado AS cotizacion_estado,
                COALESCE(NULLIF(cl.razon_social, ""), NULLIF(cl.nombre_comercial, ""), cl.nombre) AS cliente_nombre
            FROM aprobaciones_cotizacion ac
            LEFT JOIN cotizaciones c ON c.id = ac.cotizacion_id AND c.empresa_id = ac.empresa_id AND c.fecha_eliminacion IS NULL
            LEFT JOIN clientes cl ON cl.id = c.cliente_id AND cl.empresa_id = ac.empresa_id AND cl.fecha_eliminacion IS NULL
            WHERE ac.empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        if ($buscar !== '') {
            $sql .= ' AND (
                ac.motivo LIKE :buscar
                OR ac.solicitante LIKE :buscar
                OR ac.aprobador LIKE :buscar
                OR ac.observaciones LIKE :buscar
                OR c.numero LIKE :buscar
                OR cl.nombre LIKE :buscar
                OR cl.razon_social LIKE :buscar
            )';
            $params['buscar'] = "%{$buscar}%";
        }

        if ($estadoAprobacion !== '') {
            $sql .= ' AND ac.estado = :estado_aprobacion';
            $params['estado_aprobacion'] = $estadoAprobacion;
        }

        $sql .= ' ORDER BY COALESCE(ac.fecha_aprobacion, DATE(ac.fecha_creacion)) DESC, ac.id DESC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function existeAprobacionRegistrada(int $empresaId, int $cotizacionId, string $estado): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM aprobaciones_cotizacion WHERE empresa_id=:empresa_id AND cotizacion_id=:cotizacion_id AND estado=:estado LIMIT 1');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'cotizacion_id' => $cotizacionId,
            'estado' => $estado,
        ]);
        return (bool) $stmt->fetch();
    }

    public function crear(string $tabla, array $data): int
    {
        if (!in_array($tabla, $this->tablasPermitidas, true)) {
            throw new \InvalidArgumentException('Tabla no permitida para escritura.');
        }
        $campos = array_keys($data);
        $columns = implode(',', $campos);
        $binds = ':' . implode(',:', $campos);
        $sql = "INSERT INTO {$tabla} ({$columns}) VALUES ({$binds})";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function obtenerPorId(string $tabla, int $empresaId, int $id): ?array
    {
        if (!in_array($tabla, $this->tablasPermitidas, true)) {
            return null;
        }
        $stmt = $this->db->prepare("SELECT * FROM {$tabla} WHERE empresa_id = :empresa_id AND id = :id LIMIT 1");
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function eliminar(string $tabla, int $empresaId, int $id): void
    {
        if (!in_array($tabla, $this->tablasPermitidas, true)) {
            return;
        }
        $stmt = $this->db->prepare("DELETE FROM {$tabla} WHERE empresa_id = :empresa_id AND id = :id");
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
    }

    public function actualizarDinamico(string $tabla, int $empresaId, int $id, array $data): void
    {
        if (!in_array($tabla, $this->tablasPermitidas, true)) {
            return;
        }

        $actual = $this->obtenerPorId($tabla, $empresaId, $id);
        if (!$actual) {
            return;
        }

        $permitidos = array_diff(array_keys($actual), ['id', 'empresa_id', 'fecha_creacion']);
        $asignaciones = [];
        $params = ['empresa_id' => $empresaId, 'id' => $id];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $data)) {
                $asignaciones[] = "{$campo} = :{$campo}";
                $params[$campo] = $data[$campo] === '' ? null : $data[$campo];
            }
        }

        if ($asignaciones === []) {
            return;
        }

        $sql = "UPDATE {$tabla} SET " . implode(', ', $asignaciones) . " WHERE empresa_id = :empresa_id AND id = :id";
        $this->db->prepare($sql)->execute($params);
    }

    public function estadisticasInicio(int $empresaId): array
    {
        $queries = [
            'cotizaciones_mes' => "SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND MONTH(fecha_emision)=MONTH(CURDATE()) AND YEAR(fecha_emision)=YEAR(CURDATE())",
            'aprobadas' => "SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND estado='aprobada'",
            'rechazadas' => "SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND estado='rechazada'",
            'pendientes' => "SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND estado='pendiente'",
            'por_vencer' => "SELECT COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
            'monto_mes' => "SELECT COALESCE(SUM(total),0) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND MONTH(fecha_emision)=MONTH(CURDATE()) AND YEAR(fecha_emision)=YEAR(CURDATE())",
            'clientes_recientes' => "SELECT nombre, correo, fecha_creacion FROM clientes WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL ORDER BY id DESC LIMIT 5",
            'productos_top' => "SELECT descripcion, COUNT(*) total FROM items_cotizacion ic INNER JOIN cotizaciones c ON c.id = ic.cotizacion_id WHERE c.empresa_id=:empresa_id GROUP BY descripcion ORDER BY total DESC LIMIT 5",
            'vendedores_top' => "SELECT u.nombre, COUNT(*) total FROM cotizaciones c INNER JOIN usuarios u ON u.id=c.usuario_id WHERE c.empresa_id=:empresa_id GROUP BY u.nombre ORDER BY total DESC LIMIT 5",
            'estados_distribucion' => "SELECT estado, COUNT(*) total FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL GROUP BY estado",
            'cotizaciones_ultimos_meses' => "SELECT DATE_FORMAT(fecha_emision, '%Y-%m') periodo, COUNT(*) total, COALESCE(SUM(total),0) monto FROM cotizaciones WHERE empresa_id=:empresa_id AND fecha_eliminacion IS NULL AND fecha_emision >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(fecha_emision, '%Y-%m') ORDER BY periodo ASC",
        ];

        $salida = [];
        foreach ($queries as $clave => $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            if (str_contains($clave, 'recientes')
                || str_contains($clave, 'top')
                || str_contains($clave, 'distribucion')
                || str_contains($clave, 'ultimos_meses')
            ) {
                $salida[$clave] = $stmt->fetchAll();
            } elseif ($clave === 'monto_mes') {
                $salida[$clave] = (float) ($stmt->fetch()['total'] ?? 0);
            } else {
                $salida[$clave] = (int) ($stmt->fetch()['total'] ?? 0);
            }
        }

        return $salida;
    }

    public function listarListasPreciosActivas(int $empresaId): array
    {
        if (!$this->tablaExiste('listas_precios')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT id, nombre, tipo_lista, vigencia_desde, vigencia_hasta
            FROM listas_precios
            WHERE empresa_id = :empresa_id AND estado = "activo"
            ORDER BY nombre ASC');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function obtenerListaPrecioCliente(int $empresaId, int $clienteId): ?int
    {
        $listas = $this->obtenerListasPrecioCliente($empresaId, $clienteId);
        return $listas[0] ?? null;
    }

    public function obtenerListasPrecioCliente(int $empresaId, int $clienteId): array
    {
        $this->asegurarTablaClientesListas();

        $stmt = $this->db->prepare('SELECT lista_precio_id
            FROM clientes_listas_precios
            WHERE empresa_id = :empresa_id AND cliente_id = :cliente_id
            ORDER BY id DESC');
        $stmt->execute(['empresa_id' => $empresaId, 'cliente_id' => $clienteId]);
        $ids = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $valor) {
            $ids[] = (int) $valor;
        }
        return $ids;
    }

    public function asignarListaPrecioCliente(int $empresaId, int $clienteId, ?int $listaPrecioId): void
    {
        $this->asignarListasPrecioCliente($empresaId, $clienteId, $listaPrecioId !== null ? [$listaPrecioId] : []);
    }

    public function asignarListasPrecioCliente(int $empresaId, int $clienteId, array $listaPrecioIds): void
    {
        $this->asegurarTablaClientesListas();

        $idsLimpios = [];
        foreach ($listaPrecioIds as $listaPrecioId) {
            $id = (int) $listaPrecioId;
            if ($id > 0) {
                $idsLimpios[$id] = $id;
            }
        }

        $stmtDelete = $this->db->prepare('DELETE FROM clientes_listas_precios WHERE empresa_id = :empresa_id AND cliente_id = :cliente_id');
        $stmtDelete->execute(['empresa_id' => $empresaId, 'cliente_id' => $clienteId]);

        if ($idsLimpios === []) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO clientes_listas_precios (empresa_id, cliente_id, lista_precio_id, fecha_creacion)
            VALUES (:empresa_id, :cliente_id, :lista_precio_id, NOW())');
        foreach ($idsLimpios as $listaPrecioId) {
            $stmt->execute([
                'empresa_id' => $empresaId,
                'cliente_id' => $clienteId,
                'lista_precio_id' => $listaPrecioId,
            ]);
        }
    }

    public function clienteTieneListaPrecio(int $empresaId, int $clienteId, int $listaPrecioId): bool
    {
        if ($listaPrecioId <= 0) {
            return false;
        }

        $this->asegurarTablaClientesListas();
        $stmt = $this->db->prepare('SELECT 1
            FROM clientes_listas_precios
            WHERE empresa_id = :empresa_id AND cliente_id = :cliente_id AND lista_precio_id = :lista_precio_id
            LIMIT 1');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'cliente_id' => $clienteId,
            'lista_precio_id' => $listaPrecioId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function obtenerPlantillaCorreoCotizacion(int $empresaId): ?array
    {
        $this->asegurarTablaDocumentosPlantillas();
        $stmt = $this->db->prepare('SELECT *
            FROM documentos_plantillas
            WHERE empresa_id = :empresa_id
              AND tipo_documento = :tipo_documento
            ORDER BY id DESC
            LIMIT 1');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'tipo_documento' => 'correo_cotizacion',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function guardarPlantillaCorreoCotizacion(int $empresaId, string $asunto, string $html): int
    {
        $this->asegurarTablaDocumentosPlantillas();
        $plantilla = $this->obtenerPlantillaCorreoCotizacion($empresaId);

        $data = [
            'nombre' => 'Plantilla correo cotización',
            'terminos_defecto' => $asunto,
            'observaciones_defecto' => $html,
            'estado' => 'activo',
            'tipo_documento' => 'correo_cotizacion',
        ];

        if ($plantilla) {
            $this->actualizarDinamico('documentos_plantillas', $empresaId, (int) $plantilla['id'], $data);
            return (int) $plantilla['id'];
        }

        return $this->crear('documentos_plantillas', [
            'empresa_id' => $empresaId,
            'nombre' => $data['nombre'],
            'tipo_documento' => $data['tipo_documento'],
            'terminos_defecto' => $data['terminos_defecto'],
            'observaciones_defecto' => $data['observaciones_defecto'],
            'estado' => $data['estado'],
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);
    }

    public function obtenerPlantillaCorreoOrdenCompra(int $empresaId): ?array
    {
        $this->asegurarTablaDocumentosPlantillas();
        $stmt = $this->db->prepare('SELECT *
            FROM documentos_plantillas
            WHERE empresa_id = :empresa_id
              AND tipo_documento = :tipo_documento
            ORDER BY id DESC
            LIMIT 1');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'tipo_documento' => 'correo_orden_compra',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function guardarPlantillaCorreoOrdenCompra(int $empresaId, string $asunto, string $html): int
    {
        $this->asegurarTablaDocumentosPlantillas();
        $plantilla = $this->obtenerPlantillaCorreoOrdenCompra($empresaId);

        $data = [
            'nombre' => 'Plantilla correo orden de compra',
            'terminos_defecto' => $asunto,
            'observaciones_defecto' => $html,
            'estado' => 'activo',
            'tipo_documento' => 'correo_orden_compra',
        ];

        if ($plantilla) {
            $this->actualizarDinamico('documentos_plantillas', $empresaId, (int) $plantilla['id'], $data);
            return (int) $plantilla['id'];
        }

        return $this->crear('documentos_plantillas', [
            'empresa_id' => $empresaId,
            'nombre' => $data['nombre'],
            'tipo_documento' => $data['tipo_documento'],
            'terminos_defecto' => $data['terminos_defecto'],
            'observaciones_defecto' => $data['observaciones_defecto'],
            'estado' => $data['estado'],
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);
    }

    private function asegurarTablaClientesListas(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS clientes_listas_precios (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            empresa_id BIGINT UNSIGNED NOT NULL,
            cliente_id BIGINT UNSIGNED NOT NULL,
            lista_precio_id BIGINT UNSIGNED NOT NULL,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NULL,
            UNIQUE KEY uniq_cliente_lista (empresa_id, cliente_id, lista_precio_id),
            INDEX idx_clientes_listas_lista (lista_precio_id),
            CONSTRAINT fk_clientes_listas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
            CONSTRAINT fk_clientes_listas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
            CONSTRAINT fk_clientes_listas_lista FOREIGN KEY (lista_precio_id) REFERENCES listas_precios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $stmt = $this->db->prepare('SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "clientes_listas_precios"
              AND INDEX_NAME = "uniq_cliente_lista"
              AND SEQ_IN_INDEX = 3');
        $stmt->execute();
        $tieneIndiceCompuesto = (int) $stmt->fetchColumn() > 0;
        if (!$tieneIndiceCompuesto) {
            try {
                $stmtIndice = $this->db->prepare('SELECT COUNT(*)
                    FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = "clientes_listas_precios"
                      AND INDEX_NAME = "uniq_cliente_lista"');
                $stmtIndice->execute();
                if ((int) $stmtIndice->fetchColumn() > 0) {
                    $this->db->exec('ALTER TABLE clientes_listas_precios DROP INDEX uniq_cliente_lista');
                }
                $this->db->exec('ALTER TABLE clientes_listas_precios ADD UNIQUE KEY uniq_cliente_lista (empresa_id, cliente_id, lista_precio_id)');
            } catch (\Throwable $e) {
                // Si la base no permite ALTER en runtime, mantener operación sin bloquear el flujo.
            }
        }
    }

    private function asegurarTablaDocumentosPlantillas(): void
    {
        if ($this->tablaExiste('documentos_plantillas')) {
            return;
        }

        $this->db->exec('CREATE TABLE IF NOT EXISTS documentos_plantillas (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            empresa_id BIGINT UNSIGNED NOT NULL,
            nombre VARCHAR(160) NOT NULL,
            tipo_documento VARCHAR(80) NOT NULL DEFAULT "cotizacion",
            terminos_defecto TEXT NULL,
            observaciones_defecto TEXT NULL,
            firma VARCHAR(180) NULL,
            logo VARCHAR(255) NULL,
            pie_documento VARCHAR(255) NULL,
            estado ENUM("activo","inactivo") NOT NULL DEFAULT "activo",
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_documentos_empresa (empresa_id),
            CONSTRAINT fk_documentos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function tablaExiste(string $tabla): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla LIMIT 1');
        $stmt->execute(['tabla' => $tabla]);
        return (bool) $stmt->fetchColumn();
    }
}
