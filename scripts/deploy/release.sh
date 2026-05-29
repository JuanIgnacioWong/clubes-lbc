#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
ARCHIVE_PATH="${2:-/tmp/clubes-lbc-release.tgz}"
KEEP_RELEASES="${3:-5}"

RELEASES_DIR="$APP_ROOT/releases"
SHARED_DIR="$APP_ROOT/shared"
CURRENT_LINK="$APP_ROOT/current"
TIMESTAMP="$(date +%Y%m%d%H%M%S)"
RELEASE_DIR="$RELEASES_DIR/$TIMESTAMP"

mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage" "$SHARED_DIR/storage/app/private" "$SHARED_DIR/storage/logs" "$SHARED_DIR/storage/framework/cache" "$SHARED_DIR/storage/framework/sessions" "$SHARED_DIR/storage/framework/views"

if [[ ! -f "$SHARED_DIR/.env" ]]; then
  echo "[ERROR] Falta $SHARED_DIR/.env. Crea el archivo antes de desplegar."
  exit 1
fi

mkdir -p "$RELEASE_DIR"
tar -xzf "$ARCHIVE_PATH" -C "$RELEASE_DIR"

rm -rf "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

cd "$RELEASE_DIR"
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"

# Actualiza scripts de deploy en la raíz para próximos despliegues.
if [[ -f "$RELEASE_DIR/scripts/deploy/release.sh" ]]; then
  mkdir -p "$APP_ROOT/scripts/deploy"
  cp "$RELEASE_DIR/scripts/deploy/release.sh" "$APP_ROOT/scripts/deploy/release.sh"
  chmod +x "$APP_ROOT/scripts/deploy/release.sh"
fi

if [[ -f "$RELEASE_DIR/scripts/deploy/rollback.sh" ]]; then
  mkdir -p "$APP_ROOT/scripts/deploy"
  cp "$RELEASE_DIR/scripts/deploy/rollback.sh" "$APP_ROOT/scripts/deploy/rollback.sh"
  chmod +x "$APP_ROOT/scripts/deploy/rollback.sh"
fi

cd "$RELEASES_DIR"
ls -1dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf

echo "[OK] Release desplegado: $RELEASE_DIR"
