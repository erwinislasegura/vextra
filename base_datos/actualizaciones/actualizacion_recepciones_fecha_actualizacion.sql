SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='recepciones_inventario' AND COLUMN_NAME='fecha_actualizacion') = 0,
  'ALTER TABLE recepciones_inventario ADD COLUMN fecha_actualizacion DATETIME NULL AFTER fecha_creacion',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
