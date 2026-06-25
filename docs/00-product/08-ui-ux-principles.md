# 08 – UI / UX Principles

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines the experience
> principles for the new ERP. The goal: keep the **familiar** experience of the proven reference
> application while rebuilding on a **modern** foundation. This document governs UX intent — not
> visual design specs, component code, or framework detail.

---

## 0. UI Preservation Policy (Mandatory · Permanent)

**This is the single highest-priority product rule.** The project exists because the purchased
Google Apps Script reference application has an excellent UI and user experience. Preserving that
experience is a **mandatory, permanent product principle** — it is not optional and does not expire
with any release.

### 0.1 Policy Statement
The new ERP **must preserve the overall UI and UX of the reference application as closely as possible.**

### 0.2 What Must Be Preserved
- Sidebar layout
- Dashboard layout
- Navigation flow
- Page hierarchy
- Cards
- Tables
- Dialogs
- User workflows
- Overall user experience

### 0.3 What Is Rebuilt
The implementation is **completely rebuilt** on a modern stack:
- **Frontend:** React · Vite · Tailwind CSS
- **Backend:** Laravel
- **Mobile:** Flutter

### 0.4 Hard Rules
- The reference application's **code must NEVER be copied**.
- **Only the experience is preserved**, never the implementation.
- This policy is **permanent** and binding on every release and every module, now and in the future.

> Conformance to this policy is a standing acceptance criterion for every release
> (see [10-release-plan.md](10-release-plan.md) §5).

---

## 1. Guiding Principle: Familiar Experience, Modern Foundation

The new ERP's UI should **closely match** the purchased Apps Script application so existing users feel
at home from day one. The **experience is preserved**; the **code is not reused** — the application is
completely rebuilt on a modern stack.

**Preserve:**
- Dashboard layout
- Sidebar
- Navigation structure
- User experience and overall workflow
- Cards
- Tables
- Forms
- Dialogs / modals

**Rebuild (foundation only — out of scope for this PRD's detail):**
- Web: React + Vite + Tailwind CSS
- Mobile: Flutter

> Rule: **Do NOT copy the code. Rebuild the application; keep the experience familiar.**

---

## 2. Layout & Navigation

### 2.1 Overall Shell
- **Persistent sidebar** for primary navigation, grouped by functional area (Overview, Daily, Academic, Records, Finance, Support, Administration, Profile), mirroring the reference app's grouping.
- **Top header** with page title, global refresh, school identity/branding, and the signed-in user.
- **Global search** is available from the header on **every page**, searching across core records (students, parents, staff, admissions, fees, receipts, complaints, helpdesk, assets, inventory, documents). See [02-module-catalog.md](02-module-catalog.md) → Global Search.
- **Main content area** that renders the selected module's view.
- **Role-adaptive menus**: the sidebar shows only the modules and actions the user is permitted (per [03-role-permission-matrix.md](03-role-permission-matrix.md)), ordered to match each role's daily priorities.

### 2.2 Mobile Navigation
- The single Flutter app preserves the same information architecture, adapted to mobile patterns (e.g., bottom navigation + drawer).
- Menus and dashboards adapt automatically to the role after login.

### 2.3 Role-Based Landing
Each role lands on the most relevant view after login (e.g., Administrator → Dashboard; teacher/clerk →
their primary working module; student/parent → notices), consistent with the reference app's behaviour.

---

## 3. Core UI Patterns to Preserve

| Pattern | Intent |
|---------|--------|
| **Dashboard cards / KPIs** | At-a-glance metrics and quick actions per role. |
| **Data tables** | Sortable, searchable, paginated lists with row actions, print, and export. |
| **Forms** | Clear, validated, sectioned forms for create/edit (including long records like students). |
| **Dialogs / modals** | Focused create/edit/confirm interactions without losing context. |
| **Drill-downs** | From a record (e.g., student) into related data (fees, attendance, results, parents). |
| **Status pipelines** | Visual workflow state (e.g., admissions register → confirm → enroll). |
| **Badges / counters** | Pending-item indicators on the sidebar (helpdesk, complaints, etc.). |
| **Charts** | Dashboard analytics (attendance, collections, performance). |
| **Toasts / confirmations** | Immediate, non-disruptive feedback on actions. |

---

## 4. Experience Principles

1. **Familiarity first** — match the reference app's structure so retraining is minimal.
2. **Consistency** — one design language across every module, web, and mobile.
3. **Clarity** — obvious primary actions; sensible defaults; minimal cognitive load.
4. **Efficiency** — common workflows (mark attendance, collect a fee, enter marks) are fast and few-click/tap.
5. **Feedback** — every action confirms success/failure clearly (toasts, inline validation, status changes).
6. **Forgiveness** — confirm destructive actions; prefer reversible operations; clear error messages.
7. **Progressive disclosure** — show essentials first; reveal advanced fields/sections on demand.

---

## 5. Mobile-First & Responsive

- Every primary workflow must be usable on a phone (mobile-first product principle).
- Web layouts are responsive across desktop, tablet, and mobile browsers.
- Touch targets, input modes (numeric for admission numbers/amounts), and offline-tolerant reads are considered for mobile.
- Parity of capability across web and the Flutter app for role-appropriate features.

---

## 6. Accessibility & Inclusivity

- Readable typography, sufficient contrast, and scalable text.
- Keyboard navigability on web; screen-reader-friendly structure.
- Clear labels and error messaging.
- Localization-readiness: terminology, currency, date/time, and academic-year formats configurable; language extensibility.

---

## 7. Visual Identity

- **School branding**: school name, logo, and theme appear throughout (header, login, printable receipts/hall tickets), driven by Settings.
- **Branding assets**: the full set of branding assets — School Logo, Dark Logo, Favicon, Login Background, Theme Color, School Motto, Principal Signature, School Stamp, Report Logos, Receipt Logo, ID Card Logo — is defined in [02-module-catalog.md](02-module-catalog.md) → Branding and configured in **Settings → Branding**.
- **Theming**: support for light/dark mode and configurable accent colors, consistent with the reference app's personalization.
- **Print/Export fidelity**: receipts, hall tickets, and reports render cleanly for print and export (PDF/CSV).

---

## 8. Consistency Across Surfaces

| Surface | Experience commitment |
|---------|----------------------|
| **Web ERP** | Full module experience with sidebar shell, tables, forms, dialogs, charts. |
| **Flutter App** | Same information architecture and workflows, adapted to mobile; role-adaptive. |
| **Public Website** | Unchanged existing site; receives synchronized notices and galleries (see [04-website-mobile-integration.md](04-website-mobile-integration.md)). |

Permissions and data scope are applied identically across web and mobile, so a user sees a consistent
set of capabilities everywhere.

---

## 9. Design System Direction (intent, not implementation)

- A **reusable component library** underpins both web and mobile so cards, tables, forms, and dialogs behave consistently (supports the "reusable components" product principle).
- Shared patterns for: list+filter+export, create/edit modal, detail+drill-down, status workflow, and dashboard card grids.
- The component library evolves as the single source of UI truth; new modules compose from it rather than inventing new patterns.

---

## 10. Out of Scope (this document)

- Pixel-level visual specs, color tokens, spacing scales (belong to the design system / SRS UI spec).
- Component implementation in React/Flutter (belongs to architecture docs).
- Specific screen-by-screen wireframes (belong to UX design deliverables derived from this PRD).
