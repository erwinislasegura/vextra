USE cotiza_saas;

ALTER TABLE empresas
  ADD COLUMN IF NOT EXISTS flow_customer_id VARCHAR(120) NULL AFTER plan_id,
  ADD COLUMN IF NOT EXISTS flow_medio_pago_registrado TINYINT(1) NOT NULL DEFAULT 0 AFTER flow_customer_id,
  ADD COLUMN IF NOT EXISTS flow_estado_registro VARCHAR(40) NULL AFTER flow_medio_pago_registrado,
  ADD COLUMN IF NOT EXISTS flow_ultima_sincronizacion DATETIME NULL AFTER flow_estado_registro;

ALTER TABLE planes
  ADD COLUMN IF NOT EXISTS flow_plan_id_mensual VARCHAR(120) NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS flow_plan_id_anual VARCHAR(120) NULL AFTER flow_plan_id_mensual,
  ADD COLUMN IF NOT EXISTS flow_sincronizado TINYINT(1) NOT NULL DEFAULT 0 AFTER flow_plan_id_anual,
  ADD COLUMN IF NOT EXISTS flow_ultima_sincronizacion DATETIME NULL AFTER flow_sincronizado,
  ADD COLUMN IF NOT EXISTS flow_dias_prueba SMALLINT NOT NULL DEFAULT 0 AFTER flow_ultima_sincronizacion,
  ADD COLUMN IF NOT EXISTS flow_dias_cobro SMALLINT NOT NULL DEFAULT 3 AFTER flow_dias_prueba;

ALTER TABLE suscripciones
  ADD COLUMN IF NOT EXISTS flow_subscription_id VARCHAR(120) NULL AFTER plan_id,
  ADD COLUMN IF NOT EXISTS flow_plan_id VARCHAR(120) NULL AFTER flow_subscription_id,
  ADD COLUMN IF NOT EXISTS flow_estado VARCHAR(60) NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS entorno_flow ENUM('sandbox','produccion') NULL AFTER flow_estado,
  ADD COLUMN IF NOT EXISTS proxima_renovacion DATETIME NULL AFTER fecha_vencimiento,
  ADD COLUMN IF NOT EXISTS fecha_cancelacion DATETIME NULL AFTER proxima_renovacion,
  ADD COLUMN IF NOT EXISTS tipo_cobro ENUM('mensual','anual') NOT NULL DEFAULT 'mensual' AFTER renovacion_automatica,
  ADD COLUMN IF NOT EXISTS historial_eventos_json JSON NULL AFTER observaciones;

ALTER TABLE pagos
  ADD COLUMN IF NOT EXISTS flow_payment_id VARCHAR(120) NULL AFTER referencia_externa,
  ADD COLUMN IF NOT EXISTS flow_token VARCHAR(140) NULL AFTER flow_payment_id,
  ADD COLUMN IF NOT EXISTS flow_status VARCHAR(60) NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS entorno_flow ENUM('sandbox','produccion') NULL AFTER flow_status,
  ADD COLUMN IF NOT EXISTS commerce_order VARCHAR(120) NULL AFTER entorno_flow,
  ADD COLUMN IF NOT EXISTS fecha_confirmacion DATETIME NULL AFTER fecha_pago,
  ADD COLUMN IF NOT EXISTS payload_request JSON NULL AFTER payload,
  ADD COLUMN IF NOT EXISTS payload_response JSON NULL AFTER payload_request;

CREATE TABLE IF NOT EXISTS flow_configuracion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activo TINYINT(1) NOT NULL DEFAULT 0,
  entorno ENUM('sandbox','produccion') NOT NULL DEFAULT 'sandbox',
  api_key VARCHAR(180) NULL,
  secret_key_enc TEXT NULL,
  base_url VARCHAR(255) NULL,
  habilitar_pagos_unicos TINYINT(1) NOT NULL DEFAULT 1,
  habilitar_suscripciones TINYINT(1) NOT NULL DEFAULT 1,
  url_confirmacion VARCHAR(255) NULL,
  url_retorno VARCHAR(255) NULL,
  url_webhook_pago VARCHAR(255) NULL,
  url_webhook_suscripcion VARCHAR(255) NULL,
  url_retorno_registro VARCHAR(255) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  INDEX idx_flow_config_entorno (entorno)
);

CREATE TABLE IF NOT EXISTS flow_clientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  flow_customer_id VARCHAR(120) NOT NULL,
  correo VARCHAR(190) NOT NULL,
  nombre VARCHAR(190) NOT NULL,
  estado_local VARCHAR(40) NOT NULL DEFAULT 'pendiente',
  estado_flow VARCHAR(40) NULL,
  token_registro VARCHAR(160) NULL,
  url_registro VARCHAR(255) NULL,
  medio_pago_registrado TINYINT(1) NOT NULL DEFAULT 0,
  payload_request JSON NULL,
  payload_response JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  UNIQUE KEY uk_flow_cliente_empresa (empresa_id),
  UNIQUE KEY uk_flow_customer_id (flow_customer_id),
  CONSTRAINT fk_flow_cliente_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE IF NOT EXISTS flow_planes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id BIGINT UNSIGNED NOT NULL,
  modalidad ENUM('mensual','anual') NOT NULL,
  flow_plan_id VARCHAR(120) NOT NULL,
  moneda VARCHAR(10) NOT NULL DEFAULT 'CLP',
  monto DECIMAL(12,2) NOT NULL,
  intervalo SMALLINT NOT NULL DEFAULT 3,
  intervalo_cantidad SMALLINT NOT NULL DEFAULT 1,
  dias_trial SMALLINT NOT NULL DEFAULT 0,
  dias_vencimiento SMALLINT NOT NULL DEFAULT 3,
  periodos SMALLINT NULL,
  estado_local VARCHAR(40) NOT NULL DEFAULT 'activo',
  estado_flow VARCHAR(40) NULL,
  entorno_flow ENUM('sandbox','produccion') NOT NULL DEFAULT 'sandbox',
  payload_request JSON NULL,
  payload_response JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uk_flow_plan_modalidad (plan_id, modalidad),
  UNIQUE KEY uk_flow_plan_id (flow_plan_id),
  CONSTRAINT fk_flow_plan_plan FOREIGN KEY (plan_id) REFERENCES planes(id)
);

