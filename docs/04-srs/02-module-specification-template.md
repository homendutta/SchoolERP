# 02 – Standard Module Specification Template

**Product:** SchoolERP
**Document type:** SRS — Reusable Module Specification Template
**Status:** Approved (Framework)
**Version:** 1.0
**Last updated:** 2026-06-26

> This is the **mandatory template** for every module specification in the SRS. Every future module
> spec is a copy of the *Template Body* below, fully filled in. Do not add or remove top-level
> sections. Conventions, the requirement-ID scheme, and module codes are defined in
> [00-introduction.md](00-introduction.md); cross-cutting requirements to reference are in
> [01-system-wide-requirements.md](01-system-wide-requirements.md).

---

## How to Use This Template

1. **Copy** the *Template Body* (everything under the `══ TEMPLATE BODY ══` marker) into a new file
   `docs/04-srs/modules/NN-<module-code-lower>.md` (e.g., `03-adm.md` for Admissions).
2. **Replace** every `<…>` placeholder and the *guidance notes* (italic `> note:` lines) with real
   content. Delete the guidance notes once filled.
3. **Use the module's fixed code** (from [00-introduction.md](00-introduction.md) §6.2) in all
   requirement IDs (e.g., `FR-ADM-001`).
4. **Reference, do not restate**, system-wide requirements (e.g., cite `SYS-RBAC-001`).
5. **Trace** every requirement to a PRD / Architecture / Business-Rule source.
6. **Do not** introduce new product scope, design tables/endpoints, or write code.

### Section Checklist (all are mandatory)
Purpose · Scope · Actors · Preconditions · Workflow · Functional Requirements · Validation Rules ·
Business Rules · Permissions · Notifications · Reports · Audit Requirements · UI Preservation Notes ·
Acceptance Criteria · Non-Functional Requirements.

---

══════════════════════ TEMPLATE BODY (copy from here) ══════════════════════

# SRS – <Module Name> Module

**Module code:** `<CODE>`
**Status:** Draft | Reviewed | Approved
**Version:** <x.y>
**Last updated:** <YYYY-MM-DD>
**Traces to PRD:** `<PRD module reference>`  ·  **Architecture:** `<relevant arch docs>`

---

## 1. Purpose
> note: One short paragraph — why this module exists and the value it delivers. Derive from the PRD
> module catalog entry. No features beyond the PRD.

<Purpose statement.>

---

## 2. Scope
**In scope:**
- <capability 1>
- <capability 2>

**Out of scope:**
- <explicitly excluded item / deferred-to-future item>

**Dependencies (modules):** <list of modules this module depends on or collaborates with>

---

## 3. Actors
> note: List the roles/external systems that interact with this module and their relationship.
> Pull roles from the PRD role-permission matrix; reflect data scope (own/linked/assigned/all).

| Actor | Type | Interaction with this module | Data scope |
|-------|------|------------------------------|------------|
| <Administrator> | Role | <e.g., full management> | all |
| <Teacher> | Role | <e.g., own-class actions> | assigned |
| <Student/Parent> | Role | <e.g., view own> | own / linked |
| <Notification Service> | System | <e.g., sends alerts> | n/a |

---

## 4. Preconditions
> note: What must already be true/configured before this module's workflows can run.

- <e.g., Academic year and classes are configured.>
- <e.g., Actor is authenticated and has the required permission + scope.>
- <System-wide preconditions, e.g., SYS-AUTH-001, SYS-RBAC-001 satisfied.>

---

## 5. Workflow
> note: Describe the primary workflow(s) as ordered steps and/or a simple diagram. Preserve the
> validated reference workflow exactly (see business-rule index). Include alternate/exception paths.

**Primary workflow — <name>:**
```
<Step 1>
   ▼
<Step 2>
   ▼
<Step 3> → <outcome>
```

**Alternate / exception paths:**
- <e.g., validation failure → error, no state change.>
- <e.g., permission denied → forbidden.>

**State transitions (if applicable):**
```
<state A> ──<action>──▶ <state B> ──<action>──▶ <state C>
```

---

