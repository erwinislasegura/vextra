<?php
$chatId = (int) ($chat['id'] ?? 0);
$ultimoId = !empty($mensajes) ? (int) end($mensajes)['id'] : 0;
?>
<section class="row g-3" id="soporteApp" data-chat-id="<?= $chatId ?>" data-ultimo-id="<?= $ultimoId ?>" data-mensajes-url-base="<?= e(url('/app/soporte-chats/mensajes')) ?>" data-responder-url-base="<?= e(url('/app/soporte-chats/responder')) ?>">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><strong>Soporte</strong></div>
      <div class="card-body border-bottom">
        <form method="POST" action="<?= e(url('/app/soporte-chats/crear')) ?>" class="d-grid gap-2">
          <?= csrf_campo() ?>
          <input class="form-control" type="text" name="asunto" required maxlength="180" placeholder="Asunto">
          <textarea class="form-control" name="mensaje" rows="3" required placeholder="Describe tu requerimiento"></textarea>
          <button class="btn btn-primary" type="submit">Crear chat</button>
        </form>
      </div>
      <div class="list-group list-group-flush" style="max-height: 460px; overflow:auto;">
        <?php if (!empty($chats)): ?>
          <?php foreach ($chats as $item): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= (int) $item['id'] === $chatId ? 'active' : '' ?>" href="<?= e(url('/app/soporte-chats?chat=' . (int) $item['id'])) ?>">
              <span>
                <strong class="d-block"><?= e($item['asunto']) ?></strong>
                <small class="<?= (int) $item['id'] === $chatId ? 'text-white-50' : 'text-muted' ?>"><?= e((string) $item['fecha_ultimo_mensaje']) ?></small>
              </span>
              <?php if ((int) ($item['no_leidos_cliente'] ?? 0) > 0): ?><span class="badge text-bg-success">Nuevo</span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="p-3 text-muted small">No hay chats creados aún.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><?= $chat ? e($chat['asunto']) : 'Selecciona un chat' ?></strong>
        <span class="badge text-bg-light border" id="estadoConexion">Actualización automática</span>
      </div>
      <div class="card-body d-flex flex-column">
        <div id="hiloMensajes" class="d-grid gap-2 mb-3" style="max-height: 430px; overflow:auto;">
          <?php if ($chat): ?>
            <?php foreach ($mensajes as $mensaje): ?>
              <?php $esAdmin = ($mensaje['remitente_tipo'] ?? '') === 'admin'; ?>
              <div class="p-2 rounded border <?= $esAdmin ? 'bg-light border-success-subtle' : 'border-primary-subtle' ?>">
                <div class="small fw-semibold <?= $esAdmin ? 'text-success' : 'text-primary' ?>"><?= $esAdmin ? 'Soporte Vextra' : 'Mi empresa' ?></div>
                <div><?= nl2br(e($mensaje['mensaje'] ?? '')) ?></div>
                <div class="small text-muted mt-1"><?= e((string) ($mensaje['fecha_creacion'] ?? '')) ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted">Crea o selecciona un chat para ver la conversación.</div>
          <?php endif; ?>
        </div>

        <?php if ($chat): ?>
          <form id="formResponderChat" method="POST" action="<?= e(url('/app/soporte-chats/responder/' . $chatId)) ?>" class="d-grid gap-2 mt-auto" data-async-chat="1">
            <?= csrf_campo() ?>
            <input type="hidden" name="ultimo_id" id="ultimoIdInput" value="<?= $ultimoId ?>">
            <textarea id="mensajeInput" class="form-control" name="mensaje" rows="3" required placeholder="Escribe un mensaje..."></textarea>
            <button id="btnEnviarMensaje" type="submit" class="btn btn-primary">Enviar</button>
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
  const form = document.getElementById('formResponderChat');
  const input = document.getElementById('mensajeInput');
  const ultimoIdInput = document.getElementById('ultimoIdInput');
  const btnEnviar = document.getElementById('btnEnviarMensaje');

  let ultimoId = Number(app.dataset.ultimoId || 0);

  const renderMensaje = (m) => {
    const esAdmin = m.remitente_tipo === 'admin';
    const box = document.createElement('div');
    box.className = 'p-2 rounded border ' + (esAdmin ? 'bg-light border-success-subtle' : 'border-primary-subtle');
    box.innerHTML = `
      <div class="small fw-semibold ${esAdmin ? 'text-success' : 'text-primary'}">${esAdmin ? 'Soporte Vextra' : 'Mi empresa'}</div>
      <div>${String(m.mensaje || '').replace(/\n/g, '<br>')}</div>
      <div class="small text-muted mt-1">${m.fecha_creacion || ''}</div>
    `;
    hilo.appendChild(box);
  };

  const pedirMensajes = async () => {
    try {
      const res = await fetch(`${mensajesUrlBase}/${chatId}?ultimo_id=${ultimoId}`, {headers: {'Accept': 'application/json'}});
      if (!res.ok) throw new Error('Error');
      const data = await res.json();
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

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const mensaje = (input.value || '').trim();
      if (!mensaje) return;

      btnEnviar.disabled = true;
      const formData = new FormData(form);
      formData.set('ultimo_id', String(ultimoId));

      try {
        const res = await fetch(`${responderUrlBase}/${chatId}`, {
          method: 'POST',
          headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
          body: formData,
        });
        if (!res.ok) throw new Error('Error');
        const data = await res.json();
        (data.mensajes || []).forEach((m) => {
          renderMensaje(m);
          ultimoId = Math.max(ultimoId, Number(m.id || 0));
        });
        input.value = '';
        if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
        hilo.scrollTop = hilo.scrollHeight;
      } catch (err) {
        alert('No se pudo enviar el mensaje.');
      } finally {
        btnEnviar.disabled = false;
      }
    });
  }

  setInterval(pedirMensajes, 5000);
})();
</script>
