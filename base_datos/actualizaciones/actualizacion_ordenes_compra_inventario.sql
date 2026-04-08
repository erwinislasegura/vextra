-- Módulo de órdenes de compra integrado al flujo de inventario.
SET @db_name = DATABASE();

CREATE TABLE IF NOT EXISTS ordenes_compra (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  proveedor_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(80) NOT NULL,
  fecha_emision DATE NOT NULL,
  fecha_entrega_estimada DATE NULL,
  estado ENUM('borrador','emitida','parcial','recibida','anulada') NOT NULL DEFAULT 'emitida',
  referencia VARCHAR(120) NULL,
  observacion TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  INDEX idx_orden_compra_empresa (empresa_id, fecha_creacion),
  CONSTRAINT fk_orden_compra_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_orden_compra_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores_inventario(id),
  CONSTRAINT fk_orden_compra_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS ordenes_compra_detalle (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orden_compra_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orden_compra_detalle (orden_compra_id),
  CONSTRAINT fk_orden_detalle_orden FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id),
  CONSTRAINT fk_orden_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='recepciones_inventario' AND COLUMN_NAME='orden_compra_id') = 0,
  'ALTER TABLE recepciones_inventario ADD COLUMN orden_compra_id BIGINT UNSIGNED NULL AFTER proveedor_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='recepciones_inventario' AND CONSTRAINT_NAME='fk_recepciones_orden_compra');
SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE recepciones_inventario ADD CONSTRAINT fk_recepciones_orden_compra FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
