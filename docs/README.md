# Asylinx ERP — Documentation

**Asylinx ERP v1.0.0 — Production Ready.** A modular, API-first school ERP
(Laravel 12 backend, React admin, static Bootstrap public website, Flutter app).

## Guides
| Guide | Purpose |
|---|---|
| [Installation](installation.md) | First-time setup on a server or locally |
| [Deployment](deployment.md) | Production deployment + the production checklist |
| [Upgrade](upgrade.md) | Upgrading an existing installation safely |
| [Administrator](administrator.md) | Day-to-day administration of the ERP |
| [Backup & Restore](backup-restore.md) | Backups, verification and disaster recovery |
| [Troubleshooting](troubleshooting.md) | Diagnosing and fixing common problems |
| [Architecture](architecture.md) | Developer architecture guide |
| [API](api.md) | API conventions, auth, and the module surface |

## Verify a running system
```bash
php artisan system:doctor      # config validation + health checks (exit 0 = ready)
curl -s https://YOUR_HOST/api/v1/health   # public liveness probe
```
The **Production Dashboard** (Administration → Production Dashboard) shows overall
health, component status, queues, failed jobs, storage, sessions, integrations and
API performance.
