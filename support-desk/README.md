# SupportDesk — Mesa de ayuda de Synapse Campus

Microservicio de tickets de soporte. No tiene usuarios propios: valida el JWT de AuthVault localmente con su clave pública, consulta a AuthVault el contexto de seguridad de una cuenta y reporta sus acciones a AuditLog.

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

- **Tickets** con ciclo de vida de cinco estados y una **matriz de transiciones estricta** (no se puede saltar de cualquier estado a cualquier otro).
- **Comentarios** internos y públicos, con visibilidad según el rol.
- **Asignación manual** de agentes.
- **Visibilidad por rol:** cada usuario ve lo que su rol permite.
- **Reapertura** de tickets: solo la puede hacer quien lo solicitó.
- **Security context:** consulta a AuthVault el diagnóstico de una cuenta (2FA, sesiones activas, bloqueo). Solo para `security_admin`.
- Reporta cada acción a AuditLog (patrón híbrido: registro local + envío best-effort).

### Scopes de usuario (en AuthVault)

`support:tickets:create`, `support:tickets:read`, `support:tickets:comment`, `support:tickets:assign` y `support:tickets:close`, asignados a los roles que corresponda (por ejemplo `student`, `teacher` y `support_agent`). Sin ellos el token exchange no emite un token útil para SupportDesk.

## Dependencias del ecosistema

| Servicio | Para qué | Variable |
|---|---|---|
| AuthVault | Clave pública, token de servicio y security context | `AUTHVAULT_BASE_URL` |
| AuditLog | Reportar eventos | `AUDITLOG_BASE_URL` |

AuthVault es imprescindible. Si AuditLog está caído, SupportDesk sigue funcionando (el reporte es best-effort). Si AuthVault está caído, el security context no responde y no se pueden validar tokens nuevos (la clave pública cacheada sigue sirviendo mientras no expire).

## Nombres de contenedor

El nginx de SupportDesk se llama **`support_desk_nginx`** (guion **bajo**), fijado por `container_name` en su `docker-compose.yml`. Quien lo llame (por ejemplo el Blade con `SUPPORTDESK_BASE_URL`) debe usar ese nombre exacto. Los demás servicios usan otros nombres: verifícalos con `docker ps`.

## Cómo levantarlo (primera vez)

1. **Redes Docker** (una sola vez): `synapse-campus-network` y `proxy_network`.
2. **Variables:** `cp .env.example .env` y rellenar. Revisa `DB_*`, `DB_ROOT_PASSWORD`, `AUTHVAULT_BASE_URL`, `AUDITLOG_BASE_URL`, `CLIENT_ID` y `CLIENT_SECRET`.
3. **Levantar:** `docker compose up -d --build`. El entrypoint espera a MariaDB y corre las migraciones.
4. **Clave de aplicación:** `php artisan key:generate` dentro del contenedor `app`.
5. **Service client:** copia el secret del seeder de AuthVault al `.env` (ver abajo).
6. **Seeders** propios de SupportDesk, si los tiene.
7. **Recrear el contenedor `app`** (no solo reiniciar) y **reiniciar el nginx**.
8. **Verificar:** una ruta protegida sin token responde 401; con un token válido, la lista de tickets responde 200.

### MariaDB y el healthcheck

El compose crea MariaDB con `MARIADB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}`, `MARIADB_DATABASE=${DB_DATABASE}`, `MARIADB_USER=${DB_USERNAME}` y `MARIADB_PASSWORD=${DB_PASSWORD}`, leídos del `.env`.

- Esas variables **solo se aplican la primera vez** que se crea el volumen `mariadb_data`. Si cambias una contraseña después, hay que borrar el volumen o cambiarla dentro de MariaDB.
- El healthcheck de MariaDB tiene la contraseña root **escrita a mano** (`-p12345678`). Debe ser igual a `DB_ROOT_PASSWORD`, o el healthcheck falla y `app` nunca arranca por el `depends_on: service_healthy`.

### Service client en AuthVault

SupportDesk pide tokens de servicio para reportar a AuditLog y para consultar el security context.

| Campo | Valor |
|---|---|
| `client_id` | `support-desk` (guion medio, exacto) |
| Scopes | `audit:events:write`, `internal:security-status:read` |

El seeder de service clients de AuthVault lo crea e **imprime el secret una sola vez**. Cópialo al `.env`:

```
CLIENT_ID=support-desk
CLIENT_SECRET=<secret impreso>
```

`config/app.php` lee `CLIENT_ID` y `CLIENT_SECRET` (sin prefijo `AUTHVAULT_`). El nombre del `.env` debe coincidir con el de la config. Si pierdes el secret no se puede recuperar: genera uno nuevo y actualiza el `.env`.

Si falta el scope `internal:security-status:read`, el security context responde 403 aunque el usuario sea `security_admin`.

## Variables de entorno clave

