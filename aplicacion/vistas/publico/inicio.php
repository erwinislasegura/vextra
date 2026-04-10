<?php
$capturasBase = url('/img/Captura%20Sistema');
$cotizacionesCapturas = [
    [
        'archivo' => 'Cotizaciones%201.png',
        'titulo' => 'Listado de cotizaciones con estado comercial',
        'descripcion' => 'Visualiza en segundos qué propuestas están pendientes, aprobadas o en seguimiento para priorizar acciones del equipo de ventas.'
    ],
    [
        'archivo' => 'Cotizaciones%202.png',
        'titulo' => 'Edición detallada de propuestas',
        'descripcion' => 'Modifica ítems, descuentos y condiciones sin rehacer documentos, manteniendo consistencia comercial y trazabilidad.'
    ],
    [
        'archivo' => 'Cotizaciones%203.png',
        'titulo' => 'Documentos listos para enviar al cliente',
        'descripcion' => 'Genera cotizaciones claras y profesionales para acelerar la respuesta y mejorar la percepción de marca.'
    ],
    [
        'archivo' => 'Cotizaciones%204.png',
        'titulo' => 'Seguimiento de oportunidades',
        'descripcion' => 'Controla avances por etapa comercial para evitar oportunidades frías y aumentar la tasa de cierre.'
    ],
    [
        'archivo' => 'Cotizaciones%205.png',
        'titulo' => 'Control avanzado para cierres',
        'descripcion' => 'Integra seguimiento, aprobaciones y contexto del cliente para tomar decisiones de cierre con mejor información.'
    ],
];
?>

