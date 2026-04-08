# Actualización incremental: inventario, recepciones, ajustes y alertas por correo

## 1) Archivo SQL
Ejecuta en este orden el script:

- `base_datos/actualizaciones/actualizacion_inventario_alertas_stock.sql`

Este script agrega:

- Nuevos campos en `productos`: `stock_actual`, `stock_critico`, `ultimo_aviso_stock_bajo`, `ultimo_aviso_stock_critico`.
- Tablas: `proveedores_inventario`, `recepciones_inventario`, `recepciones_inventario_detalle`, `ajustes_inventario`, `movimientos_inventario`.
- Módulo operativo de proveedores en `/app/inventario/proveedores` para mantener datos comerciales del proveedor (RUT/NIT, contacto, correo, teléfono, dirección, ciudad y observaciones).
- Claves base en `configuraciones_empresa` para alertas de stock.

## 2) Flujo implementado

### Recepción desde proveedor
Rutas: `/app/inventario/recepciones` y `/app/inventario/proveedores`

Permite registrar:
- proveedor
- tipo de documento (`guia_despacho` o `factura`)
- número/fecha documento
- referencia interna y observación
- múltiples productos con cantidad y costo unitario

Al guardar:
- incrementa `stock_actual`
- crea detalle de recepción
- registra trazabilidad en `movimientos_inventario`

### Órdenes de compra
Rutas: `/app/inventario/ordenes-compra` y `/app/inventario/ordenes-compra/ver/{id}`

Permite:
- crear orden por proveedor con múltiples productos/cantidades
- definir fecha de entrega estimada y referencia comercial
- vincular recepción desde la orden para mantener trazabilidad

Estados:
- `emitida`: orden creada
- `parcial`: recepción parcial
- `recibida`: recepción completa

### Ajustes de inventario
Ruta: `/app/inventario/ajustes`

Permite:
- elegir producto
- tipo de ajuste (`entrada` o `salida`)
- cantidad, motivo y observación
- filtrar historial por fecha, producto y tipo

Al guardar:
- actualiza stock
- registra `ajustes_inventario`
- crea movimiento en `movimientos_inventario`
- evita stock negativo según la clave `inventario_permitir_stock_negativo` en `configuraciones_empresa`

### Movimientos de inventario
Ruta: `/app/inventario/movimientos`

Concentra entradas/salidas de recepciones, ajustes y ventas POS integradas.

## 3) Alertas automáticas por stock bajo/crítico

Rutas:
- `/app/configuracion/correos-stock`

Configuración editable:
- activar/desactivar alerta bajo/crítico
- destinatarios
- asunto y plantilla HTML por nivel
- vista previa renderizada dentro del sistema
- envío de prueba (registrado en logs de correo)

Variables disponibles:
- `{empresa}`
- `{producto}`
- `{codigo}`
- `{stock_actual}`
- `{stock_minimo}`
- `{stock_critico}`
- `{fecha}`
- `{usuario}`

Reglas de disparo:
- `normal -> bajo`: envía alerta de stock bajo.
- `normal/bajo -> crítico`: envía alerta crítica.
- `* -> normal`: limpia marcas de último aviso para habilitar futuros reenvíos cuando vuelva a caer.

## 4) Integración con SMTP actual de empresa

El envío usa `ServicioCorreo::enviarConEmpresa()` y toma host/remitente desde la empresa (`imap_*` y correo corporativo). En el estado actual, el servicio registra el envío en `logs_correos` con payload y metadatos SMTP por empresa.

## 5) Pruebas sugeridas

1. Crear recepción con documento y verificar aumento de stock.
2. Crear ajuste de salida y validar que no permita negativo si está deshabilitado.
3. Verificar columna de stock/estado en `/app/productos`.
4. Configurar destinatario de alertas y forzar transición de stock a bajo/critico.
5. Revisar `logs_correos` y `movimientos_inventario`.
