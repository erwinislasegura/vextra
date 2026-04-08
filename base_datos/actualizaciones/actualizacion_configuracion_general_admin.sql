USE cotiza_saas;

INSERT INTO configuraciones (clave, valor, descripcion, fecha_actualizacion)
VALUES
('moneda_defecto', 'CLP', 'Moneda predeterminada para nuevos módulos globales', NOW()),
('zona_horaria', 'America/Santiago', 'Zona horaria general del sistema', NOW()),
('estado_plataforma', 'activo', 'Estado general de la plataforma (activo o mantenimiento)', NOW())
ON DUPLICATE KEY UPDATE
  valor = VALUES(valor),
  descripcion = VALUES(descripcion),
  fecha_actualizacion = NOW();
