# 04 – Website & Mobile Integration Strategy

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines how the ERP, the
> school's existing public website, and the single Flutter mobile app form **one ecosystem**, and
> exactly what content synchronizes between them. Product-level only — no implementation detail.

---

## 1. Principle: One Ecosystem, One Domain

The ERP and the school's public website are **not** separate products on separate domains. They share
the school's single domain. The ERP lives under that domain's paths; the public website remains the
school's existing site.

- **Do NOT** create a separate ERP domain.
- **Do NOT** build a Website CMS — the existing HTML/CSS/JavaScript website stays as-is and remains the school's public website.
- The ERP **augments** the website by feeding it selected content automatically.

---

## 2. Domain & URL Structure

Using `schoola.com` as the example school domain:

### 2.1 Public Website (existing site — unchanged)
```
schoola.com/                 → Home
schoola.com/about            → About
schoola.com/facilities       → Facilities
schoola.com/gallery          → Photo Gallery (fed by ERP)
schoola.com/videos           → Video Gallery (fed by ERP)
schoola.com/notice-board     → Public Notice Board (fed by ERP)
schoola.com/contact          → Contact
schoola.com/login            → Login (entry to ERP)
```

### 2.2 ERP (under the same domain)
```
schoola.com/login            → Unified login
schoola.com/admin            → Administrator workspace
schoola.com/teacher          → Teacher workspace
schoola.com/student          → Student workspace
schoola.com/parent           → Parent workspace
```

> Additional role workspaces (supervisor, clerk, accountant, receptionist) follow the same pattern.
> The **single source of truth for routing** is: public marketing pages = existing website; everything
> behind login = ERP, all under one domain.

---

## 3. Synchronized Content (ERP → Website & App)

The ERP synchronizes **only** these three content types outward. Nothing else from the ERP is exposed
publicly.

| Content | Synced to Website | Synced to Flutter App | Trigger |
|---------|:-----------------:|:---------------------:|---------|
| **Public Notices** | ✓ | ✓ | When a notice is published with a public/website destination |
| **Photo Gallery** | ✓ | ✓ | When an album/photo is published |
| **Video Gallery** | ✓ | ✓ | When a video is published |

### 3.1 Behaviour
- When a gallery item or notice is **updated/published in the ERP**, it must **automatically appear** on both the website and the Flutter app — no manual re-entry on the website.
- Synchronization is **one-way** (ERP → website/app) for these content types. The website does not push content back into the ERP.
- Notices are **selectively** public: a notice publishes to a chosen set of destinations (internal ERP, website, app, push, SMS, email). Only those marked for **website/app** appear publicly. (See [06-communication-strategy.md](06-communication-strategy.md).)
- Galleries published to public are visible without login; everything else requires authentication.

### 3.2 Not Synchronized
Academic records, fees, attendance, marks, personal data, and all other ERP content remain **inside
the ERP** and are never published to the public website.

---

## 4. Public vs. Authenticated Surfaces

| Surface | Audience | Access |
|---------|----------|--------|
| Marketing pages (Home, About, Facilities, Contact) | Public | No login |
| Public Notice Board | Public | No login (only notices marked public) |
| Photo Gallery / Video Gallery (public) | Public | No login (only published items) |
| Login | Public entry point | No login |
| ERP workspaces (`/admin`, `/teacher`, …) | Authenticated users | Login + role |

---

## 5. Mobile Strategy: One Flutter App for Every Role

### 5.1 Single App Principle
There is exactly **one** Flutter application.

- **Do NOT** build separate apps per role.
- The same app serves: Super Admin, Administrator, Supervisor, Clerk, Accountant, Receptionist, Teacher, Student, Parent.

### 5.2 Role-Adaptive Experience
After login, the app **automatically** adapts:
- **Dashboard** changes to the role's view.
- **Menus/navigation** show only the modules and actions the user is permitted (per [03-role-permission-matrix.md](03-role-permission-matrix.md)).
- **Data scope** is enforced (own/linked/assigned/all).

The user never chooses an "app mode"; identity and permissions drive everything.

### 5.3 Mobile Content Parity
The app surfaces the same synchronized public content (notices, galleries) plus the authenticated,
role-appropriate ERP features (e.g., a parent pays fees and books PTM; a teacher marks attendance).

### 5.4 Push Notifications
The app is the delivery target for **Push Notifications** (notices, fee reminders, attendance/result
alerts). Push is one of the publishing destinations in the Communication module.

---

## 6. Ecosystem Diagram (conceptual)

```
                         ┌─────────────────────────────┐
                         │           SchoolERP          │
                         │  (single-tenant, one domain) │
                         └──────────────┬──────────────┘
            publish (one-way)           │            authenticated features
        ┌───────────────────────────────┼───────────────────────────────┐
        ▼                               ▼                                ▼
┌───────────────┐              ┌──────────────────┐            ┌──────────────────┐
│ Public Website │◀── notices ─│  Notices &        │─ notices ─▶│  Flutter App      │
│ (existing      │◀── gallery ─│  Galleries        │─ gallery ─▶│  (all roles,      │
│  HTML/CSS/JS)  │             │  (ERP-managed)    │  + push    │   role-adaptive)  │
└───────────────┘              └──────────────────┘            └──────────────────┘
   public pages,                                                 dashboards, fees,
   no login                                                      attendance, marks…
```

---

## 7. Constraints & Rules (recap)

1. One domain for website + ERP; **no separate ERP domain**.
2. **No website CMS**; the existing public website is retained.
3. ERP synchronizes outward **only**: Public Notices, Photo Gallery, Video Gallery.
4. Synchronization is **automatic** on publish and reflected on website **and** app.
5. Exactly **one** Flutter app, role-adaptive after login.
6. Public content is viewable without login; everything else is authenticated and permission-scoped.

---

## 8. Future Extensibility

- The same sync mechanism should generalize to additional public content types later (e.g., events, achievements) without redesign.
- Under a future multi-school SaaS model, each school keeps its own domain and its own public-content sync boundary; the single-app, role-adaptive principle remains unchanged.
