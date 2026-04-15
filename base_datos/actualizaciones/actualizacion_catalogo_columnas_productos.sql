ALTER TABLE empresas
  ADD COLUMN catalogo_columnas_productos TINYINT UNSIGNED NOT NULL DEFAULT 3
  AFTER catalogo_color_acento;
