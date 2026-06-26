# 00 – Enterprise Domain Model: Overview

**Product:** SchoolERP
**Document type:** Enterprise Domain Model — Overview
**Status:** Approved (Domain)
**Version:** 1.0
**Last updated:** 2026-06-26

> This is the **enterprise domain model**: a conceptual decomposition of the product into business
> domains, the entities each domain owns, and the relationships and rules between them. It is the
> bridge between the SRS and the future MySQL schema. It derives from — and must not change — the
> approved PRD (`docs/00-product/`), Architecture (`docs/03-system-architecture/`), and SRS
> (`docs/04-srs/`).
>
> **This document does NOT design database tables, columns, SQL, or APIs.** Entities, identities, and
> relationships are described **conceptually**. The Database Design phase will transform this model
> into a production schema.

---

## 1. Purpose of the Domain Model

| Goal | Description |
|------|-------------|
| **Single shared language** | Establish one ubiquitous vocabulary for business concepts used across SRS, schema, and code. |
| **Clear ownership** | Assign every business entity to exactly one owning domain. |
| **Explicit relationships** | Describe how entities relate, conceptually (cardinality and meaning), without foreign-key design. |
| **Bounded contexts** | Draw domain boundaries and the rules for crossing them. |
| **Schema-ready** | Produce a clean model the Database Design phase can transform into MySQL tables without re-discovering business meaning. |

---

## 2. Domain Map

The product decomposes into **nine business domains** plus cross-cutting **read models** (Reports,
Dashboards) that own no entities.

```
                          ┌─────────────────────────────────────────┐
                          │        FOUNDATION DOMAIN (platform)       │
                          │ identity · access · config · audit ·     │
                          │ numbering · media · documents            │
                          └───────────────────┬──────────────────────┘
                       depended upon by all   │  depends on none
        ┌───────────────┬───────────────┬─────┴──────┬───────────────┬───────────────┐
        ▼               ▼               ▼            ▼               ▼               ▼
┌──────────────┐ ┌─────────────┐ ┌────────────┐ ┌──────────────┐ ┌─────────────┐ ┌────────────┐
│   ACADEMIC   │ │    STAFF     │ │  STUDENT   │ │  ATTENDANCE  │ │ EXAMINATION │ │  FINANCE   │
│ structure ·  │ │ employees ·  │ │ people ·   │ │ presence ·   │ │ assessment ·│ │ fees ·     │
│ scheduling · │ │ teachers     │ │ admissions·│ │ locking      │ │ marks ·     │ │ dues ·     │
│ teaching     │ │              │ │ behaviour  │ │              │ │ results     │ │ payments   │
└──────────────┘ └─────────────┘ └────────────┘ └──────────────┘ └─────────────┘ └────────────┘
        ▲                                                                              
        │                         ┌─────────────────┐      ┌──────────────────┐        
        └─────────────────────────│ COMMUNICATION    │      │  ASSET DOMAIN     │        
                                  │ notices · msgs · │      │ assets ·          │        
                                  │ gallery · support│      │ maintenance ·     │        
                                  │ · website sync   │      │ inventory         │        
                                  └─────────────────┘      └──────────────────┘        

        Cross-cutting READ MODELS (own no entities): Reports · Dashboards · Sidebar Badges
```

---

## 3. The Nine Domains

| # | Domain | Owns (high level) | Document |
|---|--------|-------------------|----------|
| 01 | **Foundation** | Users/accounts, roles, permissions, settings, number sequences, audit, media, documents, import/export, search index. | [01-foundation-domain.md](01-foundation-domain.md) |
| 02 | **Academic** | Classes, sections, subjects, teacher assignments, periods, timetable, lesson plans, logbook, PTM, substitutes, calendar. | [02-academic-domain.md](02-academic-domain.md) |
| 03 | **Student** | Students, parents, parent–student links, admissions, promotion history, discipline, conduct, activities. | [03-student-domain.md](03-student-domain.md) |
| 04 | **Staff** | Staff/employees, teacher profiles, employment. | [04-staff-domain.md](04-staff-domain.md) |
| 05 | **Attendance** | Attendance records and locks. | [05-attendance-domain.md](05-attendance-domain.md) |
| 06 | **Finance** | Fee structures, dues, payments/receipts, day-book accounts, gateway transactions, refunds. | [06-finance-domain.md](06-finance-domain.md) |
| 07 | **Examination** | Exams, marks, hall tickets, results/marksheets. | [07-examination-domain.md](07-examination-domain.md) |
| 08 | **Communication** | Notices, messages, templates, communication logs, gallery, website sync, complaints, helpdesk. | [08-communication-domain.md](08-communication-domain.md) |
| 09 | **Asset** | Assets, maintenance, inventory items, stock transactions. | [09-asset-domain.md](09-asset-domain.md) |

---

## 4. Ubiquitous Language (core terms)

