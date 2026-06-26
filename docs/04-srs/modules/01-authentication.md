# SRS – Authentication Module

**Module code:** `AUTH`
**Status:** Draft
**Version:** 1.0
**Last updated:** 2026-06-26
**Traces to PRD:** `docs/00-product/05-authentication-strategy.md` · `docs/00-product/00-product-requirements.md`
**Architecture:** `docs/03-system-architecture/07-security-architecture.md` · `06-api-architecture.md` · `04-frontend-architecture.md` · `05-mobile-architecture.md`

> This specification follows the Standard Module Specification Template
> ([../02-module-specification-template.md](../02-module-specification-template.md)). It references the
> System-Wide Requirements ([../01-system-wide-requirements.md](../01-system-wide-requirements.md))
> rather than restating them. It preserves the reference application's login workflow and UI/UX while
> implementing the approved product behaviour. No API, database, or code design is included.

---

## 1. Purpose

The Authentication module establishes and verifies the identity of every user and governs the
password and session lifecycle. It provides one unified login that authenticates the three identity
types (staff, student, parent) and the system owner (Super Admin), provisions login accounts
automatically when person records are created, forces a password change at first login, and records
all authentication events for audit. It is the security entry point for both the web ERP and the
single Flutter app, which share one API and one domain.

---

## 2. Scope

**In scope:**
- Unified login resolving staff, student, and parent identities.
- Staff login (Staff Number / Mobile / Email).
- Student login (Admission Number only).
- Parent login (Parent ID / Mobile / Email).
- Super Admin login (system-owner identity).
- Automatic account provisioning with temporary password on person-record creation.
- First-login forced password change.
- Session management (establishment, lifetime, view/terminate, expiry, logout).
- Password reset (administrator-initiated) and self-service password change.
- Authentication audit (login, logout, failed login, password reset).
- Website login surface (under one domain) and Flutter login surface (same API).
- Enforcement of the password policy, account lockout, login history, and device history controls.

**Out of scope (owned by other modules, referenced here):**
- Creating/editing/suspending the underlying person records and accounts — owned by **Users / Staff / Students / Parents / Admissions** modules (this module consumes their "record created" events to provision logins).
- Defining roles/permissions — owned by **Role & Permission** module.
- Sending mechanics of SMS/Email — owned by **Communication / Notification Service**.
- Future multi-tenant identity federation (out of Version 1 scope).

**Dependencies (modules):** Users, Staff, Students, Parents, Admissions (record sources); Role & Permission (authorization context); Communication / Notification Service (credential delivery); Number Generator (identifier rules); Settings → Security (policy configuration); Audit Logs.

---

## 3. Actors

| Actor | Type | Interaction with this module | Data scope |
|-------|------|------------------------------|------------|
| Super Admin | Role | System-owner login; system-level administration of the installation. | system |
| Administrator | Role | Logs in; may reset other users' passwords; views authentication audit. | all |
| Supervisor / Accountant / Clerk / Receptionist / Teacher | Role | Log in; manage own session and password. | self |
| Student | Role | Logs in with Admission Number; manages own password/session. | own |
| Parent | Role | Logs in with Parent ID / Mobile / Email; manages own password/session. | linked |
| Notification Service | System | Delivers credentials and password-reset messages. | n/a |
| Number Generator | System | Supplies/validates identifier rules (e.g., Admission Number). | n/a |
| Web ERP client | System | Presents login under one domain; consumes the API. | n/a |
| Flutter app | System | Presents login; consumes the same API; registers device. | n/a |

---

## 4. Preconditions

- The installation is configured (school settings, Security settings such as password policy, lockout, session lifetime) — references SYS-CFG-002.
- For a user to authenticate, a corresponding person record and login account exist and are active (created via the owning module + automatic provisioning, FR-AUTH-020).
- System-wide authentication and authorization baselines are in force: SYS-AUTH-001, SYS-RBAC-001/002, SYS-API-004 (HTTPS).
- Communication channels are configured if credential delivery is expected (SYS-ACCT-002); their absence does not block provisioning (SYS-ACCT-004).

