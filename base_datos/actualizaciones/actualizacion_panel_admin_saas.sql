USE cotiza_saas;

-- Ajustes de estructura para panel administrador SaaS
ALTER TABLE planes
  ADD COLUMN IF NOT EXISTS descuento_anual_pct DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER precio_mensual,
  ADD COLUMN IF NOT EXISTS recomendado TINYINT(1) NOT NULL DEFAULT 0 AFTER destacado,
  ADD COLUMN IF NOT EXISTS maximo_usuarios INT NOT NULL DEFAULT 1 AFTER color_visual,
  ADD COLUMN IF NOT EXISTS usuarios_ilimitados TINYINT(1) NOT NULL DEFAULT 0 AFTER maximo_usuarios,
  ADD COLUMN IF NOT EXISTS observaciones_internas TEXT NULL AFTER usuarios_ilimitados;

ALTER TABLE empresas
  ADD COLUMN IF NOT EXISTS observaciones_internas TEXT NULL AFTER plan_id;

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS password_actualizado_en DATETIME NULL AFTER ultimo_acceso;

ALTER TABLE pagos
  ADD COLUMN IF NOT EXISTS frecuencia ENUM('mensual','anual') NULL AFTER metodo;

CREATE TABLE IF NOT EXISTS logs_administracion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_usuario_id BIGINT UNSIGNED NULL,
  empresa_id BIGINT UNSIGNED NULL,
  usuario_objetivo_id BIGINT UNSIGNED NULL,
  modulo VARCHAR(120) NOT NULL,
  accion VARCHAR(120) NOT NULL,
  detalle TEXT NULL,
  ip VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_admin_usuario (admin_usuario_id),
  INDEX idx_logs_empresa (empresa_id),
  INDEX idx_logs_usuario_objetivo (usuario_objetivo_id),
  CONSTRAINT fk_logs_admin_usuario FOREIGN KEY (admin_usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_logs_admin_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_logs_admin_usuario_obj FOREIGN KEY (usuario_objetivo_id) REFERENCES usuarios(id)
);

-- Funcionalidades SaaS orientadas a planes (idempotente por código interno)
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo clientes', 'modulo_clientes', 'Habilita gestión de clientes', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_clientes');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo productos', 'modulo_productos', 'Habilita gestión de productos', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_productos');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo cotizaciones', 'modulo_cotizaciones', 'Habilita cotizaciones', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_cotizaciones');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo POS', 'modulo_pos', 'Habilita punto de venta', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_pos');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo inventario', 'modulo_inventario', 'Habilita inventario', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_inventario');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo recepciones', 'modulo_recepciones', 'Recepciones de inventario', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_recepciones');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo ajustes', 'modulo_ajustes', 'Ajustes de inventario', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_ajustes');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo movimientos', 'modulo_movimientos', 'Movimientos de inventario', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_movimientos');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo vendedores', 'modulo_vendedores', 'Gestión de vendedores', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_vendedores');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo reportes', 'modulo_reportes', 'Acceso a reportes', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_reportes');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo listas de precios', 'modulo_listas_precios', 'Listas de precios', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_listas_precios');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo órdenes de compra', 'modulo_ordenes_compra', 'Órdenes de compra', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_ordenes_compra');
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo usuarios', 'modulo_usuarios', 'Administración de usuarios', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_usuarios');

-- Planes iniciales requeridos
INSERT INTO planes (nombre, slug, descripcion_comercial, precio_mensual, descuento_anual_pct, precio_anual, duracion_dias, visible, destacado, recomendado, orden_visualizacion, insignia, resumen_comercial, color_visual, maximo_usuarios, usuarios_ilimitados, observaciones_internas, estado)
VALUES
('Básico', 'basico', 'Plan funcional y limitado para iniciar.', 15000, 10, 162000, 30, 1, 0, 0, 1, 'Inicial', 'Incluye cotizaciones, clientes, productos y POS básico.', '#3b82f6', 2, 0, 'Límite bajo para incentivar upgrade.', 'activo'),
('Profesional', 'profesional', 'Plan recomendado para operación comercial diaria.', 26000, 10, 280800, 30, 1, 1, 1, 2, 'Más elegido', 'Incluye inventario completo y reportes operativos.', '#0ea5a4', 8, 0, 'Balance entre costo y capacidad.', 'activo'),
('Empresa', 'empresa', 'Plan completo para operación avanzada.', 55000, 15, 561000, 30, 1, 1, 0, 3, 'Escalable', 'Todas las funciones del sistema con alta capacidad.', '#7c3aed', 0, 1, 'Usuarios ilimitados con expansión corporativa.', 'activo')
ON DUPLICATE KEY UPDATE
  descripcion_comercial = VALUES(descripcion_comercial),
  precio_mensual = VALUES(precio_mensual),
  descuento_anual_pct = VALUES(descuento_anual_pct),
  precio_anual = VALUES(precio_anual),
  destacado = VALUES(destacado),
  recomendado = VALUES(recomendado),
  orden_visualizacion = VALUES(orden_visualizacion),
  insignia = VALUES(insignia),
  resumen_comercial = VALUES(resumen_comercial),
  color_visual = VALUES(color_visual),
  maximo_usuarios = VALUES(maximo_usuarios),
  usuarios_ilimitados = VALUES(usuarios_ilimitados),
  observaciones_internas = VALUES(observaciones_internas),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

-- Vinculación mínima de funcionalidades a planes por código
INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT p.id, f.id,
  CASE
    WHEN p.slug = 'basico' AND f.codigo_interno IN ('modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario') THEN 1
    WHEN p.slug = 'profesional' AND f.codigo_interno IN ('modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_vendedores','modulo_reportes','modulo_listas_precios') THEN 1
    WHEN p.slug = 'empresa' THEN 1
    ELSE 0
  END,
  0,
  CASE WHEN p.slug = 'empresa' THEN 1 ELSE 0 END,
  NOW()
FROM planes p
INNER JOIN funcionalidades f ON f.codigo_interno IN (
  'modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos','modulo_vendedores','modulo_reportes','modulo_listas_precios','modulo_ordenes_compra','modulo_usuarios'
)
WHERE p.slug IN ('basico','profesional','empresa')
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
