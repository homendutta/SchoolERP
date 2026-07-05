# Troubleshooting Guide

## First stop
```bash
php artisan system:doctor          # config + health, exit code
curl -s /api/v1/health             # public liveness
```
Then open Administration → Production Dashboard and read the component health +
readiness checks.

## Common issues
| Symptom | Likely cause | Fix |
|---|---|---|
| 401 on admin APIs | Missing/expired token | Re-login; check Sanctum config |
| 403 on an action | Role lacks the permission slug | Grant the permission under Administration |
| Jobs never run | `QUEUE_CONNECTION=sync` or no worker | Set redis/database + run `queue:work` |
| Queued exports/webhooks stuck | Worker down | Restart the supervised worker; check Failed Jobs |
| Emails not sent | `MAIL_MAILER=log` | Configure a real transport |
| Scheduler "no heartbeat" | Cron not installed | Add the `schedule:run` cron entry |
| Stale settings/menus | Cache not invalidated | `CachePlatform::invalidate('<group>')` on write |
| Storage errors | Disk not writable / no symlink | `php artisan storage:link`; fix permissions |
| Slow reports | Large unfiltered run | Add filters; large exports are queued |
| Public verify fails | Wrong document number/code | Verify via QR (Identity) or exact number |

## Logs
- Unified operator log: `GET /api/v1/system/logs` (filter by `log_name`, `action`, date).
- Integration request logs: Integrations → Integration Logs.
- Laravel log: `storage/logs/laravel.log`.

## Failed jobs
Production Dashboard → Failed Jobs (needs the database queue + `failed_jobs` table).
Retry: `POST /api/v1/system/failed-jobs/{id}/retry`. Purge: `DELETE …/{id}`.

## Rate limiting (429)
Public verification (30/min), CMS forms (20/min), incoming webhooks (60/min) and the
health probe (60/min) are throttled. Adjust limits at the route/proxy if legitimate
traffic is blocked.
