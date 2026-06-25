# 01 – Product Vision

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines why the product
> exists, who it serves, and the principles that make it distinctive.

---

## 1. Vision Statement

> **To give every school one modern, secure, and mobile-friendly platform that runs its entire
> operation — from a student's first admission enquiry to their final report card — while staying
> as familiar and easy to use as the tools the school already trusts.**

SchoolERP turns fragmented, manual, and spreadsheet-bound school administration into a single
integrated system that staff, teachers, students, and parents all use from the web and from one
mobile app — connected directly to the school's own public website.

---

## 2. Problem Statement

Schools run on a patchwork of registers, spreadsheets, message broadcasts, paper receipts, and
disconnected tools. This creates recurring pain:

- **Fragmented data** — admissions, fees, attendance, and marks live in separate places that never reconcile.
- **Manual, error-prone workflows** — re-keying student data across forms; hand-tallied attendance and marks.
- **Weak communication** — notices and fee reminders rarely reach every parent reliably.
- **Cash-heavy fee collection** — limited online payment, manual receipts, hard-to-trace dues.
- **No accountability trail** — changes to financial and academic records are not audited.
- **Poor mobile access** — parents and teachers want phone-first access; legacy tools don't provide it.
- **Disconnected web presence** — the public website and the school's internal systems are unrelated, so notices and galleries are maintained twice.

A proven Google Apps Script application already solved many of these workflow problems for real
schools — validating the model — but it is constrained by its platform and cannot scale into a
commercial, secure, multi-board, mobile-first product. SchoolERP carries those validated workflows
into a modern architecture.

---

## 3. Target Market

### 3.1 Segments
- Private Schools
- Public Schools
- CBSE Schools
- ICSE / ISC Schools
- State Board Schools
- International Schools (IB, Cambridge)

### 3.2 Initial Deployment Profile
Single-tenant: one school per installation, database, and domain. The product is sold and operated
per school, integrated with that school's existing website.

---

## 4. Personas

> Personas frame the product's role-based design. Detailed permissions are in
> [03-role-permission-matrix.md](03-role-permission-matrix.md).

| Persona | Role | Primary Goals | Key Pain Solved |
|---------|------|---------------|-----------------|
| **Vendor Owner** | Super Admin | License schools, push updates, configure globally, back up | Operating many installations safely |
| **Principal / Head** | Administrator | Run the school; oversee every module; approve and publish | One control center, full visibility |
| **Academic Coordinator** | Supervisor | Oversee academics — attendance, exams, results, conduct | Academic oversight without clerical noise |
| **Front-office Clerk** | Clerk | Admissions, student/parent records, fee collection | Fast, accurate front-desk operations |
| **Accountant** | Accountant | Fees, dues, accounts, payments, refunds, reports | Trustworthy, auditable finance |
| **Receptionist** | Receptionist | Enquiries, visitor-facing info, basic records, helpdesk | Quick lookups and front-desk support |
| **Class Teacher / Subject Teacher** | Teacher | Mark attendance, enter marks, timetable, lesson plans, logbook | Phone-first daily teaching tasks |
| **Student** | Student | View timetable, attendance, marks, notices, fees, hall tickets | Self-service academic visibility |
| **Parent** | Parent | Track child's attendance/marks/fees, pay online, book PTM, receive notices | Engagement and convenience |

---

## 5. Value Proposition

| For… | SchoolERP delivers… |
|------|---------------------|
| **School leadership** | A single, auditable system of record for the whole school, with role-based control and reporting. |
| **Administrative staff** | Faster admissions, automatic account creation, and streamlined fee and record management. |
| **Accountants** | End-to-end fee lifecycle — structures, dues, collection, online payments, refunds — fully logged. |
| **Teachers** | Mobile-first daily tools for attendance, marks, timetable, and lesson logging. |
| **Students & parents** | Always-on access to academics, notices, and online fee payment from one app. |
| **The vendor** | A modular, multi-board, single-tenant product with a clear path to SaaS. |

---

## 6. Product Pillars

1. **Familiar yet modern** — preserve the proven UX and workflows; modernize the foundation.
2. **One ecosystem** — ERP, public website, and mobile app share notices and galleries automatically.
3. **One app, every role** — a single Flutter app adapts its dashboard and menus to the signed-in user.
4. **Trustworthy by design** — role-based access and audit trails protect financial and academic data.
5. **Built to grow** — modular and tenant-aware so new modules and SaaS/multi-branch arrive without rework.

---

## 7. Differentiators

- **Workflow-validated**: built on business rules already proven in production, not theoretical.
- **Board-agnostic**: supports CBSE, ICSE, State, IB, and Cambridge concepts (curriculum stages, grading schemes, international student data).
- **Website-integrated**: no separate ERP domain and no duplicate CMS — the school's site and ERP are one.
- **Truly single mobile app**: every role served by one adaptive Flutter app.
- **Commerce-ready**: pluggable online payments (Razorpay, PhonePe, Cashfree) with test/live modes and refunds.

---

## 8. North-Star Outcome

> **Every routine school interaction — marking attendance, paying a fee, publishing a notice,
> checking a result — happens in SchoolERP, on web or mobile, with the right person seeing exactly
> what they're allowed to, and every important action recorded.**

When a school reaches that state, administrative friction collapses, parent engagement rises, and the
institution has a single trustworthy record of its operation.

---

## 9. Guiding Principles (recap)

Architecture first · API first · Security first · Mobile first · Modular design · Reusable
components · Role-based access · Audit everything · Scalable architecture · Enterprise-grade
standards. (Defined in [00-product-requirements.md](00-product-requirements.md) §6.)