| Term | Meaning (one definition, used everywhere) |
|------|-------------------------------------------|
| **Academic Year** | The school session period that scopes classes, fees, exams, and timetables. |
| **Class / Section** | An academic grouping (Class) and its sub-group (Section) within an academic year. |
| **Subject** | A taught course belonging to a class. |
| **Teacher Assignment** | The mapping of a teacher to a class+subject for a year. |
| **Student** | An enrolled learner with a unique admission number. |
| **Parent / Guardian** | A person linked to one or more students. |
| **Admission** | An applicant moving through register → confirm → enroll. |
| **Staff / Employee** | A person employed by the school; a Teacher is a staff member with teaching assignments. |
| **Attendance Record** | Presence data for a class on a date (daily or subject/period). |
| **Fee Structure / Due / Payment** | What is owed, the monthly obligation, and money received. |
| **Exam / Mark / Result** | An assessment, a recorded score, and the published outcome. |
| **Notice / Message** | A published announcement and an outbound communication. |
| **Asset / Stock Item** | A fixed asset and a consumable inventory item. |
| **Account** | A login identity (Foundation), distinct from a school financial *Account Transaction* (Finance). |

> Where a word has two meanings (e.g., "Account"), the domain qualifies it. The domain documents use
> the qualified term.

---

## 5. Aggregates & Ownership Principle

- Each domain owns a set of **business entities**; some are **aggregate roots** (the entry point and
  consistency boundary for a cluster of related entities).
- **One owner per entity.** An entity is *owned* by exactly one domain and only *referenced* by others.
- A domain may **reference** entities owned elsewhere (by identity/concept) but must **not own or
  mutate** them; changes happen through the owning domain.
- Cross-domain consistency is achieved through **domain events and services** (per the Architecture
  Blueprint), not by reaching into another domain's data.

---

## 6. Dependency Direction (no cycles)

Dependencies point **toward more foundational domains**. Higher domains depend on lower ones; never the reverse.

```
Foundation         ← depends on nothing (depended on by all)
   ▲
Staff, Academic    ← depend on Foundation
   ▲
Student            ← depends on Foundation, Academic (and references Staff)
   ▲
Attendance         ← depends on Academic, Student, Staff, Foundation
Examination        ← depends on Academic, Student, Staff, Foundation
Finance            ← depends on Student, Academic, Foundation (references Admission)
   ▲
Communication      ← depends on Student, Staff, Academic, Foundation (audience + support)
Asset              ← depends on Foundation (references Staff)
```

**Rule:** A domain may reference only entities in itself or in domains **below** it in this order.
Circular ownership dependencies are prohibited. Read models (Reports/Dashboards) may read across all
domains but own nothing and mutate nothing.

---

## 7. Cross-Cutting Concerns (provided by Foundation)

Every domain relies on Foundation for:

| Concern | Provided by Foundation |
|---------|------------------------|
| **Identity & access** | User/Account, Role, Permission, scope. |
| **Audit** | Append-only audit entries for material actions. |
| **Numbering** | Number sequences for admission numbers, receipts, codes, etc. |
| **Media & documents** | Single media library + polymorphic document metadata/verification. |
| **Configuration** | School settings (academic year, working days, currency, security, branding, gateways). |
| **Search & bulk** | Global search index and import/export jobs. |

These are **referenced** by all other domains and **owned** by Foundation.

---

## 8. Entity-to-Domain Catalog (conceptual)

A consolidated index of major business entities and their **owning** domain. (Conceptual entities, not
tables.)

| Entity (concept) | Owning domain |
|------------------|---------------|
| User / Account, Role, Permission, Permission Assignment | Foundation |
| Setting / Configuration, Branding Asset | Foundation |
| Number Sequence | Foundation |
| Audit Entry | Foundation |
| Media Asset, Document | Foundation |
| Import/Export Job, Search Index | Foundation |
| Class, Section, Subject | Academic |
| Teacher Assignment | Academic |
| School Period, Timetable Entry | Academic |
| Lesson Plan, Logbook Entry | Academic |
| PTM Slot, PTM Booking | Academic |
| Substitute Allocation | Academic |
| Calendar Event | Academic |
| Student, Parent, Parent–Student Link | Student |
| Admission, Promotion Record | Student |
| Discipline Incident, Conduct Evaluation, Activity Record | Student |
| Staff / Employee, Teacher Profile | Staff |
| Attendance Record, Attendance Lock | Attendance |
| Fee Structure, Fee Due, Fee Payment / Receipt | Finance |
| Account Transaction (day-book), Gateway Transaction, Refund | Finance |
| Exam, Mark, Hall Ticket, Result / Marksheet | Examination |
| Notice, Message, Template, Communication Log | Communication |
| Gallery Album / Photo, Video, Website Sync Item | Communication |
| Complaint, Helpdesk Ticket | Communication |
| Asset, Asset Maintenance | Asset |
| Stock Item, Stock Transaction | Asset |

> Future entities (Library, Transport, Hostel, Payroll, Visitor) attach to new or existing domains
> without disturbing this model (PRD extensibility mandate).

---

## 9. Per-Domain Document Structure

Each of the nine domain documents defines, in this order:

1. **Purpose** — why the domain exists.
2. **Responsibilities** — what it is accountable for.
3. **Owned Business Entities** — the entities it owns (conceptual, with natural identity and aggregate roots).
4. **Referenced Entities** — entities from other domains it points to (by identity).
5. **Relationships** — conceptual associations and cardinality within and across the domain.
6. **Business Boundaries** — what is inside vs. outside; what it does not own; consistency boundaries.
7. **Dependency Rules** — which domains it may depend on, and the rules for crossing boundaries.

---

## 10. What This Model Is Not

- It is **not** a database schema — no tables, columns, keys, indexes, or SQL.
- It is **not** an API design — no endpoints or payloads.
- It is **not** implementation — no code.
- It does **not** add product scope — every entity traces to an approved module/business rule.
