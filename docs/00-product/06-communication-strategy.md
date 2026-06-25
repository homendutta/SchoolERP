# 06 – Communication Strategy

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines the communication
> channels, notice publishing model, templates, bulk/scheduled messaging, logging, and gateway
> settings at a **product** level. No implementation, provider APIs, or schemas.

---

## 1. Purpose

Communication is a first-class capability: the ERP must reliably reach staff, students, and parents
through multiple channels, with templates, bulk and scheduled sending, and a complete audit trail.
Communication powers credential delivery, fee reminders, notices, results alerts, and more.

---

## 2. Channels

### 2.1 Version 1 Channels
| Channel | Use |
|---------|-----|
| **Notices** | In-ERP announcements, also publishable to website/app/push/SMS/email. |
| **SMS** | Text messages via configured SMS gateway. |
| **Email** | Emails via configured SMTP. |
| **Push Notifications** | Real-time alerts to the Flutter mobile app. |

### 2.2 Future Channel
| Channel | Status |
|---------|--------|
| **WhatsApp** | Planned future channel; the architecture must allow it to be added as a pluggable provider without redesign. |

---

## 3. Notice Publishing Model

A **Notice** is composed once and published to a selectable set of destinations:

| Destination | Description |
|-------------|-------------|
| **Internal ERP** | Visible inside the ERP to the targeted audience. |
| **Website** | Appears on the public website's Notice Board (see [04-website-mobile-integration.md](04-website-mobile-integration.md)). |
| **Flutter App** | Appears in the mobile app. |
| **Push Notification** | Sent as a push alert to the app. |
| **SMS** | Sent as a text message. |
| **Email** | Sent as an email. |

### 3.1 Notice Rules (carried from validated workflows)
- Notices target an **audience** (all / staff / teachers / students / parents / class-specific).
- **Class-specific** notices may be posted by Teachers only to classes they teach.
- Notices support **priority**, **expiry**, and an optional **acknowledgement-required** flag.
- Audience + destination together control who sees a notice and where.

### 3.2 Publishing Matrix (example)
A single notice can fan out across destinations in one action:

```
Compose Notice
   ├─ audience: parents (class 5A)
   └─ destinations: Internal ERP + App + Push + SMS
        → appears in ERP for 5A parents
        → appears in app for 5A parents
        → push alert to 5A parents' devices
        → SMS to 5A parents' mobiles
        → (NOT on public website, NOT email — not selected)
```

---

## 4. Templates & Message Composition

The product supports:

| Capability | Description |
|------------|-------------|
| **SMS Templates** | Reusable, parameterized SMS message templates. |
| **Email Templates** | Reusable, parameterized email templates (subject + body). |
| **Custom SMS** | One-off SMS composed ad hoc. |
| **Custom Email** | One-off email composed ad hoc. |

Templates support placeholders (e.g., student name, class, amount, due date) so the same template
personalizes per recipient. Common system events (account creation, fee receipt, fee reminder, result
published) use templates.

---

## 5. Bulk & Scheduled Messaging

| Capability | Description |
|------------|-------------|
| **Bulk SMS** | Send an SMS to many recipients in one operation (e.g., all parents of a class). |
| **Bulk Email** | Send an email to many recipients in one operation. |
| **Scheduled SMS** | Queue an SMS to send at a future date/time. |
| **Scheduled Email** | Queue an email to send at a future date/time. |

Bulk operations respect audience selection and permissions, and every recipient delivery is logged
individually.

---

## 6. Communication Logs

Every outbound message — Notice, SMS, Email, Push — produces a **communication log** entry capturing
at minimum: channel, recipient, template/content reference, status (queued/sent/delivered/failed),
timestamps, and the triggering event/actor.

Communication logs are used for:
- **Audit & accountability** — proof that credentials, reminders, and notices were sent.
- **Troubleshooting** — diagnosing delivery failures.
- **Reporting** — delivery success rates per channel.

Logs are viewable by Administrator (and Accountant for finance-related communications) per the
permission matrix.

---

## 7. Gateway & Channel Settings

The Settings module holds configurable, school-owned channel configuration:

| Setting Group | Configures |
|---------------|-----------|
| **Gateway Settings** | General communication/gateway configuration and channel enable/disable toggles. |
| **SMTP Settings** | Email server configuration for sending email. |
| **SMS Gateway Settings** | SMS provider configuration and credentials. |
| **Push Notification Settings** | Push provider configuration for the mobile app. |

### 7.1 Channel Toggles
Each channel can be **enabled or disabled** by the school. When disabled or misconfigured, dependent
automatic actions (e.g., credential SMS on account creation) are skipped and logged — they never block
the underlying operation.

### 7.2 Provider Pluggability
SMS, Email, and Push providers are **pluggable**: a school configures its chosen provider, and future
providers (including WhatsApp) can be added without redesigning the communication model.

---

## 8. Automatic Communication Triggers

Certain product events automatically generate communications (subject to channel toggles and templates):

| Event | Typical Channels |
|-------|-----------------|
| Account created (staff/student/parent) | SMS + Email (credentials) |
| Fee receipt issued | Email (receipt), optional SMS |
| Fee due / reminder | SMS + Email + Push |
| Result published | Push + Notice |
| Notice published | per selected destinations |
| Discipline incident (parent notification) | SMS/Email/Push as configured |
| PTM booking/reminder | Push + Email |

All triggered communications are logged.

---

## 9. Principles

1. **Reliable reach** — multiple channels with delivery logging.
2. **Template-driven** — consistent, personalized, reusable messaging.
3. **Permission-gated** — only authorized roles send bulk/scheduled communications.
4. **Audited** — every message logged for accountability.
5. **Toggle-safe** — disabled/failed channels degrade gracefully, never block core operations.
6. **Pluggable** — new providers and channels (WhatsApp) add without redesign.

---

## 10. Out of Scope (Version 1)

- WhatsApp messaging (future).
- In-app two-way chat / messaging threads (not part of V1 communication scope).
- Marketing automation beyond the templated/bulk/scheduled capabilities above.
