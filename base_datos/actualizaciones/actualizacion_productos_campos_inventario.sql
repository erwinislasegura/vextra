-- Agrega campos operativos para productos: SKU, código de barras y alertas de stock.

SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='sku') = 0,
  'ALTER TABLE productos ADD COLUMN sku VARCHAR(80) NULL AFTER codigo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='codigo_barras') = 0,
  'ALTER TABLE productos ADD COLUMN codigo_barras VARCHAR(120) NULL AFTER sku',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='stock_minimo') = 0,
  'ALTER TABLE productos ADD COLUMN stock_minimo DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento_maximo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='stock_aviso') = 0,
  'ALTER TABLE productos ADD COLUMN stock_aviso DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER stock_minimo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
