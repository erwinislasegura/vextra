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
    'Cotizaciones 1.png' => url('/imagen-opt/cotizaciones_1?w=1280&h=800&q=76'),
    'Cotizaciones 2.png' => url('/imagen-opt/cotizaciones_2?w=1280&h=800&q=76'),
    'Cotizaciones 3.png' => url('/imagen-opt/cotizaciones_3?w=1280&h=800&q=76'),
    'Cotizaciones 4.png' => url('/imagen-opt/cotizaciones_4?w=1280&h=800&q=76'),
    'Cotizaciones 5.png' => url('/imagen-opt/cotizaciones_5?w=1280&h=800&q=76'),
];
$capturaLandingUrl = static function (string $archivo) use ($capturasOptimizadas, $capturaUrl): string {
    return $capturasOptimizadas[$archivo] ?? $capturaUrl($archivo);
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
        'respuesta' => 'Puedes comenzar con una prueba gratis de 30 días y luego contratar el plan que mejor se ajuste a tu operación, cantidad de usuarios y volumen de ventas.',
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
                <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-success fs-6 px-3 py-2">🎁 30 días de prueba gratis!!</span>
                    <span class="small fw-semibold text-success">Empieza hoy y el primer cobro se realiza al terminar tu prueba.</span>
                </div>
                <h1 class="display-6 fw-bold mb-3">Sistema de cotizaciones y ventas con inventario para crecer con control real</h1>
                <p class="lead text-secondary">Vextra es un software de cotización online que conecta tu gestión comercial, sistema punto de venta y sistema de inventario para que tu empresa venda más, responda rápido y opere con orden administrativo.</p>
                <p class="mb-3">Ahora además puedes activar <strong>catálogo en línea con carrito de compras</strong> y <strong>pagos en línea integrados</strong> para vender 24/7 con una experiencia de compra más profesional para tus clientes.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar prueba gratis de 30 días</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-primary">Contáctanos</a>
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
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-primary text-white">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="badge text-bg-light text-primary mb-2">NUEVAS FUNCIONES 2026</span>
                <h2 class="h2 mb-2">Catálogo en línea + pago en línea para vender 24/7</h2>
                <p class="mb-3 text-white-50">Esta es la nueva sección destacada solicitada: publica productos con carrito, comparte tu catálogo por WhatsApp/redes y cobra online en el momento para cerrar más ventas sin fricción.</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill text-bg-light text-primary px-3 py-2">🛒 Catálogo en línea con carrito</span>
                    <span class="badge rounded-pill text-bg-light text-success px-3 py-2">💳 Pago en línea integrado</span>
                    <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">🚀 Herramienta poderosa para vender 24/7</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-grid gap-2">
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-light btn-lg text-primary fw-semibold">Activar prueba gratis</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-light">Hablar con ventas</a>
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
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <span class="badge text-bg-dark mb-2">CTA DESTACADO</span>
                        <h2 class="h3 mb-2">Catálogo en línea + pago en línea: tu herramienta poderosa para vender 24/7</h2>
                        <p class="text-secondary mb-3">Activa una vitrina digital profesional, recibe pedidos en cualquier horario y cobra en el momento con una experiencia de compra simple para tus clientes.</p>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge rounded-pill text-bg-primary px-3 py-2">🛒 Catálogo online con carrito</span>
                            <span class="badge rounded-pill text-bg-success px-3 py-2">💳 Pago en línea integrado</span>
                            <span class="badge rounded-pill text-bg-secondary px-3 py-2">⚡ Operación comercial conectada</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-2">
                            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Quiero vender 24/7</a>
                            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-primary">Hablar con un asesor</a>
                            <a href="#planes" class="btn btn-link text-decoration-none">Ver planes disponibles</a>
                        </div>
                    </div>
                </div>
            </div>
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

<section class="py-5 border-bottom">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3">Flujo real de software de cotización online para equipos comerciales</h2>
            <p class="text-secondary mb-0">Desde crear presupuesto hasta cerrar venta con seguimiento: todo en un solo entorno de trabajo.</p>
        </div>
        <div class="landing-slider" data-slider data-slider-interval="3200">
            <?php foreach ($cotizacionesCapturas as $index => $captura): ?>
                <article class="landing-slide <?= $index === 0 ? 'is-active' : '' ?>" data-slide>
                    <img src="<?= e($capturaLandingUrl($captura['archivo'])) ?>" alt="<?= e($captura['titulo']) ?>" loading="lazy" decoding="async" width="1280" height="800">
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
                                <span class="fs-4 text-primary" aria-hidden="true">⚡</span>
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
                                <span class="fs-4 text-success" aria-hidden="true">📈</span>
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
                                <span class="fs-4 text-warning" aria-hidden="true">📦</span>
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
                                <?php if ($esRecomendado): ?><span class="badge text-bg-primary"><?= $index === 1 ? 'MÁS VENDIDO' : 'MÁS CONVENIENTE' ?></span><?php endif; ?>
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
                                <?php if ($index === 1): ?>
                                    <li><strong>Catálogo online con carrito</strong></li>
                                    <li><strong>Pagos online integrados</strong></li>
                                <?php endif; ?>
                            </ul>
                            <div class="d-grid gap-2 mt-auto">
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-outline-primary btn-sm" data-home-link>Comenzar ahora</a>
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-primary btn-sm" data-home-link>Contratar plan</a>
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
                <span class="landing-timeline__icon" aria-hidden="true">🚚</span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 1</span>
                    <h3 class="h6 mb-1">Distribuidoras</h3>
                    <p class="small mb-0">Cotizan por volumen, controlan stock por rotación y despachan con mejor promesa comercial.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true">🏬</span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 2</span>
                    <h3 class="h6 mb-1">Retail especializado</h3>
                    <p class="small mb-0">Unifican POS, catálogo y reposición para vender más sin romper experiencia de cliente.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true">🛠️</span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 3</span>
                    <h3 class="h6 mb-1">Servicios técnicos</h3>
                    <p class="small mb-0">Generan presupuestos con trazabilidad y convierten propuestas en órdenes de trabajo y venta.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true">📈</span>
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
        <p class="text-secondary">Elige tu siguiente paso: comenzar ahora o contratar un plan para tu empresa.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary">Comenzar ahora</a>
            <a href="#planes" class="btn btn-outline-primary">Contratar plan</a>
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
    </div>
</div>

<script>
(() => {
    const slider = document.querySelector('[data-slider]');
    if (slider) {
        const slides = Array.from(slider.querySelectorAll('[data-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-slide-dot]'));
        const interval = Number(slider.getAttribute('data-slider-interval') || 3200);
        const prefiereReducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const esPantallaTactil = window.matchMedia('(pointer: coarse)').matches;
        const autoplayPermitido = !prefiereReducirMovimiento && !esPantallaTactil;
        let actual = 0;
        let timer = null;

        const pintar = (indice) => {
            actual = (indice + slides.length) % slides.length;
            window.requestAnimationFrame(() => {
                slides.forEach((slide, i) => slide.classList.toggle('is-active', i === actual));
                dots.forEach((dot, i) => dot.classList.toggle('is-active', i === actual));
            });
        };
        const detener = () => {
            if (!timer) return;
            window.clearInterval(timer);
            timer = null;
        };
        const iniciar = () => {
            if (!autoplayPermitido || slides.length < 2 || document.hidden) return;
            detener();
            timer = window.setInterval(() => pintar(actual + 1), interval);
        };
        const reiniciar = () => {
            detener();
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
        slider.addEventListener('mouseenter', detener);
        slider.addEventListener('mouseleave', reiniciar);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                detener();
                return;
            }
            iniciar();
        });
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
