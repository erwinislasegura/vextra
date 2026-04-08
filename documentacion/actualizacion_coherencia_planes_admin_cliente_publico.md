# Actualización incremental: coherencia de planes entre Admin, Cliente y Público

## Qué se ajustó
- Se centralizó la fuente de verdad de planes y funcionalidades desde base de datos para landing (`/`) y página pública (`/planes`).
- Se eliminó hardcode de planes en secciones públicas críticas y ahora cada tarjeta muestra funcionalidades activas por plan usando la descripción comercial.
- Se reforzó `/contratar/{plan}` para aceptar únicamente planes públicos válidos (activos + visibles).
- En panel empresa, el sidebar ahora muestra módulos según funcionalidades activas del plan vigente.
- Se agregó validación en middleware para bloquear acceso a rutas de módulos no habilitados por plan.
- En topbar de empresa se muestra plan activo + días restantes (o estado sin vigencia).
- En admin, la vista de funcionalidades por plan ahora muestra: nombre, código, descripción comercial, activo, límite e ilimitado.

## SQL incremental
Aplicar:

```sql
SOURCE base_datos/actualizaciones/actualizacion_coherencia_planes_publicos.sql;
```

Este script es idempotente e incluye:
- Inserción/actualización de funcionalidades requeridas.
- Actualización de descripciones comerciales.
- Coherencia de planes base (Básico, Profesional, Empresa).
- Matriz inicial de `plan_funcionalidades` por plan.

## Validación recomendada
1. En admin, revisar `/admin/plan-funcionalidades/{id}` y ajustar módulos por plan.
2. Ingresar como empresa con distintos planes y validar menú lateral.
3. Revisar landing y `/planes` para confirmar que las tarjetas reflejan funcionalidades activas.
4. Probar `/contratar/{slug}` con slug válido e inválido.
