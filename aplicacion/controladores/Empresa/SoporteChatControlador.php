<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Modelos\SoporteChat;
use Aplicacion\Nucleo\Controlador;

class SoporteChatControlador extends Controlador
{
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

        if ($asunto === '' || $mensaje === '') {
            flash('danger', 'Debes ingresar asunto y mensaje para abrir el chat.');
            $this->redirigir('/app/soporte-chats');
        }

        $chatId = (new SoporteChat())->crearChat($empresaId, (int) ($usuario['id'] ?? 0), $asunto, $mensaje);

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
        if ($mensaje === '') {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'Debes escribir un mensaje.'], 422);
            }
            flash('danger', 'Debes escribir un mensaje.');
            $this->redirigir('/app/soporte-chats?chat=' . $id);
        }

        try {
            $modelo = new SoporteChat();
            $modelo->responderCliente($id, $empresaId, (int) ($usuario['id'] ?? 0), $mensaje);
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
            'no_leidos' => $modelo->contarNoLeidosEmpresa($empresaId),
        ]);
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
