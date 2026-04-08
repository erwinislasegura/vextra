# Despliegue

## Recomendaciones
- Document root en `/public`.
- PHP 8.1+ con extensiones PDO y pdo_mysql.
- Variables sensibles solo en `.env`.
- Respaldos diarios de BD.

## Seguridad mínima producción
- Forzar HTTPS.
- `secure=true` en cookies de sesión.
- Rotación de contraseñas administrativas.
- Monitoreo de `almacenamiento/logs`.

## Tarea programada sugerida
Implementar cron cada noche para marcar suscripciones `por_vencer` y `vencida` según `fecha_vencimiento`.
