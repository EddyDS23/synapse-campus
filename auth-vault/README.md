# AuthVault API

> Production-Ready Authentication API — Laravel 11  
> PHP 8.3 · MariaDB 11.4 · Redis · Docker · Sanctum

---

## Descripción

API REST de autenticación robusta y segura con registro, login, refresh tokens, roles y permisos, autenticación de dos factores (2FA) con TOTP, OAuth con GitHub, auditoría y gestión de sesiones.

---

## Stack

| Tecnología | Uso |
|------------|-----|
| PHP 8.3 + Laravel 11 | Framework principal |
| MariaDB 11.4 | Base de datos |
| Redis | Caché y rate limiting |
| Docker Compose | Infraestructura |
| Laravel Sanctum | Tokens de acceso |
| Scramble | Documentación interactiva de API |

---

## Instalación

### Requisitos
- Docker y Docker Compose

### Pasos

1. Clona el repositorio:
```bash
git clone https://github.com/tu-usuario/auth-vault-system.git
cd auth-vault-system
```

2. Copia el archivo de entorno:
```bash
cp .env.example .env
```

3. Configura las variables en `.env` — especialmente las credenciales de la BD y OAuth.

4. Levanta los contenedores:
```bash
docker compose up -d
```

El `entrypoint.sh` instala dependencias, genera la app key y corre las migraciones automáticamente.

---

## Variables de entorno

```env
APP_NAME=AuthVault
APP_URL=http://authvault.local

DB_CONNECTION=mariadb
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=auth_vault_api
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=redis

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://authvault.local/api/auth/github/callback

MAX_ATTEMPTS_LOGIN=5
```

---

## Documentación de la API

Documentacion disponible en:
http://authvault.local/docs/api

---

## Características

- Registro y login con email y password
- Logout individual y en todos los dispositivos
- Refresh de tokens sin re-autenticación
- Rate limiting y bloqueo temporal por intentos fallidos
- Registro de intentos de login fallidos
- Roles y permisos (`admin`, `user`, `moderator`)
- Two Factor Authentication (TOTP) con códigos de recuperación
- OAuth con GitHub
- Sesiones activas por dispositivo
- Log de auditoría de acciones sensibles
