# Clubes LBC Chile (Instalación limpia)

Plataforma Laravel independiente para recepción de antecedentes deportivos de clubes.

## Stack

- Laravel 12
- PHP 8.3+
- MySQL/MariaDB (compatible con SQLite en local)
- Blade + TailwindCSS + Alpine.js
- Auth admin con Laravel Breeze (Blade)

## Módulos implementados

- Flujo público:
  - `GET /inscripciones`
  - `GET /inscripcion`
  - `GET /inscripcion/{season}/{division}`
  - `POST /inscripcion/{season}/{division}`
  - `GET /plantilla-nomina-jugadores` (descarga plantilla global activa)
- Correcciones seguras:
  - `GET /correcciones/{year}/{division}/{club}/{token}`
  - `POST /correcciones/{year}/{division}/{club}/{token}`
- Admin protegido (`auth` + middleware `admin`):
  - `GET /admin`
  - CRUD Temporadas, Divisiones, Clubes
  - CRUD Usuarios administrativos (con resguardo de último super_admin)
  - Configuración global editable (tabla `settings`)
  - Configuración alias `GET/PUT /admin/configuracion`
  - Descarga admin de plantilla `GET /admin/configuracion/plantilla-nomina-jugadores`
  - Historial de auditoría con filtros
  - Módulo de pagos dedicado (`/admin/pagos`) con filtros, cambio de estado y exportación CSV
  - Antecedentes (listado, detalle, estados, versiones)
  - Correcciones (generación de link, activar/desactivar, regenerar token)
  - Descarga protegida de archivos por controlador

## Reglas funcionales clave

- Temporada + división + club generan un único `submission` con múltiples `submission_versions`.
- Máximo de envíos por defecto: 2. Admin puede aumentar hasta 4.
- Cada nuevo envío crea una versión histórica.
- No se reemplazan archivos automáticamente.
- Pago pasa a `in_review` cuando se adjunta comprobante.
- Link de pago por división: 1 división = 1 link principal.

## Seguridad base

- CSRF en formularios
- Rate limit para envíos públicos y correcciones
- Archivos en `storage/app/private`
- Descargas vía controlador autenticado
- Auditoría en `audit_logs`

## Notificaciones

- Se envían correos en eventos clave:
  - Nuevo envío de inscripción/corrección.
  - Cambio de estado de pago.
  - Cambio de estado de versión.
- Correo administrativo configurable en `settings.notification_email`.

## Settings de plantilla global

- `roster_template_path`
- `roster_template_original_name`
- `roster_template_mime`
- `roster_template_extension`
- `roster_template_is_active`
- `roster_template_button_text`
- `roster_template_description`
- `roster_template_updated_at`

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run dev
php artisan serve
```

## Seed de desarrollo

- Usuario: `admin@lbcchile.com`
- Contraseña local: `Cambiar1234!`

Cambiar credenciales en cualquier entorno no local.

## Build producción

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Deploy y rollback

- Guía operativa: `DEPLOY_CPANEL.md`
- Guía terminal: `DEPLOY_TERMINAL_CPANEL.md`
- Checklist de release: `docs/RELEASE_CHECKLIST.md`
- Scripts:
  - `scripts/cpanel_deploy.sh`
  - `scripts/remote_deploy.sh`
  - `scripts/deploy/release.sh`
  - `scripts/deploy/rollback.sh`

## API utilitaria

- `GET /api/seasons/{season}/divisions`
- `GET /api/divisions/{division}/clubs`

Solo devuelve registros activos.
