# Deploy cPanel - clubes.lbcchile.com

## Estructura recomendada (con releases)

- Proyecto Laravel raíz: `/home/usuario/clubes-lbc`
- Release activo: `/home/usuario/clubes-lbc/current`
- Releases históricos: `/home/usuario/clubes-lbc/releases`
- Recursos compartidos: `/home/usuario/clubes-lbc/shared`
- DocumentRoot del subdominio `clubes.lbcchile.com`: `/home/usuario/clubes-lbc/current/public`

Si no se puede configurar DocumentRoot fuera de `public_html`, usar carpeta pública separada con `index.php` ajustado apuntando al proyecto real.

## Pasos mínimos (manual)

1. Subir código (Git, zip o rsync) sin `.env`.
2. Crear `.env` en servidor con credenciales reales en `shared/.env`.
3. Crear base de datos MySQL/MariaDB y usuario.
4. Ejecutar release:
   ```bash
   cd /home/usuario/clubes-lbc
   bash scripts/deploy/release.sh /home/usuario/clubes-lbc /tmp/clubes-lbc-release.tgz 5
   ```
5. Verificar permisos de `shared/storage` y `bootstrap/cache`.
6. Confirmar SSL activo y URL final `https://clubes.lbcchile.com`.

## Deploy automatizado

Se incluye workflow:

- `.github/workflows/deploy-cpanel.yml`
- Script cPanel Git directo: `scripts/cpanel_deploy.sh` + `.cpanel.yml`
- Script terminal SSH directo: `scripts/remote_deploy.sh`
- Guía rápida de terminal: `DEPLOY_TERMINAL_CPANEL.md`

Secrets requeridos en GitHub:

- `CPANEL_SSH_HOST`
- `CPANEL_SSH_PORT`
- `CPANEL_SSH_USER`
- `CPANEL_SSH_PRIVATE_KEY`
- `CPANEL_APP_PATH`

## Rollback rápido

```bash
cd /home/usuario/clubes-lbc
bash scripts/deploy/rollback.sh /home/usuario/clubes-lbc
```

## No subir a repositorio

- `.env`
- `storage/app/private/*`
- `storage/logs/*`
- datos sensibles de clubes
- `node_modules`

## Checklist rápido

- Login admin funciona.
- `/inscripciones` carga temporada default y divisiones activas.
- envío público crea `submission` + `submission_version`.
- descarga de archivos funciona solo autenticado.
- enlaces de corrección validan token/estado.
