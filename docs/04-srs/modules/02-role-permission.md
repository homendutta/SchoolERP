# SRS – Role & Permission Module

**Module code:** `RBAC`
**Status:** Draft
**Version:** 1.0
**Last updated:** 2026-06-26
**Traces to PRD:** `docs/00-product/03-role-permission-matrix.md` · `docs/00-product/00-product-requirements.md`
**Architecture:** `docs/03-system-architecture/07-security-architecture.md` · `02-module-architecture.md` · `03-backend-architecture.md`
**Related SRS:** `docs/04-srs/01-system-wide-requirements.md` (SYS-RBAC) · `modules/01-authentication.md` (AUTH)

> This specification follows the Standard Module Specification Template
> ([../02-module-specification-template.md](../02-module-specification-template.md)) and **references**
> the system-wide RBAC requirements ([../01-system-wide-requirements.md](../01-system-wide-requirements.md)
> §3) rather than restating them. It preserves the reference application's administrative UI/UX and
> workflow while implementing the approved product behaviour. No API, database, or code design.

---

## 1. Purpose

The Role & Permission module defines and manages the access-control model used by **every** other
module. It provides the default roles, supports unlimited custom roles, defines the grantable
permission actions and their data scopes, exposes the permission matrix for configuration, and is the
authority that all modules consult (server-side) to decide whether an actor may perform an action on a
record. It is the single place where "who can do what, to which data" is configured for the school.

---

## 2. Scope

**In scope:**
- Management of **default roles** (Super Admin, Administrator, Supervisor, Accountant, Clerk, Receptionist, Teacher, Student, Parent).
- Creation and management of **unlimited custom roles**.
- Definition and assignment of **permissions** (standard + extended/sensitive actions) per module.
- Assignment of a **data scope** (All / Assigned / Own / Linked) to each granted permission.
- The **Permission Matrix** (role × module × action × scope) and **Permission Groups** for manageable configuration.
- Assignment of roles to users.
- **Server-side enforcement** of action grants and data scope on every request.
- Audit of all role and permission changes.

**Out of scope (owned elsewhere, referenced here):**
- User accounts and authentication — owned by **Users / Authentication** (this module supplies the role/permission context consumed at login, AUTH FR-AUTH-010).
- The business effect of each permission (e.g., the actual refund) — owned by the respective module; this module only governs *whether* the action is permitted.
- Future role federation across tenants/branches (out of Version 1 scope; architecture remains ready).

**Dependencies (modules):** Users / Authentication (consumes role/permission context); all functional modules (consume permission checks); Audit Logs; Settings.

---

## 3. Actors

| Actor | Type | Interaction with this module | Data scope |
|-------|------|------------------------------|------------|
| Super Admin | Role | Manages roles/permissions at system level; protects system roles. | system |
| Administrator | Role | Manages school roles, custom roles, permission assignments, and user-role assignment. | all |
| All other roles | Role | Subject to the permissions defined here; no management access by default. | per role |
| Authentication / Users | System | Resolve the effective permission set for a session at login. | n/a |
| All functional modules | System | Enforce action grant + scope by consulting this model server-side. | n/a |
| Audit Logs | System | Receive role/permission change events. | n/a |

---

## 4. Preconditions

- The installation is initialized with the default roles seeded (FR-RBAC-001).
- The actor managing roles/permissions is authenticated and holds the management permission (Administrator or Super Admin) — references AUTH FR-AUTH-010, SYS-RBAC-001.
- The module catalog and permission action vocabulary are available to build the matrix (this spec, §8.2).
- System-wide RBAC baseline is in force: SYS-RBAC-001..008.

---

## 5. Workflows

### 5.1 Role Management Workflow
```
Authorized admin opens Role & Permission (Administration)
   ▼
Create / Clone / Edit / (De)activate / Delete a role
   • Create custom role → starts with NO permissions (least privilege)
   • Clone role → copies an existing role's permission set as a starting point
   • Edit role → adjust name/description/status
   • Delete role → blocked if it is a protected default role or is assigned to users
   ▼
Save → validate (unique name, protections) → persist
   ▼
Audit "role created/updated/deleted"
```

