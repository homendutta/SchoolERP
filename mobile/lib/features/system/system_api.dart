import '../../core/api/api_client.dart';

/// System / Operations MONITORING API surface for the mobile app (Sprint 23 —
/// Production Hardening). Administrators use the app to observe the running ERP:
/// overall health + component status, diagnostics, the production dashboard,
/// backup manifests + verification, and failed-job monitoring. A public liveness
/// probe (`/health`) is also exposed for uptime monitors. No business data here.
class SystemApi {
  SystemApi(this._api);

  final ApiClient _api;

  /// Public liveness/readiness probe (no auth) — overall status + score only.
  Future<dynamic> health() => _api.get('/health');

  Future<dynamic> dashboard() => _api.get('/system/dashboard');
  Future<dynamic> systemHealth() => _api.get('/system/health');
  Future<dynamic> diagnostics() => _api.get('/system/diagnostics');
  Future<dynamic> config() => _api.get('/system/config');
  Future<dynamic> logs([Map<String, dynamic>? params]) {
    final q = (params == null || params.isEmpty)
        ? ''
        : '?${params.entries.where((e) => e.value != null).map((e) => '${Uri.encodeQueryComponent(e.key)}=${Uri.encodeQueryComponent('${e.value}')}').join('&')}';
    return _api.get('/system/logs$q');
  }

  // Backups (metadata + verification).
  Future<dynamic> backups() => _api.get('/system/backups');
  Future<dynamic> createBackup(Map<String, dynamic> body) => _api.post('/system/backups', body: body);
  Future<dynamic> verifyBackup(int id) => _api.post('/system/backups/$id/verify');

  // Failed-job monitoring.
  Future<dynamic> failedJobs() => _api.get('/system/failed-jobs');
}