---

## 5. Login Workflows

### 5.1 Unified login (primary)
```
User opens /login (web) or app login (mobile)
   ▼
Enters identifier + password
   ▼
System resolves identity in order:  Staff → Student → Parent
   ▼
For the matched identity, apply login gates:
   • account not deleted
   • account status = active
   • password matches
   ▼
On success → establish session (role + permissions + scope; student/parent scoping context)
           → stamp last login · record login history · register device
           → audit "login success"
           → if temporary password / first login → force password change (5.4)
           → route to role-based landing
On failure → audit "login failed" (reason) · increment failed-attempt counter (→ lockout, 5.6)
```

### 5.2 Staff login
- Identifier may be **Staff Number**, **Mobile Number**, or **Email Address** (case-insensitive, trimmed).
- Resolves to the staff identity; gates and session as in 5.1.

### 5.3 Student login
- Identifier is **Admission Number only** (numeric, ≤ 6 digits, unique).
- Resolves to the student identity; session carries own-student scope (student id, class).

### 5.4 Parent login
- Identifier may be **Parent ID**, **Mobile Number**, or **Email Address**.
- Resolves to the parent identity; session carries linked-children scope.

### 5.5 Super Admin login
- The Super Admin authenticates as the system-owner identity, distinct from and above the school Administrator, with system-level scope.

### 5.6 First-login forced password change
```
Login success with temporary password (or "must change" flag set)
   ▼
System blocks access to all modules
   ▼
User sets a new password (validated against password policy)
   ▼
Temporary password invalidated · "must change" cleared · change synced across the user's identities
   ▼
Audit "password changed (first login)" · proceed to landing
```

### 5.7 Account lockout (exception path)
```
Repeated failed logins reach the configured threshold
   ▼
Account temporarily locked · audit "account locked"
   ▼
Further attempts rejected until lockout expires or admin reset
```

### 5.8 Password reset (administrator-initiated)
```
Authorized admin triggers reset for a user
   ▼
System issues a new temporary password · sets "must change"
   ▼
Notification Service delivers credentials (enabled channels) · communication log
   ▼
Audit "password reset" · next login forces change (5.6)
```

### 5.9 Logout / session expiry
```
User logs out  OR  session lifetime is exceeded
   ▼
Session invalidated · audit "logout" / "session expired"
   ▼
Protected access requires re-login
```

---

## 6. Functional Requirements

