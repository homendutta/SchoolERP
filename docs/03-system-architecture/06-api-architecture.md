# 06 – API Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the architecture and
> **conventions** of the single API consumed by both React and Flutter. This document does **not**
> design endpoints, payloads, or schemas — those belong to the separate **API Design** phase. It sets
> the rules that the endpoint design must follow.

---

## 1. One API, Two Clients

- A **single Laravel 12 API** serves the React web app and the Flutter app **identically**.
- Both clients use the **same contract, auth, authorization, and conventions**.
- The API is the **only** way clients reach business logic; there is no client-side business logic.
- The public website only consumes the outward **sync feed** (notices/gallery), not the full API.

---

## 2. API-First Principles

1. **Contract before code** — the API contract is defined and agreed before implementation.
2. **Resource-oriented** — the API is organized around business resources (modules), not screens.
3. **Consistent everywhere** — uniform conventions for envelopes, errors, pagination, filtering, sorting.
4. **Stateless requests** — each request is authenticated and self-contained.
5. **Versioned** — the API is versioned so clients evolve safely.
6. **Secure by default** — every request is authenticated, permission-checked, and scope-enforced.

---

## 3. Style & Conventions (rules for the API Design phase)

> These are **conventions**, not endpoints. The endpoint catalog is produced later and must conform.

| Concern | Convention |
|---------|-----------|
| **Protocol** | HTTPS only; JSON request/response. |
| **Style** | RESTful, resource-oriented, predictable nouns per module. |
| **Versioning** | Explicit API version segment; backward-compatible evolution. |
| **Response envelope** | Consistent success/error envelope across all modules (e.g., status, data, message, meta). |
| **Errors** | Standard error shape with machine-readable codes + human messages; consistent HTTP status usage. |
| **Pagination** | Uniform pagination for list resources (page/cursor + metadata). |
| **Filtering/Sorting/Search** | Uniform query conventions across list resources. |
| **Idempotency** | Idempotency keys for unsafe operations with external effects (payments, communications). |
| **Rate limiting** | Applied per identity/role to protect the API. |
| **Localization** | Locale-aware formatting (currency, dates) per school settings. |

---

## 4. Request Pipeline

```
Client request (token + payload)
   ▼
HTTPS / Router (versioned)
   ▼
Middleware:
   authenticate → resolve identity (staff/student/parent)
   authorize    → permission (action grant) + data scope
   rate-limit   → per identity/role
   audit-context→ attach actor/context for audit
   ▼
Controller (orchestration) → Request (validation/authorization) → Service (business) → Repository → Model
   ▼
Response Resource → standard envelope
   ▼
Async side effects (events → jobs): notifications, audit, search index, cache invalidation
```

Aligns with [03-backend-architecture.md](03-backend-architecture.md).

---

## 5. Authentication & Authorization (API)

- **Authentication**: token/session-based; the multi-identity login (per PRD) issues a session usable by web and mobile.
- **Authorization** is enforced **server-side** on every request:
  - **Action grant** from the permission matrix (View/Create/Edit/Delete/Print/Export/Import/Approve/Publish/Lock/Unlock).
  - **Data scope** (own/linked/assigned/all).
- Clients may hide unavailable actions for UX, but the **API is authoritative**. Detail: [07-security-architecture.md](07-security-architecture.md).

---

## 6. Resource Organization (by module)

- API resources are grouped **by business module**, mirroring the module catalog and the backend `Modules/*` structure.
- Cross-cutting capabilities (Global Search, Import/Export, Number Generator config, Notifications, Media) are exposed as their own resource groups under Core.
- This keeps the future endpoint catalog traceable to modules and to the PRD.

---

## 7. Consistency for Both Clients

| Aspect | Guarantee |
|--------|-----------|
| **Same payloads** | Web and mobile receive the same resource shapes. |
| **Same auth** | Identical authentication/authorization behaviour. |
| **Same errors** | Identical error envelope and codes. |
| **Same scope** | Identical data-scope results for the same user. |

This guarantees true parity between surfaces and avoids client-specific business logic.

---

## 8. Long-Running & Bulk Operations

- Bulk operations (bulk import/export, bulk promotion, bulk communications) are **accepted** by the API and processed **asynchronously** via jobs, returning a trackable handle ([10-background-jobs.md](10-background-jobs.md)).
- Report generation and exports follow the same async pattern when heavy.

---

## 9. Outward Website Sync Feed

- A **separate, read-only** outward feed exposes only **Public Notices, Photo Gallery, Video Gallery** for the public website/app sync ([04-website-mobile-integration.md](../00-product/04-website-mobile-integration.md)).
- This feed is one-way (ERP → website/app), publishes only items marked public, and never exposes internal data.

---

## 10. Versioning & Compatibility

- The API carries an explicit version; breaking changes require a new version.
- Both clients target a known version; deprecations are communicated and time-boxed.
- The contract is the shared truth in `shared/contracts/` (produced in the API Design phase).

---

## 11. Non-Goals

- No endpoint list, URLs, verbs, or payload schemas here (API Design phase).
- No database design.
- No implementation code.