## 6. Functional Requirements
> note: One testable "shall" statement per row. ID = FR-<CODE>-<NNN>. Cite source/trace. Set priority
> (M/S/C) and verification (T/D/I/A). Decompose with dotted IDs only when necessary.

| ID | Requirement (the system shall…) | Pri | Verify | Source/Trace |
|----|--------------------------------|:--:|:--:|--------------|
| FR-<CODE>-001 | <statement> | M | T | <PRD/Arch/BR ref> |
| FR-<CODE>-002 | <statement> | M | T | <ref> |
| FR-<CODE>-003 | <statement> | S | D | <ref> |

---

## 7. Validation Rules
> note: Field- and input-level rules. ID = VR-<CODE>-<NNN>. These derive from the validated business
> rules and PRD constraints. State the rule and the failure behaviour.

| ID | Validation rule | On failure | Source/Trace |
|----|-----------------|-----------|--------------|
| VR-<CODE>-001 | <e.g., required fields: …> | <reject with message> | <BR ref> |
| VR-<CODE>-002 | <e.g., format/enum/range constraint> | <reject> | <BR ref> |
| VR-<CODE>-003 | <e.g., uniqueness constraint> | <reject> | <BR ref> |

---

## 8. Business Rules
> note: Domain logic and invariants the module enforces (workflows, calculations, locks, state gates).
> ID = BR-<CODE>-<NNN>. Preserve the reference application's validated rules; do not invent new ones.

| ID | Business rule | Source/Trace |
|----|---------------|--------------|
| BR-<CODE>-001 | <e.g., state X may only transition to Y when …> | <BR-Index ref> |
| BR-<CODE>-002 | <e.g., calculation: … computed as …> | <BR-Index ref> |
| BR-<CODE>-003 | <e.g., lock: once …, edits are blocked until …> | <BR-Index ref> |

---

## 9. Permissions
> note: Map this module to the permission matrix. State which actions each role may perform and the
> data scope. Reference SYS-RBAC requirements rather than restating the model.

**Applies:** SYS-RBAC-001..008 (action grant + data scope, server-side).

| Action | Roles permitted (default) | Scope |
|--------|---------------------------|-------|
| View | <roles> | <all/assigned/own/linked> |
| Create | <roles> | <scope> |
| Edit | <roles> | <scope> |
| Delete | <roles> | <scope> |
| Approve | <roles, if applicable> | <scope> |
| Publish / Lock / Unlock | <roles, if applicable> | <scope> |
| Print / Export / Import | <roles> | <scope> |

**Module-specific permission rules:** <e.g., teacher limited to assigned classes; only admin may unlock>.

---

## 10. Notifications
> note: List the communications this module triggers and their channels/templates. Reference SYS-NOT
> requirements and the Notification Service; do not define provider detail.

**Applies:** SYS-NOT-001..007 (central Notification Service, channel toggles, logging).

| Trigger | Channels | Audience | Template/Custom |
|---------|----------|----------|-----------------|
| <event> | <Notice/SMS/Email/Push> | <role/audience> | <template ref> |

> If the module triggers no notifications, state: "None."

---

## 11. Reports
> note: List reports this module produces or contributes data to, with role visibility and
> print/export. Reference the Reports module where the report is rendered.

| Report | Description | Visible to | Print/Export |
|--------|-------------|-----------|--------------|
| <report name> | <what it shows> | <roles/scope> | <yes/no> |

> If the module produces no reports, state: "None — contributes data to <Reports module reports>."

---

## 12. Audit Requirements
> note: Specify which actions in this module are audited. Reference SYS-AUD; list module-specific
> auditable events.

**Applies:** SYS-AUD-001..005 (central audit; searchable/filterable/exportable; no secrets).

| Auditable event | Captured details |
|-----------------|------------------|
| <create/update/delete/approve/publish/lock/...> | actor, action, target, timestamp, context |

---

## 13. UI Preservation Notes
> note: State how this module preserves the reference application's UI/UX for its screens. Reference
> SYS-UI. Identify the reference screens/patterns (sidebar entry, list table, create/edit dialog,
> drill-downs, status pipeline, dashboard cards) that must be reproduced. Code is never copied.

