ALTER TABLE empresas
  ADD COLUMN catalogo_nosotros_titulo VARCHAR(120) NULL AFTER catalogo_topbar_texto,
  ADD COLUMN catalogo_nosotros_descripcion TEXT NULL AFTER catalogo_nosotros_titulo,
  ADD COLUMN catalogo_nosotros_imagen VARCHAR(255) NULL AFTER catalogo_nosotros_descripcion,
  ADD COLUMN catalogo_contacto_titulo VARCHAR(120) NULL AFTER catalogo_nosotros_imagen,
  ADD COLUMN catalogo_contacto_descripcion TEXT NULL AFTER catalogo_contacto_titulo,
  ADD COLUMN catalogo_contacto_horario VARCHAR(180) NULL AFTER catalogo_contacto_descripcion,
  ADD COLUMN catalogo_contacto_whatsapp VARCHAR(60) NULL AFTER catalogo_contacto_horario;
