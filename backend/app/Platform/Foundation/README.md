# Platform / Foundation

Cross-cutting **infrastructure services** used by every business module. **Infrastructure only** — the
business-facing configuration and registries (Settings, Number Generator, Business Number Registry,
Import/Export, Feature Flags, System Configuration) live in the **Administration** module, not here.

```
Foundation/
├── Audit/           # append-only audit infrastructure
├── Media/           # the single media library (storage abstraction)
├── Notifications/   # central notification dispatch infrastructure + channel drivers
├── Search/          # global search indexing infrastructure
├── Cache/           # caching infrastructure
└── Tenancy/         # tenant-awareness primitives (single-tenant now, SaaS-ready)
```

- These provide the technical capability; business modules consume them.
- Namespace: `App\Platform\Foundation\*`.

> No business logic, tables, migrations, or endpoints. Subfolders are structural placeholders at this
> stage.
