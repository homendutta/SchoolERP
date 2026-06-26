# 07 – Security Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines how the **Security-first**
> principle and the PRD's authentication, RBAC, and security controls are enforced architecturally.
> Implements [05-authentication-strategy.md](../00-product/05-authentication-strategy.md) and
> [03-role-permission-matrix.md](../00-product/03-role-permission-matrix.md). No code, no schemas.

---

## 1. Security Objectives

1. Authenticate three identity types (staff/student/parent) + the Super Admin.
2. Enforce least-privilege RBAC and data scope on **every** request, server-side.
3. Protect credentials, secrets, financial and academic data.
4. Audit every material action (central, searchable, exportable).
5. Degrade gracefully when external providers fail, without weakening security.
6. Stay multi-tenant-ready without leaking single-tenant assumptions.

---

## 2. Authentication Architecture

- **Multi-identity login** resolves the supplied identifier to an identity type and authenticates accordingly (staff: staff-number/mobile/email; student: admission number; parent: parent-id/mobile/email).
- **Token/session** establishes role, permissions, and data scope; the same mechanism serves web and mobile.
- **Automatic account generation** on record creation provisions credentials + temporary password and triggers credential delivery (via Notification) and audit/communication logs.
- **Forced first-login password change** is enforced before any module access.
- **Number Generator** enforces identifier rules (e.g., admission number numeric ≤6 digits, unique).

---

## 3. Authorization Architecture (RBAC + Scope)

Authorization is **two-dimensional** and enforced server-side:

```
Request
  ├─ Action grant?  → permission matrix (View/Create/Edit/Delete/Print/Export/Import/Approve/Publish/Lock/Unlock)
  └─ Data scope?    → own / linked / assigned / all
        ▼
   allow only if BOTH pass
```

- **Action grant** is checked via policies/gates in the Request layer.
- **Data scope** is enforced in the Service layer using Core scope helpers:
  - **Student → own**, **Parent → linked children**, **Teacher → assigned classes/subjects**, staff roles → as granted, **Super Admin → system**.
- **Default custom roles start with no permissions**; access is granted explicitly (least privilege).

---

## 4. Permanent Security Rules (always enforced)

Regardless of role configuration, these PRD rules are enforced architecturally:

| Rule | Enforcement |
|------|-------------|
| Self-account deletion blocked | Service-level guard. |
| Admission state gating (register→confirm→enroll) | Workflow service state checks. |
| Exam **publish lock** (locks teacher marks) | Examinations/Marks services. |
| Attendance **lock/unlock** (admin only) | Attendance service + permission. |
| Teacher scope (assigned only) for attendance/marks/logbook/discipline/conduct | Scope helpers in services. |
| Student/parent never escalate beyond own/linked | Scope helpers; not configurable away. |
| Class-specific notices limited to taught classes | Notice service. |
| Finance approvals (refund, high-value stock issue) | Approval permission + service checks. |

---

## 5. Security Controls (from PRD §Security)

Configured under **Settings → Security** and enforced by the platform:

| Control | Architectural home |
|---------|--------------------|
| **Password Policy** | Auth core: complexity/length/expiry/reuse enforced on set/change. |
| **Account Lockout** | Auth core: lockout after repeated failed logins. |
| **Login History** | Auth core + Audit: per-user success/failure records. |
| **Device History** | Auth core: device/client registry per account. |
| **Session Management** | Auth core: bounded lifetime; view/terminate sessions; consistent web+mobile. |
| **First Login Password Change** | Auth core: forced change gate. |
| **Audit Logs** | Central Audit service (searchable/filterable/exportable). |

---

## 6. Secrets & Sensitive Data

- **Gateway/SMTP/SMS/Push/Payment secrets** are stored in secure configuration, never in code, UI, or logs.
- **No card/payment-instrument data** is stored; payment is delegated to the gateway ([07-payment-strategy.md](../00-product/07-payment-strategy.md)).
- **Credentials** are stored using strong one-way hashing; never displayed.
- Sensitive student fields (safeguarding, medical, financial) are access-scoped and audited.

---

## 7. Transport & Application Security

- **HTTPS everywhere** for all client↔API and provider traffic.
- Standard application protections (input validation via Form Requests, output encoding, CSRF/XSS/SQL-injection defenses through framework conventions, rate limiting).
- **Idempotency** on payment/communication operations to prevent duplicate side effects.

---

## 8. Audit Architecture

- A **central Audit service** records material actions emitted as domain events: login, logout, failed login, password reset, user creation, student update, attendance unlock, fee collection, result publish, role/permission changes, settings changes, communication events, payment events.
- Audit entries capture **actor, action, target, timestamp, context** and are **searchable, filterable, exportable**.
- Audit is **append-oriented** and protected from tampering. Detail: [12-logging-monitoring.md](12-logging-monitoring.md).

---

## 9. Communication & Payment Security

- Credential and notice delivery go through the central Notification service; every send is logged.
- Payment flows are delegated to gateways with test/live separation; all attempts/refunds are logged as transactions and audited ([09-notification-architecture.md](09-notification-architecture.md), [07-payment-strategy.md](../00-product/07-payment-strategy.md)).

---

## 10. Tenant Isolation Readiness

- Although Version 1 is single-tenant (one DB/domain), security boundaries are written to be **tenant-aware-ready**: scope helpers, configuration, and permission checks do not hard-code single-tenant assumptions.
- This allows a future tenant boundary (multi-school SaaS) and branch scoping (multi-branch) to be introduced **without redesigning** the security model.

---

## 11. Failure & Abuse Handling

- External provider failures are caught, logged, and degraded gracefully (e.g., failed credential SMS does not block account creation) — without weakening authentication/authorization.
- Repeated failures trigger lockout/rate limits; anomalies surface through monitoring/alerting ([12-logging-monitoring.md](12-logging-monitoring.md)).

---

## 12. Non-Goals

- No specific cryptographic libraries, token formats, or schemas (chosen in implementation within these rules).
- No endpoint or table design.
- No code.