### 5.2 Permission Assignment Workflow
```
Authorized admin opens a role's Permission Matrix
   ▼
For each module (grouped) toggle the permitted actions
   • Standard actions: View, Create, Edit, Delete, Import, Export, Print, Approve, Publish, Lock, Unlock
   • Extended actions: Reset Password, Generate Login, Promote, Transfer, Issue TC, Refund, Cancel Receipt
   ▼
For each granted action, set the Data Scope (All / Assigned / Own / Linked)
   • Student/Parent scope is capped (Own / Linked) and non-configurable upward
   • Teacher write actions are capped to Assigned
   ▼
Save → validate (scope allowed, action applicable to module) → persist
   ▼
Audit "permission granted/revoked / scope changed"
   ▼
New permission set applies to subsequent requests/sessions
```

### 5.3 Runtime Enforcement Flow (consumed by all modules)
```
Authenticated request to perform <action> on <module record>
   ▼
Server resolves the actor's effective permissions (from assigned role)
   ▼
Check 1 — Action grant?  (does the role permit <action> on <module>?)
Check 2 — Data scope?    (is the target within All/Assigned/Own/Linked for this actor?)
   ▼
Allow only if BOTH pass; else deny (forbidden) and audit the denial context
```

---

## 6. Functional Requirements

| ID | Requirement (the system shall…) | Pri | Verify | Source/Trace |
|----|--------------------------------|:--:|:--:|--------------|
| FR-RBAC-001 | Provide the default roles: Super Admin, Administrator, Supervisor, Accountant, Clerk, Receptionist, Teacher, Student, Parent. | M | T | SYS-RBAC-004; PRD 03 |
| FR-RBAC-002 | Allow authorized administrators to create unlimited custom roles. | M | T | SYS-RBAC-004; PRD 03 |
| FR-RBAC-003 | Allow editing a role's name, description, and status. | M | T | PRD 03 |
| FR-RBAC-004 | Allow cloning an existing role's permission set as the starting point for a new role. | S | T | PRD 03 |
| FR-RBAC-005 | Initialize every newly created custom role with no permissions (least privilege). | M | T | SYS-RBAC-005 |
| FR-RBAC-006 | Allow deleting a custom role only when it is not assigned to any user and is not a protected default/system role. | M | T | PRD 03 |
| FR-RBAC-007 | Protect default/system roles (especially Super Admin and Administrator) from deletion and from disabling their core management capability. | M | T | PRD 03 |
| FR-RBAC-008 | Allow assigning a role to a user and changing a user's role. | M | T | PRD 03; AUTH |
| FR-RBAC-009 | Support the standard permission actions per module: View, Create, Edit, Delete, Import, Export, Print, Approve, Publish, Lock, Unlock. | M | T | SYS-RBAC-001; PRD 03 |
| FR-RBAC-010 | Support the extended/sensitive permission actions: Reset Password, Generate Login, Promote, Transfer, Issue TC, Refund, Cancel Receipt. | M | T | PRD 02/03/05; task |
| FR-RBAC-011 | Require every granted permission to carry a data scope of All, Assigned, Own, or Linked. | M | T | SYS-RBAC-002; PRD 03 |
| FR-RBAC-012 | Present permissions organized into Permission Groups (by module group and by action category) for manageable configuration. | M | D | PRD 03 §matrix |
| FR-RBAC-013 | Provide a Permission Matrix view to grant/revoke actions and set scope per role across modules. | M | D | PRD 03 |
| FR-RBAC-014 | Enforce both the action grant and the data scope on every request, server-side, treating client-side gating as advisory only. | M | T | SYS-RBAC-001/002/003 |
| FR-RBAC-015 | Cap Student permissions to Own scope and Parent permissions to Linked scope, non-configurably. | M | T | SYS-RBAC-006 |
| FR-RBAC-016 | Cap Teacher write actions to Assigned scope. | M | T | SYS-RBAC-007 |
| FR-RBAC-017 | Resolve and expose the effective permission set for a session so clients can render role-adaptive menus and actions (enforcement remaining server-side). | M | T | PRD 04; SYS-UI-003 |
| FR-RBAC-018 | Apply only actions that are applicable to a given module (per the action-applicability model) when configuring permissions. | M | T | PRD 03 §5 |
| FR-RBAC-019 | Make permission changes take effect for subsequent requests/sessions. | M | T | Arch 07 |
| FR-RBAC-020 | Audit every role create/update/delete, permission grant/revoke, scope change, and user-role assignment. | M | T | SYS-RBAC-008; SYS-AUD-001 |
| FR-RBAC-021 | Reserve the Super Admin as a system-level role above the school Administrator, able to govern role configuration. | M | T | PRD 05; SYS-AUTH-007 |
| FR-RBAC-022 | Allow only the Super Admin and the Administrator (with the management permission) to manage roles and permissions. | M | T | PRD 03 |
| FR-RBAC-023 | Provide an exportable/printable view of the permission matrix and of user-role assignments. | S | T | PRD 03; SYS-IMP-001 |
| FR-RBAC-024 | Keep the role/permission model branch-scopable-ready so future multi-branch/multi-tenant scoping can be added without redesign. | S | A | SYS-DEP-003 |
| FR-RBAC-025 | Deny and audit any action that fails the action grant or the data-scope check. | M | T | SYS-RBAC-003; SYS-AUD-001 |

