SET NAMES utf8mb4;

SET @db_name = DATABASE();

CREATE TABLE IF NOT EXISTS listas_precios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  tipo_lista ENUM('general','cliente','canal','volumen') NOT NULL DEFAULT 'general',
  moneda VARCHAR(12) NOT NULL DEFAULT 'USD',
  vigencia_desde DATE NULL,
  vigencia_hasta DATE NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  reglas_base TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  INDEX idx_listas_empresa (empresa_id),
  CONSTRAINT fk_listas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios' AND COLUMN_NAME='canal_venta') = 0,
  'ALTER TABLE listas_precios ADD COLUMN canal_venta VARCHAR(80) NULL AFTER tipo_lista',
  'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios' AND COLUMN_NAME='segmento_mercado') = 0,
  'ALTER TABLE listas_precios ADD COLUMN segmento_mercado VARCHAR(120) NULL AFTER canal_venta',
  'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios' AND COLUMN_NAME='ajuste_tipo') = 0,
  'ALTER TABLE listas_precios ADD COLUMN ajuste_tipo ENUM("incremento","descuento") NOT NULL DEFAULT "incremento" AFTER segmento_mercado',
  'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios' AND COLUMN_NAME='ajuste_porcentaje') = 0,
  'ALTER TABLE listas_precios ADD COLUMN ajuste_porcentaje DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER ajuste_tipo',
  'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS clientes_listas_precios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  cliente_id BIGINT UNSIGNED NOT NULL,
  lista_precio_id BIGINT UNSIGNED NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uniq_cliente_lista (empresa_id, cliente_id, lista_precio_id),
  INDEX idx_clientes_listas_lista (lista_precio_id),
  CONSTRAINT fk_clientes_listas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_clientes_listas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_clientes_listas_lista FOREIGN KEY (lista_precio_id) REFERENCES listas_precios(id)
);

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='clientes_listas_precios' AND INDEX_NAME='uniq_cliente_lista' AND SEQ_IN_INDEX=3) = 0,
  'ALTER TABLE clientes_listas_precios DROP INDEX uniq_cliente_lista, ADD UNIQUE KEY uniq_cliente_lista (empresa_id, cliente_id, lista_precio_id)',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS listas_precios_reglas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  lista_precio_id BIGINT UNSIGNED NOT NULL,
  ambito ENUM('global','categoria','producto') NOT NULL DEFAULT 'global',
  categoria_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  tipo_ajuste ENUM('incremento','descuento') NOT NULL DEFAULT 'incremento',
  porcentaje DECIMAL(8,4) NOT NULL DEFAULT 0,
  prioridad INT NOT NULL DEFAULT 100,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  observaciones VARCHAR(255) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  INDEX idx_lpr_empresa_lista (empresa_id, lista_precio_id),
  INDEX idx_lpr_producto (producto_id),
  INDEX idx_lpr_categoria (categoria_id),
  CONSTRAINT fk_lpr_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_lpr_lista FOREIGN KEY (lista_precio_id) REFERENCES listas_precios(id),
  CONSTRAINT fk_lpr_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_lpr_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_productos(id)
);
