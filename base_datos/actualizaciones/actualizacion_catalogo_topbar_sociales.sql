ALTER TABLE empresas
  ADD COLUMN catalogo_topbar_texto VARCHAR(220) NULL AFTER slider_boton_url,
  ADD COLUMN catalogo_social_facebook VARCHAR(255) NULL AFTER catalogo_topbar_texto,
  ADD COLUMN catalogo_social_instagram VARCHAR(255) NULL AFTER catalogo_social_facebook,
  ADD COLUMN catalogo_social_tiktok VARCHAR(255) NULL AFTER catalogo_social_instagram,
  ADD COLUMN catalogo_social_linkedin VARCHAR(255) NULL AFTER catalogo_social_tiktok,
  ADD COLUMN catalogo_social_youtube VARCHAR(255) NULL AFTER catalogo_social_linkedin;
