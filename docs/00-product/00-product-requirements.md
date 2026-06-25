# Product Requirements Document (PRD)

**Product:** School Management ERP (working name: *SchoolERP*)
**Document type:** Master Product Requirements Document
**Status:** Approved — Single Source of Truth
**Version:** 1.1 (Feature Complete)
**Last updated:** 2026-06-26

> This PRD is the **single source of truth** for the SchoolERP product. Every downstream
> document — SRS, Database Design, ER Diagram, API Design, React Architecture, Laravel
> Architecture, Flutter Architecture — and all implementation work must conform to this PRD and
> the companion documents listed in §13. Where a downstream document needs to deviate, the
> deviation must first be reflected here.

---

## 1. Executive Summary

SchoolERP is a modern, commercial-grade School Management ERP for K-12 institutions. It unifies
the day-to-day operations of a school — admissions, academics, attendance, examinations, fees and
accounts, communication, and administration — into one secure, role-driven, API-first platform
delivered through a web application and a single cross-role Flutter mobile app, tightly integrated
with the school's existing public website.

The product is a **ground-up modern rebuild** of a proven Google Apps Script application that is
already in production use. The business workflows of that reference application are well validated
and must be **preserved**; its implementation must **not** be reused. The new product is built on a
modern, modular, scalable architecture (React + Vite + Tailwind front end, Laravel 12 API back end,
MySQL database, Flutter mobile) while keeping the familiar user experience that schools already
know.

Version 1 ships as a **single-tenant** deployment — one installation, one database, one domain per
school — but the architecture is deliberately designed so that multi-school SaaS and multi-branch
capabilities can be added later without a redesign.

---

## 2. Goals & Objectives

### 2.1 Product Goals
1. Deliver a complete, commercial-quality School ERP that covers the full operational lifecycle of a school.
2. Preserve the validated business workflows of the reference application while modernizing architecture, security, and UX.
3. Provide a unified experience across web and a single multi-role mobile app.
4. Integrate seamlessly with the school's existing public website as one ecosystem (no separate ERP domain, no website CMS).
5. Make the product modular and extensible so future modules and tenancy models can be added incrementally.

### 2.2 Business Objectives
- Sell and deploy one ERP installation per school with low operational overhead.
- Reduce manual administrative effort for schools (admissions, fees, attendance, communication).
- Increase parent/student engagement through mobile, notices, and online payments.
- Establish a defensible architecture that supports a future SaaS offering.

### 2.3 Measurable Success Criteria
| Objective | Indicative Metric (per school) |
|-----------|-------------------------------|
| Operational adoption | ≥ 80% of staff active weekly within 60 days of go-live |
| Parent engagement | ≥ 60% of parents activated on the mobile app within 90 days |
| Fee digitization | ≥ 40% of fee collection via online payment within 2 terms |
| Communication reach | ≥ 95% successful delivery rate on enabled SMS/Email channels |
| Reliability | ≥ 99.5% application availability during school hours |
| Data integrity | Zero unaudited changes to financial/academic records |

---

## 3. Target Customers & Market

The product targets K-12 schools across boards and segments:

- Private Schools
- Public Schools
- CBSE Schools
- ICSE / ISC Schools
- State Board Schools
- International Schools (IB, Cambridge IGCSE/A-Level)

The reference application already models multi-board concepts (curriculum stages such as PYP/MYP/DP,
IGCSE, A-Level; grading schemes such as percentage, CGPA, IB 7-point, Cambridge A–G; medium of
instruction; international student fields). These board-agnostic capabilities are **in scope** and
carried forward.

See [01-product-vision.md](01-product-vision.md) for personas and value proposition.

---

## 4. Deployment Model

