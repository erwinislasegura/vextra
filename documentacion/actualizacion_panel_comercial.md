# Actualización panel comercial SaaS

## Resumen de mejoras
Se reforzó el panel privado con enfoque comercial en estilo empresarial (sidebar fijo claro, topbar limpia, formularios compactos y tablas con acciones por dropdown).

### Módulos mejorados o agregados
- Inicio comercial con métricas (cotizaciones del mes, aprobadas, rechazadas, por vencer, clientes recientes, top de productos y vendedores).
- Clientes (formulario superior + listado con acciones).
- Contactos (nuevo módulo operativo con formulario + listado).
- Vendedores (base funcional).
- Servicios / Productos (formulario superior + listado + campos comerciales).
- Categorías (base funcional).
- Listas de precios (base funcional).
- Cotizaciones (listado más completo con acciones comerciales).
- Seguimiento comercial (base funcional).
- Aprobaciones (base funcional).
- Reportes comerciales (vista de métricas base).
- Documentos y plantillas (base funcional).
- Configuración de empresa (estructura ampliada).
- Usuarios y permisos (formulario superior + listado con dropdown).
- Notificaciones (base funcional).
- Historial / actividad (base funcional).

También se homologó el panel de superadministrador para usar el mismo estilo visual y acciones por dropdown.

## Base de datos: cambios incrementales
SQL principal:
- `base_datos/actualizaciones/actualizacion_mejora_panel_comercial.sql`

### Tablas nuevas
- `vendedores`
- `listas_precios`
- `seguimientos_comerciales`
- `aprobaciones_cotizacion`
- `documentos_plantillas`
- `notificaciones_empresa`
- `historial_actividad`

### Columnas nuevas
- En `clientes`: `razon_social`, `nombre_comercial`, `identificador_fiscal`, `giro`, `ciudad`, `vendedor_id`.
- En `productos`: `tipo`, `costo`, `descuento_maximo`.
- En `categorias_productos`: `descripcion`.
- En `contactos_cliente`: `empresa_id`, `celular`, `es_principal`, `observaciones`.

## Cómo ejecutar la actualización
### Opción recomendada (script PHP)
```bash
php scripts/actualizar_proyecto.php
```

El script:
1. Advierte respaldo.
2. Aplica SQL incremental.
3. Genera log en `base_datos/actualizaciones/actualizacion_mejora_panel_comercial.log`.

### Opción manual
Ejecutar el SQL:
```bash
mysql -u USUARIO -p NOMBRE_BD < base_datos/actualizaciones/actualizacion_mejora_panel_comercial.sql
```

## Revisiones posteriores recomendadas
1. Verificar navegación completa del menú privado y admin.
2. Confirmar que cada empresa visualiza solo su información por `empresa_id`.
3. Probar altas en clientes, contactos, vendedores y productos.
4. Revisar creación de cotizaciones y consistencia de estados.
5. Validar que dropdowns de acciones se desplieguen correctamente.
6. Limpiar caché/opcache según servidor.

## Datos base
- La actualización inserta notificaciones iniciales por empresa para dejar trazabilidad de despliegue.
