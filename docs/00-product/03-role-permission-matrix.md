# 03 – Role & Permission Matrix

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md) and
> [02-module-catalog.md](02-module-catalog.md). Defines the role model, the grantable permission
> actions, data-scoping rules, and the complete default permission matrix. This is a **product**
> specification of access policy — not a database or API design.

---

## 1. Role Model

### 1.1 Default Roles (Version 1)

| Role | Description | Scope of authority |
|------|-------------|--------------------|
| **Super Admin** | System owner / vendor. Operates the installation itself. | Licensing, system updates, global configuration, backups, system administration. Above the school. |
| **Administrator** | The school's top operational user (Principal/Head). | Full control of all school modules; approve, publish, lock. |
| **Supervisor** | Academic coordinator / vice-principal. | Academic oversight — attendance, exams, results, conduct, discipline; limited admin. |
| **Clerk** | Front-office administrative staff. | Admissions, student/parent records, fees, documents, helpdesk. |
| **Accountant** | Finance staff. | Fee structure, collection, dues, accounts, payments, refunds, finance reports. |
| **Receptionist** | Front-desk staff. | Enquiries, basic record lookups, visitor-facing info, helpdesk intake, notices read. |
| **Teacher** | Class/subject teacher. | Own assigned classes/subjects — attendance, marks, timetable, lesson plans, logbook, conduct, discipline, PTM. |
| **Student** | Enrolled student. | Self-service read of own academics; raise helpdesk/complaints; pay/view own fees. |
| **Parent** | Parent/guardian. | View linked children's academics and fees; pay online; book PTM; raise helpdesk/complaints. |

### 1.2 Future Roles (reserved, not in V1)
Librarian · Transport Manager · Hostel Warden · HR Manager · Examination Controller · Sports
Coordinator. The role system must allow these to be added later without redesign.

### 1.3 Custom Roles
Schools can create **unlimited custom roles**. A custom role is any named role with an arbitrary
selection of module/action permissions and a data scope. Custom roles are first-class: they appear in
assignment, the matrix UI, and audit just like default roles.

---

## 2. Permission Actions

Every module permission is expressed as a combination of these grantable actions:

| Action | Meaning |
|--------|---------|
| **View** | Read/list records in the module. |
| **Create** | Add new records. |
| **Edit** | Modify existing records. |
| **Delete** | Remove records (soft or hard per module policy). |
| **Print** | Produce printable output (receipts, hall tickets, reports). |
| **Export** | Export data (e.g., CSV/PDF) out of the module. |
| **Import** | Bulk-load data into the module. |
| **Approve** | Authorize a record/workflow step (e.g., admission confirm, stock issue, lesson plan review). |
| **Publish** | Make content visible downstream (notices, exam results, gallery). |
| **Lock** | Freeze records against edits (attendance, results). |
| **Unlock** | Release a lock (typically Administrator-only). |

> Not every action applies to every module. The matrix marks applicable actions; inapplicable actions
> are simply not grantable for that module.

---

## 3. Data-Scoping Rules

Permissions are further constrained by **data scope**, independent of the action grant:

| Scope | Applies to | Meaning |
|-------|-----------|---------|
| **All** | Administrator, Supervisor, Clerk, Accountant, Receptionist (per module) | Access to all records in the module. |
| **Assigned** | Teacher | Limited to the teacher's assigned classes and class+subject pairs. |
| **Own** | Student | Limited to the student's own records and own class. |
| **Linked** | Parent | Limited to the parent's linked children and their classes. |
| **System** | Super Admin | Installation-level configuration, not school content. |

Scope is enforced **in addition to** the action grant: e.g., a Teacher with View+Edit on Marks may
only View/Edit marks for assigned class+subject.

---

## 4. Module Access Matrix (capability level)

Legend: **F** = Full (all applicable actions) · **M** = Manage (View/Create/Edit, no Delete/Approve
unless noted) · **R** = Read-only (View, often Print/Export) · **Rs** = Read-only, scoped (Own/Linked/Assigned)
· **W** = Write, scoped (Assigned) · **—** = No access · **Sys** = System-level only.

