#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

CPANEL_HOST="${CPANEL_HOST:-}"
CPANEL_USER="${CPANEL_USER:-}"
CPANEL_PORT="${CPANEL_PORT:-22}"
CPANEL_APP_PATH="${CPANEL_APP_PATH:-}"
BRANCH="${BRANCH:-}"
RUN_PUSH="${RUN_PUSH:-1}"
REPO_URL="${REPO_URL:-}"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "[deploy][error] Este directorio no es un repositorio git."
  echo "[deploy][hint] Ejecuta: git init -b main && git remote add origin <repo-url>"
  exit 1
fi

if [[ -z "$BRANCH" ]]; then
  BRANCH="$(git branch --show-current)"
fi

if [[ -z "$REPO_URL" ]]; then
  REPO_URL="$(git remote get-url origin 2>/dev/null || true)"
fi

if [[ -z "$REPO_URL" ]]; then
  echo "[deploy][error] No existe remote 'origin'."
  echo "[deploy][hint] Ejecuta: git remote add origin <repo-url>"
  exit 1
fi

if [[ -z "$CPANEL_HOST" || -z "$CPANEL_USER" || -z "$CPANEL_APP_PATH" ]]; then
  echo "[deploy][error] Faltan variables obligatorias."
  echo "Uso:"
  echo "  CPANEL_HOST=host CPANEL_USER=user CPANEL_APP_PATH=/home/user/clubes-lbc \\"
  echo "  ./scripts/remote_deploy.sh"
  exit 1
fi

if [[ "$RUN_PUSH" == "1" ]]; then
  echo "[deploy] Push de rama local '$BRANCH' a origin"
  git push origin "$BRANCH"
else
  echo "[deploy] RUN_PUSH=0, se omite push local"
fi

echo "[deploy] Ejecutando deploy remoto en cPanel ($CPANEL_HOST)"

ssh -p "$CPANEL_PORT" "$CPANEL_USER@$CPANEL_HOST" \
  "set -euo pipefail; \
  if [ ! -d '$CPANEL_APP_PATH/.git' ]; then \
    echo '[deploy][remote] Proyecto no clonado, clonando repo'; \
    git clone --branch '$BRANCH' '$REPO_URL' '$CPANEL_APP_PATH'; \
  fi; \
  cd '$CPANEL_APP_PATH'; \
  git fetch origin '$BRANCH'; \
  git checkout '$BRANCH'; \
  git pull --ff-only origin '$BRANCH'; \
  chmod +x scripts/cpanel_deploy.sh; \
  /bin/bash scripts/cpanel_deploy.sh"

echo "[deploy] Deploy remoto completado"
