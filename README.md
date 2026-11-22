# Synapse Campus

Ecosistema de microservicios para la gestión institucional de un campus, con **identidad centralizada**. Un solo servicio (AuthVault) maneja usuarios, contraseñas y tokens; los demás no tienen tabla de usuarios y confían en el JWT que AuthVault firma, validándolo localmente con su clave pública.

Proyecto construido por una sola persona, como portafolio y como proyecto de aprendizaje.

## Servicios

| Servicio | Puerto dev | Responsabilidad | Documentación |
|---|---|---|---|
| **AuthVault** | 9001 | Identity Provider: login, JWT, refresh, Token Exchange, 2FA, OAuth GitHub, service clients, roles y scopes | `auth-vault/README.md` |
| **StudentPortal** | 9002 | Datos académicos: carreras, materias, secciones, inscripciones, horarios | `student-portal/README.md` |
| **LibraryCore** | 9003 | Biblioteca: catálogo, préstamos, renovaciones, multas | `library-core/README.md` |
| **SupportDesk** | 9004 | Mesa de ayuda: tickets, comentarios, asignación, security context | `support-desk/README.md` |
| **AuditLog** | 9005 | Auditoría centralizada e inmutable | `audit-logs/README.md` |
| **Blade BFF** | 9006 | Frontend (Backend for Frontend): único punto de entrada del navegador | `blade-app/README.md` |
| **FileStore** | 9007 | Almacenamiento de archivos (subir, obtener, eliminar) | `file-store/README.md` |

Cada servicio vive en su carpeta, con su propio `docker-compose.yml`, su base de datos independiente y su `.env.example`. Ajusta las rutas de la columna de documentación a los nombres reales de tus carpetas.

## Stack

| Tecnología | Uso |
|---|---|
| PHP 8.3 + Laravel 13 (fijado a `^13.0`) | Todos los servicios |
| MariaDB 11.4 | Una base de datos por servicio |
| Redis | Solo AuthVault (rate limiting y sesiones revocadas) |
| JWT RS512 (RSA-4096) | Firma en AuthVault; validación local en los demás |
| Docker + Docker Compose | Infraestructura |
| Nginx | Un sub-proxy por servicio |
| Traefik v2.11 + Let's Encrypt | Reverse proxy y TLS en producción |
| Blade | Frontend |
| Scramble | Documentación interactiva de API |

## Arquitectura

```
Navegador
   │  (solo cookie de sesión cifrada, nunca ve un JWT)
   ▼
Blade BFF ──────────────┐
   │ Bearer JWT (aud por servicio)
   ├──▶ AuthVault ───────┤  login, refresh, token exchange, clave pública
   ├──▶ StudentPortal ───┤
   ├──▶ LibraryCore ─────┤──▶ StudentPortal (estado académico, deuda)
   ├──▶ SupportDesk ─────┤──▶ AuthVault (security context)
   └──▶ AuditLog         │
                         └──▶ AuditLog (todos reportan eventos)
```

- **BFF:** los tokens JWT viven en la sesión de PHP del servidor. El navegador solo recibe la cookie de sesión cifrada.
- **Sin usuarios en los servicios:** cada servicio valida el JWT con la clave pública de AuthVault (`GET /api/auth/public-key`) y lee `roles` y `scopes` del propio token.
- **Auditoría híbrida:** cada servicio guarda su registro local y además envía el evento a AuditLog en modo best-effort. Si AuditLog está caído, nada se rompe.
- **Sub-nginx permanente por servicio:** las llamadas entre servicios son HTTP, no FastCGI, así que cada servicio mantiene su nginx.

## Autenticación

1. **Login:** el cliente hace `POST /api/login` a AuthVault y recibe un access token (`aud: auth-vault`, 15 minutos) y un refresh token (cadena aleatoria, 7 días, con rotación).
2. **Token Exchange:** antes de usar un servicio, el cliente cambia ese token por uno con `aud` del servicio (`student-portal`, `library-core`, `support-desk`, `audit-log`) mediante `POST /api/token/exchange`. Mismos `roles` y `scopes`.
3. **Llamada:** el servicio valida la firma localmente con `firebase/php-jwt` (`new Key($publicKey, 'RS512')`) y comprueba el scope que su endpoint exige.
4. **Servicio a servicio:** cada servicio pide su propio token con `POST /api/service/token` (client credentials). Ese token lleva `type: service` y los scopes del service client. AuthVault firma el suyo internamente.

### Algoritmo

Todo el ecosistema usa **RS512**. Los `JWTCheck` de todos los servicios deben construir la clave con `'RS512'`; un algoritmo distinto produce `400 Format's bearer token invalid` / `Incorrect key for this algorithm`.

### Roles institucionales

`student` · `teacher` · `librarian` · `support_agent` · `academic_admin` · `security_admin` · `super_admin`

## Puesta en marcha desde cero

### Requisitos

Docker con Compose v2 y Git. Nada más: PHP y MariaDB corren en contenedores.

### 1. Redes Docker (una sola vez)

