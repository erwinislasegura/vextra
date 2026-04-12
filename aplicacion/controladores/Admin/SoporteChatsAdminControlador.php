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
            flash('danger', 'Debes escribir un mensaje de respuesta.');
            $this->redirigir('/admin/soporte-chats/ver/' . $id);
        }

        $usuario = usuario_actual();

        try {
            (new SoporteChat())->responderAdmin($id, (int) ($usuario['id'] ?? 0), $mensaje);
            flash('success', 'Respuesta enviada al cliente.');
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo enviar la respuesta.');
        }

        $this->redirigir('/admin/soporte-chats/ver/' . $id);
    }
}