---

## 7. Validation Rules

| ID | Validation rule | On failure | Source/Trace |
|----|-----------------|-----------|--------------|
| VR-RBAC-001 | A role name is required and must be unique within the school. | Reject with message; no save. | PRD 03 |
| VR-RBAC-002 | A default/system role may not be deleted. | Reject; deletion blocked. | FR-RBAC-007 |
| VR-RBAC-003 | A role assigned to one or more users may not be deleted until reassigned. | Reject; require reassignment. | FR-RBAC-006 |
| VR-RBAC-004 | A granted permission's scope must be exactly one of All, Assigned, Own, Linked. | Reject invalid scope. | SYS-RBAC-002 |
| VR-RBAC-005 | A Student role's scope may not exceed Own; a Parent role's scope may not exceed Linked. | Reject upward escalation. | SYS-RBAC-006 |
| VR-RBAC-006 | A Teacher write action's scope may not exceed Assigned. | Reject upward escalation. | SYS-RBAC-007 |
| VR-RBAC-007 | A permission action must be applicable to the target module (per the action-applicability model). | Reject inapplicable grant. | PRD 03 §5 |
| VR-RBAC-008 | The Super Admin's core management capability cannot be removed. | Reject; protected. | FR-RBAC-007 |
| VR-RBAC-009 | A user must be assigned exactly one role at a time (a valid existing role). | Reject invalid/empty assignment. | PRD 03 |

---

## 8. Business Rules

### 8.1 Business Rule Table

| ID | Business rule | Source/Trace |
|----|---------------|--------------|
| BR-RBAC-001 | The nine default roles are seeded at installation and always exist. | SYS-RBAC-004; PRD 03 |
| BR-RBAC-002 | Custom roles start with zero permissions; access is only ever granted explicitly (least privilege). | SYS-RBAC-005 |
| BR-RBAC-003 | Authorization is two-dimensional: an action is allowed only when both the action grant and the data scope permit it. | SYS-RBAC-001/002 |
| BR-RBAC-004 | Enforcement is server-side and authoritative; any client-side hiding of menus/actions is convenience only. | SYS-RBAC-003 |
| BR-RBAC-005 | Permanent guardrails apply regardless of configuration: Student = Own, Parent = Linked (non-configurable), Teacher write = Assigned, and self-account deletion is blocked. | SYS-RBAC-006/007; SYS-SEC-008; AUTH |
| BR-RBAC-006 | Extended/sensitive actions (Reset Password, Generate Login, Promote, Transfer, Issue TC, Refund, Cancel Receipt) are separately grantable and are required, in addition to any base action, before the owning module performs the operation. | PRD 02/03/05; task |
| BR-RBAC-007 | The Super Admin is above the school Administrator and governs system-level role configuration; only Super Admin and Administrator may manage roles/permissions. | PRD 05; FR-RBAC-022 |
| BR-RBAC-008 | Permission changes apply to subsequent requests/sessions, not retroactively to completed actions. | Arch 07 |
| BR-RBAC-009 | Every role/permission/assignment change is audited. | SYS-RBAC-008 |
| BR-RBAC-010 | The same permission model governs the web app and the Flutter app identically. | SYS-API-001; SYS-AUTH-008 |

