<?php
$capturasBase = '/img/Captura Sistema';
$capturaUrl = static function (string $archivo) use ($capturasBase): string {
    return url($capturasBase . '/' . rawurlencode($archivo));
};
$cotizacionesCapturas = [
    [
        'archivo' => 'Cotizaciones 1.png',
        'titulo' => 'Panel de sistema de cotizaciones con estado comercial',
        'descripcion' => 'Visualiza en segundos qué presupuestos están pendientes, aprobados o en seguimiento para priorizar acciones del equipo comercial.',
    ],
    [
        'archivo' => 'Cotizaciones 2.png',
        'titulo' => 'Editor de presupuesto online con control de márgenes',
        'descripcion' => 'Ajusta productos, descuentos y condiciones sin rehacer documentos, manteniendo trazabilidad para ventas y administración.',
    ],
    [
        'archivo' => 'Cotizaciones 3.png',
        'titulo' => 'Cotización profesional lista para enviar al cliente',
        'descripcion' => 'Entrega propuestas claras para acelerar aprobación y aumentar cierres en tu software de cotización online.',
    ],
    [
        'archivo' => 'Cotizaciones 4.png',
        'titulo' => 'Seguimiento comercial por etapa y vendedor',
        'descripcion' => 'Controla cada oportunidad para evitar negocios fríos y mejorar la tasa de conversión mensual.',
    ],
    [
        'archivo' => 'Cotizaciones 5.png',
        'titulo' => 'Decisiones con contexto completo del cliente',
        'descripcion' => 'Integra historial, stock y avance comercial para cerrar ventas con mejor información y menos errores.',
    ],
];

