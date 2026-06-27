# 01 – Coding Standards

> Per-stack coding rules. The architectural rules (layering, module boundaries, security) are in
> `docs/03-system-architecture/14-coding-standards.md`; this document is the day-to-day engineering
> companion.

---

## 1. Universal Rules

1. **Organize by business module**; layers live inside a module.
2. **One responsibility** per class/function/component.
3. **No reference code copied** from the original Apps Script app — only the UX is preserved.
4. **Configuration over hard-coding** — gateways, channels, limits come from config/Settings.
5. **Security & audit by default** — never weaken RBAC, data scope, or audit for convenience.
6. **No dead code, no commented-out blocks, no unused folders.**

---

## 2. Backend (Laravel 12 / PHP 8.2)

- **Style:** PSR-12, enforced by **Pint** (`pint.json`). `declare(strict_types=1);` in every file.
- **Static analysis:** **PHPStan + Larastan** (`phpstan.neon`), level 6.
- **Mandatory layering:** `Controller → Request → Service → Repository → Model` — never skip a layer.
  - **Controller:** thin orchestration; calls one Service method; returns a Resource/envelope.
  - **Request:** validation + authorization (RBAC action grant + data scope).
  - **Service:** sole home of business rules; owns transactions; emits domain events.
  - **Repository:** all persistence access; Services stay persistence-agnostic.
  - **Model:** entity concerns only (relations/casts/scopes).
- **Base classes:** extend `App\Platform\Shared\*` bases; module providers extend
  `App\Platform\Core\Providers\ModuleServiceProvider`.
- **Cross-module calls:** via the other module's Service or domain events — never reach into its data.
- **Secrets:** never in code or logs; via secure config only.

---

## 3. Web (React + Vite + Tailwind / TypeScript)

- **Type safety:** TypeScript `strict: true`; explicit types on public interfaces and API models.
- **Lint:** ESLint flat config (`eslint.config.js`); **Prettier** owns formatting (`.prettierrc.json`).
- **Structure:** feature-first under `src/features/*`; shared primitives in `src/ui/*`.
- **UI preservation:** reproduce the reference layout/navigation/components; compose from `ui/`,
  don't reinvent.
- **State:** server state via per-resource hooks; local UI state local; cross-cutting in providers.
- **API access:** only through the single API client (`src/core/api`); no ad-hoc `fetch` in components.
- **Permissions:** gate menus/actions/routes by permission + scope; the backend remains authoritative.

---

## 4. Mobile (Flutter / Dart)

- **Lint:** `flutter analyze` with `flutter_lints` (`analysis_options.yaml`); `dart format` for style.
- **Structure:** feature-first; layered presentation → domain → data.
- **Single app:** one app, role-adaptive; no per-role forks.
- **API access:** via the single shared client; no business logic on device.
- **Null safety:** sound null safety; avoid `!` unless provably safe.

---

## 5. Documentation in code

- Every module carries a short README (purpose, services, events) as it is built.
- Public service/contract interfaces are documented with intent, not restated signatures.
- Architectural decisions that *refine* the blueprint are recorded; product changes go to the PRD first.
