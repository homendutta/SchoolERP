# 01 – Folder Structure

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines how source code is organized
> across the three surfaces and the shared workspace. The organizing rule is **by business module**,
> not by technical layer alone. These are **conventions and folder trees** — not code, not schemas.

---

## 1. Repository Strategy

The product is delivered as three deployables sharing one API contract:

| Surface | Stack | Deliverable |
|---------|-------|-------------|
| **Backend API** | Laravel 12 + MySQL 8 | The single API + business logic. |
| **Web ERP** | React + Vite + Tailwind | Role-adaptive web client. |
| **Mobile** | Flutter | Single role-adaptive app. |

A **mono-repo or coordinated multi-repo** may be used; either way the top-level layout is consistent
so a contributor can locate any module across surfaces by the same module name.

```
schoolerp/
├── backend/      → Laravel 12 API
├── web/          → React + Vite + Tailwind ERP
├── mobile/       → Flutter app
├── shared/       → cross-surface contracts & assets (non-code where possible)
└── docs/         → product, analysis, and architecture documentation
```

> The existing public **website** is **not** part of this codebase (no CMS). It is the school's
> retained HTML/CSS/JS site that consumes the sync feed (notices/gallery).

---

## 2. Backend Folder Structure (Laravel 12, module-oriented)

The backend is organized so that **each business module is a self-contained vertical slice** holding
its own Controller → Request → Service → Repository → Model, plus its policies, events, jobs, and
resources. Cross-cutting services live in a shared core.

```
backend/
├── app/
│   ├── Core/                     → framework-level shared concerns
│   │   ├── Auth/                 → authentication & session primitives
│   │   ├── Permissions/          → RBAC enforcement helpers
│   │   ├── Audit/                → central audit logging
│   │   ├── NumberGenerator/      → centralized number/code generation
│   │   ├── Search/               → global search service
│   │   ├── Media/                → single media library service
│   │   ├── Notifications/        → single notification service + channel drivers
│   │   ├── ImportExport/         → bulk data movement engine
│   │   ├── Support/              → shared DTOs, value objects, helpers
│   │   └── Http/                 → base controllers, middleware, response envelope
│   │
│   ├── Modules/                  → one folder per business module
│   │   ├── Admissions/
│   │   │   ├── Http/Controllers/
│   │   │   ├── Http/Requests/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   ├── Models/
│   │   │   ├── Policies/
│   │   │   ├── Events/  Listeners/  Jobs/
│   │   │   └── Resources/         → API response transformers
│   │   ├── Students/
│   │   ├── Parents/
│   │   ├── Staff/  Users/  RolePermission/
│   │   ├── Classes/ Sections/ Subjects/ TeacherAssignments/
│   │   ├── Attendance/ Timetable/ LessonPlanning/ TeachingLogbook/
│   │   ├── PTM/ Substitutes/
│   │   ├── Examinations/ Marks/ HallTickets/
│   │   ├── Discipline/ Conduct/ Activities/
│   │   ├── FeeStructure/ FeeCollection/ FeeDues/ Accounts/
│   │   ├── Inventory/ Assets/ Documents/ Calendar/
│   │   ├── Complaints/ Helpdesk/
│   │   ├── Communication/ NoticeBoard/ Gallery/
│   │   ├── PaymentGateway/
│   │   ├── Reports/ Dashboard/ Settings/
│   │   └── WebsiteSync/           → outward sync of notices/gallery
│   │
│   └── Providers/                → service providers wiring modules & core
│
├── routes/                       → route files grouped per module (no endpoint design here)
├── config/                       → configuration (gateways, channels, storage, tenancy-ready)
├── database/                     → migrations/seeders (designed in the DB phase, not here)
├── storage/                      → local media + logs (abstracted by Media Library)
├── tests/                        → unit / feature tests grouped by module
└── ...
```

**Rules**
- A module **never** reaches into another module's internals; it talks via that module's service or via events.
- Cross-cutting concerns belong in `Core/`, never duplicated inside modules.
- The layer order **Controller → Request → Service → Repository → Model** is mandatory ([03-backend-architecture.md](03-backend-architecture.md)).

---

## 3. Web Folder Structure (React + Vite + Tailwind, feature-first)

The web app mirrors the backend's module list as **features**, preserving the reference UI shell
(sidebar, dashboard, tables, dialogs).

