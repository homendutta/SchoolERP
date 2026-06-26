# 01 – System-Wide Requirements

**Product:** SchoolERP
**Document type:** SRS — System-Wide (Cross-Cutting) Requirements
**Status:** Approved (Framework)
**Version:** 1.0
**Last updated:** 2026-06-26

> These requirements apply to **every module**. Module specifications **reference** these IDs rather
> than restating them. They derive from the approved PRD and System Architecture Blueprint and add no
> new product scope. Conventions and ID scheme are defined in
> [00-introduction.md](00-introduction.md).
>
> Priority key: **M** = Must · **S** = Should · **C** = Could (MoSCoW). Verification key: **T** = Test
> · **D** = Demonstration · **I** = Inspection · **A** = Analysis.

---

## 1. Authentication (SYS-AUTH)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-AUTH-001 | The system shall provide a single unified login that resolves the supplied identifier to one of the three identity types (staff, student, parent) and authenticates accordingly. | M | T | PRD 05 |
| SYS-AUTH-002 | The system shall allow staff to authenticate using Staff Number, Mobile Number, or Email Address. | M | T | PRD 05 |
| SYS-AUTH-003 | The system shall allow students to authenticate using Admission Number only. | M | T | PRD 05 |
| SYS-AUTH-004 | The system shall allow parents to authenticate using Parent ID, Mobile Number, or Email Address. | M | T | PRD 05 |
| SYS-AUTH-005 | The system shall reject login for deleted accounts and for accounts not in an active status, with an appropriate message. | M | T | PRD 05 / BR-Index |
| SYS-AUTH-006 | The system shall force a password change at first login when a temporary password is used. | M | T | PRD 05 |
| SYS-AUTH-007 | The system shall enforce the Super Admin as a system-level identity distinct from the school Administrator. | M | I | PRD 05 |
| SYS-AUTH-008 | The system shall apply identical authentication and session behaviour to the web and mobile clients. | M | T | Arch 06/07 |

---

## 2. Account Provisioning (SYS-ACCT)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-ACCT-001 | On successful creation of a staff, student, or parent record, the system shall automatically create a login account with a system-generated temporary password. | M | T | PRD 05 |
| SYS-ACCT-002 | The system shall send account credentials via SMS and/or Email when those channels are enabled, and record a communication log for each send. | M | T | PRD 05/06 |
| SYS-ACCT-003 | The system shall record an audit log entry for each automatic account creation. | M | T | PRD 05 |
| SYS-ACCT-004 | Account provisioning shall not be blocked by a disabled or failing communication channel; such failures shall be logged. | M | T | PRD 05/06 |

---

## 3. Authorization — RBAC & Data Scope (SYS-RBAC)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-RBAC-001 | The system shall enforce role-based access control on every action using the permission actions View, Create, Edit, Delete, Print, Export, Import, Approve, Publish, Lock, Unlock. | M | T | PRD 03 |
| SYS-RBAC-002 | The system shall enforce data scope (own, linked, assigned, all) in addition to the action grant on every request. | M | T | PRD 03 |
| SYS-RBAC-003 | The system shall enforce authorization server-side and treat any client-side gating as advisory only. | M | T | Arch 06/07 |
| SYS-RBAC-004 | The system shall support the default roles (Super Admin, Administrator, Supervisor, Accountant, Clerk, Receptionist, Teacher, Student, Parent) and unlimited custom roles. | M | T | PRD 03 |
| SYS-RBAC-005 | The system shall default custom roles to no permissions, granting access only explicitly (least privilege). | M | T | PRD 03 |
| SYS-RBAC-006 | The system shall restrict students to own data and parents to linked children's data, non-configurably. | M | T | PRD 03 |
| SYS-RBAC-007 | The system shall restrict teacher write actions to assigned classes and class+subject pairs. | M | T | PRD 03 / BR-Index |
| SYS-RBAC-008 | The system shall audit all role and permission changes. | M | T | PRD 03 |

---