| ID | Requirement (the system shall…) | Pri | Verify | Source/Trace |
|----|--------------------------------|:--:|:--:|--------------|
| FR-AUTH-001 | Provide a single unified login that accepts one identifier + password and resolves it to a staff, student, or parent identity. | M | T | SYS-AUTH-001; PRD 05 |
| FR-AUTH-002 | Resolve the supplied identifier in the order Staff → Student → Parent, authenticating against the first matching identity. | M | T | PRD 05; BR-Index Auth |
| FR-AUTH-003 | Authenticate staff using Staff Number, Mobile Number, or Email Address (case-insensitive, trimmed). | M | T | SYS-AUTH-002; PRD 05 |
| FR-AUTH-004 | Authenticate students using Admission Number only. | M | T | SYS-AUTH-003; PRD 05 |
| FR-AUTH-005 | Authenticate parents using Parent ID, Mobile Number, or Email Address (case-insensitive, trimmed). | M | T | SYS-AUTH-004; PRD 05 |
| FR-AUTH-006 | Provide Super Admin login as a system-owner identity distinct from, and higher-privileged than, the school Administrator. | M | T | SYS-AUTH-007; PRD 05 |
| FR-AUTH-007 | Reject login when the account is deleted, returning a non-disclosing message. | M | T | SYS-AUTH-005; BR-Index |
| FR-AUTH-008 | Reject login when the account status is not active, indicating the account state. | M | T | SYS-AUTH-005; BR-Index |
| FR-AUTH-009 | Reject login when the password does not match, returning an invalid-credentials message. | M | T | SYS-AUTH-005; BR-Index |
| FR-AUTH-010 | On successful login, establish a session carrying the user's role, permissions, and data scope. | M | T | SYS-RBAC-001/002; Arch 07 |
| FR-AUTH-011 | On successful login of a student or parent, include the scoping context (student → own student/class; parent → linked children/classes). | M | T | PRD 03/05; SYS-RBAC-006 |
| FR-AUTH-012 | Record the last-login timestamp and a login-history entry on each successful login. | M | T | SYS-SEC-003 |
| FR-AUTH-013 | Register the device/client used to log in in the user's device history. | M | T | SYS-SEC-003 |
| FR-AUTH-014 | Route the user to the role-based landing destination after login, consistent with the reference application. | M | D | PRD 08; Arch 04 |
| FR-AUTH-015 | Force a password change before any module access when the user logs in with a temporary password or has the "must change" flag set. | M | T | SYS-AUTH-006; PRD 05 |
| FR-AUTH-016 | Invalidate the temporary password upon a successful first-login password change. | M | T | PRD 05 |
| FR-AUTH-017 | Allow an authenticated user to change their own password after verifying their current password. | M | T | PRD 05 |
| FR-AUTH-018 | Keep a user's password consistent across all of that user's login identities when they change it. | M | T | PRD 05; BR-Index |
| FR-AUTH-019 | Allow an authorized administrator to reset another user's password, issuing a new temporary password and setting "must change". | M | T | PRD 05 |
| FR-AUTH-020 | Automatically create a login account with a system-generated temporary password when a staff, student, or parent record is successfully created. | M | T | SYS-ACCT-001; PRD 05 |
| FR-AUTH-021 | Generate temporary passwords that are system-generated, sufficiently strong, and single-use. | M | T | PRD 05; SYS-SEC-001 |
| FR-AUTH-022 | On account provisioning and on password reset, request credential delivery via SMS and/or Email through the Notification Service when those channels are enabled, recording a communication log. | M | T | SYS-ACCT-002; SYS-NOT-005 |
| FR-AUTH-023 | Not block account provisioning when a communication channel is disabled or failing; such outcomes shall be logged. | M | T | SYS-ACCT-004 |
| FR-AUTH-024 | Provide bounded-lifetime sessions; expire sessions after the configured lifetime and require re-login thereafter. | M | T | SYS-SEC-004; PRD 05 |
| FR-AUTH-025 | Allow a user to view their active sessions and terminate them. | M | T | SYS-SEC-004 |
| FR-AUTH-026 | Allow a user to log out, invalidating the current session. | M | T | PRD 05 |
| FR-AUTH-027 | Temporarily lock an account after the configured number of consecutive failed login attempts and reject further attempts until lockout expiry or administrative reset. | M | T | SYS-SEC-002 |
| FR-AUTH-028 | Apply identical authentication and session behaviour to the web ERP and the Flutter app via the single API. | M | T | SYS-AUTH-008; SYS-API-001 |
| FR-AUTH-029 | Present the login under the school's single domain at /login and route authenticated users to their role workspace, without a separate ERP domain. | M | D | SYS-DEP-002; PRD 04 |
| FR-AUTH-030 | Provide login within the single Flutter app, honouring forced first-login change and device registration. | M | D | SYS-INT-003; PRD 04 |
| FR-AUTH-031 | Conduct all authentication exchanges over HTTPS. | M | I | SYS-API-004; SYS-SEC |
| FR-AUTH-032 | Honour the configured password policy whenever a password is set, changed, or reset. | M | T | SYS-SEC-001; PRD 05 |
| FR-AUTH-033 | Record an audit entry for login, logout, failed login, account creation, forced password change, password reset, and account lockout. | M | T | SYS-AUD-001/002 |

---

## 7. Validation Rules

