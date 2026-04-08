USE cotiza_saas;

INSERT INTO roles (nombre, codigo) VALUES
('Administrador', 'administrador_empresa'),
('Vendedor', 'vendedor'),
('Administrativo', 'administrativo'),
('Contabilidad', 'contabilidad'),
('Supervisor Comercial', 'supervisor_comercial'),
('Operaciones', 'operaciones'),
('Usuario de Empresa', 'usuario_empresa')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
