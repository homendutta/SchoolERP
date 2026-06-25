# 02 – Module Catalog

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines every module of the
> product: its purpose, key capabilities, primary users, and dependencies. This catalog describes
> **what** each module does at a product level — not data models, APIs, or implementation.
> Detailed business rules carried forward live in `docs/01-existing-system-analysis/02-business-rule-index.md`.

---

## How to Read This Catalog

- **Tier 1 — Core ERP Modules**: in scope for Version 1; the operational backbone.
- **Tier 2 — Additional Modules**: in scope for Version 1; communication, website, payments.
- **Platform & Cross-Cutting Capabilities**: in scope for Version 1; system-wide services (Number Generator, Audit Logs, Global Search, Import & Export, Branding) used across all modules.
- **Tier 3 — Future Modules**: out of scope for Version 1; the architecture must allow them later.

Each module lists: **Purpose**, **Key Capabilities**, **Primary Users**, **Depends On**.
Permissions for each module are governed by [03-role-permission-matrix.md](03-role-permission-matrix.md).

---

## Tier 1 — Core ERP Modules

### 1. Authentication
**Purpose:** Securely identify and sign in staff, students, and parents; manage sessions and password lifecycle.
**Key Capabilities:** Multi-identity login (staff/student/parent); automatic account generation; temporary password; forced first-login password change; session management; password reset. (Full detail in [05-authentication-strategy.md](05-authentication-strategy.md).)
**Primary Users:** All.
**Depends On:** Users, Role & Permission, Communication (for credential delivery).

### 2. Dashboard
**Purpose:** Give each role an at-a-glance, personalized landing view.
**Key Capabilities:** Role-specific KPIs and cards; alerts; quick actions; charts; today's snapshot (attendance, fees, schedule); sidebar badges for pending items.
**Primary Users:** All.
**Depends On:** Most operational modules (read-only aggregation).

### 3. Admissions
**Purpose:** Manage the applicant lifecycle from enquiry/registration to enrollment.
**Key Capabilities:** Registration; confirmation; enrollment; reject/cancel; registration and admission fee capture; auto-provisioning of the student record, login, and admission-fee receipt on enrollment; registration/admission numbering. Operates as a controlled state machine: **register → confirm → enroll** (+ reject/cancel).
**Primary Users:** Administrator, Clerk, Receptionist.
**Depends On:** Students, Classes, Fee Structure, Fee Collection, Communication, Users.

### 4. Students
**Purpose:** Maintain the student master record across the school lifecycle.
**Key Capabilities:** Full student profile (personal, family, contact, medical, international/safeguarding, financial/concession); class/roll assignment; status lifecycle (active/transferred/passed-out/etc.); document linkage; auto-created login; drill-downs into fees, attendance, results, parents.
**Primary Users:** Administrator, Clerk, Teacher (own class), Supervisor, Student (self), Parent (own children).
**Depends On:** Classes, Parents, Documents, Users, Communication.

**Student Lifecycle:**
```
Admission → Enrollment → Active Student → Promotion → Next Academic Year
        → Graduation
        → Transfer Certificate
        → Withdrawal
        → Dropout
```

**Promotion & Progression Capabilities:**
- **Single Promotion** — promote an individual student to the next class/academic year.
- **Bulk Promotion** — promote a whole class/section in one operation.
- **Promotion Preview** — preview the outcome (target class, roll numbers, fee structure, subjects) before confirming.
- **Roll Number Regeneration** — regenerate roll numbers in the destination class/section.
- **Section Change** — move a student to a different section during promotion or mid-year.
- **Optional Subject Update** — update elective/optional subject choices on progression.
- **Fee Structure Update** — apply the destination class's fee structure on promotion.
- **Promotion History** — full, auditable record of every promotion event per student.
- **Promotion Rollback (before confirmation)** — revert an unconfirmed promotion batch.

**Lifecycle Exit States:** Graduation · Transfer Certificate (TC) · Withdrawal · Dropout — each closes the active enrollment, updates student status, and is recorded in the audit log.

### 5. Parents
**Purpose:** Maintain parent/guardian records and their relationship to students.
**Key Capabilities:** Parent profile and contact preferences; parent↔student linking with primary-contact designation; auto-created login; fee and child summaries.
**Primary Users:** Administrator, Clerk, Supervisor, Parent (self).
**Depends On:** Students, Users, Communication.

