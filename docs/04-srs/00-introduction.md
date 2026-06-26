# 00 – SRS Introduction & Framework

**Product:** SchoolERP
**Document type:** Software Requirements Specification — Framework & Conventions
**Status:** Approved (Framework)
**Version:** 1.0
**Last updated:** 2026-06-26

> This document establishes the **framework** for the SchoolERP Software Requirements Specification.
> It defines how every module specification will be written, numbered, and traced. It does **not**
> contain module requirements themselves. It derives entirely from — and must not change — the
> approved **PRD** (`docs/00-product/`, v1.1 Feature Complete) and the approved **System Architecture
> Blueprint** (`docs/03-system-architecture/`).

---

## 1. Purpose of the SRS

The SRS translates the approved product (PRD) and architecture into **precise, verifiable
requirements** that engineering can build and QA can test, module by module. The SRS is the bridge
between *what/why* (PRD) + *how the system is structured* (Architecture) and *exactly what each module
must do* (module specifications, written later using the template in this framework).

| Layer | Source | Answers |
|-------|--------|---------|
| Product | PRD (`docs/00-product/`) | What and why. |
| Architecture | Blueprint (`docs/03-system-architecture/`) | How the system is structured. |
| **SRS (this set)** | **This framework + module specs** | **Exactly what each module must do, verifiably.** |

---

## 2. Scope of the SRS

- **In scope:** functional and non-functional requirements for every module in the PRD module catalog, plus the system-wide requirements that apply across all modules.
- **Out of scope:** product decisions (owned by the PRD), architectural decisions (owned by the Blueprint), database table design, API endpoint design, and implementation code. These are produced in their own phases and referenced — never redefined — by the SRS.

The SRS covers **Version 1** scope only. Future modules are specified later, reusing this same framework, with **no redesign**.

---

## 3. Intended Audience

| Audience | Uses the SRS to… |
|----------|------------------|
| Product owner | Confirm requirements match the PRD. |
| Architects | Confirm requirements respect the Blueprint. |
| Backend / web / mobile engineers | Build to exact, traceable requirements. |
| QA / test engineers | Derive test cases from Functional Requirements and Acceptance Criteria. |
| Database & API designers | Receive the authoritative behaviour their designs must support. |

---

## 4. Reference Documents (authoritative inputs)

| Reference | Role in the SRS |
|-----------|-----------------|
| `docs/00-product/00-product-requirements.md` | Master PRD — product source of truth. |
| `docs/00-product/02-module-catalog.md` | Canonical module list (drives module spec coverage). |
| `docs/00-product/03-role-permission-matrix.md` | Roles, permission actions, scope (drives Permissions sections). |
| `docs/00-product/05-authentication-strategy.md` | Auth/identity rules. |
| `docs/00-product/06-communication-strategy.md` | Notification channels/templates (drives Notifications sections). |
| `docs/00-product/07-payment-strategy.md` | Payment rules. |
| `docs/00-product/08-ui-ux-principles.md` | **UI Preservation Policy** (drives UI Preservation Notes). |
| `docs/01-existing-system-analysis/02-business-rule-index.md` | Validated business rules to be preserved (drives Business Rules sections). |
| `docs/03-system-architecture/*` | Architecture the SRS must conform to (layering, API, security, jobs, etc.). |

Where this framework is silent, the PRD and Blueprint govern, in that order of precedence:
**PRD → Architecture Blueprint → SRS framework → module specification.**

---

## 5. Definitions & Acronyms

| Term | Meaning |
|------|---------|
| **SRS** | Software Requirements Specification. |
| **FR** | Functional Requirement. |
| **NFR** | Non-Functional Requirement. |
| **SYS requirement** | System-wide requirement applying to all modules. |
| **Module** | A business capability from the PRD module catalog. |
| **Actor** | A role or external system that interacts with a module. |
| **RBAC** | Role-Based Access Control (action grant + data scope). |
| **Data scope** | own / linked / assigned / all (per PRD permission model). |
| **Action grant** | View/Create/Edit/Delete/Print/Export/Import/Approve/Publish/Lock/Unlock. |
| **UI Preservation** | Mandatory rule to preserve the reference application's UX. |
| **Audit** | Central, searchable, exportable record of material actions. |
| **MoSCoW** | Must / Should / Could / Won't priority classification. |

Additional domain terms (PTM, TC, SLA, etc.) follow the PRD glossary.

---

## 6. Requirement Identification Scheme

Every requirement has a **unique, stable ID**. IDs are never reused or renumbered once published.

### 6.1 ID Formats

| Requirement type | Format | Example |
|------------------|--------|---------|
| System-wide functional | `SYS-<AREA>-<NNN>` | `SYS-AUTH-001` |
| System-wide non-functional | `SYS-NFR-<NNN>` | `SYS-NFR-007` |
| Module functional | `FR-<MODULE>-<NNN>` | `FR-ADM-014` |
| Module validation rule | `VR-<MODULE>-<NNN>` | `VR-FEE-003` |
| Module business rule | `BR-<MODULE>-<NNN>` | `BR-EXM-002` |
| Module non-functional | `NFR-<MODULE>-<NNN>` | `NFR-ATT-001` |
| Acceptance criterion | `AC-<MODULE>-<NNN>` | `AC-STU-009` |

- `<AREA>` / `<MODULE>` use a short, fixed code (see §6.2).
- `<NNN>` is a zero-padded sequence, unique within its module/area and type.
- Sub-requirements may use a dotted suffix (e.g., `FR-ADM-014.1`) when decomposition is needed.

