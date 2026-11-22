# Synapse Campus — Blade BFF

Frontend del ecosistema Synapse Campus. Es un **Backend for Frontend**: el navegador solo habla con esta app, y ella habla con los microservicios. Los tokens JWT viven en la sesión PHP y **nunca llegan al browser**.

**No usa base de datos.** Sesiones y caché van en archivos.

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 | Framework (fijado a `^13.0`; 13.8+ exige PHP 8.4) |
| Blade | Vistas |
| Sesión `file` | Almacena tokens JWT por servicio |
| Caché `file` | Health checks (30 s) y tokens |
| Docker | Contenedor unificado PHP-FPM + Nginx (supervisord) |
| Traefik | Reverse proxy + TLS (producción) |

## Qué hace

- Login contra AuthVault (email/password, 2FA TOTP y OAuth GitHub)
- Token Exchange automático al entrar a cada sección (un token por servicio, cacheado en sesión)
- Menú y rutas por rol (`student`, `teacher`, `librarian`, `support_agent`, `academic_admin`, `security_admin`, `super_admin`)
- Secciones: perfil/horario/materias (StudentPortal), biblioteca (LibraryCore), tickets (SupportDesk), auditoría (AuditLog)
- Sidebar con estado real de los servicios (`HealthService`, `Http::pool()`, caché 30 s)
- Modo oscuro/claro persistente

## Dependencias del ecosistema

El BFF necesita que estén corriendo (o al menos accesibles) estos servicios en la red `synapse-campus-network`:

| Servicio | Contenedor nginx | Puerto dev |
|---|---|---|
| AuthVault | `auth_vault_nginx` | 9001 |
| StudentPortal | `nginx-student-portal` | 9002 |
| LibraryCore | `library_core_nginx` | 9003 |
| SupportDesk | `support_desk_nginx` | 9004 |
| AuditLog | `audit_logs_nginx` | 9005 |
| **Blade BFF** | `nginx-blade-app` | **9006** |

AuthVault es imprescindible: sin él no hay login. Los demás pueden estar caídos; el sidebar los marcará como `offline`.

## Cómo levantarlo

### 1. Redes Docker (una sola vez)

```bash
docker network create synapse-campus-network
docker network create proxy_network
```

### 2. Variables de entorno

```bash
cp .env.example .env
```

Edita `.env` y revisa:

- `APP_KEY` — genérala (ver paso 4). Nunca vacía.
- `*_BASE_URL` — deben coincidir **exactamente** con el nombre del contenedor nginx de cada servicio.
- `APP_URL` — `http://localhost:9006` en dev; tu dominio en producción.

### 3. Levantar

```bash
docker compose up -d --build
```

### 4. Primera vez

Dentro del contenedor `app`:

- Generar la clave: `php artisan key:generate`
- Limpiar config: `php artisan config:clear`
- Asegurar permisos de `storage` y `bootstrap/cache` para `www-data` (aquí se guardan las sesiones en archivos)

Después de **cualquier cambio al `.env`**: `config:clear` y reiniciar el contenedor.

### 5. Verificar

Abre `http://localhost:9006` e inicia sesión con un usuario existente en AuthVault.

## Variables de entorno

| Variable | Valor | Nota |
|---|---|---|
| `SESSION_DRIVER` | `file` | **Crítico.** Con `database` falla buscando `sessions` en SQLite |
| `CACHE_STORE` | `file` | **Crítico.** Default `database` rompe el HealthService |
| `QUEUE_CONNECTION` | `sync` | No hay colas |
| `SESSION_ENCRYPT` | `true` | Los tokens JWT viven en la sesión |
| `AUTHVAULT_BASE_URL` | `http://auth_vault_nginx` | Nombre literal del contenedor |
| `STUDENTPORTAL_BASE_URL` | `http://nginx-student-portal` | Ojo: guiones medios |
| `LIBRARYCORE_BASE_URL` | `http://library_core_nginx` | |
| `SUPPORTDESK_BASE_URL` | `http://support_desk_nginx` | |
| `AUDITLOG_BASE_URL` | `http://audit_logs_nginx` | |

> Los nombres exactos de las variables `*_BASE_URL` deben coincidir con lo que lee `config/services.php`. Verifícalo antes de dar por buena esta lista.

## Problemas conocidos

| Síntoma | Causa | Solución |
|---|---|---|
| `Database file ... database.sqlite does not exist` | `SESSION_DRIVER` o `CACHE_STORE` en `database` | Ponerlos en `file`, `config:clear`, reiniciar |
| Sidebar muestra servicios `offline` | Nombre de contenedor mal escrito o fuera de la red | Revisar `*_BASE_URL` y que `app` esté en `synapse-campus-network` |
| Cambié `.env` y no pasa nada | Config cacheada | `config:clear` + reiniciar contenedor |
| Error de permisos al guardar sesión | `storage` sin permisos | `chown` a `www-data` en `storage` y `bootstrap/cache` |
| Cualquier ruta da 404 tras recrear contenedores | DNS interno desincronizado | Reiniciar el nginx del servicio |

## Producción

- Imagen: `eddyds23/blade-app:2.1` (PHP-FPM + Nginx vía supervisord)
- Dominio: `synapse.zerofall.qzz.io`, TLS con Let's Encrypt vía Traefik
- `config:cache`, `route:cache` y `view:cache` corren en el **entrypoint**, no en el Dockerfile
- `.env` y certificados nunca van dentro de la imagen
- Activar `SESSION_SECURE_COOKIE=true`, `APP_ENV=production`, `APP_DEBUG=false`
- GitHub OAuth: la redirect URI `https://<dominio>/auth/github/callback` debe estar en la GitHub OAuth App y en AuthVault

## Convenciones del ecosistema

ULID en todos los IDs · alias de middleware con nombre de dominio · tres redes por servicio · auditoría híbrida (local + best-effort a AuditLog) · YAGNI.