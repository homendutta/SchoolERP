# 04 – Frontend (Web) Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the React + Vite + Tailwind web
> architecture. **Highest constraint:** preserve the reference application's UI/UX
> ([08-ui-ux-principles.md](../00-product/08-ui-ux-principles.md) §0 *UI Preservation Policy*) while
> rebuilding the implementation. No code, no endpoint design.

---

## 1. Goals

- Preserve the reference UI/UX exactly: **sidebar layout, dashboard layout, navigation flow, page hierarchy, cards, tables, dialogs, workflows**.
- Be **role-adaptive**: menus and pages reflect the signed-in user's role, permissions, and data scope.
- Consume the **single API** identically to the Flutter app.
- Be **mobile-first responsive** and accessible.
- Be organized **by feature/business module**, reusing a shared design system.

---

## 2. Technology Choices

| Concern | Choice |
|---------|--------|
| Framework | React |
| Build/dev | Vite |
| Styling | Tailwind CSS |
| Structure | Feature-first modules + shared `ui/` design system |
| Routing | Role-adaptive routes under one domain (`/login`, `/admin`, `/teacher`, `/student`, `/parent`) |

> Specific libraries (state, data fetching, forms, charts, tables) are selected during implementation
> within these architectural rules. The **reference app's UX**, not its code, is the constraint.

---

## 3. Application Shell (preserved UX)

The shell reproduces the reference layout:

```
┌──────────────────────────────────────────────────────────┐
│ Header: title · global search · refresh · branding · user │
├───────────┬──────────────────────────────────────────────┤
│           │                                              │
│  Sidebar  │              Main Content Area               │
│ (grouped, │   (module pages: cards, tables, dialogs)     │
│  role-    │                                              │
│  adaptive)│                                              │
│           │                                              │
├───────────┴──────────────────────────────────────────────┤
│ Bottom navigation (mobile)                                │
└──────────────────────────────────────────────────────────┘
```

- **Sidebar** preserves the reference grouping (Overview, Daily, Academic, Records, Finance, Support, Administration, Profile) and per-role ordering.
- **Header** carries the page title, **global search (available on every page)**, refresh, school branding, and the signed-in user.
- **Bottom navigation** appears on mobile widths, mirroring the reference behaviour.
- **Role-based landing** matches the reference (e.g., admin → dashboard; teacher/clerk → primary module; student/parent → notices).

---

## 4. Layered Front-End Structure

```
Page (feature route)
   ▼
Feature components (module UI)
   ▼
Feature hooks (data + actions)
   ▼
API client (single, shared)  ──▶  Laravel API
   ▼
Cache / state (per-resource)
```

- **Pages** compose feature components into the shell.
- **Components** are built from the shared `ui/` design system (cards, tables, forms, dialogs, charts, skeletons).
- **Hooks** encapsulate data fetching, mutations, and module actions.
- **API client** is the single integration point to the backend.

---

## 5. Design System (`ui/`)

A reusable component library is the single source of UI truth, reproducing reference patterns:

| Primitive | Preserves |
|-----------|-----------|
| **Cards / KPI tiles** | Dashboard cards and metrics. |
| **Data tables** | Sortable, searchable, paginated lists with row actions, print, export. |
| **Forms** | Sectioned, validated create/edit forms (incl. long records like students). |
| **Dialogs / modals** | Focused create/edit/confirm flows. |
| **Status pipelines** | Workflow states (e.g., admissions register→confirm→enroll). |
| **Badges / counters** | Sidebar pending-item indicators. |
| **Charts** | Dashboard analytics. |
| **Skeletons / toasts** | Loading and feedback patterns. |

Features **compose** these primitives; they do not invent new patterns. Tailwind tokens carry the
branding/theme (light/dark, accent colors) from the Branding capability.

---

## 6. State & Data Management (conceptual)

- **Server state** is fetched per resource through feature hooks with a **stale-while-revalidate** style cache (mirroring the reference app's data-freshness behaviour), enabling background refresh and snappy navigation.
- **Client state** (UI state, dialogs, selections) is local to features/components.
- **Cross-cutting state** (auth/session, permissions, theme/branding, school settings) lives in app-level providers.
- Cache invalidation follows mutations (after a create/update/delete, the affected resource revalidates), and the header **refresh** re-pulls active resources. See [11-caching-strategy.md](11-caching-strategy.md).

---

## 7. Permission-Aware UI

- The UI renders **only** what the user may see/do, driven by the same permission model as the backend ([07-security-architecture.md](07-security-architecture.md)).
- **Menu visibility**, **action buttons**, and **route access** are gated by permission + data scope.
- The frontend gate is a **UX convenience**; the backend remains the **authoritative** enforcer.

---

## 8. Routing & Role Adaptation

- One app under one domain; routes are organized by role workspace (`/admin`, `/teacher`, `/student`, `/parent`, plus other staff roles).
- After login, the app derives the menu set and landing route from the user's role/permissions.
- Deep links (e.g., from a notice or a drill-down) navigate within the shell without losing context.

---

## 9. Global Search Integration

- A persistent **global search** in the header is available on every page.
- It queries the backend Global Search service across the permitted entities (students, parents, staff, admissions, fees, receipts, complaints, helpdesk, assets, inventory, documents) and respects role/scope.
- Results deep-link directly to records.

---

## 10. Branding & Theming

- Branding assets (logo, dark logo, favicon, login background, theme color, etc.) are loaded from the Branding capability and applied through Tailwind theme tokens and shell slots (header, login, printable receipts/hall tickets/ID cards).
- Light/dark mode and accent color personalization are preserved from the reference app.

---

## 11. Internationalization & Accessibility

- Locale-ready (currency, date/time, academic-year formats, language extensibility) per the PRD.
- Accessible by design: readable typography, contrast, keyboard navigation, screen-reader-friendly structure, clear validation/error messaging.

---

## 12. Build & Delivery

- Vite produces an optimized static build served under the school's single domain (ERP paths), separate from the public website root. See [13-deployment-architecture.md](13-deployment-architecture.md).
- The build consumes the one API over HTTPS; no business logic lives in the client.

---

## 13. Testing (web)

| Test type | Target |
|-----------|--------|
| **Component** | `ui/` primitives and feature components. |
| **Hook/integration** | Feature hooks against a mocked API client. |
| **Flow** | Key workflows (admission, fee collection, attendance, marks entry) for UX parity. |
| **Accessibility** | Core screens meet accessibility expectations. |

---

## 14. Non-Goals

- No endpoint definitions (API Design phase).
- No copying of reference application code — only the **experience** is preserved.
- No business logic in the client; the backend is authoritative.