```bash
docker network create synapse-campus-network
docker network create proxy_network
```

### 2. Variables de entorno

En cada servicio: `cp .env.example .env` y rellena `DB_*`, la `APP_KEY` y las URLs de los demás servicios. Los `.env` nunca se suben al repositorio.

### 3. Orden de arranque

1. **AuthVault** (todo depende de él)
2. **AuditLog**
3. **StudentPortal**
4. **LibraryCore**, **SupportDesk** y **FileStore**
5. **Blade BFF** (al final)

Por servicio: `docker compose up -d --build`, luego `php artisan key:generate` dentro del contenedor `app`.

### 4. AuthVault: llaves y seeders

1. Generar las llaves: `php artisan jwt:generate-certs` (crea `storage/certs/jwt-rsa-4096-private.pem` y `...-public.pem`). Si dice *"already exists"*, ya existen: revisa las rutas del `.env`.
2. Correr los seeders **en este orden**: roles → scopes (catálogo completo, incluidos `files:*`) → service clients → usuarios de prueba (solo desarrollo).
3. El seeder de service clients **imprime cada secret una sola vez**. Cópialo en ese momento al `.env` del servicio correspondiente.

### 5. Service clients

Cada servicio que llama a otro o reporta a AuditLog necesita su identidad en AuthVault. Usa **guion medio** en todos los `client_id`.

| `client_id` | Servicio | Scopes |
|---|---|---|
| `auth-vault` | AuthVault | `audit:events:write` |
| `student-portal` | StudentPortal | `audit:events:write`, `internal:users:basic-info:read` |
| `library-core` | LibraryCore | `audit:events:write`, `internal:student-status:read`, `internal:student-debt:write` |
| `support-desk` | SupportDesk | `audit:events:write`, `internal:security-status:read` |
| `file-store` | FileStore | `audit:events:write` |

AuditLog solo recibe: no necesita service client. Los scopes de cada cliente salen de las rutas `service.check:<scope>` de los servicios que consume.

El secret impreso va en el `.env` del servicio. Ojo con el nombre de la variable: StudentPortal usa `AUTHVAULT_CLIENT_ID` / `AUTHVAULT_CLIENT_SECRET`, mientras que FileStore, LibraryCore y SupportDesk leen `CLIENT_ID` / `CLIENT_SECRET`. Sigue el `.env.example` de cada servicio. Si pierdes un secret no se recupera: genera uno nuevo y actualiza el `.env`.

### 6. Aplicar cambios

Tras editar un `.env`: `config:clear` y **recrear** el contenedor `app` (un `restart` no relee `env_file`). Después, reiniciar su nginx.

### 7. Verificar

1. `GET http://localhost:9001/api/auth/public-key` devuelve el PEM.
2. Abre `http://localhost:9006` e inicia sesión con un usuario de prueba (`{rol}@test.com`).
3. En el nginx de AuditLog debe aparecer `POST /api/internal/events` con respuesta 201 tras un login.

## Nombres de contenedor

Docker DNS es **exacto**: guion medio y guion bajo no son lo mismo. Las URLs de los `.env` deben usar el nombre literal del contenedor nginx. Estos son los que conocemos en desarrollo; verifica siempre con `docker ps`.

| Servicio | Contenedor nginx |
|---|---|
| AuthVault | `auth-vault-nginx` |
| StudentPortal | `nginx-student-portal` |
| SupportDesk | `support_desk_nginx` (guion bajo, por `container_name`) |
| AuditLog | `audit-log-nginx` |
| Blade BFF | `nginx-blade-app` |
| LibraryCore y FileStore | comprobar con `docker ps` |

## Reglas que se repiten en todos los servicios

- **Sesión, caché y colas:** `file`, `file`, `sync`. Los valores por defecto de Laravel son `database` y fallan al no existir las tablas. Única excepción: el caché de AuthVault es `redis`.
- **El `app` y el nginx de cada servicio** están en `synapse-campus-network`. Para llamar a otro servicio se usa siempre el nombre de su **nginx**, nunca `app:9000` (habla FastCGI, no HTTP).
- **Tras recrear `app`, reinicia su nginx:** si no, queda con la IP vieja (502 o 404 en todas las rutas).
- **`storage` y `bootstrap/cache`** deben ser escribibles por `www-data` (`chown` en el entrypoint, antes de artisan). Sin permisos no hay `laravel.log` y todo da 500.
- **El token de servicio** se cachea menos tiempo que su TTL (15 minutos): unos 13 minutos.
- **`env()` solo dentro de `config/*.php`.** Fuera de config devuelve `null` con `config:cache` en producción.
- **Variables de entorno:** el nombre del `.env` debe coincidir exactamente con el de la config. Un nombre mal escrito deja `config()` en `null` sin dar error (nos pasó con AuditLog).

## Errores frecuentes (resumen)

