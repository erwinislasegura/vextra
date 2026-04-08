-- Actualización incremental del panel comercial SaaS.
-- Recomendado: ejecutar primero un respaldo completo de la base de datos.

USE cotiza_saas;

-- =====================================================
-- 1) Ajustes en tablas existentes para compatibilidad.
-- =====================================================

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='razon_social') = 0,
  'ALTER TABLE clientes ADD COLUMN razon_social VARCHAR(180) NULL AFTER nombre',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='nombre_comercial') = 0,
  'ALTER TABLE clientes ADD COLUMN nombre_comercial VARCHAR(180) NULL AFTER razon_social','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='identificador_fiscal') = 0,
  'ALTER TABLE clientes ADD COLUMN identificador_fiscal VARCHAR(80) NULL AFTER nombre_comercial','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='giro') = 0,
  'ALTER TABLE clientes ADD COLUMN giro VARCHAR(180) NULL AFTER identificador_fiscal','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='ciudad') = 0,
  'ALTER TABLE clientes ADD COLUMN ciudad VARCHAR(120) NULL AFTER direccion','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND COLUMN_NAME='vendedor_id') = 0,
  'ALTER TABLE clientes ADD COLUMN vendedor_id BIGINT UNSIGNED NULL AFTER ciudad','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='tipo') = 0,
  "ALTER TABLE productos ADD COLUMN tipo ENUM('producto','servicio') NOT NULL DEFAULT 'producto' AFTER categoria_id",'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='costo') = 0,
  'ALTER TABLE productos ADD COLUMN costo DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER precio','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='descuento_maximo') = 0,
  'ALTER TABLE productos ADD COLUMN descuento_maximo DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER impuesto','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='categorias_productos' AND COLUMN_NAME='descripcion') = 0,
  'ALTER TABLE categorias_productos ADD COLUMN descripcion VARCHAR(255) NULL AFTER nombre','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='contactos_cliente' AND COLUMN_NAME='empresa_id') = 0,
  'ALTER TABLE contactos_cliente ADD COLUMN empresa_id BIGINT UNSIGNED NULL AFTER id','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='contactos_cliente' AND COLUMN_NAME='celular') = 0,
  'ALTER TABLE contactos_cliente ADD COLUMN celular VARCHAR(80) NULL AFTER telefono','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='contactos_cliente' AND COLUMN_NAME='es_principal') = 0,
  'ALTER TABLE contactos_cliente ADD COLUMN es_principal TINYINT(1) NOT NULL DEFAULT 0 AFTER celular','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='contactos_cliente' AND COLUMN_NAME='observaciones') = 0,
  'ALTER TABLE contactos_cliente ADD COLUMN observaciones TEXT NULL AFTER es_principal','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE contactos_cliente cc
INNER JOIN clientes c ON c.id = cc.cliente_id
SET cc.empresa_id = c.empresa_id
WHERE cc.empresa_id IS NULL;

-- =====================================================
-- 2) Nuevas tablas comerciales.
-- =====================================================

CREATE TABLE IF NOT EXISTS vendedores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(160) NOT NULL,
  correo VARCHAR(160) NULL,
  telefono VARCHAR(80) NULL,
  comision DECIMAL(8,2) NOT NULL DEFAULT 0,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_vendedores_empresa (empresa_id),
  INDEX idx_vendedores_usuario (usuario_id),
  CONSTRAINT fk_vendedores_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_vendedores_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vendedores' AND COLUMN_NAME='usuario_id') = 0,
  'ALTER TABLE vendedores ADD COLUMN usuario_id BIGINT UNSIGNED NULL AFTER empresa_id','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vendedores' AND INDEX_NAME='idx_vendedores_usuario') = 0,
  'ALTER TABLE vendedores ADD INDEX idx_vendedores_usuario (usuario_id)','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vendedores' AND CONSTRAINT_NAME='fk_vendedores_usuario') = 0,
  'ALTER TABLE vendedores ADD CONSTRAINT fk_vendedores_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)','SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS listas_precios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  vigencia_desde DATE NULL,
  vigencia_hasta DATE NULL,
  tipo_lista VARCHAR(120) NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  reglas_base TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_listas_empresa (empresa_id),
  CONSTRAINT fk_listas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS seguimientos_comerciales (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  cotizacion_id BIGINT UNSIGNED NULL,
  cliente_id BIGINT UNSIGNED NULL,
  responsable VARCHAR(160) NULL,
  proxima_accion VARCHAR(220) NULL,
  fecha_seguimiento DATE NULL,
  comentarios TEXT NULL,
  estado_comercial VARCHAR(80) NOT NULL DEFAULT 'abierto',
  probabilidad_cierre TINYINT UNSIGNED NOT NULL DEFAULT 0,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_seguimiento_empresa (empresa_id),
  CONSTRAINT fk_seguimiento_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_seguimiento_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
  CONSTRAINT fk_seguimiento_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

CREATE TABLE IF NOT EXISTS aprobaciones_cotizacion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  cotizacion_id BIGINT UNSIGNED NULL,
  monto DECIMAL(12,2) NOT NULL DEFAULT 0,
  motivo VARCHAR(255) NULL,
  solicitante VARCHAR(160) NULL,
  aprobador VARCHAR(160) NULL,
  estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha_aprobacion DATE NULL,
  observaciones TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_aprobaciones_empresa (empresa_id),
  CONSTRAINT fk_aprobacion_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_aprobacion_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id)
);

CREATE TABLE IF NOT EXISTS documentos_plantillas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  tipo_documento VARCHAR(80) NOT NULL DEFAULT 'cotizacion',
  terminos_defecto TEXT NULL,
  observaciones_defecto TEXT NULL,
  firma VARCHAR(180) NULL,
  logo VARCHAR(255) NULL,
  pie_documento VARCHAR(255) NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documentos_empresa (empresa_id),
  CONSTRAINT fk_documentos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS notificaciones_empresa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  tipo VARCHAR(80) NOT NULL DEFAULT 'sistema',
  titulo VARCHAR(180) NOT NULL,
  mensaje TEXT NULL,
  estado VARCHAR(60) NOT NULL DEFAULT 'pendiente',
  fecha_evento DATE NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notificaciones_empresa (empresa_id),
  CONSTRAINT fk_notificaciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS historial_actividad (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  usuario_nombre VARCHAR(160) NOT NULL,
  modulo VARCHAR(120) NOT NULL,
  accion VARCHAR(120) NOT NULL,
  detalle TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historial_empresa (empresa_id),
  CONSTRAINT fk_historial_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

-- Relación opcional entre cliente y vendedor.
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes' AND CONSTRAINT_NAME='fk_clientes_vendedor') = 0,
  'ALTER TABLE clientes ADD CONSTRAINT fk_clientes_vendedor FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='contactos_cliente' AND INDEX_NAME='idx_contactos_empresa') = 0,
  'CREATE INDEX idx_contactos_empresa ON contactos_cliente (empresa_id)',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- =====================================================
-- 3) Catálogos base sugeridos (si no existen datos).
-- =====================================================
INSERT INTO notificaciones_empresa (empresa_id, tipo, titulo, mensaje, estado, fecha_creacion)
SELECT e.id, 'sistema', 'Panel comercial actualizado', 'Se aplicó la mejora comercial con nuevos módulos.', 'pendiente', NOW()
FROM empresas e
WHERE NOT EXISTS (
  SELECT 1 FROM notificaciones_empresa n WHERE n.empresa_id = e.id AND n.titulo = 'Panel comercial actualizado'
);
