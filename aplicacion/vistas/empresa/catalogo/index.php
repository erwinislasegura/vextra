<?php $empresaId = (int) ($empresa['id'] ?? 0); ?>

<section class="container-fluid px-0">
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h1 class="h4 mb-1">Catálogo en línea</h1>
      <p class="text-muted mb-0">Publica tus productos como landing profesional con filtros, carrito y checkout Flow.</p>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-uppercase text-muted small mb-2">Productos publicados</div>
          <div class="display-6 fw-bold"><?= (int) $publicados ?></div>
          <p class="small text-muted mt-2 mb-0">Recuerda marcar “Mostrar en catálogo en línea” en cada producto/servicio.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h6">URL pública de tu catálogo</h2>
          <div class="input-group mb-2">
            <input id="catalogoPublicoUrl" class="form-control" readonly value="<?= e($catalogoUrl) ?>">
            <button class="btn btn-outline-secondary" type="button" id="copiarCatalogoUrl">Copiar</button>
            <a class="btn btn-primary" target="_blank" rel="noopener" href="<?= e($catalogoUrl) ?>">Abrir catálogo</a>
          </div>
          <div class="small text-muted">Empresa: <strong><?= e((string) ($empresa['nombre_comercial'] ?? 'Mi empresa')) ?></strong></div>
          <hr>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/app/productos')) ?>">Ir a productos</a>
            <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/app/categorias')) ?>">Gestionar categorías</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mt-3">
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2 class="h5 mb-1">Personalización del catálogo público</h2>
      <p class="text-muted small mb-3">Configura el contenido de la barra superior y del slider destacado de tu catálogo en línea.</p>
      <form method="POST" action="<?= e(url('/app/catalogo-en-linea/configuracion')) ?>" enctype="multipart/form-data" class="row g-3">
        <?= csrf_campo() ?>
        <div class="col-12">
          <div class="border rounded-3 p-3">
            <h3 class="h6 mb-2">Barra superior</h3>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label">Texto principal superior</label>
                <input type="text" name="catalogo_topbar_texto" maxlength="220" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_topbar_texto'] ?? '')) ?>" placeholder="Ejemplo: Envíos a todo Chile • Garantía oficial • Atención personalizada">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">Facebook (URL)</label>
                <input type="url" name="catalogo_social_facebook" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_facebook'] ?? '')) ?>" placeholder="https://facebook.com/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">Instagram (URL)</label>
                <input type="url" name="catalogo_social_instagram" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_instagram'] ?? '')) ?>" placeholder="https://instagram.com/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">TikTok (URL)</label>
                <input type="url" name="catalogo_social_tiktok" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_tiktok'] ?? '')) ?>" placeholder="https://tiktok.com/@tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">LinkedIn (URL)</label>
                <input type="url" name="catalogo_social_linkedin" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_linkedin'] ?? '')) ?>" placeholder="https://linkedin.com/company/tuempresa">
              </div>
              <div class="col-md-6 col-xl-4">
                <label class="form-label">YouTube (URL)</label>
                <input type="url" name="catalogo_social_youtube" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_social_youtube'] ?? '')) ?>" placeholder="https://youtube.com/@tuempresa">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Color primario</label>
                <?php $colorPrimario = (string) ($sliderCatalogo['catalogo_color_primario'] ?? '#4632A8'); ?>
                <input id="catalogo_color_primario_picker" type="color" name="catalogo_color_primario_picker" class="form-control form-control-color w-100" value="<?= e($colorPrimario !== '' ? $colorPrimario : '#4632A8') ?>" title="Selecciona el color primario del catálogo">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Código color primario</label>
                <input type="text" name="catalogo_color_primario" maxlength="7" class="form-control" value="<?= e($colorPrimario) ?>" placeholder="#4632A8">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Color acento</label>
                <?php $colorAcento = (string) ($sliderCatalogo['catalogo_color_acento'] ?? '#5415B0'); ?>
                <input id="catalogo_color_acento_picker" type="color" name="catalogo_color_acento_picker" class="form-control form-control-color w-100" value="<?= e($colorAcento !== '' ? $colorAcento : '#5415B0') ?>" title="Selecciona el color de acento del catálogo">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Código color acento</label>
                <input type="text" name="catalogo_color_acento" maxlength="7" class="form-control" value="<?= e($colorAcento) ?>" placeholder="#5415B0">
              </div>
              <div class="col-md-6 col-xl-3">
                <label class="form-label">Columnas de productos (catálogo público)</label>
                <?php $columnasProductos = (int) ($sliderCatalogo['catalogo_columnas_productos'] ?? 3); ?>
                <?php if ($columnasProductos < 2 || $columnasProductos > 5) { $columnasProductos = 3; } ?>
                <select name="catalogo_columnas_productos" class="form-select">
                  <option value="2" <?= $columnasProductos === 2 ? 'selected' : '' ?>>2 columnas</option>
                  <option value="3" <?= $columnasProductos === 3 ? 'selected' : '' ?>>3 columnas</option>
                  <option value="4" <?= $columnasProductos === 4 ? 'selected' : '' ?>>4 columnas</option>
                  <option value="5" <?= $columnasProductos === 5 ? 'selected' : '' ?>>5 columnas</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="border rounded-3 p-3 h-100">
            <h3 class="h6 mb-2">Vista "Nosotros"</h3>
            <p class="small text-muted mb-3">Estos datos se verán en la sección pública "Nosotros" del catálogo.</p>
            <div class="mb-2">
              <label class="form-label">Título</label>
              <input type="text" name="catalogo_nosotros_titulo" maxlength="120" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_nosotros_titulo'] ?? '')) ?>" placeholder="Ejemplo: Nosotros">
            </div>
            <div class="mb-2">
              <label class="form-label">Descripción</label>
              <textarea name="catalogo_nosotros_descripcion" maxlength="900" rows="5" class="form-control" placeholder="Describe tu empresa, experiencia y propuesta de valor."><?= e((string) ($sliderCatalogo['catalogo_nosotros_descripcion'] ?? '')) ?></textarea>
            </div>
            <label class="form-label">Foto de nosotros (JPG, PNG o WEBP)</label>
            <input type="file" name="catalogo_nosotros_imagen" accept="image/jpeg,image/png,image/webp" class="form-control">
            <div class="form-text">Recomendado: imagen horizontal 1200x800 px.</div>
            <?php if (!empty($sliderCatalogo['catalogo_nosotros_imagen'])): ?>
              <div class="mt-3">
                <img src="<?= e(url('/catalogo/' . $empresaId . '/nosotros/imagen?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time())))) ?>" alt="Imagen nosotros" class="img-fluid rounded border" style="max-height:140px;width:auto;object-fit:cover;background:#f8fafc">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="eliminar_catalogo_nosotros_imagen" id="eliminar_catalogo_nosotros_imagen" value="1">
                  <label class="form-check-label" for="eliminar_catalogo_nosotros_imagen">Eliminar imagen actual</label>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="border rounded-3 p-3 h-100">
            <h3 class="h6 mb-2">Vista "Contacto"</h3>
            <p class="small text-muted mb-3">Completa información clave para que tus clientes te contacten.</p>
            <div class="mb-2">
              <label class="form-label">Título</label>
              <input type="text" name="catalogo_contacto_titulo" maxlength="120" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_titulo'] ?? '')) ?>" placeholder="Ejemplo: Contacto">
            </div>
            <div class="mb-2">
              <label class="form-label">Descripción</label>
              <textarea name="catalogo_contacto_descripcion" maxlength="900" rows="5" class="form-control" placeholder="Mensaje breve para invitar a contactar a tu equipo."><?= e((string) ($sliderCatalogo['catalogo_contacto_descripcion'] ?? '')) ?></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Título del bloque formulario</label>
              <input type="text" name="catalogo_contacto_form_titulo" maxlength="160" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_form_titulo'] ?? '')) ?>" placeholder="Nos pondremos en contacto a la brevedad">
            </div>
            <div class="mb-2">
              <label class="form-label">Subtítulo formulario</label>
              <input type="text" name="catalogo_contacto_form_subtitulo" maxlength="160" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_form_subtitulo'] ?? '')) ?>" placeholder="Enviar un mensaje">
            </div>
            <div class="mb-2">
              <label class="form-label">Texto de bajada formulario</label>
              <textarea name="catalogo_contacto_form_bajada" maxlength="1200" rows="4" class="form-control" placeholder="Texto descriptivo del formulario y cómo responderás."><?= e((string) ($sliderCatalogo['catalogo_contacto_form_bajada'] ?? '')) ?></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Correo destino (donde llegan los mensajes)</label>
              <input type="email" name="catalogo_contacto_form_correo_destino" maxlength="180" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_form_correo_destino'] ?? '')) ?>" placeholder="contacto@tuempresa.cl">
            </div>
            <div class="mb-2">
              <label class="form-label">Texto botón envío</label>
              <input type="text" name="catalogo_contacto_form_texto_boton" maxlength="60" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_form_texto_boton'] ?? '')) ?>" placeholder="Enviar mensaje">
            </div>
            <div class="mb-2">
              <label class="form-label">URL mapa (iframe o enlace)</label>
              <input type="url" name="catalogo_contacto_mapa_url" maxlength="500" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_mapa_url'] ?? '')) ?>" placeholder="https://maps.google.com/maps?q=Santiago&output=embed">
            </div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">Horario de atención</label>
                <input type="text" name="catalogo_contacto_horario" maxlength="180" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_horario'] ?? '')) ?>" placeholder="Lun a Vie de 09:00 a 18:00">
              </div>
              <div class="col-md-6">
                <label class="form-label">WhatsApp</label>
                <input type="text" name="catalogo_contacto_whatsapp" maxlength="60" class="form-control" value="<?= e((string) ($sliderCatalogo['catalogo_contacto_whatsapp'] ?? '')) ?>" placeholder="+56 9 1234 5678">
              </div>
            </div>
            <hr>
            <label class="form-label">Campos del formulario de contacto (activar/desactivar)</label>
            <?php
              $camposConfigurados = json_decode((string) ($sliderCatalogo['catalogo_contacto_form_campos'] ?? ''), true);
              if (!is_array($camposConfigurados) || $camposConfigurados === []) {
                $camposConfigurados = ['nombre', 'telefono', 'email', 'asunto', 'mensaje'];
              }
              $catalogoCamposDisponibles = [
                'nombre' => 'Nombre',
                'telefono' => 'Teléfono',
                'email' => 'Email',
                'asunto' => 'Asunto',
                'mensaje' => 'Mensaje',
                'empresa' => 'Empresa',
                'whatsapp' => 'WhatsApp',
                'ciudad' => 'Ciudad',
                'direccion' => 'Dirección',
                'cargo' => 'Cargo / Rol',
              ];
            ?>
            <div class="row g-2">
              <?php foreach ($catalogoCamposDisponibles as $campoClave => $campoLabel): ?>
                <?php $checkedCampo = in_array($campoClave, $camposConfigurados, true) ? 'checked' : ''; ?>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="catalogo_contacto_form_campos[]" value="<?= e($campoClave) ?>" id="campo_<?= e($campoClave) ?>" <?= $checkedCampo ?>>
                    <label class="form-check-label" for="campo_<?= e($campoClave) ?>"><?= e($campoLabel) ?></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="form-text">Sugerencia: mantén activos Nombre, Email y Mensaje para asegurar respuestas.</div>
          </div>
        </div>
        <div class="col-12">
          <hr class="my-1">
          <h3 class="h6 mb-2">Slider destacado</h3>
        </div>
        <div class="col-lg-5">
          <label class="form-label">Imagen principal (JPG, PNG o WEBP)</label>
          <input type="file" name="slider_imagen" accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Recomendado: 1600x500 px, peso menor a 1 MB.</div>
          <?php if (!empty($sliderCatalogo['slider_imagen'])): ?>
            <div class="mt-3">
              <img src="<?= e(url('/catalogo/' . $empresaId . '/slider/principal?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time())))) ?>" alt="Imagen actual slider" class="img-fluid rounded border" style="max-height:110px;width:auto;object-fit:contain;background:#f8fafc">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="eliminar_slider_imagen" id="eliminar_slider_imagen" value="1">
                <label class="form-check-label" for="eliminar_slider_imagen">Eliminar imagen actual</label>
              </div>
            </div>
          <?php endif; ?>
          <hr>
          <label class="form-label">Imagen secundaria (JPG, PNG o WEBP)</label>
          <input type="file" name="slider_imagen_secundaria" accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Se usa en la segunda transición del slider.</div>
          <?php if (!empty($sliderCatalogo['slider_imagen_secundaria'])): ?>
            <div class="mt-3">
              <img src="<?= e(url('/catalogo/' . $empresaId . '/slider/secundaria?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time())))) ?>" alt="Imagen secundaria slider" class="img-fluid rounded border" style="max-height:110px;width:auto;object-fit:contain;background:#f8fafc">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="eliminar_slider_imagen_secundaria" id="eliminar_slider_imagen_secundaria" value="1">
                <label class="form-check-label" for="eliminar_slider_imagen_secundaria">Eliminar imagen secundaria</label>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7">
          <div class="mb-2">
            <label class="form-label">Título</label>
            <input type="text" name="slider_titulo" maxlength="120" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_titulo'] ?? '')) ?>" placeholder="Ejemplo: Promociones destacadas del mes">
          </div>
          <div class="mb-2">
            <label class="form-label">Bajada</label>
            <textarea name="slider_bajada" maxlength="220" class="form-control" rows="3" placeholder="Describe la campaña o el mensaje principal para tus clientes."><?= e((string) ($sliderCatalogo['slider_bajada'] ?? '')) ?></textarea>
          </div>
          <div class="row g-2">
            <div class="col-md-5">
              <label class="form-label">Texto botón</label>
              <input type="text" name="slider_boton_texto" maxlength="60" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_boton_texto'] ?? '')) ?>" placeholder="Comprar ahora">
            </div>
            <div class="col-md-7">
              <label class="form-label">URL botón (https://...)</label>
              <input type="url" name="slider_boton_url" maxlength="255" class="form-control" value="<?= e((string) ($sliderCatalogo['slider_boton_url'] ?? '')) ?>" placeholder="https://tuempresa.cl/promo">
            </div>
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary" type="submit">Guardar configuración del slider</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
(() => {
  const btn = document.getElementById('copiarCatalogoUrl');
  const input = document.getElementById('catalogoPublicoUrl');
  if (!btn || !input) return;
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(input.value);
      btn.textContent = 'Copiado';
      setTimeout(() => { btn.textContent = 'Copiar'; }, 1300);
    } catch (e) {
      input.select();
      document.execCommand('copy');
      btn.textContent = 'Copiado';
      setTimeout(() => { btn.textContent = 'Copiar'; }, 1300);
    }
  });

  const colorPrimario = document.querySelector('input[name="catalogo_color_primario"]');
  const colorPrimarioPicker = document.getElementById('catalogo_color_primario_picker');
  const colorAcento = document.querySelector('input[name="catalogo_color_acento"]');
  const colorAcentoPicker = document.getElementById('catalogo_color_acento_picker');

  if (colorPrimario && colorPrimarioPicker) {
    colorPrimarioPicker.addEventListener('input', () => { colorPrimario.value = colorPrimarioPicker.value.toUpperCase(); });
    colorPrimario.addEventListener('input', () => {
      if (/^#([0-9A-Fa-f]{6})$/.test(colorPrimario.value)) colorPrimarioPicker.value = colorPrimario.value;
    });
  }
  if (colorAcento && colorAcentoPicker) {
    colorAcentoPicker.addEventListener('input', () => { colorAcento.value = colorAcentoPicker.value.toUpperCase(); });
    colorAcento.addEventListener('input', () => {
      if (/^#([0-9A-Fa-f]{6})$/.test(colorAcento.value)) colorAcentoPicker.value = colorAcento.value;
    });
  }
})();
</script>