## 4. Audit Logging (SYS-AUD)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-AUD-001 | The system shall record an audit entry for every material action, capturing actor, action, target, timestamp, and context. | M | T | PRD 02 / Arch 12 |
| SYS-AUD-002 | The system shall record at minimum: login, logout, failed login, password reset, user creation, student update, attendance unlock, fee collection, result publish, role changes, permission changes, system settings changes, communication events, and payment events. | M | T | PRD 02 |
| SYS-AUD-003 | The system shall make audit logs searchable, filterable, and exportable. | M | T | PRD 02 |
| SYS-AUD-004 | The system shall protect audit logs from modification (append-oriented). | M | A | Arch 12 |
| SYS-AUD-005 | The system shall never record secrets, credentials, tokens, or payment-instrument data in any log. | M | I | Arch 07/12 |

---

## 5. Notifications & Communication (SYS-NOT)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-NOT-001 | All outbound communication shall be dispatched through the single centralized Notification Service. | M | I | Arch 09 |
| SYS-NOT-002 | The system shall support Notice, SMS, Email, and Push channels, each independently enabled/disabled per school. | M | T | PRD 06 |
| SYS-NOT-003 | The system shall support reusable SMS and Email templates plus custom messages, with per-recipient personalization. | M | T | PRD 06 |
| SYS-NOT-004 | The system shall support bulk and scheduled SMS/Email. | M | T | PRD 06 |
| SYS-NOT-005 | The system shall record a communication log entry (channel, recipient, content reference, status, timestamps) for every send. | M | T | PRD 06 |
| SYS-NOT-006 | A notice shall be publishable to any selected subset of destinations: Internal ERP, Website, Flutter App, Push, SMS, Email. | M | T | PRD 06 |
| SYS-NOT-007 | Communication failures shall degrade gracefully and never block the underlying operation. | M | T | PRD 06 |
| SYS-NOT-008 | The notification architecture shall allow new channels (e.g., WhatsApp) to be added as drivers without redesign. | S | A | PRD 06 / Arch 09 |

---

## 6. Media Library (SYS-MED)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-MED-001 | All files shall be stored and served through the single Media Library. | M | I | Arch 08 |
| SYS-MED-002 | The Media Library shall validate uploads (type, size, content) and return a reference rather than a raw storage path. | M | T | Arch 08 |
| SYS-MED-003 | The system shall serve protected media only to authorized users within their data scope. | M | T | Arch 08 |
| SYS-MED-004 | The system shall expose publicly only media explicitly published as public (gallery, public branding). | M | T | Arch 08 |

---

## 7. Number Generator (SYS-NUM)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-NUM-001 | All official numbers/codes shall be issued by the centralized Number Generator. | M | I | PRD 02 |
| SYS-NUM-002 | The system shall allow schools to configure prefix, suffix, and numbering format per number type. | M | T | PRD 02 |
| SYS-NUM-003 | The system shall guarantee uniqueness of generated numbers within their type. | M | T | PRD 02 |
| SYS-NUM-004 | The system shall enforce the Admission Number rule: numeric, maximum 6 digits, unique. | M | T | PRD 05/02 |

---

## 8. Global Search (SYS-SRCH)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-SRCH-001 | The system shall provide a global search available from every page. | M | D | PRD 02 |
| SYS-SRCH-002 | Global search shall cover Students, Parents, Staff, Admissions, Fees, Receipts, Complaints, Helpdesk, Assets, Inventory, and Documents. | M | T | PRD 02 |
| SYS-SRCH-003 | Global search results shall respect the user's role and data scope. | M | T | PRD 02 / Arch 07 |

---

## 9. Import & Export (SYS-IMP)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-IMP-001 | The system shall provide import and export for Students, Parents, Staff, Subjects, Classes, Inventory, Assets, Attendance, Marks, Fee Structures, and Reports. | M | T | PRD 02 |
| SYS-IMP-002 | Import shall validate data before committing and report errors per row. | M | T | PRD 02 / BR-Index |
| SYS-IMP-003 | Import and export actions shall be gated by the Import/Export permissions and recorded in the audit log. | M | T | PRD 03 / Arch 12 |
| SYS-IMP-004 | Large import/export operations shall be processed asynchronously with trackable status. | S | T | Arch 10 |

---

