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
