<?php
$chatId = (int) ($chat['id'] ?? 0);
$ultimoId = !empty($mensajes) ? (int) end($mensajes)['id'] : 0;
$chatAbierto = ($chat['estado'] ?? 'abierto') === 'abierto';
?>
<section class="row g-3" id="soporteApp" data-chat-id="<?= $chatId ?>" data-ultimo-id="<?= $ultimoId ?>" data-chat-estado="<?= e((string) ($chat['estado'] ?? 'abierto')) ?>" data-mensajes-url-base="<?= e(url('/app/soporte-chats/mensajes')) ?>" data-responder-url-base="<?= e(url('/app/soporte-chats/responder')) ?>">
  <div class="col-lg-4">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-header bg-body-tertiary"><strong><i class="bi bi-headset me-1"></i>Soporte Vextra</strong></div>
      <div class="card-body border-bottom">
        <div class="alert alert-warning small mb-2">
          <strong>Importante:</strong> los mensajes y archivos de este chat son temporales. Al cerrar/eliminar el chat por soporte, se elimina historial y adjuntos.
        </div>
        <form method="POST" action="<?= e(url('/app/soporte-chats/crear')) ?>" class="d-grid gap-2" enctype="multipart/form-data">
          <?= csrf_campo() ?>
          <input class="form-control" type="text" name="asunto" required maxlength="180" placeholder="Asunto">
          <textarea class="form-control" name="mensaje" rows="3" placeholder="Describe tu requerimiento"></textarea>
          <input class="form-control" type="file" name="adjunto" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
          <button class="btn btn-primary" type="submit">Crear chat</button>
        </form>
      </div>
      <div class="list-group list-group-flush" style="max-height: 460px; overflow:auto;">
        <?php if (!empty($chats)): ?>
          <?php foreach ($chats as $item): ?>
            <?php $abierto = ($item['estado'] ?? 'abierto') === 'abierto'; ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= (int) $item['id'] === $chatId ? 'active' : '' ?>" href="<?= e(url('/app/soporte-chats?chat=' . (int) $item['id'])) ?>">
              <span>
                <strong class="d-block"><?= e($item['asunto']) ?></strong>
                <small class="<?= (int) $item['id'] === $chatId ? 'text-white-50' : 'text-muted' ?>"><?= e((string) $item['fecha_ultimo_mensaje']) ?></small>
              </span>
              <span class="d-grid gap-1 text-end">
                <?php if ((int) ($item['no_leidos_cliente'] ?? 0) > 0): ?><span class="badge text-bg-success">Nuevo</span><?php endif; ?>
                <span class="badge <?= $abierto ? 'text-bg-info' : 'text-bg-secondary' ?>"><?= $abierto ? 'Abierto' : 'Cerrado' ?></span>
              </span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="p-3 text-muted small">No hay chats creados aún.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><?= $chat ? e($chat['asunto']) : 'Selecciona un chat' ?></strong>
        <div class="d-flex gap-2 align-items-center">
          <?php if ($chat): ?><span id="estadoChat" class="badge <?= $chatAbierto ? 'text-bg-info' : 'text-bg-secondary' ?>"><?= $chatAbierto ? 'Abierto' : 'Cerrado' ?></span><?php endif; ?>
          <span class="badge text-bg-light border" id="estadoConexion">Actualización automática</span>
        </div>
      </div>
      <div class="card-body d-flex flex-column">
        <div id="hiloMensajes" class="d-grid gap-2 mb-3" style="max-height: 430px; overflow:auto;">
          <?php if ($chat): ?>
            <?php foreach ($mensajes as $mensaje): ?>
              <?php $esAdmin = ($mensaje['remitente_tipo'] ?? '') === 'admin'; ?>
              <div class="p-2 rounded border <?= $esAdmin ? 'bg-light border-success-subtle ms-4' : 'border-primary-subtle me-4' ?>">
                <div class="small fw-semibold <?= $esAdmin ? 'text-success' : 'text-primary' ?>"><?= $esAdmin ? 'Soporte Vextra' : 'Mi empresa' ?></div>
                <?php if (trim((string) ($mensaje['mensaje'] ?? '')) !== ''): ?><div><?= nl2br(e($mensaje['mensaje'] ?? '')) ?></div><?php endif; ?>
                <?php if (!empty($mensaje['archivo_ruta'])): ?>
                  <div class="mt-1"><a href="<?= e(url((string) $mensaje['archivo_ruta'])) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> <?= e($mensaje['archivo_nombre'] ?? 'Adjunto') ?></a></div>
                <?php endif; ?>
                <div class="small text-muted mt-1"><?= e((string) ($mensaje['fecha_creacion'] ?? '')) ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted">Crea o selecciona un chat para ver la conversación.</div>
          <?php endif; ?>
        </div>

        <?php if ($chat): ?>
          <?php if (!$chatAbierto): ?>
            <div class="alert alert-secondary small">Este chat está cerrado por soporte. Puedes crear un nuevo chat si necesitas ayuda adicional.</div>
          <?php endif; ?>
          <form id="formResponderChat" method="POST" action="<?= e(url('/app/soporte-chats/responder/' . $chatId)) ?>" class="d-grid gap-2 mt-auto" data-async-chat="1" enctype="multipart/form-data">
            <?= csrf_campo() ?>
            <input type="hidden" name="ultimo_id" id="ultimoIdInput" value="<?= $ultimoId ?>">
            <textarea id="mensajeInput" class="form-control" name="mensaje" rows="3" <?= $chatAbierto ? '' : 'disabled' ?> placeholder="Escribe un mensaje... (Enter envía · Shift+Enter salto de línea)"></textarea>
            <div class="d-flex gap-2 align-items-center">
              <input id="adjuntoInput" class="form-control" type="file" name="adjunto" <?= $chatAbierto ? '' : 'disabled' ?> accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
              <button id="btnEnviarMensaje" type="submit" class="btn btn-primary" <?= $chatAbierto ? '' : 'disabled' ?>>Enviar</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const app = document.getElementById('soporteApp');
  if (!app) return;

  const chatId = Number(app.dataset.chatId || 0);
  if (!chatId) return;

  const mensajesUrlBase = app.dataset.mensajesUrlBase;
  const responderUrlBase = app.dataset.responderUrlBase;
  const hilo = document.getElementById('hiloMensajes');
  const estado = document.getElementById('estadoConexion');
  const estadoChat = document.getElementById('estadoChat');
  const form = document.getElementById('formResponderChat');
  const input = document.getElementById('mensajeInput');
  const adjuntoInput = document.getElementById('adjuntoInput');
  const ultimoIdInput = document.getElementById('ultimoIdInput');
  const btnEnviar = document.getElementById('btnEnviarMensaje');

  let ultimoId = Number(app.dataset.ultimoId || 0);
  let chatAbierto = (app.dataset.chatEstado || 'abierto') === 'abierto';

  const escapeHtml = (str) => String(str || '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[c]));
  const normalizarRutaArchivo = (ruta) => {
    const valor = String(ruta || '');
    if (!valor.startsWith('/')) return valor;
    const base = String(window.APP_BASE_PATH || '').replace(/\\/$/, '');
    if (!base || valor === base || valor.startsWith(base + '/')) return valor;
    return base + valor;
  };

  const setEstadoChat = (abierto) => {
    chatAbierto = abierto;
    if (estadoChat) {
      estadoChat.textContent = abierto ? 'Abierto' : 'Cerrado';
      estadoChat.className = 'badge ' + (abierto ? 'text-bg-info' : 'text-bg-secondary');
    }
    if (input) input.disabled = !abierto;
    if (adjuntoInput) adjuntoInput.disabled = !abierto;
    if (btnEnviar) btnEnviar.disabled = !abierto;
  };

  const renderMensaje = (m) => {
    const esAdmin = m.remitente_tipo === 'admin';
    const box = document.createElement('div');
    box.className = 'p-2 rounded border ' + (esAdmin ? 'bg-light border-success-subtle ms-4' : 'border-primary-subtle me-4');
    const mensajeHtml = m.mensaje ? `<div>${escapeHtml(m.mensaje).replace(/\n/g, '<br>')}</div>` : '';
    const archivoHtml = m.archivo_ruta ? `<div class="mt-1"><a href="${normalizarRutaArchivo(m.archivo_ruta)}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> ${escapeHtml(m.archivo_nombre || 'Adjunto')}</a></div>` : '';
    box.innerHTML = `<div class="small fw-semibold ${esAdmin ? 'text-success' : 'text-primary'}">${esAdmin ? 'Soporte Vextra' : 'Mi empresa'}</div>${mensajeHtml}${archivoHtml}<div class="small text-muted mt-1">${m.fecha_creacion || ''}</div>`;
    hilo.appendChild(box);
  };

  const pedirMensajes = async () => {
    try {
      const res = await fetch(`${mensajesUrlBase}/${chatId}?ultimo_id=${ultimoId}`, {headers: {'Accept': 'application/json'}});
      if (!res.ok) throw new Error('Error');
      const data = await res.json();
      setEstadoChat((data.estado || 'abierto') === 'abierto');
      (data.mensajes || []).forEach((m) => {
        renderMensaje(m);
        ultimoId = Math.max(ultimoId, Number(m.id || 0));
      });
      if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
      if ((data.mensajes || []).length > 0) {
        hilo.scrollTop = hilo.scrollHeight;
      }
      estado.textContent = 'En línea';
      estado.className = 'badge text-bg-success';
    } catch (e) {
      estado.textContent = 'Reconectando...';
      estado.className = 'badge text-bg-warning';
    }
  };

  if (input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (form) form.requestSubmit();
      }
    });
  }

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!chatAbierto) return;

      const mensaje = (input.value || '').trim();
      const tieneArchivo = adjuntoInput && adjuntoInput.files && adjuntoInput.files.length > 0;
      if (!mensaje && !tieneArchivo) return;

      btnEnviar.disabled = true;
      const formData = new FormData(form);
      formData.set('ultimo_id', String(ultimoId));

      try {
        const res = await fetch(`${responderUrlBase}/${chatId}`, {
          method: 'POST',
          headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
          body: formData,
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.mensaje || 'Error');

        (data.mensajes || []).forEach((m) => {
          renderMensaje(m);
          ultimoId = Math.max(ultimoId, Number(m.id || 0));
        });
        input.value = '';
        if (adjuntoInput) adjuntoInput.value = '';
        if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
        hilo.scrollTop = hilo.scrollHeight;
      } catch (err) {
        alert(err.message || 'No se pudo enviar el mensaje.');
      } finally {
        btnEnviar.disabled = !chatAbierto;
      }
    });
  }

  setEstadoChat(chatAbierto);
  setInterval(pedirMensajes, 5000);
})();
</script>
