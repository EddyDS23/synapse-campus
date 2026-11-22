# AuthVault — Identity Provider de Synapse Campus

Único servicio que maneja usuarios, contraseñas, sesiones y tokens. Los demás servicios validan el JWT localmente con su clave pública y no tienen tabla de usuarios.

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 (`^13.0`) | Framework |
| MariaDB 11.4 | Base de datos |
| Redis | Rate limiting y sesiones revocadas |
| JWT RS512 (RSA-4096) | `php-open-source-saver/jwt-auth` |
| Socialite | OAuth GitHub |
| google2fa-laravel + bacon-qr-code | 2FA TOTP |
| Scramble | Documentación de API |
| Docker + Nginx por servicio | Infraestructura |

## Qué ofrece

Login/logout, refresh token con rotación, Token Exchange, 2FA TOTP con códigos de recuperación, OAuth GitHub, sesiones activas, roles y scopes institucionales, service clients (client credentials), auditoría local + reporte a AuditLog, registro de usuarios solo por admin.

## Cómo levantarlo (primera vez)

1. **Redes Docker** (una sola vez): `synapse-campus-network` y `proxy_network`.
2. **Variables:** `cp .env.example .env` y rellenar. Revisa sobre todo `DB_*`, `REDIS_HOST` y `AUDITLOG_BASE_URL`.
3. **Levantar:** `docker compose up -d --build`. El entrypoint espera a MariaDB y corre las migraciones.
4. **Clave de aplicación:** `php artisan key:generate` dentro del contenedor `app`.
5. **Llaves JWT:** `php artisan jwt:generate-certs` dentro de `app`. Genera `storage/certs/jwt-rsa-4096-private.pem` y `...-public.pem`. Si dice *"Private cert already exists"*, las llaves ya existen: revisa que las rutas del `.env` apunten a ellas (o usa `--force` para regenerar, lo que invalida todos los tokens).
6. **Seeders**, en este orden (ver sección siguiente).
7. **Recrear el contenedor `app`** (no solo reiniciar) y **reiniciar el nginx**.
8. **Verificar:** `GET /api/auth/public-key` debe devolver el PEM; después, un login.

## Seeders (orden obligatorio)

1. **Roles** institucionales: `student`, `teacher`, `librarian`, `support_agent`, `academic_admin`, `security_admin`, `super_admin`.
2. **Scopes** (catálogo completo, incluidos los `files:*` de FileStore) y su asignación a roles.
3. **Service clients** (necesita los scopes ya sembrados).
4. **Usuarios de prueba** (solo desarrollo): uno por rol, `{rol}@test.com`. Nunca en producción.

### Service clients

Cada servicio que llama a otro (o reporta a AuditLog) necesita su identidad en AuthVault: un `client_id`, un secret hasheado y sus scopes.

| `client_id` | Servicio | Nota |
|---|---|---|
| `auth-vault` | AuthVault | Firma su token por dentro sin validar secret. El `client_id` está **hardcodeado** en `AuditLogServiceClient`: debe coincidir exacto con el sembrado |
| `student-portal` | StudentPortal | |
| `library-core` | LibraryCore | |
| `support-desk` | SupportDesk | |
| `file-store` | FileStore | Solo si reporta a AuditLog |

AuditLog es solo receptor y no necesita service client.

Convención: **guion medio** en todos los `client_id`. Los scopes de cada cliente salen de las rutas `service.check:<scope>` de los servicios que consume (esa es la fuente de verdad).

**Cómo funciona el seeder:** es idempotente (busca por `client_id`, no recrea), genera el secret en claro, guarda solo el hash (bcrypt) e **imprime el secret una única vez** en la consola. Cópialo en ese momento al `.env` del servicio:

```
AUTHVAULT_CLIENT_ID=<client_id>
AUTHVAULT_CLIENT_SECRET=<secret impreso>
```

Si lo pierdes no se puede recuperar: genera uno nuevo y actualiza el `.env` de ese servicio.

> Revisa este apartado contra tu seeder real; está descrito según el diseño acordado.

### Datos de prueba y ULIDs

Los servicios que no tienen tabla de usuarios (StudentPortal) guardan ULIDs de AuthVault (`student_id`, `teacher_id`). Si se recrea la BD de AuthVault, los usuarios de prueba nacen con ULIDs nuevos y los seeders de esos servicios quedan apuntando a IDs que ya no existen. Mantén ULIDs fijos en los usuarios de prueba, o actualiza los seeders de los otros servicios tras recrear.

## Variables de entorno clave

