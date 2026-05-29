#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

echo "[deploy] Iniciando deploy Laravel en cPanel"
echo "[deploy] APP_ROOT=$APP_ROOT"
echo "[deploy] Fecha: $(date '+%Y-%m-%d %H:%M:%S %z')"

PHP_HAS_PHAR=0
PHP_HAS_PDO=0
PHP_HAS_DOM=0
HAS_NPM=0
PUBLIC_DEPLOY_PATH="${DEPLOY_PUBLIC_PATH:-}"
APP_URL_VALUE=""
APP_URL_HOST=""
APP_URL_FIRST_LABEL=""
RUN_NPM_BUILD="${RUN_NPM_BUILD:-0}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-1}"

PHP_BIN="${PHP_BIN:-php}"
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "[deploy][error] No se encontró PHP en PATH (PHP_BIN=$PHP_BIN)."
  exit 1
fi

if "$PHP_BIN" -m 2>/dev/null | grep -qi '^Phar$'; then
  PHP_HAS_PHAR=1
fi
if "$PHP_BIN" -m 2>/dev/null | grep -qi '^PDO$'; then
  PHP_HAS_PDO=1
fi
if "$PHP_BIN" -m 2>/dev/null | grep -qi '^dom$'; then
  PHP_HAS_DOM=1
fi
if command -v npm >/dev/null 2>&1; then
  HAS_NPM=1
fi

if [ -f .env ]; then
  APP_URL_VALUE="$(sed -n 's/^APP_URL=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"
fi

if [ -n "$APP_URL_VALUE" ]; then
  APP_URL_HOST="$(printf '%s\n' "$APP_URL_VALUE" | sed -E 's#^[a-zA-Z]+://##' | cut -d/ -f1 | cut -d: -f1)"
  APP_URL_FIRST_LABEL="$(printf '%s\n' "$APP_URL_HOST" | cut -d. -f1)"
fi

if [ -z "$PUBLIC_DEPLOY_PATH" ]; then
  candidates=()
  if [ -n "$APP_URL_HOST" ]; then
    candidates+=("$HOME/public_html/$APP_URL_HOST")
  fi
  if [ -n "$APP_URL_FIRST_LABEL" ]; then
    candidates+=("$HOME/public_html/$APP_URL_FIRST_LABEL")
  fi
  candidates+=(
    "$HOME/public_html/registro.lbcchile.com"
    "$HOME/public_html/registro"
    "$HOME/public_html/registro-lbcchile"
    "$HOME/public_html/registro_lbcchile"
    "$HOME/public_html/clubes.lbcchile.com"
    "$HOME/public_html/clubes"
    "$HOME/public_html/clubes-lbcchile"
    "$HOME/public_html/clubes_lbcchile"
  )

  for candidate in "${candidates[@]}"; do
    if [ -d "$candidate" ]; then
      PUBLIC_DEPLOY_PATH="$candidate"
      break
    fi
  done
fi

if command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD=(composer)
elif [ -x "/opt/cpanel/composer/bin/composer" ]; then
  COMPOSER_CMD=(/opt/cpanel/composer/bin/composer)
elif [ -x "$HOME/composer.phar" ]; then
  COMPOSER_CMD=("$PHP_BIN" "$HOME/composer.phar")
elif [ -x "$APP_ROOT/composer.phar" ]; then
  COMPOSER_CMD=("$PHP_BIN" "$APP_ROOT/composer.phar")
else
  COMPOSER_CMD=()
fi

if [ ! -f .env ]; then
  echo "[deploy][warn] No existe .env, se crea desde .env.example"
  cp .env.example .env
  echo "[deploy][error] Debes editar .env en cPanel con datos reales de producción y volver a deploy."
  exit 1
fi

required_env_keys=(
  APP_ENV
  APP_URL
  DB_CONNECTION
  DB_HOST
  DB_PORT
  DB_DATABASE
  DB_USERNAME
  DB_PASSWORD
)

for key in "${required_env_keys[@]}"; do
  if ! grep -q "^${key}=" .env; then
    echo "[deploy][error] Falta ${key} en .env"
    exit 1
  fi
done

if grep -q '^APP_ENV=local' .env; then
  echo "[deploy][error] APP_ENV=local detectado. En producción debe ser APP_ENV=production."
  exit 1
fi

if grep -q '^APP_DEBUG=true' .env; then
  echo "[deploy][error] APP_DEBUG=true detectado. En producción debe ser APP_DEBUG=false."
  exit 1
fi

if [ "${#COMPOSER_CMD[@]}" -gt 0 ]; then
  if [ "$PHP_HAS_PHAR" -eq 1 ]; then
    echo "[deploy] Instalando dependencias PHP"
    if ! "${COMPOSER_CMD[@]}" install --no-dev --optimize-autoloader --no-interaction --prefer-dist; then
      echo "[deploy][warn] Composer falló. Se intentará continuar con vendor precompilado."
      if [ ! -f "$APP_ROOT/vendor/autoload.php" ]; then
        echo "[deploy][error] No existe vendor/autoload.php."
        echo "[deploy][hint] Sube la carpeta vendor/ al repositorio o al servidor y vuelve a desplegar."
        exit 1
      fi
    fi
  else
    echo "[deploy][warn] PHP CLI no tiene extensión phar. Se omite Composer."
    if [ ! -f "$APP_ROOT/vendor/autoload.php" ]; then
      echo "[deploy][error] No existe vendor/autoload.php."
      echo "[deploy][hint] Sube la carpeta vendor/ al repositorio o al servidor y vuelve a desplegar."
      exit 1
    fi
  fi
