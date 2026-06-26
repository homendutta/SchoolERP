# 11 – Caching Strategy

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines where and how the system caches
> to stay fast without serving stale or cross-scope data. No code, no schemas.

---

## 1. Goals

- Keep the API and clients responsive under a full school's data volume.
- Reduce repeated computation/queries for hot, slow-changing data.
- Never serve **stale critical data** or **data outside a user's scope**.
- Mirror the reference app's snappy, **stale-while-revalidate** feel.

---

## 2. Cache Layers

```
┌──────────────────────────────────────────────────────────────┐
│ Client (React / Flutter)                                      │
│   • per-resource SWR cache (background revalidate)            │
│   • local read cache (mobile offline-tolerant)               │
├──────────────────────────────────────────────────────────────┤
│ API (Laravel)                                                 │
│   • application cache (lookups, permissions, aggregates)      │
│   • query/result cache (hot reads)                           │
│   • HTTP caching headers where safe                          │
├──────────────────────────────────────────────────────────────┤
│ Data (MySQL 8)                                                │
│   • DB-level optimizations (indexes designed in DB phase)    │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. What to Cache (and why)

| Data | Layer | Rationale |
|------|-------|-----------|
| **Reference/lookup data** (classes, subjects, periods, settings, enums) | App + client | Slow-changing, read often. |
| **Permissions/role definitions** | App | Checked on every request; rarely changes. |
| **Dashboard aggregates** | App | Expensive to compute; refreshed periodically. |
| **Sidebar badge counts** | App (short TTL) | Frequent reads; mirrors reference 5-minute cache. |
| **School branding/settings** | App + client | Used everywhere; changes rarely. |
| **List views** | Client (SWR) | Fast navigation; background refresh after mutations. |
| **Read-heavy mobile views** | Mobile local | Offline tolerance (timetable, notices, summaries). |

---

## 4. What NOT to Cache (or cache minimally)

- **Financial transactions, receipts, payment status** — always authoritative/fresh.
- **Marks during entry, attendance writes, lock states** — fresh.
- **Anything cross-scope** — caches are **always keyed by user scope** so a cached value never leaks across own/linked/assigned/all boundaries.
- **Secrets** — never cached in client-readable form.

---

## 5. Cache Keying & Scope Safety

- Cache keys incorporate **scope identity** (role + scope) where results differ by user, preventing cross-user/cross-scope leakage.
- Keys are namespaced **by module** and, for forward compatibility, **tenant-aware-ready** so multi-tenant can be introduced without redesign.
- Lookup/global data uses shared keys; user-specific data uses scoped keys.

---

## 6. Invalidation Strategy

```
Mutation (create/update/delete) in a module service
   └─▶ emits domain event
          ├─▶ invalidate affected app cache keys
          ├─▶ trigger client revalidation (SWR) for affected resources
          └─▶ refresh dependent aggregates/badges (lazy or via job)
```

- **Event-driven invalidation**: caches are invalidated by the same domain events that drive audit/notify/search.
- **Client revalidation**: after a successful mutation, the affected resource refetches; the header **refresh** re-pulls active resources.
- **TTL fallback**: time-boxed entries (e.g., badges, aggregates) expire even without an explicit event.

---

## 7. Freshness Model (stale-while-revalidate)

- Clients may show cached data instantly, then **revalidate in the background**, matching the reference UX.
- Critical financial/academic states bypass stale reads and fetch fresh.

---

## 8. Mobile Offline Cache

- Read-heavy views cache locally for offline viewing; writes require connectivity.
- On reconnect, cached views revalidate; conflicting writes are resolved server-side via normal validation/authorization ([05-mobile-architecture.md](05-mobile-architecture.md)).

---

## 9. Operational Concerns

| Concern | Approach |
|---------|----------|
| **Warmup** | Hot lookups/aggregates can be pre-warmed via scheduled jobs. |
| **Stampede control** | Single-flight/locking on expensive recomputations. |
| **Observability** | Cache hit/miss and invalidation surfaced to monitoring ([12-logging-monitoring.md](12-logging-monitoring.md)). |
| **Backing store** | An in-memory cache store is used in deployment; selection is a deployment detail. |

---

## 10. Non-Goals

- No cache product/driver selection here (deployment detail).
- No index/table tuning (Database Design phase).
- No code.
