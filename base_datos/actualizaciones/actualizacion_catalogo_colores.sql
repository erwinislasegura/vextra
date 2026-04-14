ALTER TABLE empresas
  ADD COLUMN catalogo_color_primario VARCHAR(7) NULL AFTER catalogo_social_youtube,
  ADD COLUMN catalogo_color_acento VARCHAR(7) NULL AFTER catalogo_color_primario;
