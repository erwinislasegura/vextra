ALTER TABLE catalogo_compras
  MODIFY COLUMN envio_metodo ENUM('starken','blue_express','chile_express') NOT NULL DEFAULT 'starken';
