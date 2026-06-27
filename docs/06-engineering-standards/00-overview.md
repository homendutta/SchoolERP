# 06 – Engineering Standards (Overview)

**Product:** Asylinx School ERP
**Status:** Approved
**Version:** 1.0
**Last updated:** 2026-06-26

> Shared development standards for all engineers across the three stacks (Laravel backend, React web,
> Flutter mobile). These standards are binding and complement the approved Architecture
> (`docs/03-system-architecture/`) and Coding Standards (`docs/03-system-architecture/14-coding-standards.md`).

---

## Documents

| # | Document | Covers |
|---|----------|--------|
| 00 | **00-overview.md** (this) | Index + toolchain summary. |
| 01 | [01-coding-standards.md](01-coding-standards.md) | Per-stack coding rules and the layering contract. |
| 02 | [02-naming-conventions.md](02-naming-conventions.md) | Naming for classes, files, variables, modules. |
| 03 | [03-folder-conventions.md](03-folder-conventions.md) | Where code lives; module + Platform structure. |
| 04 | [04-git-and-branching.md](04-git-and-branching.md) | Commit convention, branch strategy, PR rules. |

A short root [CONTRIBUTING.md](../../CONTRIBUTING.md) points here.

---

## Toolchain (configured in this foundation)

| Concern | Backend (Laravel) | Web (React/Vite) | Mobile (Flutter) |
|---------|-------------------|------------------|------------------|
| Formatter | Laravel Pint (`pint.json`) | Prettier (`.prettierrc.json`) | `dart format` |
| Linter / static analysis | PHPStan + Larastan (`phpstan.neon`) | ESLint flat config (`eslint.config.js`) | `flutter analyze` (`analysis_options.yaml`) |
| Type safety | PHP 8.2 `declare(strict_types=1)` | TypeScript `strict: true` | Dart sound null safety |
| Tests | PHPUnit (`phpunit.xml`) | (Vitest/RTL — added with features) | `flutter_test` |
| Editor config | `.editorconfig` (root, all stacks) | same | same |

> Pest is **not** adopted; PHPUnit is the backend test standard. No tests are written at the foundation
> stage — only the structure and tooling are prepared.

## Common commands

```
# Backend
composer lint        # pint --test
composer format      # pint
composer analyse     # phpstan
composer test        # phpunit

# Web
npm run typecheck    # tsc --noEmit
npm run lint         # eslint .
npm run format       # prettier --write

# Mobile
dart format .
flutter analyze
```