| Síntoma | Causa | Solución |
|---|---|---|
| `MissingAppKeyException` | `APP_KEY` vacía | `key:generate`, `config:clear`, recrear `app` |
| Error de SQLite / `no such table: sessions` | Sesión o caché en `database` | Poner `file`, recrear `app` |
| `bad address '<nombre>'` | Nombre de contenedor distinto al de la URL | `docker ps` y copiar el nombre exacto |
| 502 `Connection refused :9000` | IP vieja de `app` en nginx o PHP-FPM caído | Reiniciar el nginx |
| 500 sin `laravel.log` | Permisos de `storage` | `chown` a `www-data` en el entrypoint |
| 400 `Incorrect key for this algorithm` | Algoritmo del `JWTCheck` distinto al de AuthVault | Usar `'RS512'` en todos |
| 401 hacia otro servicio con token de servicio | Token expirado en caché o secret incorrecto | Caché menor a 15 min; revisar el secret |
| 403 en una llamada interna | Falta un scope en el service client | Revisar scopes y volver a sembrar |
| AuditLog no recibe nada y no hay error | Variable mal escrita o nombre de contenedor incorrecto | `config()` en tinker; registrar fallos en el `catch` |
| Login correcto pero perfil vacío en StudentPortal | Los ULID de usuario cambiaron al recrear la BD de AuthVault | Mantener ULID fijos en los usuarios de prueba |
| 404 en todas las rutas tras recrear contenedores | DNS interno desincronizado | Reiniciar solo el nginx |

Cada servicio tiene su tabla de errores completa en su propio README.

## Producción

- **Imágenes:** una por servicio (`eddyds23/<servicio>:2.1`), con PHP-FPM + Nginx gestionados por **supervisord** en un solo contenedor. Compatibles con ARM64 (`docker buildx` + QEMU).
- **Entrada pública:** Traefik v2.11 con Let's Encrypt en `synapse.zerofall.qzz.io` (Cloudflare en modo DNS Only). Traefik solo expone el Blade BFF; los demás servicios se comunican por `synapse-campus-network`.
- **Entrypoint:** `config:cache`, `route:cache` y `view:cache` corren al arrancar, nunca en el Dockerfile.
- **Secretos:** las llaves JWT se montan como volumen de solo lectura, nunca dentro de la imagen. El `.env` nunca va en la imagen ni en el repo. `APP_DEBUG=false`.
- **Persistencia de FileStore:** el volumen de archivos debe montarse en `storage/app/files`.
- **GitHub OAuth:** la URL de callback del Blade (`/auth/github/callback`) debe estar registrada en la GitHub OAuth App.

## Convenciones del ecosistema

- ULID en todos los IDs; pivots puras con clave compuesta.
- Alias de middleware con nombre de dominio (`authvault.jwt`, `service.check`), nunca los que registra `jwt-auth` (`jwt.auth`, `jwt.check`).
- Tres redes por servicio: interna, `synapse-campus-network` y `proxy_network`.
- Auditoría híbrida: tabla local más reporte best-effort a AuditLog.
- Una BD por servicio; Redis solo donde hay login o sesiones.
- Rutas estáticas por encima de las dinámicas (`/tickets/my` antes de `/tickets/{id}`).
- YAGNI: no se añade infraestructura hasta que se necesita.

## Pendientes y hoja de ruta

- Forzar el cambio de contraseña en el primer login (existe la columna y el setter, falta el bloqueo y el flujo).
- Rotación de claves RSA con JWKS (hoy los servicios usan el PEM directo).
- Política de retención de logs en AuditLog.
- EnrollmentCore, GradesCore, ampliación de StudentPortal y NotificationHub.
- Compose general en la raíz o script de arranque que levante todos los módulos en orden.

### Blade BFF: mejoras pendientes

Observaciones del estado actual del frontend, anotadas para la siguiente fase de trabajo (sin diagnosticar todavía):

- **Rol `teacher`:** al iniciar sesión solo aparece el dashboard, sin contenido. No está claro si el Blade tiene secciones para este rol o si falta definirlas.
- **Rol `security_admin`:** la sección de seguridad no ofrece nada más allá de lo básico; hay que definir qué debe poder hacer este rol desde la interfaz (el endpoint de security context de SupportDesk y el diagnóstico de cuentas de AuthVault existen pero no tienen una pantalla completa).
- **Nombre del maestro:** no se muestra en las pantallas donde debería aparecer (por ejemplo en el horario).
- **Revisión general de vistas por rol:** comprobar qué ve cada uno de los siete roles y que ninguno quede con un dashboard vacío.
- **Rediseño visual:** refactorizar la interfaz con una nueva paleta de colores y estilo (hoy: acento terracota `#B84C39`, fondos crema, modo oscuro cálido, tipografía Lora).
- **Documentación del Blade:** actualizar su `.env.example` y su README con los nombres reales de los contenedores en desarrollo.

## Estructura del repositorio

```
synapse-campus/
├── auth-vault/
├── student-portal/
├── library-core/
├── support-desk/
├── audit-logs/
├── file-store/
├── blade-app/
└── README.md
```

Cada carpeta contiene su `docker-compose.yml`, su `.env.example` y su propio README con la tabla de variables y de errores de ese servicio.