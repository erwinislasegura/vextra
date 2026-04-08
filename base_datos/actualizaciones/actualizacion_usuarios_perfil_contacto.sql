-- Agrega campos de perfil/contacto al módulo de usuarios de empresa.
USE cotiza_saas;

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='usuarios' AND COLUMN_NAME='telefono') = 0,
  'ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(80) NULL AFTER password',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='usuarios' AND COLUMN_NAME='cargo') = 0,
  'ALTER TABLE usuarios ADD COLUMN cargo VARCHAR(120) NULL AFTER telefono',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='usuarios' AND COLUMN_NAME='biografia') = 0,
  'ALTER TABLE usuarios ADD COLUMN biografia TEXT NULL AFTER cargo',
  'SELECT 1'
); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
