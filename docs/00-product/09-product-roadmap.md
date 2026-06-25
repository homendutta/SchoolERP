# 09 – Product Roadmap

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines the phased evolution
> of the product — themes and module sequencing — at a **product** level. Dates are indicative
> horizons, not commitments; the concrete release breakdown is in [10-release-plan.md](10-release-plan.md).

---

## 1. Roadmap Themes

| Phase | Theme | Outcome |
|-------|-------|---------|
| **Phase 1** | Foundation & Core ERP | A complete, single-tenant School ERP covering daily operations. |
| **Phase 2** | Engagement & Commerce | Communication, online payments, website/app integration fully live. |
| **Phase 3** | Operational Depth | Reporting depth, analytics readiness, and operational polish. |
| **Phase 4** | Expansion Modules | Library, Transport, Hostel, Payroll and other future modules. |
| **Phase 5** | Platform Scale | Multi-school SaaS, multi-branch, AI analytics, biometric integration. |

---

## 2. Phase 1 — Foundation & Core ERP

**Goal:** Stand up the platform foundations and the core operational modules so a school can run
day-to-day operations end-to-end.

**Foundations**
- Authentication (multi-identity), automatic account generation, forced first-login password change.
- Role & Permission system with default roles, custom roles, and the permission matrix.
- Users, Staff, Settings (school profile, academic year, periods, branding).

**Core academic & records**
- Classes, Sections, Subjects, Teacher Assignments.
- Students, Parents (+ links), Admissions pipeline.
- Attendance, Timetable, Lesson Planning, Teaching Logbook, Substitutes, PTM.
- Examinations, Marks, Hall Tickets, Discipline, Conduct, Activities.

**Core finance & admin**
- Fee Structure, Fee Collection, Fee Dues, Accounts.
- Inventory, Assets, Documents, Calendar, Complaints, Helpdesk.
- Dashboard and Reports.

**Exit criteria:** A school can admit, enroll, schedule, teach, assess, and bill students with
role-based access and audit trails.

---

## 3. Phase 2 — Engagement & Commerce

**Goal:** Connect the ERP to parents/students and to the school's public presence; enable digital money.

- **Communication module**: Notices, SMS, Email, Push; templates; bulk; scheduled; communication logs; gateway/SMTP/SMS/push settings.
- **Website Integration**: automatic sync of Public Notices, Photo Gallery, Video Gallery to website and app.
- **Flutter mobile app**: single role-adaptive app for all roles, with push notifications.
- **Online Payments**: Razorpay, PhonePe, Cashfree; test/live modes; transaction logs; refunds; reconciliation into fees.

**Exit criteria:** Parents receive notices and pay fees online from the app; published notices/galleries
appear on the website automatically.

---

## 4. Phase 3 — Operational Depth

**Goal:** Deepen insight and polish operations.

- Expanded reporting and exports across finance, academics, attendance, admissions, and audit.
- Dashboard analytics enrichment per role.
- Hardening: performance, reliability, accessibility, and admin tooling (backups, configuration).
- Data import tooling for onboarding (bulk import of students/staff/structures).

**Exit criteria:** Schools rely on the ERP for decision-useful reporting and smooth onboarding.

---

## 5. Phase 4 — Expansion Modules (Future)

**Goal:** Extend coverage to adjacent school operations as pluggable modules (no core redesign).

| Module | Adds |
|--------|------|
| **Library** | Catalog, issue/return, fines, membership. |
| **Transport** | Routes, stops, vehicles, transport fees, assignment. |
| **Hostel** | Rooms, allocations, wardens, hostel fees, mess. |
| **Payroll** | Salary structures, payslips, attendance-linked pay, deductions. |
| **Visitor Management** | Visitor logs, passes, appointments. |

New roles unlock alongside modules (Librarian, Transport Manager, Hostel Warden, HR Manager,
Examination Controller, Sports Coordinator) via the existing role system.

**Exit criteria:** Each module ships independently and integrates with existing fees, communication,
and permissions.

---

## 6. Phase 5 — Platform Scale (Future)

**Goal:** Evolve from single-tenant installs to a scalable platform.

- **Multi-school SaaS**: many schools on shared, isolated infrastructure; Super Admin governs many tenants.
- **Multi-branch**: multiple campuses under one school entity, with branch-scoped roles and reporting.
- **AI Analytics**: predictive insights (at-risk students, fee-default prediction, performance trends).
- **Biometric Attendance**: device-integrated attendance capture.

> These are enabled by Version 1's architectural constraints (tenant-aware boundaries,
> configuration-over-hard-coding, branch-scopable permissions) — **no redesign required**.

**Exit criteria:** The product operates as a multi-tenant platform without re-architecting core modules.

---

## 7. Channel & Gateway Extensions (cross-phase)

| Extension | Phase | Notes |
|-----------|-------|-------|
| **WhatsApp** messaging | Phase 2+ | Pluggable communication channel. |
| **Additional payment gateways** | Phase 2+ | Pluggable beyond Razorpay/PhonePe/Cashfree. |
| **Additional public content sync** | Phase 3+ | Generalize website sync beyond notices/galleries. |

---

## 8. Roadmap at a Glance

```
Phase 1 ──▶ Phase 2 ──▶ Phase 3 ──▶ Phase 4 ──▶ Phase 5
Foundation   Engagement   Operational  Expansion    Platform
& Core ERP   & Commerce   Depth        Modules      Scale
(single-     (comms,      (reporting,  (library,    (SaaS, multi-
 tenant      payments,    analytics    transport,   branch, AI,
 core)       website/app) readiness,   hostel,      biometric)
                          hardening)   payroll…)
```

---

## 9. Guiding Constraints Across the Roadmap

1. **Single-tenant in V1**, but never code against single-tenant assumptions in the core.
2. **Modules are pluggable** — future modules add without touching core contracts.
3. **Permissions scale** — the role/permission model already supports custom roles and future scoping.
4. **Channels and gateways are pluggable** — communications and payments extend by configuration.
5. **Experience stays familiar** — UX continuity is maintained as modules are added.
