-- Ejecutar sobre la base de datos seleccionada en la sesión actual.

-- Datos administrativos base (sin poblar tablas de clientes/cotizaciones/productos).

INSERT INTO empresas (id, razon_social, nombre_comercial, identificador_fiscal, correo, telefono, direccion, ciudad, pais, estado, fecha_activacion, plan_id)
VALUES
(1, 'CotizaPro SAS', 'CotizaPro Plataforma', '900111222', 'admin@cotizapro.com', '3001002000', 'Av. SaaS 100', 'Bogotá', 'Colombia', 'activa', '2026-01-01', 3),
(2, 'Comercial Andina SAS', 'Andina Comercial', '800555444', 'contacto@andina.com', '3015550001', 'Calle 10 #20-30', 'Medellín', 'Colombia', 'activa', '2026-02-01', 2),
(3, 'Servicios Pacífico SAS', 'Pacífico Servicios', '811222333', 'ventas@pacifico.com', '3028880001', 'Av. 3 Norte', 'Cali', 'Colombia', 'vencida', '2025-10-01', 1)
ON DUPLICATE KEY UPDATE
  razon_social = VALUES(razon_social),
  nombre_comercial = VALUES(nombre_comercial),
  identificador_fiscal = VALUES(identificador_fiscal),
  telefono = VALUES(telefono),
  direccion = VALUES(direccion),
  ciudad = VALUES(ciudad),
  pais = VALUES(pais),
  estado = VALUES(estado),
  fecha_activacion = VALUES(fecha_activacion),
  plan_id = VALUES(plan_id),
  fecha_actualizacion = NOW();

INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado)
VALUES
(NULL, 1, 'Super Admin', 'superadmin@cotizapro.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 2, 'Laura Mejía', 'admin@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 3, 'Analista Andina', 'usuario@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo'),
(2, 8, 'QA Andina', 'qa@andina.com', '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi', 'activo')
ON DUPLICATE KEY UPDATE
  empresa_id = VALUES(empresa_id),
  rol_id = VALUES(rol_id),
  nombre = VALUES(nombre),
  password = VALUES(password),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

INSERT INTO suscripciones (id, empresa_id, plan_id, estado, fecha_inicio, fecha_vencimiento, renovacion_automatica, observaciones)
VALUES
(1, 2, 2, 'activa', '2026-03-01', '2026-04-01', 0, 'Suscripción mensual activa'),
(2, 3, 1, 'vencida', '2026-01-01', '2026-02-01', 0, 'Cuenta vencida sin renovación')
ON DUPLICATE KEY UPDATE
  plan_id = VALUES(plan_id),
  estado = VALUES(estado),
  fecha_inicio = VALUES(fecha_inicio),
  fecha_vencimiento = VALUES(fecha_vencimiento),
  renovacion_automatica = VALUES(renovacion_automatica),
  observaciones = VALUES(observaciones),
  fecha_actualizacion = NOW();

INSERT INTO historial_suscripciones (suscripcion_id, accion, observaciones)
SELECT 1, 'alta', 'Registro inicial de suscripción activa'
WHERE NOT EXISTS (
  SELECT 1 FROM historial_suscripciones WHERE suscripcion_id = 1 AND accion = 'alta'
);

INSERT INTO historial_suscripciones (suscripcion_id, accion, observaciones)
SELECT 2, 'vencimiento', 'No se recibió renovación'
WHERE NOT EXISTS (
  SELECT 1 FROM historial_suscripciones WHERE suscripcion_id = 2 AND accion = 'vencimiento'
);

INSERT INTO pagos (empresa_id, suscripcion_id, monto, moneda, metodo, estado, referencia_externa, observaciones, payload, fecha_pago)
SELECT 2, 1, 49, 'USD', 'transferencia', 'aprobado', 'REF-AND-001', 'Pago mensual recibido', JSON_OBJECT('origen','admin_seed'), '2026-03-01 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM pagos WHERE referencia_externa = 'REF-AND-001');

INSERT INTO pagos (empresa_id, suscripcion_id, monto, moneda, metodo, estado, referencia_externa, observaciones, payload, fecha_pago)
SELECT 3, 2, 19, 'USD', 'tarjeta', 'rechazado', 'REF-PAC-002', 'Tarjeta sin fondos', JSON_OBJECT('origen','admin_seed'), '2026-02-01 10:30:00'
WHERE NOT EXISTS (SELECT 1 FROM pagos WHERE referencia_externa = 'REF-PAC-002');
