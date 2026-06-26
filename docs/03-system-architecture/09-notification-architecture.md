# 09 – Notification Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the **single centralized
> Notification Service** that handles all outbound communication. Implements
> [06-communication-strategy.md](../00-product/06-communication-strategy.md). No code, no schemas.

---

## 1. Principle: One Notification Service

Per the PRD there is **one centralized notification service**. Every module that needs to communicate
(credentials, fee reminders, notices, result alerts, PTM, discipline) calls this single service —
modules never integrate providers directly.

```
Module raises a communication intent
        │
        ▼
┌──────────────────────────────────────────────┐
│            Notification Service               │
│  resolve audience → render template →         │
│  select channels → enqueue → dispatch → log   │
└───────┬───────────┬───────────┬──────────────┘
        ▼           ▼           ▼          ▼
     Notice       SMS         Email       Push      (future: WhatsApp)
   (internal)   (gateway)    (SMTP)     (provider)
```

---

## 2. Channels

| Channel | Use |
|---------|-----|
| **Notice (internal)** | In-ERP/app notice board items. |
| **SMS** | Text via configured SMS gateway. |
| **Email** | Via configured SMTP. |
| **Push** | Real-time alerts to the Flutter app. |
| **WhatsApp (future)** | Pluggable additional channel; added without redesign. |

Channels are **enabled/disabled** per school and configured in Settings → Communication.

---

## 3. Provider Abstraction (drivers)

- Each channel has a **driver interface**; concrete providers (a specific SMS gateway, SMTP server, push provider) sit behind it.
- Adding/swapping a provider — or adding **WhatsApp** — is a new driver, **no change** to modules or the service contract (pluggability mandate).
- Provider credentials live in secure configuration ([07-security-architecture.md](07-security-architecture.md)).

---

## 4. Notice Publishing (multi-destination)

A single notice fans out to a **selected set of destinations** in one action:

```
Compose Notice (audience + destinations)
   ├─ Internal ERP   → notice board
   ├─ Website        → outward sync feed (public site)
   ├─ Flutter App    → app notice list
   ├─ Push           → push driver
   ├─ SMS            → sms driver
   └─ Email          → email driver
```

- Audience targeting (all/staff/teachers/students/parents/class-specific) and permission rules (e.g., teachers → own classes) are honoured.
- Only destinations marked **website/app** reach the public surfaces ([04-website-mobile-integration.md](../00-product/04-website-mobile-integration.md)).

---

## 5. Templates & Composition

- **SMS templates** and **Email templates** are parameterized and reusable; **custom** one-off messages are supported.
- A template engine personalizes per recipient (e.g., name, class, amount, due date).
- System events (account creation, fee receipt/reminder, result published, PTM, discipline) use templates.

---

## 6. Dispatch Pipeline

```
Intent → resolve recipients (audience + scope)
       → render content (template/custom)
       → choose channels (enabled + selected)
       → enqueue per recipient/channel (jobs)
       → dispatch via driver
       → record delivery status (Communication Log)
       → retries / fallback on failure
```

- **Bulk** sends fan out to many recipients; **scheduled** sends are queued for a future time.
- Heavy/bulk/scheduled dispatch runs on **background jobs** ([10-background-jobs.md](10-background-jobs.md)).

---

## 7. Communication Logs

- Every send (Notice/SMS/Email/Push) produces a **Communication Log** entry: channel, recipient, template/content reference, status (queued/sent/delivered/failed), timestamps, triggering event/actor.
- Logs support audit, troubleshooting, and delivery-rate reporting.
- Communication events also feed the central Audit trail ([12-logging-monitoring.md](12-logging-monitoring.md)).

---

## 8. Triggers (automatic communications)

| Event | Typical channels |
|-------|-----------------|
| Account created (staff/student/parent) | SMS + Email (credentials) |
| Fee receipt issued | Email (+ optional SMS) |
| Fee due / reminder | SMS + Email + Push |
| Result published | Push + Notice |
| Notice published | Per selected destinations |
| Discipline parent notification | SMS/Email/Push as configured |
| PTM booking/reminder | Push + Email |

All triggered communications are logged; disabled/failed channels degrade gracefully and never block the underlying operation.

---

## 9. Channel Toggles & Graceful Degradation

- Each channel can be disabled by the school; the service skips disabled channels and logs the skip.
- Provider failures are caught, logged, and retried per policy; a failed channel never blocks the core operation (e.g., credential SMS failure does not block account creation).

---

## 10. Idempotency & Safety

- Sends with external effects use idempotency safeguards to avoid duplicate messages on retry.
- Rate/throttle limits protect providers and the school's sender reputation.

---

## 11. Tenant Readiness

- The service is **tenant-aware-ready**: per-tenant provider configuration and sender identity can be introduced for future SaaS without redesigning the service or modules.

---

## 12. Non-Goals

- No provider selection or credentials here (configured per school).
- No message schemas or endpoint design.
- No code.
