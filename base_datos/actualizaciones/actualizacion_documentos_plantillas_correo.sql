-- Crea la tabla de plantillas de documentos/correos si no existe.
USE cotiza_saas;

CREATE TABLE IF NOT EXISTS documentos_plantillas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  tipo_documento VARCHAR(80) NOT NULL DEFAULT 'cotizacion',
  terminos_defecto TEXT NULL,
  observaciones_defecto TEXT NULL,
  firma VARCHAR(180) NULL,
  logo VARCHAR(255) NULL,
  pie_documento VARCHAR(255) NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documentos_empresa (empresa_id),
  CONSTRAINT fk_documentos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);