| ID | Validation rule | On failure | Source/Trace |
|----|-----------------|-----------|--------------|
| VR-AUTH-001 | Both an identifier and a password are required to attempt login. | Reject with "Username and password required". | SYS-AUTH; BR-Index |
| VR-AUTH-002 | The Admission Number used for student login is numeric, maximum 6 digits, and unique. | Reject; not a valid student identifier. | SYS-NUM-004; PRD 05 |
| VR-AUTH-003 | A staff identifier must match an existing, active, non-deleted staff account. | Reject (non-disclosing). | FR-AUTH-007/008 |
| VR-AUTH-004 | A parent identifier must match an existing, active, non-deleted parent account. | Reject (non-disclosing). | FR-AUTH-007/008 |
| VR-AUTH-005 | A new password (first-login, self-change, or reset) must satisfy the configured password policy (complexity, length, expiry, reuse). | Reject with policy message; no change applied. | SYS-SEC-001 |
| VR-AUTH-006 | Self-service password change requires the correct current password. | Reject with "Current password is incorrect". | PRD 05; BR-Index |
| VR-AUTH-007 | A temporary password is single-use and is rejected after it has been changed or has expired. | Reject; require reset. | PRD 05 |
| VR-AUTH-008 | Identifier matching is case-insensitive and trimmed of surrounding whitespace. | n/a (normalization) | BR-Index |

---

## 8. Business Rules

| ID | Business rule | Source/Trace |
|----|---------------|--------------|
| BR-AUTH-001 | Identity resolution follows the fixed order Staff → Student → Parent; the first match authenticates. | PRD 05; BR-Index |
| BR-AUTH-002 | A deleted account can never authenticate, regardless of password. | BR-Index |
| BR-AUTH-003 | Only accounts with status "active" may authenticate; any other status is refused with a state-aware message. | BR-Index |
| BR-AUTH-004 | Automatic account provisioning is performed as part of creating a staff/student/parent record, so creating a person record always yields a usable login. | SYS-ACCT-001; PRD 05 |
| BR-AUTH-005 | A temporary password forces a password change at first login before any module is accessible. | SYS-AUTH-006 |
| BR-AUTH-006 | An administrator password reset re-issues a temporary password and re-applies the forced-change requirement on next login. | PRD 05 |
| BR-AUTH-007 | A user's password change is propagated to keep all of that user's login identities consistent. | PRD 05; BR-Index |
| BR-AUTH-008 | The Super Admin is a system-level identity above the school Administrator and is responsible for installation-level concerns, not school content. | PRD 05 |
| BR-AUTH-009 | Provisioning and credential delivery never block on a disabled or failing communication channel; the outcome is logged instead. | SYS-ACCT-004; SYS-NOT-007 |
| BR-AUTH-010 | Repeated failed logins lead to a temporary account lockout per the configured threshold. | SYS-SEC-002 |
| BR-AUTH-011 | Sessions have a bounded, configured lifetime; on expiry the user must re-authenticate. | SYS-SEC-004; PRD 05 |
| BR-AUTH-012 | Web and mobile share identical authentication, session, and authorization behaviour through the single API. | SYS-AUTH-008; SYS-API-001 |
| BR-AUTH-013 | Authorization (action grant + data scope) is always enforced server-side; client gating is advisory only. | SYS-RBAC-003 |

---

## 9. Permissions

**Applies:** SYS-RBAC-001..008 (action grant + data scope, enforced server-side).

Authentication is the gateway used by all roles; its **self-service** actions are available to every
authenticated user, while **administrative** actions are permission-gated.

| Action | Roles permitted (default) | Scope |
|--------|---------------------------|-------|
| Log in / Log out | All identities | self |
| Change own password | All authenticated users | self / own / linked |
| View / terminate own sessions | All authenticated users | self |
| View own login & device history | All authenticated users | self |
| Reset another user's password | Administrator, Super Admin (with permission) | all / system |
| View authentication audit & login history of others | Administrator, Super Admin | all / system |
| Configure password policy / lockout / session settings | Administrator (Settings → Security), Super Admin | all / system |

**Module-specific permission rules:**
- Creating, suspending, or deleting accounts is governed by the **Users / Staff / Students / Parents** modules, not Authentication.
- Students and parents may never escalate beyond own/linked scope through any authentication action (SYS-RBAC-006).
- Super Admin actions operate at system scope and are distinct from school-Administrator actions.

