CREATE TABLE IF NOT EXISTS catalogo_compras (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  flow_token VARCHAR(160) NOT NULL,
  commerce_order VARCHAR(120) NOT NULL,
  estado_pago ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL DEFAULT 'pendiente',
  estado_envio ENUM('pendiente','preparando','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  comprador_nombre VARCHAR(160) NOT NULL,
  comprador_correo VARCHAR(150) NOT NULL,
  comprador_telefono VARCHAR(80) NOT NULL,
  comprador_documento VARCHAR(80) NULL,
  comprador_empresa VARCHAR(180) NULL,
  envio_metodo ENUM('starken','blue_express','chile_express') NOT NULL DEFAULT 'starken',
  envio_direccion VARCHAR(220) NOT NULL,
  envio_referencia VARCHAR(220) NULL,
  envio_comuna VARCHAR(120) NOT NULL,
  envio_ciudad VARCHAR(120) NOT NULL,
  envio_region VARCHAR(120) NOT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  moneda VARCHAR(10) NOT NULL DEFAULT 'CLP',
  payload_flow JSON NULL,
  fecha_confirmacion_pago DATETIME NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uq_catalogo_compras_flow_token (flow_token),
  INDEX idx_catalogo_compras_empresa (empresa_id),
  INDEX idx_catalogo_compras_estado_pago (estado_pago),
  CONSTRAINT fk_catalogo_compras_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS catalogo_compra_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  compra_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  producto_nombre VARCHAR(180) NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  metadata JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_catalogo_compra_items_compra (compra_id),
  CONSTRAINT fk_catalogo_compra_items_compra FOREIGN KEY (compra_id) REFERENCES catalogo_compras(id) ON DELETE CASCADE
);

INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Compras por catálogo', 'modulo_compras_catalogo', 'Seguimiento de compras del catálogo con checkout Flow.', 'booleano', 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_compras_catalogo'
);

INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT
  pf_base.plan_id,
  f_compra.id AS funcionalidad_id,
  pf_base.activo,
  0 AS valor_numerico,
  pf_base.es_ilimitado,
  NOW()
FROM plan_funcionalidades pf_base
INNER JOIN funcionalidades f_base ON f_base.id = pf_base.funcionalidad_id
INNER JOIN funcionalidades f_compra ON f_compra.codigo_interno = 'modulo_compras_catalogo'
WHERE f_base.codigo_interno = 'modulo_catalogo_en_linea'
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  valor_numerico = VALUES(valor_numerico),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
