# Release Checklist (cPanel)

## Antes de desplegar

- Verificar rama correcta (`main`) y cambios aprobados.
- Confirmar `.env` de producción actualizado en `shared/.env`.
- Confirmar backup reciente de base de datos.
- Ejecutar en local:
  - `php artisan test`
  - `npm run build`

## Deploy

- Ejecutar workflow `Deploy cPanel` en GitHub Actions.
- Verificar que el artifact se suba a `/tmp/clubes-lbc-release.tgz`.
- Confirmar que `scripts/deploy/release.sh` termine OK.

## Validación post-release

- Probar login admin.
- Probar `/inscripciones` y envío de ejemplo.
- Verificar descarga de archivos protegidos desde admin.
- Revisar `storage/logs/laravel.log` por errores críticos.

## Rollback

Si hay regresión severa:

```bash
cd /home/usuario/clubes-lbc
bash scripts/deploy/rollback.sh /home/usuario/clubes-lbc
```

Luego validar nuevamente rutas críticas.
