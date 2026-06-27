# Contributing — Asylinx School ERP

This project follows the approved Asylinx Enterprise Architecture. Before contributing, read the
engineering standards in [`docs/06-engineering-standards/`](docs/06-engineering-standards/00-overview.md):

- [Coding standards](docs/06-engineering-standards/01-coding-standards.md)
- [Naming conventions](docs/06-engineering-standards/02-naming-conventions.md)
- [Folder conventions](docs/06-engineering-standards/03-folder-conventions.md)
- [Git commit & branching](docs/06-engineering-standards/04-git-and-branching.md)

## Golden rules

1. Preserve the reference UX; **never copy** its code.
2. Backend layering is mandatory: **Controller → Request → Service → Repository → Model**.
3. Organize by **business module**; `Platform` is infrastructure only.
4. One API for web + mobile; enforce **RBAC + data scope** server-side; **audit** material actions.
5. Quality gates must pass: format, lint, static analysis, type check, tests.

## Quick start

```
# Backend (Laravel 12)
cd backend && composer install && cp .env.example .env && php artisan key:generate

# Web (React + Vite + Tailwind)
cd frontend && npm install && cp .env.example .env && npm run dev

# Mobile (Flutter)
cd mobile && flutter pub get && flutter run
```

> The repository currently holds the **engineering foundation only** — no database tables, migrations,
> endpoints, or business modules.
