# Mobile — Asylinx School ERP (Flutter)

The **single** mobile app for **all roles**. **Engineering foundation only** — theme, navigation,
login, and the role-adaptive dashboard shell. No business screens or API calls yet.

## One App, Every Role

There is exactly one Flutter app. After sign-in, the **DashboardShell** adapts its drawer and bottom
navigation to the signed-in role — mirroring the web client and preserving the reference application's
structure on mobile.

## Layout

```
lib/
├── app/
│   ├── app.dart                 # root MaterialApp + auth gate (Login ↔ Shell)
│   ├── theme/                   # app_colors.dart · app_theme.dart (navy identity)
│   └── navigation/menu_catalog.dart   # roles, menu catalog, groups, per-role order
├── core/
│   └── auth/session.dart        # in-memory session (ChangeNotifier; no API yet)
├── features/
│   ├── auth/login_screen.dart
│   └── dashboard/dashboard_shell.dart # app bar + grouped drawer + bottom nav
└── main.dart
```

## Theme

Navy palette preserved from the reference application (`AppColors`), applied through Material 3 themes
(`AppTheme.light()` / `AppTheme.dark()`). Branding overrides are wired as that capability is built.

## Conventions

- Feature-first; layered presentation → domain → data (added as modules grow).
- The app consumes the **same single API** as the web client; no business logic on device.

## Getting started

```bash
flutter pub get
flutter run
```

> At this stage the login uses a local, foundation-only sign-in (with a role preview selector) so the
> role-adaptive shell is navigable. Real authentication and push notifications are wired later.
