#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
RELEASES_DIR="$APP_ROOT/releases"
CURRENT_LINK="$APP_ROOT/current"

mapfile -t RELEASES < <(ls -1dt "$RELEASES_DIR"/*/)

if (( ${#RELEASES[@]} < 2 )); then
  echo "[ERROR] No hay release previo para rollback."
  exit 1
fi

TARGET="${RELEASES[1]%/}"
ln -sfn "$TARGET" "$CURRENT_LINK"

echo "[OK] Rollback aplicado a: $TARGET"