## 10. UI Preservation (SYS-UI)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-UI-001 | The system shall preserve the reference application's overall UI/UX: sidebar layout, dashboard layout, navigation flow, page hierarchy, cards, tables, dialogs, user workflows, and overall experience. | M | I/D | PRD 08 §0 |
| SYS-UI-002 | The system shall never reuse code from the reference application; only the experience is preserved. | M | I | PRD 08 §0 |
| SYS-UI-003 | The system shall present role-adaptive menus and dashboards, showing only permitted modules and actions. | M | T | PRD 04/08 |
| SYS-UI-004 | The system shall provide a consistent design system (cards, tables, forms, dialogs, charts) reused across all modules and both clients. | M | I | Arch 04/05 |
| SYS-UI-005 | The system shall apply school branding assets and light/dark theming across surfaces. | S | D | PRD 02/08 |

---

## 11. API & Clients (SYS-API)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-API-001 | A single API shall serve the React web app and the Flutter app identically. | M | I | Arch 06 |
| SYS-API-002 | The API shall use consistent conventions for response envelope, errors, pagination, filtering, sorting, and versioning across all modules. | M | I | Arch 06 |
| SYS-API-003 | Operations with external effects (payments, communications) shall be idempotent. | M | T | Arch 06/10 |
| SYS-API-004 | All client–API and provider traffic shall use HTTPS. | M | I | Arch 07/13 |
| SYS-API-005 | The system shall expose a one-way, read-only outward feed for Public Notices, Photo Gallery, and Video Gallery to the public website/app. | M | T | PRD 04 / Arch 06 |

---

## 12. Security (SYS-SEC)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-SEC-001 | The system shall enforce a configurable password policy (complexity, length, expiry, reuse). | M | T | PRD 05 |
| SYS-SEC-002 | The system shall lock accounts temporarily after repeated failed login attempts. | M | T | PRD 05 |
| SYS-SEC-003 | The system shall maintain per-user login history and device history. | M | T | PRD 05 |
| SYS-SEC-004 | The system shall provide session management with bounded lifetime and the ability to view/terminate active sessions, consistent across web and mobile. | M | T | PRD 05 |
| SYS-SEC-005 | The system shall store credentials using strong one-way hashing and never display them. | M | I | Arch 07 |
| SYS-SEC-006 | The system shall store gateway/SMTP/SMS/push/payment secrets securely and never expose them in UI or logs. | M | I | PRD 06/07 / Arch 07 |
| SYS-SEC-007 | The system shall not store payment-instrument data, delegating payment to the gateway. | M | I | PRD 07 |
| SYS-SEC-008 | The system shall enforce the permanent business-rule guards (self-delete protection, admission state gating, exam publish lock, attendance lock, scope limits) regardless of role configuration. | M | T | PRD 03 / BR-Index |

---

## 13. Payments (SYS-PAY)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-PAY-001 | The system shall support online payment via Razorpay, PhonePe, and Cashfree, with new gateways pluggable without redesign. | M | T | PRD 07 |
| SYS-PAY-002 | The system shall support Test and Live modes per gateway, clearly indicated to finance users. | M | T | PRD 07 |
| SYS-PAY-003 | The system shall log every payment attempt and result, and every refund, as transactions, and audit them. | M | T | PRD 07 |
| SYS-PAY-004 | Successful online payments shall reconcile into Fee Collection and update Fee Dues identically to offline payments. | M | T | PRD 07 |

---

## 14. Logging & Monitoring (SYS-LOG)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-LOG-001 | The system shall maintain distinct application, audit, communication, transaction, and access/security logs. | M | I | Arch 12 |
| SYS-LOG-002 | The system shall use structured logging with a correlation identifier linking a request to its jobs and side effects. | S | I | Arch 12 |
| SYS-LOG-003 | The system shall expose health/status for monitoring and alert on error spikes, job failures, and provider outages. | S | D | Arch 12 |

---

## 15. Background Processing (SYS-JOB)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-JOB-001 | The system shall process heavy, bulk, and scheduled work asynchronously via queues and a scheduler. | M | I | Arch 10 |
| SYS-JOB-002 | Asynchronous jobs with external effects shall be idempotent and retried with backoff on failure. | M | T | Arch 10 |
| SYS-JOB-003 | Scheduled jobs (e.g., monthly fee dues, scheduled messages, reminders) shall execute per configuration and be auditable. | M | T | Arch 10 / BR-Index |
| SYS-JOB-004 | Jobs shall carry actor/context so audit and data scope remain correct off-request. | M | A | Arch 10 |