### 8.2 Permission Action Catalog

Two classes of grantable actions exist. Applicability per module follows the PRD action-applicability
model (PRD 03 §5); not every action applies to every module.

**Standard actions**

| Action | Meaning |
|--------|---------|
| View | Read/list records. |
| Create | Add records. |
| Edit | Modify records. |
| Delete | Remove records (soft/hard per module policy). |
| Import | Bulk-load data into the module. |
| Export | Export data out of the module. |
| Print | Produce printable output. |
| Approve | Authorize a record/workflow step. |
| Publish | Make content visible downstream. |
| Lock | Freeze records against edits. |
| Unlock | Release a lock. |

**Extended / sensitive actions** (gate specific high-impact operations of the owning modules)

| Action | Owning module(s) | Gated operation |
|--------|------------------|-----------------|
| Reset Password | Authentication / Users | Reset another user's password (AUTH FR-AUTH-019). |
| Generate Login | Authentication / Users | (Re)provision a login account (AUTH FR-AUTH-020). |
| Promote | Students | Promote student(s) to the next class/year. |
| Transfer | Students | Section/class transfer of a student. |
| Issue TC | Students | Issue a Transfer Certificate (lifecycle exit). |
| Refund | Fee Collection | Issue a fee refund. |
| Cancel Receipt | Fee Collection | Cancel/void a fee receipt. |

> These extended actions are **permissions for existing PRD behaviours**; they add no new product
> features. The owning module performs the operation only when the actor holds the corresponding
> extended permission (BR-RBAC-006).

### 8.3 Permission Groups

Permissions are organized into groups so the matrix is manageable and can be reasoned about and bulk-toggled.

**By module group** (mirrors the reference sidebar grouping):

| Group | Modules (examples) |
|-------|--------------------|
| Overview | Dashboard, Reports |
| Daily | Attendance, Timetable, Notices, Calendar, Lesson Planning, Logbook, PTM, Substitutes |
| Academic | Examinations, Marks, Hall Tickets, Conduct, Discipline, Activities |
| Records | Admissions, Students, Parents, Classes, Sections, Subjects, Teacher Assignments, Assets, Inventory |
| Finance | Fee Structure, Fee Collection, Fee Dues, Accounts, Payment Gateway |
| Support | Complaints, Helpdesk, Documents |
| Administration | Users, Staff, Role & Permission, Settings |
| Platform / System | Communication, Gallery, Number Generator, Audit Logs, Global Search, Import/Export, Branding |

**By action category:** Standard actions vs. Extended/sensitive actions (§8.2).

A Permission Group is a configuration/management convenience; enforcement is always evaluated at the
individual action + scope level.

### 8.4 Data Scope Rules

Every granted permission carries exactly one data scope. Scope is enforced **in addition** to the
action grant (SYS-RBAC-002).

