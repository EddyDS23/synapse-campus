# FileStore — Almacenamiento de archivos de Synapse Campus

Microservicio que recibe, guarda, sirve y elimina archivos (fotos, PDF) para el ecosistema. No tiene usuarios propios: valida el JWT de AuthVault localmente con su clave pública y reporta sus acciones a AuditLog.

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 (`^13.0`) | Framework |
| MariaDB 11.4 | Metadatos de los archivos |
| Disco `files` (filesystem local) | Contenido de los archivos |
| JWT RS512 | Validación con `firebase/php-jwt` (solo decodifica) |
| Scramble | Documentación de API |
| Docker + Nginx por servicio | Infraestructura |

No usa Redis. Sesión y caché van en archivos.

## Qué hace

- `POST /api/files` — subir un archivo (scope `files:upload`)
- `GET /api/files/{id}` — obtener un archivo (scope `files:read`)
- `DELETE /api/files/{id}` — eliminar un archivo (scope `files:delete`)
- Reporta cada acción a AuditLog (patrón híbrido: registro local + envío best-effort)

### Cómo guarda los archivos

- Disco dedicado `files` (`config/filesystems.php`) apuntando a `storage/app/files`. No es el disco `local` por defecto.
- Ruta: `{año}/{mes}/{uuid}`. El directorio sale de `now()->format('Y/m')` y **no** lleva el prefijo del disco (si se incluye, la ruta se duplica).
- `stored_name` es un UUID **sin extensión**; la extensión vive en su propia columna.
- **Primero BD, luego disco:** se inserta el registro, se escribe el archivo y, si el disco falla, se revierte el registro a mano. Así no quedan huérfanos.
- Tamaño máximo por archivo: `MAX_FILE_SIZE_KB` (por defecto 5120 = 5 MB).

## Cómo levantarlo (primera vez)

1. **Redes Docker** (una sola vez): `synapse-campus-network` y `proxy_network`.
2. **Variables:** `cp .env.example .env` y rellenar. Revisa sobre todo `DB_*`, `AUTHVAULT_BASE_URL`, `AUDITLOG_BASE_URL`, `CLIENT_ID` y `CLIENT_SECRET`.
3. **Levantar:** `docker compose up -d --build`. El entrypoint espera a MariaDB y corre las migraciones.
4. **Clave de aplicación:** `php artisan key:generate` dentro del contenedor `app`.
5. **Service client:** FileStore necesita su identidad en AuthVault (ver abajo). Copia el secret al `.env`.
6. **Recrear el contenedor `app`** (no solo reiniciar) y **reiniciar el nginx**.
7. **Verificar:** una petición a una ruta protegida sin token debe responder 401. Con un token válido de un usuario con scope `files:upload`, subir un archivo debe responder 201.

### Service client en AuthVault

FileStore pide tokens de servicio para reportar a AuditLog, así que necesita su identidad.

| Campo | Valor |
|---|---|
| `client_id` | `file-store` (guion medio, exacto) |
| Scopes | `audit:events:write` |

El seeder de service clients de AuthVault lo crea e **imprime el secret una sola vez**. Cópialo al `.env` de FileStore:

```
CLIENT_ID=file-store
CLIENT_SECRET=<secret impreso>
```

Ojo: `config/authvault.php` lee `CLIENT_ID` y `CLIENT_SECRET` (sin prefijo `AUTHVAULT_`). El nombre del `.env` debe coincidir con el de la config. Si pierdes el secret no se puede recuperar: genera uno nuevo y actualiza el `.env`.

### Scopes de usuario (en AuthVault)

`files:upload`, `files:read` y `files:delete` deben estar sembrados y asignados a los roles que corresponda. Sin ellos, el token exchange no emite un token útil para FileStore.

## Variables de entorno clave

| Variable | Valor | Nota |
|---|---|---|
| `DB_CONNECTION` / `DB_HOST` | `mariadb` / nombre del servicio | No `sqlite` ni `127.0.0.1` |
| `SESSION_DRIVER` | `file` | Default de config es `database` y rompe |
| `CACHE_STORE` | `file` | Default `database`; aquí se cachea la clave pública y el token de servicio |
| `QUEUE_CONNECTION` | `sync` | No hay colas |
| `FILESYSTEM_DISK` | `local` | El disco de archivos es `files`, definido en config |
| `MAX_FILE_SIZE_KB` | `5120` | Límite de aplicación (ver errores: nginx y PHP tienen los suyos) |
| `AUTH_GUARD` | `api` | |
| `AUTHVAULT_BASE_URL` | `http://auth-vault-nginx` | Nombre literal del contenedor |
| `CLIENT_ID` / `CLIENT_SECRET` | `file-store` / secret del seeder | Sin prefijo `AUTHVAULT_` |
| `AUDITLOG_BASE_URL` | `http://audit-log-nginx` | Nombre literal; el endpoint va fijo en el código |

