<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Modelos\SoporteChat;
use Aplicacion\Nucleo\Controlador;

class SoporteChatControlador extends Controlador
{
    private const MAX_ADJUNTO_BYTES = 8_388_608;

    public function index(): void
    {
        $empresaId = (int) empresa_actual_id();
        $modelo = new SoporteChat();
        $chats = $modelo->listarChatsEmpresa($empresaId, 50);

        $chatSeleccionadoId = (int) ($_GET['chat'] ?? 0);
        if ($chatSeleccionadoId <= 0 && !empty($chats)) {
            $chatSeleccionadoId = (int) $chats[0]['id'];
        }

        $chat = $chatSeleccionadoId > 0 ? $modelo->obtenerChatEmpresa($chatSeleccionadoId, $empresaId) : null;
        $mensajes = $chat ? $modelo->listarMensajes($chatSeleccionadoId) : [];

        if ($chat) {
            $modelo->marcarLeidoCliente($chatSeleccionadoId, $empresaId);
        }

        $this->vista('empresa/soporte_chats/index', compact('chats', 'chat', 'mensajes'), 'empresa');
    }

    public function ver(int $id): void
    {
        $this->redirigir('/app/soporte-chats?chat=' . $id);
    }

    public function crear(): void
    {
        validar_csrf();

        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $asunto = trim((string) ($_POST['asunto'] ?? ''));
        $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

        try {
            $adjunto = $this->procesarAdjunto($_FILES['adjunto'] ?? null);
        } catch (\RuntimeException $e) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
            }
            flash('danger', $e->getMessage());
            $this->redirigir('/app/soporte-chats');
        }

        if ($asunto === '' || ($mensaje === '' && $adjunto === null)) {
            flash('danger', 'Debes ingresar asunto y al menos un mensaje o archivo.');
            $this->redirigir('/app/soporte-chats');
        }

        $chatId = (new SoporteChat())->crearChat($empresaId, (int) ($usuario['id'] ?? 0), $asunto, $mensaje, $adjunto);

        if ($this->esperaJson()) {
            $this->json(['ok' => true, 'chat_id' => $chatId]);
        }

        flash('success', 'Chat de soporte enviado. Te responderemos pronto.');
        $this->redirigir('/app/soporte-chats?chat=' . $chatId);
    }

    public function responder(int $id): void
    {
        validar_csrf();

        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

        try {
            $adjunto = $this->procesarAdjunto($_FILES['adjunto'] ?? null);
        } catch (\RuntimeException $e) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
            }
            flash('danger', $e->getMessage());
            $this->redirigir('/app/soporte-chats?chat=' . $id);
        }

        if ($mensaje === '' && $adjunto === null) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'Debes escribir un mensaje o adjuntar un archivo.'], 422);
            }
            flash('danger', 'Debes escribir un mensaje o adjuntar un archivo.');
            $this->redirigir('/app/soporte-chats?chat=' . $id);
        }

        try {
            $modelo = new SoporteChat();
            $modelo->responderCliente($id, $empresaId, (int) ($usuario['id'] ?? 0), $mensaje, $adjunto);
            if ($this->esperaJson()) {
                $nuevos = $modelo->listarMensajesDesde($id, max(0, (int) ($_POST['ultimo_id'] ?? 0)));
                $this->json(['ok' => true, 'mensajes' => $nuevos]);
            }
            flash('success', 'Mensaje enviado a soporte.');
        } catch (\Throwable $e) {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'No fue posible enviar tu mensaje.'], 500);
            }
            flash('danger', 'No fue posible enviar tu mensaje.');
        }

        $this->redirigir('/app/soporte-chats?chat=' . $id);
    }

    public function mensajes(int $id): void
    {
        $empresaId = (int) empresa_actual_id();
        $modelo = new SoporteChat();
        $chat = $modelo->obtenerChatEmpresa($id, $empresaId);

        if (!$chat) {
            $this->json(['ok' => false, 'mensaje' => 'Chat no encontrado.'], 404);
        }

        $ultimoId = max(0, (int) ($_GET['ultimo_id'] ?? 0));
        $mensajes = $ultimoId > 0 ? $modelo->listarMensajesDesde($id, $ultimoId) : $modelo->listarMensajes($id);
        $modelo->marcarLeidoCliente($id, $empresaId);

        $this->json([
            'ok' => true,
            'mensajes' => $mensajes,
            'estado' => $chat['estado'] ?? 'abierto',
            'no_leidos' => $modelo->contarNoLeidosEmpresa($empresaId),
        ]);
    }



    public function descargarAdjunto(int $mensajeId): void
    {
        $empresaId = (int) empresa_actual_id();
        $mensaje = (new SoporteChat())->obtenerMensajeEmpresa($mensajeId, $empresaId);
        if (!$mensaje || empty($mensaje['archivo_ruta'])) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        $this->enviarArchivo((string) $mensaje['archivo_ruta'], (string) ($mensaje['archivo_nombre'] ?? 'adjunto'));
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



    private function enviarArchivo(string $rutaPublica, string $nombreOriginal): void
    {
        if (!str_starts_with($rutaPublica, '/uploads/soporte/')) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        $rutaLocal = dirname(__DIR__, 3) . '/public' . $rutaPublica;
        if (!is_file($rutaLocal)) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        $mime = mime_content_type($rutaLocal) ?: 'application/octet-stream';
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . rawurlencode($nombreOriginal !== '' ? $nombreOriginal : basename($rutaLocal)) . '"');
        header('Content-Length: ' . filesize($rutaLocal));
        readfile($rutaLocal);
        exit;
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
