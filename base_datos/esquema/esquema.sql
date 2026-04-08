CREATE DATABASE IF NOT EXISTS cotiza_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cotiza_saas;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  codigo VARCHAR(80) NOT NULL UNIQUE,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permisos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  codigo VARCHAR(120) NOT NULL UNIQUE,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE roles_permisos (
  rol_id BIGINT UNSIGNED NOT NULL,
  permiso_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (rol_id, permiso_id),
  CONSTRAINT fk_rp_rol FOREIGN KEY (rol_id) REFERENCES roles(id),
  CONSTRAINT fk_rp_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id)
);

CREATE TABLE planes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  descripcion_comercial TEXT NULL,
  precio_mensual DECIMAL(12,2) NOT NULL DEFAULT 0,
  precio_anual DECIMAL(12,2) NOT NULL DEFAULT 0,
  duracion_dias INT NOT NULL DEFAULT 30,
  visible TINYINT(1) NOT NULL DEFAULT 1,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  orden_visualizacion INT NOT NULL DEFAULT 0,
  insignia VARCHAR(60) NULL,
  resumen_comercial VARCHAR(255) NULL,
  color_visual VARCHAR(20) NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL
);

CREATE TABLE empresas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razon_social VARCHAR(150) NOT NULL,
  nombre_comercial VARCHAR(150) NOT NULL,
  identificador_fiscal VARCHAR(80) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  telefono VARCHAR(60) NULL,
  direccion VARCHAR(200) NULL,
  ciudad VARCHAR(120) NULL,
  pais VARCHAR(120) NULL,
  logo VARCHAR(255) NULL,
  imap_host VARCHAR(180) NULL,
  imap_port SMALLINT UNSIGNED NULL,
  imap_encryption ENUM('ssl','tls','none') NULL,
  imap_usuario VARCHAR(180) NULL,
  imap_password VARCHAR(255) NULL,
  imap_remitente_correo VARCHAR(180) NULL,
  imap_remitente_nombre VARCHAR(180) NULL,
  estado ENUM('activa','suspendida','vencida','cancelada') NOT NULL DEFAULT 'activa',
  fecha_activacion DATE NULL,
  plan_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  INDEX idx_empresas_plan_id (plan_id),
  CONSTRAINT fk_empresas_plan FOREIGN KEY (plan_id) REFERENCES planes(id)
);

CREATE TABLE usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NULL,
  rol_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(80) NULL,
  cargo VARCHAR(120) NULL,
  biografia TEXT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  ultimo_acceso DATETIME NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  INDEX idx_usuarios_empresa (empresa_id),
  CONSTRAINT fk_usuarios_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE funcionalidades (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  codigo_interno VARCHAR(120) NOT NULL UNIQUE,
  descripcion TEXT NULL,
  tipo_valor ENUM('booleano','numerico','ilimitado') NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL
);

CREATE TABLE plan_funcionalidades (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id BIGINT UNSIGNED NOT NULL,
  funcionalidad_id BIGINT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  valor_numerico INT NOT NULL DEFAULT 0,
  es_ilimitado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uq_plan_funcionalidad (plan_id, funcionalidad_id),
  CONSTRAINT fk_pf_plan FOREIGN KEY (plan_id) REFERENCES planes(id),
  CONSTRAINT fk_pf_funcionalidad FOREIGN KEY (funcionalidad_id) REFERENCES funcionalidades(id)
);

CREATE TABLE suscripciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  estado ENUM('activa','pendiente','por_vencer','vencida','suspendida','cancelada') NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  renovacion_automatica TINYINT(1) NOT NULL DEFAULT 0,
  observaciones TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  INDEX idx_suscripciones_empresa (empresa_id),
  INDEX idx_suscripciones_estado (estado),
  CONSTRAINT fk_suscripciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_suscripciones_plan FOREIGN KEY (plan_id) REFERENCES planes(id)
);

CREATE TABLE historial_suscripciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  suscripcion_id BIGINT UNSIGNED NOT NULL,
  accion VARCHAR(120) NOT NULL,
  observaciones TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_historial_suscripcion FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id)
);

CREATE TABLE pagos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  suscripcion_id BIGINT UNSIGNED NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  moneda VARCHAR(10) NOT NULL DEFAULT 'USD',
  metodo VARCHAR(60) NULL,
  estado ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL,
  referencia_externa VARCHAR(120) NULL,
  observaciones TEXT NULL,
  payload JSON NULL,
  fecha_pago DATETIME NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pagos_empresa (empresa_id),
  CONSTRAINT fk_pagos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_pagos_suscripcion FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id)
);

