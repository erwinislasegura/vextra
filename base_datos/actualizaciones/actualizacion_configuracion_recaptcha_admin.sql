INSERT INTO configuraciones (clave, valor, descripcion, fecha_actualizacion)
VALUES
('recaptcha_habilitado', '0', 'Activa o desactiva Google reCAPTCHA en formularios públicos (0/1)', NOW()),
('recaptcha_site_key', '', 'Site key de Google reCAPTCHA para el frontend', NOW()),
('recaptcha_secret_key', '', 'Secret key de Google reCAPTCHA para validación backend', NOW())
ON DUPLICATE KEY UPDATE
  valor = VALUES(valor),
  descripcion = VALUES(descripcion),
  fecha_actualizacion = NOW();
