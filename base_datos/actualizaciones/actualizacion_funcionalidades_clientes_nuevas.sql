USE cotiza_saas;

-- Funcionalidades nuevas detectadas en módulo de clientes
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Exportar clientes a Excel', 'clientes_exportar_excel', 'Permite exportar el listado de clientes a archivo Excel.', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'clientes_exportar_excel');

INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Gestión de listas de precios por cliente', 'clientes_gestion_listas_precios', 'Permite asignar una o más listas de precios a cada cliente.', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'clientes_gestion_listas_precios');

INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Asignación de vendedor por cliente', 'clientes_asignar_vendedor', 'Permite asociar vendedores responsables en el alta y edición de clientes.', 'booleano', 'activo'
WHERE NOT EXISTS (SELECT 1 FROM funcionalidades WHERE codigo_interno = 'clientes_asignar_vendedor');

-- Vincular nuevas funcionalidades en los planes existentes tomando como base módulo clientes
INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT pf_base.plan_id, f_nueva.id, pf_base.activo, 0, pf_base.es_ilimitado, NOW()
FROM plan_funcionalidades pf_base
INNER JOIN funcionalidades f_base ON f_base.id = pf_base.funcionalidad_id
INNER JOIN funcionalidades f_nueva ON f_nueva.codigo_interno IN (
  'clientes_exportar_excel',
  'clientes_gestion_listas_precios',
  'clientes_asignar_vendedor'
)
WHERE f_base.codigo_interno = 'modulo_clientes'
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