CREATE TABLE IF NOT EXISTS flow_suscripciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  suscripcion_id BIGINT UNSIGNED NULL,
  empresa_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  flow_customer_id VARCHAR(120) NOT NULL,
  flow_plan_id VARCHAR(120) NOT NULL,
  flow_subscription_id VARCHAR(120) NOT NULL,
  tipo_cobro ENUM('mensual','anual') NOT NULL DEFAULT 'mensual',
  estado_local VARCHAR(40) NOT NULL DEFAULT 'pendiente',
  estado_flow VARCHAR(60) NULL,
  entorno_flow ENUM('sandbox','produccion') NOT NULL DEFAULT 'sandbox',
  fecha_inicio DATETIME NULL,
  fecha_vencimiento DATETIME NULL,
  proxima_renovacion DATETIME NULL,
  fecha_cancelacion DATETIME NULL,
  observaciones TEXT NULL,
  payload_request JSON NULL,
  payload_response JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uk_flow_subscription_id (flow_subscription_id),
  INDEX idx_flow_sub_empresa (empresa_id),
  CONSTRAINT fk_flow_sub_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_flow_sub_plan FOREIGN KEY (plan_id) REFERENCES planes(id),
  CONSTRAINT fk_flow_sub_suscripcion FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id)
);

CREATE TABLE IF NOT EXISTS flow_pagos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pago_id BIGINT UNSIGNED NULL,
  suscripcion_id BIGINT UNSIGNED NULL,
  empresa_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NULL,
  tipo_pago ENUM('unico','recurrente') NOT NULL DEFAULT 'unico',
  commerce_order VARCHAR(120) NOT NULL,
  flow_token VARCHAR(160) NULL,
  flow_order BIGINT NULL,
  flow_payment_id VARCHAR(120) NULL,
  estado_local VARCHAR(40) NOT NULL DEFAULT 'pendiente',
  estado_flow VARCHAR(60) NULL,
  monto DECIMAL(12,2) NOT NULL,
  moneda VARCHAR(12) NOT NULL DEFAULT 'CLP',
  entorno_flow ENUM('sandbox','produccion') NOT NULL DEFAULT 'sandbox',
  fecha_confirmacion DATETIME NULL,
  observaciones TEXT NULL,
  payload_request JSON NULL,
  payload_response JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uk_flow_pago_commerce (commerce_order),
  INDEX idx_flow_pago_empresa (empresa_id),
  CONSTRAINT fk_flow_pago_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_flow_pago_plan FOREIGN KEY (plan_id) REFERENCES planes(id),
  CONSTRAINT fk_flow_pago_suscripcion FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id),
  CONSTRAINT fk_flow_pago_pago FOREIGN KEY (pago_id) REFERENCES pagos(id)
);

CREATE TABLE IF NOT EXISTS flow_webhooks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_evento VARCHAR(80) NOT NULL,
  token VARCHAR(160) NULL,
  hash_unico VARCHAR(200) NOT NULL,
  payload JSON NULL,
  procesado TINYINT(1) NOT NULL DEFAULT 0,
  resultado VARCHAR(120) NULL,
  error_detalle TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_procesamiento DATETIME NULL,
  UNIQUE KEY uk_flow_webhook_hash (hash_unico),
  INDEX idx_flow_webhook_token (token)
);

CREATE TABLE IF NOT EXISTS flow_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NULL,
  admin_usuario_id BIGINT UNSIGNED NULL,
  tipo VARCHAR(80) NOT NULL,
  nivel ENUM('info','warning','error') NOT NULL DEFAULT 'info',
  mensaje VARCHAR(255) NOT NULL,
  referencia VARCHAR(180) NULL,
  payload JSON NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_flow_logs_empresa (empresa_id),
  INDEX idx_flow_logs_tipo (tipo),
  CONSTRAINT fk_flow_logs_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_flow_logs_admin FOREIGN KEY (admin_usuario_id) REFERENCES usuarios(id)
);

INSERT INTO flow_configuracion (activo, entorno, api_key, secret_key_enc, base_url, habilitar_pagos_unicos, habilitar_suscripciones)
SELECT 0, 'produccion', '484DFD4D-0A41-424D-A573-95BDAF374LD4', 'NDQ0YTdiZjdiM2JhNGMzYTg3MDhjZTlkN2JlMjQxMjIzNjA0ZWU5Ng==', 'https://www.flow.cl/api', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM flow_configuracion);

UPDATE flow_configuracion
SET
  api_key = COALESCE(NULLIF(api_key, ''), '484DFD4D-0A41-424D-A573-95BDAF374LD4'),
  secret_key_enc = COALESCE(NULLIF(secret_key_enc, ''), 'NDQ0YTdiZjdiM2JhNGMzYTg3MDhjZTlkN2JlMjQxMjIzNjA0ZWU5Ng=='),
  base_url = COALESCE(NULLIF(base_url, ''), CASE WHEN entorno = 'produccion' THEN 'https://www.flow.cl/api' ELSE 'https://sandbox.flow.cl/api' END),
  fecha_actualizacion = NOW()
WHERE id = (SELECT id FROM (SELECT id FROM flow_configuracion ORDER BY id DESC LIMIT 1) cfg);
