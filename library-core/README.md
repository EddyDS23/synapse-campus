# LibraryCore — Biblioteca de Synapse Campus

Microservicio de biblioteca: catálogo, préstamos, renovaciones y multas. No tiene usuarios propios: valida el JWT de AuthVault localmente con su clave pública, consulta el estado académico en StudentPortal, le notifica las deudas y reporta sus acciones a AuditLog.

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 (`^13.0`) | Framework |
| MariaDB 11.4 | Base de datos |
| JWT RS512 | Validación con `firebase/php-jwt` (solo decodifica) |
| Scramble | Documentación de API |
| Docker + Nginx por servicio | Infraestructura |

No usa Redis. Sesión y caché van en archivos.

## Qué hace

- **Catálogo** con filtros (título, autor, categoría, disponibilidad).
- **Préstamos** con validación cruzada del estado académico del estudiante en StudentPortal.
- **Renovaciones** con límite configurable (`BOOK_RENEW_LIMIT`).
- **Multas** calculadas al devolver: días de retraso × `FINE_PER_DAY`. El vencimiento (`overdue`) se calcula en ejecución, no se guarda.
- **Notificación de deuda** a StudentPortal (contador `debt_count`), con reintento programado si falla.
- **Inventario** (alta y baja de libros): solo rol `librarian`.
- Reporta cada acción a AuditLog (patrón híbrido: registro local + envío best-effort).

## Dependencias del ecosistema

| Servicio | Para qué | Variable |
|---|---|---|
| AuthVault | Clave pública y token de servicio | `AUTHVAULT_BASE_URL` |
| StudentPortal | Consultar estado académico y notificar deuda | `STUDENTPORTAL_BASE_URL` |
| AuditLog | Reportar eventos | `AUDITLOG_BASE_URL` |

AuthVault es imprescindible. Si StudentPortal está caído, no se pueden validar préstamos ni notificar deudas. Si AuditLog está caído, LibraryCore sigue funcionando (el reporte es best-effort).

## Cómo levantarlo (primera vez)

1. **Redes Docker** (una sola vez): `synapse-campus-network` y `proxy_network`.
2. **Variables:** `cp .env.example .env` y rellenar. Revisa `DB_*`, las tres `*_BASE_URL`, `CLIENT_ID`, `CLIENT_SECRET` y las reglas de negocio.
3. **Levantar:** `docker compose up -d --build`. El entrypoint espera a MariaDB y corre las migraciones.
4. **Clave de aplicación:** `php artisan key:generate` dentro del contenedor `app`.
5. **Service client:** copia el secret del seeder de AuthVault al `.env` (ver abajo).
6. **Seeders** propios de LibraryCore, si los tiene (libros de ejemplo, etc.).
7. **Recrear el contenedor `app`** (no solo reiniciar) y **reiniciar el nginx**.
8. **Verificar:** una ruta protegida sin token responde 401; con un token válido, el catálogo responde 200.

### Service client en AuthVault

LibraryCore pide tokens de servicio para hablar con StudentPortal y AuditLog.

| Campo | Valor |
|---|---|
| `client_id` | `library-core` (guion medio, exacto) |
| Scopes | `audit:events:write`, `internal:student-status:read`, `internal:student-debt:write` |

El seeder de service clients de AuthVault lo crea e **imprime el secret una sola vez**. Cópialo al `.env`:

```
CLIENT_ID=library-core
CLIENT_SECRET=<secret impreso>
```

`config/app.php` lee `CLIENT_ID` y `CLIENT_SECRET` (sin prefijo `AUTHVAULT_`). El nombre del `.env` debe coincidir con el de la config. Si pierdes el secret no se puede recuperar: genera uno nuevo y actualiza el `.env`.

Si falta el scope `internal:student-debt:write`, la notificación de deuda responde 403 y se queda en reintento sin que el usuario lo note.

## Variables de entorno clave

| Variable | Valor | Nota |
|---|---|---|
| `DB_CONNECTION` / `DB_HOST` | `mariadb` / nombre del servicio | No `sqlite` ni `127.0.0.1` |
| `SESSION_DRIVER` | `file` | Default de config es `database` y rompe |
| `CACHE_STORE` | `file` | Default `database`; aquí se cachea la clave pública y el token de servicio |
| `QUEUE_CONNECTION` | `sync` | No hay colas |
| `AUTH_GUARD` | `api` | |
| `AUTHVAULT_BASE_URL` | `http://auth-vault-nginx` | Nombre literal del contenedor |
| `STUDENTPORTAL_BASE_URL` | `http://nginx-student-portal` | Ojo: guiones medios |
| `AUDITLOG_BASE_URL` | `http://audit-log-nginx` | Nombre literal del contenedor |
| `CLIENT_ID` / `CLIENT_SECRET` | `library-core` / secret del seeder | Sin prefijo `AUTHVAULT_` |
| `DUE_AT` | p. ej. `7` | Vencimiento del préstamo. **Sin valor por defecto** |
| `BOOK_RENEW_LIMIT` | p. ej. `2` | Máximo de renovaciones. **Sin valor por defecto** |
| `FINE_PER_DAY` | p. ej. `5` | Multa por día de retraso. **Sin valor por defecto** |

