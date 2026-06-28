import '../../core/api/api_client.dart';

/// Communication Management API surface for the mobile app.
///
/// The central communication hub — every module publishes here; nothing sends
/// Email/SMS/Push/In-App directly. Templates, the message queue + delivery
/// tracking, configurable channels, user preferences, announcements and
/// circulars. The mobile app exposes the endpoints only (no UI in this sprint).
class CommunicationApi {
  CommunicationApi(this._api);

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

  Future<dynamic> dashboard({int? schoolId}) =>
      _api.get('/communication/dashboard${_query({'school_id': schoolId})}');

  // ---- Templates ----
  Future<dynamic> templates([Map<String, dynamic>? params]) =>
      _api.get('/communication/templates${_query(params)}');
  Future<dynamic> createTemplate(Map<String, dynamic> body) => _api.post('/communication/templates', body: body);
  Future<dynamic> updateTemplate(int id, Map<String, dynamic> body) => _api.put('/communication/templates/$id', body: body);
  Future<dynamic> deleteTemplate(int id) => _api.delete('/communication/templates/$id');

  // ---- Messages (publish is the only send path) + delivery tracking ----
  Future<dynamic> messages([Map<String, dynamic>? params]) =>
      _api.get('/communication/messages${_query(params)}');
  Future<dynamic> scheduled(int schoolId) =>
      _api.get('/communication/messages/scheduled${_query({'school_id': schoolId})}');
  Future<dynamic> publish(Map<String, dynamic> body) => _api.post('/communication/messages', body: body);
  Future<dynamic> message(int id) => _api.get('/communication/messages/$id');
  Future<dynamic> retry(int id) => _api.post('/communication/messages/$id/retry');
  Future<dynamic> markRead(int id) => _api.post('/communication/messages/$id/read');
  Future<dynamic> cancel(int id) => _api.post('/communication/messages/$id/cancel');

  // ---- Queue worker ----
  Future<dynamic> processQueue(int schoolId) =>
      _api.post('/communication/queue/process', body: {'school_id': schoolId});

  // ---- Channels (settings + provider registry) ----
  Future<dynamic> channels([Map<String, dynamic>? params]) =>
      _api.get('/communication/channels${_query(params)}');
  Future<dynamic> saveChannel(Map<String, dynamic> body) => _api.post('/communication/channels', body: body);

  // ---- User preferences ----
  Future<dynamic> preferences({int? userId}) =>
      _api.get('/communication/preferences${_query({'user_id': userId})}');
  Future<dynamic> updatePreferences(Map<String, dynamic> body) =>
      _api.put('/communication/preferences', body: body);

  // ---- Announcements ----
  Future<dynamic> announcements([Map<String, dynamic>? params]) =>
      _api.get('/communication/announcements${_query(params)}');
  Future<dynamic> createAnnouncement(Map<String, dynamic> body) =>
      _api.post('/communication/announcements', body: body);

  // ---- Circulars (Media attachment reference) ----
  Future<dynamic> circulars([Map<String, dynamic>? params]) =>
      _api.get('/communication/circulars${_query(params)}');
  Future<dynamic> createCircular(Map<String, dynamic> body) =>
      _api.post('/communication/circulars', body: body);
}
