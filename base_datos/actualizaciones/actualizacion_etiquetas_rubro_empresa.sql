ALTER TABLE empresas
  ADD COLUMN etiqueta_rubro VARCHAR(80) NULL AFTER descripcion,
  ADD COLUMN etiqueta_frases TEXT NULL AFTER etiqueta_rubro;
