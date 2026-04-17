<?php
$capturasBase = '/img/Captura Sistema';
$capturaUrl = static function (string $archivo) use ($capturasBase): string {
    return url($capturasBase . '/' . rawurlencode($archivo));
};
$capturasOptimizadas = [
    'Dashboard - Inicio.png' => url('/imagen-opt/dashboard_inicio?w=1000&h=630&q=76'),
    'Punto de venta.png' => url('/imagen-opt/punto_venta?w=1000&h=630&q=76'),
    'Movimientos de inventario.png' => url('/imagen-opt/movimientos_inventario?w=1000&h=630&q=76'),
    'Clientes.png' => url('/imagen-opt/clientes?w=1000&h=630&q=76'),
];
$capturaLandingUrl = static function (string $archivo) use ($capturasOptimizadas, $capturaUrl): string {
    return $capturasOptimizadas[$archivo] ?? $capturaUrl($archivo);
};

$faqSeo = [
    [
        'pregunta' => '¿Qué es Vextra y para qué tipo de empresas en Chile sirve?',
        'respuesta' => 'Vextra es un software para pymes Chile que integra sistema de cotización, punto de venta Chile, control de inventario, catálogo online Chile y pagos en línea Chile en una sola plataforma.',
    ],
    [
        'pregunta' => '¿Vextra sirve como sistema de cotización para vender más?',
        'respuesta' => 'Sí. Permite crear cotizaciones profesionales, hacer seguimiento comercial y convertirlas en venta con inventario actualizado en tiempo real.',
    ],
    [
        'pregunta' => '¿El catálogo online y los pagos en línea están integrados?',
        'respuesta' => 'Sí. Publicas tu catálogo en línea con carrito y recibes pagos en línea integrados, todo conectado con stock, ventas y operación comercial.',
    ],
    [
        'pregunta' => '¿Qué diferencia hay entre usar Excel y Vextra?',
        'respuesta' => 'Con Excel existen errores manuales y datos desactualizados. Con Vextra tienes control total del negocio con procesos conectados y trazabilidad comercial.',
    ],
    [
        'pregunta' => '¿Cuál plan recomiendan para una pyme chilena?',
        'respuesta' => 'El Plan Profesional es el más vendido para pymes en crecimiento porque incorpora catálogo online y pagos en línea junto al sistema de ventas.',
    ],
];

$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static function (array $item): array {
        return [
            '@type' => 'Question',
            'name' => $item['pregunta'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['respuesta'],
            ],
        ];
    }, $faqSeo),
];
?>

