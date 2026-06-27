# Frontend — Asylinx School ERP (React + Vite + Tailwind)

The web ERP client. **Engineering foundation only** — the application shell, navigation, theme, login,
and a single API client. No business pages or API calls yet.

## UI Preservation

The shell **preserves the reference Apps Script application's UI/UX** — navy sidebar, grouped
navigation, top header, cards, and overall workflow — rebuilt on React + Vite + Tailwind. **No reference
code is copied.**

## Layout

```
src/
├── app/
│   ├── layout/          # Sidebar · Header · Footer · BottomNavigation · DashboardLayout
│   ├── navigation/      # menu catalog, groups, per-role ordering, landing
│   └── routing/         # AppRoutes (role workspaces under one domain)
├── core/
│   ├── api/client.ts    # single API client (envelope-aware; no endpoints yet)
│   └── auth/AuthContext.tsx
├── features/
│   ├── auth/LoginPage.tsx
│   └── dashboard/DashboardPage.tsx
├── styles/index.css     # Tailwind + reference theme tokens (CSS variables)
├── App.tsx · main.tsx
```

Path aliases: `@`, `@app`, `@core`, `@ui`, `@features` (see `vite.config.ts` / `tsconfig.json`).

## Theme

Reference navy palette is exposed as CSS variables (`--navy-primary`, `--navy-accent`, …) and mapped to
Tailwind tokens, so the **Branding** capability can override theme color and dark mode at runtime
without a rebuild. Iconography uses Font Awesome (as in the reference).

## Conventions

- TypeScript, feature-first modules, composition from a shared `ui/` design system (added as modules grow).
- All server access goes through the single API client; the backend is the authoritative authorizer.

## Getting started

```bash
npm install
cp .env.example .env
npm run dev
```

> At this stage the login uses a local, foundation-only sign-in (with a role preview selector) so the
> role-adaptive shell is navigable. Real authentication is wired to the Authentication module API later.
