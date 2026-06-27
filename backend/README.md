# Backend — Asylinx School ERP (Laravel 12)

The single API serving the React web app and the Flutter app. **Enterprise architecture only** — no
business logic, database tables, migrations, or endpoints yet.

## Two Layers

```
app/
├── Platform/                 # framework-wide INFRASTRUCTURE (no business modules)
│   ├── Core/                 #   module kernel: ModuleServiceProvider, routing, bootstrap, middleware
│   ├── Foundation/           #   infra services: audit, media, notifications, search, cache, tenancy
│   └── Shared/               #   reusable base classes (Controller/Request/Resource/Service/
│                             #   Repository/Policy/Event/Job/ApiResponse)
│
├── Modules/                  # BUSINESS modules — one vertical slice each
│   ├── Administration/       #   settings · master data · number generator · business number
│   │                         #   registry · import/export · feature flags · system configuration
│   ├── Authentication/
│   ├── Academic/             #   academic years · terms · classes · sections · subjects ·
│   │                         #   subject types · subject groups · houses · streams
│   ├── Admissions/           #   enquiry · registration · admission workflow · enrollment
│   ├── Students/             #   student lifecycle AFTER admission
│   ├── Parents/
│   ├── Staff/
│   ├── Attendance/
│   ├── Timetable/
│   ├── Examination/
│   ├── Finance/
│   ├── Accounts/
│   ├── Communication/
│   ├── Website/              #   public website integration · notice publishing · gallery ·
│   │                         #   video gallery · public APIs
│   ├── Reports/
│   ├── Assets/
│   └── Inventory/
│   │
│   └── (future placeholders) Library · Transport · Hostel · Payroll · Visitor · Alumni
│
└── Providers/AppServiceProvider.php
```

## Standard Module Structure

Every business module follows the same internal layout:

```
ModuleName/
├── Config/
├── Database/{Migrations, Seeders, Factories}/
├── Events/
├── Http/{Controllers, Requests, Resources}/
├── Jobs/
├── Models/
├── Policies/
├── Providers/ModuleNameServiceProvider.php
├── Repositories/
├── Routes/{api.php, web.php}
├── Services/
└── Tests/
```

## Layering (mandatory)

```
Controller → Request → Service → Repository → Model
```

- **Controller** — thin orchestration; calls one Service method; returns a Resource/envelope.
- **Request** — validation + authorization (RBAC action grant + data scope).
- **Service** — the only home of business rules; owns transactions; emits domain events.
- **Repository** — the only place that touches persistence.
- **Model** — Eloquent entity (schema designed in a later phase).

Base classes live in `App\Platform\Shared\*`; every module provider extends
`App\Platform\Core\Providers\ModuleServiceProvider`.

## Registry

- `bootstrap/providers.php` — registers the 17 active module providers (in dependency order). Future
  modules are intentionally not registered.
- `config/modules.php` — the architecture registry (layers, enabled modules, future modules, standard
  structure).

## Conventions

- PSR-12 + Laravel Pint (`pint.json`); `declare(strict_types=1)` everywhere.
- Modules communicate via services and domain events — never by reaching into another module's data.
- Cross-cutting **infrastructure** lives in `Platform`; cross-cutting **business config** (settings,
  numbering, import/export) lives in the **Administration** module.

## Getting started (when implementing)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

> Database schema, migrations, and API endpoints are produced in their own phases and are intentionally
> absent here.
