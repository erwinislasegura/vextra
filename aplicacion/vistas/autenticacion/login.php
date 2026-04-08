<section class="py-5">
    <div class="container" style="max-width: 560px;">
        <div class="text-center mb-3">
            <span class="badge bg-info-subtle text-info-emphasis mb-2">Acceso seguro</span>
            <h1 class="h3 mb-2">Iniciar sesión en Vextra</h1>
            <p class="text-secondary small mb-0">Accede a tu cuenta para gestionar cotizaciones, clientes y seguimiento comercial en un entorno profesional.</p>
        </div>

        <form method="POST" class="card card-body shadow-sm">
            <?= csrf_campo() ?>
            <label class="form-label small">Correo corporativo</label>
            <input type="email" name="correo" class="form-control" placeholder="tu@empresa.com" required>

            <label class="form-label small mt-3">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>

            <button class="btn btn-primary mt-3" type="submit">Ingresar al panel</button>
            <a class="small mt-2 text-decoration-none" href="<?= e(url('/recuperar-contrasena')) ?>">¿Olvidaste tu contraseña?</a>

            <hr>
            <div class="small text-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>¿Aún no tienes cuenta?</span>
                <a href="<?= e(url('/registro')) ?>" class="btn btn-outline-primary btn-sm">Crear cuenta empresarial</a>
            </div>
        </form>
    </div>
</section>
