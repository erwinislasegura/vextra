# Actualización incremental: dominio personalizado para catálogo

## Objetivo
Permitir que cada empresa configure un dominio propio para su catálogo público, evitando colisiones entre clientes.

## SQL incremental
Aplicar:

```sql
SOURCE base_datos/actualizaciones/actualizacion_catalogo_dominio_personalizado.sql;
```

## Qué habilita
- Campo `catalogo_dominio` en `empresas`.
- Validación de unicidad de dominio entre empresas.
- Nueva funcionalidad de plan: `catalogo_dominio_personalizado`.
- Nuevo flujo en configuración de empresa para guardar dominio.
- Verificación DNS automática desde la vista de dominio (compara IPs del dominio contra servidor esperado).
- Nuevas rutas de catálogo por dominio:
  - `GET /catalogo`
  - `GET /catalogo/nosotros`
  - `GET /catalogo/contacto`
  - `POST /catalogo/contacto`

Estas rutas resuelven la empresa por `HTTP_HOST` usando `catalogo_dominio`.

## Asignación inicial por plan
- **Básico**: no incluido.
- **Profesional**: incluido.
- **Empresa**: incluido.

La asignación se puede modificar desde `/admin/plan-funcionalidades/{planId}`.

## Recomendación de despliegue
1. Crear registro DNS (A o CNAME) del dominio del cliente hacia el servidor.
2. Configurar certificado SSL para el dominio.
3. (Opcional) Rewrite en el virtual host del dominio para enviar `/` a `/catalogo`.
4. Cargar el dominio en **Configuración de empresa**.
