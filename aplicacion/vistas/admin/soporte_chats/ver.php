<?php $ultimoId = !empty($mensajes) ? (int) end($mensajes)['id'] : 0; ?>
<section class="card" id="soporteAdminApp" data-chat-id="<?= (int) ($chat['id'] ?? 0) ?>" data-ultimo-id="<?= $ultimoId ?>" data-mensajes-url-base="<?= e(url('/admin/soporte-chats/mensajes')) ?>" data-responder-url-base="<?= e(url('/admin/soporte-chats/responder')) ?>">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h6 mb-0">Chat #<?= (int) ($chat['id'] ?? 0) ?> · <?= e($chat['empresa_nombre'] ?? '') ?></h1>
      <small class="text-muted"><?= e($chat['empresa_correo'] ?? '') ?> · <?= e($chat['asunto'] ?? '') ?></small>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <span class="badge text-bg-light border" id="estadoConexionAdmin">Actualización automática</span>
      <a href="<?= e(url('/admin/soporte-chats')) ?>" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>
  </div>
  <div class="card-body">
    <div class="d-grid gap-2 mb-3" id="hiloAdmin" style="max-height: 450px; overflow:auto;">
      <?php foreach ($mensajes as $mensaje): ?>
        <?php $esAdmin = ($mensaje['remitente_tipo'] ?? '') === 'admin'; ?>
        <div class="p-2 rounded border <?= $esAdmin ? 'bg-light border-success-subtle' : 'border-primary-subtle' ?>">
          <div class="small fw-semibold <?= $esAdmin ? 'text-success' : 'text-primary' ?>"><?= $esAdmin ? 'Administrador' : 'Cliente' ?></div>
          <div><?= nl2br(e($mensaje['mensaje'] ?? '')) ?></div>
          <div class="small text-muted mt-1"><?= e((string) ($mensaje['fecha_creacion'] ?? '')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <form id="formAdminResponder" method="POST" action="<?= e(url('/admin/soporte-chats/responder/' . (int) ($chat['id'] ?? 0))) ?>" class="d-grid gap-2" data-async-chat="1">
      <?= csrf_campo() ?>
      <input type="hidden" name="ultimo_id" id="ultimoIdAdmin" value="<?= $ultimoId ?>">
      <textarea class="form-control" id="mensajeAdmin" name="mensaje" rows="4" required placeholder="Escribe la respuesta para el cliente"></textarea>
      <button type="submit" id="btnAdminEnviar" class="btn btn-success">Responder al cliente</button>
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
  const form = document.getElementById('formAdminResponder');
  const input = document.getElementById('mensajeAdmin');
  const ultimoIdInput = document.getElementById('ultimoIdAdmin');
  const btn = document.getElementById('btnAdminEnviar');
  let ultimoId = Number(app.dataset.ultimoId || 0);

  const render = (m) => {
    const esAdmin = m.remitente_tipo === 'admin';
    const item = document.createElement('div');
    item.className = 'p-2 rounded border ' + (esAdmin ? 'bg-light border-success-subtle' : 'border-primary-subtle');
    item.innerHTML = `
      <div class="small fw-semibold ${esAdmin ? 'text-success' : 'text-primary'}">${esAdmin ? 'Administrador' : 'Cliente'}</div>
      <div>${String(m.mensaje || '').replace(/\n/g, '<br>')}</div>
      <div class="small text-muted mt-1">${m.fecha_creacion || ''}</div>
    `;
    hilo.appendChild(item);
  };

  const poll = async () => {
    try {
      const res = await fetch(`${mensajesUrlBase}/${chatId}?ultimo_id=${ultimoId}`, {headers: {'Accept': 'application/json'}});
      if (!res.ok) throw new Error('error');
      const data = await res.json();
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

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const mensaje = (input.value || '').trim();
    if (!mensaje) return;

    btn.disabled = true;
    const formData = new FormData(form);
    formData.set('ultimo_id', String(ultimoId));

    try {
      const res = await fetch(`${responderUrlBase}/${chatId}`, {
        method: 'POST',
        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: formData,
      });
      if (!res.ok) throw new Error('error');
      const data = await res.json();
      (data.mensajes || []).forEach((m) => {
        render(m);
        ultimoId = Math.max(ultimoId, Number(m.id || 0));
      });
      input.value = '';
      if (ultimoIdInput) ultimoIdInput.value = String(ultimoId);
      hilo.scrollTop = hilo.scrollHeight;
    } catch (err) {
      alert('No se pudo enviar la respuesta.');
    } finally {
      btn.disabled = false;
    }
  });

  setInterval(poll, 5000);
})();
</script>
