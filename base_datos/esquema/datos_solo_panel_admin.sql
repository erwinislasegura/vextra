-- Ejecutar sobre la base de datos seleccionada en la sesión actual.

START TRANSACTION;

-- Roles del panel
INSERT INTO roles (nombre, codigo) VALUES
('Superadministrador', 'superadministrador'),
('Administrador', 'administrador_empresa'),
('Vendedor', 'vendedor'),
('Administrativo', 'administrativo'),
('Contabilidad', 'contabilidad'),
('Supervisor Comercial', 'supervisor_comercial'),
('Operaciones', 'operaciones'),
('Usuario de Empresa', 'usuario_empresa')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Permisos del panel
INSERT INTO permisos (nombre, codigo) VALUES
('Acceso panel admin', 'panel_admin'),
('Gestión planes', 'planes'),
('Gestión funcionalidades', 'funcionalidades'),
('Gestión suscripciones', 'suscripciones'),
('Gestión clientes', 'clientes'),
('Gestión productos', 'productos'),
('Gestión cotizaciones', 'cotizaciones'),
('Panel empresa', 'panel_empresa')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Funcionalidades administrativas/comerciales base
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado) VALUES
('Máximo usuarios','maximo_usuarios','Límite de usuarios por empresa','numerico','activo'),
('Máximo clientes','maximo_clientes','Límite de clientes por empresa','numerico','activo'),
('Máximo productos','maximo_productos','Límite de productos por empresa','numerico','activo'),
('Máximo cotizaciones mes','maximo_cotizaciones_mes','Límite mensual de cotizaciones','numerico','activo'),
('Cotización PDF','cotizacion_pdf','Permite generar PDF','booleano','activo'),
('Cotización por correo','cotizacion_correo','Permite enviar por correo','booleano','activo'),
('Logo personalizado','logo_personalizado','Permite subir logo','booleano','activo'),
('Reportes','reportes','Acceso a reportes avanzados','booleano','activo'),
('Plantillas personalizadas','plantillas_personalizadas','Permite personalización de plantillas','booleano','activo'),
('Acceso API','acceso_api','Habilita API','booleano','activo'),
('Soporte prioritario','soporte_prioritario','Soporte preferencial','booleano','activo'),
('Módulo clientes', 'modulo_clientes', 'Habilita gestión de clientes', 'booleano', 'activo'),
('Módulo productos', 'modulo_productos', 'Habilita gestión de productos', 'booleano', 'activo'),
('Módulo cotizaciones', 'modulo_cotizaciones', 'Habilita cotizaciones', 'booleano', 'activo'),
('Módulo POS', 'modulo_pos', 'Habilita punto de venta', 'booleano', 'activo'),
('Módulo inventario', 'modulo_inventario', 'Habilita inventario', 'booleano', 'activo'),
('Módulo recepciones', 'modulo_recepciones', 'Recepciones de inventario', 'booleano', 'activo'),
('Módulo ajustes', 'modulo_ajustes', 'Ajustes de inventario', 'booleano', 'activo'),
('Módulo movimientos', 'modulo_movimientos', 'Movimientos de inventario', 'booleano', 'activo'),
('Módulo vendedores', 'modulo_vendedores', 'Gestión de vendedores', 'booleano', 'activo'),
('Módulo catálogo en línea', 'modulo_catalogo_en_linea', 'Landing pública de catálogo con filtros, carrito y checkout Flow.', 'booleano', 'activo'),
('Módulo reportes', 'modulo_reportes', 'Acceso a reportes', 'booleano', 'activo'),
('Módulo listas de precios', 'modulo_listas_precios', 'Listas de precios', 'booleano', 'activo'),
('Módulo órdenes de compra', 'modulo_ordenes_compra', 'Órdenes de compra', 'booleano', 'activo'),
('Módulo usuarios', 'modulo_usuarios', 'Administración de usuarios', 'booleano', 'activo'),
('Módulo contactos', 'modulo_contactos', 'Gestión de contactos comerciales vinculados a clientes.', 'booleano', 'activo'),
('Módulo categorías', 'modulo_categorias', 'Clasificación de productos por categorías.', 'booleano', 'activo'),
('Módulo seguimiento', 'modulo_seguimiento', 'Seguimiento de oportunidades y actividades comerciales.', 'booleano', 'activo'),
('Módulo aprobaciones', 'modulo_aprobaciones', 'Flujos de aprobación para operaciones comerciales.', 'booleano', 'activo'),
('Módulo documentos', 'modulo_documentos', 'Gestión de plantillas y documentos comerciales.', 'booleano', 'activo'),
('Módulo configuración', 'modulo_configuracion', 'Configuración general de la empresa.', 'booleano', 'activo'),
('Módulo checkout Flow', 'modulo_checkout_flow', 'Checkout de pagos Flow para compartir links de cobro.', 'booleano', 'activo'),
('Módulo correos stock', 'modulo_correos_stock', 'Alertas y configuración de correos de stock.', 'booleano', 'activo'),
('Módulo notificaciones', 'modulo_notificaciones', 'Notificaciones operativas y comerciales del sistema.', 'booleano', 'activo'),
('Módulo historial', 'modulo_historial', 'Historial y auditoría de actividad operativa.', 'booleano', 'activo'),
('Exportar clientes a Excel', 'clientes_exportar_excel', 'Permite exportar el listado de clientes a archivo Excel.', 'booleano', 'activo'),
('Gestión de listas de precios por cliente', 'clientes_gestion_listas_precios', 'Permite asignar listas de precios por cliente.', 'booleano', 'activo'),
('Asignación de vendedor por cliente', 'clientes_asignar_vendedor', 'Permite asociar vendedores responsables por cliente.', 'booleano', 'activo')
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  tipo_valor = VALUES(tipo_valor),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

