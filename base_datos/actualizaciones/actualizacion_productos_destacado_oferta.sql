ALTER TABLE productos
  ADD COLUMN destacado_catalogo TINYINT(1) NOT NULL DEFAULT 0 AFTER mostrar_catalogo,
  ADD COLUMN precio_oferta DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER precio;
