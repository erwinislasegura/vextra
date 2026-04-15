ALTER TABLE empresas
    ADD COLUMN IF NOT EXISTS catalogo_nosotros_banner_imagen VARCHAR(255) NULL AFTER catalogo_nosotros_imagen,
    ADD COLUMN IF NOT EXISTS catalogo_nosotros_bloque_titulo VARCHAR(160) NULL AFTER catalogo_nosotros_banner_imagen,
    ADD COLUMN IF NOT EXISTS catalogo_nosotros_bloque_texto TEXT NULL AFTER catalogo_nosotros_bloque_titulo;
