# 01 – Analysis Roadmap

> **Purpose:** Recommend the order in which modules of the existing system should be analysed
> in later phases. Ordering follows **data dependencies** — foundational tables and the entities
> that everything else references (FKs) are analysed first, so that downstream modules can be
> understood without forward references.
>
> This is an ordering guide only — no redesign or implementation work.

---

## Dependency Principle

A module is placed **before** another when the second module's sheet stores a foreign key into
the first, or relies on it for permissions/lookups. Concretely:

- `Users` is referenced by nearly every sheet (`CreatedBy`, `MarkedBy`, `TeacherID`, ...).
- `Classes` → referenced by Subjects, Students, Exams, Fee_Structure, Timetable, ...
- `Students` → referenced by Marks, Attendance, Fees, Discipline, Conduct, Activities, ...
- Auth + permission helpers (`can*`) gate every endpoint, so they are understood first.

---

## Recommended Analysis Order

### Tier 0 — Platform Foundations
1. **Configuration & Schema** (`Code.gs` Config, `*_HEADERS`, 36 sheet constants)
   — Establishes the data dictionary every other module depends on. Nothing can be read correctly without column maps.
2. **Helpers & Permission Model** (`getSheet`, `next*Id`, `rowTo*`, `can*` gates, lookup maps)
   — Every endpoint routes through these; understanding the `can*(role)` matrix defines the whole RBAC model.
3. **Web Entry & App Shell** (`doGet`, `initializeSheets`; client `App`, `Dashboard`, `MainContent`, `Sidebar`, `useSWR`)
   — How requests enter, how the SPA routes by role, how the client cache works.
4. **Authentication & Session** (`authenticateUser`, staff/student/parent auth, mirrors; `LoginPage`, session)
   — Defines the three login identities and the `Users`-sheet mirror that unifies them.

### Tier 1 — Core Master Data (referenced by almost everything)
5. **Users / Staff** — root entity for FKs and login; depends only on auth.
6. **School Settings** — global config (academic year, working days, currency) used by many modules.
7. **Classes & Sections** — referenced by subjects, students, exams, fees, timetable.
8. **Subjects** — depends on Classes; referenced by marks, assignments, timetable, logbook.
9. **Teacher Assignments** — junction of Users × Classes × Subjects; needed for teacher-scoped permissions.

### Tier 2 — People Records
10. **Students** — depends on Classes; central FK target for academics, fees, behaviour.
11. **Parents** — depends on (links to) Students.
12. **Parent ↔ Student Junction** — depends on both Parents and Students.
13. **Admissions** — pipeline that *creates* Students and Fee_Payments on enrollment; analyse after Students/Fees schema is known.

### Tier 3 — Daily Academic Operations
14. **School Periods** — bell schedule; prerequisite for Timetable and period-wise attendance.
15. **Timetable** — depends on Classes, Subjects, Users, Periods; feeds substitutes & "now" widgets.
16. **Attendance** — depends on Classes, Students, Subjects, Periods (JSON status blobs, locking).
17. **Substitutes** — depends on Timetable + Users (absent-teacher allocation).

### Tier 4 — Assessment
18. **Exams** — depends on Classes; publish gate controls result visibility.
19. **Marks** — depends on Exams, Students, Subjects; grading + ranks.
20. **Hall Tickets** — depends on Exams + Students.
21. **Conduct** — periodic grading per Student.
22. **Discipline** — incident log per Student.
23. **Activities** — co-curricular records per Student.

### Tier 5 — Finance
24. **Fee Structure** — depends on Classes; defines categories/amounts.
25. **Fee Payments** — depends on Students + Fee Structure; receipts, refunds, bulk.
26. **Fee Dues (Monthly)** — auto-generated from Fee Structure + admission month; paid via Fee Payments.
27. **Daily Accounts** — standalone day-book (income/expense); minimal external FKs.

### Tier 6 — Communication & Support
28. **Notices** — depends on Classes (audience targeting) + Users.
29. **School Calendar** — depends on Classes (class-specific events); blocks attendance.
30. **PTM (Slots + Bookings)** — depends on Users (teachers), Parents, Students, Classes.
31. **Complaints** — polymorphic submitter; light dependencies.
32. **Helpdesk Tickets** — depends on Students + Users; SLA logic.
33. **Documents** — polymorphic (attaches to Student/Parent/User/Asset); analyse after those entities.

### Tier 7 — Teaching Records
34. **Lesson Plans** — depends on Users, Classes, Subjects.
35. **Teaching Logbook** — depends on Users, Classes, Subjects, Periods; correlates to attendance.

### Tier 8 — Facilities
36. **Assets + Asset Maintenance** — depends on Users (assigned-to); self-contained otherwise.
37. **Stock / Inventory + Transactions** — self-contained item register + movements.

### Tier 9 — Aggregation & Cross-cutting (analyse last — they read everything above)
38. **Reports** (10 report endpoints) — read across fees, attendance, marks, admissions, audit.
39. **Role-aware Dashboards** (6 role dashboards + charts) — aggregate KPIs across all modules.
40. **Sidebar Badges** — cached counts across support/behaviour modules.
41. **Drill-downs & Misc** (student/parent/teacher/exam drill-downs, email receipt, refund, admin reset).
42. **Logs / Audit** — written by every module; reviewed last as a cross-cutting concern.
43. **Demo-data Seeders** (`setupDemoData`, `seedTimetableDemo`) — non-production; analyse last.

---

## Why This Order

- **Schema and permissions first** — you cannot correctly interpret any row or endpoint without
  the column maps (Tier 0.1) and the `can*` permission matrix (Tier 0.2).
- **Master data before transactions** — Users → Classes → Subjects → Students form the FK backbone;
  every transactional sheet points back into them.
- **Admissions after Students + Fees** — admission *enrollment* writes Student and Fee_Payment rows,
  so its behaviour is only clear once those targets are understood.
- **Periods before Timetable/Attendance** — both reference the bell schedule.
- **Aggregations last** — reports, dashboards, and badges are pure consumers; they make sense only
  after every source module is mapped.

---

## Quick Dependency Reference

```
Config / Helpers / Auth   (foundation — gates everything)
        ↓
Users ──────────────┐
  ↓                 │ (CreatedBy / *ID FKs everywhere)
Classes ── Subjects ── Teacher_Assignments
  ↓
Students ── Parents ── Parent_Students ── Admissions
  ↓
Periods → Timetable → Substitutes
Attendance
Exams → Marks → Hall Tickets ;  Conduct · Discipline · Activities
Fee_Structure → Fee_Payments → Fee_Dues ;  Account_Transactions
Notices · Calendar · PTM · Complaints · Helpdesk · Documents
Lesson_Plans · Teaching_Logbook
Assets/Maintenance · Stock/Transactions
        ↓
Reports · Dashboards · Sidebar Badges · Drill-downs · Logs   (consume all of the above)
```
