-- Dominio personalizado para catálogo por empresa
SET @columna_existe := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'empresas'
    AND column_name = 'catalogo_dominio'
);
SET @sql_columna := IF(
  @columna_existe = 0,
  'ALTER TABLE empresas ADD COLUMN catalogo_dominio VARCHAR(190) NULL AFTER descripcion',
  'SELECT 1'
);
PREPARE stmt_columna FROM @sql_columna;
EXECUTE stmt_columna;
DEALLOCATE PREPARE stmt_columna;

-- Normalización básica para evitar duplicados por espacios
UPDATE empresas
SET catalogo_dominio = NULLIF(LOWER(TRIM(catalogo_dominio)), '')
WHERE catalogo_dominio IS NOT NULL;

-- Índice único para evitar que dos empresas compartan el mismo dominio
SET @indice_existe := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'empresas'
    AND index_name = 'uk_empresas_catalogo_dominio'
);
SET @sql_indice := IF(
  @indice_existe = 0,
  'ALTER TABLE empresas ADD UNIQUE INDEX uk_empresas_catalogo_dominio (catalogo_dominio)',
  'SELECT 1'
);
PREPARE stmt_indice FROM @sql_indice;
EXECUTE stmt_indice;
DEALLOCATE PREPARE stmt_indice;

-- Funcionalidad para control por plan
INSERT INTO funcionalidades (nombre, codigo_interno, descripcion, tipo_valor, estado)
VALUES (
  'Dominio personalizado en catálogo',
  'catalogo_dominio_personalizado',
  'Permite configurar un dominio propio para el catálogo público de la empresa.',
  'booleano',
  'activo'
)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  tipo_valor = VALUES(tipo_valor),
  estado = VALUES(estado),
  fecha_actualizacion = NOW();

-- Asignación inicial por plan
INSERT INTO plan_funcionalidades (plan_id, funcionalidad_id, activo, valor_numerico, es_ilimitado, fecha_actualizacion)
SELECT
  p.id,
  f.id,
  CASE
    WHEN p.slug IN ('profesional', 'empresa') THEN 1
    ELSE 0
  END,
  0,
  0,
  NOW()
FROM planes p
INNER JOIN funcionalidades f ON f.codigo_interno = 'catalogo_dominio_personalizado'
WHERE p.slug IN ('basico', 'profesional', 'empresa')
ON DUPLICATE KEY UPDATE
  activo = VALUES(activo),
  valor_numerico = VALUES(valor_numerico),
  es_ilimitado = VALUES(es_ilimitado),
  fecha_actualizacion = NOW();