$faqSeo = [
    [
        'pregunta' => '¿Qué es Vextra y para qué tipo de empresas en Chile sirve?',
        'respuesta' => 'Vextra es un software para empresas Chile que integra sistema de cotizaciones, sistema punto de venta e inventario en una sola plataforma. Es útil para pymes comerciales, distribuidoras, retail especializado, servicios técnicos y negocios que necesitan control de ventas con stock en tiempo real.',
    ],
    [
        'pregunta' => '¿Vextra funciona como software de cotización online?',
        'respuesta' => 'Sí. Puedes crear presupuestos online, enviarlos de forma profesional, hacer seguimiento por etapa y convertirlos en venta sin salir de la plataforma. Eso permite responder más rápido y subir la tasa de cierre.',
    ],
    [
        'pregunta' => '¿El sistema punto de venta está conectado al inventario?',
        'respuesta' => 'Sí. Cada venta en POS descuenta stock automáticamente, evitando sobreventa y quiebres de inventario. También puedes revisar movimientos, recepciones y ajustes para mantener control total.',
    ],
    [
        'pregunta' => '¿Puedo controlar inventario por producto y movimiento?',
        'respuesta' => 'Sí. El sistema de inventario permite ver entradas, salidas, ajustes, recepción de compras y alertas de stock crítico. Esto mejora la reposición y protege márgenes.',
    ],
    [
        'pregunta' => '¿Cómo ayuda Vextra a aumentar ventas?',
        'respuesta' => 'Al centralizar cotizar, vender y controlar stock en una misma herramienta, el equipo comercial reduce tiempos de respuesta, evita errores de precio y mejora el seguimiento de oportunidades, lo que impacta directamente en conversiones.',
    ],
    [
        'pregunta' => '¿Qué diferencia hay entre una planilla y un sistema de ventas con inventario?',
        'respuesta' => 'Con planillas hay riesgo de duplicidad, información desactualizada y poco seguimiento. Con un sistema de ventas con inventario tienes trazabilidad, automatización y datos en tiempo real para tomar decisiones operativas y comerciales.',
    ],
    [
        'pregunta' => '¿Incluye gestión comercial además de cotizaciones?',
        'respuesta' => 'Sí. Incluye seguimiento de oportunidades, control por vendedor, historial por cliente y herramientas para ordenar el pipeline comercial y mejorar cierre de negocios.',
    ],
    [
        'pregunta' => '¿Cómo puedo comenzar a usar Vextra en mi empresa?',
        'respuesta' => 'Puedes solicitar una demo guiada y luego contratar el plan que mejor se ajuste a tu operación, cantidad de usuarios y volumen de ventas.',
    ],
    [
        'pregunta' => '¿Cuál plan recomiendan para una pyme que quiere ordenarse y crecer?',
        'respuesta' => 'Normalmente el plan intermedio es el más conveniente para empresas en crecimiento, porque equilibra funcionalidades comerciales, control de stock y costo mensual.',
    ],
    [
        'pregunta' => '¿Se puede implementar Vextra sin frenar la operación diaria?',
        'respuesta' => 'Sí. La implementación está pensada por etapas, con acompañamiento comercial y técnico, para migrar procesos sin detener ventas ni atención de clientes.',
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
    'description' => 'Sistema de cotizaciones, punto de venta e inventario para empresas en Chile.',
    'category' => 'SoftwareApplication',
    'url' => url('/'),
    'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'CLP'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<section class="hero py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary-emphasis mb-2">Software para empresas Chile: cotización + POS + inventario</span>
                <h1 class="display-6 fw-bold mb-3">Sistema de cotizaciones y ventas con inventario para crecer con control real</h1>
                <p class="lead text-secondary">Vextra es un software de cotización online que conecta tu gestión comercial, sistema punto de venta y sistema de inventario para que tu empresa venda más, responda rápido y opere con orden administrativo.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar ahora</a>
                    <a href="#planes" class="btn btn-outline-primary">Contratar plan</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary">Solicitar demo</a>
                </div>
                <p class="small text-secondary mt-3 mb-0">Ideal para empresas chilenas que necesitan cotizar, vender y controlar stock sin depender de planillas sueltas.</p>
            </div>
            <aside class="col-lg-5">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h2 class="h5">¿Qué ganas al centralizar tu operación?</h2>
                        <ul class="small mb-0 ps-3 d-grid gap-2">
                            <li>Menos tiempo administrativo por cada cotización y venta.</li>
                            <li>Control de stock en tiempo real para evitar quiebres y sobreventa.</li>
                            <li>Seguimiento comercial para convertir más oportunidades.</li>
                            <li>Datos unificados para decidir precios, reposición y foco comercial.</li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-lg-6">
                <h2 class="h3 mb-3">Solución completa: sistema de cotización, POS y gestión de inventario</h2>
                <p>Cuando una empresa usa herramientas separadas para cotizar, facturar y mover stock, aparecen errores de precio, ventas sin disponibilidad y retrasos de respuesta. Vextra resuelve ese problema con una plataforma unificada de gestión comercial. El vendedor cotiza con datos actuales, el área administrativa valida márgenes y el equipo operativo ejecuta con trazabilidad.</p>
                <p class="mb-0">Este enfoque permite pasar de una operación reactiva a una gestión profesional: cada presupuesto tiene contexto, cada venta impacta inventario y cada decisión se toma con información real del negocio.</p>
            </div>
            <div class="col-12 col-lg-6">
                <div class="row g-3">
                    <div class="col-6"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Dashboard - Inicio.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Dashboard ejecutivo"><img src="<?= e($capturaUrl('Dashboard - Inicio.png')) ?>" alt="Dashboard del software para empresas" loading="lazy"></a><figcaption>Dashboard ejecutivo</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Punto de venta.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Sistema punto de venta"><img src="<?= e($capturaUrl('Punto de venta.png')) ?>" alt="Sistema punto de venta conectado" loading="lazy"></a><figcaption>POS conectado</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Movimientos de inventario.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Sistema de inventario"><img src="<?= e($capturaUrl('Movimientos de inventario.png')) ?>" alt="Sistema de inventario en tiempo real" loading="lazy"></a><figcaption>Inventario en tiempo real</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Clientes.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Gestión de clientes"><img src="<?= e($capturaUrl('Clientes.png')) ?>" alt="Gestión comercial por cliente" loading="lazy"></a><figcaption>Gestión de clientes</figcaption></figure></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Flujo real de software de cotización online para equipos comerciales</h2>
            <p class="text-secondary mb-0">Desde crear presupuesto hasta cerrar venta con seguimiento: todo en un solo entorno de trabajo.</p>
        </div>
        <div class="landing-slider" data-slider data-slider-interval="3200">
            <?php foreach ($cotizacionesCapturas as $index => $captura): ?>
                <article class="landing-slide <?= $index === 0 ? 'is-active' : '' ?>" data-slide>
                    <img src="<?= e($capturaUrl($captura['archivo'])) ?>" alt="<?= e($captura['titulo']) ?>" loading="lazy">
                    <div class="landing-carousel-caption">
                        <h3 class="h6 mb-1"><?= e($captura['titulo']) ?></h3>
                        <p class="small mb-0"><?= e($captura['descripcion']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
            <button class="landing-slider-control prev" type="button" data-slide-nav="prev" aria-label="Imagen anterior">‹</button>
            <button class="landing-slider-control next" type="button" data-slide-nav="next" aria-label="Imagen siguiente">›</button>
            <div class="landing-slider-dots" role="tablist" aria-label="Navegación de capturas">
                <?php foreach ($cotizacionesCapturas as $index => $captura): ?>
                    <button type="button" class="<?= $index === 0 ? 'is-active' : '' ?>" data-slide-dot="<?= $index ?>" aria-label="Ver captura <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="row g-4">
            <article class="col-lg-6">
                <h2 class="h3">Beneficios comerciales que impactan resultados</h2>
                <p class="text-secondary mb-3">Una vista rápida de impacto real para que tu equipo comercial y operativo trabaje con más velocidad y menos fricción.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card h-100 border-0 bg-light-subtle">
                            <div class="card-body d-flex gap-3 align-items-start">
                                <span class="fs-4 text-primary" aria-hidden="true"><i class="bi bi-lightning-charge-fill"></i></span>
                                <div>
                                    <h3 class="h5 mb-1">Ahorro de tiempo en cotizar y vender</h3>
                                    <p class="mb-0 small">Evita rehacer propuestas y reduce búsquedas manuales. Tu equipo responde con precios y stock actualizados en minutos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100 border-0 bg-light-subtle">
                            <div class="card-body d-flex gap-3 align-items-start">
                                <span class="fs-4 text-success" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                                <div>
                                    <h3 class="h5 mb-1">Aumento de ventas con seguimiento ordenado</h3>
                                    <p class="mb-0 small">Con pipeline y trazabilidad por cliente, se priorizan oportunidades con mejor cierre y se evita perder negocios por falta de seguimiento.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100 border-0 bg-light-subtle">
                            <div class="card-body d-flex gap-3 align-items-start">
                                <span class="fs-4 text-warning" aria-hidden="true"><i class="bi bi-box-seam-fill"></i></span>
                                <div>
                                    <h3 class="h5 mb-1">Control de stock para proteger márgenes</h3>
                                    <p class="mb-0 small">Cada venta impacta inventario en tiempo real, reduciendo sobreventa, compras urgentes y pérdidas por descoordinación operativa.</p>
                                </div>
                            </div>
                        </div>
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
                <h3 class="h5">Gestión integral de clientes y oportunidades</h3>
                <p>Historial comercial por cliente, seguimiento de estado de cotización y foco en las oportunidades con mayor probabilidad de cierre.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="<?= e(url('/caracteristicas')) ?>" class="btn btn-outline-primary btn-sm">Ver características</a>
                    <a href="<?= e(url('/preguntas-frecuentes')) ?>" class="btn btn-outline-secondary btn-sm">Revisar preguntas frecuentes</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white" id="planes">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Planes de software para empresas en Chile</h2>
            <p class="text-secondary mb-0">Compara modalidad mensual o anual. Recomendamos el plan intermedio para empresas en crecimiento comercial.</p>
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
                                <?php if (!empty($plan['destacado'])): ?><span class="badge text-bg-success">DESTACADO</span><?php endif; ?>
                            </div>
                            <h3 class="h5"><?= e($plan['nombre']) ?></h3>
                            <p class="text-secondary small"><?= e($plan['resumen_comercial'] ?: $plan['descripcion_comercial']) ?></p>
                            <div class="h3 mb-0" data-home-precio data-precio-mensual="<?= e(number_format((float) $plan['precio_mensual'], 0, ',', '.')) ?>" data-precio-anual="<?= e(number_format((float) $plan['precio_anual'], 0, ',', '.')) ?>">$<?= number_format((float) $plan['precio_mensual'], 0, ',', '.') ?> <small class="fs-6">/ mensual</small></div>
                            <p class="small text-secondary"><?= e((string) $plan['descuento_anual_pct']) ?>% descuento anual</p>
                            <ul class="small ps-3 d-grid gap-1">
                                <?php foreach ($plan['funcionalidades'] as $funcionalidad): ?>
                                    <li><?= e($funcionalidad['descripcion'] ?: $funcionalidad['nombre']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-grid gap-2 mt-auto">
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-outline-primary btn-sm" data-home-link>Comenzar ahora</a>
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-primary btn-sm" data-home-link>Contratar plan</a>
                                <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Solicitar demo</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-2">Casos de uso por tipo de empresa</h2>
            <p class="text-secondary mb-1">Cada negocio tiene desafíos distintos. Esta línea de tiempo muestra cómo Vextra acompaña tu crecimiento con foco en resultados reales.</p>
            <p class="small text-secondary mb-0">Elige el plan que mejor calce con tu etapa y transforma tu operación comercial en una experiencia profesional para tus clientes.</p>
        </div>
        <div class="landing-timeline" aria-label="Línea de tiempo de casos de uso empresariales">
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-truck"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 1</span>
                    <h3 class="h6 mb-1">Distribuidoras</h3>
                    <p class="small mb-0">Cotizan por volumen, controlan stock por rotación y despachan con mejor promesa comercial.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-shop-window"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 2</span>
                    <h3 class="h6 mb-1">Retail especializado</h3>
                    <p class="small mb-0">Unifican POS, catálogo y reposición para vender más sin romper experiencia de cliente.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-tools"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 3</span>
                    <h3 class="h6 mb-1">Servicios técnicos</h3>
                    <p class="small mb-0">Generan presupuestos con trazabilidad y convierten propuestas en órdenes de trabajo y venta.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 4</span>
                    <h3 class="h6 mb-1">Pymes en expansión</h3>
                    <p class="small mb-0">Ordenan su administración comercial con datos en tiempo real y menos dependencia de Excel.</p>
                </div>
            </article>
        </div>
        <div class="text-center mt-4">
            <a href="#planes" class="btn btn-primary btn-sm">Ver plan recomendado para mi empresa</a>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-white">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-6">
                <h2 class="h3 mb-3">Resumen ejecutivo para evaluar la solución</h2>
                <p class="mb-2">Si hoy tu empresa cotiza en una herramienta, vende en otra y controla stock en planillas, lo normal es perder tiempo, cometer errores y tener menos visibilidad comercial.</p>
                <p class="mb-0">Con Vextra puedes integrar cotizaciones, punto de venta e inventario en un flujo único. Si quieres el detalle completo por módulo, lo encontrarás en las secciones especializadas del sitio.</p>
            </div>
            <div class="col-lg-6">
                <div class="accordion" id="acordeonResumenInicio">
                    <article class="accordion-item">
                        <h3 class="accordion-header" id="resumenHeading1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#resumenCollapse1" aria-expanded="false" aria-controls="resumenCollapse1">
                                Ver detalle: impacto en rentabilidad y control
                            </button>
                        </h3>
                        <div id="resumenCollapse1" class="accordion-collapse collapse" aria-labelledby="resumenHeading1" data-bs-parent="#acordeonResumenInicio">
                            <div class="accordion-body small">
                                Ordena el trabajo comercial, mejora tiempos de respuesta y evita sobreventa gracias a una operación conectada entre cotización, POS e inventario.
                            </div>
                        </div>
                    </article>
                    <article class="accordion-item">
                        <h3 class="accordion-header" id="resumenHeading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#resumenCollapse2" aria-expanded="false" aria-controls="resumenCollapse2">
                                Ver detalle: pasos de implementación
                            </button>
                        </h3>
                        <div id="resumenCollapse2" class="accordion-collapse collapse" aria-labelledby="resumenHeading2" data-bs-parent="#acordeonResumenInicio">
                            <div class="accordion-body small">
                                La implementación se realiza por etapas: diagnóstico de proceso, configuración de flujo comercial e integración operativa para comenzar rápido y con orden.
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

<section class="py-5">
    <div class="container text-center">
        <h2 class="h3">¿Listo para vender más con orden comercial y control de stock?</h2>
        <p class="text-secondary">Elige tu siguiente paso: comenzar ahora, contratar un plan o solicitar una demo con asesoría para tu empresa.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar ahora</a>
            <a href="#planes" class="btn btn-outline-primary">Contratar plan</a>
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary">Solicitar demo</a>
        </div>
    </div>
</section>

<div class="captura-preview" id="previewCapturaLanding" hidden>
    <div class="captura-preview__backdrop" data-preview-close></div>
    <div class="captura-preview__dialog" role="dialog" aria-modal="true" aria-label="Vista previa de captura">
        <button type="button" class="captura-preview__close" data-preview-close aria-label="Cerrar vista previa">×</button>
        <h2 class="h6 mb-2" data-captura-modal-title>Vista de módulo</h2>
        <img src="" alt="" class="img-fluid w-100 rounded" data-captura-modal-image>
    </div>
</div>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2 w-100">
        <a href="#planes" class="btn btn-primary btn-sm flex-fill">Contratar plan</a>
        <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm flex-fill">Solicitar demo</a>
    </div>
</div>

<script>
(() => {
    const slider = document.querySelector('[data-slider]');
    if (slider) {
        const slides = Array.from(slider.querySelectorAll('[data-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-slide-dot]'));
        const interval = Number(slider.getAttribute('data-slider-interval') || 3200);
        let actual = 0;
        let timer = null;

        const pintar = (indice) => {
            actual = (indice + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === actual));
            dots.forEach((dot, i) => dot.classList.toggle('is-active', i === actual));
        };
        const iniciar = () => {
            if (slides.length < 2) return;
            timer = window.setInterval(() => pintar(actual + 1), interval);
        };
        const reiniciar = () => {
            if (timer) window.clearInterval(timer);
            iniciar();
        };

        slider.querySelector('[data-slide-nav="prev"]')?.addEventListener('click', () => { pintar(actual - 1); reiniciar(); });
        slider.querySelector('[data-slide-nav="next"]')?.addEventListener('click', () => { pintar(actual + 1); reiniciar(); });
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                pintar(Number(dot.getAttribute('data-slide-dot') || 0));
                reiniciar();
            });
        });
        slider.addEventListener('mouseenter', () => timer && window.clearInterval(timer));
        slider.addEventListener('mouseleave', reiniciar);
        pintar(0);
        iniciar();
    }

    const modalEl = document.getElementById('previewCapturaLanding');
    const modalImg = modalEl?.querySelector('[data-captura-modal-image]');
    const modalTitle = modalEl?.querySelector('[data-captura-modal-title]');
    const cerrarModal = () => {
        if (!modalEl) return;
        modalEl.hidden = true;
        document.body.classList.remove('preview-open');
    };

    document.querySelectorAll('.js-captura-ampliable').forEach((enlace) => {
        enlace.addEventListener('click', (evento) => {
            if (!modalEl || !modalImg || !modalTitle) return;
            evento.preventDefault();
            const src = enlace.getAttribute('href') || '';
            const title = enlace.getAttribute('data-captura-title') || 'Vista de módulo';
            modalImg.src = src;
            modalImg.alt = title;
            modalTitle.textContent = title;
            modalEl.hidden = false;
            document.body.classList.add('preview-open');
        });
    });
    modalEl?.querySelectorAll('[data-preview-close]').forEach((cerrar) => cerrar.addEventListener('click', cerrarModal));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') cerrarModal();
    });

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
            precio.innerHTML = '$' + valor + ' <small class="fs-6">/ ' + etiqueta + '</small>';
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
