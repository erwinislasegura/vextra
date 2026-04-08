-- Agrega configuración de moneda al POS por empresa.
SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='configuracion_pos' AND COLUMN_NAME='moneda') = 0,
  'ALTER TABLE configuracion_pos ADD COLUMN moneda ENUM("CLP","USD","EU") NOT NULL DEFAULT "CLP" AFTER cantidad_decimales',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE configuracion_pos SET moneda = 'CLP' WHERE moneda IS NULL OR moneda = '';