| Scope | Definition | Typical roles |
|-------|------------|---------------|
| **All** | Every record in the module across the school. | Administrator; Accountant (finance); Clerk (records); Supervisor (academic) |
| **Assigned** | Records tied to the actor's assignments (e.g., a teacher's classes/subjects). | Teacher |
| **Own** | The actor's own records only. | Student |
| **Linked** | Records of entities linked to the actor (e.g., a parent's children). | Parent |

**Scope rules:**
- Student is permanently capped to **Own**; Parent to **Linked** (VR-RBAC-005, non-configurable upward).
- Teacher write actions are capped to **Assigned** (VR-RBAC-006).
- The **Super Admin** operates at a system level (installation configuration), distinct from the four content scopes.
- Scope is **tenant/branch-scopable-ready** for future multi-branch/SaaS without redesign (FR-RBAC-024, SYS-DEP-003).

### 8.5 Permission Matrix

- The **Permission Matrix** is the authoritative configuration of `role × module × action × scope`. The default grants are defined by the PRD ([../../00-product/03-role-permission-matrix.md](../../00-product/03-role-permission-matrix.md)); this module **manages** that matrix and any custom-role rows.
- The matrix is presented grouped by Permission Group (§8.3); each cell expresses whether an action is granted and at what scope.
- Default-role rows reflect the approved PRD matrix; Administrators may add custom-role rows and adjust grants within the permanent guardrails (BR-RBAC-005).
- The matrix is exportable/printable (FR-RBAC-023).

---

## 9. Permissions

**Applies:** SYS-RBAC-001..008 (this module both *defines* and is *governed by* the access model).

| Action | Roles permitted (default) | Scope |
|--------|---------------------------|-------|
| View role/permission configuration | Administrator, Super Admin | all / system |
| Create / Edit / Clone role | Administrator, Super Admin | all / system |
| Delete role | Administrator, Super Admin | all / system (protected roles excluded) |
| Grant / Revoke permission · Set scope | Administrator, Super Admin | all / system |
| Assign role to user | Administrator, Super Admin | all / system |
| Export / Print matrix & assignments | Administrator, Super Admin | all / system |
| Any management action | All other roles | none (unless explicitly granted) |

**Module-specific permission rules:**
- No role may grant itself or others a capability that breaches a permanent guardrail (BR-RBAC-005).
- The Super Admin's management capability cannot be revoked (VR-RBAC-008).
- Managing roles/permissions requires the dedicated management permission; it is not implied by any other module access.

---

## 10. Notifications

**Applies:** SYS-NOT-001..007 (central Notification Service) where used.

| Trigger | Channels | Audience | Template/Custom |
|---------|----------|----------|-----------------|
| User's role changed (optional) | Email/Push (if enabled) | The affected user | Role-change template |

> End-user notifications are **not required** by this module. All role/permission changes are **audited**
> (§12). An optional role-change notice MAY be enabled but is not core behaviour.

---

## 11. Reports

| Report | Description | Visible to | Print/Export |
|--------|-------------|-----------|--------------|
| Permission Matrix export | The full role × module × action × scope grid. | Administrator, Super Admin | Yes |
| User–Role Assignments | Which role each user holds. | Administrator, Super Admin | Yes |
| Role/Permission Change Log | Audit of role/permission/assignment changes. | Administrator, Super Admin | Via Audit Logs / Reports |

Rendering/export is provided through the **Reports** and **Audit Logs** capabilities (SYS-AUD-003, SYS-IMP-001).

---

## 12. Audit Requirements

**Applies:** SYS-AUD-001..005 and SYS-RBAC-008 (role/permission changes are always audited).

| Auditable event | Captured details |
|-----------------|------------------|
| Role created / updated / deleted | actor, role, change, timestamp |
| Permission granted / revoked | actor, role, module, action, timestamp |
| Data scope changed | actor, role, module, action, old→new scope, timestamp |
| Role assigned / changed for a user | actor, subject user, old→new role, timestamp |
| Denied action (enforcement) | actor, attempted action, module, reason (grant/scope), timestamp |

- No secrets are recorded (SYS-AUD-005).

---

## 13. UI Preservation Notes

**Applies:** SYS-UI-001..004 (preserve reference UX; never copy code; role-adaptive; shared design system).

The Role & Permission screens preserve the reference application's administrative look-and-feel and
navigation while delivering the matrix capability; **no reference code is copied**.

- **Navigation/placement:** under the **Administration** group of the sidebar, consistent with the reference grouping and per-role visibility (visible only to Administrator/Super Admin).
- **Primary screens:** a **roles list** (reference-style data table with row actions) and a **role editor** that presents the **Permission Matrix** as a grouped grid of toggles with a per-action scope selector — built from the shared design system's tables, cards, dialogs, and form controls.
- **Preserved patterns:** the reference's tables, dialogs/modals for create/edit, confirmation prompts for destructive actions, toasts for feedback, and grouped sidebar navigation.
- **Role-adaptive outcome:** the configured permissions drive the same role-adaptive sidebar/menu behaviour users already experience in the reference app (SYS-UI-003).
- **Branding/theming:** applied via the shared design system.

> This module is more capable than the reference (which expressed roles implicitly through role-based
> menus); the **design language, navigation flow, and interaction patterns** of the reference are
> preserved while the matrix configuration UI is added within those patterns.

---

## 14. Acceptance Criteria

| ID | Acceptance criterion | Verifies |
|----|----------------------|----------|
| AC-RBAC-001 | Given a fresh installation, then the nine default roles exist. | FR-RBAC-001, BR-RBAC-001 |
| AC-RBAC-002 | Given an authorized admin, when they create a custom role, then it is created with no permissions. | FR-RBAC-002, FR-RBAC-005 |
| AC-RBAC-003 | Given a default/system role, when deletion is attempted, then it is blocked. | FR-RBAC-007, VR-RBAC-002 |
| AC-RBAC-004 | Given a role assigned to users, when deletion is attempted, then it is blocked until reassignment. | FR-RBAC-006, VR-RBAC-003 |
| AC-RBAC-005 | Given a role, when an admin grants an action and sets its scope, then the grant and scope persist and are audited. | FR-RBAC-009/011/020 |
| AC-RBAC-006 | Given the extended actions, when configuring a role, then Reset Password, Generate Login, Promote, Transfer, Issue TC, Refund, and Cancel Receipt are each independently grantable. | FR-RBAC-010 |
| AC-RBAC-007 | Given a user without an extended permission, when they attempt the gated operation (e.g., Refund), then the owning module refuses it. | FR-RBAC-010, BR-RBAC-006 |
| AC-RBAC-008 | Given a Student or Parent role, when an admin attempts to set a scope above Own/Linked, then the change is rejected. | FR-RBAC-015, VR-RBAC-005 |
| AC-RBAC-009 | Given a Teacher write action, when an admin attempts a scope above Assigned, then the change is rejected. | FR-RBAC-016, VR-RBAC-006 |
| AC-RBAC-010 | Given any request, when the action grant or data scope fails, then the action is denied server-side and the denial is audited, regardless of client UI. | FR-RBAC-014, FR-RBAC-025, BR-RBAC-004 |
| AC-RBAC-011 | Given a permission change, when the user next acts, then the new permissions apply. | FR-RBAC-019, BR-RBAC-008 |
| AC-RBAC-012 | Given a logged-in user, then the effective permissions drive a role-adaptive menu identical in behaviour to the reference app. | FR-RBAC-017, SYS-UI-003 |
| AC-RBAC-013 | Given web and Flutter clients, when the same user acts, then identical permission outcomes occur. | BR-RBAC-010, SYS-API-001 |
| AC-RBAC-014 | Given an admin, when they export the permission matrix, then a complete role × module × action × scope export is produced. | FR-RBAC-023 |
| AC-RBAC-015 | Given any role/permission/assignment change, then a corresponding audit entry is recorded. | FR-RBAC-020, SYS-AUD-001 |
| AC-RBAC-016 | Given the Super Admin role, when an admin attempts to remove its management capability, then it is rejected. | FR-RBAC-007, VR-RBAC-008 |

---

## 15. Non-Functional Requirements

**Applies:** SYS-NFR-001..008 and SYS-SEC-008 (permanent guards) as the baseline.

| ID | Non-functional requirement | Pri | Verify |
|----|----------------------------|:--:|:--:|
| NFR-RBAC-001 | Permission resolution and enforcement shall add negligible latency to requests under the school's normal load. | M | A |
| NFR-RBAC-002 | The Permission Matrix screen shall remain usable with all default and custom roles and the full module/action set. | M | A |
| NFR-RBAC-003 | The permission model shall be maintainable and extensible so new modules/actions and future roles can be added without redesign. | M | I |
| NFR-RBAC-004 | The role/permission configuration shall be usable on a phone for review/management (mobile-first). | S | D |
| NFR-RBAC-005 | Permission resolution shall use scope-safe caching so a cached decision never crosses a user's scope. | M | T |

---

## 16. Traceability Summary

| Source (PRD / Arch / SYS / BR) | Covered by |
|--------------------------------|-----------|
| PRD 03 — Default roles (9) | FR-RBAC-001 / BR-RBAC-001 |
| PRD 03 — Unlimited custom roles | FR-RBAC-002 / FR-RBAC-004 |
| PRD 03 — Standard permission actions (11) | FR-RBAC-009 / §8.2 |
| Task — Extended actions (Reset Password … Cancel Receipt) | FR-RBAC-010 / §8.2 / BR-RBAC-006 |
| PRD 03 / SYS-RBAC-002 — Data scope (All/Assigned/Own/Linked) | FR-RBAC-011 / §8.4 / VR-RBAC-004..006 |
| PRD 03 — Permission Matrix | FR-RBAC-013 / §8.5 |
| PRD 03 §matrix — Permission Groups | FR-RBAC-012 / §8.3 |
| SYS-RBAC-003 — Server-side enforcement | FR-RBAC-014 / FR-RBAC-025 / BR-RBAC-004 |
| SYS-RBAC-005 — Least privilege | FR-RBAC-005 / BR-RBAC-002 |
| SYS-RBAC-006/007 — Scope caps | FR-RBAC-015/016 / VR-RBAC-005/006 / BR-RBAC-005 |
| SYS-RBAC-008 / SYS-AUD — Change audit | FR-RBAC-020 / §12 |
| PRD 05 / SYS-AUTH-007 — Super Admin authority | FR-RBAC-021/022 / BR-RBAC-007 |
| SYS-API-001 / SYS-AUTH-008 — Web+mobile parity | BR-RBAC-010 / AC-RBAC-013 |
| SYS-DEP-003 — Branch/tenant readiness | FR-RBAC-024 / §8.4 |
| PRD 08 / SYS-UI-001..004 — UI preservation | §13 |
| SYS-NFR / SYS-SEC-008 — Non-functional & guards | NFR-RBAC-001..005 / BR-RBAC-005 |

> Coverage check: every item the task requires — the 9 default roles, unlimited custom roles, the 11
> standard actions, the 7 extended actions, the 4 data scopes, mandatory server-side enforcement, the
> Permission Matrix, Permission Groups, and Data Scope Rules — maps to at least one requirement above,
> and every requirement traces to a PRD/Architecture/SYS source.

---

## 17. Open Questions / Assumptions

- **One role per user (assumption):** A user is assigned exactly one role at a time (VR-RBAC-009), consistent with the PRD matrix and the reference application. Multi-role composition is **not** introduced here (no new feature); if ever needed it is a PRD-level decision.
- **Extended-action granularity (assumption):** The seven extended actions are modeled as distinct permissions checked by their owning modules; their *business behaviour* is specified in those modules' SRS, not here.
- **Default custom-role scope defaults:** New custom roles start empty; any scope is chosen explicitly at grant time within the guardrails.
- **System-level scope:** "System" (Super Admin) is treated as distinct from the four content scopes; the four required scopes (All/Assigned/Own/Linked) govern school content.
- No open question affects approved product scope or introduces new features.
