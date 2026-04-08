ALTER TABLE cotizaciones
  ADD COLUMN firma_cliente MEDIUMTEXT NULL AFTER token_publico,
  ADD COLUMN nombre_firmante_cliente VARCHAR(180) NULL AFTER firma_cliente,
  ADD COLUMN fecha_aprobacion_cliente DATETIME NULL AFTER nombre_firmante_cliente;
