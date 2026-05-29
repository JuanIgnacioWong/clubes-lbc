# Deploy terminal: GitHub -> cPanel

Este flujo despliega por SSH, sin usar botones de cPanel.

## 1) Preparación inicial en cPanel (una sola vez)

Conéctate por SSH y clona el proyecto:

```bash
ssh -p 22 USUARIO@HOST
git clone --branch main https://github.com/TU_USUARIO/clubes-lbc.git /home/USUARIO/clubes-lbc
cd /home/USUARIO/clubes-lbc
cp .env.cpanel.example .env
```

Edita `.env` con credenciales reales y define como mínimo:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://clubes.lbcchile.com`
- `DEPLOY_PUBLIC_PATH=/home/USUARIO/public_html/clubes.lbcchile.com`
- `RUN_MIGRATIONS=0` (o `1` cuando corresponda)

Primer deploy:

```bash
chmod +x scripts/cpanel_deploy.sh
/bin/bash scripts/cpanel_deploy.sh
```

## 2) Deploy diario desde local

Desde la raíz del proyecto:

```bash
cd /Users/ignaciowong/Documents/LBC-Documentos/clubes-lbc
chmod +x scripts/remote_deploy.sh
CPANEL_HOST=tu-host-cpanel \
CPANEL_USER=tu-usuario-cpanel \
CPANEL_PORT=22 \
CPANEL_APP_PATH=/home/tu-usuario/clubes-lbc \
BRANCH=main \
./scripts/remote_deploy.sh
```

El script hace:

1. `git push origin <branch>` local.
2. SSH a cPanel.
3. `git pull --ff-only` en servidor.
4. Ejecuta `scripts/cpanel_deploy.sh`.

## 3) Variables opcionales

- `RUN_PUSH=0`: omite push local.
- `REPO_URL=...`: fuerza URL de clonación para primer setup remoto.