---

## 10. Notifications

**Applies:** SYS-NOT-001..007 (central Notification Service, channel toggles, logging) and SYS-ACCT-002.

| Trigger | Channels | Audience | Template/Custom |
|---------|----------|----------|-----------------|
| Account provisioned (staff/student/parent) | SMS + Email (enabled channels) | The new user | Credentials/welcome template |
| Administrator password reset | SMS + Email (enabled channels) | The affected user | Password-reset template |
| Account lockout (optional) | Email/Push (if configured) | The affected user / administrator | Lockout template |

- All sends are dispatched through the single Notification Service and recorded in the communication log (SYS-NOT-005).
- Disabled/failed channels degrade gracefully and never block authentication or provisioning (SYS-NOT-007, FR-AUTH-023).

---

## 11. Reports

Authentication does not own end-user reports. It **contributes data** to:

| Report | Description | Visible to | Print/Export |
|--------|-------------|-----------|--------------|
| System Activity Log / Authentication audit | Login, logout, failed login, password-reset, lockout events with actor/time/context. | Administrator, Super Admin | Yes (via Reports / Audit Logs) |
| Login & Device History (per user) | A user's recent logins and registered devices. | Self; Administrator (others) | View/Export |

Rendering and export are provided by the **Reports** and **Audit Logs** capabilities (SYS-AUD-003).

---

## 12. Audit Requirements

**Applies:** SYS-AUD-001..005 (central, searchable, filterable, exportable audit; no secrets logged).

| Auditable event | Captured details |
|-----------------|------------------|
| Login success | actor, identity type, timestamp, device/client context |
| Login failure | attempted identifier (non-sensitive), reason, timestamp |
| Logout / session expiry | actor, session reference, timestamp |
| Account provisioned | subject, actor/source, timestamp |
| Forced first-login password change | actor, timestamp |
| Self-service password change | actor, timestamp |
| Administrator password reset | actor, subject, timestamp |
| Account lockout / unlock | subject, threshold/cause, timestamp |

- Passwords, temporary passwords, tokens, and secrets are **never** recorded in any log (SYS-AUD-005).

---

## 13. UI Preservation Notes

**Applies:** SYS-UI-001..004 (preserve reference UX; never copy code; role-adaptive; shared design system).

The login experience preserves the reference application's look, flow, and behaviour while being
rebuilt on the approved stack; **no reference code is copied**.

- **Single login page:** one unified login screen at `/login` (web) and the equivalent first screen in the Flutter app — one identifier field + password, matching the reference layout and styling.
- **Branding:** school logo, login background, and theme color (from the Branding capability) are applied to the login screen, as in the reference app.
- **Forced password-change screen:** preserves the reference flow that blocks access until a new password is set.
- **Session-expiry behaviour:** preserves the reference behaviour of notifying the user that the session expired and returning them to login.
- **Role-based landing:** after login the user lands on the same role-appropriate destination as the reference (e.g., Administrator → Dashboard; teacher/clerk → primary module; student/parent → Notices).
- **Feedback patterns:** validation and error messages use the reference's clear, non-disclosing messaging and toast/dialog patterns.
- **Mobile parity:** the Flutter login reproduces the same flow and feedback within mobile navigation patterns.

---

## 14. Acceptance Criteria