| Variable | Valor | Nota |
|---|---|---|
| `DB_CONNECTION` / `DB_HOST` | `mariadb` / nombre del servicio | No `sqlite` ni `127.0.0.1` |
| `DB_ROOT_PASSWORD` | contraseña root | La lee el compose, no Laravel; debe coincidir con el healthcheck |
| `SESSION_DRIVER` | `file` | Default de config es `database` y rompe |
| `CACHE_STORE` | `file` | Default `database`; aquí se cachea la clave pública y el token de servicio |
| `QUEUE_CONNECTION` | `sync` | No hay colas |
| `AUTH_GUARD` | `api` | |
| `AUTHVAULT_BASE_URL` | `http://auth-vault-nginx` | Nombre literal del contenedor |
| `AUDITLOG_BASE_URL` | `http://audit-log-nginx` | Nombre literal del contenedor |
| `CLIENT_ID` / `CLIENT_SECRET` | `support-desk` / secret del seeder | Sin prefijo `AUTHVAULT_` |

Las rutas de AuthVault (clave pública, token de servicio, security-status) y de AuditLog (eventos) van fijas en `config/services.php`, no son variables.

## Caché: qué limpiar y cuándo

- **Cambiaste el `.env`:** `config:clear` y **recrear** el contenedor `app` (con `env_file`, un restart no relee el archivo).
- **Rutas nuevas que no aparecen:** `route:clear`. Si una ruta nueva sigue sin responder, revisa el orden: las rutas estáticas (`/tickets/my`) deben ir **antes** de las dinámicas (`/tickets/{id}`).
- **`cache:clear`:** como el caché es `file`, solo borra la clave pública de AuthVault y el token de servicio; se regeneran solos en la siguiente petición.
- **Producción:** `config:cache`, `route:cache` y `view:cache` corren en el entrypoint, nunca en el Dockerfile. Con `config:cache` activo, `env()` fuera de `config/` devuelve `null`.

## Errores conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, `config:clear`, recrear contenedor |
| Error de SQLite o `no such table: sessions/cache` | `SESSION_DRIVER` o `CACHE_STORE` en `database` | Ponerlos en `file`, `config:clear`, recrear |
| `app` no arranca y MariaDB sale `unhealthy` | La contraseña del healthcheck (`-p12345678`) no coincide con `DB_ROOT_PASSWORD` | Igualarlas o hacer que el healthcheck lea la variable |
| Cambié la contraseña de BD en el `.env` y no pasa nada | Las variables `MARIADB_*` solo aplican al crear el volumen | Recrear el volumen `mariadb_data` (borra los datos) o cambiarla dentro de MariaDB |
| `bad address 'auth_vault_nginx'` (o de otro servicio) | Nombre de contenedor distinto al de la URL | `docker ps`, copiar el nombre exacto (AuthVault: guiones medios; SupportDesk: `support_desk_nginx`) |
| 502 en el nginx, `Connection refused` a `:9000` | Nginx recuerda la IP vieja de `app` o PHP-FPM no arrancó | Reiniciar el nginx; si persiste, revisar los logs de `app` |
| 500 y no existe `storage/logs/laravel.log` | `storage` sin permisos para PHP-FPM | `chown -R www-data:www-data storage bootstrap/cache` en el entrypoint, antes de artisan |
| 400 `Format's bearer token invalid` / `Incorrect key for this algorithm` | El `JWTCheck` usa un algoritmo distinto al de AuthVault | Todos los servicios validan con `'RS512'` |
| 401 al llamar a AuthVault o AuditLog | Token de servicio expirado (caché mayor que el TTL de 15 min) o `CLIENT_SECRET` incorrecto | Cachear el token menos de 15 min; revisar el secret |
| 403 en el security context | Falta `internal:security-status:read` en el service client, o el usuario no es `security_admin` | Revisar scopes del client y rol del usuario |
| 403 al operar tickets | El usuario no tiene el scope `support:tickets:*` correspondiente | Revisar roles y scopes en AuthVault |
| AuditLog no recibe eventos y no hay errores | Variable mal escrita: `config()` da `null` y el `catch` silencioso lo oculta | Comprobar `config('services.auditlog.base_url')` en tinker; registrar el fallo en el `catch` |
| Una ruta como `/tickets/my` devuelve 404 o se trata como ID | Una ruta dinámica `{id}` la captura antes | Declarar las estáticas por encima de las dinámicas |
| Cualquier ruta da 404 tras recrear contenedores | DNS interno desincronizado | Reiniciar solo el nginx |

## Orden de diagnóstico rápido

1. ¿El contenedor resuelve? (`docker ps` y comparar nombres).
2. ¿MariaDB está `healthy`? Si no, revisa la contraseña del healthcheck.
3. ¿502? Problema entre nginx y PHP-FPM.
4. ¿500? Leer `laravel.log` (si no existe, permisos).
5. ¿400 o 401? Algoritmo, clave pública y expiración del token.
6. ¿403? Scopes del usuario o del service client.
7. ¿Auditoría o security context sin llegar? Variables `*_BASE_URL`, service client y logs del `catch`.

## Producción

- Imagen unificada PHP-FPM + Nginx vía supervisord (en producción no hay contenedor nginx separado).
- Este servicio solo usa la clave pública de AuthVault (`GET /api/auth/public-key`); no guarda llaves.
- En producción no se monta el código (`.:/var/www/html` es solo de desarrollo).
- `.env` nunca en la imagen ni en el repo. `APP_DEBUG=false`.