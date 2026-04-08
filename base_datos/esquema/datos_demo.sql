USE cotiza_saas;

INSERT INTO empresas (id, razon_social, nombre_comercial, identificador_fiscal, correo, telefono, direccion, ciudad, pais, estado, fecha_activacion, plan_id)
VALUES
(1, 'CotizaPro SAS', 'CotizaPro Plataforma', '900111222', 'admin@cotizapro.com', '3001002000', 'Av. SaaS 100', 'Bogotá', 'Colombia', 'activa', '2026-01-01', 3),
(2, 'Comercial Andina SAS', 'Andina Comercial', '800555444', 'contacto@andina.com', '3015550001', 'Calle 10 #20-30', 'Medellín', 'Colombia', 'activa', '2026-02-01', 2),
(3, 'Servicios Pacífico SAS', 'Pacífico Servicios', '811222333', 'ventas@pacifico.com', '3028880001', 'Av. 3 Norte', 'Cali', 'Colombia', 'vencida', '2025-10-01', 1);

INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado) VALUES
(NULL, 1, 'Super Admin', 'superadmin@cotizapro.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 2, 'Laura Mejía', 'admin@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 3, 'Analista Andina', 'usuario@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 3, 'QA Andina', 'qa@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(3, 2, 'Carlos Reyes', 'admin@pacifico.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo');

INSERT INTO suscripciones (id, empresa_id, plan_id, estado, fecha_inicio, fecha_vencimiento, renovacion_automatica, observaciones)
VALUES
(1,2,2,'activa','2026-03-01','2026-04-01',0,'Suscripción mensual activa'),
(2,3,1,'vencida','2026-01-01','2026-02-01',0,'Cuenta vencida sin renovación');

INSERT INTO historial_suscripciones (suscripcion_id, accion, observaciones) VALUES
(1, 'alta', 'Registro inicial de suscripción activa'),
(2, 'vencimiento', 'No se recibió renovación');

INSERT INTO clientes (empresa_id, nombre, correo, telefono, direccion, notas, estado) VALUES
(2, 'Constructora Horizonte', 'compras@horizonte.com', '3201112233', 'Calle 123', 'Cliente estratégico', 'activo'),
(2, 'Insumos del Norte', 'contacto@insumosnorte.com', '3204445566', 'Carrera 45', 'Negociación anual', 'activo'),
(3, 'Transportes del Pacífico', 'info@transportespac.com', '3102001000', 'Avenida mar', 'Cliente histórico', 'activo');

INSERT INTO productos (empresa_id, categoria_id, codigo, nombre, descripcion, unidad, precio, impuesto, estado) VALUES
(2, NULL, 'SRV-001', 'Servicio de mantenimiento', 'Mantenimiento preventivo mensual', 'servicio', 1500, 19, 'activo'),
(2, NULL, 'PRD-002', 'Kit repuestos premium', 'Incluye piezas certificadas', 'kit', 980, 19, 'activo'),
(3, NULL, 'SRV-100', 'Consultoría logística', 'Optimización operativa', 'hora', 220, 19, 'activo');

INSERT INTO cotizaciones (empresa_id, cliente_id, usuario_id, numero, consecutivo, estado, subtotal, descuento, impuesto, total, observaciones, terminos_condiciones, fecha_emision, fecha_vencimiento)
VALUES
(2,1,2,'COT-002-000001',1,'enviada',1500,0,285,1785,'Incluye visita técnica','Validez 15 días','2026-03-20','2026-04-04'),
(2,2,2,'COT-002-000002',2,'aprobada',980,0,186.2,1166.2,'Entrega en 72 horas','Pago 50% anticipado','2026-03-21','2026-04-05'),
(3,3,4,'COT-003-000001',1,'vencida',2200,0,418,2618,'Propuesta trimestral','Sujeta a disponibilidad','2026-01-10','2026-01-25');

INSERT INTO items_cotizacion (cotizacion_id, producto_id, descripcion, cantidad, precio_unitario, porcentaje_impuesto, subtotal, total) VALUES
(1,1,'Servicio de mantenimiento',1,1500,19,1500,1785),
(2,2,'Kit repuestos premium',1,980,19,980,1166.2),
(3,3,'Consultoría logística',10,220,19,2200,2618);

INSERT INTO historial_estados_cotizacion (cotizacion_id, estado, observaciones, usuario_id) VALUES
(1,'enviada','Enviada por correo al cliente',2),
(2,'aprobada','Aprobada por cliente',2),
(3,'vencida','No hubo respuesta del cliente',4);

INSERT INTO pagos (empresa_id, suscripcion_id, monto, moneda, metodo, estado, referencia_externa, observaciones, payload, fecha_pago) VALUES
(2,1,49,'USD','transferencia','aprobado','REF-AND-001','Pago mensual recibido', JSON_OBJECT('origen','demo'), '2026-03-01 10:00:00'),
(3,2,19,'USD','tarjeta','rechazado','REF-PAC-002','Tarjeta sin fondos', JSON_OBJECT('origen','demo'), '2026-02-01 10:30:00');

INSERT INTO logs_pagos (pago_id, tipo_evento, payload, respuesta) VALUES
(1, 'webhook.aprobado', JSON_OBJECT('evento','aprobado'), 'Suscripción activa confirmada'),
(2, 'webhook.rechazado', JSON_OBJECT('evento','rechazado'), 'Se notificó rechazo');

INSERT INTO configuraciones_empresa (empresa_id, clave, valor) VALUES
(2, 'moneda_preferida', 'USD'),
(2, 'terminos_cotizacion', 'Validez 15 días'),
(3, 'moneda_preferida', 'USD');
