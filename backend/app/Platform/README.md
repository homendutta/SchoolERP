# Platform Layer

Framework-wide **infrastructure** for the Asylinx School ERP. The Platform layer contains **no business
modules** — only reusable building blocks and infrastructure that every business module depends on.

```
Platform/
├── Core/         # the framework kernel: module system, routing, bootstrap, middleware, exceptions
├── Foundation/   # infrastructure services: audit, media, notifications, search, cache, tenancy
└── Shared/       # reusable base building blocks used by all modules
```

| Sub-layer | Responsibility | Contains |
|-----------|----------------|----------|
| **Core** | The mechanism that loads and wires modules and handles framework concerns. | `Providers/ModuleServiceProvider` (abstract base every module provider extends), plus `Bootstrap/`, `Routing/`, `Middleware/`, `Exceptions/`, `Support/`. |
| **Foundation** | Cross-cutting **infrastructure** (technical, not business). | `Audit/`, `Media/`, `Notifications/`, `Search/`, `Cache/`, `Tenancy/`. |
| **Shared** | Reusable **base classes** for the module layering. | `Http/{Controllers,Requests,Resources,Responses}`, `Services/`, `Repositories/`, `Policies/`, `Events/`, `Jobs/`. |

## Rules
- Platform is **infrastructure only**. Business behaviour (Settings, Number Generator, Import/Export,
  master data, etc.) lives in the **Administration** business module, not here.
- Business modules **depend on** Platform; Platform depends on **no business module**.
- Namespaces: `App\Platform\Core\*`, `App\Platform\Foundation\*`, `App\Platform\Shared\*`.

> Refactor stage: structure + the relocated reusable base classes only. No business logic, tables,
> migrations, or endpoints.
