# 02 – Module Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines how business modules are
> bounded, structured internally, and how they interact. Modules are **vertical slices** that map
> one-to-one to the PRD module catalog. No code, no schemas, no endpoints.

---

## 1. What a "Module" Is

A **module** is a self-contained business capability (e.g., Admissions, Fees, Attendance). Each module
owns its full vertical stack on each surface:

- **Backend:** Controller → Request → Service → Repository → Model (+ policies, events, jobs, resources).
- **Web:** pages, components, hooks, and a thin API binding.
- **Mobile:** presentation, domain, data.

Modules are organized **by business meaning**, not by technical layer. The same module name is used on
every surface.

---

## 2. Module Categories

| Category | Examples | Notes |
|----------|----------|-------|
| **Foundation modules** | Settings, Role & Permission, Users, Staff | Depended on by everything; configured first. |
| **Master-data modules** | Classes, Sections, Subjects, Teacher Assignments | Define structural reference data. |
| **People modules** | Students, Parents, Admissions | Core records and lifecycle. |
| **Operational modules** | Attendance, Timetable, Lesson Planning, Logbook, PTM, Substitutes | Daily academic operations. |
| **Assessment modules** | Examinations, Marks, Hall Tickets, Conduct, Discipline, Activities | Academic assessment & behaviour. |
| **Finance modules** | Fee Structure, Fee Collection, Fee Dues, Accounts, Payment Gateway | Money lifecycle. |
| **Support & admin modules** | Documents, Calendar, Complaints, Helpdesk, Inventory, Assets, Reports, Dashboard | Operational support. |
| **Communication modules** | Communication, Notice Board, Gallery, Website Sync | Outbound engagement. |
| **Core/platform services** | Notification, Media, Audit, Number Generator, Global Search, Import/Export | Shared, not business modules. |

These correspond directly to [02-module-catalog.md](../00-product/02-module-catalog.md).

---

## 3. Internal Module Structure (canonical template)

Every backend module follows the same internal shape:

```
Modules/<ModuleName>/
├── Http/
│   ├── Controllers/   → orchestration only (no business rules)
│   └── Requests/      → validation + authorization for each action
├── Services/          → business rules & workflows (the PRD's validated rules)
├── Repositories/      → data access abstraction over Models
├── Models/            → persistence entities (designed in DB phase)
├── Policies/          → permission checks for the module's resources
├── Events/ Listeners/ → domain events + reactions (e.g., audit, notify)
├── Jobs/              → async work owned by the module
└── Resources/         → API response transformers (shape only)
```

The corresponding web/mobile feature folders mirror this intent (pages/components/hooks ·
presentation/domain/data). See [01-folder-structure.md](01-folder-structure.md).

---

## 4. Module Boundaries & Communication Rules

1. **Encapsulation** — a module exposes its capabilities through its **Service layer** (and API). Internals (repositories, models) are private to the module.
2. **No cross-module data reaching** — Module A never queries Module B's models directly; it calls B's service or listens to B's events.
3. **Domain events for side effects** — cross-module reactions (e.g., enrollment → create login, issue admission fee) happen via events/listeners, keeping modules decoupled.
4. **Shared concerns go to Core** — anything used by 2+ modules (audit, numbering, notifications, media, search, import/export) lives in Core and is injected.
5. **Stable contracts** — a module's service interface is the contract other modules depend on; internal refactors must not break it.

---

## 5. Cross-Module Workflows (preserved from the PRD)

Some validated workflows span modules. They are coordinated by a **lead module's service** that
invokes other modules' services and emits events — never by inlining another module's logic.

| Workflow | Lead module | Collaborators (via services/events) |
|----------|-------------|-------------------------------------|
| **Admission → Enrollment** | Admissions | Students (create), Users (login), Fee Collection (admission fee), Number Generator, Notification, Audit |
| **Student Promotion (single/bulk)** | Students | Classes/Sections, Subjects (optional), Fee Structure, Number Generator (roll/regen), Audit |
| **Fee Collection / Online Payment** | Fee Collection | Fee Dues, Payment Gateway, Accounts, Number Generator (receipt), Notification, Audit |
| **Result Publish** | Examinations | Marks (lock), Notification, Audit |
| **Notice Publish** | Notice Board | Communication, Website Sync, Notification (SMS/Email/Push), Audit |
| **Account creation** | Students/Parents/Staff | Users (login), Number Generator, Notification, Audit |

> These reflect the **business rules already validated** in
> `docs/01-existing-system-analysis/02-business-rule-index.md` and the PRD. The architecture **preserves**
> the workflows; it does not change them.

---

## 6. Layered Dependency Direction

Within a module, dependencies point **inward/downward** only:

```
Controller ─▶ Request ─▶ Service ─▶ Repository ─▶ Model
     │                      │
     └──────────────────────┴─▶ Core services (audit, notify, number, media, search)
```

- Controllers depend on Services; Services depend on Repositories; Repositories depend on Models.
- No upward dependency (a Service never calls a Controller; a Model never calls a Service).
- Core services are injected, not reached into.

Full layer responsibilities: [03-backend-architecture.md](03-backend-architecture.md).

---

## 7. Module Lifecycle & Extensibility

- **Adding a module** (e.g., future Library, Transport) means adding a new vertical slice following the canonical template — **no core redesign**, consistent with the PRD's extensibility mandate.
- **New roles** unlock module access through the existing Role & Permission model, not by changing module code.
- **New channels/gateways** plug into Core services (Notification, Payment), not into business modules.
- **Multi-branch / multi-tenant** readiness: modules stay tenant-agnostic by relying on Core scoping; no module hard-codes single-tenant assumptions.

---

## 8. Permission & Scope Integration

Every module integrates with the central RBAC model:

- **Requests/Policies** enforce the action grant (View/Create/Edit/Delete/Print/Export/Import/Approve/Publish/Lock/Unlock).
- **Services** enforce **data scope** (own/linked/assigned/all) using Core scoping helpers.
- This dual enforcement reproduces the PRD's permission matrix
  ([03-role-permission-matrix.md](../00-product/03-role-permission-matrix.md)).

Security detail: [07-security-architecture.md](07-security-architecture.md).

---

## 9. Module Catalog → Architecture Mapping (summary)

| PRD Tier | Architecture placement |
|----------|------------------------|
| Tier 1 Core ERP modules | `Modules/*` vertical slices |
| Tier 2 Additional modules (Communication, Gallery, Payment, Logs) | `Modules/*` + Core services |
| Platform & Cross-Cutting (Number Gen, Audit, Search, Import/Export, Media, Notifications) | `Core/*` shared services |
| Tier 3 Future modules | New `Modules/*` slices added later, no redesign |

This guarantees product ↔ architecture traceability for the SRS and all downstream design phases.
