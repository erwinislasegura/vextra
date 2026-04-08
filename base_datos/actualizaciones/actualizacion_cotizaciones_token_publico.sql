ALTER TABLE cotizaciones
  ADD COLUMN token_publico CHAR(64) NULL AFTER terminos_condiciones,
  ADD UNIQUE KEY uq_cot_token_publico (token_publico);
