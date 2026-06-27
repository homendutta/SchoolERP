# 04 – Git Commit & Branching Conventions

> One workflow for all three stacks.

---

## 1. Commit Convention (Conventional Commits)

```
<type>(<scope>): <short imperative summary>

[optional body — what & why, not how]

[optional footer — BREAKING CHANGE: …, Refs: …]
```

**Types**

| Type | Use |
|------|-----|
| `feat` | A new capability. |
| `fix` | A bug fix. |
| `refactor` | Code change that neither fixes a bug nor adds a feature. |
| `chore` | Tooling, config, deps, scaffolding. |
| `docs` | Documentation only. |
| `test` | Tests only. |
| `style` | Formatting only (no logic). |
| `perf` | Performance improvement. |
| `build` / `ci` | Build system / pipeline. |

**Scope** = the module or layer (`students`, `finance`, `platform-core`, `web-layout`, `mobile-auth`,
`infra`).

**Examples**

```
chore(backend): scaffold enterprise module structure
refactor(platform): move Shared kernel into Platform layer
docs(standards): add naming and folder conventions
feat(students): add student promotion service        # (future, when implementing)
```

Rules: imperative mood, lower-case summary, no trailing period, ≤ 72 chars on the summary line.

---

## 2. Branch Strategy (trunk-based with short-lived branches)

| Branch | Purpose |
|--------|---------|
| `main` | Always releasable. Protected. No direct commits. |
| `develop` (optional) | Integration branch if the team prefers GitFlow-lite. |
| `feat/<scope>-<slug>` | A feature/module slice. |
| `fix/<scope>-<slug>` | A bug fix. |
| `refactor/<scope>-<slug>` | A refactor. |
| `chore/<scope>-<slug>` | Tooling/config. |
| `release/<version>` | Release stabilization (if used). |

- Branches are **short-lived** and merge via Pull Request.
- Branch from `main` (or `develop`); rebase or squash-merge to keep history clean.

---

## 3. Pull Requests

A PR must:
1. Be scoped to one module/concern.
2. Pass CI: format check, lint, static analysis, type check, tests.
3. Respect the architecture: correct layering, module boundaries, permissions/scope, audit.
4. Preserve the reference UX where UI is touched.
5. Include a clear description (what + why) and link the SRS requirement(s) it implements.

At least one reviewer approval is required. Reviewers check layering, security/scope, and standards.

---

## 4. Hygiene

- Never commit secrets or `.env`; only `.env.example`.
- Never commit `vendor/`, `node_modules/`, build artifacts, or archives (`*.zip`).
- Do not bypass hooks or quality gates (`--no-verify` is not allowed).
- Keep commits atomic and reviewable; avoid mixing refactor + feature in one commit.