**Applies:** SYS-UI-001..004 (preserve reference UX; never copy code; role-adaptive; shared design system).

- **Navigation/placement:** <sidebar group + position, role visibility>.
- **Primary screens:** <list view (table), create/edit (dialog/form), detail/drill-downs>.
- **Preserved patterns:** <cards, tables, dialogs, status pipeline, badges, charts as in reference>.
- **Workflows preserved:** <name the reference workflows kept identical>.
- **Branding/theming:** applied via the shared design system.

---

## 14. Acceptance Criteria
> note: Verifiable criteria that demonstrate the requirements are met. ID = AC-<CODE>-<NNN>. Each
> criterion references the requirement IDs it verifies. Prefer Given/When/Then phrasing.

| ID | Acceptance criterion | Verifies |
|----|----------------------|----------|
| AC-<CODE>-001 | Given <context>, when <action>, then <observable outcome>. | FR-<CODE>-001 |
| AC-<CODE>-002 | Given <context>, when <invalid input>, then <rejection + message>. | VR-<CODE>-001 |
| AC-<CODE>-003 | Given <role without permission>, when <action>, then <forbidden>. | SYS-RBAC-001/002 |
| AC-<CODE>-004 | Given <material action>, then <audit entry recorded>. | SYS-AUD-001 |

---

## 15. Non-Functional Requirements
> note: Module-specific NFRs only (performance, volume, usability, mobile, accessibility). Reference
> SYS-NFR for the system-wide baseline; add module specifics. ID = NFR-<CODE>-<NNN>.

**Applies:** SYS-NFR-001..008 (system-wide baseline).

| ID | Non-functional requirement | Pri | Verify |
|----|----------------------------|:--:|:--:|
| NFR-<CODE>-001 | <e.g., list view responsive at <N> records> | M | A |
| NFR-<CODE>-002 | <e.g., primary workflow usable on mobile> | M | D |

---

## 16. Traceability Summary
> note: Confirm coverage — every PRD capability for this module maps to ≥1 requirement, and every
> requirement traces to a source. Provide a short matrix.

| Source (PRD/Arch/BR) | Covered by |
|----------------------|-----------|
| <PRD capability> | FR-<CODE>-00x |
| <Business rule> | BR-<CODE>-00x / VR-<CODE>-00x |
| <System-wide req> | referenced (SYS-…) |

---

## 17. Open Questions / Assumptions
> note: Record any clarifications needed (resolved via the PRD, not invented here) and assumptions
> made. Keep empty if none.

- <assumption / open question, or "None.">

══════════════════════ END TEMPLATE BODY ══════════════════════

---

## Template Governance

- The section list is **fixed**. A module spec must contain all sixteen body sections (1–16) plus Open
  Questions (17); none may be removed.
- Requirement IDs are **immutable** once a module spec is Approved (see [00-introduction.md](00-introduction.md) §11).
- Every requirement must carry a **Source/Trace**; every Acceptance Criterion must reference the
  requirement IDs it verifies.
- Module specs **reference** system-wide requirements and never restate them.
- No module spec may introduce new product scope, design database tables or API endpoints, or contain
  implementation code.

---

## Worked ID Example (illustrative only — not a real spec)

> Shows the ID conventions in use for a hypothetical Admissions (`ADM`) module. This is **not** a
> module specification; module specs are written in the next phase.

| Element | Example |
|---------|---------|
| Functional requirement | `FR-ADM-014` — "The system shall transition an admission from *admitted* to *enrolled* only when all enrollment fields are valid." |
| Validation rule | `VR-ADM-003` — "AppliedForClassID must reference an existing, non-deleted class." |
| Business rule | `BR-ADM-002` — "Confirm is permitted only from the *registered* state." |
| Acceptance criterion | `AC-ADM-014` — "Given an *admitted* application with valid data, when Enroll is invoked, then a Student is created and the admission becomes *enrolled*." (verifies FR-ADM-014) |
| Non-functional | `NFR-ADM-001` — "The admissions list shall remain responsive at the school's full applicant volume." |
