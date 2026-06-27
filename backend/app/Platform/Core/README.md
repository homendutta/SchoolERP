# Platform / Core

The **framework kernel** — the machinery that loads and wires business modules and handles
framework-wide concerns. Infrastructure only.

```
Core/
├── Providers/      # ModuleServiceProvider (abstract base every module provider extends)
├── Bootstrap/      # application/module bootstrapping
├── Routing/        # module route registration kernel
├── Middleware/     # framework middleware (auth/permission/scope/audit-context hooks)
├── Exceptions/     # exception handling -> standard API envelope
└── Support/        # framework-level helpers
```

- `ModuleServiceProvider` is the single contract every `App\Modules\*\Providers\*ServiceProvider`
  extends; it defines the consistent wiring hooks (bindings, policies, listeners, routes).
- Namespace: `App\Platform\Core\*`.

> No business logic. Subfolders other than `Providers/` are structural placeholders at this stage.
