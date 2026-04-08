USE cotiza_saas;

INSERT INTO roles (id, nombre, codigo) VALUES
(1, 'Superadministrador', 'superadministrador'),
(2, 'Administrador', 'administrador_empresa'),
(3, 'Vendedor', 'vendedor'),
(4, 'Administrativo', 'administrativo'),
(5, 'Contabilidad', 'contabilidad'),
(6, 'Supervisor Comercial', 'supervisor_comercial'),
(7, 'Operaciones', 'operaciones'),
(8, 'Usuario de Empresa', 'usuario_empresa');

INSERT INTO permisos (nombre, codigo) VALUES
('Acceso panel admin', 'panel_admin'),
('Gestión planes', 'planes'),
('Gestión funcionalidades', 'funcionalidades'),
('Gestión suscripciones', 'suscripciones'),
('Gestión clientes', 'clientes'),
('Gestión productos', 'productos'),
('Gestión cotizaciones', 'cotizaciones'),
('Panel empresa', 'panel_empresa');

INSERT INTO funcionalidades (id, nombre, codigo_interno, descripcion, tipo_valor, estado) VALUES
(1,'Máximo usuarios','maximo_usuarios','Límite de usuarios por empresa','numerico','activo'),
(2,'Máximo clientes','maximo_clientes','Límite de clientes por empresa','numerico','activo'),
(3,'Máximo productos','maximo_productos','Límite de productos por empresa','numerico','activo'),
(4,'Máximo cotizaciones mes','maximo_cotizaciones_mes','Límite mensual de cotizaciones','numerico','activo'),
(5,'Cotización PDF','cotizacion_pdf','Permite generar PDF','booleano','activo'),
(6,'Cotización por correo','cotizacion_correo','Permite enviar por correo','booleano','activo'),
(7,'Logo personalizado','logo_personalizado','Permite subir logo','booleano','activo'),
(8,'Reportes','reportes','Acceso a reportes avanzados','booleano','activo'),
(9,'Plantillas personalizadas','plantillas_personalizadas','Permite personalización de plantillas','booleano','activo'),
(10,'Acceso API','acceso_api','Habilita API','booleano','activo'),
(11,'Soporte prioritario','soporte_prioritario','Soporte preferencial','booleano','activo');

INSERT INTO planes (id, nombre, slug, descripcion_comercial, precio_mensual, precio_anual, duracion_dias, visible, destacado, orden_visualizacion, insignia, resumen_comercial, color_visual, estado)
VALUES
(1, 'Plan Inicial', 'plan-inicial', 'Ideal para empresas que comienzan.', 19, 190, 30, 1, 0, 1, NULL, 'Control básico y orden comercial', '#4f7ea8', 'activo'),
(2, 'Plan Profesional', 'plan-profesional', 'Para equipos que buscan productividad.', 49, 490, 30, 1, 1, 2, 'Más vendido', 'Automatización completa de cotizaciones', '#1f4f78', 'activo'),
(3, 'Plan Corporativo', 'plan-corporativo', 'Para operación avanzada multiusuario.', 99, 990, 30, 1, 0, 3, 'Escalable', 'Mayor capacidad y soporte prioritario', '#0f2f4a', 'activo');

INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado) VALUES
(1,1,1,3,0),(1,2,1,50,0),(1,3,1,100,0),(1,4,1,80,0),(1,5,1,1,0),(1,6,0,0,0),(1,7,0,0,0),(1,8,0,0,0),
(2,1,1,10,0),(2,2,1,250,0),(2,3,1,500,0),(2,4,1,300,0),(2,5,1,1,0),(2,6,1,1,0),(2,7,1,1,0),(2,8,1,1,0),(2,9,1,1,0),
(3,1,1,0,1),(3,2,1,0,1),(3,3,1,0,1),(3,4,1,0,1),(3,5,1,1,0),(3,6,1,1,0),(3,7,1,1,0),(3,8,1,1,0),(3,9,1,1,0),(3,10,1,1,0),(3,11,1,1,0);

INSERT INTO configuraciones (clave, valor, descripcion) VALUES
('nombre_plataforma', 'CotizaPro', 'Nombre comercial de la plataforma'),
('correo_soporte', 'soporte@cotizapro.local', 'Correo principal de soporte'),
('dias_alerta_vencimiento', '7', 'Días previos para alertar vencimientos');
