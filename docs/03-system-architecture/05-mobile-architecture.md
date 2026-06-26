# 05 – Mobile Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the Flutter mobile
> architecture. **One Flutter app serves all roles** and consumes the **same single API** as the web
> app. No code, no endpoint design.

---

## 1. Goals

- **One app, every role**: Super Admin, Administrator, Supervisor, Clerk, Accountant, Receptionist, Teacher, Student, Parent — all in a single Flutter app.
- **Role-adaptive**: dashboard and menus change automatically after login based on role + permissions + scope.
- **Same API** as web: identical contract, identical authorization.
- **Mobile-first parity**: role-appropriate workflows match the web experience and the reference UX.
- **Push-enabled**: the app is the push-notification delivery target.

---

## 2. Single-App Principle

- There is exactly **one** Flutter application. No per-role apps.
- The user never selects a "mode"; **identity and permissions** drive the entire experience.
- Menus, dashboards, available actions, and data scope are derived after login from the same RBAC model used everywhere.

---

## 3. Layered Architecture (feature-first)

Each feature/module is layered:

```
Presentation  → screens, widgets, view-state (role-adaptive UI)
   ▼
Domain        → use cases / module actions, models, validation
   ▼
Data          → repositories + the single API client (+ local cache)
   ▼
Core services → auth, permissions, push, storage, media, search
```

- **Presentation** preserves reference UX patterns adapted to mobile (cards, lists/tables, dialogs, bottom navigation + drawer).
- **Domain** holds module use cases; no UI or transport concerns.
- **Data** talks only to the shared API client and local storage.
- **Core** provides cross-cutting capabilities shared by all features.

---

## 4. App Shell & Navigation

```
After login:
  derive role + permissions + scope
     ▼
  build role-adaptive navigation (drawer + bottom nav)
     ▼
  land on role's home (e.g., admin → dashboard, parent/student → notices)
```

- Navigation structure mirrors the web information architecture and the reference grouping.
- Only permitted modules/actions appear; data scope (own/linked/assigned/all) is enforced.

---

## 5. Shared API Client

- A **single API client** in `core/` is the only integration point to the backend — the same contract the web app uses.
- Handles auth tokens/session, request/response envelope, errors, retries, and pagination/filtering conventions defined in [06-api-architecture.md](06-api-architecture.md).
- No business logic in the client; the backend is authoritative.

---

## 6. Authentication & Session (mobile)

- Same multi-identity login rules as the PRD (staff by staff-number/mobile/email; student by admission number; parent by parent-id/mobile/email).
- Secure token/session storage on device; session lifetime consistent with the backend.
- Forced first-login password change is honoured in-app.
- Device is recorded in **device history** ([07-security-architecture.md](07-security-architecture.md)).

---

## 7. Push Notifications

- The app registers with the configured push provider and receives notices, fee reminders, attendance/result alerts.
- Push is one destination of the central **Notification Service** ([09-notification-architecture.md](09-notification-architecture.md)); the app does not talk to providers directly for business logic — it receives and renders.
- Tapping a push deep-links into the relevant module screen.

---

## 8. Offline Tolerance

- **Read-heavy** views (timetable, notices, recent attendance/marks, fee summary) may cache locally for offline viewing where feasible.
- **Writes** require connectivity and go through the API with the same validation/authorization as web.
- Cached data is revalidated when connectivity returns (stale-while-revalidate style), consistent with [11-caching-strategy.md](11-caching-strategy.md).

---

## 9. Permission-Aware UI (mobile)

- The app renders only permitted modules/actions; the backend remains the authoritative enforcer.
- The same permission matrix and scope rules apply as on web, ensuring consistent capability across surfaces.

---

## 10. Branding & Theming

- Branding assets (logo, theme color, etc.) are applied from the Branding capability so the app reflects the school's identity.
- Light/dark theming consistent with web/reference behaviour.

---

## 11. Public Content Parity

- The app surfaces the same synchronized public content (Public Notices, Photo Gallery, Video Gallery) that flows from the ERP, in addition to authenticated role features ([04-website-mobile-integration.md](../00-product/04-website-mobile-integration.md)).

---

## 12. Testing (mobile)

| Test type | Target |
|-----------|--------|
| **Unit** | Domain use cases, repositories (mocked API). |
| **Widget** | Presentation components and role-adaptive shell. |
| **Integration** | Key workflows (login + forced change, attendance, fee payment, notices). |

---

## 13. Distribution

- Single app distributed through the appropriate app stores / enterprise distribution.
- Same backend, same domain; configuration points the app at the school's API. See [13-deployment-architecture.md](13-deployment-architecture.md).

---

## 14. Non-Goals

- No separate or per-role apps.
- No business logic on device beyond presentation/validation aids.
- No direct provider/gateway integration for business actions — all via the one API.
