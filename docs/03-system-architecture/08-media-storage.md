# 08 – Media & Storage Architecture

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Companion to [00-system-overview.md](00-system-overview.md). Defines the **single Media Library**
> that manages all files across the product. One library, one access model, one upload pipeline. No
> code, no schemas.

---

## 1. Principle: One Media Library

Per the PRD, there is **one centralized Media Library**. Every module that needs to store or serve a
file uses this single service — modules never implement their own storage.

```
Any module needing a file
        │
        ▼
┌───────────────────────────┐
│      Media Library         │  (single Core service)
│  validate → store → index  │
│  access-control → serve    │
└──────────┬────────────────┘
           ▼
   Storage backend (abstracted: local now, cloud/CDN-ready)
```

---

## 2. Media Categories

| Category | Examples | Visibility |
|----------|----------|-----------|
| **Branding assets** | Logo, dark logo, favicon, login background, principal signature, school stamp, report/receipt/ID-card logos | Public/app as applicable |
| **Documents** | Student/parent/staff/asset documents (polymorphic) with verification | Authenticated, scoped |
| **Photo Gallery** | Public album images | Public (when published) |
| **Video Gallery** | Public videos (links/uploads) | Public (when published) |
| **Profile images** | User/student/parent profile photos | Authenticated, scoped |
| **Generated documents** | Receipts, hall tickets, ID cards, report exports | Authenticated, scoped / printable |
| **Leave/attachment files** | Substitute leave docs, complaint/helpdesk attachments | Authenticated, scoped |
| **Import/Export artifacts** | Uploaded import files, generated export files | Authenticated, scoped, time-limited |

---

## 3. Storage Abstraction

- The Media Library exposes a **storage-agnostic interface**; the backend (local filesystem in V1) sits behind it.
- The abstraction is **cloud/CDN-ready**: object storage and a CDN can be introduced later without changing module code (extensibility mandate).
- Configuration (Settings) selects the active backend; no module hard-codes a storage path.

---

## 4. Upload Pipeline

```
Upload request (authenticated, permission-checked)
   ▼
Validate  → type, size, extension, content sanity
   ▼
Store     → write to backend via abstraction; assign stable identifier
   ▼
Index     → record metadata (owner module, entity, type, visibility, timestamps)
   ▼
Audit     → log the upload event
   ▼
Return reference (not a raw path)
```

- Modules receive a **reference/handle**, never a raw storage path.
- Validation rules (allowed types/sizes) are centralized and configurable.

---

## 5. Access Control

Media access is governed by the **same RBAC + scope** model as the rest of the system:

| Visibility | Rule |
|------------|------|
| **Public** | Only items explicitly published public (gallery, public branding) are served without auth. |
| **Authenticated + scoped** | Documents/profile/generated files honour role + data scope (own/linked/assigned/all). |
| **Restricted** | Sensitive documents (e.g., safeguarding) are access-scoped and audited. |

The Media Library checks authorization before serving; it does not expose direct, unguarded storage URLs for protected media.

---

## 6. Website & App Sync (gallery/notices)

- **Photo Gallery** and **Video Gallery** items marked public are exposed through the **one-way outward sync feed** consumed by the public website and the Flutter app ([04-website-mobile-integration.md](../00-product/04-website-mobile-integration.md)).
- Only **published** items are exposed; nothing else from the Media Library is public.
- Sync is ERP → website/app only.

---

## 7. Branding Asset Management

- All branding assets are stored and versioned in the Media Library and applied across web, mobile, public website, and printable documents.
- The Branding capability and Settings → Branding reference these assets; rendering surfaces pull them through the library.

---

## 8. Generated Documents

- Receipts, hall tickets, ID cards, and report exports are produced by their modules and stored/served via the Media Library when persistence is needed, with the same access control and audit.
- Print/Export actions are permission-gated.

---

## 9. Retention, Naming & Integrity

| Concern | Approach |
|---------|----------|
| **Naming** | Stable, opaque identifiers; no sensitive data in names. |
| **Metadata** | Owner module, linked entity, type, visibility, timestamps, verification state. |
| **Retention** | Configurable retention for transient artifacts (import/export files); long-lived for records/branding. |
| **Integrity** | Validation on upload; audit on upload/verify/delete; soft-handling consistent with module policy. |
| **Backups** | Media is included in backup scope ([13-deployment-architecture.md](13-deployment-architecture.md)). |

---

## 10. Tenant Readiness

- Media organization is **tenant-aware-ready**: a future tenant boundary can partition media without redesigning the library or modules.

---

## 11. Non-Goals

- No storage schema or path scheme design here.
- No specific cloud provider chosen (kept pluggable).
- No code.