### 6. Teachers
**Purpose:** Manage teaching staff as an academic resource.
**Key Capabilities:** Teacher profiles; class-teacher and subject assignments; teaching schedule; lesson plans and logbook linkage; substitution participation.
**Primary Users:** Administrator, Supervisor.
**Depends On:** Staff/Users, Teacher Assignments, Timetable.

### 7. Staff
**Purpose:** Manage all employees (teaching and non-teaching).
**Key Capabilities:** Employee profiles; employee code; role assignment; status; emergency contacts; auto-created login.
**Primary Users:** Administrator.
**Depends On:** Users, Role & Permission, Communication.

### 8. Users
**Purpose:** The unified account directory underpinning every login.
**Key Capabilities:** Account records for staff, plus mirrored accounts for students and parents; status; credentials; profile/theme settings; account lifecycle.
**Primary Users:** Administrator, Super Admin.
**Depends On:** Role & Permission.

### 9. Role & Permission
**Purpose:** Define what every user may see and do.
**Key Capabilities:** Default roles; unlimited custom roles; per-module, per-action permissions (View, Create, Edit, Delete, Print, Export, Import, Approve, Publish, Lock, Unlock); role assignment; data-scope rules (own/assigned/all). (Full detail in [03-role-permission-matrix.md](03-role-permission-matrix.md).)
**Primary Users:** Super Admin, Administrator.
**Depends On:** Users.

### 10. Classes
**Purpose:** Define classes/grades and their attributes.
**Key Capabilities:** Class definition (grade level, academic year, class code, curriculum stage, medium, stream, shift); class teacher and assistant; capacity and room; computed strength; status.
**Primary Users:** Administrator.
**Depends On:** Teachers, Settings (academic year).

### 11. Sections
**Purpose:** Organize students within classes into sections.
**Key Capabilities:** Section definition under a class; section-level uniqueness with class + academic year; section-aware rosters and reporting.
**Primary Users:** Administrator.
**Depends On:** Classes.

### 12. Subjects
**Purpose:** Define subjects taught per class.
**Key Capabilities:** Subject definition (code, type, marks scheme — theory/practical/internal/external splits, pass marks); optional vs. core; subject groups; per-class uniqueness.
**Primary Users:** Administrator.
**Depends On:** Classes.

### 13. Teacher Assignments
**Purpose:** Map teachers to the classes and subjects they teach.
**Key Capabilities:** Teacher↔class↔subject↔year assignment; class-teacher flag; periods-per-week; bulk assignment; uniqueness per teacher/class/subject/year. Drives marks, attendance (subject-wise), timetable, and logbook permissions.
**Primary Users:** Administrator, Supervisor.
**Depends On:** Teachers, Classes, Subjects.

### 14. Attendance
**Purpose:** Record and track student attendance.
**Key Capabilities:** Daily and subject-wise/period-wise modes; bulk marking; status types (present/absent/late/half-day); attendance summaries and trends; teacher constraints (assigned classes, day window); admin lock/unlock of records.
**Primary Users:** Teacher, Supervisor, Administrator; read by Clerk, Student, Parent.
**Depends On:** Classes, Students, Subjects, Timetable, Calendar (holidays), Settings (working days).

### 15. Timetable
**Purpose:** Build and view class and teacher schedules.
**Key Capabilities:** Period-based weekly grid; subject/teacher assignment per slot; free periods/breaks; online/offline/hybrid modes with meeting links; slot uniqueness; teacher conflict detection; copy timetable; class and teacher views; "now" widget.
**Primary Users:** Administrator (write); all roles (scoped read).
**Depends On:** Classes, Subjects, Teacher Assignments, School Periods, Settings.

### 16. Lesson Planning
**Purpose:** Let teachers plan instruction and have it reviewed.
**Key Capabilities:** Lesson plans by class/subject/period; objectives, methods, resources, assessment plan; status; review workflow (reviewer + review status).
**Primary Users:** Teacher, Supervisor, Administrator.
**Depends On:** Teacher Assignments, Classes, Subjects.

