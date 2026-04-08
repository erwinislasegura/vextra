-- Módulo POS comercial: cajas, ventas, pagos, cierres y stock operativo.
SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='productos' AND COLUMN_NAME='stock_actual') = 0,
  'ALTER TABLE productos ADD COLUMN stock_actual DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER stock_aviso',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS cajas_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  codigo VARCHAR(60) NOT NULL,
  estado ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uq_caja_empresa_codigo (empresa_id, codigo),
  INDEX idx_cajas_pos_empresa (empresa_id),
  CONSTRAINT fk_cajas_pos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS aperturas_caja_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  caja_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  monto_inicial DECIMAL(12,2) NOT NULL DEFAULT 0,
  observacion VARCHAR(255) NULL,
  fecha_apertura DATETIME NOT NULL,
  estado ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  INDEX idx_aperturas_empresa_estado (empresa_id, estado),
  CONSTRAINT fk_aperturas_caja_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_aperturas_caja_pos FOREIGN KEY (caja_id) REFERENCES cajas_pos(id),
  CONSTRAINT fk_aperturas_usuario_pos FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS cierres_caja_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  apertura_caja_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  monto_esperado DECIMAL(12,2) NOT NULL,
  monto_contado DECIMAL(12,2) NOT NULL,
  diferencia DECIMAL(12,2) NOT NULL,
  observacion VARCHAR(255) NULL,
  fecha_cierre DATETIME NOT NULL,
  monto_efectivo DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_transferencia DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_tarjeta DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_inicial DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX idx_cierres_empresa (empresa_id),
  CONSTRAINT fk_cierres_empresa_pos FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_cierres_apertura_pos FOREIGN KEY (apertura_caja_id) REFERENCES aperturas_caja_pos(id),
  CONSTRAINT fk_cierres_usuario_pos FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS ventas_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  caja_id BIGINT UNSIGNED NOT NULL,
  apertura_caja_id BIGINT UNSIGNED NOT NULL,
  cliente_id BIGINT UNSIGNED NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  tipo_venta ENUM('registrada','rapida') NOT NULL DEFAULT 'rapida',
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado ENUM('pagada','anulada') NOT NULL DEFAULT 'pagada',
  numero_venta VARCHAR(80) NOT NULL,
  fecha_venta DATETIME NOT NULL,
  observaciones TEXT NULL,
  monto_recibido DECIMAL(12,2) NOT NULL DEFAULT 0,
  vuelto DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX idx_ventas_pos_empresa_fecha (empresa_id, fecha_venta),
  UNIQUE KEY uq_ventas_pos_numero_empresa (empresa_id, numero_venta),
  CONSTRAINT fk_ventas_pos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_ventas_pos_caja FOREIGN KEY (caja_id) REFERENCES cajas_pos(id),
  CONSTRAINT fk_ventas_pos_apertura FOREIGN KEY (apertura_caja_id) REFERENCES aperturas_caja_pos(id),
  CONSTRAINT fk_ventas_pos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_ventas_pos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS items_venta_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  venta_pos_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  codigo_producto VARCHAR(80) NULL,
  nombre_producto VARCHAR(180) NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL DEFAULT 0,
  precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(12,2) NOT NULL DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX idx_items_venta_pos_venta (venta_pos_id),
  CONSTRAINT fk_items_venta_pos_venta FOREIGN KEY (venta_pos_id) REFERENCES ventas_pos(id),
  CONSTRAINT fk_items_venta_pos_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS pagos_venta_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  venta_pos_id BIGINT UNSIGNED NOT NULL,
  metodo_pago ENUM('efectivo','transferencia','tarjeta') NOT NULL,
  monto DECIMAL(12,2) NOT NULL DEFAULT 0,
  referencia VARCHAR(120) NULL,
  fecha_pago DATETIME NOT NULL,
  INDEX idx_pagos_venta_pos_venta (venta_pos_id),
  CONSTRAINT fk_pagos_venta_pos_venta FOREIGN KEY (venta_pos_id) REFERENCES ventas_pos(id)
);

CREATE TABLE IF NOT EXISTS movimientos_caja_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  caja_id BIGINT UNSIGNED NOT NULL,
  apertura_caja_id BIGINT UNSIGNED NOT NULL,
  tipo_movimiento ENUM('ingreso_venta','egreso_manual','ingreso_manual') NOT NULL,
  concepto VARCHAR(255) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  fecha_movimiento DATETIME NOT NULL,
  venta_pos_id BIGINT UNSIGNED NULL,
  INDEX idx_movimientos_caja_pos (empresa_id, fecha_movimiento),
  CONSTRAINT fk_mov_caja_empresa_pos FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_mov_caja_pos FOREIGN KEY (caja_id) REFERENCES cajas_pos(id),
  CONSTRAINT fk_mov_apertura_pos FOREIGN KEY (apertura_caja_id) REFERENCES aperturas_caja_pos(id),
  CONSTRAINT fk_mov_usuario_pos FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_mov_venta_pos FOREIGN KEY (venta_pos_id) REFERENCES ventas_pos(id)
);

CREATE TABLE IF NOT EXISTS movimientos_inventario_pos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  venta_pos_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  tipo_movimiento ENUM('salida_venta','ajuste_manual') NOT NULL DEFAULT 'salida_venta',
  cantidad DECIMAL(12,2) NOT NULL,
  stock_anterior DECIMAL(12,2) NOT NULL,
  stock_actual DECIMAL(12,2) NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  fecha_movimiento DATETIME NOT NULL,
  INDEX idx_mov_inv_pos_empresa (empresa_id, fecha_movimiento),
  CONSTRAINT fk_mov_inv_pos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_mov_inv_pos_venta FOREIGN KEY (venta_pos_id) REFERENCES ventas_pos(id),
  CONSTRAINT fk_mov_inv_pos_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_mov_inv_pos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS configuracion_pos (
  empresa_id BIGINT UNSIGNED PRIMARY KEY,
  permitir_venta_sin_stock TINYINT(1) NOT NULL DEFAULT 0,
  impuesto_por_defecto DECIMAL(8,2) NOT NULL DEFAULT 0,
  usar_decimales TINYINT(1) NOT NULL DEFAULT 1,
  cantidad_decimales TINYINT UNSIGNED NOT NULL DEFAULT 2,
  fecha_actualizacion DATETIME NULL,
  CONSTRAINT fk_configuracion_pos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);


SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='configuracion_pos' AND COLUMN_NAME='usar_decimales') = 0,
  'ALTER TABLE configuracion_pos ADD COLUMN usar_decimales TINYINT(1) NOT NULL DEFAULT 1 AFTER impuesto_por_defecto',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='configuracion_pos' AND COLUMN_NAME='cantidad_decimales') = 0,
  'ALTER TABLE configuracion_pos ADD COLUMN cantidad_decimales TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER usar_decimales',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
