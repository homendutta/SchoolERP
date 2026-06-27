# 02 – Naming Conventions

> Consistent names across stacks. Module names are identical on backend, web, and mobile.

---

## 1. Modules

- **PascaledPlural/canonical names** matching the approved list: `Administration`, `Authentication`,
  `Academic`, `Admissions`, `Students`, `Parents`, `Staff`, `Attendance`, `Timetable`, `Examination`,
  `Finance`, `Accounts`, `Communication`, `Website`, `Reports`, `Assets`, `Inventory`.
- The same module name is used in `app/Modules/<Module>` (backend), `src/features/<module>` (web), and
  `lib/features/<module>` (mobile).

---

## 2. Backend (PHP)

| Element | Convention | Example |
|---------|-----------|---------|
| Namespace | `App\Modules\<Module>\<Layer>` | `App\Modules\Students\Services` |
| Class file | `StudlyCase.php`, one class per file | `StudentService.php` |
| Controller | `<Noun>Controller` | `StudentController` |
| Form request | `<Verb><Noun>Request` | `CreateStudentRequest` |
| Service | `<Noun>Service` | `StudentService` |
| Repository interface / impl | `<Noun>RepositoryInterface` / `<Noun>Repository` | `StudentRepository` |
| Policy | `<Noun>Policy` | `StudentPolicy` |
| Domain event | past-tense | `StudentEnrolled` |
| Job | `<Verb><Noun>Job` | `GenerateMonthlyDuesJob` |
| Resource | `<Noun>Resource` | `StudentResource` |
| Method | `camelCase`, verb-first | `enrollStudent()` |
| Constant | `UPPER_SNAKE_CASE` | `MAX_ADMISSION_DIGITS` |

---

## 3. Web (TypeScript/React)

| Element | Convention | Example |
|---------|-----------|---------|
| Component file | `PascalCase.tsx` | `Sidebar.tsx` |
| Hook | `useCamelCase.ts` | `useStudents.ts` |
| Non-component module | `camelCase.ts` | `menu.ts`, `client.ts` |
| Type / interface | `PascalCase` | `SessionUser` |
| Variable / function | `camelCase` | `buildSidebar` |
| Constant | `UPPER_SNAKE_CASE` | `MENU_CATALOG` |
| CSS class (utility) | Tailwind utilities; custom classes `kebab-case` | `sidebar-item` |

---

## 4. Mobile (Dart)

| Element | Convention | Example |
|---------|-----------|---------|
| File | `snake_case.dart` | `dashboard_shell.dart` |
| Class / enum | `PascalCase` | `DashboardShell`, `Role` |
| Member / function | `camelCase` | `menuForRole` |
| Constant | `lowerCamelCase` (Dart style) | `groupLabels` |

---

## 5. Database (when designed later)

> Not created yet — recorded here for the Database Design phase.

- Tables: `snake_case`, plural (`students`, `fee_payments`).
- Columns: `snake_case`; foreign keys `<entity>_id`.
- Primary key: `id`. Timestamps: `created_at`, `updated_at`.

---

## 6. General

- Names are descriptive and unabbreviated except well-known domain terms (PTM, TC, SLA).
- Booleans read affirmatively (`isActive`, `hasPaid`), never negated (`isNotActive`).
- Avoid stutter (`StudentStudentService`) and generic names (`data`, `helper`, `manager`) without context.
