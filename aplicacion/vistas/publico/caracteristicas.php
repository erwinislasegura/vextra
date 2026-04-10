<?php
$capturasBase = '/img/Captura Sistema';
$capturaUrl = static fn(string $archivo): string => url($capturasBase . '/' . rawurlencode($archivo));
$capturasRutaFs = dirname(__DIR__, 3) . '/img/Captura Sistema/';
$capturaConFallback = static function (array $archivos) use ($capturaUrl): string {
    $raiz = dirname(__DIR__, 3) . '/img/Captura Sistema/';
    foreach ($archivos as $archivo) {
        if (is_file($raiz . $archivo)) {
            return $capturaUrl($archivo);
        }
    }
    return $capturaUrl($archivos[0] ?? '');
};
$capturaConFallbackInline = static function (array $archivos) use ($capturaConFallback, $capturasRutaFs): string {
    foreach ($archivos as $archivo) {
        $ruta = $capturasRutaFs . $archivo;
        if (!is_file($ruta)) {
            continue;
        }

        $contenido = @file_get_contents($ruta);
        if ($contenido !== false) {
            return 'data:image/png;base64,' . base64_encode($contenido);
        }
    }

    return $capturaConFallback($archivos);
};
?>

<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-info-subtle text-info-emphasis mb-2">Página de características</span>
                <h1 class="display-6 fw-bold mb-3">Características del sistema de cotizaciones</h1>
                <p class="lead text-secondary mb-4">Conoce todas las herramientas que te permiten organizar tu proceso comercial, reducir errores y trabajar de forma más eficiente en tu empresa.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('/planes')) ?>" class="btn btn-primary btn-sm">Ver planes</a>
                    <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Contactar</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <h2 class="h6">¿Qué incluye esta solución?</h2>
                        <ul class="small mb-0">
                            <li>Gestión de clientes, contactos y catálogo.</li>
                            <li>Control de cotizaciones con seguimiento comercial.</li>
                            <li>Módulos para vendedores, inventario y reportes.</li>
                            <li>Flujo de trabajo pensado para empresas reales.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light-subtle">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-2 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary-emphasis mb-2">Características con evidencia visual</span>
                <h2 class="h4 mb-1">Módulos clave en pantallas reales</h2>
                <p class="text-secondary mb-0">La plataforma combina gestión comercial, inventario y operación diaria en una interfaz consistente y profesional.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Dashboard - Inicio.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Dashboard principal"><img src="<?= e($capturaUrl('Dashboard - Inicio.png')) ?>" alt="Pantalla principal del dashboard" loading="lazy"></a><figcaption>Dashboard principal</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaConFallback(['Servicios y Productos.png', 'Servicios : Productos.png'])) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Catálogo de productos"><img src="<?= e($capturaConFallback(['Servicios y Productos.png', 'Servicios : Productos.png'])) ?>" alt="Módulo de productos" loading="lazy"></a><figcaption>Catálogo de productos</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Carga masiva de productos.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Carga masiva"><img src="<?= e($capturaUrl('Carga masiva de productos.png')) ?>" alt="Carga masiva de productos" loading="lazy"></a><figcaption>Carga masiva</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Ordenes de compra.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Órdenes de compra"><img src="<?= e($capturaUrl('Ordenes de compra.png')) ?>" alt="Órdenes de compra" loading="lazy"></a><figcaption>Órdenes de compra</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Recepciones de inventario.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Recepciones de inventario"><img src="<?= e($capturaUrl('Recepciones de inventario.png')) ?>" alt="Recepciones de inventario" loading="lazy"></a><figcaption>Recepciones de inventario</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Proveedores.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Proveedores"><img src="<?= e($capturaUrl('Proveedores.png')) ?>" alt="Módulo de proveedores" loading="lazy"></a><figcaption>Proveedores</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Ajustes de inventario.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Ajustes de inventario"><img src="<?= e($capturaUrl('Ajustes de inventario.png')) ?>" alt="Ajustes de inventario" loading="lazy"></a><figcaption>Ajustes de inventario</figcaption></figure></div>
            <div class="col-12 col-md-6 col-lg-3"><figure class="landing-shot-card mb-0"><a href="<?= e($capturaUrl('Vendedores.png')) ?>" class="landing-shot-link js-captura-ampliable" data-captura-title="Gestión de vendedores"><img src="<?= e($capturaUrl('Vendedores.png')) ?>" alt="Módulo de vendedores" loading="lazy"></a><figcaption>Gestión de vendedores</figcaption></figure></div>
        </div>
    </div>