---

## 16. Caching (SYS-CACHE)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-CACHE-001 | The system shall cache hot, slow-changing data and shall never serve data outside a user's scope from cache. | M | T | Arch 11 |
| SYS-CACHE-002 | The system shall invalidate affected caches on the domain events produced by create/update/delete operations. | M | T | Arch 11 |
| SYS-CACHE-003 | Critical financial and academic states shall not be served stale. | M | T | Arch 11 |

---

## 17. Localization & Configuration (SYS-CFG)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-CFG-001 | The system shall be locale-ready: currency, date/time, academic-year formats, and language are configurable/extensible. | S | T | PRD 00/08 |
| SYS-CFG-002 | The system shall organize Settings into the sections General, Academic, Attendance, Examination, Fees, Communication, Payment Gateway, Branding, Security, Backup, System. | M | I | PRD 02 |
| SYS-CFG-003 | The system shall source gateways, channels, storage, and limits from configuration, never hard-coded. | M | I | Arch 03/13 |

---

## 18. Deployment & Tenancy (SYS-DEP)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-DEP-001 | The system shall deploy as a single-tenant installation: one installation, one database, one domain per school. | M | I | PRD 00 / Arch 13 |
| SYS-DEP-002 | The public website and the ERP shall share one domain, with the ERP under /login, /admin, /teacher, /student, /parent and the API under its path. | M | D | PRD 04 / Arch 13 |
| SYS-DEP-003 | The system shall not hard-code single-tenant assumptions into core logic, remaining ready for multi-school SaaS and multi-branch without redesign. | M | A | PRD 00 / Arch 13 |
| SYS-DEP-004 | The system shall include the database, media, and configuration in backup scope. | M | I | Arch 13 |

---

## 19. Website & Mobile Integration (SYS-INT)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-INT-001 | The system shall synchronize only Public Notices, Photo Gallery, and Video Gallery outward to the public website and the mobile app. | M | T | PRD 04 |
| SYS-INT-002 | Published gallery/notice updates shall appear automatically on both the website and the app. | M | T | PRD 04 |
| SYS-INT-003 | The system shall provide exactly one Flutter app that adapts dashboards and menus to the signed-in user's role and permissions. | M | D | PRD 04 |
| SYS-INT-004 | The system shall not provide a website CMS and shall not use a separate ERP domain. | M | I | PRD 04 |

---

## 20. Non-Functional Requirements (SYS-NFR)

| ID | Requirement | Pri | Verify | Source |
|----|-------------|:--:|:--:|--------|
| SYS-NFR-001 | The system shall remain responsive for common workflows under a full school's data volume on web and mid-range mobile devices. | M | A/T | PRD 00 |
| SYS-NFR-002 | The system shall target high availability during school hours and degrade gracefully when an external provider is unavailable. | M | A | PRD 00 / Arch 13 |
| SYS-NFR-003 | The system shall protect student, parent, financial, and safeguarding data per the security architecture. | M | I | PRD 00 / Arch 07 |
| SYS-NFR-004 | The system shall be usable on a phone for every primary workflow (mobile-first). | M | D | PRD 00/08 |
| SYS-NFR-005 | The system shall meet accessibility expectations (readable typography, contrast, keyboard navigation, clear error messaging). | S | T | PRD 08 |
| SYS-NFR-006 | The system shall be maintainable per the coding standards and modular architecture. | M | I | Arch 02/14 |
| SYS-NFR-007 | The system shall be scalable horizontally for the API and workers without redesign. | S | A | Arch 13 |
| SYS-NFR-008 | The system shall provide consistent capability and behaviour across the web and mobile clients. | M | T | Arch 04/05/06 |

---

## 21. Applicability & Reuse

- Every module specification **inherits** these system-wide requirements and **references** the relevant IDs in its sections (e.g., a module's Permissions section cites SYS-RBAC-001/002; its Audit section cites SYS-AUD-001).
- Module specifications **do not restate** system-wide requirements; they add only module-specific requirements.
- If a module needs an exception to a system-wide requirement, it must be justified by the PRD/Architecture and recorded explicitly — exceptions are rare and traceable.
