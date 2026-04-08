USE cotiza_saas;

-- Funcionalidades faltantes + descripciones comerciales coherentes
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
VALUES
('Módulo clientes', 'modulo_clientes', 'Gestión de clientes y sus datos comerciales.', 'booleano', 'activo'),
('Módulo productos', 'modulo_productos', 'Catálogo de productos y servicios con soporte de carga masiva.', 'booleano', 'activo'),
('Módulo cotizaciones', 'modulo_cotizaciones', 'Creación, envío y seguimiento de cotizaciones.', 'booleano', 'activo'),
('Módulo POS', 'modulo_pos', 'Punto de venta para registrar ventas y movimientos de caja.', 'booleano', 'activo'),
('Módulo inventario', 'modulo_inventario', 'Inventario base con proveedores y control de stock.', 'booleano', 'activo'),
('Módulo recepciones', 'modulo_recepciones', 'Recepciones de mercadería asociadas a inventario.', 'booleano', 'activo'),
('Módulo ajustes', 'modulo_ajustes', 'Ajustes manuales de inventario por diferencias operativas.', 'booleano', 'activo'),
('Módulo movimientos', 'modulo_movimientos', 'Historial de movimientos de stock y trazabilidad.', 'booleano', 'activo'),
('Módulo contactos', 'modulo_contactos', 'Gestión de contactos comerciales vinculados a clientes.', 'booleano', 'activo'),
('Módulo vendedores', 'modulo_vendedores', 'Gestión de vendedores y asignación comercial.', 'booleano', 'activo'),
('Módulo categorías', 'modulo_categorias', 'Clasificación de productos por categorías.', 'booleano', 'activo'),
('Módulo listas de precios', 'modulo_listas_precios', 'Listas de precios por canal, cliente o condición comercial.', 'booleano', 'activo'),
('Módulo seguimiento', 'modulo_seguimiento', 'Seguimiento de oportunidades y actividades comerciales.', 'booleano', 'activo'),
('Módulo aprobaciones', 'modulo_aprobaciones', 'Flujos de aprobación para operaciones comerciales.', 'booleano', 'activo'),
('Módulo documentos', 'modulo_documentos', 'Gestión de plantillas y documentos comerciales.', 'booleano', 'activo'),
('Módulo reportes', 'modulo_reportes', 'Reportes de ventas, inventario y desempeño comercial.', 'booleano', 'activo'),
('Módulo configuración', 'modulo_configuracion', 'Configuración general de la empresa.', 'booleano', 'activo'),
('Módulo usuarios', 'modulo_usuarios', 'Gestión de usuarios internos y permisos.', 'booleano', 'activo'),
('Módulo correos stock', 'modulo_correos_stock', 'Alertas y configuración de correos de stock.', 'booleano', 'activo'),
('Módulo órdenes de compra', 'modulo_ordenes_compra', 'Gestión de órdenes de compra a proveedores.', 'booleano', 'activo'),
('Módulo notificaciones', 'modulo_notificaciones', 'Notificaciones operativas y comerciales del sistema.', 'booleano', 'activo'),
('Módulo historial', 'modulo_historial', 'Historial y auditoría de actividad operativa.', 'booleano', 'activo')
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  tipo_valor = VALUES(tipo_valor),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

-- Coherencia de planes base
INSERT INTO planes (nombre, slug, descripcion_comercial, precio_mensual, descuento_anual_pct, precio_anual, duracion_dias, visible, destacado, recomendado, orden_visualizacion, insignia, resumen_comercial, color_visual, maximo_usuarios, usuarios_ilimitados, observaciones_internas, estado)
VALUES
('Básico', 'basico', 'Plan de entrada para operar cotizaciones y operación comercial inicial.', 15000, 10, 162000, 30, 1, 0, 0, 1, 'Inicial', 'Ideal para comenzar con operación comercial ordenada.', '#3b82f6', 2, 0, 'Plan inicial con alcance controlado.', 'activo'),
('Profesional', 'profesional', 'Plan recomendado para escalar ventas con inventario y control comercial.', 26000, 10, 280800, 30, 1, 1, 1, 2, 'Más elegido', 'Incluye inventario completo, seguimiento y analítica base.', '#0ea5a4', 8, 0, 'Plan recomendado para la mayoría de empresas.', 'activo'),
('Empresa', 'empresa', 'Plan avanzado para operación integral con mayor capacidad y control.', 55000, 15, 561000, 30, 1, 1, 0, 3, 'Escalable', 'Acceso completo a módulos y operación multiusuario.', '#7c3aed', 0, 1, 'Plan corporativo con usuarios ilimitados.', 'activo')
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion_comercial = VALUES(descripcion_comercial),
  precio_mensual = VALUES(precio_mensual),
  descuento_anual_pct = VALUES(descuento_anual_pct),
  precio_anual = VALUES(precio_anual),
  visible = VALUES(visible),
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

-- Matriz inicial de módulos por plan (idempotente)
INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT
  p.id,
  f.id,
  CASE
    WHEN p.slug = 'basico' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_contactos','modulo_categorias','modulo_configuracion'
    ) THEN 1
    WHEN p.slug = 'profesional' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
      'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos',
      'modulo_reportes','modulo_configuracion','modulo_usuarios','modulo_correos_stock','modulo_notificaciones','modulo_historial'
    ) THEN 1
    WHEN p.slug = 'empresa' AND f.codigo_interno IN (
      'modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
      'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos',
      'modulo_reportes','modulo_configuracion','modulo_usuarios','modulo_correos_stock','modulo_ordenes_compra','modulo_notificaciones','modulo_historial'
    ) THEN 1
    ELSE 0
  END AS activo,
  0 AS valor_numerico,
  CASE WHEN p.slug = 'empresa' THEN 1 ELSE 0 END AS es_ilimitado,
  NOW()
FROM planes p
INNER JOIN funcionalidades f ON f.codigo_interno IN (
  'modulo_clientes','modulo_productos','modulo_cotizaciones','modulo_pos','modulo_inventario','modulo_recepciones','modulo_ajustes','modulo_movimientos',
  'modulo_contactos','modulo_vendedores','modulo_categorias','modulo_listas_precios','modulo_seguimiento','modulo_aprobaciones','modulo_documentos','modulo_reportes',
  'modulo_configuracion','modulo_usuarios','modulo_correos_stock','modulo_ordenes_compra','modulo_notificaciones','modulo_historial'
)
WHERE p.slug IN ('basico', 'profesional', 'empresa')
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  valor_numerico = VALUES(valor_numerico),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