### 4.1 Version 1 (current scope)
- **One ERP installation per school.**
- **One database per school.**
- **One domain per school** (the school's existing public website domain).
- **No** multi-school SaaS.
- **No** multi-branch support.

### 4.2 Future Extensibility (non-negotiable design constraint)
The architecture must remain extensible so that **multi-school SaaS** and **multi-branch** support
can be added later **without redesigning the system**. This is a forward-looking constraint on
architecture decisions, not a Version 1 feature. Examples of what this implies at the product level:
tenant-aware data boundaries, configuration over hard-coding, role/permission model that can scope
to a branch, and a licensing layer owned by the Super Admin.

---

## 5. Scope

### 5.1 In Scope (Version 1)
- All **Core ERP Modules** and **Additional Modules** listed in [02-module-catalog.md](02-module-catalog.md).
- Role & Permission system with default roles, custom roles, and a full permission matrix ([03-role-permission-matrix.md](03-role-permission-matrix.md)).
- Multi-identity authentication and automatic account generation ([05-authentication-strategy.md](05-authentication-strategy.md)).
- Communication module: Notices, SMS, Email, Push Notifications, with templates, bulk, scheduling, and logs ([06-communication-strategy.md](06-communication-strategy.md)).
- Online payments via Razorpay, PhonePe, and Cashfree with test/live modes, transaction logs, and refunds ([07-payment-strategy.md](07-payment-strategy.md)).
- Website + Mobile integration: synchronization of Public Notices, Photo Gallery, and Video Gallery ([04-website-mobile-integration.md](04-website-mobile-integration.md)).
- A single Flutter mobile app serving all roles.
- Familiar, modernized UI/UX ([08-ui-ux-principles.md](08-ui-ux-principles.md)).

### 5.2 Out of Scope (Version 1)
- Multi-school SaaS and multi-branch operation.
- A Website Content Management System (the existing HTML/CSS/JS website remains the school's public site).
- A separate ERP domain.
- Separate or per-role mobile apps.
- WhatsApp messaging (planned future channel).
- Future Modules listed in §7.3 (Library, Transport, Hostel, Payroll, Visitor Management, Biometric Attendance, AI Analytics).

### 5.3 Explicitly Forbidden in this PRD set
This PRD set defines the **product only**. It must not contain: source code, database table designs,
API endpoint designs, or framework-specific (Laravel/React/Flutter) implementation detail. Those
belong to downstream documents.

---

## 6. Product Principles

These principles govern every product and architecture decision and are binding on all downstream work.

1. **Architecture first** — design the structure before features; keep modules decoupled.
2. **API first** — every capability is exposed through a clean API consumed equally by web and mobile.
3. **Security first** — least-privilege access, encrypted secrets, protected financial/academic data.
4. **Mobile first** — every primary workflow is usable on a phone.
5. **Modular design** — modules are independently understandable, testable, and extendable.
6. **Reusable components** — shared UI and domain patterns across the product.
7. **Role-based access** — nothing is visible or actionable without an explicit permission.
8. **Audit every important action** — create/update/delete/login/financial/academic events are logged.
9. **Scalable architecture** — ready to grow from one school to many without redesign.
10. **Enterprise-grade standards** — consistent, maintainable, documented engineering throughout.
11. **UI preservation (mandatory)** — preserve the reference application's UI/UX as closely as possible while rebuilding the implementation; never copy its code. See [08-ui-ux-principles.md](08-ui-ux-principles.md) §0 *UI Preservation Policy*.

---

## 7. Functional Scope Summary

Full detail lives in [02-module-catalog.md](02-module-catalog.md). Summarized here for the PRD record.

### 7.1 Core ERP Modules
Authentication · Dashboard · Admissions · Students · Parents · Teachers · Staff · Users · Role &
Permission · Classes · Sections · Subjects · Teacher Assignments · Attendance · Timetable · Lesson
Planning · Teaching Logbook · Parent-Teacher Meeting · Teacher Substitutes · Examinations · Marks ·
Hall Tickets · Discipline · Conduct · Activities · Fee Structure · Fee Collection · Fee Dues ·
Accounts · Inventory · Assets · Documents · Calendar · Complaints · Helpdesk · Reports · Settings.

### 7.2 Additional Modules
Communication · Notice Board · SMS · Email · Push Notification · Website Integration · Photo Gallery
· Video Gallery · Payment Gateway · Communication Logs.

### 7.3 Future Modules (extensibility targets, not V1)
Library · Transport · Hostel · Payroll · Visitor Management · Biometric Attendance · AI Analytics ·
Multi-school SaaS · Multi-branch.

### 7.4 Platform & Cross-Cutting Capabilities (Version 1)
Number Generator · Audit Logs · Global Search · Import & Export · Branding · Student Lifecycle &
Promotion. These system-wide services are detailed in [02-module-catalog.md](02-module-catalog.md)
and govern numbering, auditability, search, bulk data movement, and visual identity across every
module.

---

## 8. Non-Functional Requirements (Product-Level)

> Stated at product level; quantified technical targets belong in the SRS.

| Category | Requirement |
|----------|-------------|
| **Security** | Role-based access control on every action; least privilege; encrypted credentials and gateway secrets; forced password change on first login; full audit trail of sensitive actions. |
| **Privacy & Compliance** | Protect student/parent personal data, financial records, and safeguarding fields; configurable data retention; consent flags (e.g., media consent) honoured. |
| **Availability** | High availability during school hours; graceful degradation when an external gateway (SMS/Email/Payment) is unavailable. |
| **Performance** | Responsive interactions for common workflows on web and mid-range mobile devices and typical school-grade connectivity. |
| **Scalability** | Support a full school's data volume (students, parents, staff, multi-year history) without redesign; architecture ready for multi-tenant growth. |
| **Usability** | Familiar, low-training UX preserved from the reference app; consistent navigation and components. |
| **Mobile** | Single Flutter app; role-adaptive menus; offline-tolerant for read-heavy views where feasible. |
| **Maintainability** | Modular, documented, enterprise-grade standards; configuration over hard-coding. |
| **Auditability** | Communication logs, transaction logs, and audit logs for all material events. |
| **Localization-ready** | Multi-board terminology, currency, time zone, academic-year configuration; language extensibility. |
| **Extensibility** | New modules, roles, payment gateways, and communication channels can be added as plug-ins without core redesign. |

---

## 9. Key Product Decisions (carried from analysis)

These validated workflow decisions from the reference application are **preserved** in the new
product (implementation re-built, not copied):

- **Multi-identity login**: staff, students, and parents authenticate through one login experience with distinct identifier rules.
- **Automatic account generation** when staff/student/parent records are created, with temporary password and forced first-login change.
- **Admission pipeline** as a controlled state machine: register → confirm → enroll (with reject/cancel side states); enrollment provisions the student record, login, and admission fee.
- **Examination publish lock**: publishing results makes them visible to students/parents and locks teacher edits.
- **Attendance integrity**: teachers mark only their assigned classes (constrained timing), with admin lock/unlock.
- **Fee lifecycle**: structures, collection, auto-generated monthly dues, receipts, and refunds.
- **Role-scoped visibility**: students see own data, parents see linked children, teachers see assigned classes.
- **Audit-everything** discipline across financial, academic, and administrative actions.

See `docs/01-existing-system-analysis/02-business-rule-index.md` for the full validated rule set
these decisions derive from.

---

## 10. Assumptions

1. Each school already operates a public website built with HTML/CSS/JavaScript that will remain the public site.
2. Each school provides its own domain and hosting target for its single-tenant installation.
3. Schools will supply credentials for chosen SMS, Email (SMTP), Push, and Payment gateways.
4. A Super Admin (system owner/vendor) operates licensing, updates, global configuration, and backups.
5. Connectivity and devices at schools are adequate for a modern web app and a mobile app.
6. Admission numbers are issued by the school under the numeric, ≤6-digit, unique rule.

---

## 11. Constraints

1. Single-tenant for Version 1 (one school per installation/database/domain).
2. No website CMS and no separate ERP domain.
3. One Flutter app for all roles.
4. Technology direction is fixed: React + Vite + Tailwind (web), Laravel 12 (API), MySQL (DB), Flutter (mobile). *(Stated as product direction; implementation detail is out of scope here.)*
5. The reference application's code must not be reused; only its workflows are preserved.

---

## 12. Risks & Mitigations (Product-Level)

| Risk | Impact | Mitigation |
|------|--------|------------|
| External gateway outages (SMS/Email/Payment) | Failed communications/payments | Channel toggles, retries, logs, graceful fallback to internal notices. |
| Scope creep from future modules | Delivery delay | Strict module catalog + roadmap gating; future modules deferred by design. |
| Data migration from reference app | Onboarding friction | Treat migration as a separate, well-scoped workstream; preserve identifiers. |
| Permission misconfiguration | Security exposure | Ship sensible default roles + matrix; require explicit grants; audit changes. |
| Single-tenant assumptions leaking into core | Blocks future SaaS | Enforce tenant-aware boundaries and configuration-over-hard-coding from day one. |

---

## 13. Companion Documents (this PRD set)

| # | Document | Purpose |
|---|----------|---------|
| 00 | **00-product-requirements.md** (this file) | Master PRD and single source of truth. |
| 01 | [01-product-vision.md](01-product-vision.md) | Vision, problem, market, personas, value proposition. |
| 02 | [02-module-catalog.md](02-module-catalog.md) | Full catalog of core, additional, and future modules. |
| 03 | [03-role-permission-matrix.md](03-role-permission-matrix.md) | Roles, permission actions, and the complete matrix. |
| 04 | [04-website-mobile-integration.md](04-website-mobile-integration.md) | Website ecosystem, URLs, sync scope, single mobile app. |
| 05 | [05-authentication-strategy.md](05-authentication-strategy.md) | Identities, login rules, account generation, sessions. |
| 06 | [06-communication-strategy.md](06-communication-strategy.md) | Channels, templates, bulk/scheduled, logs, gateways. |
| 07 | [07-payment-strategy.md](07-payment-strategy.md) | Online payment gateways, modes, logs, refunds. |
| 08 | [08-ui-ux-principles.md](08-ui-ux-principles.md) | Familiar UX, layout, design system principles. |
| 09 | [09-product-roadmap.md](09-product-roadmap.md) | Phased roadmap and future direction. |
| 10 | [10-release-plan.md](10-release-plan.md) | Releases, milestones, MVP, and release gates. |

---

## 14. Glossary

| Term | Meaning |
|------|---------|
| **ERP** | Enterprise Resource Planning — the integrated school management system. |
| **Single-tenant** | One isolated installation/database/domain per school (Version 1 model). |
| **Multi-tenant / SaaS** | Future model where many schools share infrastructure with isolation. |
| **Super Admin** | System owner/vendor responsible for licensing, updates, global config, backups. |
| **Administrator** | The school's top operational user. |
| **Identity types** | Staff, Student, Parent — each with distinct login identifiers. |
| **Module** | A self-contained functional area of the ERP (e.g., Admissions, Fees). |
| **Permission action** | A grantable verb (View, Create, Edit, Delete, Print, Export, Import, Approve, Publish, Lock, Unlock). |
| **Notice** | A communication item publishable to internal ERP, website, app, push, SMS, email. |
| **Publish lock** | State where published academic results are visible downstream and locked for edits. |
| **Reference application** | The existing Google Apps Script system whose workflows are preserved. |

---

## 15. Approval

This document is the controlling PRD. Changes are made here first and then cascaded to companion and
downstream documents. A software team should be able to begin writing the **Software Requirements
Specification (SRS)** directly from this PRD set.

**Version 1.1 — Feature Complete.** This revision incorporates the approved Version 1.1 product
decisions: the mandatory *UI Preservation Policy*, the expanded *Student Lifecycle & Promotion*,
*Branding*, the centralized *Number Generator*, expanded *Audit Logs*, *Global Search*,
*Import & Export*, expanded *Settings* sections, and expanded *Security* controls. **The PRD is now
considered feature complete; no further product scope will be added.** The next phase is the
**Software Requirements Specification (SRS)**; no additional product brainstorming should occur after
Version 1.1.
