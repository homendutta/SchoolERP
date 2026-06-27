# 03 – Folder Conventions

> Where code lives. The repository has three stacks plus documentation; the backend uses the Asylinx
> two-layer enterprise structure (Platform + Modules).

---

## 1. Repository Root

```
SchoolERP/
├── backend/     # Laravel 12 API
├── frontend/    # React + Vite + Tailwind web ERP
├── mobile/      # Flutter app (all roles)
├── docs/        # product, analysis, architecture, SRS, domain model, standards
└── reference/   # original Apps Script app — reference UX only, never copied
```

No other top-level folders. Empty/unused folders are not committed.

---

## 2. Backend — Platform vs Modules

```
backend/app/
├── Platform/            # framework INFRASTRUCTURE only (no business modules)
│   ├── Core/            #   module kernel: Providers, Bootstrap, Routing, Middleware, Exceptions, Support
│   ├── Foundation/      #   infra services: Audit, Media, Notifications, Search, Cache, Tenancy
│   └── Shared/          #   reusable base classes: Http/{Controllers,Requests,Resources,Responses},
│                        #   Services, Repositories, Policies, Events, Jobs
└── Modules/<Module>/    # business vertical slice (standard structure below)
```

**Rules**
- `Platform` never contains business logic or ERP modules.
- Cross-cutting **business config** (Settings, Number Generator, Import/Export, Feature Flags) lives in
  the **Administration** module — not in Platform.
- Reusable **base classes** live in `Platform/Shared`; the **module-loading kernel** lives in
  `Platform/Core`.

---

## 3. Standard Module Structure (every module, identical)

```
<Module>/
├── Config/
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   └── Factories/
├── Events/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Jobs/
├── Models/
├── Policies/
├── Providers/<Module>ServiceProvider.php
├── Repositories/
├── Routes/
│   ├── api.php
│   └── web.php
├── Services/
└── Tests/
```

- A folder that would be empty keeps a `.gitkeep` until populated.
- A module never spans unrelated folders; one module = one slice.

---

## 4. Web Structure

```
frontend/src/
├── app/{layout, navigation, routing}/   # shell, menu model, routes
├── core/{api, auth}/                     # single API client, session
├── ui/                                   # shared design-system primitives
├── features/<module>/{pages, components, hooks, api}/
└── styles/                               # Tailwind + theme tokens
```

## 5. Mobile Structure

```
mobile/lib/
├── app/{theme, navigation}/   # MaterialApp, theme, menu model
├── core/{auth, api}/          # session, single API client
├── ui/                        # shared widgets
└── features/<module>/{presentation, domain, data}/
```

---

## 6. Documentation

```
docs/
├── 00-product/                # PRD
├── 01-existing-system-analysis/
├── 03-system-architecture/
├── 04-srs/
├── 05-domain-model/
└── 06-engineering-standards/  # this set
```
