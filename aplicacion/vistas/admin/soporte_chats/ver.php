<?php
$ultimoId = !empty($mensajes) ? (int) end($mensajes)['id'] : 0;
$chatAbierto = ($chat['estado'] ?? 'abierto') === 'abierto';
?>
<section class="card" id="soporteAdminApp" data-chat-id="<?= (int) ($chat['id'] ?? 0) ?>" data-ultimo-id="<?= $ultimoId ?>" data-chat-estado="<?= e((string) ($chat['estado'] ?? 'abierto')) ?>" data-mensajes-url-base="<?= e(url('/admin/soporte-chats/mensajes')) ?>" data-responder-url-base="<?= e(url('/admin/soporte-chats/responder')) ?>">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="h6 mb-0">Chat #<?= (int) ($chat['id'] ?? 0) ?> · <?= e($chat['empresa_nombre'] ?? '') ?></h1>
      <small class="text-muted"><?= e($chat['empresa_correo'] ?? '') ?> · <?= e($chat['asunto'] ?? '') ?></small>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <span id="estadoChatAdmin" class="badge <?= $chatAbierto ? 'text-bg-info' : 'text-bg-secondary' ?>"><?= $chatAbierto ? 'Abierto' : 'Cerrado' ?></span>
      <span class="badge text-bg-light border" id="estadoConexionAdmin">Actualización automática</span>
      <?php if ($chatAbierto): ?>
        <form method="POST" action="<?= e(url('/admin/soporte-chats/cerrar/' . (int) ($chat['id'] ?? 0))) ?>" class="m-0">
          <?= csrf_campo() ?>
          <button class="btn btn-sm btn-outline-secondary" data-confirmar="¿Cerrar este chat de soporte?">Cerrar chat</button>
        </form>
      <?php endif; ?>
      <form method="POST" action="<?= e(url('/admin/soporte-chats/eliminar/' . (int) ($chat['id'] ?? 0))) ?>" class="m-0">
        <?= csrf_campo() ?>
        <button class="btn btn-sm btn-outline-danger" data-confirmar="¿Eliminar chat y todos los archivos adjuntos?">Eliminar chat</button>
      </form>
      <a href="<?= e(url('/admin/soporte-chats')) ?>" class="btn btn-sm btn-outline-primary">Volver</a>
    </div>
  </div>
  <div class="card-body">
    <div class="d-grid gap-2 mb-3" id="hiloAdmin" style="max-height: 450px; overflow:auto;">
      <?php foreach ($mensajes as $mensaje): ?>
        <?php $esAdmin = ($mensaje['remitente_tipo'] ?? '') === 'admin'; ?>
        <div class="p-2 rounded border <?= $esAdmin ? 'bg-light border-success-subtle ms-4' : 'border-primary-subtle me-4' ?>">
          <div class="small fw-semibold <?= $esAdmin ? 'text-success' : 'text-primary' ?>"><?= $esAdmin ? 'Administrador' : 'Cliente' ?></div>
          <?php if (trim((string) ($mensaje['mensaje'] ?? '')) !== ''): ?><div><?= nl2br(e($mensaje['mensaje'] ?? '')) ?></div><?php endif; ?>
          <?php if (!empty($mensaje['archivo_ruta'])): ?>
            <div class="mt-1"><a href="<?= e(url((string) $mensaje['archivo_ruta'])) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> <?= e($mensaje['archivo_nombre'] ?? 'Adjunto') ?></a></div>
          <?php endif; ?>
          <div class="small text-muted mt-1"><?= e((string) ($mensaje['fecha_creacion'] ?? '')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <form id="formAdminResponder" method="POST" action="<?= e(url('/admin/soporte-chats/responder/' . (int) ($chat['id'] ?? 0))) ?>" class="d-grid gap-2" data-async-chat="1" enctype="multipart/form-data">
      <?= csrf_campo() ?>
      <input type="hidden" name="ultimo_id" id="ultimoIdAdmin" value="<?= $ultimoId ?>">
      <textarea class="form-control" id="mensajeAdmin" name="mensaje" rows="3" <?= $chatAbierto ? '' : 'disabled' ?> placeholder="Responder... (Enter envía · Shift+Enter salto de línea)"></textarea>
      <div class="d-flex gap-2 align-items-center">
        <input class="form-control" id="adjuntoAdmin" type="file" name="adjunto" <?= $chatAbierto ? '' : 'disabled' ?> accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
        <button type="submit" id="btnAdminEnviar" class="btn btn-success" <?= $chatAbierto ? '' : 'disabled' ?>>Responder</button>
      </div>
    </form>
  </div>
</section>

<script>
(() => {
  const app = document.getElementById('soporteAdminApp');
  if (!app) return;

  const chatId = Number(app.dataset.chatId || 0);
  const mensajesUrlBase = app.dataset.mensajesUrlBase;
  const responderUrlBase = app.dataset.responderUrlBase;
  const hilo = document.getElementById('hiloAdmin');
  const estado = document.getElementById('estadoConexionAdmin');
  const estadoChat = document.getElementById('estadoChatAdmin');
  const form = document.getElementById('formAdminResponder');
  const input = document.getElementById('mensajeAdmin');
  const adjunto = document.getElementById('adjuntoAdmin');
  const ultimoIdInput = document.getElementById('ultimoIdAdmin');
  const btn = document.getElementById('btnAdminEnviar');
  let ultimoId = Number(app.dataset.ultimoId || 0);
  let chatAbierto = (app.dataset.chatEstado || 'abierto') === 'abierto';

  const escapeHtml = (str) => String(str || '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

  const setEstadoChat = (abierto) => {
    chatAbierto = abierto;
    estadoChat.textContent = abierto ? 'Abierto' : 'Cerrado';
    estadoChat.className = 'badge ' + (abierto ? 'text-bg-info' : 'text-bg-secondary');
    input.disabled = !abierto;
    adjunto.disabled = !abierto;
    btn.disabled = !abierto;
  };

  const render = (m) => {
    const esAdmin = m.remitente_tipo === 'admin';
    const item = document.createElement('div');
    item.className = 'p-2 rounded border ' + (esAdmin ? 'bg-light border-success-subtle ms-4' : 'border-primary-subtle me-4');
    const mensajeHtml = m.mensaje ? `<div>${escapeHtml(m.mensaje).replace(/\n/g, '<br>')}</div>` : '';
    const archivoHtml = m.archivo_ruta ? `<div class="mt-1"><a href="${m.archivo_ruta}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> ${escapeHtml(m.archivo_nombre || 'Adjunto')}</a></div>` : '';
    item.innerHTML = `<div class="small fw-semibold ${esAdmin ? 'text-success' : 'text-primary'}">${esAdmin ? 'Administrador' : 'Cliente'}</div>${mensajeHtml}${archivoHtml}<div class="small text-muted mt-1">${m.fecha_creacion || ''}</div>`;
    hilo.appendChild(item);
  };

  const poll = async () => {
    try {
      const res = await fetch(`${mensajesUrlBase}/${chatId}?ultimo_id=${ultimoId}`, {headers: {'Accept': 'application/json'}});
      if (!res.ok) throw new Error('error');
      const data = await res.json();
      setEstadoChat((data.estado || 'abierto') === 'abierto');
      (data.mensajes || []).forEach((m) => {
        render(m);
        ultimoId = Math.max(ultimoId, Number(m.id || 0));
      });
      if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
      if ((data.mensajes || []).length > 0) hilo.scrollTop = hilo.scrollHeight;
      estado.textContent = 'En línea';
      estado.className = 'badge text-bg-success';
    } catch (e) {
      estado.textContent = 'Reconectando...';
      estado.className = 'badge text-bg-warning';
    }
  };

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!chatAbierto) return;

    const mensaje = (input.value || '').trim();
    const tieneArchivo = adjunto && adjunto.files && adjunto.files.length > 0;
    if (!mensaje && !tieneArchivo) return;

    btn.disabled = true;
    const formData = new FormData(form);
    formData.set('ultimo_id', String(ultimoId));

    try {
      const res = await fetch(`${responderUrlBase}/${chatId}`, {
        method: 'POST',
        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: formData,
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.mensaje || 'error');
      (data.mensajes || []).forEach((m) => {
        render(m);
        ultimoId = Math.max(ultimoId, Number(m.id || 0));
      });
      input.value = '';
      adjunto.value = '';
      if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
      hilo.scrollTop = hilo.scrollHeight;
    } catch (err) {
      alert(err.message || 'No se pudo enviar la respuesta.');
    } finally {
      btn.disabled = !chatAbierto;
    }
  });

  setEstadoChat(chatAbierto);
  setInterval(poll, 5000);
})();
</script>
