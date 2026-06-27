# Asylinx School ERP

A modern, commercial-grade School Management ERP — single-tenant (one installation, one database, one
domain per school), delivered as a web app, a single cross-role mobile app, and an API, integrated with
the school's existing public website.

> **This repository currently contains the engineering foundation only.** No business modules,
> database tables, migrations, or API endpoints are implemented yet. The foundation is production-ready
> and follows the approved architecture so module engineering can begin on a solid base.

---

## Governing Documents (single source of truth)

The build conforms to the approved documentation set under `docs/`:

| Phase | Location |
|-------|----------|
| Product Requirements (PRD, v1.1) | `docs/00-product/` |
| Existing System Analysis | `docs/01-existing-system-analysis/` |
| System Architecture Blueprint | `docs/03-system-architecture/` |
| Software Requirements (SRS framework + modules) | `docs/04-srs/` |
| Enterprise Domain Model | `docs/05-domain-model/` |

**Binding rules carried into the code:**
- **UI Preservation Policy** — the reference application's UI/UX is preserved; its code is never copied.
- **Backend layering** — `Controller → Request → Service → Repository → Model`.
- **Organize by business module**, not by technical layer alone.
- **One API** for web and mobile · **one** Flutter app for all roles · **one** domain for website + ERP.
- **Security-first** (server-side RBAC + data scope) and **audit-everything**.

---

## Repository Layout

```
SchoolERP/
├── backend/    → Laravel 12 API (modular: Foundation, Authentication, Students,
│                 Staff, Attendance, Finance, Examination, Communication, Shared)
├── frontend/   → React + Vite + Tailwind web ERP (reference UI preserved)
├── mobile/     → Flutter single app for all roles
├── docs/       → product, analysis, architecture, SRS, domain model
└── reference/  → the original Apps Script app (reference UX only — never copied)
```

Each stack has its own README:
- [backend/README.md](backend/README.md)
- [frontend/README.md](frontend/README.md)
- [mobile/README.md](mobile/README.md)

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend API | Laravel 12 (PHP 8.2+) |
| Database | MySQL 8 (schema designed in a later phase — none yet) |
| Web | React + Vite + TypeScript + Tailwind CSS |
| Mobile | Flutter (Dart) |

---

## What the Foundation Provides

**Backend** — modular Laravel 12 skeleton with one vertical slice per business module and a `Shared`
kernel: base `Controller`, `Request`, `Resource`, `ApiResponse` envelope, `Service` + `Repository`
abstractions and contracts, base `Policy`, `DomainEvent`, `Job`, and an abstract module
`ServiceProvider`. No business logic, tables, or endpoints.

**Frontend** — Vite + Tailwind app preserving the reference navy UI: role-adaptive **Sidebar**,
**Header** (with global search), **Footer**, **Bottom navigation**, **DashboardLayout** shell, **Login**
page, the full grouped **navigation catalog** with per-role ordering, theme tokens, an auth context, and
a single API client. No business pages or API calls.

**Mobile** — Flutter foundation: navy **theme**, role-adaptive **navigation** catalog, **Login** screen,
and a single **DashboardShell** (app bar + grouped drawer + bottom nav). No business screens.

---

## Out of Scope (by design, at this stage)

- No database tables or migrations.
- No API endpoints.
- No module/business logic.
- No website CMS; the existing public site is retained and only consumes the notice/gallery sync.

---

## License

Proprietary — Asylinx.
