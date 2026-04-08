# Módulos funcionales

## Público
Landing, planes, características, contacto, contratación.

## Autenticación
Login, logout, registro de empresa con suscripción inicial, recuperación y restablecimiento base.

## Superadministrador
Dashboard, empresas, planes CRUD, funcionalidades CRUD, asignación plan-funcionalidades, suscripciones y pagos, reportes base.

## Empresa
Dashboard, clientes CRUD base, productos CRUD base, cotizaciones con items, usuarios por empresa y configuración base.

## Servicios
- `ServicioPlan`: validación de límites y funcionalidades por plan.
- `ServicioCorreo`: logging desacoplado para plantillas/correos.
- `ServicioPagos`: base desacoplada para pasarela futura.