### 17. Teaching Logbook
**Purpose:** Record what was actually taught each session.
**Key Capabilities:** Per-session log (topic, description, homework, due date, students present); same-day editing by the owning teacher; uniqueness per teacher/class/subject/date/period.
**Primary Users:** Teacher; read by Supervisor, Administrator, Student, Parent.
**Depends On:** Teacher Assignments, Classes, Subjects.

### 18. Parent-Teacher Meeting (PTM)
**Purpose:** Schedule and run parent-teacher meetings.
**Key Capabilities:** Teacher availability slots (with capacity, mode, meeting link); parent booking against linked children; overlap/capacity/duplicate guards; completion notes, minutes, action items, and parent rating; cancellation rules.
**Primary Users:** Teacher, Parent; oversight by Administrator, Supervisor.
**Depends On:** Teachers, Parents, Students, Parent↔Student links, Classes.

### 19. Teacher Substitutes
**Purpose:** Allocate substitute teachers when a teacher is absent.
**Key Capabilities:** Absence record with reason; per-period substitute allocations validated against the absent teacher's timetable; substitute-conflict checks; allocation status; leave document linkage.
**Primary Users:** Administrator, Supervisor; affected Teachers (read).
**Depends On:** Timetable, Teachers, Classes, Subjects.

### 20. Examinations
**Purpose:** Define exams and govern result publication.
**Key Capabilities:** Exam definitions (type, term, assessment type, grading scheme, curriculum stage, weightage, duration, passing criteria); publish/unpublish results; publish lock that reveals results downstream and freezes teacher edits.
**Primary Users:** Administrator (write); Teacher/Supervisor (read); Student/Parent (published only).
**Depends On:** Classes, Subjects.

### 21. Marks
**Purpose:** Capture and process student marks.
**Key Capabilities:** Bulk subject-wise mark entry; theory/practical/internal/external components; auto-grading; percentage and grade points; rank computation (tie-aware); moderation with original-mark audit; teacher edit gated by assignment and publish state.
**Primary Users:** Teacher (assigned), Administrator; read by Supervisor, Student, Parent.
**Depends On:** Examinations, Students, Subjects, Teacher Assignments.

### 22. Hall Tickets
**Purpose:** Issue exam admit cards.
**Key Capabilities:** Generate hall-ticket data for eligible exam/student; printable/exportable admit card with schedule and student details; scoped to own data for students/parents.
**Primary Users:** Administrator; Student, Parent (own).
**Depends On:** Examinations, Students, Timetable/Schedule, Settings (branding).

### 23. Discipline
**Purpose:** Record and manage student disciplinary incidents.
**Key Capabilities:** Incident logging (type, severity, location, witnesses, description, action taken); status workflow; parent-notification flag and toggle; student discipline history.
**Primary Users:** Teacher (own class), Supervisor, Administrator; read by Student, Parent.
**Depends On:** Students.

### 24. Conduct
**Purpose:** Periodic behaviour/conduct evaluation.
**Key Capabilities:** Conduct grades per evaluation period; sub-grades (punctuality, behavior, teamwork, leadership); uniqueness per student/period/label/year.
**Primary Users:** Teacher (own class), Supervisor, Administrator; read by Student, Parent.
**Depends On:** Students.

### 25. Activities
**Purpose:** Track co-curricular achievements.
**Key Capabilities:** Activity records (type, level, position, date, certificate); coach/mentor linkage.
**Primary Users:** Teacher, Administrator; read by Student, Parent, Supervisor.
**Depends On:** Students.

### 26. Fee Structure
**Purpose:** Define what fees a class owes and on what schedule.
**Key Capabilities:** Fee items by class/category/frequency/year; amount, due day, late fee, tax, installments, description; per-class/category/frequency/year uniqueness; active/inactive.
**Primary Users:** Administrator, Accountant, Clerk.
**Depends On:** Classes, Settings (academic year).

### 27. Fee Collection
**Purpose:** Collect fees and issue receipts.
**Key Capabilities:** Single and bulk payment capture; payment modes; auto receipt numbering; computed expected/due with late fee and discount; payment status (auto with admin override); online payment integration; receipt printing/email; refunds.
**Primary Users:** Accountant, Clerk, Administrator; Student/Parent (pay own/linked).
**Depends On:** Students, Fee Structure, Payment Gateway, Communication, Accounts.