Las rutas de AuthVault (clave pública, token de servicio), de StudentPortal (estado, deuda) y de AuditLog (eventos) van fijas en `config/services.php`, no son variables.

### Reglas de negocio: cuidado con los valores vacíos

`DUE_AT`, `BOOK_RENEW_LIMIT` y `FINE_PER_DAY` se leen sin valor por defecto. Si faltan en el `.env`, valen `null` y el fallo es silencioso:

- Sin `BOOK_RENEW_LIMIT`, nadie puede renovar un préstamo.
- Sin `FINE_PER_DAY`, todas las multas salen en 0.
- Sin `DUE_AT`, el vencimiento del préstamo no es el esperado.

## Reintento de notificaciones de deuda

Si StudentPortal no responde al notificar una deuda, el aviso queda pendiente y un **comando programado** lo reintenta. Para que funcione:

- El entrypoint debe lanzar `php artisan schedule:work &` antes de `exec php-fpm` (así no hace falta cron del sistema en Docker).
- El `app` debe estar en `synapse-campus-network` para poder llamar a StudentPortal.
- El nombre del comando está en `php artisan list`; para forzar un reintento manual, ejecútalo desde el contenedor `app`.
- Si el reintento no corre, revisa que el proceso `schedule:work` siga vivo en el contenedor.

## Caché: qué limpiar y cuándo

- **Cambiaste el `.env`:** `config:clear` y **recrear** el contenedor `app` (con `env_file`, un restart no relee el archivo).
- **Rutas nuevas que no aparecen:** `route:clear`.
- **`cache:clear`:** como el caché es `file`, solo borra la clave pública de AuthVault y el token de servicio; se regeneran solos en la siguiente petición.
- **Producción:** `config:cache`, `route:cache` y `view:cache` corren en el entrypoint, nunca en el Dockerfile. Con `config:cache` activo, `env()` fuera de `config/` devuelve `null`.

## Errores conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, `config:clear`, recrear contenedor |
| Error de SQLite o `no such table: sessions/cache` | `SESSION_DRIVER` o `CACHE_STORE` en `database` | Ponerlos en `file`, `config:clear`, recrear |
| `bad address 'auth_vault_nginx'` (o de otro servicio) | Nombre de contenedor distinto al de la URL | `docker ps`, copiar el nombre exacto (dev: guiones medios) |
| 502 en el nginx, `Connection refused` a `:9000` | Nginx recuerda la IP vieja de `app` o PHP-FPM no arrancó | Reiniciar el nginx; si persiste, revisar los logs de `app` |
| 500 y no existe `storage/logs/laravel.log` | `storage` sin permisos para PHP-FPM | `chown -R www-data:www-data storage bootstrap/cache` en el entrypoint, antes de artisan |
| 400 `Format's bearer token invalid` / `Incorrect key for this algorithm` | El `JWTCheck` usa un algoritmo distinto al de AuthVault | Todos los servicios validan con `'RS512'` |
| 401 al llamar a StudentPortal o AuditLog | Token de servicio expirado (caché mayor que el TTL de 15 min) o `CLIENT_SECRET` incorrecto | Cachear el token menos de 15 min; revisar el secret |
| 403 al notificar deuda o consultar estado | Faltan scopes en el service client `library-core` | Volver a sembrar scopes y revisar `internal:student-*` |
| AuditLog no recibe eventos y no hay errores | Variable mal escrita: `config()` da `null` y el `catch` silencioso lo oculta | Comprobar `config('services.auditlog.base_url')` en tinker; registrar el fallo en el `catch` |
| Las multas salen siempre en 0 o no se puede renovar | `FINE_PER_DAY` o `BOOK_RENEW_LIMIT` vacíos | Definirlos en el `.env` y recrear `app` |
| La deuda no llega a StudentPortal | StudentPortal caído, scope faltante o `schedule:work` no corre | Revisar logs, scopes y el proceso del scheduler |
| Cualquier ruta da 404 tras recrear contenedores | DNS interno desincronizado | Reiniciar solo el nginx |

## Orden de diagnóstico rápido

1. ¿El contenedor resuelve? (`docker ps` y comparar nombres).
2. ¿502? Problema entre nginx y PHP-FPM.
3. ¿500? Leer `laravel.log` (si no existe, permisos).
4. ¿400 o 401? Algoritmo, clave pública y expiración del token.
5. ¿403? Scopes del usuario o del service client.
6. ¿Préstamos rechazados? Estado académico: ¿responde StudentPortal y existe el estudiante con los mismos ULID de AuthVault?
7. ¿Deuda o auditoría sin llegar? Variables `*_BASE_URL`, service client y logs del `catch`.

## Producción

- Imagen unificada PHP-FPM + Nginx vía supervisord.
- Este servicio solo usa la clave pública de AuthVault (`GET /api/auth/public-key`); no guarda llaves.
- `.env` nunca en la imagen ni en el repo. `APP_DEBUG=false`.
- Define siempre `DUE_AT`, `BOOK_RENEW_LIMIT` y `FINE_PER_DAY` en el `.env` de producción.