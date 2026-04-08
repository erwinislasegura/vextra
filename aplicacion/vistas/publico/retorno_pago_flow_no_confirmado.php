<section class="py-5">
  <?php
    $estadoPago = (string) ($estado ?? 'pendiente');
    $esNoAprobado = in_array($estadoPago, ['rechazado', 'anulado'], true);
    $iconoEstado = $esNoAprobado ? '❌' : '⏳';
  ?>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-md-5 text-center">
            <div class="display-5 mb-3"><?= e($iconoEstado) ?></div>
            <h2 class="h4 mb-2">Pago aún no confirmado</h2>
            <p class="text-muted mb-4"><?= e($mensaje ?? 'Estamos revisando tu pago con Flow.') ?></p>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
              <a href="<?= e(url('/iniciar-sesion')) ?>" class="btn btn-primary px-4">Iniciar sesión</a>
              <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-secondary px-4">Ver planes</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
