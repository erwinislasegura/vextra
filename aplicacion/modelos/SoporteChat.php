<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class SoporteChat extends Modelo
{
    public function __construct()
    {
        parent::__construct();
        $this->asegurarTablas();
        $this->asegurarColumnasAdjuntos();
    }

    public function listarChatsEmpresa(int $empresaId, int $limite = 20): array
    {
        $stmt = $this->db->prepare('SELECT id, asunto, estado, no_leidos_admin, no_leidos_cliente, fecha_ultimo_mensaje, fecha_creacion
            FROM soporte_chats
            WHERE empresa_id = :empresa_id
            ORDER BY fecha_ultimo_mensaje DESC, id DESC
            LIMIT :limite');
        $stmt->bindValue(':empresa_id', $empresaId, \PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listarChatsAdmin(string $buscar = '', int $limite = 100): array
    {
        $sql = 'SELECT sc.id, sc.empresa_id, sc.asunto, sc.estado, sc.no_leidos_admin, sc.no_leidos_cliente,
                sc.fecha_ultimo_mensaje, sc.fecha_creacion,
                e.nombre_comercial AS empresa_nombre, e.correo AS empresa_correo
            FROM soporte_chats sc
            INNER JOIN empresas e ON e.id = sc.empresa_id
            WHERE e.fecha_eliminacion IS NULL';
        $params = [];

        if ($buscar !== '') {
            $sql .= ' AND (sc.asunto LIKE :buscar OR e.nombre_comercial LIKE :buscar OR e.correo LIKE :buscar)';
            $params['buscar'] = '%' . $buscar . '%';
        }

        $sql .= ' ORDER BY (sc.no_leidos_admin > 0) DESC, sc.fecha_ultimo_mensaje DESC, sc.id DESC LIMIT :limite';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerChatEmpresa(int $chatId, int $empresaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM soporte_chats WHERE id = :id AND empresa_id = :empresa_id LIMIT 1');
        $stmt->execute(['id' => $chatId, 'empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerChatAdmin(int $chatId): ?array
    {
        $stmt = $this->db->prepare('SELECT sc.*, e.nombre_comercial AS empresa_nombre, e.correo AS empresa_correo
            FROM soporte_chats sc
            INNER JOIN empresas e ON e.id = sc.empresa_id
            WHERE sc.id = :id AND e.fecha_eliminacion IS NULL
            LIMIT 1');
        $stmt->execute(['id' => $chatId]);
        return $stmt->fetch() ?: null;
    }

    public function listarMensajes(int $chatId): array
    {
        $stmt = $this->db->prepare('SELECT id, chat_id, remitente_tipo, remitente_id, mensaje, archivo_ruta, archivo_nombre, archivo_tipo, archivo_peso, fecha_creacion
            FROM soporte_chat_mensajes
            WHERE chat_id = :chat_id
            ORDER BY id ASC');
        $stmt->execute(['chat_id' => $chatId]);
        return $stmt->fetchAll();
    }

    public function listarMensajesDesde(int $chatId, int $ultimoId = 0): array
    {
        $stmt = $this->db->prepare('SELECT id, chat_id, remitente_tipo, remitente_id, mensaje, archivo_ruta, archivo_nombre, archivo_tipo, archivo_peso, fecha_creacion
            FROM soporte_chat_mensajes
            WHERE chat_id = :chat_id AND id > :ultimo_id
            ORDER BY id ASC');
        $stmt->bindValue(':chat_id', $chatId, \PDO::PARAM_INT);
        $stmt->bindValue(':ultimo_id', $ultimoId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function crearChat(int $empresaId, int $usuarioId, string $asunto, string $mensaje, ?array $adjunto = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO soporte_chats
            (empresa_id, asunto, estado, no_leidos_admin, no_leidos_cliente, fecha_ultimo_mensaje, fecha_creacion, fecha_actualizacion)
            VALUES (:empresa_id, :asunto, "abierto", 1, 0, NOW(), NOW(), NOW())');
        $stmt->execute([
            'empresa_id' => $empresaId,
            'asunto' => $asunto,
        ]);

        $chatId = (int) $this->db->lastInsertId();
        $this->agregarMensaje($chatId, 'cliente', $usuarioId, $mensaje, $adjunto);
        return $chatId;
    }

    public function responderCliente(int $chatId, int $empresaId, int $usuarioId, string $mensaje, ?array $adjunto = null): void
    {
        $chat = $this->obtenerChatEmpresa($chatId, $empresaId);
        if (!$chat) {
            throw new \RuntimeException('Chat no encontrado.');
        }
        if (($chat['estado'] ?? 'abierto') !== 'abierto') {
            throw new \RuntimeException('El chat está cerrado.');
        }

        $this->agregarMensaje($chatId, 'cliente', $usuarioId, $mensaje, $adjunto);
        $stmt = $this->db->prepare('UPDATE soporte_chats
            SET no_leidos_admin = no_leidos_admin + 1,
                estado = "abierto",
                fecha_ultimo_mensaje = NOW(),
                fecha_actualizacion = NOW()
            WHERE id = :id');
        $stmt->execute(['id' => $chatId]);
    }

    public function responderAdmin(int $chatId, int $adminId, string $mensaje, ?array $adjunto = null): void
    {
        $chat = $this->obtenerChatAdmin($chatId);
        if (!$chat) {
            throw new \RuntimeException('Chat no encontrado.');
        }
        if (($chat['estado'] ?? 'abierto') !== 'abierto') {
            throw new \RuntimeException('El chat está cerrado.');
        }

        $this->agregarMensaje($chatId, 'admin', $adminId, $mensaje, $adjunto);
        $stmt = $this->db->prepare('UPDATE soporte_chats
            SET no_leidos_cliente = no_leidos_cliente + 1,
                no_leidos_admin = 0,
                estado = "abierto",
                fecha_ultimo_mensaje = NOW(),
                fecha_actualizacion = NOW()
            WHERE id = :id');
        $stmt->execute(['id' => $chatId]);
    }

    public function cerrarChatAdmin(int $chatId): void
    {
        $stmt = $this->db->prepare('UPDATE soporte_chats SET estado = "cerrado", no_leidos_admin = 0, no_leidos_cliente = 0, fecha_actualizacion = NOW() WHERE id = :id');
        $stmt->execute(['id' => $chatId]);
    }

    public function eliminarChatAdmin(int $chatId): void
    {
        $archivos = $this->listarArchivosChat($chatId);

        $stmt = $this->db->prepare('DELETE FROM soporte_chats WHERE id = :id');
        $stmt->execute(['id' => $chatId]);

        foreach ($archivos as $ruta) {
            $rutaLocal = $this->resolverRutaLocalAdjunto($ruta);
            if ($rutaLocal && is_file($rutaLocal)) {
                @unlink($rutaLocal);
            }
        }
    }

    public function marcarLeidoAdmin(int $chatId): void
    {
        $stmt = $this->db->prepare('UPDATE soporte_chats SET no_leidos_admin = 0, fecha_actualizacion = NOW() WHERE id = :id');
        $stmt->execute(['id' => $chatId]);
    }

    public function marcarLeidoCliente(int $chatId, int $empresaId): void
    {
        $stmt = $this->db->prepare('UPDATE soporte_chats SET no_leidos_cliente = 0, fecha_actualizacion = NOW() WHERE id = :id AND empresa_id = :empresa_id');
        $stmt->execute(['id' => $chatId, 'empresa_id' => $empresaId]);
    }

    public function contarNoLeidosAdmin(): int
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(no_leidos_admin),0) total FROM soporte_chats WHERE estado = "abierto"');
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function contarNoLeidosEmpresa(int $empresaId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(no_leidos_cliente),0) total FROM soporte_chats WHERE empresa_id = :empresa_id AND estado = "abierto"');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    private function agregarMensaje(int $chatId, string $remitenteTipo, int $remitenteId, string $mensaje, ?array $adjunto = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO soporte_chat_mensajes
            (chat_id, remitente_tipo, remitente_id, mensaje, archivo_ruta, archivo_nombre, archivo_tipo, archivo_peso, fecha_creacion)
            VALUES (:chat_id, :remitente_tipo, :remitente_id, :mensaje, :archivo_ruta, :archivo_nombre, :archivo_tipo, :archivo_peso, NOW())');
        $stmt->execute([
            'chat_id' => $chatId,
            'remitente_tipo' => $remitenteTipo,
            'remitente_id' => $remitenteId,
            'mensaje' => $mensaje,
            'archivo_ruta' => $adjunto['ruta'] ?? null,
            'archivo_nombre' => $adjunto['nombre'] ?? null,
            'archivo_tipo' => $adjunto['tipo'] ?? null,
            'archivo_peso' => $adjunto['peso'] ?? null,
        ]);
    }

    private function listarArchivosChat(int $chatId): array
    {
        $stmt = $this->db->prepare('SELECT archivo_ruta FROM soporte_chat_mensajes WHERE chat_id = :chat_id AND archivo_ruta IS NOT NULL');
        $stmt->execute(['chat_id' => $chatId]);
        $salida = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $ruta) {
            $ruta = trim((string) $ruta);
            if ($ruta !== '') {
                $salida[] = $ruta;
            }
        }
        return array_values(array_unique($salida));
    }

    private function resolverRutaLocalAdjunto(string $rutaPublica): ?string
    {
        $rutaPublica = trim($rutaPublica);
        if ($rutaPublica === '' || !str_starts_with($rutaPublica, '/uploads/soporte/')) {
            return null;
        }

        return dirname(__DIR__, 2) . '/public' . $rutaPublica;
    }

    private function asegurarTablas(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS soporte_chats (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            empresa_id BIGINT UNSIGNED NOT NULL,
            asunto VARCHAR(180) NOT NULL,
            estado ENUM("abierto","cerrado") NOT NULL DEFAULT "abierto",
            no_leidos_admin INT UNSIGNED NOT NULL DEFAULT 0,
            no_leidos_cliente INT UNSIGNED NOT NULL DEFAULT 0,
            fecha_ultimo_mensaje DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NULL,
            INDEX idx_soporte_chats_empresa (empresa_id),
            INDEX idx_soporte_chats_no_leidos_admin (no_leidos_admin),
            CONSTRAINT fk_soporte_chats_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->db->exec('CREATE TABLE IF NOT EXISTS soporte_chat_mensajes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            chat_id BIGINT UNSIGNED NOT NULL,
            remitente_tipo ENUM("cliente","admin") NOT NULL,
            remitente_id BIGINT UNSIGNED NULL,
            mensaje TEXT NOT NULL,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_soporte_mensajes_chat (chat_id),
            CONSTRAINT fk_soporte_mensajes_chat FOREIGN KEY (chat_id) REFERENCES soporte_chats(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function asegurarColumnasAdjuntos(): void
    {
        $columnas = [
            'archivo_ruta' => 'ALTER TABLE soporte_chat_mensajes ADD COLUMN archivo_ruta VARCHAR(255) NULL AFTER mensaje',
            'archivo_nombre' => 'ALTER TABLE soporte_chat_mensajes ADD COLUMN archivo_nombre VARCHAR(255) NULL AFTER archivo_ruta',
            'archivo_tipo' => 'ALTER TABLE soporte_chat_mensajes ADD COLUMN archivo_tipo VARCHAR(120) NULL AFTER archivo_nombre',
            'archivo_peso' => 'ALTER TABLE soporte_chat_mensajes ADD COLUMN archivo_peso BIGINT UNSIGNED NULL AFTER archivo_tipo',
        ];

        foreach ($columnas as $columna => $sql) {
            if (!$this->tablaTieneColumna('soporte_chat_mensajes', $columna)) {
                try {
                    $this->db->exec($sql);
                } catch (\Throwable $e) {
                    // Evitar bloquear ejecución en ambientes sin permisos ALTER.
                }
            }
        }
    }

    private function tablaTieneColumna(string $tabla, string $columna): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla AND COLUMN_NAME = :columna LIMIT 1');
        $stmt->execute(['tabla' => $tabla, 'columna' => $columna]);
        return (bool) $stmt->fetchColumn();
    }
}
