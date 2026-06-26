# 06 – Finance Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Finance domain owns the school's **money lifecycle**: what fees are owed (fee structures), the
recurring monthly obligation (dues), the money received (payments and receipts), online payment
transactions and refunds, and the **non-fee day-book** of other income and expenses. It is the single
financial system of record for the school.

---

## 2. Responsibilities

- Define **fee structures** per class (category, amount, frequency, due day, late fee, tax, installments).
- Generate and track **monthly fee dues** per student (auto-generated from admission month forward, idempotent).
- Record **fee payments** and issue **receipts**, including bulk and multi-month payment.
- Process **online payments** through gateways and reconcile them into payments/dues.
- Process **refunds** and **receipt cancellations** under authorization.
- Maintain the **day-book** of non-fee income and expenses (donations, rent, salaries, utilities, etc.).
- Provide the financial data consumed by collection, dues, cash-book, and income/expense reports.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Fee Structure** | A fee item for a class (category, frequency, amount, year); unique per class/category/frequency/year. | ✓ |
| **Fee Due** | A student's monthly obligation for a fee item (billing month, status pending/paid/partial/waived). | ✓ |
| **Fee Payment / Receipt** | Money received from/for a student; identified by a unique receipt number; carries status, mode, amounts. | ✓ |
| **Refund** | A refund recorded against a fee payment (amount, reason, date). | — (within Fee Payment) |
| **Gateway Transaction** | An online payment attempt/result via a gateway (mode test/live, status). | ✓ |
| **Account Transaction** | A non-fee day-book entry (income or expense, category, mode, amount). | ✓ |

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Student** | Student | Dues and payments are for a student. |
| **Class** | Academic | Fee structures belong to a class. |
| **Admission** | Student | An admission's enrollment raises the admission fee payment. |
| **Staff / User (collected-by, recorded-by)** | Staff / Foundation | The actor collecting/recording the money. |
| **Number Sequence (receipt/invoice)** | Foundation | Receipt and invoice numbers are issued centrally. |
| **Audit, Communication Log** | Foundation / Communication | Financial actions are audited; receipts are emailed (logged in Communication). |
| **Payment Gateway settings** | Foundation | Gateway configuration and secrets. |

---

## 5. Relationships

- A **Fee Structure** belongs to one **Class** and applies for one **academic year**; it drives **Fee Dues** generation for students of that class.
- A **Fee Due** belongs to one **Student** and one **Fee Structure** for a billing month; many dues form the student's outstanding picture.
- A **Fee Payment/Receipt** is for one **Student**; it may settle one or many **Fee Dues** (multi-month payment) and may reference a **Fee Structure**; it may have a **Refund**.
- A **Gateway Transaction** corresponds to an online **Fee Payment** attempt and reconciles into a payment on success.
- An **Account Transaction** stands alone (non-fee), categorized as income or expense.
- A **Fee Payment** is **collected by** a **Staff/User**; an **Account Transaction** is **recorded by** a **Staff/User**.

---

## 6. Business Boundaries

**Inside:** fee structures, dues, payments/receipts, refunds, gateway transactions, and the non-fee day-book.

**Outside (not owned here):**
- **Student** and **Class** definitions — owned by Student and Academic (Finance references them).
- **Receipt/invoice number issuing** — performed by Foundation's number generator (Finance requests numbers).
- **The act of sending a receipt email** — performed by the Communication domain (Finance triggers it; the communication log is owned there).
- **Payroll** (salary computation) — a future domain; Version 1 records salary only as a day-book **expense**.

**Consistency boundaries:**
- A Fee Payment with its settled Dues and any Refund is one consistency boundary (multi-month payment marks all selected dues atomically).
- Dues generation is idempotent within the student/fee/month boundary.
- A Gateway Transaction's reconciliation into a payment is one boundary (idempotent on retry).

---

## 7. Dependency Rules

- Finance **depends on Student** (payer), **Academic** (class for fee structures), and **Foundation** (numbering, audit, gateway config); it **references Admission** (admission fee) and **Staff** (collector).
- Finance must **not depend on** Attendance, Examination, or Asset.
- Communication **references** Finance events (receipt/reminder) to send messages; Reports **read** Finance data (collection, dues, cash book, income/expense).
- Finance owns the **financial truth**; other domains never write financial records — they trigger Finance through its services/events.