| ID | Acceptance criterion | Verifies |
|----|----------------------|----------|
| AC-AUTH-001 | Given a valid staff identifier (Staff Number, Mobile, or Email) and correct password, when the user logs in, then a session is established and the user lands on their role workspace. | FR-AUTH-003, FR-AUTH-010, FR-AUTH-014 |
| AC-AUTH-002 | Given a valid Admission Number and correct password, when a student logs in, then the session carries own-student scope. | FR-AUTH-004, FR-AUTH-011 |
| AC-AUTH-003 | Given a valid Parent ID/Mobile/Email and correct password, when a parent logs in, then the session carries linked-children scope. | FR-AUTH-005, FR-AUTH-011 |
| AC-AUTH-004 | Given the same identifier exists across identity types, when login is attempted, then resolution follows Staff → Student → Parent order. | FR-AUTH-002, BR-AUTH-001 |
| AC-AUTH-005 | Given a deleted or non-active account, when login is attempted with the correct password, then login is refused with an appropriate message. | FR-AUTH-007, FR-AUTH-008 |
| AC-AUTH-006 | Given an incorrect password, when login is attempted, then login is refused and a failed-login audit entry is recorded. | FR-AUTH-009, FR-AUTH-033 |
| AC-AUTH-007 | Given a new staff/student/parent record is successfully created, then a login account with a temporary password is provisioned automatically and an audit entry is recorded. | FR-AUTH-020, FR-AUTH-021 |
| AC-AUTH-008 | Given provisioning with SMS and Email enabled, then credentials are sent via both channels and a communication log entry is recorded for each. | FR-AUTH-022 |
| AC-AUTH-009 | Given a disabled or failing communication channel, when a record is created, then provisioning still succeeds and the channel outcome is logged. | FR-AUTH-023, BR-AUTH-009 |
| AC-AUTH-010 | Given a user logs in with a temporary password, when they reach the app, then they are forced to set a new password before any module is accessible. | FR-AUTH-015, BR-AUTH-005 |
| AC-AUTH-011 | Given a successful first-login password change, then the temporary password no longer works. | FR-AUTH-016, VR-AUTH-007 |
| AC-AUTH-012 | Given an authenticated user, when they change their password with the correct current password and a policy-compliant new password, then the change applies and is consistent across their identities. | FR-AUTH-017, FR-AUTH-018, VR-AUTH-005, VR-AUTH-006 |
| AC-AUTH-013 | Given an administrator resets a user's password, then a new temporary password is issued, the user is forced to change it next login, and the event is audited. | FR-AUTH-019, BR-AUTH-006 |
| AC-AUTH-014 | Given the configured number of consecutive failed logins is reached, then the account is locked temporarily and further attempts are refused until expiry or admin reset. | FR-AUTH-027, BR-AUTH-010 |
| AC-AUTH-015 | Given a session exceeds its configured lifetime, when the user next acts, then they are required to re-login. | FR-AUTH-024, BR-AUTH-011 |
| AC-AUTH-016 | Given an authenticated user, when they view sessions, then they can see and terminate active sessions. | FR-AUTH-025 |
| AC-AUTH-017 | Given the same user, when logging in from web and from the Flutter app, then authentication, session, and authorization behave identically. | FR-AUTH-028, BR-AUTH-012 |
| AC-AUTH-018 | Given the school domain, when a user navigates to /login, then the unified login is served under the single domain (no separate ERP domain). | FR-AUTH-029, SYS-DEP-002 |
| AC-AUTH-019 | Given any login, logout, failed login, reset, or lockout event, then a corresponding audit entry is recorded without storing secrets. | FR-AUTH-033, SYS-AUD-005 |
| AC-AUTH-020 | Given any authentication exchange, then it occurs over HTTPS. | FR-AUTH-031, SYS-API-004 |

---

## 15. Non-Functional Requirements

**Applies:** SYS-NFR-001..008 (system-wide baseline) and SYS-SEC-001..008 (security controls).

| ID | Non-functional requirement | Pri | Verify |
|----|----------------------------|:--:|:--:|
| NFR-AUTH-001 | The login interaction shall respond promptly under the school's normal concurrent-login load (e.g., start-of-day sign-ins). | M | A |
| NFR-AUTH-002 | Authentication shall remain available during school hours and degrade gracefully if credential-delivery providers are unavailable (login itself unaffected). | M | A |
| NFR-AUTH-003 | The login and forced-password-change flows shall be fully usable on a phone (mobile-first). | M | D |
| NFR-AUTH-004 | Authentication messaging shall be non-disclosing (must not reveal whether an identifier exists) while remaining clear to legitimate users. | M | I |
| NFR-AUTH-005 | Credentials shall be stored using strong one-way hashing and never displayed or logged. | M | I |
| NFR-AUTH-006 | Login, forced-change, and session-expiry screens shall meet the accessibility baseline (contrast, keyboard navigation, clear errors). | S | T |

