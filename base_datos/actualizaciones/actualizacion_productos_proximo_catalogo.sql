ALTER TABLE productos
  ADD COLUMN proximo_catalogo TINYINT(1) NOT NULL DEFAULT 0 AFTER destacado_catalogo,
  ADD COLUMN proximo_dias_catalogo INT NOT NULL DEFAULT 0 AFTER proximo_catalogo;