## Persistencia de archivos (importante)

El volumen `filestore_data` debe montarse en `storage/app/files`. Sin él, los archivos se pierden al recrear el contenedor mientras la BD sigue apuntando a rutas que ya no existen. En producción, comprueba el volumen antes de actualizar la imagen.

## Caché: qué limpiar y cuándo

- **Cambiaste el `.env`:** `config:clear` y **recrear** el contenedor `app` (con `env_file`, un restart no relee el archivo).
- **Rutas nuevas que no aparecen:** `route:clear`.
- **`cache:clear`:** como el caché es `file`, solo borra la clave pública de AuthVault y el token de servicio; se regeneran solos en la siguiente petición.
- **Producción:** `config:cache`, `route:cache` y `view:cache` corren en el entrypoint, nunca en el Dockerfile.

## Errores conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, `config:clear`, recrear contenedor |
| `no such table: sessions/cache` o error de SQLite | `SESSION_DRIVER` o `CACHE_STORE` en `database` | Ponerlos en `file`, `config:clear`, recrear |
| `bad address 'auth_vault_nginx'` | Nombre de contenedor distinto al de la URL | `docker ps`, copiar el nombre exacto (dev: guiones medios) |
| 502 en el nginx, `Connection refused` a `:9000` | Nginx recuerda la IP vieja de `app` o PHP-FPM no arrancó | Reiniciar el nginx; si persiste, revisar los logs de `app` |
| 500 y no existe `storage/logs/laravel.log` | `storage` sin permisos para PHP-FPM | `chown -R www-data:www-data storage bootstrap/cache` en el entrypoint, antes de artisan |
| 400 `Format's bearer token invalid` / `Incorrect key for this algorithm` | El `JWTCheck` usa un algoritmo distinto al de AuthVault | Todos los servicios validan con `'RS512'` |
| 401 al llamar a AuditLog desde FileStore | Token de servicio expirado (caché mayor que el TTL de 15 min) o `CLIENT_SECRET` incorrecto | Cachear el token menos de 15 min; revisar el secret |
| AuditLog no recibe eventos y no hay errores | Variable mal escrita (`AUDITLOGS_...` en vez de `AUDITLOG_...`): `config()` da `null` y el `catch` silencioso lo oculta | Comprobar `config('auditlog.base_url')` en tinker; registrar el fallo en el `catch` |
| 413 al subir un archivo grande | Límite de nginx o PHP antes de llegar a Laravel | Subir `client_max_body_size` (nginx), `upload_max_filesize` y `post_max_size` (PHP) a un valor mayor que `MAX_FILE_SIZE_KB` |
| Los archivos desaparecen tras recrear el contenedor | `storage/app/files` sin volumen | Montar `filestore_data` |
| Ruta del archivo duplicada (`2026/11/2026/11/...`) | El directorio incluye el prefijo del disco o el año/mes dos veces | Usar solo `now()->format('Y/m')` |
| Cualquier ruta da 404 tras recrear contenedores | DNS interno desincronizado | Reiniciar solo el nginx |

## Orden de diagnóstico rápido

1. ¿El contenedor resuelve? (`docker ps` y comparar nombres).
2. ¿502? Problema entre nginx y PHP-FPM.
3. ¿500? Leer `laravel.log` (si no existe, permisos).
4. ¿400 o 401? Algoritmo, clave pública y expiración del token.
5. ¿403? Faltan scopes `files:*` en el token o en AuthVault.
6. ¿Sube pero AuditLog no lo ve? Variable `AUDITLOG_BASE_URL`, service client y logs del `catch`.

## Producción

- Imagen unificada PHP-FPM + Nginx vía supervisord.
- Llaves JWT: este servicio solo usa la clave pública de AuthVault (`GET /api/auth/public-key`); no guarda llaves.
- Volumen `filestore_data` persistente en `storage/app/files`.
- `.env` nunca en la imagen ni en el repo. `APP_DEBUG=false`.