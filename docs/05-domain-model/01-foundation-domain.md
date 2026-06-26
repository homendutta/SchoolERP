# 01 – Foundation Domain (Platform)

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Foundation domain is the **platform layer** that every other domain depends on. It owns identity
and access, system configuration, and the shared cross-cutting services — numbering, audit, media,
documents, search, and bulk data movement. It depends on **no other domain** and is depended upon by
all.

---

## 2. Responsibilities

- Maintain **login accounts** for all identities (staff, student, parent, super admin) and their authentication state.
- Define and enforce the **role and permission** model (action grant + data scope).
- Hold **school-wide configuration** (academic year, working days, currency, time zone, security policy, branding, gateway/channel settings).
- Issue **official numbers/codes** through a single configurable number generator.
- Record the **central audit trail** of material actions.
- Provide the **single media library** and **document** metadata/verification.
- Provide **global search** indexing and **import/export** job orchestration.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **User / Account** | A login identity for any person; identified by its login identifiers (staff number / admission number / parent id / mobile / email). | ✓ |
| **Role** | A named set of permissions (default or custom). | ✓ |
| **Permission** | A grantable action on a module (standard or extended). | — (within Role) |
| **Permission Assignment** | A role's grant of an action at a data scope (All/Assigned/Own/Linked). | — (within Role) |
| **Setting / Configuration** | A school-wide configuration value, grouped by Settings section. | ✓ |
| **Branding Asset** | A school visual-identity asset reference (logo, favicon, stamp, etc.). | — (within Settings) |
| **Number Sequence** | A configurable generator definition + counter for a number type (admission, receipt, invoice, complaint, ticket, asset, visitor pass…). | ✓ |
| **Audit Entry** | An append-only record of a material action (actor, action, target, time, context). | ✓ |
| **Media Asset** | A stored file managed by the media library, with visibility and metadata. | ✓ |
| **Document** | Metadata + verification state for a file attached to an entity in another domain (polymorphic). | ✓ |
| **Import/Export Job** | A tracked bulk data-movement operation and its status/result. | ✓ |
| **Search Index Entry** | A conceptual indexed reference enabling global search across entities. | — |

> Natural identities (e.g., "admission number", "receipt number") are stated as **business identity**,
> not as table keys. Schema design is a later phase.

---

## 4. Referenced Entities

The Foundation domain is **referenced by** all domains but itself references almost nothing. The few
outward references are **polymorphic by identity only**:

| Referenced (by identity) | From | Why |
|--------------------------|------|-----|
| Any owning entity (Student, Staff, Asset, Parent…) | their domains | A **Document** or **Media Asset** can be attached to an entity in another domain (polymorphic link by type + identity). |
| Person (Student/Staff/Parent) | Student/Staff domains | A **User/Account** corresponds to a person record owned by another domain. |

Foundation never owns or mutates those external entities; it only references them by identity.

---

## 5. Relationships

Conceptual associations (cardinality in words):

- A **Role** groups many **Permissions**; each **Permission Assignment** binds an action to a data scope within a Role.
- A **User/Account** is assigned **one Role** at a time (a Role may be held by many Users).
- A **User/Account** corresponds to **one Person** (a Student, Staff member, or Parent owned by another domain).
- A **Number Sequence** issues many official numbers; each issued number is **unique within its type**.
- An **Audit Entry** records **one action by one actor**; actors are Users.
- A **Media Asset** may be referenced by **one or many** owning entities; a **Document** attaches to **exactly one** owning entity (polymorphic).
- An **Import/Export Job** targets **one module/domain** and produces a result artifact (a Media Asset).
- **Settings** are singular per school; **Branding Assets** belong to Settings.

---

## 6. Business Boundaries

**Inside the boundary:**
- Authentication state, role/permission definitions, configuration, numbering, audit, media/documents, search index, import/export jobs.

**Outside the boundary (not owned here):**
- The **person records** themselves (Student, Parent, Staff) — owned by Student/Staff domains; Foundation owns only their *login account*.
- The **business meaning** of numbers it issues (e.g., what a receipt represents) — owned by the consuming domain.
- **Communication logs** — owned by the Communication domain (distinct from the Audit trail owned here).
- **Financial account transactions** — owned by Finance (the word "Account" here means a *login account*, not a ledger account).

**Consistency boundaries:**
- Role + its Permission Assignments form one consistency boundary (a role's grants change atomically).
- A Number Sequence guarantees uniqueness within its type as one boundary.
- An Audit Entry is immutable once written (append-only).

---

## 7. Dependency Rules

- **Foundation depends on no other domain.** It must remain free of references to Academic, Student, Staff, Attendance, Finance, Examination, Communication, or Asset internals.
- All other domains **may reference** Foundation entities (User/Account, Role/Permission, Settings, Number Sequence, Audit, Media, Document) by identity.
- Cross-cutting actions (audit writing, number issuing, media storing, document attaching) are invoked **through Foundation services**, never by another domain manipulating Foundation data directly.
- Foundation is **tenant-aware-ready**: identity, configuration, and numbering are modeled so a future tenant/branch boundary can scope them without redesign.
