<section class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h1 class="h5 mb-0">Chats de soporte</h1>
    <form class="d-flex gap-2" method="GET" action="<?= e(url('/admin/soporte-chats')) ?>">
      <input class="form-control form-control-sm" type="search" name="q" value="<?= e($buscar ?? '') ?>" placeholder="Buscar empresa o asunto">
      <button class="btn btn-sm btn-outline-primary" type="submit">Buscar</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Asunto</th>
          <th>Último mensaje</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($chats)): ?>
          <?php foreach ($chats as $chat): ?>
            <tr>
              <td>
                <strong><?= e($chat['empresa_nombre'] ?? '') ?></strong>
                <div class="small text-muted"><?= e($chat['empresa_correo'] ?? '') ?></div>
              </td>
              <td>
                <?= e($chat['asunto'] ?? '') ?>
                <?php if ((int) ($chat['no_leidos_admin'] ?? 0) > 0): ?>
                  <span class="badge text-bg-success ms-1">Nuevo</span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= e((string) ($chat['fecha_ultimo_mensaje'] ?? '')) ?></td>
              <td><?= e($chat['estado'] ?? 'abierto') ?></td>
              <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= e(url('/admin/soporte-chats/ver/' . (int) $chat['id'])) ?>">Abrir</a></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center text-muted py-3">Sin chats de soporte.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
