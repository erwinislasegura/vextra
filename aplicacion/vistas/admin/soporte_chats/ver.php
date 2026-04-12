<section class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h6 mb-0">Chat #<?= (int) ($chat['id'] ?? 0) ?> · <?= e($chat['empresa_nombre'] ?? '') ?></h1>
      <small class="text-muted"><?= e($chat['empresa_correo'] ?? '') ?> · <?= e($chat['asunto'] ?? '') ?></small>
    </div>
    <a href="<?= e(url('/admin/soporte-chats')) ?>" class="btn btn-sm btn-outline-secondary">Volver</a>
  </div>
  <div class="card-body">
    <div class="d-grid gap-2 mb-3">
      <?php foreach ($mensajes as $mensaje): ?>
        <?php $esAdmin = ($mensaje['remitente_tipo'] ?? '') === 'admin'; ?>
        <div class="p-2 rounded border <?= $esAdmin ? 'bg-light border-success-subtle' : 'border-primary-subtle' ?>">
          <div class="small fw-semibold <?= $esAdmin ? 'text-success' : 'text-primary' ?>"><?= $esAdmin ? 'Administrador' : 'Cliente' ?></div>
          <div><?= nl2br(e($mensaje['mensaje'] ?? '')) ?></div>
          <div class="small text-muted mt-1"><?= e((string) ($mensaje['fecha_creacion'] ?? '')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= e(url('/admin/soporte-chats/responder/' . (int) ($chat['id'] ?? 0))) ?>" class="d-grid gap-2">
      <?= csrf_campo() ?>
      <textarea class="form-control" name="mensaje" rows="4" required placeholder="Escribe la respuesta para el cliente"></textarea>
      <button type="submit" class="btn btn-success">Responder al cliente</button>
    </form>
  </div>
</section>