else
  echo "[deploy][warn] Composer no disponible en servidor. Se usará vendor precompilado."
  if [ ! -f "$APP_ROOT/vendor/autoload.php" ]; then
    echo "[deploy][error] No existe vendor/autoload.php."
    echo "[deploy][hint] Sube la carpeta vendor/ al repositorio o al servidor y vuelve a desplegar."
    exit 1
  fi
fi

if [ "$RUN_NPM_BUILD" = "1" ]; then
  if [ "$HAS_NPM" -eq 1 ]; then
    echo "[deploy] RUN_NPM_BUILD=1 detectado, compilando assets frontend"
    if [ -f package-lock.json ]; then
      npm ci --no-audit --no-fund
    else
      npm install --no-audit --no-fund
    fi
    npm run build
  else
    echo "[deploy][warn] RUN_NPM_BUILD=1 pero npm no está disponible. Se usarán assets precompilados."
  fi
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  if [ "$PHP_HAS_DOM" -eq 1 ]; then
    echo "[deploy] APP_KEY ausente, generando clave con artisan"
    CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan key:generate --force
  else
    echo "[deploy][warn] APP_KEY ausente. Generando clave sin artisan (PHP CLI sin DOM)."
    GENERATED_KEY="$("$PHP_BIN" -r 'echo "base64:".base64_encode(random_bytes(32));')"
    if grep -q '^APP_KEY=' .env; then
      sed -i.bak "s|^APP_KEY=.*|APP_KEY=${GENERATED_KEY}|" .env && rm -f .env.bak
    else
      printf "\nAPP_KEY=%s\n" "$GENERATED_KEY" >> .env
    fi
  fi
fi

if [ "$RUN_MIGRATIONS" = "1" ] && [ "$PHP_HAS_PDO" -eq 1 ]; then
  echo "[deploy] Ejecutando migraciones (RUN_MIGRATIONS=1)"
  "$PHP_BIN" artisan migrate --force
elif [ "$RUN_MIGRATIONS" = "1" ]; then
  echo "[deploy][warn] RUN_MIGRATIONS=1 ignorado porque PHP CLI no tiene PDO."
else
  echo "[deploy] Migraciones desactivadas (RUN_MIGRATIONS=0). Se reutiliza esquema actual de base de datos."
fi

if [ "${RUN_SEEDERS:-0}" = "1" ] && [ "$PHP_HAS_PDO" -eq 1 ]; then
  echo "[deploy] Ejecutando seeders (RUN_SEEDERS=1)"
  "$PHP_BIN" artisan db:seed --force
elif [ "${RUN_SEEDERS:-0}" = "1" ]; then
  echo "[deploy][warn] RUN_SEEDERS=1 ignorado porque PHP CLI no tiene PDO."
fi

if [ "$PHP_HAS_DOM" -eq 1 ]; then
  echo "[deploy] Asegurando symlink de storage"
  CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan storage:link || true

  echo "[deploy] Limpiando y recacheando"
  CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan optimize:clear
  CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan config:cache
  CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan route:cache
  CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync "$PHP_BIN" artisan view:cache
else
  echo "[deploy][warn] PHP CLI sin extensión DOM. Se omiten comandos artisan de mantenimiento."
  if [ -e "$APP_ROOT/public/storage" ] && [ ! -L "$APP_ROOT/public/storage" ]; then
    echo "[deploy][warn] public/storage existe y no es symlink. No se reemplaza automáticamente."
  else
    ln -sfn "$APP_ROOT/storage/app/public" "$APP_ROOT/public/storage" || true
    echo "[deploy] Symlink public/storage actualizado por shell."
  fi
fi

if [ -n "$PUBLIC_DEPLOY_PATH" ]; then
  echo "[deploy] APP_URL host detectado: ${APP_URL_HOST:-desconocido}"
  echo "[deploy] DEPLOY_PUBLIC_PATH resuelto: $PUBLIC_DEPLOY_PATH"
  REPO_PUBLIC_REALPATH="$(cd "$APP_ROOT/public" && pwd)"
  TARGET_PUBLIC_REALPATH="$(cd "$PUBLIC_DEPLOY_PATH" 2>/dev/null && pwd || true)"

  if [ "$TARGET_PUBLIC_REALPATH" = "$REPO_PUBLIC_REALPATH" ]; then
    echo "[deploy] Document root ya apunta a $REPO_PUBLIC_REALPATH"
  else
    echo "[deploy] Publicando contenido web en $PUBLIC_DEPLOY_PATH"
    mkdir -p "$PUBLIC_DEPLOY_PATH"
    cp -a "$APP_ROOT/public/." "$PUBLIC_DEPLOY_PATH/"

    cat > "$PUBLIC_DEPLOY_PATH/index.php" <<EOF
<?php

define('LARAVEL_START', microtime(true));

require '$APP_ROOT/vendor/autoload.php';

\$app = require_once '$APP_ROOT/bootstrap/app.php';

\$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);

\$response = \$kernel->handle(
    \$request = Illuminate\Http\Request::capture()
);

\$response->send();

\$kernel->terminate(\$request, \$response);
EOF
  fi
else
  echo "[deploy][warn] No se detectó carpeta document root en public_html."
  echo "[deploy][warn] Define DEPLOY_PUBLIC_PATH en .env, por ejemplo:"
  echo "[deploy][warn] DEPLOY_PUBLIC_PATH=/home/ligabasq/public_html/clubes"
fi

echo "[deploy] Deploy completado"
