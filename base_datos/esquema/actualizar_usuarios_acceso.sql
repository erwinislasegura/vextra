USE cotiza_saas;

START TRANSACTION;

-- Hash bcrypt para la clave demo: Demo1234*
SET @hash_demo = '$2y$12$l7d9QArsnPnqUeo/YjnXfOsDig87Wswc2LvMubdMw2kt1LRD4xhdi';
SET @rol_superadmin = (SELECT id FROM roles WHERE codigo = 'superadministrador' LIMIT 1);
SET @rol_admin_empresa = (SELECT id FROM roles WHERE codigo = 'administrador_empresa' LIMIT 1);
SET @rol_usuario_empresa = (SELECT id FROM roles WHERE codigo = 'usuario_empresa' LIMIT 1);
SET @empresa_andina = (SELECT id FROM empresas WHERE correo = 'contacto@andina.com' LIMIT 1);

-- 1) Superadministrador
INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado, fecha_creacion)
SELECT NULL, @rol_superadmin, 'Super Admin', 'superadmin@cotizapro.com', @hash_demo, 'activo', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE correo = 'superadmin@cotizapro.com'
);

UPDATE usuarios
SET rol_id = @rol_superadmin,
    empresa_id = NULL,
    password = @hash_demo,
    estado = 'activo',
    fecha_actualizacion = NOW()
WHERE correo = 'superadmin@cotizapro.com';

-- 2) Administrador empresa Andina
INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado, fecha_creacion)
SELECT @empresa_andina, @rol_admin_empresa, 'Administrador Andina', 'admin@andina.com', @hash_demo, 'activo', NOW()
WHERE @empresa_andina IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM usuarios WHERE correo = 'admin@andina.com'
);

UPDATE usuarios
SET empresa_id = @empresa_andina,
    rol_id = @rol_admin_empresa,
    password = @hash_demo,
    estado = 'activo',
    fecha_actualizacion = NOW()
WHERE correo = 'admin@andina.com';

-- 3) Usuario QA de empresa Andina
INSERT INTO usuarios (empresa_id, rol_id, nombre, correo, password, estado, fecha_creacion)
SELECT @empresa_andina, @rol_usuario_empresa, 'QA Andina', 'qa@andina.com', @hash_demo, 'activo', NOW()
WHERE @empresa_andina IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM usuarios WHERE correo = 'qa@andina.com'
);

UPDATE usuarios
SET empresa_id = @empresa_andina,
    rol_id = @rol_usuario_empresa,
    password = @hash_demo,
    estado = 'activo',
    fecha_actualizacion = NOW()
WHERE correo = 'qa@andina.com';

COMMIT;
