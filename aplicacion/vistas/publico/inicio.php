<?php
$capturasBaseUrl = url('/img/Captura Sistema/');
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Vextra",
  "brand": {"@type": "Brand", "name": "Vextra"},
  "description": "Sistema de cotizaciones, punto de venta e inventario para empresas en Chile.",
  "category": "SoftwareApplication",
  "url": "<?= e(url('/')) ?>",
  "offers": {"@type": "AggregateOffer", "priceCurrency": "CLP"}
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type":"Question","name":"¿Qué es Vextra?","acceptedAnswer":{"@type":"Answer","text":"Es un sistema de cotizaciones, punto de venta e inventario para empresas en Chile."}},
    {"@type":"Question","name":"¿Tiene software de cotización online?","acceptedAnswer":{"@type":"Answer","text":"Sí, permite crear y enviar cotizaciones con seguimiento comercial."}},
    {"@type":"Question","name":"¿El POS descuenta inventario automáticamente?","acceptedAnswer":{"@type":"Answer","text":"Sí, cada venta actualiza el stock en tiempo real."}},
    {"@type":"Question","name":"¿Puedo solicitar una demo?","acceptedAnswer":{"@type":"Answer","text":"Sí, puedes solicitar demo comercial desde contacto."}},
    {"@type":"Question","name":"¿Cuál plan recomiendan?","acceptedAnswer":{"@type":"Answer","text":"Generalmente el plan intermedio es el más conveniente para empresas en crecimiento."}}
  ]
}
</script>

