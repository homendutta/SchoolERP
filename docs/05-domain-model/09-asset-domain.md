# 09 – Asset Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Asset domain owns the school's **physical resources**: fixed **assets** and their **maintenance**,
and consumable **inventory** (stock items) with their **stock movements**. It tracks what the school
owns, where it is, its condition/value, and what is consumed or reordered.

---

## 2. Responsibilities

- Maintain the **fixed-asset register** (tag, category, purchase, vendor, warranty, location, assignment, condition, status, depreciation/current value).
- Record **asset maintenance** history (type, cost, next-due, warranty claims).
- Maintain **inventory items** (codes, units, reorder/minimum levels, vendor, cost, expiry).
- Record **stock transactions** (in / out / adjustment) and keep current stock consistent.
- Raise **reorder alerts** when stock reaches reorder/minimum levels.
- Support optional **approval** for high-value stock issues.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Asset** | A fixed asset; identified by a unique asset tag. | ✓ |
| **Asset Maintenance** | A maintenance event for an asset (type, cost, next-due, warranty claim). | — (within Asset) |
| **Stock Item** | A consumable inventory item; identified by a unique item code. | ✓ |
| **Stock Transaction** | An inventory movement (in/out/adjustment) that updates the item's current stock. | — (within Stock Item) |
| **Reorder Alert** | A derived signal that an item is at/below its reorder or minimum level. | — (derived) |

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Staff / User (assigned-to, performed-by, approved-by)** | Staff / Foundation | Assets are assigned to staff; transactions are performed/approved by users. |
| **Number Sequence (asset number)** | Foundation | Asset numbers/tags may be centrally issued. |
| **Media Asset (photos, receipts)** | Foundation | Asset photos and maintenance receipts. |
| **Audit** | Foundation | Asset and stock actions are audited. |
| **Vendor** | Asset (or referenced) | Supplier of an asset/item (modeled within this domain or as a referenced concept). |

---

## 5. Relationships

- An **Asset** has many **Asset Maintenance** records and may be **assigned to** a **Staff** member.
- A **Stock Item** has many **Stock Transactions**; each transaction adjusts the item's current stock (in adds, out subtracts with sufficiency check, adjustment sets).
- A **Stock Transaction** is **performed by** a **Staff/User** and may require an **approver** for high-value issues.
- A **Reorder Alert** is derived from a **Stock Item** crossing its reorder/minimum threshold.
- Assets and items may reference a **Vendor** (supplier).

---

## 6. Business Boundaries

**Inside:** the asset register, maintenance history, inventory items, stock movements, and reorder signals.

**Outside (not owned here):**
- The **staff person** an asset is assigned to — owned by Staff (Asset references the assignee).
- **Asset/maintenance financial postings** — if treated as expenses, they are recorded in the Finance day-book (Finance owns the financial entry; Asset owns the asset/maintenance record).
- **Document/media files** — stored via Foundation's media library.
- **Future Visitor Pass / facility entities** — may join this or a related operations domain later without redesign.

**Consistency boundaries:**
- An Asset with its maintenance history is one boundary.
- A Stock Item with its transactions and current-stock value is one boundary; a stock transaction updates current stock atomically with the movement and enforces the sufficiency rule for "out".

---

## 7. Dependency Rules

- Asset **depends on Foundation** (numbering, media, audit, authorization) and **references Staff** (assignee/performer/approver).
- Asset must **not depend on** Academic, Student, Attendance, Examination, Finance, or Communication.
- Finance may record asset-related **expenses** in its day-book by referencing asset/maintenance events; Reports **read** asset/inventory data (reorder alerts, registers).
- Asset owns the **physical-resource truth**; other domains never mutate asset/stock records — they reference or trigger them through Asset services.
