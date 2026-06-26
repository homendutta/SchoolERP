# 00 – System Architecture Overview

**Product:** SchoolERP
**Document type:** System Architecture Blueprint — Overview
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> This blueprint governs all future engineering work. It implements — and must not change — the
> approved **Product Requirements Document** (`docs/00-product/`, Version 1.1, Feature Complete).
> This document does **not** redesign the product, add features, design database tables, design API
> endpoints, or contain code. It defines **how** the product is built.

---

## 1. Purpose & Authority

| Aspect | Statement |
|--------|-----------|
| **Source of truth** | The approved PRD set in `docs/00-product/`. Where this blueprint is silent, the PRD governs. |
| **Scope** | System structure, layering, module organization, cross-cutting services, deployment. |
| **Out of scope** | Product decisions, feature additions, table schemas, endpoint signatures, implementation code. |
| **Binding constraint** | The **UI Preservation Policy** ([08-ui-ux-principles.md](../00-product/08-ui-ux-principles.md) §0): the reference application's UI/UX is preserved; its code is never copied. |

---

## 2. Architectural Principles (from the PRD)

The architecture is shaped by the PRD's product principles:

1. **Architecture first** — structure precedes features; modules stay decoupled.
2. **API first** — one API serves both the React web app and the Flutter mobile app.
3. **Security first** — least-privilege RBAC and audit on every material action.
4. **Mobile first** — every primary workflow works on a phone.
5. **Modular design** — code is organized by **business module**, not technical layer alone.
6. **Reusable components** — shared UI and domain patterns across surfaces.
7. **Role-based access** — nothing visible/actionable without an explicit permission.
8. **Audit everything** — central, searchable, exportable audit trail.
9. **Scalable architecture** — single-tenant now, multi-tenant/multi-branch ready without redesign.
10. **Enterprise-grade standards** — consistent, documented, maintainable engineering.
11. **UI preservation (mandatory)** — preserve reference UX, rebuild the implementation.

---

## 3. Technology Stack (fixed by the PRD)

| Layer | Technology |
|-------|-----------|
| **Web frontend** | React + Vite + Tailwind CSS |
| **Mobile** | Flutter (single app, all roles) |
| **Backend API** | Laravel 12 |
| **Database** | MySQL 8 |
| **Cross-cutting services** | One Notification Service · One Media Library · Centralized Audit · Number Generator · Global Search |
| **External providers** | SMS gateway, SMTP, Push provider, Payment gateways (Razorpay, PhonePe, Cashfree) — all pluggable |

---

## 4. System Context (conceptual)

```
                          ┌──────────────────────────────────────────┐
                          │              schoola.com (one domain)      │
                          └──────────────────────────────────────────┘
                                              │
        ┌──────────────────────┬──────────────┴───────────────┬───────────────────────┐
        ▼                      ▼                               ▼                       ▼
┌────────────────┐   ┌──────────────────┐          ┌────────────────────┐   ┌──────────────────┐
│ Public Website │   │  React Web ERP    │          │  Flutter Mobile App │   │  External Providers│
│ (existing      │   │  /login /admin    │          │  (all roles, one    │   │  SMS · SMTP · Push │
│  HTML/CSS/JS)  │   │  /teacher /student│          │   adaptive app)     │   │  Payment gateways  │
└───────┬────────┘   │  /parent          │          └─────────┬──────────┘   └─────────┬────────┘
        │            └─────────┬─────────┘                    │                        │
        │ public notices/      │ API (HTTPS)                  │ API (HTTPS)            │ provider APIs
        │ gallery (read)       ▼                              ▼                        │
        │            ┌──────────────────────────────────────────────────┐             │
        └───────────▶│                 Laravel 12 API                     │◀────────────┘
          sync feed  │   Controller → Request → Service → Repository →    │
                     │   Model   (organized by business module)          │
                     │   + Notification Svc · Media Library · Audit ·     │
                     │     Number Generator · Search · Jobs · Cache       │
                     └───────────────────────┬──────────────────────────┘
                                             ▼
                                     ┌───────────────┐
                                     │   MySQL 8     │
                                     └───────────────┘
```

**Key facts:**
- **One domain** hosts the public website (root + marketing paths) and the ERP (under `/login`, `/admin`, `/teacher`, `/student`, `/parent`). See [13-deployment-architecture.md](13-deployment-architecture.md).
- **One API** (Laravel 12) is consumed identically by the React web app and the Flutter app.
- The **public website** remains the existing HTML/CSS/JS site; the ERP **synchronizes outward only** Public Notices, Photo Gallery, and Video Gallery.

---

## 5. Container View (logical components)

