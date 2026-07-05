# API Guide

## Conventions
- Base path: `/api/v1`. JSON in/out.
- **Auth:** Laravel Sanctum bearer token. `POST /api/v1/auth/login` → `{ token }`;
  send `Authorization: Bearer <token>`.
- **RBAC:** protected routes declare `permission:<slug>`; enforced server-side. Super
  admins bypass.
- **Envelope:** success `{ "data": …, "message"?, "meta"? }`; errors
  `{ "message", "code", "errors"? }` with the appropriate HTTP status.
- **Pagination:** list endpoints accept `page` + `per_page` and return
  `meta.{total,per_page,current_page,last_page}`.
- **Search:** `search[field]=value` (declarative Search Builder); `filter[field]=value`;
  `sort=field` / `sort=-field`.

## Public (no auth, throttled)
- `GET /api/v1/health` — liveness/readiness probe.
- `GET /api/v1/cms/public/*` — published website content; `POST …/forms|enquiries`.
- `POST /api/v1/public/document/verify` — certificate verification.
- `POST /api/v1/public/integrations/webhooks/{id}` — signature-verified webhook intake.

## Module surface (auth + RBAC)
Academic, Admissions, Students, Staff, Attendance, Timetable, Examination, Finance,
Communication, Library, Transport, Hostel, Inventory, HR, Payroll, CMS, Portal, LMS,
Documents, Reports, Integrations, System — each exposes REST resources +
domain actions (e.g. `payroll/runs/{id}/process`, `documents/generate`,
`reports/export`, `integrations/providers/{id}/test`, `system/backups/{id}/verify`).

## OpenAPI
Controllers/requests carry OpenAPI annotations. Generate/serve the spec with your
preferred generator (e.g. l5-swagger) pointed at `app/Modules/**/Http`. Every
endpoint documents its parameters, responses, auth and required permission slug.

## Rate limits
Auth + portal login, public verification (30/min), CMS forms (20/min), incoming
webhooks (60/min), and the health probe (60/min) are throttled to prevent abuse.

## Versioning
The surface is `v1`. Optimizations in Sprint 23 are backward-compatible — no
breaking changes to existing endpoints.
