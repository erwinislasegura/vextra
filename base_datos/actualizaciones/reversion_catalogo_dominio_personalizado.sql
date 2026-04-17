-- Reversión: dominio personalizado de catálogo

-- Eliminar asignaciones por plan de la funcionalidad
DELETE pf
FROM plan_funcionalidades pf
INNER JOIN funcionalidades f ON f.id = pf.funcionalidad_id
WHERE f.codigo_interno = 'catalogo_dominio_personalizado';

-- Eliminar funcionalidad
DELETE FROM funcionalidades
WHERE codigo_interno = 'catalogo_dominio_personalizado';

-- Eliminar índice único si existe
SET @idx_existe := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'empresas'
    AND index_name = 'uk_empresas_catalogo_dominio'
);
SET @sql_drop_idx := IF(
  @idx_existe > 0,
  'ALTER TABLE empresas DROP INDEX uk_empresas_catalogo_dominio',
  'SELECT 1'
);
PREPARE stmt_drop_idx FROM @sql_drop_idx;
EXECUTE stmt_drop_idx;
DEALLOCATE PREPARE stmt_drop_idx;

-- Eliminar columna catalogo_dominio si existe
SET @col_existe := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'empresas'
    AND column_name = 'catalogo_dominio'
);
SET @sql_drop_col := IF(
  @col_existe > 0,
  'ALTER TABLE empresas DROP COLUMN catalogo_dominio',
  'SELECT 1'
);
PREPARE stmt_drop_col FROM @sql_drop_col;
EXECUTE stmt_drop_col;
DEALLOCATE PREPARE stmt_drop_col;
