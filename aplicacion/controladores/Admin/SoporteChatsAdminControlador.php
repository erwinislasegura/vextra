<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\SoporteChat;
use Aplicacion\Nucleo\Controlador;

class SoporteChatsAdminControlador extends Controlador
{
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
        if ($mensaje === '') {
            if ($this->esperaJson()) {
                $this->json(['ok' => false, 'mensaje' => 'Debes escribir un mensaje de respuesta.'], 422);
            }
            flash('danger', 'Debes escribir un mensaje de respuesta.');
            $this->redirigir('/admin/soporte-chats/ver/' . $id);
        }

        $usuario = usuario_actual();

        try {
            $modelo = new SoporteChat();
            $modelo->responderAdmin($id, (int) ($usuario['id'] ?? 0), $mensaje);
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
            'no_leidos_admin' => $modelo->contarNoLeidosAdmin(),
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