| Module | Super Admin | Admin | Supervisor | Clerk | Accountant | Receptionist | Teacher | Student | Parent |
|--------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Authentication (self) | F | F | F | F | F | F | F | F | F |
| Dashboard | Sys | F | R | R | R | R | R | Rs | Rs |
| Role & Permission | F | M | — | — | — | — | — | — | — |
| Users | F | F | — | — | — | — | — | — | — |
| Staff | Sys | F | R | R | — | R | — | — | — |
| Teachers | Sys | F | R | R | — | R | Rs | — | — |
| Students | Sys | F | R | M | R | R | Rs (assigned) | Rs (own) | Rs (linked) |
| Parents | Sys | F | R | M | R | R | Rs | — | Rs (own) |
| Admissions | — | F | R | F | R | M | — | — | — |
| Classes | Sys | F | R | R | — | R | R | R | R |
| Sections | Sys | F | R | R | — | R | R | R | R |
| Subjects | Sys | F | R | — | — | — | R | Rs | Rs |
| Teacher Assignments | Sys | F | M | — | — | — | Rs | — | — |
| Attendance | — | F | M | R | — | — | W (assigned) | Rs (own) | Rs (linked) |
| Timetable | Sys | F | R | R | — | R | Rs | Rs | Rs |
| Lesson Planning | — | M | M (review) | — | — | — | W | — | — |
| Teaching Logbook | — | R | R | — | — | — | W | Rs | Rs |
| Parent-Teacher Meeting | — | M | M | — | — | — | W (own slots) | — | Rs+Book |
| Teacher Substitutes | — | F | M | — | — | — | Rs | — | — |
| Examinations | — | F | R | — | — | — | R | Rs (published) | Rs (published) |
| Marks | — | F | R | — | — | — | W (assigned) | Rs (own) | Rs (linked) |
| Hall Tickets | — | M | R | R | — | R | — | Rs (own) | Rs (linked) |
| Discipline | — | F | M | — | — | — | W (assigned) | Rs (own) | Rs (linked) |
| Conduct | — | F | M | — | — | — | W (assigned) | Rs (own) | Rs (linked) |
| Activities | — | F | R | — | — | — | M | Rs (own) | Rs (linked) |
| Fee Structure | — | F | — | M | F | — | — | Rs | Rs |
| Fee Collection | — | F | — | M | F | R | — | Rs+Pay | Rs+Pay |
| Fee Dues | — | F | — | M | F | R | — | Rs | Rs |
| Accounts | — | F | — | — | F | — | — | — | — |
| Inventory | — | F | — | M | R | — | — | — | — |
| Assets | — | F | — | M | R | — | — | — | — |
| Documents | — | F | R | M | R | R | Rs (assigned) | Rs (own) | Rs (linked) |
| Calendar | Sys | F | M | R | R | R | R | R | R |
| Complaints | — | M | M | M | — | M | M (own) | M (own) | M (own) |
| Helpdesk | — | F | M | F | — | M | — | M (own) | M (own) |
| Reports | Sys | F | R (academic) | R (admissions/roster) | R (finance) | R (limited) | R (own-class academic) | — | — |
| Settings | Sys | F | — | — | R (gateway scope) | — | — | — | — |
| Communication | — | F | M | M | M (finance) | M | M (class notices) | — | — |
| Notice Board | — | F (publish) | M (publish) | M | — | R | W (class-specific) | R | R |
| SMS / Email / Push | — | F | M | M | M (finance) | — | — | — | — |
| Website Integration | — | F (publish) | — | M | — | — | — | — | — |
| Photo / Video Gallery | — | F (publish) | — | M | — | — | — | R | R |
| Payment Gateway | Sys | F | — | — | M | — | — | — | — |
| Communication Logs | F | R | R | R | R (finance) | — | — | — | — |
| Global Search | Sys | F | R | R | R | R | Rs (assigned) | — | — |
| Import & Export | Sys | F | R | M | M (finance) | — | — | — | — |
| Number Generator | Sys | F | — | — | — | — | — | — | — |
| Audit Logs | F | R | R | — | R (finance) | — | — | — | — |
| Branding | Sys | F | — | — | — | — | — | — | — |