<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'Vextra',
    'brand' => ['@type' => 'Brand', 'name' => 'Vextra'],
    'description' => 'Sistema de cotización, punto de venta Chile, control de inventario, catálogo online Chile y pagos en línea Chile para pymes.',
    'category' => 'SoftwareApplication',
    'url' => url('/'),
    'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'CLP'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<section class="hero py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary-emphasis mb-3">Software para pymes Chile</span>
                <h1 class="display-6 fw-bold mb-3">Vende más con tu catálogo online y pagos en línea, todo conectado a tu sistema de ventas</h1>
                <p class="lead text-secondary mb-3">Vextra reúne en un solo lugar tu <strong>sistema de cotización</strong>, <strong>punto de venta Chile</strong>, <strong>control de inventario</strong>, <strong>catálogo en línea con carrito</strong> y <strong>pagos en línea integrados</strong>.</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-primary btn-lg">Prueba gratis 30 días</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-primary btn-lg">Ver demo</a>
                </div>
                <h1 class="display-6 fw-bold mb-3">Sistema de cotizaciones y ventas con inventario para crecer con control real</h1>
                <p class="lead text-secondary">Vextra es un software de cotización online que conecta tu gestión comercial, sistema punto de venta y sistema de inventario para que tu empresa venda más, responda rápido y opere con orden administrativo.</p>
                <p class="mb-3">Ahora además puedes activar <strong>catálogo en línea con carrito de compras</strong> y <strong>pagos en línea integrados</strong> para vender 24/7 con una experiencia de compra más profesional para tus clientes.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar prueba gratis de 30 días</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-primary">Ver demo</a>
                </div>
                <p class="small text-secondary mt-3 mb-0">Ideal para empresas chilenas que necesitan cotizar, vender y controlar stock sin depender de planillas sueltas.</p>
            </div>
            <div class="col-lg-5">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">¿Qué puedes activar hoy?</h2>
                        <ul class="small ps-3 mb-0 d-grid gap-2">
                            <li>Publicar catálogo online Chile y vender 24/7.</li>
                            <li>Cobrar con pagos en línea Chile sin fricciones.</li>
                            <li>Emitir cotizaciones y cerrar más rápido.</li>
                            <li>Vender en caja con POS conectado a inventario.</li>
                            <li>Tomar decisiones con datos en tiempo real.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge text-bg-primary mb-2">NUEVO</span>
            <h2 class="h3 mb-2">Nuevas funcionalidades para vender más</h2>
            <p class="text-secondary mb-0">Más alcance comercial y cobro simple para pymes chilenas.</p>
        </div>
        <div class="row g-3">
            <article class="col-lg-6">
                <div class="card h-100 border-primary border-2">
                    <div class="card-body">
                        <h3 class="h5"><i class="bi bi-cart3 text-primary me-2"></i>Catálogo en línea</h3>
                        <p class="mb-2">Publica tus productos con carrito y convierte más visitas en ventas.</p>
                        <ul class="small mb-0 ps-3 d-grid gap-1">
                            <li>Comparte tu catálogo por WhatsApp, redes y web.</li>
                            <li>Muestra productos y precios de forma profesional.</li>
                            <li>Conecta ventas online con tu inventario real.</li>
                        </ul>
                    </div>
                </div>
            </article>
            <article class="col-lg-6">
                <div class="card h-100 border-success border-2">
                    <div class="card-body">
                        <h3 class="h5"><i class="bi bi-credit-card text-success me-2"></i>Pagos en línea</h3>
                        <p class="mb-2">Recibe pagos en línea integrados para cerrar más rápido y sin fricción.</p>
                        <ul class="small mb-0 ps-3 d-grid gap-1">
                            <li>Reduce abandono al momento de pagar.</li>
                            <li>Automatiza la confirmación de compra.</li>
                            <li>Mejora la experiencia de tu cliente final.</li>
                        </ul>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge text-bg-dark mb-2">NUEVO</span>
            <h2 class="h3 mb-2">Nuevas funcionalidades para vender más</h2>
            <p class="text-secondary mb-0">El principal gancho comercial de Vextra para empresas que quieren crecer sin complejidad técnica.</p>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <article class="card h-100 border-2 border-primary shadow-sm">
                    <div class="card-body">
                        <h3 class="h4 d-flex align-items-center gap-2"><i class="bi bi-cart3 text-primary"></i>Catálogo en línea con carrito</h3>
                        <p class="mb-2">Muestra tus productos de forma profesional y transforma visitas en pedidos reales.</p>
                        <ul class="small ps-3 mb-0 d-grid gap-1">
                            <li>Tu vitrina digital lista para compartir por WhatsApp y redes.</li>
                            <li>Carrito de compras para subir ticket promedio.</li>
                            <li>Productos, precios y stock conectados a tu operación.</li>
                        </ul>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="card h-100 border-2 border-success shadow-sm">
                    <div class="card-body">
                        <h3 class="h4 d-flex align-items-center gap-2"><i class="bi bi-credit-card text-success"></i>Pagos en línea integrados</h3>
                        <p class="mb-2">Recibe pagos rápido y mejora la experiencia de compra sin procesos manuales.</p>
                        <ul class="small ps-3 mb-0 d-grid gap-1">
                            <li>Cobro online simple para vender sin horarios.</li>
                            <li>Confirmación de pago conectada a ventas e inventario.</li>
                            <li>Menos fricción de cobro, más conversiones para tu pyme.</li>
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-2">Módulos principales de Vextra</h2>
            <p class="text-secondary mb-0">Todo lo que necesitas para ordenar tu negocio y acelerar ventas en Chile.</p>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <article class="card h-100 border-3 border-primary-subtle">
                    <div class="card-body">
                        <h3 class="h4"><i class="bi bi-shop me-2 text-primary"></i>Catálogo Online (Destacado)</h3>
                        <ul class="small ps-3 d-grid gap-1">
                            <li>Vitrina profesional para vender sin depender de marketplaces.</li>
                            <li>Carrito integrado para cerrar compras más rápido.</li>
                            <li>Actualización automática de productos y stock.</li>
                            <li>Más alcance digital para tu pyme chilena.</li>
                        </ul>
                        <p class="fw-semibold mb-0">Tu negocio abierto 24/7 para vender más todos los días.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="card h-100 border-3 border-success-subtle">
                    <div class="card-body">
                        <h3 class="h4"><i class="bi bi-cash-coin me-2 text-success"></i>Pagos Online (Destacado)</h3>
                        <ul class="small ps-3 d-grid gap-1">
                            <li>Cobro online directo desde catálogo y cotizaciones.</li>
                            <li>Menor abandono por fricción al momento de pagar.</li>
                            <li>Registro ordenado de pagos para control financiero.</li>
                            <li>Experiencia de compra confiable para tus clientes.</li>
                        </ul>
                        <p class="fw-semibold mb-0">Cobras mejor, más rápido y con control total del flujo comercial.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card h-100 bg-light-subtle border-0">
                    <div class="card-body">
                        <h3 class="h5"><i class="bi bi-receipt me-2"></i>Punto de Venta</h3>
                        <ul class="small ps-3 d-grid gap-1">
                            <li>Ventas ágiles en caja.</li>
                            <li>Registro por vendedor y sucursal.</li>
                            <li>Descuento automático de stock.</li>
                        </ul>
                        <p class="small fw-semibold mb-0">Vende rápido y con respaldo de información real.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card h-100 bg-light-subtle border-0">
                    <div class="card-body">
                        <h3 class="h5"><i class="bi bi-file-earmark-text me-2"></i>Cotizaciones</h3>
                        <ul class="small ps-3 d-grid gap-1">
                            <li>Propuestas claras y profesionales.</li>
                            <li>Seguimiento por estado comercial.</li>
                            <li>Mayor velocidad de respuesta.</li>
                        </ul>
                        <p class="small fw-semibold mb-0">Cierra más negocios con un sistema de cotización ordenado.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card h-100 bg-light-subtle border-0">
                    <div class="card-body">
                        <h3 class="h5"><i class="bi bi-box-seam me-2"></i>Inventario</h3>
                        <ul class="small ps-3 d-grid gap-1">
                            <li>Control por movimiento y producto.</li>
                            <li>Alertas para evitar quiebres.</li>
                            <li>Decisiones con datos actualizados.</li>
                        </ul>
                        <p class="small fw-semibold mb-0">Protege tus márgenes con control de inventario en tiempo real.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-2">Todo tu negocio en un solo sistema</h2>
            <p class="text-secondary mb-0">Deja atrás Excel y sistemas separados: en Vextra todo está conectado sin integraciones complejas.</p>
        </div>
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-6">
                <div class="card h-100 border-danger-subtle">
                    <div class="card-body">
                        <h3 class="h5 text-danger">Sin Vextra: Excel + herramientas sueltas</h3>
                        <ul class="small ps-3 mb-0 d-grid gap-1">
                            <li>Datos duplicados y errores manuales.</li>
                            <li>Ventas sin stock real.</li>
                            <li>Pérdida de tiempo entre plataformas.</li>
                        </ul>
                    </div>
                </div>
            </article>
            <article class="col-lg-6">
                <h2 class="h3">Características clave para gestión comercial moderna</h2>
                <h3 class="h5 mt-3">Cotizaciones y presupuestos profesionales</h3>
                <p>Plantillas claras, control de versiones, descuentos y condiciones comerciales para mantener coherencia entre vendedor, cliente y administración.</p>
                <h3 class="h5">Sistema punto de venta conectado</h3>
                <p>Ventas rápidas en caja, registro por vendedor y trazabilidad de transacciones con impacto automático en inventario.</p>
                <h3 class="h5">Inventario con movimientos y alertas</h3>
                <p>Recepciones, ajustes, control de quiebres y consulta por producto para evitar decisiones a ciegas.</p>
                <h3 class="h5 text-primary">Catálogo online con carrito (nuevo)</h3>
                <p>Publica tus productos en minutos y permite compras directas con una vitrina digital lista para vender en Chile.</p>
                <h3 class="h5 text-success">Pagos online integrados (nuevo)</h3>
                <p>Recibe pagos sin procesos manuales y conecta cada cobro con tu operación comercial e inventario.</p>
                <h3 class="h5">Gestión integral de clientes y oportunidades</h3>
                <p>Historial comercial por cliente, seguimiento de estado de cotización y foco en las oportunidades con mayor probabilidad de cierre.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="<?= e(url('/caracteristicas')) ?>" class="btn btn-outline-primary btn-sm">Ver características</a>
                    <a href="<?= e(url('/preguntas-frecuentes')) ?>" class="btn btn-outline-secondary btn-sm">Revisar preguntas frecuentes</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-2">Resultados que tu pyme puede lograr</h2>
            <p class="text-secondary mb-0">Beneficios directos para mejorar conversión, control y atención comercial.</p>
        </div>
        <div class="row g-3 text-center">
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><i class="bi bi-graph-up-arrow fs-3 text-success"></i><p class="fw-semibold mb-0 mt-2">Aumenta ventas</p></div></div></div>
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><i class="bi bi-shield-check fs-3 text-primary"></i><p class="fw-semibold mb-0 mt-2">Reduce pérdidas</p></div></div></div>
            <div class="col-md-4"><div class="card h-100"><div class="card-body"><i class="bi bi-speedometer2 fs-3 text-warning"></i><p class="fw-semibold mb-0 mt-2">Control en tiempo real</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><i class="bi bi-clock-history fs-3 text-info"></i><p class="fw-semibold mb-0 mt-2">Vende 24/7</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><i class="bi bi-emoji-smile fs-3 text-secondary"></i><p class="fw-semibold mb-0 mt-2">Mejora la atención</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white" id="planes">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Planes de software para pymes Chile</h2>
            <p class="text-secondary mb-0">Valores en CLP, sin decimales. El Plan Profesional es el más vendido.</p>
        </div>
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" aria-label="Seleccionar modalidad">
                <button type="button" class="btn btn-primary" data-home-billing="mensual">Mensual</button>
                <button type="button" class="btn btn-outline-primary" data-home-billing="anual">Anual (Ahorra hasta 10%)</button>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            <?php foreach ($planes as $index => $plan): ?>
                <?php
                    $nombresPlanes = ['Plan Básico', 'Plan Profesional', 'Plan Empresa'];
                    $nombreVisual = $nombresPlanes[$index] ?? $plan['nombre'];
                    $esProfesional = $index === 1;
                ?>
                <div class="col-12 col-lg-4">
                    <article class="card h-100 border-2 <?= $esProfesional ? 'border-primary border-3 shadow' : '' ?>" style="border-color: <?= e($plan['color_visual'] ?: '#dce3eb') ?> !important;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php if ($esRecomendado): ?><span class="badge text-bg-primary"><?= $index === 1 ? 'MÁS VENDIDO' : 'MÁS CONVENIENTE' ?></span><?php endif; ?>
                                <?php if (!empty($plan['destacado'])): ?><span class="badge text-bg-success">DESTACADO</span><?php endif; ?>
                            </div>
                            <h3 class="h5"><?= e($nombreVisual) ?></h3>
                            <p class="text-secondary small mb-2"><?= e($plan['resumen_comercial'] ?: $plan['descripcion_comercial']) ?></p>
                            <div class="h3 mb-0" data-home-precio data-precio-mensual="<?= e(number_format((float) $plan['precio_mensual'], 0, ',', '.')) ?>" data-precio-anual="<?= e(number_format((float) $plan['precio_anual'], 0, ',', '.')) ?>">$<?= number_format((float) $plan['precio_mensual'], 0, ',', '.') ?> <small class="fs-6">/ mensual</small></div>
                            <p class="small text-secondary"><?= e((string) $plan['descuento_anual_pct']) ?>% descuento anual</p>
                            <ul class="small ps-3 d-grid gap-1 mb-3">
                                <?php foreach ($plan['funcionalidades'] as $funcionalidad): ?>
                                    <li><?= e($funcionalidad['descripcion'] ?: $funcionalidad['nombre']) ?></li>
                                <?php endforeach; ?>
                                <?php if ($index === 1): ?>
                                    <li><strong>Catálogo online con carrito</strong></li>
                                    <li><strong>Pagos online integrados</strong></li>
                                <?php endif; ?>
                            </ul>
                            <div class="d-grid gap-2 mt-auto">
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-outline-primary btn-sm" data-home-link>Prueba gratis 30 días</a>
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-primary btn-sm" data-home-link>Contratar plan</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 border-bottom" id="faq">
    <div class="container">
        <h2 class="h3 mb-2">Preguntas frecuentes sobre sistema de cotización, punto de venta e inventario</h2>
        <p class="text-secondary">Respuestas claras para evaluar un software para pymes Chile con catálogo online Chile y pagos en línea Chile.</p>
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