-- Planes del panel administrador
INSERT INTO planes (nombre, slug, descripcion_comercial, precio_mensual, descuento_anual_pct, precio_anual, duracion_dias, visible, destacado, recomendado, orden_visualizacion, insignia, resumen_comercial, color_visual, maximo_usuarios, usuarios_ilimitados, observaciones_internas, estado)
VALUES
('Básico', 'basico', 'Plan funcional y limitado para iniciar.', 15000, 10, 162000, 30, 1, 0, 0, 1, 'Inicial', 'Incluye cotizaciones, clientes, productos y POS básico.', '#3b82f6', 2, 0, 'Límite bajo para incentivar upgrade.', 'activo'),
('Profesional', 'profesional', 'Plan recomendado para operación comercial diaria.', 26000, 10, 280800, 30, 1, 1, 1, 2, 'Más elegido', 'Incluye inventario completo y reportes operativos.', '#0ea5a4', 8, 0, 'Balance entre costo y capacidad.', 'activo'),
('Empresa', 'empresa', 'Plan completo para operación avanzada.', 55000, 15, 561000, 30, 1, 1, 0, 3, 'Escalable', 'Todas las funciones del sistema con alta capacidad.', '#7c3aed', 0, 1, 'Usuarios ilimitados con expansión corporativa.', 'activo')
ON DUPLICATE KEY UPDATE
  descripcion_comercial = VALUES(descripcion_comercial),
  precio_mensual = VALUES(precio_mensual),
  descuento_anual_pct = VALUES(descuento_anual_pct),
  precio_anual = VALUES(precio_anual),
  destacado = VALUES(destacado),
  recomendado = VALUES(recomendado),
  orden_visualizacion = VALUES(orden_visualizacion),
  insignia = VALUES(insignia),
  resumen_comercial = VALUES(resumen_comercial),
  color_visual = VALUES(color_visual),
  maximo_usuarios = VALUES(maximo_usuarios),
  usuarios_ilimitados = VALUES(usuarios_ilimitados),
  observaciones_internas = VALUES(observaciones_internas),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