### 28. Fee Dues
**Purpose:** Track outstanding monthly dues automatically.
**Key Capabilities:** Auto-generated monthly due slots from admission month to current month for active monthly fees; idempotent generation; due status (pending/paid/partial/waived); multi-month payment; outstanding-dues reporting.
**Primary Users:** Accountant, Clerk, Administrator; Student/Parent (view own/linked).
**Depends On:** Fee Structure, Fee Collection, Students.

### 29. Accounts
**Purpose:** Record non-fee income and expenses (day-book).
**Key Capabilities:** Income/expense transactions with categories and modes; daily summary; cash book and income/expense reporting; party and reference tracking.
**Primary Users:** Accountant, Administrator.
**Depends On:** Settings, Reports.

### 30. Inventory
**Purpose:** Manage consumable stock.
**Key Capabilities:** Stock items (codes, units, reorder/minimum levels, vendor, cost, expiry); in/out/adjustment transactions with stock recomputation; insufficient-stock guard; reorder alerts; optional approval for high-value issues.
**Primary Users:** Administrator, Clerk/Store keeper.
**Depends On:** Users (approver), Reports.

### 31. Assets
**Purpose:** Manage fixed assets and their upkeep.
**Key Capabilities:** Asset register (tag, category, purchase, vendor, warranty, location, assignment, condition, status, depreciation/current value); maintenance history with cost, warranty claims, next-due tracking.
**Primary Users:** Administrator, Clerk.
**Depends On:** Users (assignee), Reports.

### 32. Documents
**Purpose:** Store and verify documents attached to entities.
**Key Capabilities:** Polymorphic document linkage (student/parent/staff/asset); type classification; expiry tracking; verification workflow; file storage.
**Primary Users:** Administrator, Clerk, Teacher; scoped read for Student/Parent.
**Depends On:** Students, Parents, Staff, Assets.

### 33. Calendar
**Purpose:** Maintain the school's events and holidays.
**Key Capabilities:** Events (holiday/event/exam/meeting/sports/function/PTM/working-day) with dates, audience, class targeting, color, recurrence; holidays block attendance.
**Primary Users:** Administrator, Supervisor; read by all.
**Depends On:** Classes, Settings.

### 34. Complaints
**Purpose:** Capture and resolve complaints/grievances.
**Key Capabilities:** Polymorphic submitter; categories, priority, status workflow; anonymous option; assignment and resolution notes; complaint codes.
**Primary Users:** All (submit); Administrator/Clerk/Supervisor (manage).
**Depends On:** Users, Students (related), Communication.

### 35. Helpdesk
**Purpose:** Support tickets for student/parent issues.
**Key Capabilities:** Ticket raising (category, subject, description, priority); SLA due-by auto-derived from priority; status workflow; assignment and admin response; ticket codes.
**Primary Users:** Student, Parent (raise); Administrator, Clerk, Supervisor (manage).
**Depends On:** Students, Users, Communication.

### 36. Reports
**Purpose:** Provide operational and analytical reporting.
**Key Capabilities:** Finance (fee collection, outstanding dues, cash book, income/expense), academic (exam results/marksheet, attendance summary), roster (student roster, staff list), admissions pipeline, and activity/audit log reports; date-range and role-scoped; print/export.
**Primary Users:** Administrator, Accountant, Supervisor; Teacher (limited own-class academic reports).
**Depends On:** Source modules (Fees, Accounts, Exams/Marks, Attendance, Students, Staff, Admissions, Logs).

### 37. Settings
**Purpose:** Configure school-wide parameters.
**Key Capabilities:** School profile and branding; academic year and start/end dates; working days; currency; time zone; bell-schedule periods; module/system configuration; theme.
**Primary Users:** Administrator, Super Admin.
**Depends On:** All modules (configuration source).

**Settings Sections:** Settings is organized into the following sections:
| Section | Configures |
|---------|-----------|
| **General** | School profile, identity, contact, locale (currency, time zone, language). |
| **Academic** | Academic year, start/end dates, working days, terms, grading schemes. |
| **Attendance** | Attendance modes, lock policy, working-day rules. |
| **Examination** | Exam defaults, grading and publish rules, mark components. |
| **Fees** | Fee defaults, late-fee/discount/tax rules, dues generation. |
| **Communication** | Channel toggles, SMS/SMTP/Push gateway settings, templates. |
| **Payment Gateway** | Razorpay / PhonePe / Cashfree configuration, test/live mode. |
| **Branding** | All branding assets (see Branding capability). |
| **Security** | Password policy, account lockout, session, login/device history (see Security). |
| **Backup** | Backup configuration and recovery operations (Super Admin). |
| **System** | Number Generator formats, audit-log settings, module/system configuration. |

