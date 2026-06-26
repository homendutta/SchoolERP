# 12 – Logging & Monitoring

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines logging, the central audit
> trail, and monitoring. Implements the PRD's **audit-everything** principle and expanded Audit Logs
> capability. No code, no schemas.

---

## 1. Logging Domains

The system maintains distinct, purpose-built logging domains:

| Domain | Purpose | Audience |
|--------|---------|----------|
| **Application logs** | Technical events, errors, diagnostics. | Engineers/ops. |
| **Audit logs** | Material business actions (who did what, when). | Admin, Super Admin, auditors. |
| **Communication logs** | Every SMS/Email/Push/Notice sent. | Admin, accountant (finance), auditors. |
| **Transaction logs** | Every payment/refund attempt and result. | Accountant, admin, auditors. |
| **Access/security logs** | Login, logout, failed login, lockouts, device/session events. | Admin, security. |

These are separate concerns with separate retention and access, even if they share infrastructure.

---

## 2. Central Audit Trail

The **Audit service** is the single, authoritative record of material actions, fed by domain events
([03-backend-architecture.md](03-backend-architecture.md) §5). Per the PRD it records at minimum:

- Login · Logout · Failed Login · Password Reset
- User Creation · Student Update
- Attendance Unlock · Fee Collection · Result Publish
- Role Changes · Permission Changes · System Settings Changes
- Communication Events · Payment Events

Each entry captures **actor, action, target, timestamp, and context**, and is **searchable,
filterable, and exportable**. Audit is **append-oriented** and protected from tampering.

```
Module service performs action
   └─▶ domain event
          └─▶ Audit listener → write audit entry (actor, action, target, time, context)
```

---

## 3. Structured Logging

- Logs are **structured** (consistent fields) so they can be searched, filtered, and correlated.
- A **correlation/request identifier** ties together the API request, its jobs, and resulting side effects (audit/notify/cache).
- Log **levels** (debug/info/warning/error/critical) are used consistently; sensitive data is never logged (no secrets, no raw credentials, no card data).

---

## 4. Searchability & Export

- **Audit, communication, and transaction logs** are searchable and filterable (by actor, action, module, date range, status) and **exportable**, satisfying the PRD.
- Access to logs is governed by the permission matrix (e.g., finance logs for accountant; full audit for admin/super admin).

---

## 5. Monitoring & Health

| Concern | What is monitored |
|---------|-------------------|
| **Availability** | API/app health, especially during school hours. |
| **Performance** | Request latency, slow queries, job throughput. |
| **Queues/jobs** | Backlog depth, failure rate, retries, dead-letter. |
| **External providers** | SMS/Email/Push/Payment success and failure rates. |
| **Cache** | Hit/miss, invalidation activity. |
| **Errors** | Application error rate and exceptions. |

Health checks expose service status for the deployment to supervise
([13-deployment-architecture.md](13-deployment-architecture.md)).

---

## 6. Alerting

- Threshold/anomaly alerts on: elevated error rates, job failure spikes, provider outages (SMS/Email/Payment), authentication anomalies (lockout surges), and availability drops.
- Alerts route to the operators/Super Admin so issues are addressed before they impact a school day.

---

## 7. Error Tracking

- Unhandled exceptions are captured with context (correlation id, module, actor scope — never sensitive payloads) for diagnosis.
- External failures are logged distinctly from internal errors so graceful-degradation paths are visible.

---

## 8. Retention & Protection

| Log type | Retention posture |
|----------|-------------------|
| **Audit** | Long-lived; tamper-resistant; exportable. |
| **Communication/Transaction** | Retained for accountability/reconciliation; exportable. |
| **Application** | Operational retention with rotation. |
| **Access/security** | Retained for security review. |

Logs are included in backup scope where appropriate and respect data-protection expectations.

---

## 9. Privacy in Logs

- No secrets, passwords, tokens, or payment-instrument data in any log.
- Personal data in logs is minimized and access-controlled, consistent with [07-security-architecture.md](07-security-architecture.md).

---

## 10. Tenant Readiness

- Logging/audit is **tenant-aware-ready**: entries can carry a tenant boundary for future SaaS so logs remain isolatable without redesign.

---

## 11. Non-Goals

- No log-store/observability product selection here (deployment detail).
- No audit schema design (Database Design phase).
- No code.
