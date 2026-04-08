-- Flujo integral de inventario: recepciones, ajustes, movimientos y alertas de stock por empresa.
SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='stock_actual') = 0,
  'ALTER TABLE productos ADD COLUMN stock_actual DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER stock_aviso',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='stock_critico') = 0,
  'ALTER TABLE productos ADD COLUMN stock_critico DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER stock_minimo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE productos SET stock_critico = stock_aviso WHERE stock_critico = 0 AND stock_aviso > 0;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='ultimo_aviso_stock_bajo') = 0,
  'ALTER TABLE productos ADD COLUMN ultimo_aviso_stock_bajo DATETIME NULL AFTER stock_critico',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='ultimo_aviso_stock_critico') = 0,
  'ALTER TABLE productos ADD COLUMN ultimo_aviso_stock_critico DATETIME NULL AFTER ultimo_aviso_stock_bajo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS proveedores_inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(180) NOT NULL,
  identificador_fiscal VARCHAR(80) NULL,
  contacto VARCHAR(140) NULL,
  correo VARCHAR(160) NULL,
  telefono VARCHAR(80) NULL,
  direccion VARCHAR(200) NULL,
  ciudad VARCHAR(120) NULL,
  observacion TEXT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_proveedores_empresa (empresa_id),
  CONSTRAINT fk_proveedores_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);


SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='proveedores_inventario' AND COLUMN_NAME='identificador_fiscal') = 0,
  'ALTER TABLE proveedores_inventario ADD COLUMN identificador_fiscal VARCHAR(80) NULL AFTER nombre',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='proveedores_inventario' AND COLUMN_NAME='contacto') = 0,
  'ALTER TABLE proveedores_inventario ADD COLUMN contacto VARCHAR(140) NULL AFTER identificador_fiscal',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='proveedores_inventario' AND COLUMN_NAME='direccion') = 0,
  'ALTER TABLE proveedores_inventario ADD COLUMN direccion VARCHAR(200) NULL AFTER telefono',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='proveedores_inventario' AND COLUMN_NAME='ciudad') = 0,
  'ALTER TABLE proveedores_inventario ADD COLUMN ciudad VARCHAR(120) NULL AFTER direccion',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='proveedores_inventario' AND COLUMN_NAME='observacion') = 0,
  'ALTER TABLE proveedores_inventario ADD COLUMN observacion TEXT NULL AFTER ciudad',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS recepciones_inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  proveedor_id BIGINT UNSIGNED NULL,
  tipo_documento ENUM('guia_despacho','factura') NOT NULL,
  numero_documento VARCHAR(100) NOT NULL,
  fecha_documento DATE NOT NULL,
  referencia_interna VARCHAR(120) NULL,
  observacion TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_recepciones_empresa (empresa_id, fecha_creacion),
  CONSTRAINT fk_recepciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_recepciones_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores_inventario(id),
  CONSTRAINT fk_recepciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS recepciones_inventario_detalle (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recepcion_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX idx_recepcion_detalle (recepcion_id),
  CONSTRAINT fk_recepcion_detalle_recepcion FOREIGN KEY (recepcion_id) REFERENCES recepciones_inventario(id),
  CONSTRAINT fk_recepcion_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS ajustes_inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  tipo_ajuste ENUM('entrada','salida') NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  motivo VARCHAR(120) NOT NULL,
  observacion TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ajustes_empresa (empresa_id, fecha_creacion),
  CONSTRAINT fk_ajustes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_ajustes_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_ajustes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS movimientos_inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  tipo_movimiento VARCHAR(80) NOT NULL,
  modulo_origen VARCHAR(80) NOT NULL,
  documento_origen VARCHAR(120) NULL,
  referencia_id BIGINT UNSIGNED NULL,
  entrada DECIMAL(12,2) NOT NULL DEFAULT 0,
  salida DECIMAL(12,2) NOT NULL DEFAULT 0,
  saldo_resultante DECIMAL(12,2) NOT NULL DEFAULT 0,
  observacion TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_mov_empresa (empresa_id, fecha_creacion),
  INDEX idx_mov_producto (producto_id, fecha_creacion),
  CONSTRAINT fk_mov_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_mov_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO configuraciones_empresa (empresa_id, clave, valor, fecha_actualizacion)
SELECT id, 'activar_alerta_stock_bajo', '1', NOW() FROM empresas
ON DUPLICATE KEY UPDATE fecha_actualizacion = NOW();

INSERT INTO configuraciones_empresa (empresa_id, clave, valor, fecha_actualizacion)
SELECT id, 'activar_alerta_stock_critico', '1', NOW() FROM empresas
ON DUPLICATE KEY UPDATE fecha_actualizacion = NOW();
