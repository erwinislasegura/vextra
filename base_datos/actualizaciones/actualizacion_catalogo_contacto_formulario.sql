ALTER TABLE empresas
  ADD COLUMN catalogo_contacto_form_titulo VARCHAR(160) NULL AFTER catalogo_contacto_whatsapp,
  ADD COLUMN catalogo_contacto_form_subtitulo VARCHAR(160) NULL AFTER catalogo_contacto_form_titulo,
  ADD COLUMN catalogo_contacto_form_bajada TEXT NULL AFTER catalogo_contacto_form_subtitulo,
  ADD COLUMN catalogo_contacto_form_correo_destino VARCHAR(180) NULL AFTER catalogo_contacto_form_bajada,
  ADD COLUMN catalogo_contacto_form_campos TEXT NULL AFTER catalogo_contacto_form_correo_destino,
  ADD COLUMN catalogo_contacto_form_texto_boton VARCHAR(60) NULL AFTER catalogo_contacto_form_campos,
  ADD COLUMN catalogo_contacto_mapa_url VARCHAR(500) NULL AFTER catalogo_contacto_form_texto_boton;
