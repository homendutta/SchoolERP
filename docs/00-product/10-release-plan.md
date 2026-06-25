# 10 – Release Plan

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md) and
> [09-product-roadmap.md](09-product-roadmap.md). Defines the release structure, MVP, milestones, and
> release gates at a **product** level. Sequencing reflects dependency order; durations are
> indicative, not contractual.

---

## 1. Release Strategy

The product ships in incremental, demonstrable releases. Each release is a **vertical slice** that is
secure, role-aware, audited, and usable on web and (from R3) mobile. Core platform capabilities
(auth, roles, settings, audit) are built **first** because every other module depends on them.

| Release | Name | Theme |
|---------|------|-------|
| **R1** | Platform Core | Foundations: auth, roles, users, settings, audit. |
| **R2** | Academic & Records Core | Classes→students→admissions→academics. |
| **R3** | Finance & Mobile | Fees, accounts, and the single Flutter app. |
| **R4** | Engagement & Commerce | Communication, website sync, online payments. |
| **R5** | Depth & Hardening | Reporting depth, imports, performance, polish. |
| **R6+** | Expansion | Future modules and platform scale (per roadmap). |

---

## 2. MVP Definition

The **Minimum Viable Product** is reached at the end of **R3**: a school can run its full core
operation — admissions, records, academics, attendance, exams, and the complete fee lifecycle — with
role-based access and audit, on both web and the single mobile app.

**MVP includes:** Authentication & account generation · Role & Permission · Users/Staff · Settings ·
Classes/Sections/Subjects/Assignments · Students/Parents/Admissions · Attendance/Timetable/Lesson
Plans/Logbook/Substitutes/PTM · Exams/Marks/Hall Tickets/Discipline/Conduct/Activities · Fee
Structure/Collection/Dues/Accounts · Dashboard · core Reports · Documents/Calendar/Complaints/Helpdesk
· single Flutter app.

**MVP excludes (deferred to R4+):** full Communication suite, website/app content sync, online
payments, reporting depth, and all future modules.

---

## 3. Release Breakdown

### R1 — Platform Core
**Scope:** Authentication (multi-identity), automatic account generation, forced first-login change;
Role & Permission (default + custom roles, permission matrix); Users, Staff; Settings (school profile,
academic year, periods, branding); audit logging foundation.
**Demo:** Create a staff/student/parent record → auto account + temporary password → first-login forced
change; configure a custom role and verify access control.
**Gate:** Security review of auth + permissions; audit trail verified.

### R2 — Academic & Records Core
**Scope:** Classes, Sections, Subjects, Teacher Assignments; Students, Parents (+links), Admissions
pipeline (register→confirm→enroll, reject/cancel); Attendance, Timetable, Lesson Planning, Teaching
Logbook, Substitutes, PTM; Examinations, Marks, Hall Tickets, Discipline, Conduct, Activities;
Dashboard (academic).
**Demo:** Admit and enroll a student (auto-provisioned login + admission fee placeholder); build a
timetable; mark attendance; enter and publish marks (publish lock verified); print a hall ticket.
**Gate:** Workflow parity with validated reference rules; scoping (teacher/student/parent) verified.

### R3 — Finance & Mobile (MVP)
**Scope:** Fee Structure, Fee Collection, Fee Dues (auto-generation), Accounts; receipts; refunds;
finance reports; Inventory, Assets, Documents, Calendar, Complaints, Helpdesk; **single Flutter app**
covering all roles with role-adaptive menus.
**Demo:** Configure fees → auto-generate monthly dues → collect a payment → issue receipt → process a
refund; perform the same core tasks from the mobile app as each role.
**Gate:** Financial audit trail verified; mobile parity for core role workflows; **MVP sign-off**.

