import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../app/navigation/menu_catalog.dart';
import '../../app/theme/app_colors.dart';
import '../api/api_client.dart';

/// Holds the signed-in session (token + user) and drives the role-adaptive
/// shell. Real Sanctum token auth against the Laravel API; the token is
/// persisted so the session survives app restarts.
class Session extends ChangeNotifier {
  Session(this._api) {
    _api.tokenProvider = () => _token;
  }

  static const _tokenKey = 'asylinx.token';

  final ApiClient _api;
  String? _token;
  Map<String, dynamic>? _user;
  bool _loading = true;

  bool get isLoading => _loading;
  bool get isAuthenticated => _user != null;
  String get fullName => (_user?['name'] as String?) ?? 'User';

  List<String> get roles =>
      (_user?['roles'] as List?)?.map((e) => e.toString()).toList() ?? const [];

  List<String> get permissions =>
      (_user?['permissions'] as List?)?.map((e) => e.toString()).toList() ?? const [];

  Role get role => roles.isNotEmpty ? roleFromString(roles.first) : Role.admin;

  // ---- Settings + theme synchronization ----
  Map<String, dynamic> _settings = {};
  Color _themeColor = AppColors.navyPrimary;

  Map<String, dynamic> get settings => _settings;
  Color get themeColor => _themeColor;

  /// Pull school settings (best-effort) and apply the branding theme color.
  Future<void> _syncSettings() async {
    try {
      final data = await _api.get('/admin/settings') as Map<String, dynamic>;
      _settings = data;
      final hex = (data['appearance'] as Map?)?['theme_color'] as String?;
      if (hex != null) _themeColor = _parseHex(hex);
    } catch (_) {
      // Settings require permission; ignore for roles without access.
    }
  }

  Color _parseHex(String input) {
    var hex = input.replaceAll('#', '');
    if (hex.length == 6) hex = 'FF$hex';
    final value = int.tryParse(hex, radix: 16);
    return value == null ? AppColors.navyPrimary : Color(value);
  }

  /// Restore a persisted session on startup (GET /auth/me).
  Future<void> bootstrap() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    if (_token != null) {
      try {
        _user = await _api.get('/auth/me') as Map<String, dynamic>;
        await _syncSettings();
      } catch (_) {
        _token = null;
        await prefs.remove(_tokenKey);
      }
    }
    _loading = false;
    notifyListeners();
  }

  Future<void> login(String identifier, String password) async {
    final data = await _api.post('/auth/login', body: {
      'identifier': identifier,
      'password': password,
    }) as Map<String, dynamic>;

    _token = data['token'] as String;
    _user = data['user'] as Map<String, dynamic>;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, _token!);
    await _syncSettings();
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {
      // Best-effort; clear locally regardless.
    }
    _token = null;
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    notifyListeners();
  }
}
