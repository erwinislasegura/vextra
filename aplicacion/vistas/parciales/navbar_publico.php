<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top public-navbar">
  <div class="container">
    <a class="navbar-brand d-inline-flex align-items-center fw-semibold" href="<?= e(url('/')) ?>">
      <img src="<?= e(url('/img/logo/logo_vextra.png')) ?>" alt="Vextra" class="brand-logo-public" width="146" height="72" fetchpriority="high" decoding="async">
    </a>
    <button class="navbar-toggler" type="button" data-nav-toggle="#n" aria-controls="n" aria-expanded="false" aria-label="Abrir menú"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="n" data-nav-collapse>
      <ul class="navbar-nav ms-auto gap-1 small align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/')) ?>">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/caracteristicas')) ?>">Características</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/planes')) ?>">Planes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/preguntas-frecuentes')) ?>">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/contacto')) ?>">Contacto</a></li>
        <li class="nav-item"><a class="btn btn-outline-primary btn-sm w-100" href="<?= e(url('/iniciar-sesion')) ?>">Iniciar sesión</a></li>
        <li class="nav-item"><a class="btn btn-primary btn-sm w-100" href="<?= e(url('/registro')) ?>">Crear cuenta empresarial</a></li>
      </ul>
    </div>
  </div>
</nav>