```
web/
├── src/
│   ├── app/                      → app shell, routing, providers
│   │   ├── layout/               → sidebar, header, bottom-nav (preserved UX)
│   │   ├── routing/              → role-adaptive routes (/admin /teacher …)
│   │   └── providers/            → auth, theme, query/cache, i18n
│   │
│   ├── core/                     → cross-cutting front-end concerns
│   │   ├── api/                  → single API client + interceptors
│   │   ├── auth/                 → session, permissions, scope
│   │   ├── permissions/          → permission/role gates for UI
│   │   ├── search/               → global search (available everywhere)
│   │   └── utils/
│   │
│   ├── ui/                       → reusable design-system components
│   │   ├── cards/ tables/ forms/ dialogs/ charts/ skeletons/
│   │   └── theme/                → Tailwind tokens, branding hooks
│   │
│   ├── features/                 → one folder per business module
│   │   ├── admissions/
│   │   │   ├── pages/  components/  hooks/  api/
│   │   ├── students/ parents/ staff/ users/ roles/
│   │   ├── classes/ subjects/ assignments/
│   │   ├── attendance/ timetable/ lessonPlans/ logbook/ ptm/ substitutes/
│   │   ├── exams/ marks/ hallTickets/ discipline/ conduct/ activities/
│   │   ├── feeStructure/ feeCollection/ feeDues/ accounts/ payments/
│   │   ├── inventory/ assets/ documents/ calendar/
│   │   ├── complaints/ helpdesk/
│   │   ├── communication/ notices/ gallery/
│   │   ├── reports/ dashboard/ settings/
│   │
│   └── main.tsx                  → entry (Vite)
├── public/
└── ...
```

**Rules**
- The **layout shell** (sidebar grouping, header, navigation flow, page hierarchy) preserves the reference application precisely ([04-frontend-architecture.md](04-frontend-architecture.md)).
- Shared visual primitives live in `ui/`; features compose them rather than inventing new patterns.
- Each feature owns its pages, components, hooks, and its thin API binding to the single API client.

---

## 4. Mobile Folder Structure (Flutter, single app, feature-first)

One app for all roles; the same module names as web/backend.

```
mobile/
├── lib/
│   ├── app/                      → app entry, routing, role-adaptive shell
│   ├── core/                     → api client, auth, permissions, push, storage
│   ├── ui/                       → shared widgets (cards, tables, dialogs, theme)
│   ├── features/                 → one folder per business module
│   │   ├── dashboard/ notices/ attendance/ marks/ fees/ ptm/ …
│   │   └── (each: presentation / domain / data)
│   └── main.dart
├── assets/                       → branding-driven assets, fonts
└── ...
```

**Rules**
- A single app adapts dashboards/menus by role after login ([05-mobile-architecture.md](05-mobile-architecture.md)).
- Layered per feature: presentation → domain → data, consuming the one shared API client.

---

## 5. Shared Workspace

```
shared/
├── contracts/        → API contract artifacts (produced in the API Design phase)
├── enums/            → canonical enumerations/value lists (board stages, statuses…)
├── branding/         → reference branding asset placeholders
└── conventions/      → naming, formatting references used by all surfaces
```

> `shared/` holds **contracts and conventions**, not business logic. The authoritative API contract is
> defined later (API Design phase); this folder is the agreed home for it.

---

## 6. Naming & Placement Conventions

| Convention | Rule |
|------------|------|
| **Module name parity** | The same module name is used across backend `Modules/`, web `features/`, and mobile `features/`. |
| **One module = one folder** | A business module never spans unrelated folders. |
| **Core vs. module** | Anything used by 2+ modules lives in `Core/` (backend) or `core/` (clients), never duplicated. |
| **No layer-only top level** | The top level is modules; layers (controllers/services/…) live *inside* a module. |
| **Tests beside modules** | Tests are grouped by module to keep slices self-contained. |
| **No reference code** | No file may be copied from the reference Apps Script application. |

---

## 7. Mapping to the PRD

Every folder under `Modules/` / `features/` corresponds to a module in the PRD module catalog
([02-module-catalog.md](../00-product/02-module-catalog.md)). Platform capabilities (Number Generator,
Audit, Global Search, Import/Export, Media, Notifications) live in `Core/` / `core/`. This one-to-one
mapping keeps product and code aligned and makes the SRS traceable to folders.
