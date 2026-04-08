# Integración Flow Ecommerce (sin Cargo Automático)

## Estado del flujo activo
Este proyecto usa **solo Flow Ecommerce** para cobro de planes SaaS.

Flujo activo:
1. Crear empresa + suscripción local pendiente.
2. Crear orden en Flow con `payment/create`.
3. Redirigir al checkout de Flow (`url + ?token=...`).
4. Recibir confirmación en `urlConfirmation`.
5. Reconsultar estado con `payment/getStatus`.
6. Si pago aprobado, activar plan/suscripción local.
7. Usuario vuelve por `urlReturn` y ve resultado real.

## Endpoints activos
- `payment/create`
- `payment/getStatus`

## Endpoints de Cargo Automático
No se usan como flujo principal:
- `customer/register`
- `customer/charge`
- `subscription/create`

En panel admin quedaron deshabilitadas las acciones de iniciar registro de tarjeta y crear suscripción automática para evitar uso accidental.

## Configuración recomendada
En `/admin/flow/configuracion`:
- Activar integración.
- Elegir entorno correcto (`sandbox` o `produccion`).
- Cargar `apiKey` y `secretKey` del comercio ecommerce.
- Configurar:
  - `urlConfirmation`: `/flow/webhook/payment-confirmation`
  - `urlReturn`: `/flow/retorno/pago`

## Activación de plan
La activación de plan se realiza **solo con estado aprobado** luego de consultar API oficial (`payment/getStatus`).

Nunca se activa únicamente por retorno visual del navegador.

## Renovación manual
La renovación se ejecuta generando un nuevo `payment/create` desde botones de pago (sin débito automático).

## SQL incremental
Para este ajuste no se añadieron tablas/campos nuevos.
Se reutiliza la estructura existente (`flow_pagos`, `pagos`, `suscripciones`, `flow_logs`, etc.).