| Variable | Valor | Nota |
|---|---|---|
| `DB_CONNECTION` / `DB_HOST` | `mariadb` / nombre del servicio | No `sqlite` ni `127.0.0.1` |
| `CACHE_STORE` | `redis` | Rate limiting y revocaciones. Con `file` se pierden al reiniciar |
| `REDIS_HOST` | nombre del servicio redis | |
| `SESSION_DRIVER` | `file` | API: no hay sesión web |
| `JWT_ALGO` | `RS512` | No se usa `JWT_SECRET` |
| `JWT_PRIVATE_KEY` / `JWT_PUBLIC_KEY` | rutas `file://` a los `.pem` reales | |
| `JWT_TTL` | `15` | Minutos |
| `MAX_ATTEMPTS_LOGIN` | `5` | No dejar vacío (castea a 0) |
| `GITHUB_REDIRECT_URI` | callback del Blade (`/auth/github/callback`) | Misma URL en la GitHub OAuth App |
| `AUDITLOG_BASE_URL` | nombre literal del nginx de AuditLog | |

## Caché: qué limpiar y cuándo

- **Cambiaste el `.env`:** `config:clear` y **recrear** el contenedor `app` (con `env_file`, un restart no relee el archivo).
- **Rutas nuevas que no aparecen:** `route:clear`.
- **`cache:clear` tiene efectos reales en AuthVault:** como la caché es Redis, borra también las **sesiones revocadas** y los **contadores de rate limiting**. Úsalo con criterio.
- **Producción:** `config:cache`, `route:cache` y `view:cache` corren en el entrypoint, nunca en el Dockerfile.

## Errores conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, `config:clear`, recrear contenedor |
| `bad address 'auth_vault_nginx'` desde otro servicio | Nombre de contenedor distinto al de la URL, o contenedor no levantado | `docker ps`, copiar el nombre exacto a `AUTHVAULT_BASE_URL` |
| 502 en el nginx de AuthVault, `Connection refused` a `:9000` | Nginx recuerda la IP vieja de `app` (se recreó después de nginx), o PHP-FPM no arrancó | Reiniciar el nginx; si persiste, revisar los logs del `app` (bucle de espera de MariaDB, error de migración) |
| 500 y no existe `storage/logs/laravel.log` | `storage` sin permisos de escritura para el usuario de PHP-FPM | `chown -R www-data:www-data storage bootstrap/cache` en el entrypoint, **antes** de los comandos artisan. Solo funciona si el entrypoint corre como root; con `user: 1000:1000` hay que arreglar el dueño desde el host |
| `InvalidKeyProvided ... No such file or directory` al hacer login | `JWT_PRIVATE_KEY` apunta a una ruta que no existe | Usar los nombres reales (`jwt-rsa-4096-*.pem`) y recrear el contenedor |
| `jwt:generate-certs` → *Private cert already exists* | Las llaves ya existen | No regenerar; corregir las rutas del `.env` |
| `TypeError: fromUser(): Argument #1 must be JWTSubject, null given` en `AuditLogServiceClient` | No existe el service client de AuthVault en la BD | Correr el seeder de service clients. Además, `sendLog()` solo captura `ConnectionException`: conviene que ante un cliente inexistente registre un aviso y retorne `false`, para que el reporte a AuditLog nunca rompa el login |
| Login responde 401 aunque todo funcione | BD vacía, sin usuarios (el registro público está cerrado) | Correr el seeder de usuarios de prueba |
| Login correcto pero perfil vacío en StudentPortal | ULIDs de usuario cambiaron al recrear la BD | Ver "Datos de prueba y ULIDs" |
| Cualquier ruta del nginx da 404 tras recrear contenedores | DNS interno desincronizado | Reiniciar solo el nginx |
| Un servicio consumidor responde 400 `Format's bearer token invalid` / `Incorrect key for this algorithm` | El algoritmo con que el servicio construye la clave (`new Key($publicKey, '...')`) no coincide con el de AuthVault | AuthVault firma con **RS512**: todos los `JWTCheck` de los servicios deben usar `'RS512'`. Revisa también AuditLog, LibraryCore, SupportDesk y FileStore |

## Orden de diagnóstico rápido

1. ¿El contenedor del servicio resuelve? (`docker ps` y comparar nombres)
2. ¿Hay 502? Problema entre nginx y PHP-FPM (IP vieja o PHP no arrancó).
3. ¿Hay 500? Leer `laravel.log` (si no existe, permisos) o pedir el login con `Accept: application/json` para ver el mensaje.
4. ¿Falla la firma? Llaves y rutas.
5. ¿Falla tras firmar? Service clients y reporte a AuditLog.

## Producción

- Imagen `eddyds23/auth-vault:2.1` (PHP-FPM + Nginx vía supervisord).
- Llaves JWT montadas como volumen de solo lectura, nunca dentro de la imagen.
- `.env` nunca en la imagen ni en el repo. `APP_DEBUG=false`.
- La clave pública se expone en `GET /api/auth/public-key` (PEM). JWKS en `/.well-known/jwks.json` existe pero los servicios usan el PEM hasta que haya rotación de claves.