</section>

<div class="captura-preview" id="previewCapturaCaracteristicas" hidden>
    <div class="captura-preview__backdrop" data-preview-close></div>
    <div class="captura-preview__dialog" role="dialog" aria-modal="true" aria-label="Vista previa de captura">
        <button type="button" class="captura-preview__close" data-preview-close aria-label="Cerrar vista previa">×</button>
        <h2 class="h6 mb-2" data-captura-modal-title>Vista de módulo</h2>
        <img src="" alt="" class="img-fluid w-100 rounded" data-captura-modal-image>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-3">¿Qué es y para qué sirve un sistema de cotizaciones?</h2>
        <p class="text-secondary mb-0">Un <strong>sistema de cotizaciones</strong> es una herramienta que centraliza la creación de propuestas comerciales, clientes y productos en una sola plataforma. Un <strong>software de cotizaciones</strong> te ayuda a responder más rápido, reducir errores manuales y mantener control del proceso de ventas. Por eso se ha vuelto clave para gestionar <strong>cotizaciones para empresas</strong> que necesitan orden, seguimiento y crecimiento comercial sostenido.</p>
    </div>
</section>


<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="h4 mb-2">Comparativo de desempeño comercial</h2>
                <p class="text-secondary small mb-3">La diferencia entre trabajar sin sistema y con una plataforma profesional se refleja en productividad y control.</p>
                <div class="card chart-card">
                    <div class="card-body">
                        <canvas id="graficoComparativoCaracteristicas" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="h4 mb-2">Evolución de ganancias tras implementar el sistema</h2>
                <p class="text-secondary small mb-3">Visualización estilo dashboard para entender el impacto en ingresos al profesionalizar cotizaciones y seguimiento.</p>
                <div class="card chart-card">
                    <div class="card-body">
                        <canvas id="graficoGananciasCaracteristicas" height="220"></canvas>
                    </div>
                </div>
                <p class="small text-secondary mt-2 mb-0">Escenario de referencia: crecimiento acumulado de <strong>+48%</strong> en 12 meses.</p>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    const modalEl = document.getElementById('previewCapturaCaracteristicas');
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

    const comparativo = document.getElementById('graficoComparativoCaracteristicas');
    const ganancias = document.getElementById('graficoGananciasCaracteristicas');
    if (!comparativo && !ganancias) return;

    const iniciarGraficos = () => {
        if (typeof Chart === 'undefined') return;

        const esMovil = window.innerWidth < 768;

        if (comparativo) {
            new Chart(comparativo, {
                type: 'radar',
                data: {
                    labels: ['Velocidad de cotización', 'Control comercial', 'Seguimiento', 'Productividad', 'Calidad de propuesta'],
                    datasets: [
                        { label: 'Sin sistema', data: [38, 35, 31, 40, 42], backgroundColor: 'rgba(143,160,181,.2)', borderColor: '#8fa0b5', pointBackgroundColor: '#8fa0b5' },
                        { label: 'Con Vextra', data: [86, 89, 88, 91, 87], backgroundColor: 'rgba(70,50,168,.18)', borderColor: '#4632a8', pointBackgroundColor: '#4632a8' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: esMovil ? 'top' : 'bottom', labels: { boxWidth: 10, font: { size: esMovil ? 10 : 12 } } } },
                    scales: { r: { suggestedMin: 0, suggestedMax: 100, ticks: { stepSize: 20, backdropColor: 'transparent', font: { size: esMovil ? 9 : 11 } }, grid: { color: '#e7edf6' }, pointLabels: { font: { size: esMovil ? 9 : 11 } } } }
                }
            });
        }

        if (ganancias) {
            new Chart(ganancias, {
                type: 'bar',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4'],
                    datasets: [{
                        label: 'Crecimiento acumulado de ingresos (%)',
                        data: [9, 21, 34, 48],
                        backgroundColor: ['#d9d1ff', '#b8aaf6', '#8773de', '#4632a8'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: esMovil ? 'top' : 'bottom', labels: { boxWidth: 10, font: { size: esMovil ? 10 : 12 } } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => v + '%', font: { size: esMovil ? 10 : 12 } }, grid: { color: '#edf1f7' } }, x: { ticks: { font: { size: esMovil ? 10 : 12 } } } }
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

<style>
.feature-card-icon{
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    line-height: 1;
    color: #4632a8;
    background: rgba(70, 50, 168, .10);
}
.feature-card-icon svg{
    width: 1.2rem;
    height: 1.2rem;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.feature-new-badge{
    background: #4632a8;
    color: #fff;
    font-size: .65rem;
    letter-spacing: .03em;
}
</style>

<svg width="0" height="0" style="position:absolute">
    <symbol id="i-building" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h.01M12 7h.01M16 7h.01M8 11h.01M12 11h.01M16 11h.01M12 21v-4"/></symbol>
    <symbol id="i-doc" viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M10 12h5M10 16h5"/></symbol>
    <symbol id="i-box" viewBox="0 0 24 24"><path d="M3 8l9-5 9 5-9 5-9-5z"/><path d="M3 8v8l9 5 9-5V8"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20 13l-7 7-9-9V4h7z"/><path d="M8 8h.01"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M6 15l4-4 3 2 5-6"/></symbol>
    <symbol id="i-truck" viewBox="0 0 24 24"><path d="M3 6h11v9H3zM14 10h4l3 3v2h-7z"/><circle cx="8" cy="17" r="1.5"/><circle cx="18" cy="17" r="1.5"/></symbol>
    <symbol id="i-cart" viewBox="0 0 24 24"><path d="M3 5h2l2 10h10l2-7H7"/><circle cx="10" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M4 8l8 6 8-6"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="9" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M4 19c0-3 2.5-5 5-5s5 2 5 5M14 19c0-2 1.5-3.5 3.5-3.5S21 17 21 19"/></symbol>
    <symbol id="i-bars" viewBox="0 0 24 24"><path d="M4 20h16"/><rect x="6" y="11" width="2.5" height="7"/><rect x="11" y="8" width="2.5" height="10"/><rect x="16" y="5" width="2.5" height="13"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 16h12l-1.5-2.5V10a4.5 4.5 0 10-9 0v3.5z"/><path d="M10 18a2 2 0 004 0"/></symbol>
    <symbol id="i-gear" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4L7 17M17 7l1.4-1.4"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-inbox" viewBox="0 0 24 24"><path d="M4 5h16l-1.5 12h-13z"/><path d="M4 13h5l2 2h2l2-2h5"/></symbol>
</svg>

<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <h2 class="h4 mb-4">Características funcionales del sistema</h2>
        <div class="row g-3 small">
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-building"></use></svg></div><h3 class="h6">Clientes y contactos</h3><p class="mb-0 text-secondary">Administra cartera de clientes, múltiples contactos por empresa y acceso rápido a historial comercial para un seguimiento más ordenado.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-doc"></use></svg></div><h3 class="h6">Cotizaciones completas</h3><p class="mb-0 text-secondary">Crea, edita, imprime y exporta cotizaciones en PDF; además puedes enviarlas y dar continuidad del proceso sin salir de la plataforma.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-box"></use></svg></div><h3 class="h6">Productos y categorías</h3><p class="mb-0 text-secondary">Gestiona catálogo con categorías, precios, impuestos y estados, incluyendo carga masiva con plantillas para acelerar la implementación.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-tag"></use></svg></div><h3 class="h6">Listas de precios</h3><p class="mb-0 text-secondary">Define listas comerciales por contexto y aplica reglas de precios para cotizar con mayor consistencia entre vendedores y canales.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-chart"></use></svg></div><h3 class="h6">Seguimiento y aprobaciones</h3><p class="mb-0 text-secondary">Controla etapas comerciales, tareas de seguimiento y registros de aprobación para aumentar la tasa de cierre con visibilidad real.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-truck"></use></svg></div><h3 class="h6">Inventario operativo</h3><p class="mb-0 text-secondary">Incluye proveedores, órdenes de compra, recepciones, ajustes y movimientos de inventario para mantener trazabilidad y stock confiable.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-cart"></use></svg></div><h3 class="h6">Punto de venta (POS)</h3><p class="mb-0 text-secondary">Registra ventas, apertura y cierre de caja, movimientos y terminales POS con historial para controlar operación diaria de caja.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-mail"></use></svg></div><h3 class="h6">Documentos y plantillas</h3><p class="mb-0 text-secondary">Personaliza plantillas de correo para cotizaciones y flujo de órdenes de compra HTML para estandarizar comunicaciones con clientes.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-users"></use></svg></div><h3 class="h6">Usuarios y permisos</h3><p class="mb-0 text-secondary">Gestiona usuarios por empresa y capacidades por plan para controlar acceso a módulos, procesos y datos sensibles.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-bars"></use></svg></div><h3 class="h6">Reportes y exportación</h3><p class="mb-0 text-secondary">Exporta información clave a Excel en clientes, productos, cotizaciones, inventario y POS para análisis operativo y financiero.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-bell"></use></svg></div><h3 class="h6">Notificaciones e historial</h3><p class="mb-0 text-secondary">Centraliza alertas y trazas de actividad para mejorar control interno, auditoría de cambios y continuidad del trabajo en equipo.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-gear"></use></svg></div><h3 class="h6">Configuración empresarial</h3><p class="mb-0 text-secondary">Configura datos de empresa, correos de stock y parámetros clave para adaptar la plataforma a la operación real de tu negocio.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100 border-primary-subtle"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-mail"></use></svg></div><div class="d-flex align-items-center gap-2 mb-1"><h3 class="h6 mb-0">Alertas de stock por correo</h3><span class="badge feature-new-badge text-uppercase">Nueva</span></div><p class="mb-0 text-secondary">Activa avisos automáticos a correos definidos cuando el stock llega a niveles críticos para reaccionar a tiempo.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100 border-primary-subtle"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-doc"></use></svg></div><div class="d-flex align-items-center gap-2 mb-1"><h3 class="h6 mb-0">Envío OC HTML</h3><span class="badge feature-new-badge text-uppercase">Nueva</span></div><p class="mb-0 text-secondary">Configura y envía órdenes de compra en formato HTML con mejor presentación y estructura para proveedores.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100 border-primary-subtle"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-clock"></use></svg></div><div class="d-flex align-items-center gap-2 mb-1"><h3 class="h6 mb-0">Historial de actividad</h3><span class="badge feature-new-badge text-uppercase">Nueva</span></div><p class="mb-0 text-secondary">Revisa trazabilidad de acciones por usuario para mejorar control interno, seguimiento operativo y auditorías.</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card h-100 border-primary-subtle"><div class="card-body"><div class="feature-card-icon mb-3"><svg aria-hidden="true"><use href="#i-inbox"></use></svg></div><div class="d-flex align-items-center gap-2 mb-1"><h3 class="h6 mb-0">Recepciones de inventario</h3><span class="badge feature-new-badge text-uppercase">Nueva</span></div><p class="mb-0 text-secondary">Registra recepciones de mercadería y cruza con órdenes de compra para validar ingresos y stock en tiempo real.</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h4 mb-2">Cómo se usa en la práctica</h2>
            <p class="text-secondary mb-0">Una vista rápida del flujo real para pasar de cotizar con desorden a vender con continuidad comercial.</p>
        </div>
        <div class="landing-timeline" aria-label="Línea de tiempo de uso práctico de Vextra">
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 1</span>
                    <h3 class="h6 mb-1">Registras clientes</h3>
                    <p class="small mb-0 text-secondary">Centralizas tus contactos y dejas atrás la información dispersa en planillas.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 2</span>
                    <h3 class="h6 mb-1">Agregas productos</h3>
                    <p class="small mb-0 text-secondary">Configuras catálogo y precios para cotizar con consistencia comercial.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 3</span>
                    <h3 class="h6 mb-1">Creas cotizaciones</h3>
                    <p class="small mb-0 text-secondary">Generas propuestas profesionales y listas para enviar en minutos.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-activity"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 4</span>
                    <h3 class="h6 mb-1">Haces seguimiento</h3>
                    <p class="small mb-0 text-secondary">Controlas estados y avances para no perder oportunidades de cierre.</p>
                </div>
            </article>
            <article class="landing-timeline__item">
                <span class="landing-timeline__icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                <div class="landing-timeline__card">
                    <span class="landing-timeline__step">Paso 5</span>
                    <h3 class="h6 mb-1">Cierras ventas</h3>
                    <p class="small mb-0 text-secondary">Conectas cotización, POS e inventario para cerrar con más orden y rentabilidad.</p>
                </div>
            </article>
        </div>
        <p class="text-secondary mt-4 mb-4 text-center">Este flujo te ayuda a trabajar con orden, mejorar experiencia de cliente y aumentar conversiones sin complejidad operativa.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= e(url('/planes')) ?>" class="btn btn-primary btn-sm">Ver planes</a>
            <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-primary btn-sm">Contratar</a>
            <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Contactar</a>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <h2 class="h4 mb-4">Beneficios clave para tu empresa</h2>
        <div class="row g-3 small">
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Menos errores</strong><p class="mb-0 text-secondary">La automatización reduce fallas en cálculos y datos de cotización.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Más rapidez</strong><p class="mb-0 text-secondary">Respondes más rápido a clientes y aprovechas mejor cada oportunidad.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Mejor imagen profesional</strong><p class="mb-0 text-secondary">Entregas propuestas claras y consistentes que transmiten confianza.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Más control</strong><p class="mb-0 text-secondary">Visualizas estados y resultados para tomar decisiones comerciales con criterio.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Mejor organización</strong><p class="mb-0 text-secondary">Centralizas la gestión comercial y eliminas desorden entre archivos y correos.</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-3">Sección técnica simple</h2>
        <div class="row g-3 small">
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Sistema en la nube</strong><p class="mb-0 text-secondary">Puedes acceder desde cualquier lugar con conexión a internet, sin necesidad de instalar programas.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Acceso desde cualquier dispositivo</strong><p class="mb-0 text-secondary">Trabaja desde computador, tablet o celular según tu dinámica comercial diaria.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Información centralizada y uso seguro</strong><p class="mb-0 text-secondary">Toda la operación comercial se concentra en un solo lugar para mantener orden y control de accesos.</p></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-body"><strong>Actualizaciones continuas</strong><p class="mb-0 text-secondary">El sistema evoluciona para mantener una herramienta vigente, práctica y confiable para empresas.</p></div></div></div>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <h2 class="h4 mb-3">¿Por qué este sistema y no otro?</h2>
        <p class="text-secondary mb-0">Porque es un sistema simple, práctico y enfocado en empresas reales que necesitan resultados en el día a día. Está diseñado para ordenar la operación comercial, facilitar el trabajo del equipo y escalar contigo a medida que crecen tus ventas.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-3">SEO y contexto comercial</h2>
        <p class="text-secondary mb-0">Si buscas un <strong>sistema de cotizaciones para empresas</strong>, este <strong>software para cotizar</strong> funciona como una <strong>herramienta de ventas</strong> para mejorar el <strong>control de cotizaciones</strong> y fortalecer la <strong>gestión comercial</strong> sin complejidad innecesaria.</p>
    </div>
</section>

<section class="py-5 border-top">
    <div class="container text-center">
        <h2 class="h4">Empieza a trabajar con orden y mejora tu proceso comercial desde hoy.</h2>
        <p class="text-secondary">Conoce los planes, elige la opción que mejor se adapta a tu empresa y da el siguiente paso para profesionalizar tus cotizaciones.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary btn-sm">Crear cuenta</a>
            <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-primary btn-sm">Ver planes</a>
        </div>
    </div>
</section>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2">
        <a href="<?= e(url('/planes')) ?>" class="btn btn-primary btn-sm">Ver planes</a>
        <a href="<?= e(url('/contacto')) ?>" class="btn btn-outline-secondary btn-sm">Contactar</a>
    </div>
</div>