> The matrix above is the **default** grant. Administrators (and Super Admin) may adjust grants and
> create custom roles. Where a cell shows a scope in parentheses, the data-scope rule (§3) applies.

---

## 5. Action Applicability by Module (reference)

Which permission actions are meaningful per module (used when configuring roles):

| Module | View | Create | Edit | Delete | Print | Export | Import | Approve | Publish | Lock | Unlock |
|--------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Admissions | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ (confirm/enroll) | — | — | — |
| Students | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Parents | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Staff/Users | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Role & Permission | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | — | — | — | — |
| Classes/Sections/Subjects | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Teacher Assignments | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ | — | — | — | — |
| Attendance | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | ✓ | ✓ |
| Timetable | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — |
| Lesson Planning | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ (review) | — | — | — |
| Teaching Logbook | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — |
| PTM | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | — | — | — | — |
| Substitutes | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ (confirm) | — | — | — |
| Examinations | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | ✓ (results) | ✓ | ✓ |
| Marks | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ (moderate) | — | ✓ | ✓ |
| Hall Tickets | ✓ | ✓ | — | — | ✓ | ✓ | — | — | — | — | — |
| Discipline/Conduct/Activities | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — |
| Fee Structure | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Fee Collection/Dues | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ (refund) | — | — | — |
| Accounts | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Inventory | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ (issue) | — | — | — |
| Assets | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — | — |
| Documents | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ (verify) | — | — | — |
| Calendar | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | ✓ | — | — |
| Complaints/Helpdesk | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ (resolve) | — | — | — |
| Reports | ✓ | — | — | — | ✓ | ✓ | — | — | — | — | — |
| Notice Board | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ | — | — |
| Communication (SMS/Email/Push) | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ | ✓ (schedule) | ✓ | — | — |
| Gallery (Photo/Video) | ✓ | ✓ | ✓ | ✓ | — | — | ✓ | — | ✓ | — | — |
| Payment Gateway | ✓ | ✓ | ✓ | — | — | ✓ | — | — | — | — | — |
| Settings | ✓ | — | ✓ | — | — | ✓ | ✓ | — | — | — | — |
| Global Search | ✓ | — | — | — | — | — | — | — | — | — | — |
| Import & Export | ✓ | — | — | — | — | ✓ | ✓ | — | — | — | — |
| Number Generator | ✓ | ✓ | ✓ | — | — | ✓ | — | — | — | — | — |
| Audit Logs | ✓ | — | — | — | ✓ | ✓ | — | — | — | — | — |
| Branding | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ | — | ✓ | — | — |

---

## 6. Special Permission Rules (carried from validated workflows)

These reflect proven rules that must be enforced regardless of role configuration:

1. **Self-protection** — a user cannot delete their own account.
2. **Admission workflow gating** — Confirm requires "registered" state; Enroll requires "admitted"; reject/cancel only from registered/admitted. These are **Approve**-type actions.
3. **Exam publish lock** — only roles with **Publish** on Examinations may publish; publishing **Locks** Marks for teachers; only **Unlock** (Administrator) re-opens editing.
4. **Attendance lock** — only roles with **Lock/Unlock** on Attendance (Administrator) may freeze/release; teachers cannot edit locked rows.
5. **Teacher scoping** — Teacher write actions (Attendance, Marks, Logbook, Discipline, Conduct) are always limited to **Assigned** classes/subjects.
6. **Student/Parent scoping** — always **Own**/**Linked**; students/parents never gain All scope through configuration.
7. **Class-specific notices** — Teachers may only **Publish** notices to classes they teach.
8. **Finance approvals** — refunds and high-value stock issues require **Approve** by an authorized role (Administrator/Accountant).
9. **Audit** — permission changes themselves are audited.

---

## 7. Permission Governance

- **Who can manage permissions:** Super Admin (system) and Administrator (school). Supervisors and others never edit the permission model unless explicitly granted.
- **Least privilege:** default custom roles start with no permissions; access is granted explicitly.
- **Change audit:** every role/permission change is recorded in the audit log (actor, target role, change, timestamp).
- **Consistency across surfaces:** the same permission set governs web and the Flutter mobile app; menus and actions appear only where permitted.
