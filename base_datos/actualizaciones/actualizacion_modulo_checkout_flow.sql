USE cotiza_saas;

INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo checkout Flow', 'modulo_checkout_flow', 'Checkout de pagos Flow para compartir links de cobro.', 'booleano', 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_checkout_flow'
);

INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT
  pf_base.plan_id,
  f_checkout.id AS funcionalidad_id,
  pf_base.activo,
  0 AS valor_numerico,
  pf_base.es_ilimitado,
  NOW()
FROM plan_funcionalidades pf_base
INNER JOIN funcionalidades f_base ON f_base.id = pf_base.funcionalidad_id
INNER JOIN funcionalidades f_checkout ON f_checkout.codigo_interno = 'modulo_checkout_flow'
WHERE f_base.codigo_interno = 'modulo_configuracion'
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  valor_numerico = VALUES(valor_numerico),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
