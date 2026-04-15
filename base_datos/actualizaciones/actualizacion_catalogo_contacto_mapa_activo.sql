ALTER TABLE empresas
  ADD COLUMN catalogo_contacto_mapa_activo TINYINT(1) NOT NULL DEFAULT 1
  AFTER catalogo_contacto_mapa_url;