<section class="py-5 bg-light-subtle">
    <div class="container text-center">
        <h2 class="h3 mb-2">Empieza hoy y lleva el control total de tu negocio</h2>
        <p class="text-secondary">Activa tu software para pymes Chile y comienza a vender más con una operación conectada.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary btn-lg">Prueba gratis</a>
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-primary btn-lg">Contacto</a>
        </div>
    </div>
</section>

<script>
(() => {
    const botones = document.querySelectorAll('[data-home-billing]');
    const precios = document.querySelectorAll('[data-home-precio]');
    const links = document.querySelectorAll('[data-home-link]');

    const aplicar = (modalidad) => {
        const tipo = modalidad === 'anual' ? 'anual' : 'mensual';
        botones.forEach((btn) => {
            const activa = btn.getAttribute('data-home-billing') === tipo;
            btn.classList.toggle('btn-primary', activa);
            btn.classList.toggle('btn-outline-primary', !activa);
        });

        precios.forEach((precio) => {
            const valorMensual = precio.getAttribute('data-precio-mensual') || '0';
            const valorAnual = precio.getAttribute('data-precio-anual') || valorMensual;
            const valor = tipo === 'anual' ? valorAnual : valorMensual;
            const etiqueta = tipo === 'anual' ? 'anual' : 'mensual';
            precio.textContent = '$' + valor + ' / ' + etiqueta;
        });

        links.forEach((link) => {
            const href = new URL(link.getAttribute('href'), window.location.origin);
            href.searchParams.set('frecuencia', tipo);
            link.setAttribute('href', href.pathname + href.search);
        });
    };

    botones.forEach((btn) => {
        btn.addEventListener('click', () => aplicar(btn.getAttribute('data-home-billing')));
    });

    aplicar('mensual');
})();
</script>
