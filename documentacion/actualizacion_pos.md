# Módulo Punto de Venta (POS)

## Qué incluye
- Apertura y cierre de caja por usuario.
- Registro de ventas POS (rápidas y con cliente registrado).
- Carrito de venta con cobro en efectivo, transferencia, tarjeta y pagos mixtos.
- Descuento de stock en `productos.stock_actual` al confirmar venta.
- Boucher/ticket imprimible automático al registrar la venta.
- Historial de ventas, movimientos de caja y configuración POS (incluye decimales y cantidad de decimales).

## Rutas del módulo
- `/app/punto-venta`
- `/app/punto-venta/ventas`
- `/app/punto-venta/ventas/ver/{id}`
- `/app/punto-venta/movimientos`
- `/app/punto-venta/cajas`
- `/app/punto-venta/configuracion`

## Flujo operativo
1. Usuario entra al POS.
2. Si no hay caja abierta del usuario, debe abrirla desde el modal en la pantalla POS.
3. Agrega productos al carrito (panel derecho) y revisa detalle (panel izquierdo).
4. Selecciona tipo de venta (rápida / cliente registrado).
5. Registra uno o varios pagos (efectivo, transferencia, tarjeta).
6. Finaliza venta, se descuenta inventario y se imprime boucher de pago.
7. Al cierre de jornada, realiza arqueo y cierra caja.

## Reglas de pago
- El vuelto se calcula solo cuando existe pago en efectivo.

## Integración con inventario
- Cada ítem vendido descuenta `stock_actual` del producto.
- Se registra traza en `movimientos_inventario_pos`.
- Si `permitir_venta_sin_stock = 0`, el sistema bloquea venta sin existencias.

## Permisos sugeridos
- ver POS
- abrir caja
- cerrar caja
- registrar venta
- aplicar descuento
- editar precio
- ver historial POS
- administrar cajas
- configurar POS

## Cómo aplicar la actualización
```bash
php scripts/actualizar_pos.php
```
