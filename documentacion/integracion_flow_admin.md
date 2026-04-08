# Integración Flow en panel administrador

## Qué se implementó
- Módulo administrativo Flow con dashboard, configuración, planes, clientes, suscripciones, pagos y logs.
- Servicios Flow para firma HMAC SHA-256, llamadas API (sandbox/producción), pagos únicos, suscripciones, clientes y webhooks.
- Persistencia dedicada (`flow_configuracion`, `flow_planes`, `flow_clientes`, `flow_suscripciones`, `flow_pagos`, `flow_webhooks`, `flow_logs`) y campos de relación en tablas base (`planes`, `empresas`, `suscripciones`, `pagos`).
- Endpoints públicos para confirmación de pago, callback de suscripción, callback de registro de medio de pago y retornos de navegador.

## Configuración Flow (admin)
Ruta: `/admin/flow/configuracion`
1. Activar integración.
2. Elegir entorno `sandbox` o `produccion`.
3. Cargar `API Key` y `Secret Key`.
4. Confirmar URLs:
   - Confirmación pago: `/flow/webhook/payment-confirmation`
   - Retorno pago: `/flow/retorno/pago`
   - Webhook suscripción: `/flow/webhook/subscription`
   - Retorno registro: `/flow/retorno/registro`
5. Guardar.

> La Secret Key queda almacenada cifrada en base64 y enmascarada en interfaz.
> Se dejó precarga de credenciales en configuración inicial. Si tu comercio está en producción, confirma entorno `produccion` y base URL `https://www.flow.cl/api`.

## Firma Flow implementada
- La firma se genera ordenando parámetros por clave, concatenando `clave + valor` y aplicando `HMAC-SHA256` con Secret Key.
- Si Flow responde `Invalid Signature`, revisar:
  - entorno y llaves del mismo comercio (sandbox/prod),
  - que no existan espacios/comillas en credenciales,
  - que la migración esté aplicada y configuración vigente en `/admin/flow/configuracion`.

## URLs callback válidas (evitar 401 urlCallback is not a valid URL)
- El sistema ahora construye URLs absolutas usando `APP_URL` (si existe) o detectando host/esquema de la petición actual.
- Recomendada en producción: definir `APP_URL=https://tu-dominio.com/cotiza` en `.env`.
- Verifica que Flow pueda acceder públicamente a:
  - `/flow/webhook/subscription`
  - `/flow/webhook/payment-confirmation`
  - `/flow/retorno/pago`
  - `/flow/retorno/registro`

## Sandbox y producción
- Si no se define `base_url`, el sistema usa:
  - Sandbox: `https://sandbox.flow.cl/api`
  - Producción: `https://www.flow.cl/api`
- Se puede sobreescribir con `base_url` desde el panel.

## Planes y decisión técnica
Ruta: `/admin/flow/planes`
- Se manejan versiones separadas por modalidad para cada plan interno.
- Convención de `flow_plan_id`: `SLUG_MENSUAL` y `SLUG_ANUAL`.
- Los `días de prueba` y `días hasta cobro` se definen en la creación/edición del plan SaaS en `/admin/planes/crear` y `/admin/planes/editar/{id}`.
- Al sincronizar en `/admin/flow/planes`, esos valores se envían a Flow automáticamente (`trial_period_days` y `days_until_due`).
- Si Flow responde que `planId` ya existe, la integración intenta actualización automática del plan (`plans/update`) en lugar de fallar.
- Si la API devuelve `No services available` en update, se deja la relación local vinculada y se registra warning para no bloquear la operación administrativa.
- Planes base esperados:
  - Básico: 15000 mensual / 162000 anual
  - Profesional: 26000 mensual / 280800 anual
  - Empresa: 55000 mensual / 561000 anual (15% descuento)

## Clientes y registro de medio de pago
Ruta: `/admin/flow/clientes`
- Crear cliente Flow por empresa.
- Iniciar/reintentar registro de tarjeta (Flow `customer/register`).
- Retorno navegador en `/flow/retorno/registro` y sincronización por `customer/getRegisterStatus`.
- Si el correo de empresa no cumple formato compatible con Flow, el sistema genera uno técnico seguro (`cliente.empresa{id}@gmail.com`) para evitar errores `email is not valid`.

## Suscripciones
Ruta: `/admin/flow/suscripciones`
- Crear suscripción (`subscription/create`) con tipo mensual/anual.
- Sincronizar estado (`subscription/get`).
- Cancelar (`subscription/cancel`).
- Actualiza también suscripción local del SaaS.

## Pagos
Ruta: `/admin/flow/pagos`
- Crear pago único administrativo (`payment/create`).
- Confirmar estado por consulta (`payment/getStatus`).
- No se marca pago exitoso solo por retorno del navegador.

## Webhooks
Rutas nuevas:
- `POST /flow/webhook/payment-confirmation`
- `POST /flow/webhook/subscription`
- `POST /flow/webhook/card-register`
- `GET /flow/retorno/pago`
- `GET /flow/retorno/registro`

Lógica:
- Registro de webhook con hash único para evitar duplicados.
- Respuesta rápida `HTTP 200`.
- Confirmación de estado consultando API oficial de Flow.

## SQL/migración
Archivo incremental:
- `base_datos/actualizaciones/actualizacion_flow_admin_integracion.sql`

Aplicación sugerida:
```sql
SOURCE base_datos/actualizaciones/actualizacion_flow_admin_integracion.sql;
```

## Seguridad y operación
- Rutas admin Flow protegidas por `AutenticadoMiddleware + SuperAdminMiddleware`.
- Secret Key no visible completa después de guardar.
- Logs de integración (`flow_logs`) y webhooks (`flow_webhooks`) disponibles en `/admin/flow/logs`.
- Trazabilidad de payloads request/response para auditoría.
