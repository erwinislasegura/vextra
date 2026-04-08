-- Actualización incremental para soportar múltiples líneas con descuentos por línea y descuento global.
USE cotiza_saas;

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cotizaciones' AND COLUMN_NAME='descuento_tipo') = 0,
  "ALTER TABLE cotizaciones ADD COLUMN descuento_tipo ENUM('valor','porcentaje') NOT NULL DEFAULT 'valor' AFTER subtotal",
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cotizaciones' AND COLUMN_NAME='descuento_valor') = 0,
  "ALTER TABLE cotizaciones ADD COLUMN descuento_valor DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento_tipo",
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='items_cotizacion' AND COLUMN_NAME='descuento_tipo') = 0,
  "ALTER TABLE items_cotizacion ADD COLUMN descuento_tipo ENUM('valor','porcentaje') NOT NULL DEFAULT 'valor' AFTER precio_unitario",
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='items_cotizacion' AND COLUMN_NAME='descuento_valor') = 0,
  "ALTER TABLE items_cotizacion ADD COLUMN descuento_valor DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento_tipo",
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='items_cotizacion' AND COLUMN_NAME='descuento_monto') = 0,
  "ALTER TABLE items_cotizacion ADD COLUMN descuento_monto DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento_valor",
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