<section class="hero py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary-emphasis mb-2">Software para empresas Chile: cotización + POS + inventario</span>
                <h1 class="display-6 fw-bold mb-3">Sistema de cotizaciones y ventas con inventario para crecer con control real</h1>
                <p class="lead text-secondary">Vextra conecta gestión comercial, sistema punto de venta y sistema de inventario para que tu empresa venda más y trabaje con orden.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar ahora</a>
                    <a href="#planes" class="btn btn-outline-primary">Contratar plan</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary">Solicitar demo</a>
                </div>
            </div>
            <aside class="col-lg-5">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h2 class="h5">¿Qué ganas al centralizar tu operación?</h2>
                        <ul class="small mb-0 ps-3 d-grid gap-2">
                            <li>Ahorro de tiempo en cotización y ventas.</li>
                            <li>Control de stock en tiempo real.</li>
                            <li>Seguimiento comercial para cerrar más.</li>
                            <li>Orden administrativo y decisiones con datos.</li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Vista del sistema en operación</h2>
            <p class="text-secondary mb-0">Cotizaciones, POS e inventario conectados en un solo flujo.</p>
        </div>
        <div class="landing-slider" data-slider data-slider-interval="3200">
            <article class="landing-slide is-active" data-slide>
                <img src="<?= e($capturasBaseUrl . rawurlencode('Cotizaciones 1.png')) ?>" alt="Panel de cotizaciones" loading="lazy">
                <div class="landing-carousel-caption"><h3 class="h6 mb-1">Panel de cotizaciones</h3><p class="small mb-0">Control de estados y seguimiento comercial.</p></div>
            </article>
            <article class="landing-slide" data-slide>
                <img src="<?= e($capturasBaseUrl . rawurlencode('Cotizaciones 2.png')) ?>" alt="Editor de cotización" loading="lazy">
                <div class="landing-carousel-caption"><h3 class="h6 mb-1">Editor de presupuesto</h3><p class="small mb-0">Ajusta precios, descuentos y condiciones.</p></div>
            </article>
            <article class="landing-slide" data-slide>
                <img src="<?= e($capturasBaseUrl . rawurlencode('Punto de venta.png')) ?>" alt="Punto de venta" loading="lazy">
                <div class="landing-carousel-caption"><h3 class="h6 mb-1">POS integrado</h3><p class="small mb-0">Cada venta actualiza inventario automáticamente.</p></div>
            </article>
            <button class="landing-slider-control prev" type="button" data-slide-nav="prev" aria-label="Imagen anterior">‹</button>
            <button class="landing-slider-control next" type="button" data-slide-nav="next" aria-label="Imagen siguiente">›</button>
            <div class="landing-slider-dots" role="tablist" aria-label="Navegación de capturas">
                <button type="button" class="is-active" data-slide-dot="0" aria-label="Ver captura 1"></button>
                <button type="button" data-slide-dot="1" aria-label="Ver captura 2"></button>
                <button type="button" data-slide-dot="2" aria-label="Ver captura 3"></button>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="row g-4">
            <article class="col-lg-6">
                <h2 class="h3">Beneficios comerciales que impactan resultados</h2>
                <div class="row g-3 mt-1">
                    <div class="col-12"><div class="card border-0 bg-light-subtle"><div class="card-body d-flex gap-3"><span class="fs-4 text-primary"><i class="bi bi-lightning-charge-fill"></i></span><div><h3 class="h5 mb-1">Ahorro de tiempo</h3><p class="mb-0 small">Cotiza más rápido con datos actualizados.</p></div></div></div></div>
                    <div class="col-12"><div class="card border-0 bg-light-subtle"><div class="card-body d-flex gap-3"><span class="fs-4 text-success"><i class="bi bi-graph-up-arrow"></i></span><div><h3 class="h5 mb-1">Más ventas</h3><p class="mb-0 small">Seguimiento ordenado para mejorar cierres.</p></div></div></div></div>
                    <div class="col-12"><div class="card border-0 bg-light-subtle"><div class="card-body d-flex gap-3"><span class="fs-4 text-warning"><i class="bi bi-box-seam-fill"></i></span><div><h3 class="h5 mb-1">Control de stock</h3><p class="mb-0 small">Evita sobreventa y mejora reposición.</p></div></div></div></div>
                </div>
            </article>
            <article class="col-lg-6">
                <h2 class="h3">Características clave</h2>
                <ul class="small ps-3 d-grid gap-2">
                    <li>Cotizaciones y presupuestos profesionales.</li>
                    <li>Sistema punto de venta conectado.</li>
                    <li>Inventario con movimientos y alertas.</li>
                    <li>Gestión de clientes y oportunidades.</li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="<?= e(url('/caracteristicas')) ?>" class="btn btn-outline-primary btn-sm">Ver características</a>
                    <a href="<?= e(url('/preguntas-frecuentes')) ?>" class="btn btn-outline-secondary btn-sm">Preguntas frecuentes</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white" id="planes">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Planes de software para empresas en Chile</h2>
            <p class="text-secondary mb-0">Compara modalidad mensual o anual.</p>
        </div>
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" aria-label="Seleccionar modalidad">
                <button type="button" class="btn btn-primary" data-home-billing="mensual">Mensual</button>
                <button type="button" class="btn btn-outline-primary" data-home-billing="anual">Anual (Ahorra hasta 10%)</button>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            <?php foreach ($planes as $index => $plan): ?>
                <?php $esRecomendado = !empty($plan['recomendado']) || $index === 1; ?>
                <div class="col-12 col-lg-4">
                    <article class="card h-100 border-2 <?= $esRecomendado ? 'border-primary border-3 shadow' : '' ?>" style="border-color: <?= e($plan['color_visual'] ?: '#dce3eb') ?> !important;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php if ($esRecomendado): ?><span class="badge text-bg-primary">MÁS CONVENIENTE</span><?php endif; ?>
                            </div>
                            <h3 class="h5"><?= e($plan['nombre']) ?></h3>
                            <p class="text-secondary small"><?= e($plan['resumen_comercial'] ?: $plan['descripcion_comercial']) ?></p>
                            <div class="h3 mb-0" data-home-precio data-precio-mensual="<?= e(number_format((float) $plan['precio_mensual'], 0, ',', '.')) ?>" data-precio-anual="<?= e(number_format((float) $plan['precio_anual'], 0, ',', '.')) ?>">$<?= number_format((float) $plan['precio_mensual'], 0, ',', '.') ?> <small class="fs-6">/ mensual</small></div>
                            <ul class="small ps-3 d-grid gap-1 mt-2">
                                <?php foreach ($plan['funcionalidades'] as $funcionalidad): ?>
                                    <li><?= e($funcionalidad['descripcion'] ?: $funcionalidad['nombre']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-grid gap-2 mt-auto">
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-primary btn-sm" data-home-link>Contratar plan</a>
                                <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Solicitar demo</a>
                            </div>
                        </div>
                    </article>
                </div>
                <p class="small text-secondary mt-3 mb-0">
                    Revisa más detalle en <a href="<?= e(url('/caracteristicas')) ?>">Características</a>, compara alternativas en <a href="<?= e(url('/planes')) ?>">Planes</a>, resuelve dudas en <a href="<?= e(url('/preguntas-frecuentes')) ?>">Preguntas frecuentes</a> o contacta al equipo en <a href="<?= e(url('/contacto')) ?>">Contacto</a>.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom" id="faq">
    <div class="container">
        <h2 class="h3 mb-2">Preguntas frecuentes sobre cotización, POS e inventario</h2>
        <p class="text-secondary">Respuestas claras para evaluar un sistema de cotizaciones, software de cotización online y sistema de ventas con inventario para empresas en Chile.</p>
        <div class="accordion" id="acordeonFaqSeo">
            <?php foreach ($faqSeo as $index => $faq): ?>
                <article class="accordion-item">
                    <h3 class="accordion-header" id="faqHeading<?= $index ?>">
                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="faqCollapse<?= $index ?>">
                            <?= e($faq['pregunta']) ?>
                        </button>
                    </h3>
                    <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="faqHeading<?= $index ?>" data-bs-parent="#acordeonFaqSeo">
                        <div class="accordion-body small">
                            <?= e($faq['respuesta']) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 border-bottom" id="faq">
    <div class="container">
        <h2 class="h3 mb-2">Preguntas frecuentes sobre cotización, POS e inventario</h2>
        <div class="accordion" id="acordeonFaqSeo">
            <article class="accordion-item"><h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">¿Qué es Vextra?</button></h3><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#acordeonFaqSeo"><div class="accordion-body small">Es una plataforma para cotizar, vender y controlar inventario en una sola operación.</div></div></article>
            <article class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">¿Se puede usar como software de cotización online?</button></h3><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#acordeonFaqSeo"><div class="accordion-body small">Sí, permite crear presupuestos, enviarlos y hacer seguimiento comercial.</div></div></article>
            <article class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">¿Cómo solicito una demo?</button></h3><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#acordeonFaqSeo"><div class="accordion-body small">Desde el formulario de contacto o el botón “Solicitar demo”.</div></div></article>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="h3">¿Listo para vender más con orden comercial?</h2>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar ahora</a>
            <a href="#planes" class="btn btn-outline-primary">Contratar plan</a>
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary">Solicitar demo</a>
        </div>
    </div>
</section>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2 w-100">
        <a href="#planes" class="btn btn-primary btn-sm flex-fill">Contratar plan</a>
        <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm flex-fill">Solicitar demo</a>
    </div>
</div>

<script>
(() => {
    var toggles = Array.prototype.slice.call(document.querySelectorAll('[data-bs-toggle="collapse"]'));
    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var selector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
            if (!selector) return;
            var panel = document.querySelector(selector);
            if (!panel) return;
            var parentSelector = panel.getAttribute('data-bs-parent');
            var willOpen = !panel.classList.contains('show');

            if (parentSelector) {
                var parent = document.querySelector(parentSelector);
                if (parent) {
                    parent.querySelectorAll('.accordion-collapse.show').forEach(function (openPanel) {
                        if (openPanel === panel) return;
                        openPanel.classList.remove('show');
                        var opener = parent.querySelector('[data-bs-target="#' + openPanel.id + '"]');
                        if (opener) {
                            opener.classList.add('collapsed');
                            opener.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            }

            panel.classList.toggle('show', willOpen);
            toggle.classList.toggle('collapsed', !willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    var slider = document.querySelector('[data-slider]');
    if (slider) {
        var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-slide]'));
        var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-slide-dot]'));
        var interval = Number(slider.getAttribute('data-slider-interval') || 3200);
        var actual = 0;
        var timer = null;

        var pintar = function (indice) {
            actual = (indice + slides.length) % slides.length;
            slides.forEach(function (slide, i) { slide.classList.toggle('is-active', i === actual); });
            dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === actual); });
        };

        var iniciar = function () {
            if (slides.length < 2) return;
            timer = window.setInterval(function () { pintar(actual + 1); }, interval);
        };

        var reiniciar = function () {
            if (timer) window.clearInterval(timer);
            iniciar();
        };

        var prev = slider.querySelector('[data-slide-nav="prev"]');
        var next = slider.querySelector('[data-slide-nav="next"]');
        if (prev) prev.addEventListener('click', function () { pintar(actual - 1); reiniciar(); });
        if (next) next.addEventListener('click', function () { pintar(actual + 1); reiniciar(); });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                pintar(Number(dot.getAttribute('data-slide-dot') || 0));
                reiniciar();
            });
        });

        slider.addEventListener('mouseenter', function () { if (timer) window.clearInterval(timer); });
        slider.addEventListener('mouseleave', reiniciar);
        pintar(0);
        iniciar();
    }

    var botones = document.querySelectorAll('[data-home-billing]');
    var precios = document.querySelectorAll('[data-home-precio]');
    var links = document.querySelectorAll('[data-home-link]');

    var aplicar = function (modalidad) {
        var tipo = modalidad === 'anual' ? 'anual' : 'mensual';

        botones.forEach(function (btn) {
            var activa = btn.getAttribute('data-home-billing') === tipo;
            btn.classList.toggle('btn-primary', activa);
            btn.classList.toggle('btn-outline-primary', !activa);
        });

        precios.forEach(function (precio) {
            var valorMensual = precio.getAttribute('data-precio-mensual') || '0';
            var valorAnual = precio.getAttribute('data-precio-anual') || valorMensual;
            var valor = tipo === 'anual' ? valorAnual : valorMensual;
            var etiqueta = tipo === 'anual' ? 'anual' : 'mensual';
            precio.innerHTML = '$' + valor + ' <small class="fs-6">/ ' + etiqueta + '</small>';
        });

        links.forEach(function (link) {
            var href = new URL(link.getAttribute('href'), window.location.origin);
            href.searchParams.set('frecuencia', tipo);
            link.setAttribute('href', href.pathname + href.search);
        });
    };

    botones.forEach(function (btn) {
        btn.addEventListener('click', function () { aplicar(btn.getAttribute('data-home-billing')); });
    });

    aplicar('mensual');
})();
</script>
