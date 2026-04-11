SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='ordenes_compra' AND COLUMN_NAME='estado') = 1,
  "ALTER TABLE ordenes_compra MODIFY COLUMN estado ENUM('borrador','emitida','parcial','recibida','recepcionada','aprobada','rechazada','anulada') NOT NULL DEFAULT 'emitida'",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE ordenes_compra o
SET o.estado = 'recepcionada'
WHERE EXISTS (
  SELECT 1
  FROM recepciones_inventario r
  WHERE r.empresa_id = o.empresa_id
    AND r.orden_compra_id = o.id
)
AND o.estado <> 'rechazada'
AND o.estado <> 'anulada';
