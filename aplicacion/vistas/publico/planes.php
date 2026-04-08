<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-info-subtle text-info-emphasis mb-2">Planes del sistema de cotizaciones</span>
                <h1 class="display-6 fw-bold mb-3">Elige el plan ideal para vender con orden y escalar tu operación comercial</h1>
                <p class="lead text-secondary mb-0">Compara precios, beneficios y alcance de cada plan para implementar un software de cotizaciones que se adapte a la etapa actual de tu empresa.</p>
            </div>
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-body small">
                        <h2 class="h6">¿Qué ganas con cualquier plan?</h2>
                        <ul class="mb-0">
                            <li>Cotizaciones profesionales en menos tiempo.</li>
                            <li>Control del proceso comercial por estado.</li>
                            <li>Base centralizada de clientes y productos.</li>
                            <li>Escalabilidad para crecer sin desorden.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="planes-lista">
    <div class="container">
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" aria-label="Seleccionar modalidad">
                <button type="button" class="btn btn-primary" data-billing-toggle="mensual">Mensual</button>
                <button type="button" class="btn btn-outline-primary" data-billing-toggle="anual">Anual (Ahorra hasta 10%)</button>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach($planes as $plan): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-2" style="border-color: <?= e($plan['color_visual']) ?> !important;">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 mb-2"><?= e($plan['nombre']) ?></h2>
                            <p class="small text-secondary mb-3"><?= e($plan['resumen_comercial'] ?: $plan['descripcion_comercial']) ?></p>
                            <div
                                class="mb-3"
                                data-precio-mensual="<?= e(number_format((float) $plan['precio_mensual'], 0, ',', '.')) ?>"
                                data-precio-anual="<?= e(number_format((float) $plan['precio_anual'], 0, ',', '.')) ?>"
                            >
                                <div class="h3 mb-0" data-price-value>$<?= number_format((float)$plan['precio_mensual'],0,',','.') ?></div>
                                <small class="text-secondary" data-price-label>CLP / mes</small>
                            </div>
                            <ul class="small ps-3 d-grid gap-1 mb-3">
                                <?php foreach (($plan['funcionalidades'] ?? []) as $funcionalidad): ?>
                                    <li><?= e($funcionalidad['descripcion'] ?: $funcionalidad['nombre']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="mt-auto d-grid gap-2">
                                <a
                                    class="btn btn-primary btn-sm"
                                    data-plan-link
                                    href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>"
                                >Contratar plan</a>
                                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/contacto')) ?>">Hablar con ventas</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <h2 class="h4 mb-3">¿Cómo elegir el plan correcto?</h2>
        <div class="row g-3 small">
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><strong>Evalúa tu volumen</strong><p class="mb-0 text-secondary">Si cotizas a diario, prioriza planes con mayor capacidad para crecer sin fricción.</p></div></div></div>
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><strong>Revisa tu equipo</strong><p class="mb-0 text-secondary">Si trabajan varios vendedores, elige un plan que mejore coordinación y control de gestión.</p></div></div></div>
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><strong>Piensa en escalabilidad</strong><p class="mb-0 text-secondary">El mejor plan es el que cubre lo actual y te permite ampliar operación cuando vendas más.</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="h4">¿Quieres ayuda para elegir el plan ideal?</h2>
        <p class="text-secondary">Nuestro equipo comercial te orienta para que contrates con claridad y sin pagar de más.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-primary btn-sm">Contactar asesor</a>
            <a href="<?= e(url('/registro')) ?>" class="btn btn-outline-primary btn-sm">Crear cuenta</a>
        </div>
    </div>
</section>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2">
        <a href="#planes-lista" class="btn btn-primary btn-sm">Comprar plan</a>
        <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Contactar</a>
    </div>
</div>

<script>
(() => {
    const toggles = document.querySelectorAll('[data-billing-toggle]');
    const tarjetas = document.querySelectorAll('#planes-lista .card');
    let modalidad = 'mensual';

    const aplicarModalidad = (valor) => {
        modalidad = valor === 'anual' ? 'anual' : 'mensual';

        toggles.forEach((btn) => {
            const activa = btn.getAttribute('data-billing-toggle') === modalidad;
            btn.classList.toggle('btn-primary', activa);
            btn.classList.toggle('btn-outline-primary', !activa);
        });

        tarjetas.forEach((card) => {
            const bloquePrecio = card.querySelector('[data-precio-mensual]');
            const valorPrecio = card.querySelector('[data-price-value]');
            const etiqueta = card.querySelector('[data-price-label]');
            const link = card.querySelector('[data-plan-link]');
            if (!bloquePrecio || !valorPrecio || !etiqueta || !link) return;

            const mensual = bloquePrecio.getAttribute('data-precio-mensual') || '0';
            const anual = bloquePrecio.getAttribute('data-precio-anual') || mensual;
            valorPrecio.textContent = '$' + (modalidad === 'anual' ? anual : mensual);
            etiqueta.textContent = modalidad === 'anual' ? 'CLP / año' : 'CLP / mes';

            const href = new URL(link.getAttribute('href'), window.location.origin);
            href.searchParams.set('frecuencia', modalidad);
            link.setAttribute('href', href.pathname + href.search);
        });
    };

    toggles.forEach((btn) => {
        btn.addEventListener('click', () => aplicarModalidad(btn.getAttribute('data-billing-toggle')));
    });

    aplicarModalidad('mensual');
})();
</script>
