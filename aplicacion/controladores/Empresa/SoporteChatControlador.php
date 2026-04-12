<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Modelos\SoporteChat;
use Aplicacion\Nucleo\Controlador;

class SoporteChatControlador extends Controlador
{
    public function ver(int $id): void
    {
        $empresaId = (int) empresa_actual_id();
        $modelo = new SoporteChat();
        $chat = $modelo->obtenerChatEmpresa($id, $empresaId);

        if (!$chat) {
            flash('danger', 'Chat de soporte no encontrado.');
            $this->redirigir('/app/panel');
        }

        $modelo->marcarLeidoCliente($id, $empresaId);
        $mensajes = $modelo->listarMensajes($id);
        $this->vista('empresa/soporte_chats/ver', compact('chat', 'mensajes'), 'empresa');
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
            $this->redirigir('/app/panel');
        }

        $chatId = (new SoporteChat())->crearChat($empresaId, (int) ($usuario['id'] ?? 0), $asunto, $mensaje);
        flash('success', 'Chat de soporte enviado. Te responderemos pronto.');
        $this->redirigir('/app/soporte-chats/ver/' . $chatId);
    }

    public function responder(int $id): void
    {
        validar_csrf();

        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $mensaje = trim((string) ($_POST['mensaje'] ?? ''));
        if ($mensaje === '') {
            flash('danger', 'Debes escribir un mensaje.');
            $this->redirigir('/app/soporte-chats/ver/' . $id);
        }

        try {
            (new SoporteChat())->responderCliente($id, $empresaId, (int) ($usuario['id'] ?? 0), $mensaje);
            flash('success', 'Mensaje enviado a soporte.');
        } catch (\Throwable $e) {
            flash('danger', 'No fue posible enviar tu mensaje.');
        }

        $this->redirigir('/app/soporte-chats/ver/' . $id);
    }
}
