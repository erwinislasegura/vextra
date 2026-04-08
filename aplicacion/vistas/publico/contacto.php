<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-info-subtle text-info-emphasis mb-2">Contacto comercial</span>
                <h1 class="display-6 fw-bold mb-3">Habla con ventas y elige el plan correcto para tu empresa</h1>
                <p class="lead text-secondary mb-0">Cuéntanos cómo cotiza hoy tu negocio y te recomendamos la opción más conveniente para mejorar control comercial, velocidad de respuesta y cierre de ventas.</p>
            </div>
            <div class="col-lg-5">
                <div class="card card-soft h-100">
                    <div class="card-body small">
                        <h2 class="h6">¿Qué pasa después de escribirnos?</h2>
                        <ul class="mb-0">
                            <li>Revisamos tu necesidad comercial.</li>
                            <li>Te sugerimos el plan más adecuado.</li>
                            <li>Resolvemos dudas de implementación.</li>
                            <li>Te acompañamos en el inicio.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Solicita asesoría comercial</h2>
                        <p class="small text-secondary">Completa el formulario y te contactaremos para ayudarte a implementar un sistema de cotizaciones alineado a tu operación.</p>

                        <form class="row g-3 mt-1" method="POST" action="<?= e(url('/contacto')) ?>" data-recaptcha-form="1" data-recaptcha-action="contacto_landing">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="g-recaptcha-response" value="">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Nombre</label>
                                <input class="form-control" name="nombre" placeholder="Tu nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Correo</label>
                                <input class="form-control" type="email" name="correo" placeholder="tu@empresa.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Teléfono / WhatsApp</label>
                                <input class="form-control" name="telefono" placeholder="+56 9 1234 5678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Empresa</label>
                                <input class="form-control" name="empresa" placeholder="Nombre de tu empresa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Tipo de contacto</label>
                                <select class="form-select" name="tipo_contacto" required>
                                    <option value="prospecto">Posible cliente</option>
                                    <option value="cliente_actual">Cliente actual</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-1">Motivo de la consulta</label>
                                <select class="form-select" name="motivo_consulta" required>
                                    <option value="">Selecciona una opción</option>
                                    <option value="demo">Solicitar demo</option>
                                    <option value="planes">Consulta de planes y precios</option>
                                    <option value="implementacion">Consulta de implementación</option>
                                    <option value="soporte_cliente_actual">Soporte para cliente actual</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-1">Mensaje</label>
                                <textarea class="form-control" name="mensaje" rows="5" placeholder="Cuéntanos cuántas cotizaciones manejan al mes y qué desean mejorar" required></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button class="btn btn-primary btn-sm" type="submit">Contactar ventas</button>
                                <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-primary btn-sm">Ver planes</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6">Razones para avanzar hoy</h2>
                        <div class="small text-secondary">
                            <p><strong>Menos dependencia de Excel:</strong> organiza cotizaciones, clientes y seguimiento en una sola plataforma.</p>
                            <p><strong>Más control de ventas:</strong> visualiza el estado de cada oportunidad y prioriza mejor el trabajo comercial.</p>
                            <p><strong>Implementación simple:</strong> funciona en la nube y no requiere instalaciones complejas.</p>
                            <p class="mb-0"><strong>Escalabilidad real:</strong> empieza con el plan correcto y crece sin perder orden operativo.</p>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-secondary btn-sm">Comparar planes</a>
                            <a href="<?= e(url('/registro')) ?>" class="btn btn-primary btn-sm">Crear cuenta</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="d-md-none mobile-buy-bar">
    <div class="d-flex gap-2">
        <a href="<?= e(url('/planes')) ?>" class="btn btn-primary btn-sm">Ver planes</a>
        <a href="<?= e(url('/registro')) ?>" class="btn btn-outline-secondary btn-sm">Crear cuenta</a>
    </div>
</div>
