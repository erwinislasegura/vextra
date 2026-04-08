ALTER TABLE empresas
  ADD COLUMN imap_host VARCHAR(180) NULL AFTER logo,
  ADD COLUMN imap_port SMALLINT UNSIGNED NULL AFTER imap_host,
  ADD COLUMN imap_encryption ENUM('ssl','tls','none') NULL AFTER imap_port,
  ADD COLUMN imap_usuario VARCHAR(180) NULL AFTER imap_encryption,
  ADD COLUMN imap_password VARCHAR(255) NULL AFTER imap_usuario,
  ADD COLUMN imap_remitente_correo VARCHAR(180) NULL AFTER imap_password,
  ADD COLUMN imap_remitente_nombre VARCHAR(180) NULL AFTER imap_remitente_correo;
