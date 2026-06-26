# 14 – Coding Standards

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the engineering standards that
> govern all future implementation across backend, web, and mobile. These are **standards and rules**,
> not implementation code.

---

## 1. Universal Principles

1. **Organize by business module** — modules first, layers inside ([02-module-architecture.md](02-module-architecture.md)).
2. **Single responsibility** — each class/component/function does one thing.
3. **Explicit over implicit** — clear names, clear contracts, no hidden side effects.
4. **Security and audit by default** — never weaken RBAC, scope, or audit for convenience.
5. **No reference code** — the Apps Script reference application's **code is never copied**; only its UX is preserved ([08-ui-ux-principles.md](../00-product/08-ui-ux-principles.md) §0).
6. **Configuration over hard-coding** — gateways, channels, storage, limits come from config/Settings.
7. **Tenant-aware-ready** — never hard-code single-tenant assumptions into core logic.

---

## 2. Backend Standards (Laravel 12 / PHP)

| Area | Standard |
|------|----------|
| **Style** | Follow PSR-12 and Laravel conventions; automated formatting/linting. |
| **Layering** | Mandatory **Controller → Request → Service → Repository → Model**; no layer-skipping. |
| **Controllers** | Thin orchestration only; no business logic, no validation, no direct DB access. |
| **Requests** | All validation + authorization live here (form requests + policies/gates). |
| **Services** | Sole home of business rules and workflows; own transaction boundaries. |
| **Repositories** | All data access; services stay persistence-agnostic. |
| **Models** | Entity concerns only (relations/casts/scopes); no workflow logic. |
| **Events** | Cross-module side effects via domain events/listeners (audit, notify, search, cache). |
| **Naming** | Descriptive, module-scoped class names; verbs for services, nouns for resources. |
| **Errors** | Domain errors translated to the standard API envelope; no leaking internals. |
| **Secrets** | Never in code or logs; via secure config only. |

---

## 3. Web Standards (React + Vite + Tailwind / TypeScript)

| Area | Standard |
|------|----------|
| **Structure** | Feature-first modules + shared `ui/` design system ([04-frontend-architecture.md](04-frontend-architecture.md)). |
| **UI preservation** | Reproduce reference layout/navigation/components; compose from `ui/`, don't reinvent. |
| **Typing** | TypeScript with explicit types on public interfaces and API models. |
| **Styling** | Tailwind utility classes + theme tokens; branding via tokens, no inline magic values. |
| **State** | Server state via per-resource hooks (SWR style); local UI state local; cross-cutting in providers. |
| **API access** | Only through the single shared API client; no ad-hoc fetch in components. |
| **Permissions** | Gate menus/actions/routes by permission + scope; backend remains authoritative. |
| **Accessibility** | Semantic markup, keyboard support, contrast, clear validation messaging. |
| **Linting/format** | ESLint + formatter enforced in CI. |

---

## 4. Mobile Standards (Flutter / Dart)

| Area | Standard |
|------|----------|
| **Structure** | Feature-first; layered presentation → domain → data ([05-mobile-architecture.md](05-mobile-architecture.md)). |
| **Single app** | One app, role-adaptive; no per-role forks. |
| **API access** | Only via the single shared API client; no business logic on device. |
| **Style** | Dart/Flutter style guide; analyzer/linter enforced. |
| **Permissions** | Render only permitted modules/actions; backend authoritative. |
| **Offline** | Read-tolerant caching where specified; writes require connectivity. |
| **Parity** | Match web/reference workflows and the preserved UX. |

---

## 5. API Contract Standards

- Conform to [06-api-architecture.md](06-api-architecture.md): consistent envelope, errors, pagination, filtering, versioning.
- The contract in `shared/contracts/` is the shared truth; web and mobile consume it identically.
- Breaking changes require a new API version and coordinated client updates.

---

## 6. Naming Conventions

| Item | Convention |
|------|-----------|
| **Modules** | Same business name across backend/web/mobile. |
| **Files/classes** | Descriptive, role-clear (e.g., `*Service`, `*Repository`, `*Request`, `*Resource`). |
| **Enums/value lists** | Canonical lists in `shared/enums`; not duplicated. |
| **Booleans/flags** | Affirmative, unambiguous names. |
| **Avoid abbreviations** | Except well-known domain terms (PTM, TC, SLA). |

---

## 7. Testing Standards

| Surface | Expectation |
|---------|-------------|
| **Backend** | Unit (services/repos), feature (controller→DB), workflow, and policy tests, organized by module ([03-backend-architecture.md](03-backend-architecture.md)). |
| **Web** | Component, hook/integration (mocked API), key-flow, accessibility tests. |
| **Mobile** | Unit (domain), widget, integration of key flows. |
| **Coverage focus** | Validated PRD workflows, permission/scope correctness, and money/academic integrity paths. |

Tests are required for new behaviour and must pass in CI before promotion.

---

## 8. Documentation Standards

- Every module carries a short README describing its purpose, services, and events.
- Public service/contract interfaces are documented.
- Architectural decisions that refine (not change) this blueprint are recorded; product changes go to the PRD first.

---

## 9. Version Control & Review

| Practice | Standard |
|----------|----------|
| **Branching** | Short-lived feature branches; no direct commits to the default branch. |
| **Commits** | Clear, scoped messages referencing module/issue. |
| **Reviews** | Mandatory peer review; reviewers check layering, permissions/scope, audit, and UX preservation. |
| **CI gates** | Build + tests + linting + standards must pass before merge/promotion. |
| **Hooks** | Never bypass hooks or quality gates. |

---

## 10. Security Coding Rules

- Validate all input (Form Requests); encode all output.
- Enforce permission + scope server-side on every action; never trust the client.
- Never log secrets, credentials, tokens, or payment-instrument data.
- Use idempotency for payment/communication side effects.
- Store secrets in secure configuration only.

---

## 11. Performance & Reliability Rules

- Push heavy/bulk/scheduled work to jobs ([10-background-jobs.md](10-background-jobs.md)).
- Cache hot, slow-changing, scope-safe data; invalidate via events ([11-caching-strategy.md](11-caching-strategy.md)).
- Degrade gracefully on external-provider failure; never corrupt core data.

---

## 12. The Golden Rules (non-negotiable)

1. **Preserve the reference UX; never copy its code.**
2. **Controller → Request → Service → Repository → Model — always.**
3. **Organize by business module.**
4. **One API for web and mobile; one notification service; one media library.**
5. **Enforce RBAC + scope and audit every material action, server-side.**
6. **Stay single-tenant in V1 but never hard-code single-tenant assumptions.**
7. **No product scope changes here — the PRD is the source of truth.**
