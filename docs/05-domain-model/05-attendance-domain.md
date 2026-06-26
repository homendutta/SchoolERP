# 05 – Attendance Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Attendance domain owns the **recording and integrity of student presence**. It captures attendance
per class on a date — in **daily** or **subject/period-wise** mode — computes presence counts, and
governs **locking** so finalized attendance cannot be altered without authorization.

---

## 2. Responsibilities

- Record **student attendance** for a class on a date, in daily or subject-wise (per-period) mode.
- Capture each student's **status** (present / absent / late / half-day) and derive present/absent/total counts.
- Enforce **marking constraints** referenced from other domains (teacher marks only assigned classes; timing rules).
- Govern **locking/unlocking** of attendance records so finalized data is protected from edits.
- Respect the **academic calendar** (holidays block attendance) and working-day configuration.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Attendance Record** | The attendance for one class on one date in one mode (daily, or subject + period), holding per-student statuses and derived counts; unique per class/date/mode/subject/period. | ✓ |
| **Attendance Lock** | The locked/unlocked state (and lock time) of an Attendance Record. | — (within Attendance Record) |

> The per-student status set is owned **within** the Attendance Record aggregate (it is the record's
> content), not as an independently owned entity.

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Class** | Academic | Attendance is recorded for a class. |
| **Subject**, **School Period** | Academic | Subject-wise/period attendance references them. |
| **Student** | Student | Each per-student status references a student. |
| **Teacher / Staff (marked-by)** | Staff | The marker of an attendance record. |
| **Calendar Event (holiday)** | Academic | Holidays block attendance for affected dates. |
| **User/Account, Audit** | Foundation | Authorization and audit (e.g., attendance unlock is audited). |

---

## 5. Relationships

- An **Attendance Record** belongs to one **Class** and one **date**, in one **mode**; subject-wise records also reference one **Subject** and one **Period**.
- An **Attendance Record** contains the **status of each Student** in the class for that occasion.
- An **Attendance Record** is marked by one **Staff** member (teacher/supervisor/admin) and may be **locked** by an authorized actor.
- A **Holiday Calendar Event** prevents attendance for the dates it covers.
- A **Student** accumulates many attendance statuses across many Attendance Records (the basis for attendance summaries consumed by Reports/Dashboards).

---

## 6. Business Boundaries

**Inside:** attendance records, per-student statuses within a record, derived counts, and lock state.

**Outside (not owned here):**
- **Class/Subject/Period** definitions and the **calendar** — owned by Academic.
- **Student** identity and membership — owned by Student.
- The **teacher's assignment** that authorizes marking — owned by Academic/Staff (Attendance *applies* the constraint but does not own it).
- **Attendance reports/percentages** — produced by the Reports read model from attendance data.

**Consistency boundaries:**
- One Attendance Record (with its per-student statuses, counts, and lock state) is one consistency boundary; saving recomputes counts atomically.
- Lock/unlock transitions are controlled, audited state changes within that boundary.

---

## 7. Dependency Rules

- Attendance **depends on Academic** (class/subject/period/calendar), **Student** (students), **Staff** (marker), and **Foundation** (authorization/audit).
- Attendance must **not depend on** Examination, Finance, Communication, or Asset.
- No domain owns attendance data except this one; Reports/Dashboards **read** it; Communication may **notify** based on attendance events (consuming events, not owning data).
- Marking and locking rules enforced here reference the permission/scope model (Foundation) and the teacher-assignment model (Academic), but Attendance owns the **record** and its **lock**.
