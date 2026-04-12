<?php
use Aplicacion\Modelos\SoporteChat;

$resumenPlan = resumen_plan_empresa_actual();
$empresaNombre = nombre_empresa_actual() ?? 'Empresa';
$estadoSuscripcion = 'Sin suscripción vigente';
$claseEstado = 'text-bg-warning';
$esSuperAdmin = (usuario_actual()['rol_codigo'] ?? '') === 'superadministrador';
$soporteLink = $esSuperAdmin ? url('/admin/soporte-chats') : url('/app/soporte-chats');
$soporteNuevos = 0;
try {
    $soporteModel = new SoporteChat();
    $soporteNuevos = $esSuperAdmin
        ? $soporteModel->contarNoLeidosAdmin()
        : $soporteModel->contarNoLeidosEmpresa((int) (empresa_actual_id() ?? 0));
} catch (\Throwable $e) {
    $soporteNuevos = 0;
}

if ($resumenPlan) {
    $dias = $resumenPlan['dias_restantes'];
    if ($dias === null) {
        $estadoSuscripcion = ucfirst((string) ($resumenPlan['estado'] ?? 'Sin vigencia'));
        $claseEstado = in_array($resumenPlan['estado'] ?? '', ['cancelada', 'suspendida'], true) ? 'text-bg-danger' : 'text-bg-secondary';
    } elseif ((int) $dias < 0) {
        $estadoSuscripcion = 'Vencida hace ' . abs((int) $dias) . ' días';
        $claseEstado = 'text-bg-danger';
    } elseif ((int) $dias <= 7) {
        $estadoSuscripcion = $dias . ' días restantes';
        $claseEstado = 'text-bg-warning';
    } else {
        $estadoSuscripcion = $dias . ' días restantes';
        $claseEstado = 'text-bg-success';
    }
}
?>
<header class="topbar border-bottom bg-white px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div class="topbar-identidad d-flex align-items-center gap-3">
    <div class="topbar-identidad__avatar">
      <i class="bi bi-building"></i>
    </div>
    <div>
      <strong class="d-block topbar-identidad__empresa"><?= e($empresaNombre) ?></strong>
      <div class="topbar-plan mt-1 d-flex align-items-center flex-wrap gap-2">
        <span class="badge rounded-pill text-bg-primary"><?= e($resumenPlan['plan_nombre'] ?? 'Plan no asignado') ?></span>
        <span class="badge rounded-pill <?= e($claseEstado) ?>"><?= e($estadoSuscripcion) ?></span>
      </div>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2 ms-auto">
    <a class="btn btn-sm btn-outline-success" href="<?= e($soporteLink) ?>">
      <i class="bi bi-headset"></i> Soporte
      <?php if ($soporteNuevos > 0): ?><span class="badge text-bg-success ms-1"><?= (int) $soporteNuevos ?></span><?php endif; ?>
    </a>
    <span class="text-muted small d-none d-md-inline">Hola, <?= e(usuario_actual()['nombre'] ?? 'Invitado') ?></span>
    <?php if (!empty($_SESSION['admin_original'])): ?>
      <form method="POST" action="<?= e(url('/app/volver-admin')) ?>" class="m-0">
        <?= csrf_campo() ?>
        <button class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-return-left"></i> Volver a admin</button>
      </form>
    <?php endif; ?>
    <?php if (!$esSuperAdmin && !empty(usuario_actual()['id'])): ?>
      <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/app/usuarios/editar/' . (int) usuario_actual()['id'])) ?>"><i class="bi bi-person-gear"></i> Mi perfil</a>
    <?php endif; ?>
    <form method="POST" action="<?= e(url('/cerrar-sesion')) ?>" class="m-0"><?= csrf_campo() ?><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right"></i> Salir</button></form>
  </div>
</header>