---

## Tier 2 — Additional Modules (Version 1)

### 38. Communication
**Purpose:** Central hub for all outbound messaging and templates.
**Key Capabilities:** Orchestrates Notices, SMS, Email, and Push; templates; custom, bulk, and scheduled messaging; logs. (Full detail in [06-communication-strategy.md](06-communication-strategy.md).)
**Primary Users:** Administrator, Clerk, Accountant (scoped).
**Depends On:** Notice Board, SMS, Email, Push Notification, Communication Logs, Settings (gateways).

### 39. Notice Board
**Purpose:** Publish notices across channels.
**Key Capabilities:** Compose notices with audience targeting; multi-destination publishing — Internal ERP, Website, Flutter App, Push, SMS, Email; acknowledgement option; priority and expiry.
**Primary Users:** Administrator, Supervisor, Teacher (class-specific).
**Depends On:** Communication, Website Integration, Push, SMS, Email.

### 40. SMS
**Purpose:** Send text messages via configured gateways.
**Key Capabilities:** Single, bulk, scheduled SMS; templates and custom messages; delivery logging; gateway settings (test/live).
**Primary Users:** Administrator, Clerk.
**Depends On:** Communication, Settings (SMS gateway), Communication Logs.

### 41. Email
**Purpose:** Send emails via configured SMTP.
**Key Capabilities:** Single, bulk, scheduled email; templates and custom messages; attachments (e.g., receipts); delivery logging; SMTP settings.
**Primary Users:** Administrator, Clerk, Accountant.
**Depends On:** Communication, Settings (SMTP), Communication Logs.

### 42. Push Notification
**Purpose:** Deliver real-time alerts to the mobile app.
**Key Capabilities:** Targeted and broadcast push; notice/event/fee/attendance triggers; push gateway settings; delivery logging.
**Primary Users:** Administrator; automated triggers.
**Depends On:** Communication, Mobile App, Settings (push), Communication Logs.

### 43. Website Integration
**Purpose:** Keep the public website in sync with the ERP for selected content.
**Key Capabilities:** One-way synchronization of **Public Notices**, **Photo Gallery**, and **Video Gallery** from ERP to website and app; automatic appearance on update. No website CMS, no separate ERP domain. (Full detail in [04-website-mobile-integration.md](04-website-mobile-integration.md).)
**Primary Users:** Administrator.
**Depends On:** Notice Board, Photo Gallery, Video Gallery.

### 44. Photo Gallery
**Purpose:** Manage school photo albums shared publicly.
**Key Capabilities:** Albums and images; publish to website and app; ordering and captions.
**Primary Users:** Administrator, Clerk.
**Depends On:** Website Integration, Documents/Storage.

### 45. Video Gallery
**Purpose:** Manage school videos shared publicly.
**Key Capabilities:** Video entries (links/uploads); publish to website and app; ordering and captions.
**Primary Users:** Administrator, Clerk.
**Depends On:** Website Integration, Storage.

### 46. Payment Gateway
**Purpose:** Enable online fee payment.
**Key Capabilities:** Pluggable gateways (Razorpay, PhonePe, Cashfree); test/live modes; transaction logs; refunds; reconciliation with Fee Collection. (Full detail in [07-payment-strategy.md](07-payment-strategy.md).)
**Primary Users:** Accountant, Administrator; Student/Parent (pay).
**Depends On:** Fee Collection, Settings (gateway config), Communication.

### 47. Communication Logs
**Purpose:** Audit and trace all communications.
**Key Capabilities:** Records of every SMS/Email/Push/Notice with recipient, channel, template, status, timestamps; filtering and reporting.
**Primary Users:** Administrator, Accountant (finance-related), Auditor.
**Depends On:** Communication and all channels.

---

## Platform & Cross-Cutting Capabilities (Version 1)

System-wide services used across all modules. **In scope for Version 1.**

