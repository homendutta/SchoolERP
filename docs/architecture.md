# Developer Architecture Guide

## Shape
- **Backend:** Laravel 12, API-first, modular monolith. Each business capability is a
  self-contained module under `app/Modules/<Name>` with its own
  `Enums/ Models/ Services/ Http/{Controllers,Requests,Resources}/ Routes/ Providers/
  Database/Migrations`. Cross-cutting platform code lives under `app/Platform`.
- **Frontend:** React (admin) under `frontend/src/features/<module>` + a shared AX
  component library and an `EntityManager` for CRUD.
- **Public site:** static HTML/CSS/JS/Bootstrap under `website/`, hydrated from the
  read-only CMS public API.
- **Mobile:** Flutter under `mobile/lib/features/<module>` — API bindings only.

## Request flow
`Controller (thin) → Form Request (validate) → Service/Action → Model → Resource`.
Controllers never contain business logic; services own it; DTOs/Actions carry
operations; Policies + the `permission:` middleware enforce RBAC server-side.

## Platform services (reused everywhere — never duplicated)
- **Audit Engine** (`ActivityLogger`) — every business action is logged.
- **Timeline Engine** — per student/staff lifecycle events.
- **Identity Platform** (`HasIdentity`, `IdentityService`) — permanent identities +
  QR/verification; used by students, staff, library copies, assets, documents.
- **Number Generator** — all human-facing numbers (receipts, admission nos, run/
  payslip/document numbers).
- **Search Builder** — declarative filter/enum/relation/date search on list services.
- **Media Platform** — every uploaded file is referenced, never duplicated.
- **Communication Engine** — the only path for notifications.
- **Maintenance Engine** — reusable polymorphic maintenance (Inventory today).
- **Cache Platform** (Sprint 23) — grouped, version-invalidated caching.

## Engines & registries (extensible without touching callers)
- **Finance Gateway registry** → payment providers.
- **Reporting/Export/Print engines** + **ReportRegistry** → all reports.
- **Integration Provider registry** → all third-party providers.
- **Document template engine** → versioned templates + immutable generated docs.

## Conventions
- Enums (`declare(strict_types=1)`) back every status/type; models cast them.
- Models set `$attributes` defaults so freshly-created records serialize safely.
- History is immutable (submissions, salary versions, generated documents, events);
  regeneration/transfer creates new rows, never overwrites.
- Isolation: the Portal + LMS reuse a single authorization boundary (parent→children,
  student→self, teacher→assigned subjects).

## Quality gates
`vendor/bin/pest`, `vendor/bin/phpstan`, `vendor/bin/pint` (backend);
`npm run build` + ESLint (frontend); `flutter analyze` (mobile). All green at v1.0.0.
