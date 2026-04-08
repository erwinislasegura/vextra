# Base de datos

El esquema usa una sola base compartida multiempresa y aplica `empresa_id` para aislar información de negocio.

## Tablas clave
- Seguridad: `roles`, `permisos`, `roles_permisos`, `usuarios`, `restablecimientos_contrasena`.
- SaaS comercial: `planes`, `funcionalidades`, `plan_funcionalidades`, `suscripciones`, `historial_suscripciones`, `pagos`, `logs_pagos`.
- Dominio cotizaciones: `clientes`, `contactos_cliente`, `categorias_productos`, `productos`, `cotizaciones`, `items_cotizacion`, `historial_estados_cotizacion`.
- Operación: `logs_correos`, `logs_actividad`, `configuraciones`, `configuraciones_empresa`.

## Reglas implementadas
- Numeración de cotización por empresa (`numero` único por `empresa_id`).
- Soft delete en tablas de negocio críticas.
- Relaciones y llaves foráneas para integridad.
- Índices por estado y por `empresa_id`.

## Instalación solo panel administrador (con actualizaciones)
Si necesitas levantar la base sin datos demo de empresas/clientes, usa el instalador acumulado:

```sql
SOURCE base_datos/esquema/panel_admin_completo.sql;
```

Este script:
- Crea todas las tablas del sistema (incluyendo módulos de cliente/comercial).
- Carga datos administrativos: roles, permisos, funcionalidades, planes, empresas, usuarios, suscripciones y pagos.
- Aplica todas las actualizaciones SQL disponibles en `base_datos/actualizaciones`.
- No inserta datos en tablas de cliente/comercial (`clientes`, `productos`, `cotizaciones`, `items_cotizacion`, etc.).

## Cargar solo datos del panel administrador
Si ya tienes el esquema creado y solo necesitas poblar datos administrativos (planes, funcionalidades, roles/permisos, superadmin y admin principal), ejecuta:

```sql
USE tu_base_de_datos;
SOURCE base_datos/esquema/datos_solo_panel_admin.sql;
```

No inserta datos de clientes/productos/cotizaciones.
