<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\SoporteChat;
use Aplicacion\Nucleo\Controlador;

class SoporteChatsAdminControlador extends Controlador
{
    private const MAX_ADJUNTO_BYTES = 8_388_608;

    public function index(): void
    {
        $buscar = trim((string) ($_GET['q'] ?? ''));
        $chats = (new SoporteChat())->listarChatsAdmin($buscar);
        $this->vista('admin/soporte_chats/index', compact('chats', 'buscar'), 'admin');
    }

    public function ver(int $id): void
    {
        $modelo = new SoporteChat();
        $chat = $modelo->obtenerChatAdmin($id);
        if (!$chat) {
            flash('danger', 'Chat de soporte no encontrado.');
            $this->redirigir('/admin/soporte-chats');
        }

        $modelo->marcarLeidoAdmin($id);
        $mensajes = $modelo->listarMensajes($id);
        $this->vista('admin/soporte_chats/ver', compact('chat', 'mensajes'), 'admin');
    }

    public function responder(int $id): void
    {
        validar_csrf();

        $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

        try {
            $adjunto = $this->procesarAdjunto($_FILES['adjunto'] ?? null);
        } catch (\RuntimeException $e) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
            }
            flash('danger', $e->getMessage());
            $this->redirigir('/admin/soporte-chats/ver/' . $id);
        }

        if ($mensaje === '' && $adjunto === null) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'Debes escribir un mensaje o adjuntar archivo.'], 422);
            }
            flash('danger', 'Debes escribir un mensaje o adjuntar archivo.');
            $this->redirigir('/admin/soporte-chats/ver/' . $id);
        }

        $usuario = usuario_actual();

        try {
            $modelo = new SoporteChat();
            $modelo->responderAdmin($id, (int) ($usuario['id'] ?? 0), $mensaje, $adjunto);
            if ($this->esperaJson()) {
                $nuevos = $modelo->listarMensajesDesde($id, max(0, (int) ($_POST['ultimo_id'] ?? 0)));
                $this->json(['ok' => true, 'mensajes' => $nuevos]);
            }
            flash('success', 'Respuesta enviada al cliente.');
        } catch (\Throwable $e) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'No se pudo enviar la respuesta.'], 500);
            }
            flash('danger', 'No se pudo enviar la respuesta.');
        }

        $this->redirigir('/admin/soporte-chats/ver/' . $id);
    }

    public function cerrar(int $id): void
    {
        validar_csrf();
        (new SoporteChat())->cerrarChatAdmin($id);
        flash('success', 'Chat cerrado. El cliente ya no podrá seguir enviando mensajes en este hilo.');
        $this->redirigir('/admin/soporte-chats/ver/' . $id);
    }

    public function eliminar(int $id): void
    {
        validar_csrf();
        (new SoporteChat())->eliminarChatAdmin($id);
        flash('success', 'Chat eliminado junto con todos sus archivos adjuntos.');
        $this->redirigir('/admin/soporte-chats');
    }

    public function mensajes(int $id): void
    {
        $modelo = new SoporteChat();
        $chat = $modelo->obtenerChatAdmin($id);
        if (!$chat) {
            $this->json(['ok' => false, 'mensaje' => 'Chat no encontrado.'], 404);
        }

        $ultimoId = max(0, (int) ($_GET['ultimo_id'] ?? 0));
        $mensajes = $ultimoId > 0 ? $modelo->listarMensajesDesde($id, $ultimoId) : $modelo->listarMensajes($id);
        $modelo->marcarLeidoAdmin($id);

        $this->json([
            'ok' => true,
            'mensajes' => $mensajes,
            'estado' => $chat['estado'] ?? 'abierto',
            'no_leidos_admin' => $modelo->contarNoLeidosAdmin(),
        ]);
    }

    private function procesarAdjunto(?array $archivo): ?array
    {
        if (!is_array($archivo) || (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('No se pudo subir el archivo adjunto.');
        }

        $tmp = (string) ($archivo['tmp_name'] ?? '');
        $nombreOriginal = trim((string) ($archivo['name'] ?? 'archivo'));
        $peso = (int) ($archivo['size'] ?? 0);

        if ($peso <= 0 || $peso > self::MAX_ADJUNTO_BYTES) {
            throw new \RuntimeException('El archivo excede el máximo de 8MB.');
        }

        $permitidos = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip', 'application/x-zip-compressed',
        ];

        $mime = mime_content_type($tmp) ?: 'application/octet-stream';
        if (!in_array($mime, $permitidos, true)) {
            throw new \RuntimeException('Tipo de archivo no permitido.');
        }

        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]+/', '', $ext) ?: 'bin';

        $dirPublico = dirname(__DIR__, 3) . '/public/uploads/soporte';
        if (!is_dir($dirPublico) && !@mkdir($dirPublico, 0775, true) && !is_dir($dirPublico)) {
            throw new \RuntimeException('No se pudo crear carpeta para adjuntos.');
        }

        $nombreArchivo = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destino = $dirPublico . '/' . $nombreArchivo;

        if (!move_uploaded_file($tmp, $destino)) {
            throw new \RuntimeException('No se pudo guardar el archivo adjunto.');
        }

        return [
            'ruta' => '/uploads/soporte/' . $nombreArchivo,
            'nombre' => mb_substr($nombreOriginal, 0, 250),
            'tipo' => $mime,
            'peso' => $peso,
        ];
    }

    private function esperaJson(): bool
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $xhr = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        return str_contains($accept, 'application/json') || $xhr === 'xmlhttprequest';
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
