# Instalación local

1. Clona el repositorio.
2. Crea archivo `.env` desde `.env.example`.
3. Importa SQL en este orden:
   1) `base_datos/esquema/esquema.sql`
   2) `base_datos/esquema/semillas.sql`
   3) `base_datos/esquema/datos_demo.sql`
4. Levanta servidor:
   - `php -S localhost:8000 -t public`
5. Accede a `http://localhost:8000`.

## Verificación mínima
- Login superadmin y panel `/admin/panel`.
- Login empresa y panel `/app/panel`.
- Crear cliente, producto y cotización en empresa demo.