### R4 — Engagement & Commerce
**Scope:** Communication (Notices, SMS, Email, Push; templates; bulk; scheduled; logs; gateway/SMTP/
SMS/push settings); Website Integration (notices + photo/video gallery sync to website & app); Online
Payments (Razorpay, PhonePe, Cashfree; test/live; transaction logs; refunds; reconciliation); Push
notifications in the app.
**Demo:** Publish one notice to ERP + website + app + push + SMS; pay a fee online (test then live) and
see it reconcile into fees; update a gallery and see it appear on the website automatically.
**Gate:** Communication logs + payment transaction logs verified; secret handling reviewed; graceful
degradation when a gateway is disabled/unavailable.

### R5 — Depth & Hardening
**Scope:** Expanded reporting and exports; bulk data import (onboarding); dashboard analytics
enrichment; performance, reliability, accessibility hardening; admin tooling (backups, configuration).
**Demo:** Bulk-import students/staff; run finance/academic/attendance/admissions reports with exports.
**Gate:** Performance and accessibility targets met; onboarding import validated.

### R6+ — Expansion
**Scope:** Future modules (Library, Transport, Hostel, Payroll, Visitor Management, Biometric, AI
Analytics) and platform scale (multi-school SaaS, multi-branch), each shipped independently per the
[09-product-roadmap.md](09-product-roadmap.md).
**Gate:** Per-module gates; no core redesign required to add each.

---

## 4. Milestones

| Milestone | Reached at | Significance |
|-----------|-----------|--------------|
| **M1 — Secure Platform** | End R1 | Auth, roles, audit operational. |
| **M2 — School Runs Academics** | End R2 | Admissions→exams workflows live. |
| **M3 — MVP / Go-Live Ready** | End R3 | Full core + finance + mobile; first school can go live. |
| **M4 — Connected & Paid** | End R4 | Communication, website sync, online payments live. |
| **M5 — Production-Hardened** | End R5 | Reporting depth, imports, performance. |
| **M6 — Expanding** | R6+ | Future modules and platform scale. |

---

## 5. Release Gates (apply to every release)

Each release must pass these product-level gates before sign-off:

1. **Functional completeness** — release scope is implemented and matches this PRD set.
2. **Role & permission correctness** — every feature respects the permission matrix and data scope.
3. **Audit & logs** — material actions are audited; communications/transactions logged.
4. **Security review** — auth, secrets, and sensitive data handling reviewed for the release scope.
5. **Web + mobile parity** (from R3) — role-appropriate parity across surfaces.
6. **Workflow fidelity** — validated reference workflows are preserved (not regressed).
7. **UX consistency** — familiar layout/navigation/components maintained ([08-ui-ux-principles.md](08-ui-ux-principles.md)).
8. **Documentation** — SRS-traceable; release notes prepared.

---

## 6. Dependencies & Sequencing Rules

- **R1 precedes everything** — no module ships without auth, roles, and audit.
- **Master data precedes transactions** — Classes/Subjects/Assignments before Attendance/Marks; Students/Fee Structure before Fees/Dues.
- **Communication & Payments (R4) build on R2/R3** — notices need audiences; payments need the fee lifecycle.
- **Website/app sync (R4) requires the mobile app (R3)** and the notice/gallery sources.
- **Future modules (R6+) require no core redesign** — enforced by the architecture constraints in the PRD.

---

## 7. Quality & Acceptance Principles

- **Single source of truth**: releases are accepted against this PRD set; deviations are reflected here first.
- **Incremental and demonstrable**: every release ends with a working, demoable vertical slice.
- **Secure and audited by default**: no release weakens access control or audit.
- **Familiar experience preserved**: UX continuity is a standing acceptance criterion.
- **Extensibility protected**: no release introduces single-tenant assumptions that block future SaaS/multi-branch.

---

## 8. Out of Scope for Version 1 Releases (R1–R5)

Multi-school SaaS · Multi-branch · Website CMS · separate ERP domain · separate per-role apps ·
WhatsApp channel · Library/Transport/Hostel/Payroll/Visitor/Biometric/AI modules. (All are future per
[09-product-roadmap.md](09-product-roadmap.md).)
