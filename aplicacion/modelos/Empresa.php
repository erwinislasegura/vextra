<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Empresa extends Modelo
{
    private function columnaExisteEnEmpresas(string $columna): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'empresas' AND column_name = :columna LIMIT 1");
        $stmt->execute(['columna' => $columna]);
        return (bool) $stmt->fetchColumn();
    }

    private function tablasDependientesDe(string $tabla): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                kcu.table_name AS tabla_hija,
                kcu.column_name AS columna_hija,
                kcu.referenced_column_name AS columna_padre
            FROM information_schema.key_column_usage kcu
            WHERE kcu.table_schema = DATABASE()
              AND kcu.referenced_table_name = :tabla"
        );
        $stmt->execute(['tabla' => $tabla]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function tablasConEmpresaId(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT table_name FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name = 'empresa_id'");
        $tablas = $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
        return array_values(array_filter($tablas, static fn ($tabla) => $tabla !== 'empresas'));
    }

    private function tablaTieneColumna(string $tabla, string $columna): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tabla AND column_name = :columna LIMIT 1");
        $stmt->execute(['tabla' => $tabla, 'columna' => $columna]);
        return (bool) $stmt->fetchColumn();
    }

    private function ordenarTablasParaEliminar(array $tablas): array
    {
        if (count($tablas) <= 1) {
            return $tablas;
        }

        $pendientes = array_fill_keys($tablas, true);
        $hijosPorPadre = [];

        $stmt = $this->db->query(
            "SELECT table_name AS tabla_hija, referenced_table_name AS tabla_padre
             FROM information_schema.key_column_usage
             WHERE table_schema = DATABASE()
               AND referenced_table_name IS NOT NULL"
        );
        $relaciones = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        foreach ($relaciones as $relacion) {
            $tablaHija = (string) ($relacion['tabla_hija'] ?? '');
            $tablaPadre = (string) ($relacion['tabla_padre'] ?? '');
            if (!isset($pendientes[$tablaPadre]) || !isset($pendientes[$tablaHija]) || $tablaHija === $tablaPadre) {
                continue;
            }
            $hijosPorPadre[$tablaPadre][$tablaHija] = true;
        }

        $orden = [];
        while (!empty($pendientes)) {
            $hojas = [];
            foreach (array_keys($pendientes) as $tabla) {
                $hijosActivos = array_intersect_key($hijosPorPadre[$tabla] ?? [], $pendientes);
                if (empty($hijosActivos)) {
                    $hojas[] = $tabla;
                }
            }

            if (empty($hojas)) {
                $hojas = array_keys($pendientes);
            }

            foreach ($hojas as $tabla) {
                if (!isset($pendientes[$tabla])) {
                    continue;
                }
                $orden[] = $tabla;
                unset($pendientes[$tabla]);
            }

            foreach ($hijosPorPadre as $padre => $hijos) {
                foreach ($hojas as $tablaEliminada) {
                    unset($hijosPorPadre[$padre][$tablaEliminada]);
                }
            }
        }

        return $orden;
    }

    private function relacionesFkEntreTablas(array $tablas): array
    {
        if (empty($tablas)) {
            return [];
        }

        $setTablas = array_fill_keys($tablas, true);
        $stmt = $this->db->query(
            "SELECT table_name AS tabla_hija, referenced_table_name AS tabla_padre
             FROM information_schema.key_column_usage
             WHERE table_schema = DATABASE()
               AND referenced_table_name IS NOT NULL"
        );
        $relaciones = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        $resultado = [];
        foreach ($relaciones as $relacion) {
            $tablaHija = (string) ($relacion['tabla_hija'] ?? '');
            $tablaPadre = (string) ($relacion['tabla_padre'] ?? '');
            if (!isset($setTablas[$tablaHija]) || !isset($setTablas[$tablaPadre]) || $tablaHija === $tablaPadre) {
                continue;
            }
            $resultado[] = ['hija' => $tablaHija, 'padre' => $tablaPadre];
        }

        return $resultado;
    }

    private function resolverTablasEliminacionFisica(array $tablas): array
    {
        $eliminacionFisica = [];
        foreach ($tablas as $tabla) {
            if (!$this->tablaTieneColumna($tabla, 'fecha_eliminacion')) {
                $eliminacionFisica[$tabla] = true;
            }
        }

        $relaciones = $this->relacionesFkEntreTablas($tablas);
        $cambio = true;
        while ($cambio) {
            $cambio = false;
            foreach ($relaciones as $relacion) {
                $padre = $relacion['padre'];
                $hija = $relacion['hija'];
                if (!isset($eliminacionFisica[$padre]) || isset($eliminacionFisica[$hija])) {
                    continue;
                }
                $eliminacionFisica[$hija] = true;
                $cambio = true;
            }
        }

        return $eliminacionFisica;
    }

    public function obtenerResumenDatosAsociados(int $empresaId): array
    {
        $resumen = [];
        foreach ($this->tablasConEmpresaId() as $tabla) {
            $tieneSoftDelete = $this->tablaTieneColumna($tabla, 'fecha_eliminacion');
            $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '', $tabla) . '` WHERE empresa_id = :empresa_id';
            if ($tieneSoftDelete) {
                $sql .= ' AND fecha_eliminacion IS NULL';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $total = (int) $stmt->fetchColumn();
            if ($total > 0) {
                $resumen[] = ['tabla' => $tabla, 'total' => $total];
            }
        }

        usort($resumen, static fn ($a, $b) => $b['total'] <=> $a['total']);
        return $resumen;
    }

    private function eliminarDependenciasSinEmpresaId(int $empresaId, array $tablasConEmpresaId): void
    {
        foreach ($tablasConEmpresaId as $tablaPadre) {
            foreach ($this->tablasDependientesDe($tablaPadre) as $relacion) {
                $tablaHija = (string) ($relacion['tabla_hija'] ?? '');
                if ($tablaHija === '' || in_array($tablaHija, $tablasConEmpresaId, true) || $tablaHija === 'empresas') {
                    continue;
                }

                $columnaHija = (string) ($relacion['columna_hija'] ?? '');
                $columnaPadre = (string) ($relacion['columna_padre'] ?? '');
                if ($columnaHija === '' || $columnaPadre === '') {
                    continue;
                }

                $tablaPadreSegura = '`' . str_replace('`', '', $tablaPadre) . '`';
                $tablaHijaSegura = '`' . str_replace('`', '', $tablaHija) . '`';
                $columnaHijaSegura = '`' . str_replace('`', '', $columnaHija) . '`';
                $columnaPadreSegura = '`' . str_replace('`', '', $columnaPadre) . '`';

                if ($this->tablaTieneColumna($tablaHija, 'fecha_eliminacion')) {
                    $stmt = $this->db->prepare(
                        'UPDATE ' . $tablaHijaSegura . ' hija
                         INNER JOIN ' . $tablaPadreSegura . ' padre ON hija.' . $columnaHijaSegura . ' = padre.' . $columnaPadreSegura . '
                         SET hija.fecha_eliminacion = NOW()
                         WHERE padre.empresa_id = :empresa_id
                           AND hija.fecha_eliminacion IS NULL'
                    );
                    $stmt->execute(['empresa_id' => $empresaId]);
                    continue;
                }

                $stmt = $this->db->prepare(
                    'DELETE hija FROM ' . $tablaHijaSegura . ' hija
                     INNER JOIN ' . $tablaPadreSegura . ' padre ON hija.' . $columnaHijaSegura . ' = padre.' . $columnaPadreSegura . '
                     WHERE padre.empresa_id = :empresa_id'
                );
                $stmt->execute(['empresa_id' => $empresaId]);
            }
        }
    }

    public function eliminarConDatosAsociados(int $empresaId): void
    {
        $this->db->beginTransaction();
        try {
            $tablasConEmpresaId = $this->ordenarTablasParaEliminar($this->tablasConEmpresaId());
            $tablasEliminacionFisica = $this->resolverTablasEliminacionFisica($tablasConEmpresaId);
            $this->eliminarDependenciasSinEmpresaId($empresaId, $tablasConEmpresaId);

            foreach ($tablasConEmpresaId as $tabla) {
                $tablaSegura = '`' . str_replace('`', '', $tabla) . '`';
                if (isset($tablasEliminacionFisica[$tabla])) {
                    $stmt = $this->db->prepare('DELETE FROM ' . $tablaSegura . ' WHERE empresa_id = :empresa_id');
                    $stmt->execute(['empresa_id' => $empresaId]);
                    continue;
                }

                $stmt = $this->db->prepare('UPDATE ' . $tablaSegura . ' SET fecha_eliminacion = NOW() WHERE empresa_id = :empresa_id AND fecha_eliminacion IS NULL');
                $stmt->execute(['empresa_id' => $empresaId]);
            }

            $stmtEmpresa = $this->db->prepare('UPDATE empresas SET estado = "cancelada", fecha_eliminacion = NOW(), fecha_actualizacion = NOW() WHERE id = :id');
            $stmtEmpresa->execute(['id' => $empresaId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listar(array $filtros = []): array
    {
        $sql = 'SELECT e.*, p.nombre AS plan_nombre,
                s.estado AS suscripcion_estado,
                s.fecha_vencimiento AS suscripcion_fecha_vencimiento,
                DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes_plan,
                (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id = e.id AND u.fecha_eliminacion IS NULL) AS total_usuarios,
                (SELECT u.ultimo_acceso FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.empresa_id = e.id AND r.codigo = "administrador_empresa" AND u.fecha_eliminacion IS NULL ORDER BY u.id ASC LIMIT 1) AS ultimo_acceso_admin
            FROM empresas e
            LEFT JOIN planes p ON p.id = e.plan_id
            LEFT JOIN suscripciones s ON s.id = (
                SELECT sx.id FROM suscripciones sx WHERE sx.empresa_id = e.id AND sx.fecha_eliminacion IS NULL ORDER BY sx.id DESC LIMIT 1
            )
            WHERE e.fecha_eliminacion IS NULL';

        $params = [];
        if (!empty($filtros['busqueda'])) {
            $sql .= ' AND (e.nombre_comercial LIKE :q OR e.razon_social LIKE :q OR e.correo LIKE :q OR e.identificador_fiscal LIKE :q)';
            $params['q'] = '%' . $filtros['busqueda'] . '%';
        }
        if (!empty($filtros['estado'])) {
            $sql .= ' AND e.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['plan_id'])) {
            $sql .= ' AND e.plan_id = :plan_id';
            $params['plan_id'] = (int) $filtros['plan_id'];
        }

        $sql .= ' ORDER BY e.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM empresas WHERE id=:id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarDetalleAdmin(int $id): ?array
    {
        $sql = 'SELECT e.*, p.nombre AS plan_nombre, p.precio_mensual AS plan_precio_mensual,
                s.id AS suscripcion_id, s.estado AS suscripcion_estado, s.fecha_inicio, s.fecha_vencimiento,
                DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes
            FROM empresas e
            LEFT JOIN planes p ON p.id = e.plan_id
            LEFT JOIN suscripciones s ON s.id = (
                SELECT sx.id FROM suscripciones sx WHERE sx.empresa_id = e.id AND sx.fecha_eliminacion IS NULL ORDER BY sx.id DESC LIMIT 1
            )
            WHERE e.id = :id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $data): int
    {
        $sql = 'INSERT INTO empresas (razon_social,nombre_comercial,identificador_fiscal,correo,telefono,direccion,ciudad,pais,estado,fecha_activacion,plan_id,fecha_creacion) VALUES (:razon_social,:nombre_comercial,:identificador_fiscal,:correo,:telefono,:direccion,:ciudad,:pais,:estado,:fecha_activacion,:plan_id,NOW())';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function actualizarEstado(int $empresaId, string $estado): void
    {
        $this->db->prepare('UPDATE empresas SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id')->execute([
            'id' => $empresaId,
            'estado' => $estado,
        ]);
    }

    public function actualizarPlanYObservacion(int $empresaId, int $planId, string $observaciones): void
    {
        $this->db->prepare('UPDATE empresas SET plan_id = :plan_id, observaciones_internas = :observaciones, fecha_actualizacion = NOW() WHERE id = :id')->execute([
            'id' => $empresaId,
            'plan_id' => $planId,
            'observaciones' => $observaciones,
        ]);
    }

    public function existePorIdentificadorFiscal(string $identificadorFiscal): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM empresas WHERE identificador_fiscal = :identificador_fiscal AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['identificador_fiscal' => $identificadorFiscal]);
        return (bool) $stmt->fetchColumn();
    }

    public function obtenerConfiguracion(int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM empresas WHERE id = :id AND fecha_eliminacion IS NULL');
        $stmt->execute(['id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarConfiguracion(int $empresaId, array $data): void
    {
        $columnasValores = [
            'razon_social' => $data['razon_social'] ?? '',
            'nombre_comercial' => $data['nombre_comercial'] ?? '',
            'identificador_fiscal' => $data['identificador_fiscal'] ?? '',
            'correo' => $data['correo'] ?? '',
            'telefono' => $data['telefono'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'ciudad' => $data['ciudad'] ?? '',
            'pais' => $data['pais'] ?? '',
            'descripcion' => $data['descripcion'] ?? '',
            'catalogo_dominio' => $data['catalogo_dominio'] ?? null,
            'logo' => $data['logo'] ?? '',
            'imap_host' => $data['imap_host'] ?? '',
            'imap_port' => $data['imap_port'] ?? null,
            'imap_encryption' => $data['imap_encryption'] ?? '',
            'imap_usuario' => $data['imap_usuario'] ?? '',
            'imap_password' => $data['imap_password'] ?? '',
            'imap_remitente_correo' => $data['imap_remitente_correo'] ?? '',
            'imap_remitente_nombre' => $data['imap_remitente_nombre'] ?? '',
            'etiqueta_rubro' => $data['etiqueta_rubro'] ?? 'general',
            'etiqueta_frases' => $data['etiqueta_frases'] ?? '',
        ];

        $sets = [];
        $params = ['empresa_id' => $empresaId];
        foreach ($columnasValores as $columna => $valor) {
            if (!$this->columnaExisteEnEmpresas($columna)) {
                continue;
            }
            $sets[] = $columna . ' = :' . $columna;
            $params[$columna] = $valor;
        }

        if ($this->columnaExisteEnEmpresas('fecha_actualizacion')) {
            $sets[] = 'fecha_actualizacion = NOW()';
        }
        if ($sets === []) {
            return;
        }

        $sql = 'UPDATE empresas SET ' . implode(', ', $sets) . ' WHERE id = :empresa_id AND fecha_eliminacion IS NULL';
        $this->db->prepare($sql)->execute($params);
    }

    public function buscarPorCatalogoDominio(string $host): ?array
    {
        if (!$this->columnaExisteEnEmpresas('catalogo_dominio')) {
            return null;
        }

        $dominio = trim(mb_strtolower($host));
        if ($dominio === '') {
            return null;
        }

        if (str_contains($dominio, ':')) {
            $dominio = explode(':', $dominio, 2)[0];
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM empresas
             WHERE LOWER(TRIM(catalogo_dominio)) = :dominio
               AND estado = "activa"
               AND fecha_eliminacion IS NULL
             LIMIT 1'
        );
        $stmt->execute(['dominio' => $dominio]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarCatalogoDominio(int $empresaId, ?string $dominio): void
    {
        if (!$this->columnaExisteEnEmpresas('catalogo_dominio')) {
            return;
        }

        $sql = 'UPDATE empresas SET catalogo_dominio = :catalogo_dominio';
        if ($this->columnaExisteEnEmpresas('fecha_actualizacion')) {
            $sql .= ', fecha_actualizacion = NOW()';
        }
        $sql .= ' WHERE id = :empresa_id AND fecha_eliminacion IS NULL';

        $this->db->prepare($sql)->execute([
            'catalogo_dominio' => $dominio,
            'empresa_id' => $empresaId,
        ]);
    }

    public function obtenerConfiguracionCatalogoEnLinea(int $empresaId): array
    {
        $config = [
            'slider_imagen' => '',
            'slider_imagen_secundaria' => '',
            'slider_titulo' => '',
            'slider_bajada' => '',
            'slider_boton_texto' => '',
            'slider_boton_url' => '',
            'catalogo_topbar_texto' => '',
            'catalogo_nosotros_titulo' => '',
            'catalogo_nosotros_descripcion' => '',
            'catalogo_nosotros_imagen' => '',
            'catalogo_nosotros_banner_imagen' => '',
            'catalogo_nosotros_bloque_titulo' => '',
            'catalogo_nosotros_bloque_texto' => '',
            'catalogo_contacto_titulo' => '',
            'catalogo_contacto_descripcion' => '',
            'catalogo_contacto_horario' => '',
            'catalogo_contacto_whatsapp' => '',
            'catalogo_contacto_form_titulo' => '',
            'catalogo_contacto_form_subtitulo' => '',
            'catalogo_contacto_form_bajada' => '',
            'catalogo_contacto_form_correo_destino' => '',
            'catalogo_contacto_form_campos' => '',
            'catalogo_contacto_form_texto_boton' => '',
            'catalogo_contacto_mapa_url' => '',
            'catalogo_contacto_mapa_activo' => '1',
            'catalogo_social_facebook' => '',
            'catalogo_social_instagram' => '',
            'catalogo_social_tiktok' => '',
            'catalogo_social_linkedin' => '',
            'catalogo_social_youtube' => '',
            'catalogo_color_primario' => '',
            'catalogo_color_acento' => '',
            'catalogo_columnas_productos' => '3',
        ];

        $columnas = array_keys($config);
        $existentes = [];
        foreach ($columnas as $columna) {
            if ($this->columnaExisteEnEmpresas($columna)) {
                $existentes[] = $columna;
            }
        }
        if ($existentes === []) {
            return $config;
        }

        $sql = 'SELECT ' . implode(', ', $existentes) . ' FROM empresas WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $empresaId]);
        $fila = $stmt->fetch() ?: [];
        foreach ($existentes as $columna) {
            $config[$columna] = (string) ($fila[$columna] ?? '');
        }

        return $config;
    }

    public function guardarConfiguracionCatalogoEnLinea(int $empresaId, array $data): void
    {
        $columnas = [
            'slider_imagen',
            'slider_imagen_secundaria',
            'slider_titulo',
            'slider_bajada',
            'slider_boton_texto',
            'slider_boton_url',
            'catalogo_topbar_texto',
            'catalogo_nosotros_titulo',
            'catalogo_nosotros_descripcion',
            'catalogo_nosotros_imagen',
            'catalogo_nosotros_banner_imagen',
            'catalogo_nosotros_bloque_titulo',
            'catalogo_nosotros_bloque_texto',
            'catalogo_contacto_titulo',
            'catalogo_contacto_descripcion',
            'catalogo_contacto_horario',
            'catalogo_contacto_whatsapp',
            'catalogo_contacto_form_titulo',
            'catalogo_contacto_form_subtitulo',
            'catalogo_contacto_form_bajada',
            'catalogo_contacto_form_correo_destino',
            'catalogo_contacto_form_campos',
            'catalogo_contacto_form_texto_boton',
            'catalogo_contacto_mapa_url',
            'catalogo_contacto_mapa_activo',
            'catalogo_social_facebook',
            'catalogo_social_instagram',
            'catalogo_social_tiktok',
            'catalogo_social_linkedin',
            'catalogo_social_youtube',
            'catalogo_color_primario',
            'catalogo_color_acento',
            'catalogo_columnas_productos',
        ];
        $sets = [];
        $params = ['empresa_id' => $empresaId];
        foreach ($columnas as $columna) {
            if (!$this->columnaExisteEnEmpresas($columna)) {
                continue;
            }
            $sets[] = $columna . ' = :' . $columna;
            $params[$columna] = (string) ($data[$columna] ?? '');
        }
        if ($sets === []) {
            return;
        }

        $sql = 'UPDATE empresas SET ' . implode(', ', $sets) . ', fecha_actualizacion = NOW() WHERE id = :empresa_id';
        $this->db->prepare($sql)->execute($params);
    }
}
