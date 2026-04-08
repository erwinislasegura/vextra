# Actualización incremental: Panel de Administración SaaS

## Módulos mejorados
- Dashboard administrativo con KPIs de empresas, planes, suscripciones e ingresos estimados.
- Gestión de planes (crear/editar/activar/desactivar, recomendado/destacado, visibilidad, límites de usuarios).
- Gestión de funciones por plan con activación y límites por funcionalidad.
- Gestión de empresas con filtros, cambio de plan, cambio de estado y extensión de vigencia.
- Gestión de administradores de empresas (credenciales de acceso, estado y reseteo de contraseña).
- Suscripciones y pagos con filtros administrativos.
- Reportes ejecutivos y módulo de historial/actividad administrativa.

## Planes iniciales incluidos
1. **Básico**: 15.000 CLP mensual, 10% anual, límite de **2 usuarios**.
2. **Profesional**: 26.000 CLP mensual, 10% anual, límite de **8 usuarios**, marcado como recomendado y más elegido.
3. **Empresa**: 55.000 CLP mensual, 15% anual, usuarios ilimitados.

> Decisión de límite: se definió 2 usuarios en Básico para mantener uso funcional, y motivar upgrade a Profesional.

## Seguridad en credenciales de empresas
- El superadministrador puede resetear contraseña de administradores de empresa **sin contraseña actual**.
- El cambio se guarda con hash seguro (`password_hash`).
- La acción queda auditada en `logs_administracion` (quién, cuándo, acción, IP, detalle).
- No se muestran hashes de contraseña en interfaz.

## SQL incremental
Aplicar el script:

```sql
SOURCE base_datos/actualizaciones/actualizacion_panel_admin_saas.sql;
```

Incluye:
- ALTER TABLE para nuevos campos.
- Tabla `logs_administracion`.
- Inserción/actualización de los 3 planes obligatorios.
- Inserción de funcionalidades base y asignación por plan.

## Rutas administrativas nuevas/relevantes
- `/admin/administradores-empresa`
- `/admin/historial`
- `/admin/empresas/estado/{id}`
- `/admin/empresas/plan/{id}`
- `/admin/empresas/extender-vigencia/{id}`
- `/admin/planes/estado/{id}`
- `/admin/administradores-empresa/reset-password/{id}`

## Revisión recomendada post despliegue
1. Ejecutar SQL incremental en base de datos de staging.
2. Validar acceso exclusivo con rol `superadministrador`.
3. Probar flujo de reseteo de contraseña y verificar registro en historial.
4. Revisar que dashboard y reportes muestren datos reales.
