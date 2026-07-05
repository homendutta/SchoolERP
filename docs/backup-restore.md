# Backup & Restore / Disaster Recovery

The System module records **backup manifests** (metadata + a checksum) for
Database, Media, Config or Full backups, and verifies them. Cloud backup providers
are out of scope — pair these manifests with real infrastructure-level dumps.

## What each backup covers
- **Database** — table list + per-table row counts.
- **Media** — object count in the Media Platform.
- **Config** — environment/driver snapshot.
- **Full** — all of the above.

## Recording + verifying a backup
```bash
# via API (admin token)
POST /api/v1/system/backups        { "type": "full", "note": "nightly" }
POST /api/v1/system/backups/{id}/verify
```
Or use Administration → Production Dashboard → Backups. Verification recomputes the
manifest checksum and marks the record `verified`.

## Real backups (recommended cron)
```bash
# Database
mysqldump --single-transaction asylinx > /backups/db-$(date +%F).sql
# Media (storage/app/public or your disk root)
tar czf /backups/media-$(date +%F).tgz storage/app/public
# then record a manifest so recovery is auditable
php artisan tinker --execute="app(App\\Modules\\System\\Services\\BackupService::class)->create(App\\Modules\\System\\Enums\\BackupType::Full, null, null);"
```

## Restore procedure
1. Put the app in maintenance: `php artisan down`.
2. Restore the database dump into a clean database.
3. Restore the media archive to the storage disk; `php artisan storage:link`.
4. Restore `.env` (or re-enter secrets) and `php artisan config:cache`.
5. `php artisan migrate --force` (idempotent) then `php artisan up`.
6. Validate: `php artisan system:doctor` + spot-check logins, fees, reports.

## Recovery validation
- Row counts in the restored DB match the latest **verified** manifest.
- `/api/v1/health` returns `ok`; no failed jobs; integrations healthy.
- Confirm a QR/document verification still resolves (Identity + Documents intact).
