# 10 – Background Jobs & Scheduling

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the asynchronous processing
> architecture — queues, workers, and scheduled jobs — that keeps the API responsive and runs
> time-based and bulk work. No code, no schemas.

---

## 1. Why Background Processing

Some work must not block an API request: sending many messages, generating dues, importing/exporting
data, building reports, reconciling payments. These run **asynchronously** on queues and on a
scheduler, so user-facing requests stay fast.

```
API request → enqueue job → respond quickly (with a trackable handle if needed)
                  │
                  ▼
            Queue → Worker → execute → record result → side effects (audit/notify/cache)
```

---

## 2. Components

| Component | Role |
|-----------|------|
| **Queue** | Holds pending jobs (durable). |
| **Workers** | Long-running processes that execute jobs. |
| **Scheduler** | Triggers time-based jobs (cron-style). |
| **Job records** | Track status/result for bulk operations and failures. |

These are standard Laravel queue/scheduler facilities, configured per deployment
([13-deployment-architecture.md](13-deployment-architecture.md)).

---

## 3. Job Categories

| Category | Examples | Trigger |
|----------|----------|---------|
| **Communication dispatch** | Send SMS/Email/Push per recipient; bulk fan-out | Event / user action |
| **Scheduled communications** | Scheduled SMS/Email at a future time | Scheduler |
| **Fee dues generation** | Monthly dues generation/backfill (idempotent) | Scheduler + on-read for admin/clerk |
| **Bulk import** | Validate + persist imported Students/Staff/etc. | User action |
| **Bulk export / reports** | Generate large exports/reports | User action |
| **Bulk promotion** | Process single/bulk student promotion batches | User action (after preview/confirm) |
| **Payment reconciliation** | Confirm pending online payments; update fees | Gateway callback / scheduler |
| **Search indexing** | Update the global search index after changes | Domain event |
| **Audit/log fan-out** | Persist heavy audit/communication log entries | Domain event |
| **Media post-processing** | Validate/finalize uploads if needed | Upload event |
| **Reminders** | Fee/PTM/expiry reminders | Scheduler |

> All categories implement **PRD-validated behaviour** (e.g., idempotent dues generation); they add no new product behaviour.

---

## 4. Scheduling (time-based)

The scheduler runs recurring jobs, for example:

- **Monthly fee dues** generation for active students (idempotent).
- **Scheduled SMS/Email** dispatch at their due time.
- **Reminders** (fee due, PTM, document/asset expiry) per configuration.
- **Maintenance** (cache warmups, cleanup of expired transient artifacts).

Schedules are configuration-driven; no business rule is hard-coded outside the owning module's service.

---

## 5. Reliability & Failure Handling

| Concern | Approach |
|---------|----------|
| **Retries** | Failed jobs retry with backoff per policy. |
| **Dead-letter / failed jobs** | Exhausted jobs are recorded for inspection and re-run. |
| **Idempotency** | Jobs with external effects (payments, communications, dues) are idempotent to avoid duplicates on retry. |
| **Atomicity** | Multi-step jobs use service-owned transactions where consistency is required. |
| **Graceful degradation** | Provider failures are logged; they don't corrupt core data. |
| **Observability** | Job outcomes feed logging/monitoring ([12-logging-monitoring.md](12-logging-monitoring.md)). |

---

## 6. Bulk Operation Pattern

```
User initiates bulk op (import/export/promotion/bulk comms)
   ▼
API validates request → creates a tracking record → enqueues job(s)
   ▼
Worker processes (chunked) → updates progress/status
   ▼
Result available (file/summary) → user notified / can poll status
```

- Large operations are **chunked** to bound memory and allow progress.
- Validation-first semantics are preserved (e.g., validate all rows before committing where the PRD requires).

---

## 7. Ordering, Concurrency & Throttling

- Independent jobs run concurrently across workers.
- Communication jobs respect provider **throttle/rate limits**.
- Where order matters (e.g., a workflow's follow-up steps), jobs are sequenced via the owning service/events.

---

## 8. Security & Scope in Jobs

- Jobs carry the **actor/context** so audit and data scope remain correct off-request.
- Jobs never bypass permission/scope rules; they execute within the same authorization model.

---

## 9. Tenant Readiness

- Queue/scheduler design is **tenant-aware-ready**: jobs can carry a tenant boundary for future SaaS without redesign; V1 runs single-tenant.

---

## 10. Non-Goals

- No queue-driver or infrastructure product selection here (deployment detail).
- No job class design or code.
- No schemas.
