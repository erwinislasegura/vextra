<div class="container py-5">
  <h1 class="h3">Contratar plan: <?= e($plan['nombre']) ?></h1>
  <p>Continúa con el registro de tu empresa para activar el servicio.</p>
  <a class="btn btn-primary btn-sm" href="/registro?plan=<?= (int) $plan['id'] ?>">Crear cuenta ahora</a>
</div>
