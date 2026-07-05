# Deployment Guide

## Production checklist
Run `php artisan system:doctor` — it must exit `0`. It validates:

- [ ] `APP_KEY` set
- [ ] `APP_DEBUG=false` in production
- [ ] `APP_URL` set to the real domain
- [ ] Database reachable
- [ ] Default storage disk writable
- [ ] Queue driver is **not** `sync` (use redis/database) + a worker is running
- [ ] Mail transport configured (not `log`)
- [ ] Secure session cookies over HTTPS (`SESSION_SECURE_COOKIE=true`)

Additional manual checks:
- [ ] HTTPS enforced (HSTS at the proxy)
- [ ] `php artisan config:cache route:cache event:cache` after deploy
- [ ] `npm run build` artifacts deployed for the admin
- [ ] Queue worker supervised (systemd/supervisor) with restart-on-failure
- [ ] Cron entry installed for `schedule:run`
- [ ] Backups scheduled + verified (see Backup & Restore)
- [ ] Rate limits reviewed (auth, public verification, CMS forms, webhooks)

## Deploy steps
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan event:cache
php artisan queue:restart          # reload workers with new code
cd ../frontend && npm ci && npm run build
php artisan system:doctor          # post-deploy verification
```

## Zero-downtime notes
Migrations in this project are additive/backward-compatible (Sprint 23 index
additions included) — safe to run before switching traffic. Roll back by
re-deploying the previous release; the schema remains compatible.

## Scaling
- **Cache/Queue:** move to Redis. The `CachePlatform` groups (settings, master_data,
  academic, menus, roles, permissions, dashboards, report_catalog) invalidate by
  version, so scaling out is safe.
- **Queue:** run multiple `queue:work` processes; large report exports, webhook
  deliveries and bulk document generation are already queued.
- **Static:** serve the admin `dist/`, the public `website/`, and media via a CDN.
