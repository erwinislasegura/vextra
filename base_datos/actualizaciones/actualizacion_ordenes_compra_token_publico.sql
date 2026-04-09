-- Agrega token público para compartir órdenes de compra por enlace externo.
SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='ordenes_compra' AND COLUMN_NAME='token_publico') = 0,
  'ALTER TABLE ordenes_compra ADD COLUMN token_publico VARCHAR(128) NULL AFTER usuario_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='ordenes_compra' AND INDEX_NAME='idx_orden_compra_token_publico');
SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE ordenes_compra ADD INDEX idx_orden_compra_token_publico (token_publico)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