---

## 16. Traceability Summary

| Source (PRD / Arch / SYS / BR) | Covered by |
|--------------------------------|-----------|
| PRD 05 — Unified login | FR-AUTH-001, FR-AUTH-002 |
| PRD 05 — Staff login identifiers | FR-AUTH-003 / VR-AUTH-003 |
| PRD 05 — Student login (Admission Number, numeric ≤6, unique) | FR-AUTH-004 / VR-AUTH-002 |
| PRD 05 — Parent login identifiers | FR-AUTH-005 / VR-AUTH-004 |
| PRD 05 — Super Admin login | FR-AUTH-006 / BR-AUTH-008 |
| PRD 05 — Automatic account provisioning | FR-AUTH-020, FR-AUTH-021 / BR-AUTH-004 |
| PRD 05 — Temporary password | FR-AUTH-021 / VR-AUTH-007 |
| PRD 05 — First-login forced change | FR-AUTH-015, FR-AUTH-016 / BR-AUTH-005 |
| PRD 05 — Password reset & self-change | FR-AUTH-017..019 / VR-AUTH-005/006 / BR-AUTH-006/007 |
| PRD 05 / SYS-SEC-004 — Session management | FR-AUTH-024..026 / BR-AUTH-011 |
| SYS-SEC-002 — Account lockout | FR-AUTH-027 / BR-AUTH-010 |
| SYS-SEC-003 — Login & device history | FR-AUTH-012, FR-AUTH-013 |
| SYS-AUTH-005 / BR-Index — Login gates | FR-AUTH-007..009 / BR-AUTH-002/003 |
| SYS-RBAC — Session carries role/permission/scope | FR-AUTH-010, FR-AUTH-011 / BR-AUTH-013 |
| SYS-ACCT-002 / SYS-NOT — Credential delivery | FR-AUTH-022, FR-AUTH-023 / BR-AUTH-009 |
| SYS-AUD-001/002 — Authentication audit | FR-AUTH-033 / §12 |
| PRD 04 / SYS-DEP-002 — Website login (one domain) | FR-AUTH-029 |
| PRD 04 / SYS-INT-003 — Flutter login (one app) | FR-AUTH-030 / FR-AUTH-028 |
| SYS-API-001/004 — One API, HTTPS | FR-AUTH-028, FR-AUTH-031 / BR-AUTH-012 |
| PRD 08 / SYS-UI-001..004 — UI preservation | §13 (UI Preservation Notes) |
| SYS-NFR / SYS-SEC — Non-functional baseline | NFR-AUTH-001..006 |

> Coverage check: every Authentication capability listed in the task (Unified Login, Staff/Student/
> Parent/Super Admin Login, First-Login Password Change, Automatic Account Provisioning, Temporary
> Password, Session Management, Password Reset, Authentication Audit, Website Login, Flutter Login)
> maps to at least one requirement above, and every requirement traces to a PRD/Architecture/SYS/BR
> source.

---

## 17. Open Questions / Assumptions

- **Session lifetime value:** The exact session duration is configurable under Settings → Security (SYS-CFG/SYS-SEC-004). The reference application used a fixed session window; the product makes it configurable. *Assumption:* a sensible default is set at installation and tunable by the Administrator.
- **Password policy specifics:** Concrete complexity/length/expiry/reuse values are configuration, defined under Settings → Security; this spec requires enforcement (FR-AUTH-032, VR-AUTH-005), not specific values.
- **Lockout threshold/duration:** Configurable under Settings → Security; this spec requires the control (FR-AUTH-027), not specific numbers.
- **Lockout notification (optional):** Listed as optional in §10; final inclusion is a configuration/UX choice and does not change core authentication behaviour.
- No open questions affect the approved product scope; none introduce new features.
