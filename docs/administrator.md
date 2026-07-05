# Administrator Guide

## Roles & permissions
RBAC is enforced **server-side** (the `permission:<slug>` middleware). The React
sidebar only reflects what a role can see. Manage roles/users under Administration.
Super admins bypass permission checks.

## Modules at a glance
- **Academic / Admissions / Students / Staff** — the master records.
- **Attendance / Timetable / Examination** — daily academics.
- **Finance & Fees / HR / Payroll** — money + people. Finance owns payments; Payroll
  consumes HR/Attendance and never edits them.
- **Library / Transport / Hostel / Inventory** — operations.
- **Communication** — every notification flows through the Communication Engine.
- **Website CMS** — the public site's dynamic content + enquiries.
- **Portal** — parent/student/teacher self-service (consumes other modules; isolated).
- **LMS** — lessons, homework, assignments, quizzes (independent of Examination).
- **Documents** — certificates/templates + QR verification (single source of truth).
- **Reports** — the one reporting/printing/export engine.
- **Integrations** — the single gateway to third-party providers.
- **System** — production dashboard, health, diagnostics, backups.

## Common tasks
- **Publish a notice/news/event:** Website CMS → create → set status *published*.
- **Generate a certificate:** Documents → Generate → pick template + subject.
- **Run a report / export:** Reports → Report Viewer → run → CSV/Excel/Print.
- **Configure a provider:** Integrations → Providers (config is encrypted at rest).
- **Check system health:** Administration → Production Dashboard.

## Operational hygiene
- Watch **Failed Jobs** and the **retry queue** on the Production Dashboard.
- Keep **integration health** green (run provider health checks).
- Record + verify **backups** regularly.
- Every administrative + business action is written to the **Audit Log**; timelines
  record lifecycle events per student/staff.
