# 03 – Backend Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the Laravel 12 backend
> architecture and the mandatory layering **Controller → Request → Service → Repository → Model**,
> organized by business module. No code, no table schemas, no endpoint design.

---

## 1. Backend Responsibilities

The Laravel 12 backend is the **single API** and the home of **all business logic**. It:

- Authenticates identities and enforces RBAC + data scope.
- Executes the PRD's validated business workflows.
- Persists to MySQL 8 through a repository abstraction.
- Coordinates cross-cutting services (notification, media, audit, number generation, search, import/export).
- Dispatches asynchronous work to queues.
- Serves both the React web app and the Flutter app identically.

---

## 2. Mandatory Layering

```
HTTP Request
   ▼
┌───────────────┐
│  Controller   │  orchestration only — no business rules, no validation logic
└──────┬────────┘
       ▼
┌───────────────┐
│   Request     │  input validation + authorization (form request)
└──────┬────────┘
       ▼
┌───────────────┐
│   Service     │  business rules & workflows (single place for domain logic)
└──────┬────────┘
       ▼
┌───────────────┐
│  Repository   │  data-access abstraction (queries, persistence)
└──────┬────────┘
       ▼
┌───────────────┐
│    Model      │  Eloquent entity (persistence representation)
└───────────────┘
       ▼
     MySQL 8
```

This order is **mandatory** and uniform across every module.

---

## 3. Layer Responsibilities

### 3.1 Controller
- Thin orchestration: receive the validated request, call **one** service method, return a response resource.
- **No** business rules, **no** direct model/DB access, **no** validation logic.
- One controller action ≈ one use case.

### 3.2 Request (Form Request)
- Validates input shape and rules.
- Performs **authorization** (does this user/role/scope may perform this action) via policies/gates.
- Produces clean, typed input for the service.

### 3.3 Service
- The **only** place business rules live — the PRD's validated workflows (admission state machine, exam publish lock, attendance constraints, fee/dues math, promotion, etc.).
- Enforces **data scope** (own/linked/assigned/all) using Core scoping helpers.
- Coordinates repositories, emits domain events, calls Core services, dispatches jobs.
- Transaction boundaries are owned here (atomic multi-step workflows).

### 3.4 Repository
- Abstracts data access behind module-owned interfaces.
- Encapsulates queries and persistence; hides Eloquent/query details from services.
- Enables testability and keeps services persistence-agnostic.

### 3.5 Model
- Eloquent representation of a persistence entity and its relationships.
- No business workflow logic; only entity-level concerns (casts, relations, scopes).
- Concrete schema is defined in the separate **Database Design** phase, not here.

---

## 4. Cross-Cutting (Core) Services

Shared services live in `app/Core` and are injected into module services:

| Core service | Role |
|--------------|------|
| **Auth & Session** | Identity resolution (staff/student/parent), token/session lifecycle. |
| **Permissions** | RBAC checks against the permission matrix. |
| **Scope** | Data-scope resolution (own/linked/assigned/all). |
| **Audit** | Central audit logging of material actions. |
| **Number Generator** | Configurable official numbers/codes. |
| **Notification** | Single dispatcher for Notice/SMS/Email/Push. |
| **Media** | Single media library for all files. |
| **Search** | Global, permission-scoped search. |
| **Import/Export** | Bulk data movement engine. |
| **Response/Envelope** | Consistent API response shape ([06-api-architecture.md](06-api-architecture.md)). |

Modules **never** duplicate these; they depend on the Core abstractions.

---

## 5. Domain Events & Listeners

Side effects and cross-module reactions use events:

```
Service performs primary action
   └─▶ emits Domain Event (e.g., StudentEnrolled, FeeCollected, ExamPublished)
          ├─▶ Listener: write Audit log
          ├─▶ Listener: enqueue Notification (SMS/Email/Push)
          ├─▶ Listener: update Search index
          └─▶ Listener: invalidate Cache
```

Benefits: modules stay decoupled; audit/notification/search/cache are consistent and centralized.
Heavy listeners run on queues ([10-background-jobs.md](10-background-jobs.md)).

---

## 6. Validation & Authorization Strategy

- **Validation** lives in Form Requests (and is reused by import where applicable).
- **Authorization** combines:
  - **Action grant** (permission matrix) via policies/gates.
  - **Data scope** (own/linked/assigned/all) enforced in services.
- Special permanent rules are enforced regardless of configuration (self-delete protection, admission state gating, exam publish lock, attendance lock, teacher/student/parent scoping) per the PRD.

---

## 7. Transactions & Consistency

- Multi-step workflows (enrollment, promotion, multi-month fee payment, bulk import) run inside **service-owned transactions** so they succeed or fail atomically.
- Idempotency is applied where external effects occur (payments, communications) to avoid duplicates on retry.
- Auditing of a workflow records the outcome, not partial states.

---

## 8. Configuration & Tenancy Readiness

- All gateway/channel/storage settings come from configuration (and the Settings module), never hard-coded.
- Core services and repositories are written to be **tenant-aware-ready**: no single-tenant assumption is baked into business logic, so a tenant boundary can be introduced later without redesign (PRD extensibility mandate).

---

## 9. Error Handling

- Services raise meaningful domain errors; controllers translate them to the standard API error envelope ([06-api-architecture.md](06-api-architecture.md)).
- External failures (gateway/SMTP/payment) are caught, logged, and degraded gracefully — they never corrupt the core operation (e.g., a failed credential SMS does not block account creation), consistent with the PRD.

---

## 10. Testing Strategy (backend)

| Test type | Target |
|-----------|--------|
| **Unit** | Services (business rules), repositories (with test doubles), Core services. |
| **Feature/Integration** | Controller→Service→Repository→DB happy paths and permission/scope enforcement. |
| **Workflow** | Cross-module workflows (enrollment, promotion, fee payment, publish). |
| **Policy** | Permission matrix and scope correctness per role. |

Tests are organized **by module** to keep slices self-contained.

---

## 11. What This Document Does Not Do

- Does not define tables, columns, or relationships (Database Design phase).
- Does not define endpoints, routes, or payloads (API Design phase).
- Contains no implementation code.
