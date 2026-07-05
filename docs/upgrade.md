# Upgrade Guide

## Before upgrading
1. Record a backup manifest and **verify** it (Administration → Production Dashboard
   → Backups, or `POST /api/v1/system/backups`).
2. Take a real database dump + media snapshot at the infrastructure level.
3. Note the current version and read the release notes.

## Upgrade steps
```bash
php artisan down                    # optional maintenance window
git fetch --tags && git checkout vX.Y.Z
composer install --no-dev --optimize-autoloader
php artisan migrate --force         # additive, backward-compatible migrations
php artisan config:cache route:cache event:cache
php artisan queue:restart
cd ../frontend && npm ci && npm run build
php artisan up
php artisan system:doctor           # post-upgrade verification
```

## Verify after upgrade
- `system:doctor` exits `0`.
- `/api/v1/health` returns `ok`.
- Spot-check a login, an attendance mark, a fee payment and a report export.
- Confirm no failed jobs accumulated (Production Dashboard → Failed Jobs).

## Rollback
Re-deploy the previous release. Because migrations are additive and APIs are
backward-compatible, the previous code runs against the current schema. Restore a
verified backup only if data was corrupted.