CREATE TABLE logs_pagos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pago_id BIGINT UNSIGNED NULL,
  tipo_evento VARCHAR(120) NOT NULL,
  payload JSON NULL,
  respuesta TEXT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_pagos_pago FOREIGN KEY (pago_id) REFERENCES pagos(id)
);

CREATE TABLE clientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  razon_social VARCHAR(180) NULL,
  nombre_comercial VARCHAR(180) NULL,
  identificador_fiscal VARCHAR(80) NULL,
  giro VARCHAR(180) NULL,
  correo VARCHAR(150) NULL,
  telefono VARCHAR(80) NULL,
  direccion VARCHAR(220) NULL,
  ciudad VARCHAR(120) NULL,
  vendedor_id BIGINT UNSIGNED NULL,
  notas TEXT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  INDEX idx_clientes_empresa (empresa_id),
  CONSTRAINT fk_clientes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE contactos_cliente (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  cargo VARCHAR(120) NULL,
  correo VARCHAR(150) NULL,
  telefono VARCHAR(80) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_contactos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

CREATE TABLE categorias_productos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_categorias_empresa (empresa_id),
  CONSTRAINT fk_categorias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE productos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  categoria_id BIGINT UNSIGNED NULL,
  tipo ENUM('producto','servicio') NOT NULL DEFAULT 'producto',
  codigo VARCHAR(60) NOT NULL,
  sku VARCHAR(80) NULL,
  codigo_barras VARCHAR(120) NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  unidad VARCHAR(40) NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0,
  costo DECIMAL(12,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(8,2) NOT NULL DEFAULT 0,
  descuento_maximo DECIMAL(8,2) NOT NULL DEFAULT 0,
  stock_minimo DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_aviso DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  INDEX idx_productos_empresa (empresa_id),
  CONSTRAINT fk_productos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_productos(id)
);

CREATE TABLE cotizaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  cliente_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(60) NOT NULL,
  consecutivo INT NOT NULL,
  estado ENUM('borrador','enviada','aprobada','rechazada','vencida','anulada') NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  descuento_tipo ENUM('valor','porcentaje') NOT NULL DEFAULT 'valor',
  descuento_valor DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,
  observaciones TEXT NULL,
  terminos_condiciones TEXT NULL,
  lista_precio_id BIGINT UNSIGNED NULL,
  token_publico CHAR(64) NULL,
  firma_cliente MEDIUMTEXT NULL,
  nombre_firmante_cliente VARCHAR(180) NULL,
  fecha_aprobacion_cliente DATETIME NULL,
  fecha_emision DATE NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  fecha_eliminacion DATETIME NULL,
  UNIQUE KEY uq_cot_num_empresa (empresa_id, numero),
  UNIQUE KEY uq_cot_token_publico (token_publico),
  INDEX idx_cotizaciones_empresa (empresa_id),
  CONSTRAINT fk_cotizaciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_cotizaciones_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_cotizaciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE items_cotizacion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NULL,
  descripcion VARCHAR(255) NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL,
  descuento_tipo ENUM('valor','porcentaje') NOT NULL DEFAULT 'valor',
  descuento_valor DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento_monto DECIMAL(12,2) NOT NULL DEFAULT 0,
  porcentaje_impuesto DECIMAL(8,2) NOT NULL DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_items_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
  CONSTRAINT fk_items_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE historial_estados_cotizacion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id BIGINT UNSIGNED NOT NULL,
  estado VARCHAR(60) NOT NULL,
  observaciones TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_historial_estado_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
  CONSTRAINT fk_historial_estado_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE logs_correos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  destinatario VARCHAR(160) NOT NULL,
  asunto VARCHAR(180) NOT NULL,
  plantilla VARCHAR(120) NOT NULL,
  payload JSON NULL,
  estado VARCHAR(40) NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs_actividad (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NULL,
  usuario_id BIGINT UNSIGNED NULL,
  modulo VARCHAR(120) NOT NULL,
  accion VARCHAR(120) NOT NULL,
  detalle TEXT NULL,
  ip VARCHAR(45) NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE restablecimientos_contrasena (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(120) NOT NULL,
  expiracion DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_restablecimientos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE configuraciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(120) NOT NULL UNIQUE,
  valor TEXT NULL,
  descripcion VARCHAR(255) NULL,
  fecha_actualizacion DATETIME NULL
);

CREATE TABLE configuraciones_empresa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id BIGINT UNSIGNED NOT NULL,
  clave VARCHAR(120) NOT NULL,
  valor TEXT NULL,
  fecha_actualizacion DATETIME NULL,
  UNIQUE KEY uq_conf_empresa (empresa_id, clave),
  CONSTRAINT fk_conf_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE documentos_plantillas (
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