| Container | Responsibility |
|-----------|----------------|
| **React Web ERP** | Role-adaptive web client preserving the reference UI/UX; consumes the API. |
| **Flutter Mobile App** | Single role-adaptive mobile client; consumes the same API; receives push. |
| **Public Website** | Existing public site; receives synced notices/gallery; entry point to login. |
| **Laravel 12 API** | All business logic, organized by module via Controller→Request→Service→Repository→Model. |
| **MySQL 8** | System of record (single database per school). |
| **Queue Workers** | Asynchronous jobs (notifications, dues generation, imports/exports, reports). See [10-background-jobs.md](10-background-jobs.md). |
| **Scheduler** | Time-based jobs (scheduled messages, monthly dues, reminders). |
| **Media Library** | Single storage service for all files (branding, documents, gallery, receipts). See [08-media-storage.md](08-media-storage.md). |
| **Notification Service** | Single dispatcher across Notice/SMS/Email/Push (+future WhatsApp). See [09-notification-architecture.md](09-notification-architecture.md). |
| **Cache** | Application/query/HTTP caching. See [11-caching-strategy.md](11-caching-strategy.md). |

---

## 6. Cross-Cutting Services (single, shared)

Per the PRD, these are **centralized and shared** across all modules and both clients:

| Service | Mandate |
|---------|---------|
| **Notification Service** | One service for all channels; provider-pluggable; logs every send. |
| **Media Library** | One library for all media; one access-control model; feeds website/app sync. |
| **Audit Logging** | One central, searchable, filterable, exportable audit trail. |
| **Number Generator** | One configurable generator for all official numbers/codes. |
| **Global Search** | One permission-scoped search available from every page. |
| **Import / Export** | One bulk data-movement capability across listed modules. |

These map directly to the PRD's Platform & Cross-Cutting Capabilities
([02-module-catalog.md](../00-product/02-module-catalog.md) §48–52).

---

## 7. Request Lifecycle (high level)

```
Client (React / Flutter)
   │  authenticated, permission-aware request
   ▼
API Gateway/Router (Laravel)
   ▼
Middleware  → auth · permission · scope · rate-limit · audit-context
   ▼
Controller  → orchestration only
   ▼
Request     → validation + authorization
   ▼
Service     → business rules (the PRD's validated workflows)
   ▼
Repository  → data access abstraction
   ▼
Model / MySQL 8
   ▼
Response envelope → client
   │  side effects (async): notifications, audit, search index, cache invalidation
   ▼
Queue / Workers
```

Detail in [03-backend-architecture.md](03-backend-architecture.md) and
[06-api-architecture.md](06-api-architecture.md).

---

## 8. Module-Oriented Organization

The codebase is organized by **business module** (Admissions, Students, Fees, Attendance,
Examinations, Communication, …) rather than by technical layer alone. Each module is a vertical slice
that contains its own controllers, requests, services, repositories, and models. Cross-cutting
services are shared. See [01-folder-structure.md](01-folder-structure.md) and
[02-module-architecture.md](02-module-architecture.md).

This mirrors the PRD module catalog so that product modules map one-to-one onto code modules.

---

## 9. Single-Tenant Now, Multi-Tenant Ready

- **Version 1:** one installation, one MySQL database, one domain per school.
- **Forward design:** tenant-aware boundaries, configuration over hard-coding, and branch-scopable
  permissions are respected from day one so multi-school SaaS and multi-branch can be added **without
  redesign**. No single-tenant assumption is hard-coded into core modules.

See [13-deployment-architecture.md](13-deployment-architecture.md) §Scaling and
[07-security-architecture.md](07-security-architecture.md) §Tenant Isolation Readiness.

---

## 10. Document Map (this blueprint)

| # | Document | Governs |
|---|----------|---------|
| 00 | **00-system-overview.md** (this) | Overall architecture and principles. |
| 01 | [01-folder-structure.md](01-folder-structure.md) | Repository/folder organization. |
| 02 | [02-module-architecture.md](02-module-architecture.md) | Module boundaries and internal structure. |
| 03 | [03-backend-architecture.md](03-backend-architecture.md) | Laravel layering (Controller→…→Model). |
| 04 | [04-frontend-architecture.md](04-frontend-architecture.md) | React + Vite + Tailwind structure. |
| 05 | [05-mobile-architecture.md](05-mobile-architecture.md) | Flutter single-app architecture. |
| 06 | [06-api-architecture.md](06-api-architecture.md) | One-API conventions for both clients. |
| 07 | [07-security-architecture.md](07-security-architecture.md) | Auth, RBAC, scoping, secrets. |
| 08 | [08-media-storage.md](08-media-storage.md) | Single media library. |
| 09 | [09-notification-architecture.md](09-notification-architecture.md) | Single notification service. |
| 10 | [10-background-jobs.md](10-background-jobs.md) | Queues, workers, scheduling. |
| 11 | [11-caching-strategy.md](11-caching-strategy.md) | Caching layers and invalidation. |
| 12 | [12-logging-monitoring.md](12-logging-monitoring.md) | Logging, audit, monitoring. |
| 13 | [13-deployment-architecture.md](13-deployment-architecture.md) | Environments, one-domain hosting. |
| 14 | [14-coding-standards.md](14-coding-standards.md) | Engineering standards across stacks. |

---

## 11. Non-Goals of This Blueprint

- No database table or column design (separate Database Design phase).
- No API endpoint catalog or request/response schemas (separate API Design phase).
- No implementation code in any language.
- No new product scope — the PRD is feature complete.