### 48. Number Generator
**Purpose:** Centralized, configurable generation of all official numbers/codes.
**Key Capabilities:** Single source for **Admission Number, Staff Number, Parent ID, Receipt Number, Invoice Number, Complaint Number, Helpdesk Ticket Number, Asset Number, Visitor Pass Number**, and future numbers. Schools define **prefixes, suffixes, and numbering format** (width, year segment, reset policy) per number type. Enforces uniqueness and the Admission Number rule (numeric, ≤ 6 digits, unique).
**Primary Users:** Administrator (configure); system (generate).
**Depends On:** Settings (System), the consuming modules.

### 49. Audit Logs
**Purpose:** Tamper-evident record of all material actions across the ERP.
**Key Capabilities:** Records — **Login, Logout, Failed Login, Password Reset, User Creation, Student Update, Attendance Unlock, Fee Collection, Result Publish, Role Changes, Permission Changes, System Settings Changes, Communication Events, Payment Events** (and other sensitive actions). **Searchable, filterable, and exportable.** Captures actor, action, target, timestamp, and context.
**Primary Users:** Super Admin, Administrator, Auditor.
**Depends On:** All modules (event sources), Security.

### 50. Global Search
**Purpose:** Fast, permission-scoped search available from **every page**.
**Key Capabilities:** Search across **Students, Parents, Staff, Admissions, Fees, Receipts, Complaints, Helpdesk, Assets, Inventory, Documents**. Results respect the user's role and data scope; users jump directly to records.
**Primary Users:** All staff roles (scoped).
**Depends On:** The searched modules, Role & Permission.

### 51. Import & Export
**Purpose:** Bulk data movement in and out of the ERP.
**Key Capabilities:** Import and export for **Students, Parents, Staff, Subjects, Classes, Inventory, Assets, Attendance, Marks, Fee Structures, and Reports**. Validation on import; standard export formats; gated via the Import/Export permission actions; events recorded in Audit Logs.
**Primary Users:** Administrator, Clerk, Accountant (scoped).
**Depends On:** The target modules, Role & Permission, Audit Logs.

### 52. Branding
**Purpose:** Centralized management of the school's visual identity assets used across web, mobile, public website, and printed output.
**Key Capabilities:** Manage — **School Logo, Dark Logo, Favicon, Login Background, Theme Color, School Motto, Principal Signature, School Stamp, Report Logos, Receipt Logo, ID Card Logo**. Assets are applied to dashboards, login, the mobile app, public-website sync, and printable documents (receipts, hall tickets, ID cards, reports).
**Primary Users:** Administrator.
**Depends On:** Settings (Branding section), Website Integration, Documents/Reports.

---

## Tier 3 — Future Modules (Out of Scope for V1)

The architecture must accommodate these without redesign. They are deferred by roadmap decision.

| Module | Intended Purpose (future) |
|--------|---------------------------|
| **Library** | Catalog, issue/return, fines, member management. |
| **Transport** | Routes, stops, vehicles, driver assignment, fees, tracking. |
| **Hostel** | Rooms, allocations, wardens, mess, hostel fees. |
| **Payroll** | Salary structures, attendance-linked pay, payslips, statutory deductions. |
| **Visitor Management** | Visitor logging, passes, appointments, gate security. |
| **Biometric Attendance** | Device-integrated staff/student attendance capture. |
| **AI Analytics** | Predictive insights — at-risk students, fee defaults, performance trends. |
| **Multi-school SaaS** | Many schools on shared, isolated infrastructure. |
| **Multi-branch** | Multiple branches/campuses under one school entity. |

See [09-product-roadmap.md](09-product-roadmap.md) for sequencing.

---

## Module Dependency Overview (high level)

```
Settings · Role & Permission · Users        ← foundation (everything depends on these)
        ↓
Classes → Sections → Subjects → Teacher Assignments
        ↓
Students ↔ Parents (links) → Admissions
        ↓
Timetable · Attendance · Substitutes        (daily academic ops)
Examinations → Marks → Hall Tickets ; Conduct · Discipline · Activities
Fee Structure → Fee Collection → Fee Dues ; Accounts ; Payment Gateway
Calendar · Documents · Inventory · Assets
Communication → Notice Board / SMS / Email / Push → Website Integration (Gallery)
        ↓
Reports · Dashboard · Communication Logs     (consume all of the above)
```
