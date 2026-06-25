# 07 – Online Payment Strategy

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines the online payment
> capability at a **product** level — supported gateways, modes, logs, refunds, and pluggability. No
> implementation, provider APIs, keys, or schemas.

---

## 1. Purpose

Enable students and parents to pay school fees online, and enable finance staff to reconcile those
payments inside the ERP's fee lifecycle. Online payment complements existing offline collection
(cash/cheque/etc.) and feeds the same Fee Collection, Fee Dues, and Accounts records.

---

## 2. Supported Gateways (Version 1)

| Gateway | Status |
|---------|--------|
| **Razorpay** | Supported |
| **PhonePe** | Supported |
| **Cashfree** | Supported |

### 2.1 Pluggability
Payment gateways are **pluggable**. Future gateways must be addable **easily**, without redesigning the
payment model or the fee modules. Each school configures one or more gateways and selects the active
gateway(s) for collection.

---

## 3. Operating Modes

| Mode | Purpose |
|------|---------|
| **Test Mode** | Sandbox configuration for safe verification before go-live; no real money moves. |
| **Live Mode** | Production configuration; real transactions. |

A school can switch a gateway between Test and Live in its gateway settings. Mode is clearly indicated
to finance users to prevent accidental live/test confusion.

---

## 4. Core Capabilities

| Capability | Description |
|------------|-------------|
| **Online Fee Payment** | Students/parents pay due fees online from web and the Flutter app. |
| **Transaction Logs** | Every payment attempt and result is logged (initiated/success/failure/pending). |
| **Refund Support** | Authorized finance roles can issue refunds against eligible payments. |
| **Reconciliation** | Online payments reconcile into Fee Collection, update Fee Dues, and reflect in Accounts and receipts. |

---

## 5. Payment Flow (conceptual)

```
Student / Parent selects dues to pay
        │
        ▼
ERP initiates payment via configured gateway (Test or Live)
        │
        ├─ success  ──▶ record Fee Payment + receipt ──▶ mark dues paid ──▶ transaction log ──▶ notify (email/SMS/push)
        ├─ pending  ──▶ transaction log (pending) ──▶ await confirmation
        └─ failure  ──▶ transaction log (failed) ──▶ user can retry
```

- Successful online payments generate a **receipt** and update **Fee Dues** exactly as offline payments do (see [02-module-catalog.md](02-module-catalog.md) Fee Collection / Fee Dues).
- Payment confirmations and receipts are delivered through the Communication module (email/SMS/push) and logged.

---

## 6. Refunds

| Aspect | Rule (product-level) |
|--------|---------------------|
| **Who** | Only roles with the **Approve/refund** permission on Fee Collection (Administrator/Accountant). |
| **Eligibility** | Refund amount cannot exceed the original amount paid; a reason is required. |
| **Effect** | The payment is marked refunded; refund details (amount, date, reason) are recorded; transaction log updated. |
| **Audit** | Every refund is audited and logged. |

These rules preserve the validated refund workflow from the reference application.

---

## 7. Transaction Logs

Every online payment and refund produces a transaction log entry capturing at minimum: gateway, mode
(test/live), amount, status, reference identifiers, related student/fee, initiating user, and
timestamps. Transaction logs support:

- **Reconciliation** of online collections against gateway settlements.
- **Audit** of all money movement.
- **Troubleshooting** of failed/pending payments.

Transaction logs are viewable by Accountant and Administrator per the permission matrix.

---

## 8. Gateway Settings

Configured in the Settings / Payment Gateway module (school-owned):

- Select and configure gateway(s): Razorpay, PhonePe, Cashfree.
- Set **Test** or **Live** mode per gateway.
- Store gateway credentials/secrets securely (never exposed in UI or logs).
- Enable/disable online payment as a collection method.

---

## 9. Security & Compliance Principles

1. **Secret protection** — gateway keys/secrets are stored securely and never displayed or logged.
2. **No card data handling** — payment is delegated to the gateway; the ERP does not store sensitive payment instrument data.
3. **Mode clarity** — test vs. live is unambiguous to finance users.
4. **Audit everything** — all payments and refunds are logged and auditable.
5. **Reconciliation integrity** — online payments map cleanly into the single fee record of truth.

---

## 10. Integration Points

| Module | Relationship |
|--------|--------------|
| **Fee Collection** | Online payments are recorded as fee payments with receipts. |
| **Fee Dues** | Paid dues are marked settled; multi-month payment supported. |
| **Accounts** | Collections reflect in financial reporting. |
| **Communication** | Confirmations/receipts delivered via email/SMS/push and logged. |
| **Reports** | Online collections appear in fee collection and cash-book reporting. |

---

## 11. Out of Scope (Version 1)

- Gateways beyond Razorpay, PhonePe, Cashfree (future, pluggable).
- Subscription/auto-debit/mandate-based recurring collection.
- Wallet/stored-value balances.
- Splitting settlements across multiple bank accounts/branches (relates to future multi-branch).
