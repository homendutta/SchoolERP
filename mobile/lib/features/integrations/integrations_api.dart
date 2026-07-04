import '../../core/api/api_client.dart';

/// Integrations Platform — MONITORING API surface for the mobile app (Sprint 22).
///
/// The Integration Platform is the single gateway between the ERP and every
/// third-party system: providers are resolved by category (never called directly),
/// credentials are encrypted, requests/failures are logged, webhooks verify HMAC
/// signatures and retry on the queue, and the event bus records immutable domain
/// events. Administrators use the app to MONITOR only — view provider status, run a
/// provider test, and read integration logs/events. No configuration is possible
/// from Flutter (config is admin-web + encrypted-at-rest only).
class IntegrationsApi {
  IntegrationsApi(this._api);

  final ApiClient _api;

  String _query(Map<String, dynamic>? params) {
    if (params == null || params.isEmpty) return '';
    final pairs = <String>[];
    params.forEach((key, value) {
      if (value == null) return;
      pairs.add('${Uri.encodeQueryComponent(key)}=${Uri.encodeQueryComponent('$value')}');
    });
    return pairs.isEmpty ? '' : '?${pairs.join('&')}';
  }

  /// Provider status, success rate, response time, retry queue, failed requests.
  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/integrations/dashboard${_query(params)}');

  /// The registered adapter catalog (discovery).
  Future<dynamic> adapters() => _api.get('/integrations/adapters');

  /// Providers (read-only monitoring).
  Future<dynamic> providers([Map<String, dynamic>? params]) => _api.get('/integrations/providers${_query(params)}');

  /// Run a provider's health check.
  Future<dynamic> health(int id) => _api.get('/integrations/providers/$id/health');

  /// Run a provider's connectivity/config test.
  Future<dynamic> test(int id) => _api.post('/integrations/providers/$id/test');

  /// The immutable event bus.
  Future<dynamic> events([Map<String, dynamic>? params]) => _api.get('/integrations/events${_query(params)}');

  /// Integration request logs.
  Future<dynamic> logs([Map<String, dynamic>? params]) => _api.get('/integrations/logs${_query(params)}');
}
