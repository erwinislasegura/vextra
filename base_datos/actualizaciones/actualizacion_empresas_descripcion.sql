SET @existe_columna := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'empresas'
    AND COLUMN_NAME = 'descripcion'
);

SET @sql := IF(
  @existe_columna = 0,
  'ALTER TABLE empresas ADD COLUMN descripcion TEXT NULL AFTER pais',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