-- Mapeo de funcionalidades por plan
INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT p.id, f.id,
  CASE
    WHEN p.slug = 'basico' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_catalogo_en_linea','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_contactos','modulo_categorias','modulo_configuracion','modulo_checkout_flow',
      'clientes_exportar_excel','clientes_gestion_listas_precios','clientes_asignar_vendedor'
    ) THEN 1
    WHEN p.slug = 'profesional' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_catalogo_en_linea','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
      'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos',
      'modulo_reportes','modulo_configuracion','modulo_checkout_flow','modulo_usuarios','modulo_correos_stock','modulo_notificaciones','modulo_historial',
      'clientes_exportar_excel','clientes_gestion_listas_precios','clientes_asignar_vendedor'
    ) THEN 1
    WHEN p.slug = 'empresa' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_catalogo_en_linea','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
      'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos',
      'modulo_reportes','modulo_configuracion','modulo_checkout_flow','modulo_usuarios','modulo_correos_stock','modulo_ordenes_compra','modulo_notificaciones','modulo_historial',
      'clientes_exportar_excel','clientes_gestion_listas_precios','clientes_asignar_vendedor'
    ) THEN 1
    ELSE 0
  END,
  0,
  CASE WHEN p.slug = 'empresa' THEN 1 ELSE 0 END,
  NOW()
FROM planes p
INNER JOIN funcionalidades f
WHERE p.slug IN ('basico','profesional','empresa')
  AND f.codigo_interno IN (
    'modulo_clientes','modulo_productos','modulo_catalogo_en_linea','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
    'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos',
    'modulo_reportes','modulo_configuracion','modulo_checkout_flow','modulo_usuarios','modulo_correos_stock','modulo_ordenes_compra','modulo_notificaciones','modulo_historial',
    'clientes_exportar_excel','clientes_gestion_listas_precios','clientes_asignar_vendedor'
  )
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();

-- Empresa y usuarios administrativos base
INSERT INTO empresas (razon_social, nombre_comercial, identificador_fiscal, correo, estado, fecha_activacion, plan_id)
SELECT 'CotizaPro SAS', 'CotizaPro Plataforma', '900111222', 'admin@cotizapro.com', 'activa', CURDATE(), p.id
FROM planes p WHERE p.slug = 'empresa'
AND NOT EXISTS (SELECT 1 FROM empresas WHERE correo = 'admin@cotizapro.com');

SET @rol_superadmin = (SELECT id FROM roles WHERE codigo = 'superadministrador' LIMIT 1);
SET @rol_admin_empresa = (SELECT id FROM roles WHERE codigo = 'administrador_empresa' LIMIT 1);
SET @empresa_admin = (SELECT id FROM empresas WHERE correo = 'admin@cotizapro.com' LIMIT 1);
SET @hash_demo = '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi'; -- Demo1234*

INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado)
SELECT NULL, @rol_superadmin, 'Super Admin', 'superadmin@cotizapro.com', @hash_demo, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'superadmin@cotizapro.com');

INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado)
SELECT @empresa_admin, @rol_admin_empresa, 'Administrador Principal', 'admin@cotizapro.com', @hash_demo, 'activo'
WHERE @empresa_admin IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'admin@cotizapro.com');

INSERT INTO configuraciones (clave, valor, descripcion)
VALUES
('nombre_plataforma', 'CotizaPro', 'Nombre comercial de la plataforma'),
('correo_soporte', 'soporte@cotizapro.local', 'Correo principal de soporte'),
('dias_alerta_vencimiento', '7', 'Días previos para alertar vencimientos')
ON DUPLICATE KEY UPDATE
  valor = VALUES(valor),
  descripcion = VALUES(descripcion),
  fecha_actualizacion = NOW();

COMMIT;
