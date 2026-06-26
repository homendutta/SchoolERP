# 04 – Staff Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Staff domain owns the school's **employees** — all teaching and non-teaching personnel — and the
**teacher** as a teaching resource. It is the source of truth for who works at the school; the Academic
domain references teachers from here for assignments and scheduling.

---

## 2. Responsibilities

- Maintain the **staff/employee master record** for every employee, across all roles (Administrator, Supervisor, Accountant, Clerk, Receptionist, Teacher, and future staff roles).
- Identify each employee by a unique **employee/staff number** and capture employment attributes (qualification, specialization, joining, status, emergency contacts).
- Expose the **Teacher** as a teaching resource that the Academic domain assigns to classes/subjects and schedules.
- Trigger **login provisioning** for staff on record creation (via Foundation).

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Staff / Employee** | An employed person; identified by a unique staff/employee number. | ✓ |
| **Teacher Profile** | The teaching facet of a staff member (specialization, teaching status). | — (within Staff) |
| **Employment Detail** | Employment attributes of a staff member (role designation, joining, status, emergency contact). | — (within Staff) |

> A **Teacher** is not a separate person from a Staff member — it is a staff member who teaches. The
> Staff domain owns the person; the Academic domain owns the *assignment* of that teacher to classes
> and subjects.

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **User / Account** | Foundation | Each staff member has an auto-provisioned login. |
| **Role** | Foundation | A staff member's access is governed by an assigned role. |
| **Number Sequence, Audit, Document, Media** | Foundation | Staff numbers, audit, documents, profile photos. |
| **Teacher Assignment, Timetable, Substitute Allocation** | Academic | Academic references the teacher; Staff does not own these. |
| **Asset (assigned-to)** | Asset | Assets may be assigned to a staff member. |

---

## 5. Relationships

- A **Staff/Employee** corresponds to **one User/Account** (Foundation) and holds **one Role**.
- A **Staff** member who teaches has a **Teacher Profile**; that teacher is referenced by many **Teacher Assignments**, **Timetable Entries**, **Lesson Plans**, **Logbook Entries**, **PTM Slots**, and **Substitute Allocations** (all owned by Academic).
- A **Staff** member may be the **assigned-to** of many **Assets** (Asset domain) and the **recorded-by/collected-by** actor on records across domains (referenced as an actor, owned elsewhere).

---

## 6. Business Boundaries

**Inside:** the employee person record, employment attributes, and the teacher-as-resource identity.

**Outside (not owned here):**
- **Teacher Assignments, timetable, lesson plans, logbook, PTM, substitutes** — owned by Academic (these reference the teacher).
- The **login account and role** — owned by Foundation (Staff triggers provisioning and references the role).
- **Payroll/HR** depth (salary, payslips) — a **future** domain/module; not owned in Version 1.
- **Attendance of staff / biometric** — future scope; not owned here in V1.

**Consistency boundaries:**
- A Staff member with its employment detail and teacher profile is one boundary.

---

## 7. Dependency Rules

- Staff **depends on Foundation** (account, role, numbering, audit, documents).
- Staff must **not depend on** Academic, Student, Attendance, Examination, Finance, Communication, or Asset.
- Academic, Attendance, Examination, Finance, Communication, and Asset domains **reference** the staff/teacher identity; they do not own staff data.
- Future Payroll/HR will **depend on** Staff (and Finance) without requiring changes to this domain's ownership.
