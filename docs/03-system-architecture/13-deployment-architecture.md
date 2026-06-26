# 13 – Deployment Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines how the system is deployed for a
> single school — **one installation, one database, one domain** — and how the public website, ERP, and
> API coexist. No code, no schemas.

---

## 1. Deployment Model (Version 1)

Per the PRD: **single-tenant** — one ERP installation, one MySQL database, one domain per school.
**No** multi-school SaaS and **no** multi-branch in V1; the architecture stays ready for both.

```
                         schoola.com  (one domain, HTTPS)
                                │
        ┌───────────────────────┼─────────────────────────────┐
        ▼                       ▼                             ▼
  Public Website          React Web ERP                  API (Laravel 12)
  (existing site,         (static build,                 /api  → business logic
   root + marketing       served under ERP paths)             │
   paths)                       │                              ▼
        ▲ sync feed             │ consumes API            MySQL 8 (one DB)
        └───────────────────────┴──────────────┐              ▲
                                                ▼              │
                                       Queue Workers + Scheduler
                                                │              │
                                                ▼              ▼
                                       Media storage     Cache store
                                                │
                                       External providers (SMS/SMTP/Push/Payment)
```

---

## 2. One Domain, Many Paths

The public website and the ERP share the **same domain** (no separate ERP domain, no CMS):

| Path | Served by |
|------|-----------|
| `/`, `/about`, `/facilities`, `/contact` … | Existing public website |
| `/gallery`, `/videos`, `/notice-board` | Public website, fed by ERP **sync feed** |
| `/login`, `/admin`, `/teacher`, `/student`, `/parent` | React Web ERP |
| `/api/…` | Laravel API (consumed by web + mobile) |

Routing at the edge directs public paths to the website, ERP paths to the React build, and API paths
to Laravel. The Flutter app talks to the same API over the same domain.

---

## 3. Deployable Components

| Component | Description |
|-----------|-------------|
| **Web ERP build** | Static React/Vite bundle served under ERP paths. |
| **API service** | Laravel 12 application serving `/api`. |
| **MySQL 8** | Single database (system of record). |
| **Queue workers** | Process async jobs ([10-background-jobs.md](10-background-jobs.md)). |
| **Scheduler** | Triggers time-based jobs (dues, scheduled messages, reminders). |
| **Cache store** | In-memory cache ([11-caching-strategy.md](11-caching-strategy.md)). |
| **Media storage** | Single Media Library backend ([08-media-storage.md](08-media-storage.md)). |
| **Public website** | Existing static site (retained, not part of this codebase). |
| **External providers** | SMS, SMTP, Push, Payment gateways (configured per school). |

---

## 4. Environments

| Environment | Purpose |
|-------------|---------|
| **Development** | Engineering; sandbox provider/payment keys (test mode). |
| **Staging** | Pre-production verification; mirrors production config with test gateways. |
| **Production** | The school's live single-tenant installation; live gateway keys. |

Configuration (gateways, SMTP, push, storage, payment mode) is environment-specific and never
hard-coded; secrets are stored securely ([07-security-architecture.md](07-security-architecture.md)).

---

## 5. CI/CD (conceptual)

```
Commit → CI (build + tests + standards checks) → artifact
      → deploy to staging → verify → promote to production
```

- Backend, web, and mobile have their own pipelines producing their own artifacts.
- Quality gates (tests, linting, standards in [14-coding-standards.md](14-coding-standards.md)) must pass before promotion.
- Database migrations run as a controlled deploy step (designed in the DB phase).

---

## 6. Mobile Distribution

- The single Flutter app is built and distributed via the appropriate app stores / enterprise distribution.
- App configuration points at the school's domain/API; the same backend serves it.

---

## 7. Backups & Recovery

- **Backup scope:** MySQL database + Media storage + critical configuration.
- Backups are configured/operated under the **Super Admin** responsibility (Settings → Backup).
- Documented recovery procedure restores database + media to a known-good state.

---

## 8. Reliability & Availability

- Target high availability during school hours; graceful degradation when an external provider is down (SMS/Email/Payment) without blocking core operations.
- Health checks and monitoring supervise the components ([12-logging-monitoring.md](12-logging-monitoring.md)).

---

## 9. Scaling Readiness

| Dimension | V1 posture | Future readiness |
|-----------|-----------|------------------|
| **Vertical** | Size the single install to the school's volume. | Straightforward. |
| **Horizontal (API/workers)** | Stateless API + queue workers can scale out. | Add workers/instances. |
| **Multi-school SaaS** | Out of scope in V1. | Tenant-aware boundaries already respected → no redesign. |
| **Multi-branch** | Out of scope in V1. | Branch-scopable permissions/config already respected. |

No single-tenant assumption is hard-coded into core modules, satisfying the PRD extensibility mandate.

---

## 10. Security at Deployment

- HTTPS/TLS terminated at the edge; all traffic encrypted.
- Secrets injected via secure configuration, never in artifacts or logs.
- Network/segmentation, least-privilege service access, and patching are operational responsibilities of the Super Admin.

---

## 11. Non-Goals

- No specific cloud/host, container, or orchestration product mandated here (operational choice within these constraints).
- No infrastructure-as-code or pipeline scripts (implementation).
- No schemas or endpoints.
