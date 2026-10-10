# Transmetro — guía para agentes

Prototipo Laravel 13 + MySQL del proyecto final de Análisis de Sistemas II. Ver [README.md](README.md) para requisitos, supuestos y despliegue.

- **Reglas de negocio** en `app/Services/ReglasTransmetro.php`; los controladores las invocan, no las dupliquen.
- **Reportes:** el SQL vive en `database/sql/reportes/*.sql` y se ejecuta tal cual (es entregable del documento). Si cambia el modelo, actualice esos archivos y regenere `database/sql/schema.sql` con `mysqldump --no-data`.
- **Frontend:** Blade + Bootstrap 5 / Leaflet por CDN. No hay Vite ni npm, para que Railway construya solo PHP.
- **Seeder:** idempotente y sin Faker, porque corre en producción en cada deploy.
- **BD local:** MySQL en Docker, puerto 3307 (`transmetro-mysql`). Los tests usan SQLite en memoria, así que los reportes (SQL específico de MySQL) no se prueban ahí.
- **Verificar:** `php artisan test`.
