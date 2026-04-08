<h1 class="h4 mb-3">Editar vendedor</h1>
<div class="alert alert-info info-modulo mb-3">
    <div class="fw-semibold mb-1">Recomendaciones de actualización</div>
    <ul class="mb-0 small ps-3">
        <li>Confirma comisión y estado para evitar cálculos comerciales incorrectos.</li>
        <li>Si el vendedor no opera temporalmente, usa estado inactivo.</li>
    </ul>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" class="row g-2">
            <?= csrf_campo() ?>
            <?php require __DIR__ . '/_formulario_vendedor.php'; ?>
            <div class="col-12">
                <button class="btn btn-primary btn-sm">Guardar cambios</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/vendedores')) ?>">Cancelar</a>
            </div>
        </form>
    </div>
</div>
