CREATE TABLE IF NOT EXISTS flow_configuraciones_empresa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  api_key VARCHAR(180) NOT NULL,
  secret_key_enc TEXT NOT NULL,
  entorno ENUM('sandbox','produccion') NOT NULL DEFAULT 'sandbox',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  base_url VARCHAR(255) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uq_flow_config_empresa (empresa_id),
  CONSTRAINT fk_flow_config_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);
