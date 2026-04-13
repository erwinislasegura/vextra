SET NAMES utf8mb4;

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios_reglas' AND COLUMN_NAME='cantidad_min') = 0,
  'ALTER TABLE listas_precios_reglas ADD COLUMN cantidad_min DECIMAL(12,2) NULL AFTER porcentaje',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='listas_precios_reglas' AND COLUMN_NAME='cantidad_max') = 0,
  'ALTER TABLE listas_precios_reglas ADD COLUMN cantidad_max DECIMAL(12,2) NULL AFTER cantidad_min',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

