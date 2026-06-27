import 'package:flutter/material.dart';
import 'theme/app_theme.dart';
import 'theme/app_colors.dart';
import '../core/api/api_client.dart';
import '../core/auth/session.dart';
import '../features/auth/login_screen.dart';
import '../features/dashboard/dashboard_shell.dart';

/// Root application — one app for all roles. Wires the API client + session,
/// restores any persisted login, then shows the role-adaptive DashboardShell or
/// the LoginScreen.
///
/// API base URL is configurable: pass
///   --dart-define=API_BASE_URL=https://schoola.example/api/v1
/// Default targets the Android emulator host (10.0.2.2) -> Laravel dev server.
class AsylinxApp extends StatefulWidget {
  const AsylinxApp({super.key});

  static const _defaultBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  @override
  State<AsylinxApp> createState() => _AsylinxAppState();
}

class _AsylinxAppState extends State<AsylinxApp> {
  late final Session _session;

  @override
  void initState() {
    super.initState();
    _session = Session(ApiClient(AsylinxApp._defaultBaseUrl));
    _session.bootstrap();
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: _session,
      builder: (context, _) {
        final Widget home;
        if (_session.isLoading) {
          home = const Scaffold(
            backgroundColor: AppColors.navyPrimary,
            body: Center(child: CircularProgressIndicator(color: Colors.white)),
          );
        } else if (_session.isAuthenticated) {
          home = DashboardShell(session: _session);
        } else {
          home = LoginScreen(session: _session);
        }

        return MaterialApp(
          title: 'Asylinx School ERP',
          debugShowCheckedModeBanner: false,
          // Theme synchronization: the school's branding color drives the theme.
          theme: AppTheme.light(_session.themeColor),
          darkTheme: AppTheme.dark(),
          themeMode: ThemeMode.light,
          home: home,
        );
      },
    );
  }
}
