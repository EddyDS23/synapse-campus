# AuditLog — Auditoría centralizada de Synapse Campus

Microservicio que recibe y almacena de forma **inmutable** los eventos importantes de todo el ecosistema. Solo escriben los servicios; leen servicios y roles privilegiados.

- **Puerto dev:** 9005
- **Base de datos:** `audit_logs_system` (MariaDB)
- **Sin Redis**, sin colas, sin sesión web

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 (`^13.0`) | Framework |
| MariaDB 11.4 | Tabla `audit_logs` |
| `firebase/php-jwt` | Validación local del JWT con la clave pública de AuthVault |
| Scramble | Documentación de API (`api/internal` excluido) |
| Docker + Nginx propio | Infraestructura |

## Qué hace

- Registra eventos: actor, servicio de origen, acción, recurso, IP y metadata (JSON).
- Tabla inmutable: sin `updated_at`, solo `created_at`.
- Consulta con filtros combinables: `actor_id`, `service`, `action`, `from`, `to`, `per_page`.
- **No es service client**: no pide tokens a AuthVault. Solo valida los JWT que recibe.

## Endpoints

| Método | Ruta | Middleware | Quién |
|---|---|---|---|
| POST | `/api/internal/events` | `service.check:audit:events:write` | Servicios (token de servicio) |
| GET | `/api/internal/events` | `service.check:audit:events:read` | Servicios |
| GET | `/api/events` | `scope.check:audit:events:read` | Humanos (`security_admin`, `super_admin`) vía Token Exchange con `aud: audit-log` |

## Cómo levantarlo

1. **Redes Docker** (si no existen): `synapse-campus-network` y `proxy_network`.
2. **Variables:** `cp .env.example .env` y rellenar (ver abajo). Revisa `DB_*` y `AUTHVAULT_BASE_URL`.
3. **Levantar:** `docker compose up -d --build`.
4. **Clave de aplicación:** `php artisan key:generate` dentro del contenedor `app`.
5. **Migraciones:** las corre el entrypoint (espera a MariaDB). No hay seeders obligatorios.
6. **Recrear `app` y reiniciar nginx** si cambiaste el `.env`.
7. **Verificar:** haz un login en el Blade y comprueba que aparece un evento de `auth-vault` en la tabla.

### Prerrequisito en AuthVault

Los servicios que reportan (AuthVault, StudentPortal, LibraryCore, SupportDesk, FileStore) necesitan el scope `audit:events:write` en su service client. Eso se asigna con el seeder de service clients de AuthVault.

## Variables de entorno

| Variable | Valor | Nota |
|---|---|---|
| `DB_CONNECTION` | `mariadb` | No `sqlite` |
| `DB_HOST` | nombre del servicio `mariadb` | |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | los mismos del compose | Ver sección siguiente |
| `SESSION_DRIVER` | `file` | `database` provoca error de `database.sqlite` |
| `CACHE_STORE` | `file` | Guarda la clave pública de AuthVault |
| `QUEUE_CONNECTION` | `sync` | |
| `AUTHVAULT_BASE_URL` | nombre literal del nginx de AuthVault | Dev: `http://auth-vault-nginx` |
| `AUTHVAULT_PUBLIC_KEY_CACHE_TTL` | `3600` | Segundos |

No lleva `AUTHVAULT_CLIENT_ID` ni `AUTHVAULT_CLIENT_SECRET`.

## Docker Compose: usar las variables del `.env`

Para no repetir credenciales en dos sitios, el `docker-compose.yml` debe leerlas del `.env`:

- **Servicio `mariadb`:** sus variables de inicialización (`MARIADB_DATABASE`, `MARIADB_USER`, `MARIADB_PASSWORD`, `MARIADB_ROOT_PASSWORD`) se alimentan con interpolación `${DB_DATABASE}`, `${DB_USERNAME}`, `${DB_PASSWORD}`. Compose lee automáticamente el `.env` de la carpeta del proyecto para esa interpolación.
- **Servicio `app`:** usa `env_file: .env` para recibir el resto de variables.

**Trampas:**

- **Las variables `MARIADB_*` solo se aplican la primera vez** que se crea el volumen de datos. Si cambias usuario o contraseña después, MariaDB las ignora y el login falla con `Access denied`. Solución en desarrollo: `docker compose down -v` (borra el volumen) y volver a levantar. Con datos que importan, cambia las credenciales desde dentro de MariaDB.
- **`env_file` e interpolación son cosas distintas.** `env_file` inyecta variables al contenedor al **crearlo**; la interpolación `${VAR}` se resuelve al leer el compose. En ambos casos, tras cambiar el `.env` hay que **recrear** el contenedor, no basta reiniciar.
- **Valores con `$`** en una contraseña deben escaparse como `$$` en el compose.

## Caché: qué limpiar y cuándo

- Cambiaste el `.env`: `config:clear` y recrear `app`.
- Rutas nuevas que no aparecen: `route:clear`.
- **Clave pública en caché vacía o inválida:** si el primer intento de obtenerla falló y se cacheó vacía, todos los requests siguientes fallan con *OpenSSL unable to validate key*. Solución: `cache:clear`. Aquí es seguro porque la caché es `file` y solo contiene eso.
- Producción: `config:cache`, `route:cache` y `view:cache` en el entrypoint, nunca en el Dockerfile.

## Errores conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| `Database file ... database.sqlite does not exist` | `DB_CONNECTION`, `SESSION_DRIVER` o `CACHE_STORE` en `sqlite`/`database` | Poner `mariadb`, `file`, `file`; `config:clear`; recrear |
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, recrear contenedor |
| `Access denied for user` en migraciones | Credenciales del `.env` distintas a las con las que se creó el volumen de MariaDB | `down -v` y levantar de nuevo, o alinear credenciales |
| 502 en su nginx tras recrear `app` | Nginx conserva la IP vieja | Reiniciar solo el nginx |
| 500 y no existe `storage/logs/laravel.log` | Permisos de `storage` | `chown -R www-data:www-data storage bootstrap/cache` en el entrypoint (como root, antes de artisan) |
| `OpenSSL unable to validate key` | Clave pública cacheada vacía | `cache:clear` |
| 503 al llamar con token | AuthVault no responde o URL mal escrita | Revisar `AUTHVAULT_BASE_URL` contra `docker ps`; probar `public-key` con curl/wget entre contenedores |
| 401 con token válido | `aud` distinto de `audit-log` o token de usuario en ruta interna | Hacer Token Exchange con `audience: audit-log`; las rutas internas exigen token de servicio |
| 403 al escribir un evento | El service client no tiene `audit:events:write` | Añadir el scope en el seeder de AuthVault y volver a correrlo |
| `Field 'ip_address' doesn't have a default value` | `ip_address` falta en las reglas del `FormRequest` | Añadirlo a las reglas de validación |
| Los servicios no pueden llegar a AuditLog | Nombre del contenedor distinto al de su URL, o falta `synapse-campus-network` | `docker ps` y comparar con `AUDITLOG_BASE_URL` del servicio que reporta |

## Notas de diseño

- **Patrón híbrido:** cada servicio guarda su auditoría local y reporta aquí en modo best-effort. Si AuditLog cae, el evento sigue en la tabla local del servicio.
- **Inmutable por diseño:** no hay endpoints de edición ni borrado.
- **Pendiente antes de producción real:** política de retención (comando schedulado que purgue o archive logs antiguos) e índices en `actor_id`, `action`, `service`.

## Producción

- Imagen `eddyds23/audit-log:2.1` (PHP-FPM + Nginx vía supervisord).
- `.env` nunca en la imagen ni en el repo. `APP_DEBUG=false`.