### 6.2 Module Codes (canonical)

Each PRD module is assigned one fixed code, used in all its requirement IDs. Module specifications must
use these codes:

| Module | Code | Module | Code |
|--------|------|--------|------|
| Authentication | AUTH | Fee Structure | FST |
| Dashboard | DSH | Fee Collection | FEE |
| Admissions | ADM | Fee Dues | DUE |
| Students | STU | Accounts | ACC |
| Parents | PAR | Inventory | INV |
| Teachers | TCH | Assets | AST |
| Staff | STF | Documents | DOC |
| Users | USR | Calendar | CAL |
| Role & Permission | RBAC | Complaints | CMP |
| Classes | CLS | Helpdesk | HLP |
| Sections | SEC | Reports | RPT |
| Subjects | SUB | Settings | SET |
| Teacher Assignments | TAS | Communication | COM |
| Attendance | ATT | Notice Board | NTC |
| Timetable | TMT | Gallery (Photo/Video) | GAL |
| Lesson Planning | LSN | Payment Gateway | PAY |
| Teaching Logbook | LOG | Website Integration | WEB |
| PTM | PTM | Number Generator | NUM |
| Substitutes | SUBST | Audit Logs | AUD |
| Examinations | EXM | Global Search | SRCH |
| Marks | MRK | Import & Export | IMP |
| Hall Tickets | HTK | Branding | BRD |
| Discipline | DSC | Communication Logs | CLG |
| Conduct | CND | — | — |
| Activities | ACT | — | — |

> Codes are fixed. New future modules receive new codes when specified, without altering existing ones.

---

## 7. Requirement Attributes

Each requirement is written with these attributes (the template enforces them):

| Attribute | Meaning |
|-----------|---------|
| **ID** | Unique identifier (per §6). |
| **Statement** | A single, testable "the system shall…" requirement. |
| **Priority** | MoSCoW — Must / Should / Could / Won't (this version). |
| **Source/Trace** | Link to the PRD/Architecture/business-rule item it derives from. |
| **Verification** | How it is verified (see §9). |

Writing rules:
- One requirement = one verifiable statement; avoid compound "and/or" requirements.
- Use **"shall"** for mandatory requirements.
- Be unambiguous, complete, and testable; no implementation detail.

---

## 8. Traceability

The SRS is **fully traceable** in both directions:

```
PRD / Architecture / Business Rule  ──▶  SRS Requirement (FR/VR/BR/NFR)  ──▶  Acceptance Criterion  ──▶  Test
```

- Every module requirement cites its **Source/Trace** to a PRD module, permission-matrix entry,
  business rule, or architecture rule.
- Every **Acceptance Criterion** references the requirement IDs it verifies.
- A traceability matrix (maintained per module and aggregated) ensures **no PRD capability is unspecified** and **no requirement is orphaned**.

---

## 9. Verification Methods

Each requirement declares how it is verified:

| Method | Use |
|--------|-----|
| **Test** | Automated/manual test demonstrates the behaviour. |
| **Demonstration** | Observed in a running workflow. |
| **Inspection** | Reviewed against the spec/standard. |
| **Analysis** | Reasoned/measured (e.g., performance, scope-safety). |

---

## 10. Priority Classification (MoSCoW)

| Priority | Meaning |
|----------|---------|
| **Must** | Required for the version to be acceptable. |
| **Should** | Important but not release-blocking. |
| **Could** | Desirable if capacity allows. |
| **Won't (this version)** | Explicitly deferred (e.g., future-module behaviour). |

Priorities align with the PRD release plan (`docs/00-product/10-release-plan.md`); they do not change product scope.

---

## 11. Requirement Status Lifecycle

```
Draft → Reviewed → Approved → Implemented → Verified
                      │
                      └─▶ Deprecated (only via PRD change, with rationale)
```

- Requirements change **only** when the PRD/Architecture changes (which must happen there first).
- Approved IDs are **never reused**; deprecation is explicit and traceable.

---

## 12. Document Conventions

- **"Shall"** = mandatory; **"should"** = recommended; **"may"** = optional.
- Requirements are presented in tables with their ID, statement, priority, source, and verification.
- Cross-references use relative links to the PRD/Architecture/SRS documents.
- Each module specification is one Markdown file using the standard template
  ([02-module-specification-template.md](02-module-specification-template.md)).
- File naming for module specs: `NN-<module-code-lower>.md` under `docs/04-srs/modules/` (created in the module-spec phase, not now).

---

## 13. How the Framework Is Used (next phase)

1. For each PRD module, create one spec file from the **standard module template**.
2. Fill every template section; assign requirement IDs using the module's code (§6.2).
3. Cite Source/Trace for each requirement; reuse the **system-wide requirements** rather than restating them.
4. Define Acceptance Criteria that reference requirement IDs.
5. Confirm UI Preservation Notes against the reference UX.
6. Review against PRD + Architecture before approval.

> This framework document, the **system-wide requirements**
> ([01-system-wide-requirements.md](01-system-wide-requirements.md)), and the **module template**
> ([02-module-specification-template.md](02-module-specification-template.md)) together constitute the
> complete SRS framework. Module specifications are written in the subsequent phase using these.

---

## 14. Non-Goals of This Framework

- Does not specify any module's requirements (done later, per module).
- Does not design database tables or API endpoints.
- Does not add or change product scope or architecture.
- Contains no implementation code.
