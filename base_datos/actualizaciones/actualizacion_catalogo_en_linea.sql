ALTER TABLE productos
  ADD COLUMN IF NOT EXISTS mostrar_catalogo TINYINT(1) NOT NULL DEFAULT 0 AFTER estado,
  ADD COLUMN IF NOT EXISTS imagen_catalogo_url VARCHAR(255) NULL AFTER mostrar_catalogo;

INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
SELECT 'Módulo catálogo en línea', 'modulo_catalogo_en_linea', 'Landing pública de catálogo con filtros, carrito y checkout Flow.', 'booleano', 'activo'
WHERE NOT EXISTS (
  SELECT 1 FROM funcionalidades WHERE codigo_interno = 'modulo_catalogo_en_linea'
);

INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT
  pf_base.plan_id,
  f_catalogo.id AS funcionalidad_id,
  pf_base.activo,
  0 AS valor_numerico,
  pf_base.es_ilimitado,
  NOW()
FROM plan_funcionalidades pf_base
INNER JOIN funcionalidades f_base ON f_base.id = pf_base.funcionalidad_id
INNER JOIN funcionalidades f_catalogo ON f_catalogo.codigo_interno = 'modulo_catalogo_en_linea'
WHERE f_base.codigo_interno = 'modulo_productos'
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  valor_numerico = VALUES(valor_numerico),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
