SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cotizaciones' AND COLUMN_NAME='orden_compra_origen_id') = 0,
  'ALTER TABLE cotizaciones ADD COLUMN orden_compra_origen_id BIGINT UNSIGNED NULL AFTER lista_precio_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=@db_name
    AND TABLE_NAME='cotizaciones'
    AND INDEX_NAME='idx_cotizaciones_orden_origen'
);
SET @sql = IF(
  @idx_exists = 0,
  'ALTER TABLE cotizaciones ADD INDEX idx_cotizaciones_orden_origen (empresa_id, orden_compra_origen_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
