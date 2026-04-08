<?php

return [
    'host' => $_ENV['SMTP_HOST'] ?? 'localhost',
    'puerto' => (int) ($_ENV['SMTP_PUERTO'] ?? 1025),
    'usuario' => $_ENV['SMTP_USUARIO'] ?? '',
    'password' => $_ENV['SMTP_PASSWORD'] ?? '',
    'remite' => $_ENV['SMTP_REMITE'] ?? 'no-reply@example.com',
    'nombre' => $_ENV['SMTP_NOMBRE'] ?? 'CotizaPro',
];