<section class="hero py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-info-subtle text-info-emphasis mb-2">Sistema de gestión comercial con POS + inventario</span>
                <h1 class="display-6 fw-bold">No es solo para cotizar: es para vender más, trabajar con orden y decidir con datos reales</h1>
                <p class="lead text-secondary">Centraliza cotizaciones, ventas e inventario en un solo sistema para evitar errores, mejorar el control diario y crecer con una operación profesional.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#planes" class="btn btn-primary btn-sm">Ver planes</a>
                    <a href="<?= e(url('/registro')) ?>" class="btn btn-outline-primary btn-sm">Comenzar ahora</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Hablar con ventas</a>
                </div>
                <p class="small text-secondary mt-3 mb-0">Cuando cotizaciones, POS e inventario están conectados, tu negocio gana velocidad, control y claridad para crecer sin improvisar.</p>
            </div>
            <div class="col-lg-5">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h2 class="h6 mb-3">Impacto operativo en el día a día</h2>
                        <ul class="small mb-0 ps-3 d-grid gap-2">
                            <li>Sin control de stock pierdes ventas y credibilidad frente al cliente.</li>
                            <li>Sin sistema aumentan errores en precios, productos y procesos.</li>
                            <li>Sin seguimiento comercial se enfrían oportunidades y se pierden cierres.</li>
                            <li>Sin datos no puedes detectar qué vender más, ni dónde ajustar.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-lg-6">
                <span class="badge bg-primary-subtle text-primary-emphasis mb-2">Vista real del sistema</span>
                <h2 class="h4 mb-2">Una plataforma que se ve tan profesional como tu operación</h2>
                <p class="text-secondary mb-0">Estas pantallas reflejan cómo Vextra conecta gestión comercial, inventario y ventas en una experiencia ordenada, limpia y enfocada en productividad.</p>
            </div>
            <div class="col-12 col-lg-6">
                <div class="row g-3">
                    <div class="col-6"><figure class="landing-shot-card mb-0"><img src="<?= e($capturasBase . '/Dashboard%20-%20Inicio.png') ?>" alt="Panel de inicio del sistema" loading="lazy"><figcaption>Dashboard ejecutivo</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><img src="<?= e($capturasBase . '/Punto%20de%20venta.png') ?>" alt="Módulo de punto de venta" loading="lazy"><figcaption>POS conectado</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><img src="<?= e($capturasBase . '/Movimientos%20de%20inventario.png') ?>" alt="Módulo de inventario" loading="lazy"><figcaption>Inventario en tiempo real</figcaption></figure></div>
                    <div class="col-6"><figure class="landing-shot-card mb-0"><img src="<?= e($capturasBase . '/Clientes.png') ?>" alt="Módulo de clientes" loading="lazy"><figcaption>Clientes y contactos</figcaption></figure></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-info-subtle text-info-emphasis mb-2">Módulo de cotizaciones</span>
            <h2 class="h4 mb-2">Carrusel de cotizaciones: flujo real de uso</h2>
            <p class="text-secondary mb-0">Las siguientes vistas muestran cómo trabajar de punta a punta: crear, editar, enviar y hacer seguimiento hasta el cierre.</p>
        </div>
        <div id="cotizacionesCarousel" class="carousel slide landing-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($cotizacionesCapturas as $index => $captura): ?>
                    <button type="button" data-bs-target="#cotizacionesCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($cotizacionesCapturas as $index => $captura): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= e($capturasBase . '/' . $captura['archivo']) ?>" class="d-block w-100" alt="<?= e($captura['titulo']) ?>" loading="lazy">
                        <div class="landing-carousel-caption">
                            <h3 class="h6 mb-1"><?= e($captura['titulo']) ?></h3>
                            <p class="small mb-0"><?= e($captura['descripcion']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#cotizacionesCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#cotizacionesCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </div>
</section>

<section id="planes" class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-2">Planes diseñados por nivel de control y gestión</h2>
            <p class="text-secondary mb-0">Planes activos configurados desde el panel de administración.</p>
        </div>
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" aria-label="Seleccionar modalidad">
                <button type="button" class="btn btn-primary" data-home-billing="mensual">Mensual</button>
                <button type="button" class="btn btn-outline-primary" data-home-billing="anual">Anual (Ahorra hasta 10%)</button>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            <?php foreach ($planes as $plan): ?>
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border-2 <?= !empty($plan['recomendado']) ? 'border-primary border-3 shadow' : '' ?>" style="border-color: <?= e($plan['color_visual'] ?: '#dce3eb') ?> !important;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php if (!empty($plan['recomendado'])): ?><span class="badge text-bg-primary">RECOMENDADO</span><?php endif; ?>
                                <?php if (!empty($plan['destacado'])): ?><span class="badge text-bg-success">DESTACADO</span><?php endif; ?>
                            </div>
                            <h3 class="h5"><?= e($plan['nombre']) ?></h3>
                            <p class="text-secondary small"><?= e($plan['resumen_comercial'] ?: $plan['descripcion_comercial']) ?></p>
                            <div
                                class="h3 mb-0"
                                data-home-precio
                                data-precio-mensual="<?= e(number_format((float) $plan['precio_mensual'], 0, ',', '.')) ?>"
                                data-precio-anual="<?= e(number_format((float) $plan['precio_anual'], 0, ',', '.')) ?>"
                            >$<?= number_format((float) $plan['precio_mensual'], 0, ',', '.') ?> <small class="fs-6">/ mensual</small></div>
                            <p class="small text-secondary"><?= e((string) $plan['descuento_anual_pct']) ?>% descuento anual</p>
                            <ul class="small ps-3 d-grid gap-1">
                                <?php foreach ($plan['funcionalidades'] as $funcionalidad): ?>
                                    <li><?= e($funcionalidad['descripcion'] ?: $funcionalidad['nombre']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-grid gap-2 mt-auto">
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-outline-primary btn-sm" data-home-link>Comenzar ahora</a>
                                <a href="<?= e(url('/registro?plan=' . (int) $plan['id'] . '&frecuencia=mensual')) ?>" class="btn btn-primary btn-sm" data-home-link>Contratar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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

<section class="py-5 border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <h2 class="h4 mb-2">Impacto operativo: sin sistema vs con sistema</h2>
                <p class="small text-secondary mb-3">Referencia realista sobre tiempos, errores y cierres cuando se integra cotización + POS + inventario.</p>
                <div class="card">
                    <div class="card-body">
                        <div style="position: relative; height: 260px;">
                            <canvas id="graficoComparativoLanding"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h2 class="h4 mb-2">Evolución de control comercial e inventario</h2>
                <p class="small text-secondary mb-3">Al operar con datos en tiempo real, el negocio sostiene crecimiento con menos errores y mejor respuesta.</p>
                <div class="card">
                    <div class="card-body">
                        <div style="position: relative; height: 260px;">
                            <canvas id="graficoEvolucionLanding"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    const comparativo = document.getElementById('graficoComparativoLanding');
    const evolucion = document.getElementById('graficoEvolucionLanding');
    if (!comparativo && !evolucion) return;

    const iniciarGraficos = () => {
        if (typeof Chart === 'undefined') return;

        const mobile = window.innerWidth < 768;

        if (comparativo) {
            new Chart(comparativo, {
                type: 'bar',
                data: {
                    labels: ['Tiempo por venta (min)', 'Errores operativos (%)', 'Cierres mensuales'],
                    datasets: [
                        { label: 'Sin sistema', data: [28, 19, 15], backgroundColor: '#9aa9bc', borderRadius: 8 },
                        { label: 'Con sistema', data: [11, 5, 31], backgroundColor: '#4632a8', borderRadius: 8 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 150,
                    plugins: {
                        legend: {
                            position: mobile ? 'top' : 'bottom',
                            labels: { boxWidth: 10, font: { size: mobile ? 10 : 12 } }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: mobile ? 10 : 12 } } },
                        x: { ticks: { font: { size: mobile ? 10 : 12 } } }
                    }
                }
            });
        }

        if (evolucion) {
            new Chart(evolucion, {
                type: 'line',
                data: {
                    labels: ['Mes 1', 'Mes 2', 'Mes 3', 'Mes 4', 'Mes 5', 'Mes 6'],
                    datasets: [{
                        label: 'Mejora acumulada de control y eficiencia (%)',
                        data: [6, 12, 19, 27, 34, 41],
                        fill: true,
                        tension: 0.35,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,.15)',
                        pointRadius: mobile ? 2 : 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 150,
                    plugins: {
                        legend: {
                            position: mobile ? 'top' : 'bottom',
                            labels: { boxWidth: 10, font: { size: mobile ? 10 : 12 } }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: (v) => v + '%', font: { size: mobile ? 10 : 12 } }
                        },
                        x: { ticks: { font: { size: mobile ? 10 : 12 } } }
                    }
                }
            });
        }
    };

    const cargarChart = () => {
        if (typeof Chart !== 'undefined') {
            iniciarGraficos();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
        script.async = true;
        script.onload = iniciarGraficos;
        document.head.appendChild(script);
    };

    if ('requestIdleCallback' in window) {
        requestIdleCallback(cargarChart, { timeout: 900 });
    } else {
        setTimeout(cargarChart, 150);
    }
})();
</script>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card h-100 border-danger-subtle">
                    <div class="card-body">
                        <h2 class="h4 mb-3">Qué pasa cuando NO tienes un sistema</h2>
                        <ul class="mb-0 small ps-3 d-grid gap-2">
                            <li>Pierdes ventas por no saber qué tienes disponible en stock.</li>
                            <li>No sabes cuánto ganas realmente por producto, vendedor o período.</li>
                            <li>Vendes sin inventario actualizado y luego debes resolver reclamos.</li>
                            <li>Trabajas desordenado con múltiples planillas, chats y notas sueltas.</li>
                            <li>Dependes de Excel para tareas críticas que requieren control en tiempo real.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100 border-success-subtle">
                    <div class="card-body">
                        <h2 class="h4 mb-3">Qué cambia cuando usas este sistema</h2>
                        <ul class="mb-0 small ps-3 d-grid gap-2">
                            <li>Ves ventas en tiempo real y tomas decisiones con información confiable.</li>
                            <li>Reduces errores humanos al automatizar cálculo, registro y seguimiento.</li>
                            <li>Controlas inventario y evitas vender productos sin disponibilidad.</li>
                            <li>Ordenas el trabajo comercial para responder más rápido y cerrar mejor.</li>
                            <li>Mejoras la experiencia del cliente con procesos claros y profesionales.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-bottom">
    <div class="container">
        <h2 class="h4 mb-3">Beneficios reales para el negocio</h2>
        <p class="text-secondary mb-4">Este sistema está diseñado para operar mejor cada día: vender más, evitar errores y mantener control comercial e inventario sin perder tiempo.</p>
        <div class="row g-3 small">
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Control en tiempo real</strong><p class="mb-0">Consulta ventas, stock y movimiento comercial sin esperar cierres manuales.</p></div></div></div>
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Menos errores</strong><p class="mb-0">Estandariza procesos para evitar fallas de carga, cálculos y duplicidades.</p></div></div></div>
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Más velocidad de venta</strong><p class="mb-0">Cotiza, cobra y registra más rápido para atender más oportunidades.</p></div></div></div>
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Mejor imagen frente al cliente</strong><p class="mb-0">Proyecta una operación profesional con documentos y respuestas consistentes.</p></div></div></div>
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Mejor organización</strong><p class="mb-0">Cada área trabaja con la misma información y un flujo comercial claro.</p></div></div></div>
            <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><strong>Más ventas con control</strong><p class="mb-0">Al combinar seguimiento + stock + reportes, mejoras conversión y rentabilidad.</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-lg-7">
                <h2 class="h4 mb-3">POS + inventario integrados: la base para operar con orden</h2>
                <p class="text-secondary">Aquí no gestionas ventas y stock por separado. Cada venta impacta inventario automáticamente para que siempre sepas qué tienes, qué falta y qué debes reponer.</p>
                <div class="row g-3 small mt-1">
                    <div class="col-12 col-sm-6"><div class="card h-100"><div class="card-body"><strong>Ventas conectadas al stock</strong><p class="mb-0">Cada transacción descuenta inventario y actualiza disponibilidad real.</p></div></div></div>
                    <div class="col-12 col-sm-6"><div class="card h-100"><div class="card-body"><strong>Evita quiebres y sobreventas</strong><p class="mb-0">No prometes productos sin existencia, protegiendo margen y confianza.</p></div></div></div>
                    <div class="col-12 col-sm-6"><div class="card h-100"><div class="card-body"><strong>Decisiones con datos</strong><p class="mb-0">Identifica rotación, productos críticos y oportunidades de mejora.</p></div></div></div>
                    <div class="col-12 col-sm-6"><div class="card h-100"><div class="card-body"><strong>Operación más eficiente</strong><p class="mb-0">Menos tareas manuales y más tiempo para vender y atender clientes.</p></div></div></div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body">
                        <h3 class="h6">Mensaje central</h3>
                        <p class="small mb-0">Este sistema no es solo para cotizar. Es una herramienta de trabajo para vender más, evitar errores, controlar inventario, ordenar el negocio y tomar decisiones con datos reales.</p>
                    </div>
                </div>
            </div>
        </div>
        <p class="small text-secondary mt-3 mb-0 text-center">Si buscas crecer, controlar stock y tomar decisiones con datos, el plan Profesional suele ser la decisión más rentable.</p>
    </div>
</section>

<section class="py-5 border-bottom">
    <div class="container">
        <h2 class="h4 mb-3">Tabla comparativa de funcionalidades</h2>
        <p class="text-secondary small">Comparativa automática según funcionalidades activas por plan.</p>
        <div class="table-responsive">
            <table class="table table-bordered align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Funcionalidad</th>
                        <?php foreach ($planes as $plan): ?><th><?= e($plan['nombre']) ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $funcionalidadesUnicas = [];
                    foreach ($planes as $plan) {
                        foreach ($plan['funcionalidades'] as $funcionalidad) {
                            $funcionalidadesUnicas[$funcionalidad['codigo_interno']] = $funcionalidad['descripcion'] ?: $funcionalidad['nombre'];
                        }
                    }
                    ?>
                    <?php foreach ($funcionalidadesUnicas as $codigo => $nombre): ?>
                        <tr>
                            <td><?= e($nombre) ?></td>
                            <?php foreach ($planes as $plan): ?>
                                <?php
                                $incluida = false;
                                foreach ($plan['funcionalidades'] as $funcionalidad) {
                                    if ($funcionalidad['codigo_interno'] === $codigo) {
                                        $incluida = true;
                                        break;
                                    }
                                }
                                ?>
                                <td><?= $incluida ? '✔' : '—' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="h4">Convierte tu operación comercial en un sistema que realmente impulsa el negocio</h2>
        <p class="text-secondary">No se trata solo de cotizar: se trata de vender más, controlar mejor y crecer con orden.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary btn-sm">Comenzar ahora</a>
            <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-primary btn-sm">Ver planes</a>
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Contratar</a>
        </div>
    </div>
</section>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2 w-100">
        <a href="#planes" class="btn btn-primary btn-sm flex-fill">Ver planes</a>
        <a href="<?= e(url('/registro')) ?>" class="btn btn-outline-secondary btn-sm flex-fill">Comenzar ahora</a>
    </div>
</div>
