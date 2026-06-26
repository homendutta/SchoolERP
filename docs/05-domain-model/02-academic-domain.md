# 02 – Academic Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Academic domain defines the school's **academic structure** (classes, sections, subjects) and its
**scheduling and teaching operations** (teacher assignments, periods, timetable, lesson plans,
teaching logbook, parent-teacher meetings, substitutes, and the academic calendar). It is the
structural backbone that Student, Attendance, and Examination domains build upon.

---

## 2. Responsibilities

- Define **classes** and their **sections** within an academic year, including curriculum stage, medium, stream, and shift.
- Define **subjects** per class and their mark composition.
- Maintain **teacher assignments** (teacher ↔ class ↔ subject ↔ year), including class-teacher designation.
- Define the **bell schedule** (periods) and the **timetable** (class and teacher), with conflict prevention.
- Hold **lesson plans** and the **teaching logbook** (what was planned and what was actually taught).
- Manage **parent-teacher meeting** availability and bookings.
- Allocate **substitutes** when a teacher is absent, validated against the timetable.
- Maintain the **academic calendar** (events, holidays) that scopes operations and blocks attendance.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Class** | An academic grouping for a year; identified by class + academic year (+ shift). | ✓ |
| **Section** | A sub-group within a class; identified by class + section name + year. | — (within Class) |
| **Subject** | A taught course of a class; identified by subject code + class. | ✓ |
| **Teacher Assignment** | A teacher's mapping to a class+subject for a year; unique per teacher/class/subject/year. | ✓ |
| **School Period** | A bell-schedule slot; unique per period number + year + day type. | ✓ |
| **Timetable Entry** | A scheduled slot for a class (day, period, subject, teacher, mode); unique per class/day/period/year/term. | ✓ |
| **Lesson Plan** | A teacher's instructional plan for a class/subject/period with review state. | ✓ |
| **Logbook Entry** | A record of a taught session; unique per teacher/class/subject/date/period. | ✓ |
| **PTM Slot** | A teacher's meeting availability window with capacity and mode. | ✓ |
| **PTM Booking** | A parent's booking of a slot for a student; unique per slot/student. | — (within PTM Slot) |
| **Substitute Allocation** | A substitution arrangement for an absent teacher on a date, with per-period allocations. | ✓ |
| **Calendar Event** | A school event/holiday with audience, date(s), and recurrence. | ✓ |

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Teacher / Staff** | Staff | Teacher assignments, timetable slots, lesson plans, logbook, PTM slots, substitutes reference the teacher. |
| **Student** | Student | PTM bookings reference a student; rosters reference class membership. |
| **Parent** | Student | PTM bookings are made by a parent. |
| **Academic Year / Settings** | Foundation | Classes, fees, timetables are scoped to the configured academic year and working days. |
| **User / Account, Audit, Number Sequence** | Foundation | Authorization, audit, and any codes. |

---

## 5. Relationships

- A **Class** has many **Sections** and many **Subjects**; a Class belongs to one **Academic Year**.
- A **Subject** belongs to one **Class**.
- A **Teacher Assignment** binds one **Teacher** to one **Class** and one **Subject** for one year; a class may have many assignments; a teacher may hold many.
- A **Timetable Entry** belongs to one **Class** and references one **Subject** and one **Teacher** (empty for free periods); it occupies one **Period** on one day. A teacher cannot occupy two classes in the same period/day/year/term (conflict rule).
- A **School Period** is referenced by many **Timetable Entries**.
- A **Lesson Plan** and a **Logbook Entry** each reference one **Teacher**, **Class**, and **Subject**.
- A **PTM Slot** belongs to one **Teacher** and may have many **PTM Bookings** up to capacity; a **PTM Booking** references one **Parent** and one **Student**.
- A **Substitute Allocation** references one absent **Teacher** and many per-period allocations, each naming a substitute **Teacher**, validated against the **Timetable**.
- A **Calendar Event** may target all, a role, or a specific **Class**; holiday events influence the **Attendance** domain.

---

## 6. Business Boundaries

**Inside:** academic structure, scheduling, teaching records, PTM, substitutes, calendar.

**Outside (not owned here):**
- The **Teacher as a person/employee** — owned by Staff (Academic references the teacher identity).
- **Student records and membership status** — owned by Student (Academic references class membership conceptually; the Student domain owns the student's class assignment).
- **Attendance and Marks** — owned by Attendance and Examination, which *reference* Academic structure.
- **Fee structures** tied to a class — owned by Finance (references Class).

**Consistency boundaries:**
- A Class with its Sections is one boundary; a Timetable Entry's uniqueness/conflict checks form a boundary; a Substitute Allocation with its per-period allocations is one boundary.

---

## 7. Dependency Rules

- Academic **depends on Foundation** (settings/academic year, authorization, audit) and **references Staff** (teachers).
- Academic must **not depend on** Student, Attendance, Examination, Finance, Communication, or Asset.
- Student, Attendance, and Examination domains **reference** Academic structure (class/section/subject/period/timetable) but do not own it.
- The class assignment of a **Student** is owned by the Student domain; Academic defines the **Class** the student points to.
