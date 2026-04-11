-- Instalación completa enfocada al panel de administrador.
-- Incluye todas las tablas + actualizaciones, carga datos administrativos
-- (roles, permisos, planes, empresas, usuarios, suscripciones, pagos)
-- y no inserta datos en tablas del módulo cliente/comercial.
-- Uso sugerido:
--   mysql -u usuario -p < base_datos/esquema/panel_admin_completo.sql

SOURCE base_datos/esquema/esquema.sql;
SOURCE base_datos/esquema/semillas.sql;

-- Actualizaciones acumuladas del proyecto.
SOURCE base_datos/actualizaciones/actualizacion_panel_admin_saas.sql;
SOURCE base_datos/actualizaciones/actualizacion_flow_admin_integracion.sql;
SOURCE base_datos/actualizaciones/actualizacion_configuracion_general_admin.sql;
SOURCE base_datos/actualizaciones/actualizacion_funcionalidades_clientes_nuevas.sql;
SOURCE base_datos/actualizaciones/actualizacion_roles_empresa.sql;
SOURCE base_datos/actualizaciones/actualizacion_configuracion_empresa_logo_imap.sql;
SOURCE base_datos/actualizaciones/actualizacion_pos_moneda.sql;
SOURCE base_datos/actualizaciones/actualizacion_pos_comercial.sql;
SOURCE base_datos/actualizaciones/actualizacion_cotizaciones_token_publico.sql;
SOURCE base_datos/actualizaciones/actualizacion_cotizaciones_lista_precio.sql;
SOURCE base_datos/actualizaciones/actualizacion_cotizaciones_origen_orden_compra.sql;
SOURCE base_datos/actualizaciones/actualizacion_cotizaciones_detalle_descuentos.sql;
SOURCE base_datos/actualizaciones/actualizacion_cotizaciones_firma_cliente.sql;
SOURCE base_datos/actualizaciones/actualizacion_documentos_plantillas_correo.sql;
SOURCE base_datos/actualizaciones/actualizacion_listas_precios_reglas.sql;
SOURCE base_datos/actualizaciones/actualizacion_mejora_panel_comercial.sql;
SOURCE base_datos/actualizaciones/actualizacion_inventario_alertas_stock.sql;
SOURCE base_datos/actualizaciones/actualizacion_productos_campos_inventario.sql;
SOURCE base_datos/actualizaciones/actualizacion_ordenes_compra_inventario.sql;
SOURCE base_datos/actualizaciones/actualizacion_ordenes_compra_aprobacion.sql;
SOURCE base_datos/actualizaciones/actualizacion_recepciones_fecha_actualizacion.sql;
SOURCE base_datos/actualizaciones/actualizacion_ordenes_compra_recepcionada.sql;
SOURCE base_datos/actualizaciones/actualizacion_coherencia_planes_publicos.sql;

-- Usuarios de acceso estandarizados.
SOURCE base_datos/esquema/actualizar_usuarios_acceso.sql;

-- Datos del panel administrativo (sin clientes/productos/cotizaciones).
SOURCE base_datos/esquema/datos_admin.sql;
