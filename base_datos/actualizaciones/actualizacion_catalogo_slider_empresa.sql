ALTER TABLE empresas
  ADD COLUMN slider_imagen VARCHAR(255) NULL AFTER logo,
  ADD COLUMN slider_titulo VARCHAR(120) NULL AFTER slider_imagen,
  ADD COLUMN slider_bajada VARCHAR(220) NULL AFTER slider_titulo,
  ADD COLUMN slider_boton_texto VARCHAR(60) NULL AFTER slider_bajada,
  ADD COLUMN slider_boton_url VARCHAR(255) NULL AFTER slider_boton_texto;
