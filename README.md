# Sistema de Control Transmetro

Prototipo web del proyecto final de **Análisis de Sistemas II**: control de líneas, estaciones, accesos, buses, parqueos, pilotos y operadores del Transmetro de Guatemala y municipios aledaños.

**Stack:** Laravel 13 (PHP 8.3+), MySQL 8, Blade + Bootstrap 5, Leaflet (mapas). Se despliega en **Railway**.

## Usuarios de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@transmetro.gt | admin123 |
| Operador (Plaza Barrios) | operador@transmetro.gt | operador123 |
| Operador de otra estación | op.&lt;estacion&gt;@transmetro.gt (ej. `op.el-trebol@transmetro.gt`) | operador123 |

## Cómo se cubre cada requisito del enunciado

| # | Requisito | Implementación |
|---|---|---|
| 1 | Orden de estaciones en la línea y visitas de cada unidad | `linea_estacion.orden`, editor del recorrido en *Líneas*, tabla `visitas` |
| 2 | Estación en una o varias líneas | Relación N:M `linea_estacion`. No se puede quitar una estación de su única línea |
| 3 | Accesos por estación | Tabla `accesos` (FK a estación) |
| 4 | Bus en máximo una línea, o en ninguna | `buses.linea_id` acepta NULL |
| 5 | Entre n y 2n buses por línea | `ReglasTransmetro::validarCambioLineaBus`, con estado en el dashboard y en el reporte de líneas |
| 6 | Parqueos en algunas estaciones; cada bus tiene uno | Tabla `parqueos`; `buses.parqueo_id` NOT NULL |
| 7 | El bus cambia de parqueo pero no se queda sin él | Validación `required` y FK `RESTRICT` |
| 8 | Accesos de cada línea | Reporte *Accesos por línea* |
| 9 | Historial educativo, residencia y comunicación del piloto | Tablas `pilotos` e `historial_educativo` |
| 10 | Al menos un guardia por acceso | El acceso se crea junto con su primer guardia; no se puede retirar el último guardia; el reporte marca incumplimientos |
| 11 | Línea y estación pertenecen a una municipalidad | FK `municipalidad_id` |
| 12 | Distancia entre estaciones | `linea_estacion.distancia_siguiente_km` |
| 13 | Distancia de la línea | `SUM(distancia_siguiente_km)` |
| 14 | Alerta cuando la estación supera en 50 % su capacidad | `ReglasTransmetro::actualizarPasajeros` |
| 15 | Bus con menos del 25 % de ocupación espera 5 min | `ReglasTransmetro::registrarVisita` |
| 16 | PC y operador por estación | Rol `operador` y panel `/panel/{estacion}`, limitado a su estación |

### Supuestos de interpretación
- **Req. 14:** “la estación tiene al menos un 50 % más de su capacidad” se interpreta como *pasajeros en espera ≥ 150 % de la capacidad de la estación*. Mientras haya una alerta abierta de una estación no se crea otra.
- **Req. 5:** el máximo (2n) siempre se exige. El mínimo (n) impide retirar buses de una línea que ya cumple, pero una línea recién creada puede quedar “incompleta” hasta que se le asignen buses.
- En los datos de ejemplo, el *Acceso Montúfar* de la estación Montúfar se deja **a propósito sin guardia** para mostrar cómo lo detecta el reporte.

## Reportes y queries (entregable)
Los reportes ejecutan **exactamente** los archivos de [database/sql/reportes/](database/sql/reportes/). Para el documento, copie el SQL desde ahí o con el botón **“Ver SQL”** de cada reporte.

- [1_estaciones.sql](database/sql/reportes/1_estaciones.sql)
- [2_lineas.sql](database/sql/reportes/2_lineas.sql)
- [3_buses_por_linea.sql](database/sql/reportes/3_buses_por_linea.sql)
- [4_accesos_por_linea.sql](database/sql/reportes/4_accesos_por_linea.sql)

El script de creación de la base está en [database/sql/schema.sql](database/sql/schema.sql) y se generó desde las migraciones con `mysqldump --no-data`.

## Ejecutar en local
Requisitos: PHP 8.3+, Composer y Docker (o MySQL/XAMPP).

```bash
docker run -d --name transmetro-mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=transmetro -p 3307:3306 mysql:8.4
cp .env.example .env          # ya apunta a 127.0.0.1:3307, usuario root/root
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://localhost:8000
```

Con XAMPP: cree la BD `transmetro` y en `.env` ponga `DB_PORT=3306`, `DB_PASSWORD=` (vacío).

Pruebas automáticas de las reglas de negocio: `php artisan test`.

## Desplegar en Railway
1. Suba este repositorio a GitHub.
2. En [railway.com](https://railway.com): **New Project → Deploy from GitHub repo** y elija el repositorio.
3. En el mismo proyecto: **+ Create → Database → MySQL**.
4. En el servicio de la app, abra **Variables → Raw Editor** y pegue:
   ```
   APP_NAME="Transmetro"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:...            # salida de: php artisan key:generate --show
   APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
   APP_LOCALE=es
   DB_CONNECTION=mysql
   DB_URL=${{MySQL.MYSQL_URL}}
   SESSION_DRIVER=database
   CACHE_STORE=database
   LOG_CHANNEL=stderr
   ```
5. En **Settings → Networking**, pulse **Generate Domain**.
6. Railway construye la app con Railpack (detecta Laravel). Antes de cada despliegue ejecuta `php artisan migrate --force --seed`, configurado en [railway.json](railway.json). El seeder solo carga datos si la base está vacía.

Cada `git push` a la rama principal vuelve a desplegar automáticamente.

## Estructura relevante
```
app/Services/ReglasTransmetro.php   Reglas de negocio (Req. 5, 14, 15)
app/Http/Controllers/               CRUD, panel de operador y reportes
database/migrations/                Modelo de datos
database/seeders/DatabaseSeeder.php Datos de demostración
database/sql/                       schema.sql y queries de reportes
resources/views/                    Vistas Blade + Bootstrap
tests/Feature/ReglasTransmetroTest.php
```
