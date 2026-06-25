# 05 – Authentication Strategy

**Product:** SchoolERP
**Status:** Approved
**Version:** 1.1
**Last updated:** 2026-06-26

> Companion to [00-product-requirements.md](00-product-requirements.md). Defines identities, login
> rules, automatic account generation, and the password lifecycle at a **product** level. No
> implementation, token formats, or schemas — those belong to the SRS and downstream design.

---

## 1. Identity Types

The product recognizes three identity types plus the system owner:

| Identity | Who | Where they sign in |
|----------|-----|--------------------|
| **Super Admin** | Vendor / system owner | System/administration surface |
| **Staff** | Administrator, Supervisor, Clerk, Accountant, Receptionist, Teacher (and future staff roles) | School ERP |
| **Student** | Enrolled student | School ERP |
| **Parent** | Parent/guardian | School ERP |

All identities use **one unified login experience** (one login page) with identifier rules that vary
by identity type. Role and permissions determine the workspace shown after login.

---

## 2. Super Admin

The **Super Admin** is the system owner (vendor), not a school user. Responsibilities:

- **Licensing** — issue/manage the school's license.
- **System Updates** — deploy product updates to the installation.
- **Global Configuration** — installation-wide settings.
- **Backup** — data backup and recovery operations.
- **System Administration** — operational control of the installation.

The Super Admin sits **above** the school's Administrator and is the only identity with **System**
scope. In Version 1 (single-tenant) the Super Admin operates one installation; the role is designed so
that, under a future SaaS model, it can govern many.

---

## 3. School Administrator

The **Administrator** is the school's top operational user. Responsible for managing the school:
configuring modules, managing users/roles, and overseeing all operational modules. The Administrator
does **not** perform system-owner functions (licensing, updates, backups) — those are Super Admin.

---

## 4. Login Rules by Identity

### 4.1 Staff
Staff may log in using any of:
- **Staff Number** (employee code)
- **Mobile Number**
- **Email Address**

### 4.2 Student
Students log in using:
- **Admission Number**

**Admission Number requirements:**
- **Numeric only**
- **Maximum 6 digits**
- **Unique** across the school

### 4.3 Parent
Parents may log in using any of:
- **Parent ID**
- **Mobile Number**
- **Email Address**

### 4.4 Unified Resolution
The single login attempts to resolve the supplied identifier against the appropriate identity type and
authenticates accordingly. A successful login establishes the user's role(s), permissions, and data
scope, then routes to the correct workspace.

---

## 5. Automatic Account Generation

Whenever a **Staff**, **Student**, or **Parent** record is **successfully created**, the system
automatically performs the following:

1. **Create login account** — provision the user's credentials/identity for sign-in.
2. **Generate a temporary password** — system-generated, secure, one-time.
3. **Send SMS** — if SMS is enabled, deliver credentials/welcome via SMS.
4. **Send Email** — if Email is enabled, deliver credentials/welcome via email.
5. **Record a communication log** — log every credential message sent (channel, recipient, status).
6. **Record an audit log** — log the account-creation event (actor, subject, timestamp).

> Account generation is **automatic and atomic with record creation** — creating a person record always
> yields a usable login. This preserves the validated workflow from the reference application.

### 5.1 Channel Toggles
SMS and Email sending are governed by the school's enable/disable toggles and configured gateways (see
[06-communication-strategy.md](06-communication-strategy.md)). If a channel is disabled or unavailable,
the failure is logged and does not block account creation.

---

## 6. First Login & Password Lifecycle

### 6.1 Forced Password Change
On **first login** with a temporary password, the user is **forced to change their password** before
accessing any module.

### 6.2 Password Rules (product-level)
- Temporary passwords are single-use and expire on first successful change.
- Password strength requirements are enforced (specifics defined in the SRS).
- Users can change their own password from their account at any time.
- Self-service password change for student/parent keeps all of the user's login identities consistent.

### 6.3 Password Reset
- Administrators (with permission) can reset a user's password, which re-issues a temporary password and re-triggers forced change on next login.
- A documented recovery path exists for the Administrator/Super Admin to restore access.

---

## 7. Sessions

- Authenticated sessions have a bounded lifetime; expired sessions require re-login.
- The same authentication and session model governs both the web app and the single Flutter mobile app.
- Session and identity context determine which menus, modules, and data scope are presented.

---

## 8. Security Principles for Authentication

| Principle | Requirement |
|-----------|-------------|
| Least privilege | No access without an explicit permission grant (per role/permission matrix). |
| Credential protection | Credentials and secrets are stored securely; never exposed in logs or UI. |
| Forced rotation | Temporary passwords must be changed on first use. |
| Auditability | Login successes/failures and account/permission changes are audited. |
| Channel safety | Credential delivery uses configured, school-owned gateways; deliveries are logged. |
| Scope enforcement | Data scope (own/linked/assigned/all) is enforced on every authenticated request. |

---

## 9. Account Lifecycle Summary

```
Create Staff/Student/Parent record
        │
        ▼
Auto-create login account ──▶ generate temporary password
        │
        ├─▶ send SMS (if enabled)  ──▶ communication log
        ├─▶ send Email (if enabled) ─▶ communication log
        └─▶ audit log (account created)
        │
        ▼
First login (temporary password) ──▶ FORCE password change
        │
        ▼
Active account (role + permissions + data scope)
        │
        ├─▶ self password change (kept consistent across identities)
        └─▶ admin reset ──▶ new temporary password ──▶ force change again
```

---

## 10. Forward Compatibility

- The identity model (staff/student/parent + Super Admin) is designed so additional staff roles and future identity-bearing modules (e.g., Librarian) integrate without redesign.
- Under a future multi-school SaaS model, identity resolution becomes tenant-aware while the login rules per identity type remain the same.

---

## 11. Security Controls (Expanded)

Beyond the authentication-specific principles in §8, the product provides the following security
controls, configured in **Settings → Security**:

| Control | Description |
|---------|-------------|
| **Password Policy** | Configurable complexity, length, expiry, and reuse rules; enforced whenever a password is set or changed. |
| **Account Lockout** | Temporary lockout after repeated failed login attempts; protects against brute-force. |
| **Login History** | Per-user record of successful and failed logins (time, identifier used, result). |
| **Device History** | Record of the devices/clients used to access an account. |
| **Session Management** | Bounded session lifetime; view and terminate active sessions; consistent across web and the single mobile app. |
| **First Login Password Change** | Temporary passwords must be changed on first login (see §6.1). |
| **Audit Logs** | Authentication and security events — login, logout, failed login, password reset, role changes, permission changes — are recorded centrally in [02-module-catalog.md](02-module-catalog.md) → Audit Logs, and are **searchable, filterable, and exportable**. |

These controls reinforce the product's **Security-first** principle and are fully auditable.
